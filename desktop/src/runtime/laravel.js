const { app } = require('electron');
const path = require('path');
const fs = require('fs');
const crypto = require('crypto');
const spawn = require('cross-spawn');
const net = require('net');
const http = require('http');
const log = require('electron-log');

function isPortAvailable(port) {
  return new Promise((resolve) => {
    const server = net.createServer();
    server.unref();
    server.on('error', () => resolve(false));
    server.listen({ port, host: '127.0.0.1' }, () => {
      server.close(() => resolve(true));
    });
  });
}

async function pickPort(preferredPorts) {
  for (const port of preferredPorts) {
    // eslint-disable-next-line no-await-in-loop
    const available = await isPortAvailable(port);
    if (available) return port;
  }

  return new Promise((resolve, reject) => {
    const server = net.createServer();
    server.unref();
    server.on('error', reject);
    server.listen({ port: 0, host: '127.0.0.1' }, () => {
      const address = server.address();
      const port = typeof address === 'object' && address ? address.port : 0;
      server.close(() => resolve(port));
    });
  });
}

function getLaravelBasePath(repoRoot) {
  if (app.isPackaged) {
    return path.join(process.resourcesPath, 'laravel');
  }

  return repoRoot;
}

function resolvePhpBinary() {
  const packagedPhp = path.join(process.resourcesPath, 'php', 'php.exe');
  if (app.isPackaged && fs.existsSync(packagedPhp)) {
    return packagedPhp;
  }

  const devBundledPhp = path.resolve(__dirname, '..', '..', 'resources', 'php', 'php.exe');
  if (!app.isPackaged && fs.existsSync(devBundledPhp)) {
    return devBundledPhp;
  }

  return 'php';
}

function resolvePhpIni() {
  const packagedIni = path.join(process.resourcesPath, 'php', 'php.ini');
  if (app.isPackaged && fs.existsSync(packagedIni)) {
    return packagedIni;
  }

  const devBundledIni = path.resolve(__dirname, '..', '..', 'resources', 'php', 'php.ini');
  const devBundledPhp = path.resolve(__dirname, '..', '..', 'resources', 'php', 'php.exe');
  if (!app.isPackaged && fs.existsSync(devBundledPhp) && fs.existsSync(devBundledIni)) {
    return devBundledIni;
  }

  return null;
}

async function assertSqliteSupport({ phpBin, phpIni, laravelPath, env }) {
  const args = phpIni
    ? ['-c', phpIni, '-r', "if (!extension_loaded('pdo_sqlite') || !extension_loaded('sqlite3')) { fwrite(STDERR, 'Missing sqlite extensions'); exit(2); }"]
    : ['-r', "if (!extension_loaded('pdo_sqlite') || !extension_loaded('sqlite3')) { fwrite(STDERR, 'Missing sqlite extensions'); exit(2); }"];

  try {
    await runPhpCommand({ phpBin, laravelPath, args, env });
  } catch (e) {
    throw new Error(
      'Tu runtime de PHP no tiene SQLite habilitado (pdo_sqlite/sqlite3).\n' +
        'Solución rápida para probar local: descarga un PHP portable que incluya SQLite y colócalo en: desktop/resources/php/php.exe (y su carpeta ext/).\n' +
        'Luego vuelve a ejecutar el wrapper.\n\n' +
        String(e?.message || e)
    );
  }
}

function ensureAppKey(userDataPath) {
  const keyFile = path.join(userDataPath, 'laravel-app.key');
  if (fs.existsSync(keyFile)) {
    const key = fs.readFileSync(keyFile, 'utf8').trim();
    if (key) return key;
  }

  const raw = crypto.randomBytes(32);
  const key = `base64:${raw.toString('base64')}`;
  fs.mkdirSync(userDataPath, { recursive: true });
  fs.writeFileSync(keyFile, key, 'utf8');
  return key;
}

function ensureSqliteFile(sqlitePath) {
  fs.mkdirSync(path.dirname(sqlitePath), { recursive: true });
  if (!fs.existsSync(sqlitePath)) {
    fs.writeFileSync(sqlitePath, '');
  }
}

function ensureLaravelStorage(storagePath) {
  const dirs = [
    storagePath,
    path.join(storagePath, 'app'),
    path.join(storagePath, 'framework'),
    path.join(storagePath, 'framework', 'cache'),
    path.join(storagePath, 'framework', 'sessions'),
    path.join(storagePath, 'framework', 'views'),
    path.join(storagePath, 'logs')
  ];

  for (const dir of dirs) {
    fs.mkdirSync(dir, { recursive: true });
  }
}

