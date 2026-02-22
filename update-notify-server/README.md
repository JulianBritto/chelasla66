# Update Notify Server (Push)

Servidor mínimo para "push" de actualizaciones.

## Qué hace

- Expone `POST /notify` (para que CI notifique que hay una nueva versión publicada).
- Expone `GET /health` (estado básico).
- Mantiene un WebSocket en `ws://<host>:<port>/ws`.
- Cuando llega `POST /notify`, guarda la última versión y la envía (push) a todos los clientes conectados.

## Variables de entorno

- `PORT` (default `8787`)
- `PUSH_NOTIFY_TOKEN` (recomendado): token que CI envía como `Authorization: Bearer <token>`.

## Correr local

```powershell
cd update-notify-server
npm install
$env:PUSH_NOTIFY_TOKEN = "un-token-largo"
npm start
```

Prueba manual:

```powershell
Invoke-RestMethod -Method Post -Uri http://localhost:8787/notify \
  -Headers @{ Authorization = "Bearer un-token-largo" } \
  -ContentType "application/json" \
  -Body '{"version":"0.1.123","releaseUrl":null,"sha":"test"}'
```

## Hosting

Debes hostearlo en un lugar accesible por los PCs (Render/Fly.io/VPS/etc).
Luego configuras la app desktop para conectarse al WebSocket.
