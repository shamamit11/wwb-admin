# Task: Add review-only AI actions for draft generation mode, metadata suggestions, and title/excerpt refinement

Status: Completed

## Goal

Add the new service AI contracts to Admin with the smallest consistent API client and Livewire UI changes.

## Background

The local admin OpenAPI reference in `docs/API_INTEGRATION.md` points to `/Users/amitsharma/Downloads/document (5).json`, but that snapshot does not expose the new contracts. Sibling service context was required to confirm the live request classes, controller methods, and `AiJobResource` 202 response shape for:

- `POST /api/v1/admin/content-briefs/{contentBrief}/generate-draft`
- `POST /api/v1/admin/posts/{post}/suggest-metadata`
- `POST /api/v1/admin/posts/{post}/refine-title-excerpt`

## Required Context

- `.agent/API-CONTRACT.md`
- `.agent/ARCHITECTURE.md`
- `.agent/skills/laravel-livewire.md`
- `.agent/skills/api-client-integration.md`
- `.agent/skills/seo-admin.md`
- `docs/API_INTEGRATION.md`
- `docs/AI_WORKFLOW_ADMIN.md`
- `docs/AI_JOBS_ADMIN.md`
- `../service/app/Http/Requests/Api/V1/Admin/GenerateBlogDraftRequest.php`
- `../service/app/Http/Requests/Api/V1/Admin/QueuePostMetadataSuggestionRequest.php`
- `../service/app/Http/Requests/Api/V1/Admin/QueuePostTitleExcerptRefinementRequest.php`
- `../service/app/Http/Controllers/Api/V1/Admin/ContentBriefController.php`
- `../service/app/Http/Controllers/Api/V1/Admin/PostController.php`
- `../service/app/Http/Resources/Api/V1/AiJobResource.php`

## Files To Inspect

- `app/Services/WideWebBlogApi/Clients/ContentBriefClient.php`
- `app/Services/WideWebBlogApi/Clients/PostClient.php`
- `app/Services/WideWebBlogApi/Clients/AiJobClient.php`
- `app/Livewire/Admin/ContentBriefs/Show.php`
- `resources/views/livewire/admin/content-briefs/show.blade.php`
- `app/Livewire/Admin/Posts/Editor.php`
- `resources/views/livewire/admin/posts/editor.blade.php`
- `tests/Unit/WideWebBlogApi/Clients/ContentBriefClientTest.php`
- `tests/Feature/ContentBriefs/ContentBriefScreensTest.php`
- `tests/Feature/Posts/PostEditorTest.php`

## Files To Change

- `.agent/tasks/current-task.md`
- `app/Services/WideWebBlogApi/Clients/ContentBriefClient.php`
- `app/Services/WideWebBlogApi/Clients/PostClient.php`
- `app/Livewire/Admin/ContentBriefs/Show.php`
- `resources/views/livewire/admin/content-briefs/show.blade.php`
- `app/Livewire/Admin/Posts/Editor.php`
- `resources/views/livewire/admin/posts/editor.blade.php`
- narrow tests as needed

## Implementation Steps

- Add client support for optional `generation_mode` on draft generation.
- Add post client methods for `suggest-metadata` and `refine-title-excerpt`.
- Extend content brief draft-generation dialog with optional generation mode.
- Extend AI draft review UI with review-only actions for metadata suggestion and title/excerpt refinement.
- Reuse the existing AI job flash + redirect pattern instead of inventing synchronous result handling.
- Add narrow client and Livewire tests for the new queued actions.

## Acceptance Criteria

- Existing draft generation still works when `generation_mode` is omitted.
- Admin can queue metadata suggestion and title/excerpt refinement jobs from post draft review/edit screens.
- UI wording is explicit that these are review-only jobs and do not auto-apply or publish.
- Successful actions follow the existing AI job handoff pattern and do not assume immediate results.
- No unrelated UI or architecture changes are introduced.

## Validation Commands

- `php artisan test --filter=ContentBriefClientTest`
- `php artisan test --filter=ContentBriefScreensTest`
- `php artisan test --filter=PostEditorTest`

## Risks

- The admin-side contract docs may still lag the service repo after implementation.
- Post review screens currently expose rewrite actions only in AI review mode; placement for the two new actions should stay aligned with that existing pattern.

## Completion Notes

- Added optional `generation_mode` support to content brief draft generation and surfaced it in the existing review dialog.
- Added post client methods and AI review UI actions for review-only metadata suggestion and title/excerpt refinement jobs.
- Reused the existing AI job flash + redirect pattern so Admin never assumes synchronous results or auto-applies suggestions.
- Validation run:
  - `php artisan test --filter=ContentBriefClientTest`
  - `php artisan test --filter=ContentBriefScreensTest`
  - `php artisan test --filter=PostClientTest`
  - `php artisan test --filter=PostEditorTest`
