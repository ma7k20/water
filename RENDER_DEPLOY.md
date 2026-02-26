# Deploy To Render

## 1) Push to GitHub

Make sure the repository is on GitHub and `.env` is not committed.

## 2) Deploy using Blueprint

In Render dashboard:

1. `New` -> `Blueprint`
2. Connect your GitHub repo
3. Render will detect `render.yaml` and create:
   - `water-billing-app` (Web Service)
   - `water-billing-db` (PostgreSQL)

## 3) Set required environment variables in Render

In `water-billing-app` -> `Environment` add:

- `APP_URL=https://YOUR-SERVICE.onrender.com`
- `WHATSAPP_ADMIN_PHONE=9725XXXXXXX`
- `WHATSAPP_WEBJS_BASE_URL=http://...` (your whatsapp-web.js service URL)
- `WHATSAPP_WEBJS_API_KEY=...`

Optional:

- `RUN_MIGRATIONS=true` (default already true)

## 4) First login

Create an admin user in DB and set `is_admin=1`.

If you need a quick user seed, run from Render shell:

```bash
php artisan tinker
```

Then:

```php
\App\Models\User::create([
  'name' => 'Admin',
  'email' => 'admin@example.com',
  'password' => bcrypt('ChangeMe123!'),
  'is_admin' => true
]);
```

## 5) Notes

- The app runs with `php artisan serve` inside Docker for simplicity.
- For production-grade performance, you can later switch to Nginx + PHP-FPM image.
- If you run `whatsapp-web.js`, use a separate service and persistent storage for auth sessions.
