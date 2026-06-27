# VMS Backend — Visitor Management System

Laravel 11 + Vue 3 + Inertia.js + Shadcn-style UI admin dashboard.

## Requirements
- PHP 8.2+
- Composer
- Node.js 20+
- MySQL 8.0+

## Setup

```bash
# 1. Install PHP dependencies
composer install

# 2. Install JS dependencies
npm install

# 3. Copy and configure environment
cp .env.example .env
php artisan key:generate

# 4. Configure your database in .env, then:
php artisan migrate --seed

# 5. Create storage symlink
php artisan storage:link

# 6. Build frontend assets
npm run build

# 7. Start development server
php artisan serve
```

## Default Login
- **Email:** `admin@vms.local`
- **Password:** `password`
- **2FA:** You will be prompted to set up 2FA on first login.

## Roles
| Role | Access |
|------|--------|
| `super_admin` | Full access to all features |
| `building_manager` | Manage users, units, visits, reports for their building |
| `tenant` | Pre-register visitors, view visit history |
| `security_officer` | Mobile app only — check in/out visitors |

## Features

### Admin Dashboard
- **2FA** — TOTP-based 2FA enforced for all dashboard users
- **User Management** — Create users with roles and assign to buildings
- **Building & Unit Management** — Multi-building support, assign tenants to units
- **Working Hours** — Configure shifts and working hours per officer
- **Visitor Types** — Configurable visitor categories with colors and escort flags
- **Shifts** — Schedule security officer shifts with handover tracking
- **Reports** — Activity log, visitor activity, tenant activity reports

### Tenant Portal
- Pre-register expected visitors (notifies security officers)
- View and cancel upcoming visits
- Full visit history

### REST API (for mobile)
Base URL: `/api/v1`

| Method | Endpoint | Description |
|--------|----------|-------------|
| POST | `/auth/login` | Login (supports 2FA) |
| POST | `/auth/logout` | Logout |
| GET | `/auth/me` | Current user & active shift |
| GET | `/visits/today` | Today's expected + checked-in visitors |
| POST | `/visits/check-in` | Check in a visitor (expected or walk-in) |
| PUT | `/visits/{id}/check-out` | Check out a visitor |
| GET | `/lookup/national-id?national_id=` | Look up visitor by ID |
| GET | `/lookup/plate?plate_number=` | Look up visitor by vehicle plate |
| GET | `/units` | List units for officer's building |
| GET | `/visitor-types` | List visitor types |
| POST | `/shifts/start` | Start active shift |
| POST | `/shifts/end` | End active shift |
