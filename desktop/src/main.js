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
        buttons: ['Aceptar'],
        defaultId: 0,
        cancelId: 0,
        title: 'Actualización obligatoria',
        message: 'Hay una nueva versión del sistema. La aplicación se actualizará y necesitará reiniciarse para continuar.',
        detail: 'Se descargará en segundo plano y cuando esté lista se cerrará para aplicar la actualización.'
      });
    } catch (e) {
      log.warn('Failed to show update-available dialog', e);
    }
  });

  autoUpdater.on('update-downloaded', async () => {
    try {
      await dialog.showMessageBox({
        type: 'info',
        buttons: ['Reiniciar ahora'],
        defaultId: 0,
        cancelId: 0,
        title: 'Actualización lista (obligatoria)',
        message: 'Hay una actualización lista y es obligatorio reiniciar para continuar usando el sistema.',
        detail: 'La aplicación se cerrará y se volverá a abrir con la última versión.'
      });
    } catch (e) {
      log.warn('Failed to show update-downloaded dialog', e);
    }

    // Aplique siempre la actualización, incluso si el usuario cierra el diálogo.
    autoUpdater.quitAndInstall();
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
  // Al abrir la aplicación desde cero, verifica si hay actualizaciones
  // para que el usuario sea notificado incluso sin estar conectado al servidor push.
  checkForUpdatesIfNeeded('startup');
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

    // Si el servidor de push no está disponible, ya se ejecutó un check al inicio
    // (startup), así que no es necesario hacer otro aquí.
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
