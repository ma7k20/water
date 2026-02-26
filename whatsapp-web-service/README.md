# WhatsApp Web Service (whatsapp-web.js)

This service exposes a local HTTP API used by Laravel to send WhatsApp messages.

## 1) Install

```bash
cd whatsapp-web-service
npm install
copy .env.example .env
```

Set `WHATSAPP_WEBJS_API_KEY` in `whatsapp-web-service/.env`.

## 2) Run

```bash
npm start
```

On first run, scan the QR code shown in terminal from WhatsApp on your phone.

## 3) Laravel .env

Set:

```env
WHATSAPP_PROVIDER=webjs
WHATSAPP_WEBJS_BASE_URL=http://127.0.0.1:3001
WHATSAPP_WEBJS_API_KEY=the-same-key-from-node-service
WHATSAPP_ADMIN_PHONE=9725XXXXXXXX
```

Then clear config cache:

```bash
php artisan config:clear
php artisan cache:clear
```

## Notes

- Keep this service running while sending invoices.
- `whatsapp-web.js` depends on a logged-in WhatsApp Web session.
