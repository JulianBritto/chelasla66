const path = require('path');
const { spawnSync } = require('child_process');

function runElectronBuilder(args) {
  const electronBuilderCmd = path.resolve(__dirname, '..', 'node_modules', '.bin', 'electron-builder.cmd');
  const result = spawnSync(electronBuilderCmd, args, {
    stdio: 'inherit',
    env: process.env,
    shell: true
  });

  if (result.error) {
    console.warn(`\n[build] No se pudo ejecutar electron-builder: ${result.error.message}`);
    console.warn('[build] Nota: si usas PowerShell con políticas estrictas, ejecuta el script con npm.cmd.');
  }

  // electron-builder often fails on some Windows setups when extracting winCodeSign
  // (symlink privilege). Even in that case it may still have produced dist/win-unpacked.
  if (typeof result.status === 'number' && result.status !== 0) {
    console.warn(`\n[build] electron-builder terminó con error (exit ${result.status}). Voy a intentar copiar lo que se haya generado igual.`);
  } else if (result.status == null) {
    console.warn('\n[build] electron-builder no devolvió código de salida; igual intentaré copiar lo que exista en dist/.');
  }

  return result.status ?? 0;
}

function main() {
  // Try to create the portable single-file exe first (best UX).
  // If that fails, electron-builder still usually produces dist/win-unpacked.
  runElectronBuilder(['--win', 'portable', '--x64']);

  // Copy either installer/portable exe OR fallback win-unpacked folder to Desktop
  // (and create shortcut).
  // eslint-disable-next-line global-require
  require('./copy-installer-to-desktop').main();
}

main();
