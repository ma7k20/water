const express = require('express');
const qrcode = require('qrcode-terminal');
const { Client, LocalAuth } = require('whatsapp-web.js');
require('dotenv').config();

const app = express();
app.use(express.json({ limit: '1mb' }));

const PORT = process.env.PORT || 3001;
const API_KEY = process.env.WHATSAPP_WEBJS_API_KEY;

let clientReady = false;

const client = new Client({
  authStrategy: new LocalAuth({ clientId: 'water-billing' }),
  puppeteer: {
    headless: true,
    args: ['--no-sandbox', '--disable-setuid-sandbox']
  }
});

client.on('qr', (qr) => {
  console.log('Scan this QR with WhatsApp:');
  qrcode.generate(qr, { small: true });
});

client.on('ready', () => {
  clientReady = true;
  console.log('whatsapp-web.js client is ready.');
});

client.on('disconnected', (reason) => {
  clientReady = false;
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
