# Task: Bootstrap Laravel 13 Application With Livewire And Shadcn-Inspired Blade Foundation

Status: Completed

## Goal

Bootstrap the actual Laravel 13 admin application in this repository and add the initial Livewire + Tailwind + Blade-native shadcn-inspired UI foundation.

## Background

The repository previously contained only planning docs, task plans, and the shared `.agent` environment. It needed a real Laravel application shell before any implementation phases could proceed.

## Required Context

- `AGENTS.md`
- `.agent/INDEX.md`
- `.agent/ARCHITECTURE.md`
- `.agent/API-CONTRACT.md`
- `.agent/COMPONENT-SYSTEM.md`
- `.agent/FOLDER-STRUCTURE.md`
- `.agent/COMMANDS.md`
- `.agent/skills/laravel-livewire.md`
- `.agent/skills/blade-components.md`
- `.agent/skills/shadcn-inspired-ui.md`
- `docs/ARCHITECTURE.md`
- `docs/COMPONENT_SYSTEM.md`
- `docs/FOLDER_STRUCTURE.md`
- `docs/IMPLEMENTATION_ROADMAP.md`
- `ADMIN_TASKS.md`

## Files To Inspect

- repository root
- `composer.json`
- `package.json`
- `routes/web.php`
- `resources/css/app.css`
- `resources/views/*`
- `config/app.php`

## Files To Change

- Laravel app skeleton files and directories
- `composer.json`
- `package.json`
- `.env`
- `.env.example`
- `config/app.php`
- `routes/web.php`
- `resources/css/app.css`
- `resources/views/layouts/admin.blade.php`
- `resources/views/components/ui/*`
- `resources/views/components/admin/*`
- `resources/views/livewire/admin/dashboard/index.blade.php`
- `app/Livewire/Admin/Dashboard/Index.php`
- `.agent/MEMORY.md`
- `.agent/AGENT-HANDOVER.md`
- `.agent/tasks/current-task.md`

## Implementation Steps

1. Verified PHP, Composer, Node, npm, and current repo state.
2. Created a Laravel 13 project in a temporary directory and added Livewire.
3. Merged the generated Laravel app into the current repo while preserving docs, `.agent`, and planning files.
4. Replaced the default welcome route with a full-page Livewire dashboard placeholder.
5. Added an admin layout and the first Blade-native shadcn-inspired components.
6. Updated CSS tokens and theme foundations for the admin look.
7. Fixed local `.env` defaults and made the layout tolerant of a missing built Vite manifest so tests can pass before assets are built.

## Acceptance Criteria

- Laravel 13 application shell exists in the repository
- Livewire is installed
- the stock welcome page is replaced with an admin-oriented entry screen
- the repo has an initial Blade-native shadcn-inspired UI foundation
- PHP app boot validation succeeds

## Validation Commands

- `php -v`
- `composer --version`
- `node -v`
- `npm -v`
- `php artisan about`
- `php artisan route:list`
- `npm install`
- `npm run build`
- `php artisan key:generate --ansi`
- `php artisan test`

## Risks

- `npm run build` is currently blocked by the local Node runtime version: Node `21.7.3` is not accepted by the Laravel 13 Vite 8 toolchain, which expects Node `20.19+` or `22.12+`
- frontend asset build should be revalidated under a supported Node version before relying on compiled assets

## Completion Notes

Completed:

- Laravel 13 bootstrapped into the repo
- Livewire 4 installed
- `composer.json`, `package.json`, `artisan`, `app/`, `bootstrap/`, `config/`, `resources/`, `routes/`, and related framework directories now exist
- root route now mounts `App\Livewire\Admin\Dashboard\Index`
- added admin shell layout and initial Blade components:
  - `resources/views/components/ui/button.blade.php`
  - `resources/views/components/ui/card.blade.php`
  - `resources/views/components/ui/badge.blade.php`
  - `resources/views/components/admin/nav-link.blade.php`
- added initial admin dashboard placeholder and theme styling
- updated app naming to `Wide Web Blog Admin`

Validated successfully:

- Artisan boots
- routes load
- PHPUnit passes

Not fully validated:

- frontend asset build under the current local Node version
