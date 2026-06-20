# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## Project Overview

**finledger** is a multi-country fintech backend API for managing invoices, receipts, and tax calculations. Entrepreneurs register in the system, create invoices, and upload receipts. The system handles different tax systems and payment providers per country.

**Current Tech Stack:**
- Laravel 12, PHP 8.5
- MySQL 8.0 (via Docker Compose / Laravel Sail)
- Redis (Queue driver)
- Laravel Sanctum (API authentication)
- Laravel daily log driver with custom channels

## Quick Start Commands

### Development Setup
```bash
composer run setup
```
Installs dependencies, creates .env, generates app key, runs migrations, and builds frontend assets.

### Running the Application
```bash
composer run dev
```
Starts Laravel server, queue listener, logs, and Vite dev server concurrently.

### Running Tests
```bash
composer run test
```
Clears config cache and runs PHPUnit tests in tests/Unit/ and tests/Feature/.

```bash
php artisan test tests/Feature/ExampleTest.php    # Single test file
php artisan test --filter=TestName                # Single test method
```

## Architecture & Design Patterns

### Core Principles
- **Interfaces First**: Every service must have an interface contract
- **Adapter Pattern**: Used for tax calculators and payment providers per country
- **Repository Pattern**: Data access layer abstraction
- **Service Layer**: Contains business logic; controllers remain thin
- **Code Style**: PSR-12 compliance

### Directory Structure (app/)

```
app/
  Adapters/
    Tax/
      Contracts/
        TaxCalculator.php          # Interface for all tax adapters
      TurkeyTaxAdapter.php         # KDV: 1%, 10%, 20%
      UKTaxAdapter.php             # VAT: 20%
      USTaxAdapter.php             # Sales Tax: 8.5%
      ...other country adapters
    Payment/
      Contracts/
        PaymentProcessor.php
  Services/                         # Business logic
    Contracts/                      # Service interfaces
    InvoiceService.php
    TaxCalculationService.php
    ReceiptProcessingService.php
  Repositories/                     # Data access layer
    Contracts/
      InvoiceRepository.php
      ReceiptRepository.php
    EloquentInvoiceRepository.php
    EloquentReceiptRepository.php
  Jobs/                             # Queue jobs for async processing
    ProcessReceiptJob.php
  Http/
    Controllers/                    # Thin controllers
    Middleware/                     # Auth, country resolver
    Requests/                       # Form request validation
  Models/
    User.php
    Company.php
    ...other domain models
  Providers/
    AppServiceProvider.php
```

### Supported Countries & Tax Systems

Currently supported countries in the domain:
- **TR** (Turkey): TurkeyTaxAdapter - KDV: 1%, 10%, 20%
- **US** (USA): USTaxAdapter - Sales Tax: 8.5%
- **UK** (UK): UKTaxAdapter - VAT: 20%
- **EU** (EU): EUTaxAdapter
- **AE** (UAE): AETaxAdapter

Each country has a dedicated tax adapter implementing TaxCalculator interface.

### Key Design Patterns in Use

**Tax Calculation (Adapter Pattern):**
All tax calculators implement the TaxCalculator interface. Services resolve the correct adapter based on the company's country field.

**Data Access (Repository Pattern):**
Controllers and Services depend on repository interfaces, not Eloquent models directly. This allows swapping implementations and testing.

**Async Processing (Queue Jobs):**
- ProcessReceiptJob handles receipt uploads asynchronously via Redis queue
- Triggered when receipt is uploaded
- Updates invoice state and logs audit trail

## API Structure

All API endpoints use /api/ prefix with auth:sanctum middleware for protected routes.

**Example flow:**
1. Entrepreneur registers/logs in (Laravel Sanctum token)
2. Creates invoice via POST /api/invoices
3. Uploads receipt via POST /api/receipts
4. Backend processes receipt async via ProcessReceiptJob
5. Tax is calculated based on company's country using appropriate adapter

## Logging & Auditing

