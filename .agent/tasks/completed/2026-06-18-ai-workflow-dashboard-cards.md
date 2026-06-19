# Task: 2026-06-18 AI Workflow Dashboard Cards

Status: Complete

## Goal

Implement `WB-ADMIN-AI-014` by adding AI workflow summary cards and a recent AI jobs block to the admin dashboard.

## Background

The dashboard still contains placeholder AI widgets even though Topic Queue, Content Briefs, AI Jobs, and Prompt Templates are now service-backed. This slice should replace those placeholders with actionable dashboard summaries that link into the implemented AI workflow screens.

## Required Context

- `.agent/INDEX.md`
- `.agent/MEMORY.md`
- `.agent/agents/SHARED-INSTRUCTIONS.md`
- `.agent/TASK-WORKFLOW.md`
- `.agent/API-CONTRACT.md`
- `.agent/ARCHITECTURE.md`
- `.agent/UI-UX-RULES.md`
- `.agent/skills/laravel-livewire.md`
- `.agent/skills/api-client-integration.md`
- `docs/API_INTEGRATION.md`
- `docs/UI_UX_GUIDELINES.md`
- `openapi.json`
- `WB_ADMIN_AI_WORKFLOW_TASKS.md`

## Files To Inspect

- `app/Livewire/Admin/Dashboard/Index.php`
- `resources/views/livewire/admin/dashboard/index.blade.php`
- `app/Services/WideWebBlogApi/Clients/PostClient.php`
- `app/Services/WideWebBlogApi/Clients/ContentTopicClient.php`
- `app/Services/WideWebBlogApi/Clients/ContentBriefClient.php`
- `app/Services/WideWebBlogApi/Clients/AiJobClient.php`

## Files To Change

- `.agent/tasks/current-task.md`
- `app/Livewire/Admin/Dashboard/Index.php`
- `resources/views/livewire/admin/dashboard/index.blade.php`
- `tests/Feature/Dashboard/DashboardIndexTest.php`

## Implementation Steps

1. Load AI workflow summary data from existing service-backed topic, brief, post, and AI job endpoints.
2. Add dashboard cards for suggested topics, approved topics, draft briefs, approved briefs, draft posts pending review, and failed AI jobs.
3. Add a recent AI jobs block with links into the AI Jobs module.
4. Replace the old placeholder AI widgets with real dashboard links and empty/error handling.
5. Add focused dashboard feature coverage, then run validation.

## Acceptance Criteria

- Admin can quickly see AI workflow status from the dashboard.
- Failed jobs are highlighted clearly.
- Drafts pending human review are visible.
- Cards and recent-job rows link to the relevant AI workflow pages.
- Dashboard uses the existing admin design system and service API clients.

## Validation Commands

- `php artisan test tests/Feature/Dashboard/DashboardIndexTest.php`
- `php artisan test`
- `npm run build`

## Risks

- The current service endpoints return lists rather than dedicated aggregate counts, so dashboard counts depend on filtered index responses remaining sufficient for MVP overview use.
- `WB-ADMIN-AI-009` remains separate because post provenance fields needed for AI-draft-specific review are still not documented in the Admin contract.

## Completion Notes

- Replaced the old dashboard AI placeholders with service-backed AI workflow cards for suggested topics, approved topics, draft briefs, approved briefs, draft posts pending review, and failed AI jobs.
- Added a recent AI jobs dashboard block with direct links into the AI Jobs detail screen and updated quick actions to point into active AI workflow modules.
- Kept the dashboard contract-aligned by using existing filtered index endpoints for counts and by explicitly treating draft-post counts as general draft review volume because the post contract still does not expose AI-only provenance.
- Added focused dashboard feature coverage and updated existing dashboard assertions to match the new service-backed dashboard behavior.
- Validation completed:
  - `php artisan test tests/Feature/Dashboard/DashboardIndexTest.php`
  - `php artisan test tests/Feature/Dashboard/DashboardTest.php tests/Feature/Dashboard/DashboardIndexTest.php`
  - `php artisan test`
  - `npm run build` succeeded, but Vite emitted a Node compatibility warning because the local runtime is `22.1.0` and recommends `22.12+`.
