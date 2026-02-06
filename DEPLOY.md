# 🚀 Hướng Dẫn Deploy ZDream trên cPanel Shared Hosting

## Mục Lục
- [1. Chuẩn Bị](#1-chuẩn-bị)
- [2. Clone Repository](#2-clone-repository)
- [3. Cài Đặt Dependencies](#3-cài-đặt-dependencies)
- [4. Cấu Hình Environment](#4-cấu-hình-environment)
- [5. Bootstrap Laravel](#5-bootstrap-laravel)
- [6. Cấu Hình Webroot](#6-cấu-hình-webroot)
- [7. Cấu Hình Cron Jobs](#7-cấu-hình-cron-jobs)
- [8. Deploy Lần Sau](#8-deploy-lần-sau)
- [9. Troubleshooting](#9-troubleshooting)

---

## 1. Chuẩn Bị

### Yêu cầu hệ thống
- PHP >= 8.2
- MySQL >= 8.0
- Composer
- Git
- Node.js >= 18 & npm *(tùy chọn - chỉ cần nếu build assets trên server)*

### Kiểm tra phiên bản PHP
```bash
which php
php -v
```

### Cấu trúc thư mục khuyến nghị
```
/home/zdream/
├── repositories/
│   └── zdream2/          # Source code Laravel
├── public_html/          # Webroot (nếu cần)
└── logs/                 # Log files
    ├── schedule.log
    └── queue.log
```

---

## 2. Clone Repository

### Cách 1: Qua cPanel Git Version Control (Khuyên dùng)
1. Đăng nhập cPanel
2. Vào **Git™ Version Control**
3. Click **Create**
4. Điền thông tin:
   - **Clone URL**: `https://github.com/dangtuandat123/zdream2.git`
   - **Repository Path**: `/home/zdream/repositories/zdream2`
   - **Repository Name**: `zdream2`
5. Click **Create**

### Cách 2: Qua Terminal
```bash
mkdir -p /home/zdream/repositories
cd /home/zdream/repositories
git clone https://github.com/dangtuandat123/zdream2.git
cd zdream2
```

---

## 3. Cài Đặt Dependencies

### PHP Dependencies
```bash
cd /home/zdream/repositories/zdream2
composer install --no-dev --optimize-autoloader
```

### Frontend Assets (Tailwind CSS + Alpine.js)

Project dùng **Vite** để build Tailwind CSS và Alpine.js.

#### Cách A: Build trên máy local (Khuyên dùng cho shared hosting ⭐)
```bash
# Trên máy local của bạn
npm install
npm run build

# Commit thư mục build
git add public/build/
git commit -m "Build production assets"
git push
```
→ **Không cần cài Node.js trên server!** Thư mục `public/build/` đã có sẵn.

#### Cách B: Build trên server (Nếu hosting có Node.js)
```bash
npm ci
npm run build
```

> ⚠️ **Lưu ý**: Nếu thư mục `public/build/` đã được commit, bạn có thể **bỏ qua bước npm hoàn toàn**.

### Phân quyền thư mục
```bash
chmod -R 755 storage bootstrap/cache
chmod -R 775 storage/logs storage/framework
```

---

## 4. Cấu Hình Environment

### Tạo file .env
```bash
cp .env.example .env
nano .env
```

### Nội dung .env cho Production
```env
APP_NAME="ZDream AI"
APP_ENV=production
APP_KEY=
APP_DEBUG=false
APP_TIMEZONE=Asia/Ho_Chi_Minh
APP_URL=https://zdream.vn

# Logging
LOG_CHANNEL=daily
LOG_LEVEL=error

# Database
DB_CONNECTION=mysql
DB_HOST=localhost
DB_PORT=3306
DB_DATABASE=zdream_db
DB_USERNAME=zdream_user
DB_PASSWORD=YOUR_STRONG_PASSWORD_HERE

# Cache & Session
CACHE_DRIVER=file
SESSION_DRIVER=file
SESSION_LIFETIME=5256000
SESSION_SECURE_COOKIE=true
SESSION_SAME_SITE_COOKIE=lax

# Queue
QUEUE_CONNECTION=database

# API Keys (Điền đầy đủ)
BFL_API_KEY=your_bfl_key
BFL_API_URL=https://api.bfl.ml

MINIO_ENDPOINT=your_minio_endpoint
MINIO_ACCESS_KEY=your_access_key
MINIO_SECRET_KEY=your_secret_key
MINIO_BUCKET=zdream

INTERNAL_API_SECRET=your_internal_secret

VIETQR_CLIENT_ID=your_client_id
VIETQR_API_KEY=your_api_key
```

> 🔐 **Bảo mật**: Sử dụng mật khẩu mạnh (16+ ký tự, chữ hoa/thường/số/ký tự đặc biệt)

---

## 5. Bootstrap Laravel

### Chạy các lệnh khởi tạo
```bash
cd /home/zdream/repositories/zdream2

# Generate app key
php artisan key:generate --force

# Chạy migrations
php artisan migrate --force

# Tạo symbolic link cho storage
php artisan storage:link

# Cache config cho production
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan event:cache

# Hoặc dùng lệnh optimize (bao gồm tất cả)
php artisan optimize
```

---

## 6. Cấu Hình Webroot

### Cách A: Đổi Document Root (Khuyên dùng ⭐)

1. Đăng nhập cPanel
2. Vào **Domains** hoặc **Subdomains**
3. Tìm domain `zdream.vn`
4. Đổi **Document Root** thành:
   ```
   /home/zdream/repositories/zdream2/public
   ```
5. Lưu lại

✅ **Ưu điểm**: Đơn giản, bảo mật cao, không cần rsync.

---

### Cách B: Sử dụng public_html (Nếu không đổi được Document Root)

#### Bước 1: Sync thư mục public
```bash
rsync -av --delete \
    --exclude='index.php' \
    --exclude='.htaccess' \
    /home/zdream/repositories/zdream2/public/ \
    /home/zdream/public_html/
```

#### Bước 2: Tạo index.php custom
Tạo file `/home/zdream/public_html/index.php`:
```php
<?php

use Illuminate\Http\Request;

define('LARAVEL_START', microtime(true));
define('APP_BASE', '/home/zdream/repositories/zdream2');

// Maintenance mode
if (file_exists($maintenance = APP_BASE.'/storage/framework/maintenance.php')) {
    require $maintenance;
}

// Load autoloader
require APP_BASE.'/vendor/autoload.php';

// Bootstrap Laravel
$app = require_once APP_BASE.'/bootstrap/app.php';

// Handle request
$app->handleRequest(Request::capture());
```

#### Bước 3: Tạo .htaccess
Tạo file `/home/zdream/public_html/.htaccess`:
```apache
<IfModule mod_rewrite.c>
    <IfModule mod_negotiation.c>
        Options -MultiViews -Indexes
    </IfModule>

    RewriteEngine On

    # Handle Authorization Header
    RewriteCond %{HTTP:Authorization} .
    RewriteRule .* - [E=HTTP_AUTHORIZATION:%{HTTP:Authorization}]

    # Redirect Trailing Slashes
    RewriteCond %{REQUEST_FILENAME} !-d
    RewriteCond %{REQUEST_URI} (.+)/$
    RewriteRule ^ %1 [L,R=301]

    # Send Requests To Front Controller
    RewriteCond %{REQUEST_FILENAME} !-d
    RewriteCond %{REQUEST_FILENAME} !-f
    RewriteRule ^ index.php [L]
</IfModule>

# Security Headers
<IfModule mod_headers.c>
    Header always set X-Content-Type-Options "nosniff"
    Header always set X-Frame-Options "SAMEORIGIN"
    Header always set X-XSS-Protection "1; mode=block"
</IfModule>

# Block sensitive files
<FilesMatch "^\.">
    Order allow,deny
    Deny from all
</FilesMatch>
```

---

## 7. Cấu Hình Cron Jobs

### Truy cập Cron Jobs trong cPanel
1. Đăng nhập cPanel
2. Vào **Cron Jobs**
3. Thêm các cron sau:

### Scheduler (Bắt buộc)
```
* * * * * /usr/local/bin/php /home/zdream/repositories/zdream2/artisan schedule:run >> /home/zdream/logs/schedule.log 2>&1
```

### Queue Worker
```
* * * * * /usr/local/bin/php /home/zdream/repositories/zdream2/artisan queue:work database --stop-when-empty --max-time=55 --tries=3 --timeout=300 >> /home/zdream/logs/queue.log 2>&1
```

### Tạo thư mục logs
```bash
mkdir -p /home/zdream/logs
touch /home/zdream/logs/schedule.log
touch /home/zdream/logs/queue.log
```

---

## 8. Deploy Lần Sau

### Script tự động deploy
Tạo file `/home/zdream/deploy.sh`:
```bash
#!/bin/bash
set -e

echo "🚀 Starting deployment at $(date)"

cd /home/zdream/repositories/zdream2

# Pull latest code
echo "📥 Pulling latest code..."
git pull origin main

# Install dependencies
echo "📦 Installing PHP dependencies..."
composer install --no-dev --optimize-autoloader --no-interaction

# Build assets (nếu có npm)
if command -v npm &> /dev/null; then
    echo "🔨 Building assets..."
    npm ci --production
    npm run build
fi

# Run migrations
echo "🗄️ Running migrations..."
php artisan migrate --force

# Cache everything
echo "⚡ Optimizing..."
php artisan optimize

# Restart queue workers
echo "🔄 Restarting queue workers..."
php artisan queue:restart

# Sync public (nếu dùng Cách B)
# echo "📂 Syncing public folder..."
# rsync -av --delete --exclude='index.php' --exclude='.htaccess' public/ /home/zdream/public_html/

echo "✅ Deployment completed at $(date)"
```

### Phân quyền và chạy
```bash
chmod +x /home/zdream/deploy.sh
/home/zdream/deploy.sh
```

### Quy trình deploy thủ công
```bash
cd /home/zdream/repositories/zdream2
git pull origin main
composer install --no-dev --optimize-autoloader
npm ci --production && npm run build  # Nếu có npm
php artisan migrate --force
php artisan optimize
php artisan queue:restart
```

---

## 9. Troubleshooting

### Lỗi 500 Internal Server Error
```bash
# Kiểm tra log
tail -f /home/zdream/repositories/zdream2/storage/logs/laravel.log

# Kiểm tra permissions
chmod -R 755 storage bootstrap/cache

# Clear cache
php artisan cache:clear
php artisan config:clear
```

### Lỗi Class not found
```bash
composer dump-autoload --optimize
```

### Lỗi Session/CSRF
```bash
# Kiểm tra .env
SESSION_DRIVER=file
SESSION_SECURE_COOKIE=true  # Nếu dùng HTTPS

# Clear session
php artisan session:flush
```

### Lỗi Queue không chạy
```bash
# Kiểm tra cron
crontab -l

# Chạy thủ công để test
php artisan queue:work --once

# Kiểm tra log
tail -f /home/zdream/logs/queue.log
```

### Lỗi Storage link
```bash
# Xóa link cũ và tạo lại
rm public/storage
php artisan storage:link
```

---

## 📋 Checklist Deploy

- [ ] Clone repository
- [ ] `composer install --no-dev --optimize-autoloader`
- [ ] `npm ci && npm run build`
- [ ] Tạo `.env` với thông tin production
- [ ] `php artisan key:generate --force`
- [ ] `php artisan migrate --force`
- [ ] `php artisan storage:link`
- [ ] `php artisan optimize`
- [ ] Cấu hình Document Root hoặc index.php custom
- [ ] Thêm Cron jobs (scheduler + queue)
- [ ] Test website hoạt động
- [ ] Đổi mật khẩu DB (nếu cần)

---

## 🔐 Lưu Ý Bảo Mật

1. **Không bao giờ** commit file `.env` lên Git
2. Sử dụng **mật khẩu mạnh** cho database
3. Đặt `APP_DEBUG=false` trong production
4. Cấu hình **HTTPS** cho domain
5. Thường xuyên cập nhật dependencies:
   ```bash
   composer update --no-dev
   npm update
   ```

---

**Tác giả**: ZDream Team  
**Cập nhật**: Tháng 2/2026
