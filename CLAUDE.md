# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## Project Overview

A web-based ERP system ("Ultimate POS v6") built with Laravel 9. It handles point-of-sale, inventory, purchasing, sales, accounting, and loan/lending management. Spanish is used in commit messages and some UI text.

## Local Environment

The project runs locally via Docker (Laradock), not a native PHP install. Containers include `laravel9-workspace-1`, `laravel9-php-fpm-1`, `laravel9-nginx-1`, `laravel9-mysql-1`, `laravel9-redis-1`, `laravel9-phpmyadmin-1`. Run `artisan`/`composer`/`npm` commands inside the workspace container, not on the host:

```bash
docker exec -w /var/www laravel9-workspace-1 php artisan migrate
docker exec -w /var/www laravel9-workspace-1 php artisan test
docker exec -w /var/www laravel9-workspace-1 composer install
```

The host's PHP (if any) may be a different, incompatible version — always prefer the containerized `php` for anything artisan-related.

## Common Commands

```bash
# Install dependencies
composer install
npm install

# Setup
php artisan key:generate
php artisan migrate
php artisan storage:link

# Development
php artisan serve
php artisan queue:work

# Cache management
php artisan cache:clear
php artisan config:cache
php artisan view:clear

# Testing (PHPUnit)
php artisan test
php artisan test --filter=TestClassName
./vendor/bin/phpunit --filter=TestName
```

## Architecture

### Module System

The project uses `nwidart/laravel-modules`. Feature modules live under `/Modules/` (15 active modules including Accounting, Crm, Manufacturing, Repair, Woocommerce, etc.). Each module has its own Controllers, Models, Routes, and Views following the same Laravel conventions.

### Core Utilities (`/app/Utils/`)

Business logic is concentrated in utility/service classes rather than controllers:
- **TransactionUtil** — Core engine for all sales and purchase transactions
- **ProductUtil** — Product operations, stock management, variation handling
- **BusinessUtil** — Business-level settings and multi-location logic
- **ContactUtil** — Customer/supplier operations
- **AccountTransactionUtil** — Double-entry accounting transactions
- **LoanUtil** — Loan calculations and payment schedules
- **TaxUtil** — Tax rate calculations

Controllers are thin and delegate to these utilities. When modifying transaction or stock behavior, the Utils are the primary files to touch.

### Transaction Model

All sales and purchases share a single `transactions` table and `Transaction` model, differentiated by `type` column (`sell`, `purchase`, `sell_return`, `purchase_return`, etc.). `TransactionSellLine` and `TransactionPurchaseLine` models store line items.

### Multi-Currency

Exchange rates are stored per transaction. Precision is 4 decimal places for exchange rates (recent change from 2). Currency formatting uses the `@currency_format` Blade directive defined in `AppServiceProvider`.

### Blade Directives (defined in AppServiceProvider)

Custom directives used throughout views:
- `@num_format` / `@format_quantity` — Number formatting
- `@currency_format` — Currency with exchange rate
- `@format_date` / `@format_datetime` / `@format_time` — Date formatting
- `@transaction_status` / `@payment_status` — Colored status badges
- `@show_tooltip` — Help tooltip

### Frontend

- Bootstrap 3 + jQuery (no build step required for most changes)
- Shared JS utilities in `public/js/functions.js`
- DataTables (`yajra/laravel-datatables-oracle`) for all list views
- PDF generation via `barryvdh/laravel-dompdf`
- Real-time notifications via Pusher

### Permissions

Uses `spatie/laravel-permission`. Controllers check permissions with `auth()->user()->can('permission.name')`. Role and permission names follow dot notation (e.g., `purchase.create`, `sell.view`).

### Key Route Middleware

- `SetSessionData` — Loads business/location data into session
- `AdminSidebarMenu` — Builds navigation
- `CheckUserLogin` — Validates active session state
- `language` / `timezone` — Per-user localization

## Current Development Focus

Based on recent commits, active work is on:
- **Multi-currency / exchange rate handling** in purchases (`PurchaseController`, `PurchaseOrderController`)
- **Mixed payment methods** (multiple payment types in a single transaction)
- **Purchase invoice printing** (`resources/views/purchase/partials/print_invoice.blade.php` — new file)
