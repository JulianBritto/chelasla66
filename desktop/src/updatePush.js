const { app, dialog } = require('electron');
const log = require('electron-log');
const fs = require('fs');
const path = require('path');
const WebSocket = require('ws');

function normalizeVersion(v) {
  return String(v || '').trim().replace(/^v/i, '');
}

function parseSemver(v) {
  const cleaned = normalizeVersion(v);
  const main = cleaned.split('-')[0];
  const parts = main.split('.').map((p) => Number.parseInt(p, 10));
  if (parts.some((n) => Number.isNaN(n))) return null;
  return {
    major: parts[0] ?? 0,
    minor: parts[1] ?? 0,
    patch: parts[2] ?? 0
  };
}

function isNewerVersion(incoming, current) {
  const a = parseSemver(incoming);
  const b = parseSemver(current);
  if (!a || !b) return false;
  if (a.major !== b.major) return a.major > b.major;
  if (a.minor !== b.minor) return a.minor > b.minor;
  return a.patch > b.patch;
}

function readPushConfig() {
  // 1) Env var (useful for dev or custom shortcuts)
  const envUrl = process.env.UPDATE_PUSH_WS_URL;
  if (envUrl && String(envUrl).trim()) {
    return { wsUrl: String(envUrl).trim() };
  }

  // 2) Config file inside userData so it's configurable per-machine
  try {
    const configPath = path.join(app.getPath('userData'), 'push-updates.json');
    if (fs.existsSync(configPath)) {
      const raw = fs.readFileSync(configPath, 'utf8');
      const parsed = JSON.parse(raw);
      if (parsed && parsed.wsUrl) return { wsUrl: String(parsed.wsUrl) };
    }
  } catch (e) {
    log.warn('Failed to read push-updates.json', e);
  }

  return { wsUrl: null };
}

async function maybeAlertAndTriggerUpdate({ version, releaseUrl, triggerUpdate }) {
  const current = app.getVersion();
  if (!isNewerVersion(version, current)) {
    return;
  }

  const message = releaseUrl
    ? `Hay una nueva versión del sistema (${version}).\n\n${releaseUrl}`
    : `Hay una nueva versión del sistema (${version}).`;

  await dialog.showMessageBox({
    type: 'info',
    buttons: ['Actualizar'],
    defaultId: 0,
    title: 'Nueva versión disponible',
    message,
    detail: 'Se descargará en segundo plano y cuando esté lista te pedirá reiniciar.'
  });

  triggerUpdate();
}

function startUpdatePushClient({ triggerUpdate }) {
  const { wsUrl } = readPushConfig();
  if (!wsUrl) {
    log.info('Push updates disabled (no UPDATE_PUSH_WS_URL or push-updates.json)');
    return { enabled: false, stop: () => {} };
  }

  let stopped = false;
  let socket = null;
  let reconnectDelayMs = 1000;
  let lastNotifiedVersion = null;

  const connect = () => {
    if (stopped) return;

    try {
      socket = new WebSocket(wsUrl);
    } catch (e) {
      log.warn('Failed to create WebSocket', e);
      scheduleReconnect();
      return;
    }

    socket.on('open', () => {
      reconnectDelayMs = 1000;
      log.info('Connected to update push server', { wsUrl });
    });

    socket.on('message', async (data) => {
      try {
        const msg = JSON.parse(String(data));
        if (msg.type !== 'desktop-release') return;
        if (!msg.version) return;

        // Avoid spamming duplicates (especially on reconnect).
        if (lastNotifiedVersion && normalizeVersion(lastNotifiedVersion) === normalizeVersion(msg.version)) {
          return;
        }
        lastNotifiedVersion = msg.version;

        await maybeAlertAndTriggerUpdate({
          version: msg.version,
          releaseUrl: msg.releaseUrl,
          triggerUpdate
        });
      } catch (e) {
        log.warn('Invalid push message', e);
      }
    });

    socket.on('close', () => {
      log.warn('Update push socket closed');
      scheduleReconnect();
    });

    socket.on('error', (e) => {
      log.warn('Update push socket error', e);
      try {
        socket.close();
      } catch {
        // ignore
      }
    });
  };

  const scheduleReconnect = () => {
    if (stopped) return;
    setTimeout(() => {
      reconnectDelayMs = Math.min(reconnectDelayMs * 2, 30000);
      connect();
    }, reconnectDelayMs);
  };

  connect();

  return {
    enabled: true,
    stop: () => {
      stopped = true;
      try {
        socket?.close();
      } catch {
        // ignore
      }
    }
  };
}

module.exports = {
  startUpdatePushClient
};
