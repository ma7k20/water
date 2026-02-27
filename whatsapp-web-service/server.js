const express = require('express');
const QRCode = require('qrcode');
const qrcode = require('qrcode-terminal');
const { Client, LocalAuth } = require('whatsapp-web.js');
require('dotenv').config();

const app = express();
app.use(express.json({ limit: '1mb' }));

const PORT = process.env.PORT || 3001;
const API_KEY = process.env.WHATSAPP_WEBJS_API_KEY;

let clientReady = false;
let latestQr = null;

const client = new Client({
  authStrategy: new LocalAuth({ clientId: 'water-billing' }),
  puppeteer: {
    headless: true,
    args: ['--no-sandbox', '--disable-setuid-sandbox']
  }
});

client.on('qr', (qr) => {
  latestQr = qr;
  console.log('Scan this QR with WhatsApp:');
  qrcode.generate(qr, { small: true });
});

client.on('ready', () => {
  clientReady = true;
  latestQr = null;
  console.log('whatsapp-web.js client is ready.');
});

client.on('disconnected', (reason) => {
  clientReady = false;
  latestQr = null;
  console.log('whatsapp-web.js disconnected:', reason);
});

client.initialize();

function authorized(req, res, next) {
  if (!API_KEY) {
    return res.status(500).json({ ok: false, error: 'WHATSAPP_WEBJS_API_KEY is missing' });
  }

  if (req.header('X-Api-Key') !== API_KEY) {
    return res.status(401).json({ ok: false, error: 'Unauthorized' });
  }

  next();
}

function toChatId(rawPhone) {
  const phone = String(rawPhone || '').replace(/\D/g, '');
  return phone ? `${phone}@c.us` : '';
}

app.get('/health', (req, res) => {
  res.json({ ok: true, ready: clientReady });
});

app.get('/', (req, res) => {
  res.json({
    ok: true,
    service: 'whatsapp-web-service',
    ready: clientReady,
    qr_url: '/qr'
  });
});

app.get('/qr', async (req, res) => {
  try {
    if (clientReady) {
      return res.status(200).send('<h3>WhatsApp is already connected.</h3>');
    }

    if (!latestQr) {
      return res.status(404).send('<h3>QR is not available yet. Refresh after a few seconds.</h3>');
    }

    const svg = await QRCode.toString(latestQr, {
      type: 'svg',
      width: 320,
      margin: 2,
      errorCorrectionLevel: 'M'
    });

    res.setHeader('Content-Type', 'image/svg+xml; charset=utf-8');
    return res.send(svg);
  } catch (error) {
    return res.status(500).send(`Failed to render QR: ${error.message || 'Unknown error'}`);
  }
});

app.post('/api/send-text', authorized, async (req, res) => {
  try {
    const { to, message } = req.body || {};
    const chatId = toChatId(to);

    if (!chatId || !message) {
      return res.status(422).json({ ok: false, error: 'to and message are required' });
    }

    if (!clientReady) {
      return res.status(503).json({ ok: false, error: 'WhatsApp client is not ready yet' });
    }

    const result = await client.sendMessage(chatId, String(message));
    return res.json({ ok: true, id: result.id._serialized });
  } catch (error) {
    return res.status(500).json({
      ok: false,
      error: error && error.message ? error.message : 'Unknown error'
    });
  }
});

app.listen(PORT, () => {
  console.log(`whatsapp-web.js API listening on http://127.0.0.1:${PORT}`);
});
