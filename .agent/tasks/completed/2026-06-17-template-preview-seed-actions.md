# Task: Template Preview And Seed-Post Actions

Status: Completed

## Goal

Support previewing templates and seeding post payloads from them.

## Background

The template CRUD flow is already implemented. The service also exposes `preview` and `seed-post` actions for templates, and the admin now needs a clear way to trigger them and inspect the returned payloads without inventing any extra post-editor behavior.

## Required Context

- `AGENTS.md`
- `.agent/INDEX.md`
- `.agent/MEMORY.md`
- `.agent/tasks/current-task.md`
- `.agent/agents/SHARED-INSTRUCTIONS.md`
- `.agent/API-CONTRACT.md`
- `.agent/skills/api-client-integration.md`
- `.agent/skills/laravel-livewire.md`
- `docs/API_INTEGRATION.md`
- `docs/UI_UX_GUIDELINES.md`

## Files To Inspect

- `app/Livewire/Admin/Templates/Index.php`
- `resources/views/livewire/admin/templates/index.blade.php`
- `app/Services/WideWebBlogApi/Clients/TemplateClient.php`
- `tests/Feature/Templates/TemplateIndexTest.php`
- `tests/Integration/TemplateClientTest.php`
- sibling service template preview and seed-post resource / request files

## Files To Change

- `app/Livewire/Admin/Templates/Index.php`
- `resources/views/livewire/admin/templates/index.blade.php`
- `app/Services/WideWebBlogApi/Clients/TemplateClient.php`
- `tests/Feature/Templates/*`
- `tests/Integration/*Template*`

## Implementation Steps

- Add template client methods for `preview` and `seed-post`.
- Add admin actions that collect the documented context fields and call the documented endpoints.
- Render preview output safely and clearly.
- Render seeded post payload output in a way that is useful now and does not assume future post-editor behavior.
- Add focused feature and client tests with HTTP fakes.

## Acceptance Criteria

- preview action uses the documented endpoint
- seed-post action uses the documented endpoint
- output is usable without inventing extra backend behavior

## Validation Commands

- `php artisan test tests/Integration/TemplateClientTest.php`
- `php artisan test tests/Feature/Templates/TemplateIndexTest.php`

## Risks

- The returned preview and seeded post payloads are intentionally flexible arrays, so the UI should present them clearly without overfitting to undocumented fields.

## Completion Notes

- Added template client methods for `POST /admin/templates/{template}/preview` and `POST /admin/templates/{template}/seed-post`.
- Added `Preview` and `Seed Post` actions to the template table.
- Added a dedicated action drawer that collects the documented `title` and `topic` context fields.
- Preview results now render the returned title, topic, meta payload, and per-block content safely without interpreting backend content as HTML.
- Seed-post results now render the returned post payload clearly as service output only, without assuming later post-editor navigation behavior.
- Added focused client and feature tests for both actions using HTTP fakes.
