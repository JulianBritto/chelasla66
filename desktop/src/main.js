const { app, BrowserWindow, dialog } = require('electron');
const log = require('electron-log');
const path = require('path');
const { autoUpdater } = require('electron-updater');

const { startLaravel, stopLaravel } = require('./runtime/laravel');
const { startUpdatePushClient } = require('./updatePush');

let mainWindow;
let laravelRuntime;
let lastUpdateCheckAt = 0;
let updateAvailableNotified = false;
let pushClient;

function createWindow() {
  mainWindow = new BrowserWindow({
    width: 1280,
    height: 800,
    show: false,
    webPreferences: {
      contextIsolation: true,
      sandbox: true
    }
  });

  mainWindow.loadFile(path.join(__dirname, 'loading.html'));

  mainWindow.once('ready-to-show', () => {
    mainWindow.show();
  });
}

function configureAutoUpdate() {
  autoUpdater.logger = log;
  autoUpdater.autoDownload = true;

  autoUpdater.on('error', (error) => {
    log.error('AutoUpdater error', error);
  });

  autoUpdater.on('update-available', async () => {
    if (updateAvailableNotified) return;
    updateAvailableNotified = true;

    try {
      await dialog.showMessageBox({
        type: 'info',
        buttons: ['OK'],
        defaultId: 0,
        title: 'Actualización disponible',
        message: 'Hay una nueva versión del sistema. Se descargará en segundo plano y te pedirá reiniciar cuando esté lista.'
      });
    } catch (e) {
      log.warn('Failed to show update-available dialog', e);
    }
  });

  autoUpdater.on('update-downloaded', async () => {
    const result = await dialog.showMessageBox({
      type: 'info',
      buttons: ['Reiniciar ahora', 'Después'],
      defaultId: 0,
      cancelId: 1,
      title: 'Actualización lista',
      message: 'Hay una actualización lista. ¿Quieres reiniciar para aplicarla?'
    });

    if (result.response === 0) {
      autoUpdater.quitAndInstall();
    }
  });
}

function checkForUpdatesIfNeeded(reason) {
  if (!app.isPackaged) return;

  const now = Date.now();
  // Throttle checks to avoid spamming GitHub/creating repeated dialogs.
  if (now - lastUpdateCheckAt < 60 * 1000) return;
  lastUpdateCheckAt = now;

  log.info('Checking for updates', { reason });

  try {
    autoUpdater.checkForUpdates();
  } catch (e) {
    log.warn('AutoUpdater check failed', e);
  }
}

app.on('window-all-closed', () => {
  if (process.platform !== 'darwin') app.quit();
});

app.on('before-quit', async () => {
  try {
    pushClient?.stop?.();
  } catch (e) {
    log.warn('Error stopping push client', e);
  }

  try {
    await stopLaravel(laravelRuntime);
  } catch (e) {
    log.warn('Error stopping Laravel runtime', e);
  }
});

app.whenReady().then(async () => {
  configureAutoUpdate();
  createWindow();

  log.info('Electron userData path', app.getPath('userData'));

  try {
    laravelRuntime = await startLaravel({
      repoRoot: path.resolve(__dirname, '..', '..')
    });

    // Always require login after reopening the desktop app.
    // Clearing cookies/storage ensures Laravel session cookies are not persisted.
    try {
      await mainWindow.webContents.session.clearStorageData({
        storages: [
          'cookies',
          'localstorage',
          'sessionstorage',
          'indexdb',
          'serviceworkers',
          'cachestorage'
        ]
      });
      log.info('Cleared browser storage for fresh login');
    } catch (e) {
      log.warn('Failed to clear browser storage', e);
    }

    await mainWindow.loadURL(laravelRuntime.url);

    // Push-driven updates (recommended). If not configured, fallback to a one-time check.
    pushClient = startUpdatePushClient({
      triggerUpdate: () => checkForUpdatesIfNeeded('push-notify')
    });

    if (!pushClient?.enabled) {
      checkForUpdatesIfNeeded('startup-fallback');
    }
  } catch (error) {
    log.error('Failed to start app', error);
    await dialog.showMessageBox({
      type: 'error',
      title: 'Error al iniciar',
      message: 'No se pudo iniciar el servidor local de la aplicación.',
      detail: String(error?.stack || error)
    });
    app.quit();
  }
});
