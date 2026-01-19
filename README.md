# Helpdesk Ticket System (Laravel + Filament)

Helpdesk Ticket System is a Laravel application built with **Filament** for managing internal support tickets using a **Kanban board** workflow.

## Features
- User authentication
- Employees management
- Tickets management
- Kanban board for ticket statuses (e.g. Open, In Progress, Closed)

---

## Tech Stack
- Laravel
- Filament Admin Panel
- (Optional) Filament Kanban plugin (if used)
- PHP / Composer
- Node.js / NPM (Vite)

---

## Requirements
- PHP 8.2+ (recommended)
- Composer
- Node.js + NPM
- MySQL/MariaDB (or PostgreSQL)
- NGINX or Apache
- Git

---

## Installation (Quick Start)

### 1) Clone the repository
```bash
git clone git@github.com:ryanboc/helpdesk-ticket-system.git
cd helpdesk-ticket-system
```

### 2) Install dependencies
```bash
composer install
npm install
```

### 3) Environment setup
```bash
cp .env.example .env
php artisan key:generate
```

Update `.env` credentials (DB, APP_URL, etc).

### 4) Permissions
```bash
sudo chown -R www-data:www-data storage bootstrap/cache
sudo chmod -R 775 storage bootstrap/cache
```

### 5) Database
```bash
php artisan migrate
```

(Optional) Seeders (only if included):
```bash
php artisan db:seed
```

### 6) Frontend assets
Development:
```bash
npm run dev
```

Production:
```bash
npm run build
```

---

## Filament Admin Panel
Default Filament admin path is usually:
- `/admin`

Create an admin user (if needed):
```bash
php artisan make:filament-user
```

---

## Tickets & Kanban Board

Tickets are managed through Filament resources/pages, with a Kanban board view for status tracking.

Typical statuses:
- Open
- In Progress
- Closed

> If your project uses a specific Kanban plugin/package, document it here (package name + link) and any config notes.

---

## Web Server Config

> Replace paths, domain, and PHP-FPM version to match your environment.

### NGINX
Create:
`/etc/nginx/sites-available/helpdesk-ticket-system`

```nginx
server {
    listen 80;
    server_name helpdesk.yourdomain.com;

    root /var/www/helpdesk-ticket-system/public;
    index index.php index.html;

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    location ~ \.php$ {
        include snippets/fastcgi-php.conf;
        fastcgi_pass unix:/run/php/php8.2-fpm.sock; # adjust version/path
        fastcgi_param SCRIPT_FILENAME $document_root$fastcgi_script_name;
        include fastcgi_params;
    }

    location ~ /\.ht {
        deny all;
    }

    client_max_body_size 20M;
}
```

Enable and reload:
```bash
sudo ln -s /etc/nginx/sites-available/helpdesk-ticket-system /etc/nginx/sites-enabled/
sudo nginx -t
sudo systemctl reload nginx
```

### Apache
Create:
`/etc/apache2/sites-available/helpdesk-ticket-system.conf`

```apache
<VirtualHost *:80>
    ServerName helpdesk.yourdomain.com
    DocumentRoot /var/www/helpdesk-ticket-system/public

    <Directory /var/www/helpdesk-ticket-system/public>
        AllowOverride All
        Require all granted
    </Directory>

    ErrorLog ${APACHE_LOG_DIR}/helpdesk-ticket-system-error.log
    CustomLog ${APACHE_LOG_DIR}/helpdesk-ticket-system-access.log combined
</VirtualHost>
```

Enable and reload:
```bash
sudo a2enmod rewrite
sudo a2ensite helpdesk-ticket-system.conf
sudo apache2ctl configtest
sudo systemctl reload apache2
```

---

## Troubleshooting

### Clear caches
```bash
php artisan optimize:clear
```

### Common permission fix
```bash
sudo chown -R www-data:www-data storage bootstrap/cache
sudo chmod -R 775 storage bootstrap/cache
```

### Vite assets not loading
Dev:
```bash
npm run dev
```

Prod:
```bash
npm run build
```

---

## License
Private / Internal project (update as needed).