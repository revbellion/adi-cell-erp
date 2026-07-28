# Deploy Checklist — Cash Tracker (ADI CELL POS)

## Sebelum Deploy

### 1. Siapkan Server
- [ ] Domain & SSL (HTTPS) ready
- [ ] Web server (Nginx/Apache/Laragon)
- [ ] PHP 8.3+
- [ ] MySQL / MariaDB
- [ ] Composer

### 2. Copy Project ke Server
```bash
git clone https://github.com/...cash-tracker.git
cd cash-tracker
composer install --no-dev --optimize-autoloader
npm ci && npm run build
```

### 3. Setup Environment
```bash
cp .env.example .env
php artisan key:generate
```
Edit `.env`:
```
APP_ENV=production
APP_DEBUG=false
APP_URL=https://domain-anda.com
DB_PASSWORD=******
SESSION_DRIVER=database
SESSION_ENCRYPT=true
LOG_LEVEL=error
```

### 4. Database
```bash
php artisan migrate --force
php artisan storage:link
```

### 5. Optimasi
```bash
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan optimize
```

### 6. Permission (Linux)
```bash
chmod -R 775 storage bootstrap/cache
chown -R www-data:www-data storage bootstrap/cache
```

### 7. Cron Job (untuk schedule)
```bash
# Tambahkan ke crontab:
* * * * * cd /path/project && php artisan schedule:run >> /dev/null 2>&1
```

### 8. Verifikasi
- [ ] Login berhasil
- [ ] Mutasi, Pendapatan, Pengeluaran bisa dicatat
- [ ] Export Excel jalan
- [ ] Storage link berfungsi (gambar/profile)
- [ ] Error page 403/404/500 tampil (bukan stack trace)
