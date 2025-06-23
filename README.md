# Eventory – Inventory Management for Festivals & Events

[![Laravel](https://img.shields.io/badge/Laravel-12.x-red.svg)](https://laravel.com/)
[![Filament](https://img.shields.io/badge/Filament-Admin%20Panel-blueviolet)](https://filamentphp.com/)
![PHP](https://img.shields.io/badge/PHP->=8.4-blue)
![MySQL](https://img.shields.io/badge/Database-MySQL-brightgreen)

**Eventory** is a Laravel-based application powered by [Filament Admin Panel](https://filamentphp.com/) for tracking food and beverage inventory at festivals and events. The system is role-based, includes a seeded user setup, and supports full inventory management with an intuitive UI.

---

## Features

- Laravel 10+ with Filament 3.x
- MySQL database support
- Role-based access using Spatie Permissions
- Inventory tracking for drinks and food
- Admin dashboard with CRUD operations
- Laravel Mailer integration for notifications
- Fully responsive and mobile-friendly Filament interface

---

## Requirements

- PHP >= 8.1
- Composer
- Node.js & NPM
- MySQL
- Laravel 10+

---

## Installation

```bash
git clone https://github.com/Baziel09/eventory.git
cd eventory

composer install
npm install && npm run build

cp .env.example .env
php artisan key:generate
```

### Configure `.env`

Update your `.env` file with database and mail credentials:

```env
DB_DATABASE=eventory
DB_USERNAME=root
DB_PASSWORD=yourpassword

MAIL_MAILER=smtp
MAIL_HOST=smtp.example.com
MAIL_PORT=587
MAIL_USERNAME=your@email.com
MAIL_PASSWORD=yourpassword
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS=no-reply@eventory.com
MAIL_FROM_NAME="Eventory"
```

### Run Migrations & Seeders

```bash
php artisan migrate --seed
```

---

## 👥 Seeded Accounts

| Role               | Email                      | Password  |
|--------------------|----------------------------|-----------|
| Admin              | `admin@example.com`        | `password` |
| Voorraadbeheerder  | `voorraad@example.com`     | `password` |
| Vrijwilliger       | `vrijwilliger@example.com` | `password` |

---

## 🔐 Roles & Permissions

- **Admin**: Full access to all features including user and role management
- **Voorraadbeheerder**: Can manage stock and view reports
- **Vrijwilliger**: Limited access (e.g., view-only or record consumptions)

### Access the Admin Panel

```text
http://localhost:8000
```

Use one of the seeded accounts to log in.

---

## Running Tests

```bash
php artisan test
```

---

## Project Structure Highlights

- `app/Filament/Resources/` – Filament resource definitions
- `database/seeders/` – Contains default roles and users
- `app/Models/User.php` – Uses `HasRoles` trait from Spatie
- `app/Providers/Filament/AdminPanelProvider` - Filament settings
- `config/permission.php` – Role & permission settings

---

Made with ❤️ using Laravel and Filament.
