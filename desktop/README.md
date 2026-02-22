# ProyectoChelas Desktop (Windows)

Este folder agrega un **wrapper de escritorio** (Electron) para ejecutar tu proyecto Laravel como una app instalable.

## Qué hace

- Levanta Laravel localmente en `http://127.0.0.1:<puerto>` usando `php -S ... server.php`.
- Usa **SQLite local** en `%AppData%/ProyectoChelas/data/database.sqlite`.
- Corre `php artisan migrate --force` al iniciar.
- (Opcional) Auto-update con **GitHub Releases** usando `electron-updater`.

## Requisitos (modo desarrollo)

- PHP 8.1+ disponible en PATH (`php -v`)
- Composer
- Node.js (incluye npm)

## Ejecutar en tu PC (dev)

Desde la raíz del repo:

1) Instala dependencias del wrapper:

```powershell
cd desktop
npm install
```

2) Asegura dependencias Laravel:

```powershell
cd ..
composer install
npm install
npm run build
```

3) Inicia la app desktop:

```powershell
cd desktop
npm run dev
```

## Empaquetar para otro computador (release)

> Lo ideal es hacerlo desde CI (GitHub Actions). Aun así, localmente puedes generar el instalador.

1) Genera los artefactos de Laravel (incluye `vendor/` y `public/build/`):

```powershell
cd ..
composer install --no-dev --optimize-autoloader
npm ci
npm run build
```

2) Empaqueta:

```powershell
cd desktop
npm ci
npm run dist:win
```

Si quieres que el instalador quede automáticamente en tu Escritorio:

```powershell
cd desktop
npm.cmd run dist:win:desktop
```

Este comando intenta generar un **.exe portable**. Si Windows bloquea la extracción de `winCodeSign` (error de *symbolic links*), hace fallback y copia `desktop/dist/win-unpacked` al Escritorio como una carpeta `ProyectoChelas-App` y deja un lanzador en el Escritorio.

Si necesitas que electron-builder termine y genere el instalador/portable en un solo `.exe`, habilita **Developer Mode** en Windows (Permite symlinks) o ejecuta la consola como **Administrador**.

El instalador queda en `desktop/dist/`.

## Auto-update (GitHub)

El auto-update ya está configurado para el repo `JulianBritto/chelasla66`.

### Flujo recomendado

1) Pruebas local (XAMPP/navegador o `php artisan serve`).
2) Cuando validas, haces `git commit` + `git push`.
3) GitHub Actions construye y publica una nueva versión (release) automáticamente.
4) En el otro PC, la app de escritorio revisa actualizaciones al abrirse y cuando vuelves a enfocar la ventana.

> Nota: la app revisa updates **al abrirse** y cuando vuelves a enfocar la ventana (sin polling fijo). Si hay update, muestra un aviso y cuando termine de descargar te pedirá reiniciar.

### Publicación

- Puedes crear releases con tags `v*` (por ejemplo `v0.1.0`).
- Además, el workflow en `.github/workflows/desktop-release.yml` está configurado para publicar un release en cada `push` a `main/master` usando una versión incremental `0.1.<run_number>`.

## Push (sin polling)

Si quieres que la app "se entere" inmediatamente cuando CI publicó una nueva versión (sin estar consultando cada X minutos), usa el servidor push incluido en este repo:

- Carpeta: `update-notify-server/`
- WebSocket: `ws://<host>:8787/ws`

### 1) Hostea el servidor push

Puedes correrlo en un VPS/Render/Fly.io. Debe ser accesible desde los PCs.

Variables:
- `PUSH_NOTIFY_TOKEN`: token largo (secreto)

### 2) Configura GitHub Actions (Secrets)

En tu repo GitHub, agrega estos secrets:
- `PUSH_NOTIFY_URL`: URL completa al endpoint `/notify` (ej: `https://tu-dominio.com/notify`)
- `PUSH_NOTIFY_TOKEN`: el mismo token del servidor

El workflow publicará el release y luego hará POST a `PUSH_NOTIFY_URL`.

### 3) Configura la app Desktop para escuchar el push

Opción A (recomendada): crea el archivo `push-updates.json` en la carpeta `userData` de la app con este contenido:

```json
{ "wsUrl": "wss://tu-dominio.com/ws" }
```

Opción B: define la variable de entorno `UPDATE_PUSH_WS_URL` antes de abrir la app.

Cuando llegue un push, la app muestra una alerta y descarga la actualización; al terminar te pedirá reiniciar.

## Nota sobre PHP embebido

Para que el otro PC NO necesite PHP instalado, debes incluir un `php.exe` en:

- `desktop/resources/php/php.exe`

El wrapper lo detecta automáticamente en modo empaquetado.