Three logging channels configured:

- **daily**: General application logs (daily rotation)
- **audit**: Business events (invoice creation, payments, critical state changes)
- **queue**: Job lifecycle events

Usage example:
```php
Log::channel('audit')->info('Invoice created', ['invoice_id' => $id, 'company_id' => $companyId]);
Log::channel('queue')->debug('Processing receipt', ['job' => 'ProcessReceiptJob', 'receipt_id' => $id]);
```

## Development Guidelines

### Creating a New Service

1. Create interface in app/Services/Contracts/MyServiceInterface.php
2. Implement in app/Services/MyService.php
3. Bind in AppServiceProvider->register()
4. Inject via constructor in Controllers/Jobs

### Creating a Tax Adapter for a New Country

1. Create class app/Adapters/Tax/CountryNameTaxAdapter.php
2. Implement TaxCalculator interface
3. Define tax rates for categories
4. Register in service container or factory

The adapter receives an amount and tax category, returns calculated tax amount.

### Creating Queue Jobs

1. Create job: `php artisan make:job ProcessReceiptJob`
2. Implement handle logic
3. Dispatch from service: `ProcessReceiptJob::dispatch($receipt)`
4. Add logging to track job lifecycle

Jobs are essential for long-running operations that shouldn't block HTTP requests.

### Creating a Repository

1. Create interface app/Repositories/Contracts/EntityRepository.php
2. Create Eloquent implementation app/Repositories/EloquentEntityRepository.php
3. Implement all interface methods
4. Bind in AppServiceProvider

Repositories encapsulate all Eloquent queries and database logic.

## Database

- **Driver**: MySQL 8.0 (via Docker)
- **Migrations**: database/migrations/
- **Seeders**: database/seeders/
- **Factories**: database/factories/ (for testing)

Run migrations:
```bash
php artisan migrate
php artisan migrate:rollback
php artisan migrate:refresh
```

## Docker Development

Start all services (MySQL, Redis, etc.):
```bash
./vendor/bin/sail up
./vendor/bin/sail down
./vendor/bin/sail artisan migrate
./vendor/bin/sail npm run build
```

## Common Commands

```bash
php artisan make:migration create_table_name          # Create migration
php artisan make:model ModelName -m                   # Model with migration
php artisan make:job JobName                          # Queue job
php artisan make:request FormRequestName              # Form request
php artisan tinker                                    # Interactive shell
php artisan queue:listen                              # Listen for jobs
php artisan config:clear && php artisan cache:clear  # Clear caches
```

## Testing

- Unit tests in tests/Unit/
- Feature tests in tests/Feature/
- Use TestCase base class for setup/teardown

```bash
php artisan test tests/Feature --filter=InvoiceTest
php artisan test --parallel                           # Run tests in parallel
```

## Code Review Checklist

Before committing:
1. Verify interfaces are defined for new services
2. Confirm business logic is in Service layer, not Controller
3. Check Repository pattern is used for data access
4. Ensure appropriate logging (audit channel for business events)
5. Validate tax calculation uses correct adapter for country
6. PSR-12 code style compliance
7. Tests pass with composer run test

## Environment Variables

Key .env variables:
```
APP_NAME=FinLedger
APP_ENV=local
APP_DEBUG=true
APP_URL=http://localhost

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=finledger
DB_USERNAME=root
DB_PASSWORD=

QUEUE_CONNECTION=redis
REDIS_HOST=127.0.0.1
REDIS_PORT=6379
```

## Important Notes

- Always inject dependencies via constructor (type hints)
- Use Eloquent models but access data through repositories
- Log business-critical events to 'audit' channel for compliance
- Tax calculations must use appropriate country adapter based on company.country field
- Queue jobs should be used for long-running operations (receipt processing)
- API authentication uses Laravel Sanctum (token-based)
- Controllers should be thin; delegate complex logic to services
- Company model stores country code (TR, US, UK, EU, AE) for multi-country support
