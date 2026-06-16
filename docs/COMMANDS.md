# Wide Web Blog Admin Panel Commands

## Expected Stack Commands

These commands assume a standard Laravel 13 + Livewire setup in the admin app.

## Initial Setup

```bash
composer install
cp .env.example .env
php artisan key:generate
php artisan storage:link
```

## Development

```bash
php artisan serve
php artisan optimize:clear
php artisan route:list
php artisan config:clear
php artisan view:clear
php artisan cache:clear
```

## Frontend Assets

```bash
npm install
npm run dev
npm run build
```

If the project uses the Laravel dev server bundle pattern, add:

```bash
composer run dev
```

## Livewire and Component Scaffolding

Examples:

```bash
php artisan make:livewire Admin/Dashboard/Index
php artisan make:livewire Admin/Posts/Index
php artisan make:livewire Admin/Posts/Edit
php artisan make:component Ui/Button
php artisan make:component Admin/PageHeader
```

## Testing

```bash
php artisan test
php artisan test --filter=Auth
php artisan test --parallel
```

## Code Quality

The exact tooling can be finalized during bootstrap, but the project should expect commands in this category:

```bash
./vendor/bin/pint
./vendor/bin/phpstan analyse
```

If Pest is adopted:

```bash
./vendor/bin/pest
```

## Useful Laravel Maintenance Commands

```bash
php artisan about
php artisan migrate:status
php artisan queue:work
php artisan schedule:list
```

Note: the admin panel is expected to be a service-API client and may not need local database migrations for domain data. Only add migration commands to day-to-day workflows if the admin app later introduces local persistence needs beyond framework/session concerns.
