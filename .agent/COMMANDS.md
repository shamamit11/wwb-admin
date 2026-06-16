# Admin Command Guidance

Agents must inspect the actual repository scripts and installed tooling before assuming commands exist.

At the moment this repository contains documentation scaffolding only, so the commands below are examples for the expected Laravel 13 + Livewire app shape and must be verified before use.

## Install Dependencies

Examples:

- `composer install`
- `npm install`

## Start Laravel

Examples:

- `php artisan serve`
- `composer run dev`

## Start Vite

Examples:

- `npm run dev`

## Run Tests

Examples:

- `php artisan test`
- `php artisan test --filter=...`

## Run Pint

Example:

- `./vendor/bin/pint`

## Run Static Analysis If Installed

Examples:

- `./vendor/bin/phpstan analyse`
- `./vendor/bin/pest`

## Clear Caches

Examples:

- `php artisan optimize:clear`
- `php artisan config:clear`
- `php artisan view:clear`
- `php artisan cache:clear`

## Build Assets

Examples:

- `npm run build`

Before running commands, verify `composer.json`, `package.json`, available binaries, and the actual application bootstrap state.
