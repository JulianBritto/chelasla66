const express = require('express');
const http = require('http');
const WebSocket = require('ws');

const PORT = Number(process.env.PORT || 8787);
const AUTH_TOKEN = process.env.PUSH_NOTIFY_TOKEN || '';

if (!AUTH_TOKEN) {
  // This server can run without auth for local testing, but it's unsafe for public hosting.
  // In production you should set PUSH_NOTIFY_TOKEN.
  // eslint-disable-next-line no-console
  console.warn('[server] WARNING: PUSH_NOTIFY_TOKEN is not set. /notify will accept any request.');
}

const app = express();
app.use(express.json({ limit: '256kb' }));

let latest = null; // { version, releaseUrl, sha, ts }

function isAuthorized(req) {
  if (!AUTH_TOKEN) return true;
  const header = String(req.headers.authorization || '');
  const match = header.match(/^Bearer\s+(.+)$/i);
  return Boolean(match && match[1] === AUTH_TOKEN);
}

app.get('/health', (_req, res) => {
  res.json({ ok: true, latest });
});

app.post('/notify', (req, res) => {
  if (!isAuthorized(req)) {
    res.status(401).json({ ok: false, error: 'unauthorized' });
    return;
  }

  const { version, releaseUrl, sha } = req.body || {};
  if (!version) {
    res.status(400).json({ ok: false, error: 'missing version' });
    return;
  }

  latest = {
    version: String(version),
    releaseUrl: releaseUrl ? String(releaseUrl) : null,
    sha: sha ? String(sha) : null,
    ts: Date.now()
  };

  broadcast({ type: 'desktop-release', ...latest });
  res.json({ ok: true });
});

const server = http.createServer(app);
const wss = new WebSocket.Server({ server, path: '/ws' });

function broadcast(message) {
  const data = JSON.stringify(message);
  for (const client of wss.clients) {
    if (client.readyState === WebSocket.OPEN) {
      client.send(data);
    }
  }
}

wss.on('connection', (socket) => {
  // Send latest info to new clients so they don't miss updates while offline.
  if (latest) {
    try {
      socket.send(JSON.stringify({ type: 'desktop-release', ...latest }));
    } catch {
      // ignore
    }
  }

  socket.on('message', () => {
    // no-op (we don't need client messages)
  });
});

server.listen(PORT, '0.0.0.0', () => {
  // eslint-disable-next-line no-console
  console.log(`[server] listening on http://0.0.0.0:${PORT} (ws: /ws)`);
});
