const fs = require('fs');
const path = require('path');
const os = require('os');
const { spawnSync } = require('child_process');

function exists(p) {
  try {
    fs.accessSync(p);
    return true;
  } catch {
    return false;
  }
}

function listFiles(dir) {
  return fs.readdirSync(dir, { withFileTypes: true })
    .filter((d) => d.isFile())
    .map((d) => path.join(dir, d.name));
}

function pickInstallerExe(distDir) {
  const exes = listFiles(distDir)
    .filter((p) => p.toLowerCase().endsWith('.exe'))
    // ignore potential helper exes
    .filter((p) => !p.toLowerCase().includes('unins'));

  if (exes.length === 0) return null;

  // Prefer portable (single file) naming
  const portable = exes.find((p) => /portable/i.test(path.basename(p)));
  if (portable) return portable;

  // Next, prefer typical NSIS naming
  const setup = exes.find((p) => /setup/i.test(path.basename(p)));
  if (setup) return setup;

  // Otherwise pick the newest .exe
  const sorted = exes
    .map((p) => ({ p, mtime: fs.statSync(p).mtimeMs }))
    .sort((a, b) => b.mtime - a.mtime);

  return sorted[0].p;
}

function copyDir(src, dest) {
  if (exists(dest)) {
    fs.rmSync(dest, { recursive: true, force: true });
  }

  if (typeof fs.cpSync === 'function') {
    fs.cpSync(src, dest, { recursive: true });
    return;
  }

  fs.mkdirSync(dest, { recursive: true });
  for (const entry of fs.readdirSync(src, { withFileTypes: true })) {
    const srcPath = path.join(src, entry.name);
    const destPath = path.join(dest, entry.name);
    if (entry.isDirectory()) {
      copyDir(srcPath, destPath);
    } else if (entry.isFile()) {
      fs.copyFileSync(srcPath, destPath);
    }
  }
}

function createWindowsShortcut({ shortcutPath, targetPath, workingDirectory }) {
  const esc = (s) => s.replace(/'/g, "''");
  const psCommand = [
    "$WshShell = New-Object -ComObject WScript.Shell;",
    `$Shortcut = $WshShell.CreateShortcut('${esc(shortcutPath)}');`,
    `$Shortcut.TargetPath = '${esc(targetPath)}';`,
    `$Shortcut.WorkingDirectory = '${esc(workingDirectory)}';`,
    '$Shortcut.Save();'
  ].join(' ');

  const result = spawnSync('powershell', ['-NoProfile', '-ExecutionPolicy', 'Bypass', '-Command', psCommand], {
    stdio: 'inherit'
  });

  if (result.status !== 0) {
    throw new Error(`No se pudo crear el acceso directo (exit ${result.status ?? 'unknown'})`);
  }
}

function main() {
  const distDir = path.resolve(__dirname, '..', 'dist');
  if (!exists(distDir)) {
    console.error(`[copy] No existe la carpeta dist: ${distDir}`);
    process.exit(1);
  }

  const installer = pickInstallerExe(distDir);

  const desktopDir = process.env.USERPROFILE
    ? path.join(process.env.USERPROFILE, 'Desktop')
    : path.join(os.homedir(), 'Desktop');

  if (!exists(desktopDir)) {
    console.error(`[copy] No existe la carpeta Desktop: ${desktopDir}`);
    process.exit(1);
  }

  if (installer) {
    const destName = 'ProyectoChelas-Installer.exe';
    const destPath = path.join(desktopDir, destName);
    fs.copyFileSync(installer, destPath);
    console.log(`[copy] Instalador/portable copiado a: ${destPath}`);
    console.log('[copy] Puedes ejecutarlo y el instalador creará el acceso directo en el Escritorio.');
    return;
  }

  const unpackedDir = path.join(distDir, 'win-unpacked');
  const unpackedExe = path.join(unpackedDir, 'ProyectoChelas.exe');
  if (!exists(unpackedExe)) {
    console.error('[copy] No se encontró un .exe en desktop/dist ni el fallback dist/win-unpacked/ProyectoChelas.exe');
    console.error('[copy] Nota: si ves error de winCodeSign (symlinks), habilita Developer Mode o ejecuta como Administrador.');
    process.exit(1);
  }

  // IMPORTANT: this repo is commonly located at Desktop\ProyectoChelas.
  // Don't overwrite the source code folder.
  const destFolder = path.join(desktopDir, 'ProyectoChelas-App');
  copyDir(unpackedDir, destFolder);

  // Always create a simple launcher on the Desktop.
  // This works even if creating a .lnk shortcut is blocked by policy.
  try {
    const batPath = path.join(desktopDir, 'ProyectoChelas.bat');
    const bat = [
      '@echo off',
      'set "APP_DIR=%~dp0ProyectoChelas-App"',
      'start "ProyectoChelas" "%APP_DIR%\\ProyectoChelas.exe"',
      ''
    ].join('\r\n');
    fs.writeFileSync(batPath, bat, 'utf8');
  } catch (e) {
    console.warn(`[copy] No pude crear ProyectoChelas.bat en el Escritorio: ${e.message}`);
  }

  const shortcutPath = path.join(desktopDir, 'ProyectoChelas.lnk');
  const targetPath = path.join(destFolder, 'ProyectoChelas.exe');

  try {
    createWindowsShortcut({
      shortcutPath,
      targetPath,
      workingDirectory: destFolder
    });
  } catch (e) {
    console.warn(`[copy] Copié la app a ${destFolder}, pero falló crear el acceso directo: ${e.message}`);
  }

  console.log(`[copy] App copiada a: ${destFolder}`);
  console.log(`[copy] Ejecuta desde el acceso directo del Escritorio (ProyectoChelas) o desde: ${targetPath}`);
}

if (require.main === module) {
  main();
}

module.exports = { main };