function waitForHttp(url, timeoutMs = 20000) {
  const start = Date.now();

  return new Promise((resolve, reject) => {
    const tick = () => {
      const elapsed = Date.now() - start;
      if (elapsed > timeoutMs) {
        reject(new Error(`Timeout esperando HTTP: ${url}`));
        return;
      }

      const req = http.get(url, (res) => {
        res.resume();
        if (res.statusCode && res.statusCode >= 200 && res.statusCode < 500) {
          resolve();
        } else {
          setTimeout(tick, 250);
        }
      });

      req.on('error', () => setTimeout(tick, 250));
      req.end();
    };

    tick();
  });
}

function runPhpCommand({ phpBin, laravelPath, args, env }) {
  return new Promise((resolve, reject) => {
    const child = spawn(phpBin, args, {
      cwd: laravelPath,
      env,
      stdio: ['ignore', 'pipe', 'pipe'],
      windowsHide: true
    });

    let stdout = '';
    let stderr = '';

    child.stdout.on('data', (d) => (stdout += d.toString()));
    child.stderr.on('data', (d) => (stderr += d.toString()));

    child.on('error', reject);
    child.on('close', (code) => {
      if (code === 0) return resolve({ stdout, stderr });
      reject(new Error(`PHP command failed (${code})\n${stderr || stdout}`));
    });
  });
}

async function startLaravel({ repoRoot }) {
  const laravelPath = getLaravelBasePath(repoRoot);
  const phpBin = resolvePhpBinary();
  const phpIni = resolvePhpIni();
  const userDataPath = app.getPath('userData');
  const sqlitePath = path.join(userDataPath, 'data', 'database.sqlite');
  const laravelStoragePath = app.isPackaged
    ? path.join(userDataPath, 'laravel-storage')
    : path.join(laravelPath, 'storage');

  ensureSqliteFile(sqlitePath);
  ensureLaravelStorage(laravelStoragePath);
  const appKey = ensureAppKey(userDataPath);

  const port = await pickPort([35123, 35124, 35125]);
  const url = `http://127.0.0.1:${port}`;

  const baseEnv = {
    ...process.env,
    APP_ENV: 'desktop',
    APP_DEBUG: 'false',
    APP_URL: url,
    APP_KEY: appKey,
    ...(app.isPackaged ? { LARAVEL_STORAGE_PATH: laravelStoragePath } : {}),
    DB_CONNECTION: 'sqlite',
    DB_DATABASE: sqlitePath,
    CACHE_DRIVER: 'file',
    SESSION_DRIVER: 'file',
    QUEUE_CONNECTION: 'sync',
    LOG_CHANNEL: 'stack'
  };

  await assertSqliteSupport({ phpBin, phpIni, laravelPath, env: baseEnv });

  log.info('Running migrations (sqlite at)', sqlitePath);
  const artisanArgs = phpIni ? ['-c', phpIni, 'artisan'] : ['artisan'];
  await runPhpCommand({
    phpBin,
    laravelPath,
    env: baseEnv,
    args: [...artisanArgs, 'migrate', '--force', '--no-interaction']
  });

  log.info('Seeding default users (AdminUserSeeder)');
  await runPhpCommand({
    phpBin,
    laravelPath,
    env: baseEnv,
    args: [
      ...artisanArgs,
      'db:seed',
      '--class=Database\\Seeders\\AdminUserSeeder',
      '--force',
      '--no-interaction'
    ]
  });

  const serverArgs = phpIni
    ? ['-c', phpIni, '-S', `127.0.0.1:${port}`, '-t', 'public', 'server.php']
    : ['-S', `127.0.0.1:${port}`, '-t', 'public', 'server.php'];
  log.info('Starting Laravel server', phpBin, serverArgs.join(' '));

  const serverProcess = spawn(phpBin, serverArgs, {
    cwd: laravelPath,
    env: baseEnv,
    stdio: ['ignore', 'pipe', 'pipe'],
    windowsHide: true
  });

  serverProcess.stdout.on('data', (d) => log.info('[php]', d.toString().trimEnd()));
  serverProcess.stderr.on('data', (d) => log.warn('[php]', d.toString().trimEnd()));

  await waitForHttp(url, 30000);

  return {
    url,
    laravelPath,
    phpBin,
    port,
    sqlitePath,
    serverProcess
  };
}

async function stopLaravel(runtime) {
  if (!runtime?.serverProcess) return;

  return new Promise((resolve) => {
    try {
      runtime.serverProcess.once('close', () => resolve());
      runtime.serverProcess.kill();
      setTimeout(() => resolve(), 1500);
    } catch {
      resolve();
    }
  });
}

module.exports = {
  startLaravel,
  stopLaravel
};
