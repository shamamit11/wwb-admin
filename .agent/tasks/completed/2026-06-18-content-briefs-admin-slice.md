# Task: 2026-06-18 Content Briefs Admin Slice

Status: Complete

## Goal

Implement the Content Briefs Admin slice covering:

- WB-ADMIN-AI-005
- WB-ADMIN-AI-006
- WB-ADMIN-AI-007
- WB-ADMIN-AI-008

## Background

The previous slice delivered Topic Queue, Topic Discovery, and AI Jobs. The next contract-backed slice is the editorial review flow for content briefs, including listing, detail/editing, approval, and draft generation through the Service API.

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
- `app/Services/WideWebBlogApi/Clients/CategoryClient.php`
- `app/Services/WideWebBlogApi/Clients/TemplateClient.php`
- `app/Livewire/Admin/TopicQueue/Show.php`
- `app/Livewire/Admin/Posts/Editor.php`
- `resources/views/livewire/admin/topic-queue/show.blade.php`

## Files To Change

- `.agent/tasks/current-task.md`
- `routes/web.php`
- `app/Support/Navigation/AdminNavigation.php`
- `app/Services/WideWebBlogApi/Clients/ContentBriefClient.php`
- `app/Livewire/Admin/ContentBriefs/Index.php`
- `app/Livewire/Admin/ContentBriefs/Show.php`
- `resources/views/livewire/admin/content-briefs/index.blade.php`
- `resources/views/livewire/admin/content-briefs/show.blade.php`
- `tests/Unit/WideWebBlogApi/Clients/ContentBriefClientTest.php`
- `tests/Feature/ContentBriefs/ContentBriefScreensTest.php`
- `tests/Feature/Navigation/AdminNavigationTest.php`

## Implementation Steps

1. Add a centralized Content Brief API client for list, show, update, approve, and generate-draft actions.
2. Add Content Brief routes and navigation entries under AI Content.
3. Implement the Content Briefs list screen with search, status filter, topic relationship display, and local UI pagination.
4. Implement the Content Brief detail/review screen with editable brief fields and structured suggestion sections.
5. Add approve action for review-ready briefs.
6. Add draft generation flow using the contract-backed `GenerateBlogDraftRequest` payload and redirect to AI Job detail.
7. Add focused client and feature tests, then run project validation.

## Acceptance Criteria

- Admin can view content briefs from the Service API.
- Admin can filter briefs by status and inspect the source topic.
- Admin can edit brief content and save changes through the Service API.
- Admin can approve eligible briefs.
- Admin can start blog draft generation only through the Service API.
- Admin shows clear loading, empty, success, and error states.

## Validation Commands

- `php artisan test tests/Unit/WideWebBlogApi/Clients/ContentBriefClientTest.php tests/Feature/ContentBriefs/ContentBriefScreensTest.php tests/Feature/Navigation/AdminNavigationTest.php`
- `php artisan test`
- `npm run build`

## Risks

- The brief resource exposes structured arrays for outline, headings, FAQs, internal links, and image suggestions; Admin needs a pragmatic editing UI without inventing unsupported structure semantics.
- `GenerateBlogDraftRequest` requires `category_id`, so the brief detail flow must load category options before allowing draft generation.
- Draft generation can optionally use template and media references; those fields should remain optional and contract-aligned.

## Completion Notes

- Implemented the content brief admin slice end to end: service API client, list/detail Livewire screens, topic-to-brief generation entry point, approval flow, and draft generation handoff to AI jobs.
- Added focused client, screen, topic-review, and navigation coverage for the new content brief workflow.
- Fixed a Blade component parsing edge in the review screen by simplifying `hint` copy on structured JSON fields.
- Validation completed:
  - `php artisan test tests/Unit/WideWebBlogApi/Clients/ContentBriefClientTest.php tests/Feature/ContentBriefs/ContentBriefScreensTest.php tests/Feature/TopicQueue/TopicQueueScreensTest.php tests/Feature/Navigation/AdminNavigationTest.php`
  - `php artisan test`
  - `npm run build` succeeded, but Vite emitted a Node compatibility warning because the local runtime is `22.1.0` and recommends `22.12+`.
