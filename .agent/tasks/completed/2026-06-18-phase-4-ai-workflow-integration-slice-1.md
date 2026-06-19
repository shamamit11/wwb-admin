# Task: 2026-06-18 Phase 4 AI Workflow Integration Slice 1

Status: Completed

## Goal

Create the implementation plan for the first Admin AI workflow integration slice:

- WB-ADMIN-AI-001
- WB-ADMIN-AI-002
- WB-ADMIN-AI-003
- WB-ADMIN-AI-004
- WB-ADMIN-AI-010
- WB-ADMIN-AI-011

## Background

`WB_ADMIN_AI_WORKFLOW_TASKS.md` defines the Admin-side replacement of Topic Queue and AI Jobs placeholders with Service API-driven workflows. This planning task is limited to understanding scope, dependencies, and the smallest coherent implementation order before code changes begin.

Sibling context was required because the previously referenced shared OpenAPI file did not show AI workflow endpoints. The smallest relevant verification was the Service API route/controller layer to confirm actual AI endpoint availability and names before implementing Admin clients.

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
- `docs/API_INTEGRATION.md`
- `docs/AUTHENTICATION.md`
- `docs/UI_UX_GUIDELINES.md`
- `WB_ADMIN_AI_WORKFLOW_TASKS.md`

## Files To Inspect

- `routes/web.php`
- `app/Support/Navigation/AdminNavigation.php`
- OpenAPI reference: `/Users/amitsharma/Downloads/document (5).json`
- Sibling verification: `/Users/amitsharma/Herd/widewebblog/service/routes/api.php`

## Files To Change

- `.agent/tasks/current-task.md`
- `routes/web.php`
- `app/Support/Navigation/AdminNavigation.php`
- `resources/views/components/admin/status-badge.blade.php`
- `app/Services/WideWebBlogApi/Clients/ContentTopicClient.php`
- `app/Services/WideWebBlogApi/Clients/AiJobClient.php`
- `app/Livewire/Admin/TopicQueue/Index.php`
- `app/Livewire/Admin/TopicQueue/Show.php`
- `app/Livewire/Admin/AiJobs/Index.php`
- `app/Livewire/Admin/AiJobs/Show.php`
- `resources/views/livewire/admin/topic-queue/index.blade.php`
- `resources/views/livewire/admin/topic-queue/show.blade.php`
- `resources/views/livewire/admin/ai-jobs/index.blade.php`
- `resources/views/livewire/admin/ai-jobs/show.blade.php`
- `tests/Unit/WideWebBlogApi/Clients/ContentTopicClientTest.php`
- `tests/Unit/WideWebBlogApi/Clients/AiJobClientTest.php`
- `tests/Feature/TopicQueue/TopicQueueScreensTest.php`
- `tests/Feature/AiJobs/AiJobScreensTest.php`
- `tests/Feature/Navigation/AdminNavigationTest.php`

## Implementation Steps

1. Confirm the first-slice scope from `WB_ADMIN_AI_WORKFLOW_TASKS.md`.
2. Verify Admin architecture and API-client constraints from `.agent` and `docs/` guidance.
3. Verify the current placeholder implementation for Topic Queue and AI Jobs.
4. Verify whether the current OpenAPI contract includes AI workflow endpoints needed for the first slice.
5. Implement contract-backed Topic Queue and AI Jobs clients, routes, screens, and tests.
6. Implement topic discovery trigger flow once the API contract exposes that action.

## Acceptance Criteria

- The first implementation slice is clearly scoped.
- Required dependencies and repository touchpoints are identified.
- Contract gaps or `TBC` items are called out before implementation starts.
- The plan follows existing Admin API/client, Livewire, and UI conventions.

## Validation Commands

- `php artisan test tests/Unit/WideWebBlogApi/Clients/ContentTopicClientTest.php tests/Unit/WideWebBlogApi/Clients/AiJobClientTest.php tests/Feature/TopicQueue/TopicQueueScreensTest.php tests/Feature/AiJobs/AiJobScreensTest.php tests/Feature/Navigation/AdminNavigationTest.php`
- `php -l app/Livewire/Admin/TopicQueue/Index.php`
- `php -l app/Livewire/Admin/TopicQueue/Show.php`
- `php -l app/Livewire/Admin/AiJobs/Index.php`
- `php -l app/Livewire/Admin/AiJobs/Show.php`
- `php -l app/Services/WideWebBlogApi/Clients/AiJobClient.php`
- `php artisan test tests/Unit/WideWebBlogApi/Clients/AiJobClientTest.php tests/Feature/TopicQueue/TopicQueueScreensTest.php`
- `php artisan test`
- `npm run build`

## Risks

- Topic Queue and AI Jobs list endpoints currently expose filtering and sorting only, not server-side pagination parameters; the Admin screens therefore use local UI pagination over the returned collection.
- Existing docs outside this task still contain older placeholder-era wording and should be updated in the later documentation task.

## Completion Notes

- Implemented the first contract-backed Topic Queue and AI Jobs slice.
- Endpoints used:
  - `POST /admin/ai-jobs/topic-discovery`
  - `GET /admin/content-topics`
  - `GET /admin/content-topics/{contentTopic}`
  - `PATCH /admin/content-topics/{contentTopic}`
  - `POST /admin/content-topics/{contentTopic}/approve`
  - `POST /admin/content-topics/{contentTopic}/reject`
  - `POST /admin/content-topics/{contentTopic}/mark-used`
  - `GET /admin/ai-jobs`
  - `GET /admin/ai-jobs/{aiJob}`
  - `POST /admin/ai-jobs/{aiJob}/retry`
- Screens added:
  - Topic Queue index
  - Topic detail / review
  - AI Jobs index
  - AI Job detail
- Topic discovery flow added to Topic Queue with contract-backed job creation and redirect to AI Job detail
- Tests added:
  - Topic client request coverage
  - AI job client request coverage
  - Topic Queue feature coverage
  - AI Jobs feature coverage
  - Navigation coverage update
