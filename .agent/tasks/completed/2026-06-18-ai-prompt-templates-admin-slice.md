# Task: 2026-06-18 AI Prompt Templates Admin Slice

Status: Complete

## Goal

Implement the AI Prompt Templates admin slice covering:

- WB-ADMIN-AI-012
- WB-ADMIN-AI-013

## Background

The draft-review task (`WB-ADMIN-AI-009`) is not cleanly implementable from the current Admin OpenAPI contract because the documented post endpoints do not expose an AI-only filter or source-topic/source-brief provenance on `PostResource`. The next coherent contract-backed slice is prompt template management through the documented `/admin/ai-prompts` endpoints.

## Required Context

- `.agent/INDEX.md`
- `.agent/MEMORY.md`
- `.agent/agents/SHARED-INSTRUCTIONS.md`
- `.agent/TASK-WORKFLOW.md`
- `.agent/API-CONTRACT.md`
- `.agent/ARCHITECTURE.md`
- `.agent/FOLDER-STRUCTURE.md`
- `.agent/UI-UX-RULES.md`
- `.agent/skills/laravel-livewire.md`
- `.agent/skills/api-client-integration.md`
- `.agent/skills/tables-filters-pagination.md`
- `.agent/skills/forms-validation.md`
- `docs/API_INTEGRATION.md`
- `docs/AUTHENTICATION.md`
- `docs/UI_UX_GUIDELINES.md`
- `openapi.json`
- `WB_ADMIN_AI_WORKFLOW_TASKS.md`

## Files To Inspect

- `routes/web.php`
- `app/Support/Navigation/AdminNavigation.php`
- `app/Livewire/Admin/Templates/Index.php`
- `resources/views/livewire/admin/templates/index.blade.php`
- `app/Services/WideWebBlogApi/Clients/TemplateClient.php`
- `tests/Feature/Templates/TemplateIndexTest.php`

## Files To Change

- `.agent/tasks/current-task.md`
- `routes/web.php`
- `app/Support/Navigation/AdminNavigation.php`
- `app/Services/WideWebBlogApi/Clients/AiPromptClient.php`
- `app/Livewire/Admin/AiPrompts/Index.php`
- `app/Livewire/Admin/AiPrompts/Show.php`
- `resources/views/livewire/admin/ai-prompts/index.blade.php`
- `resources/views/livewire/admin/ai-prompts/show.blade.php`
- `tests/Unit/WideWebBlogApi/Clients/AiPromptClientTest.php`
- `tests/Feature/AiPrompts/AiPromptScreensTest.php`
- `tests/Feature/Navigation/AdminNavigationTest.php`

## Implementation Steps

1. Add an AI prompt API client for list, show, create, update, create-version, and activate-version actions.
2. Add prompt template routes and navigation under AI Content.
3. Implement a prompt template listing screen with search, type/status filters, active-version display, and local pagination.
4. Implement a prompt template detail screen with metadata editing, version history, new-version creation, and activate-version action.
5. Add clear warnings that prompt changes affect future generations only.
6. Add focused client and feature tests, then run project validation.

## Acceptance Criteria

- Admin can view prompt templates from the Service API.
- Admin can filter prompt templates by type and status.
- Admin can inspect active and historical prompt versions.
- Admin can update prompt metadata, add a new version, and activate a specific version.
- Admin UI makes clear that prompt changes affect future AI generations only.

## Validation Commands

- `php artisan test tests/Unit/WideWebBlogApi/Clients/AiPromptClientTest.php tests/Feature/AiPrompts/AiPromptScreensTest.php tests/Feature/Navigation/AdminNavigationTest.php`
- `php artisan test`
- `npm run build`

## Risks

- `WB-ADMIN-AI-009` remains TBC until the Admin contract exposes AI-draft provenance fields or a dedicated draft review endpoint/filter.
- Prompt version payload fields such as `output_schema` and `variables` are array-shaped, so the admin UI should use pragmatic JSON editing without inventing typed subforms.

## Completion Notes

- Implemented the AI prompt templates admin slice end to end with a dedicated service API client, AI Content navigation entry, list screen, create/detail screen, version history, new-version creation, and activate-version action.
- Kept version payload editing pragmatic and contract-aligned by using JSON-array editing for `output_schema` and line-based editing for `variables`.
- Added focused client and feature coverage for prompt listing, creation, metadata updates, version creation, activation, and admin navigation.
- Validation completed:
  - `php artisan test tests/Unit/WideWebBlogApi/Clients/AiPromptClientTest.php tests/Feature/AiPrompts/AiPromptScreensTest.php tests/Feature/Navigation/AdminNavigationTest.php`
  - `php artisan test`
  - `npm run build` succeeded, but Vite emitted a Node compatibility warning because the local runtime is `22.1.0` and recommends `22.12+`.
