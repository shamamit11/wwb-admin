# Task: WB-ADMIN-AI-009 Draft Review page for AI-generated posts

Status: Completed

## Goal

Implement a dedicated Admin draft-review experience for AI-generated posts using the new post provenance fields and the existing post editor workflow.

## Background

- The Service `PostResource` now exposes:
  - `is_ai_generated`
  - `source_content_brief_id`
  - `source_content_topic_id`
  - `generated_by_ai_job_id`
  - `generated_by`
- `GET /api/v1/admin/posts` now supports filtering by:
  - `is_ai_generated`
  - `source_content_brief_id`
  - `source_content_topic_id`
  - `generated_by_ai_job_id`
- The existing Admin post editor already covers manual editing, SEO editing, and publish-state actions.
- Sibling Service context was required to verify the exact new `PostResource` fields and the AI draft meta keys used by generated drafts.

## Required Context

- `.agent/INDEX.md`
- `.agent/MEMORY.md`
- `.agent/agents/SHARED-INSTRUCTIONS.md`
- `.agent/TASK-WORKFLOW.md`
- `.agent/UI-UX-RULES.md`
- `.agent/API-CONTRACT.md`
- `.agent/skills/laravel-livewire.md`
- `.agent/skills/tables-filters-pagination.md`
- `.agent/skills/post-editor.md`
- `docs/API_INTEGRATION.md`
- `docs/UI_UX_GUIDELINES.md`
- `WB_ADMIN_AI_WORKFLOW_TASKS.md`

## Files To Inspect

- `routes/web.php`
- `app/Support/Navigation/AdminNavigation.php`
- `app/Services/WideWebBlogApi/Clients/PostClient.php`
- `app/Data/Posts/PostEditorData.php`
- `app/Livewire/Admin/Posts/Index.php`
- `app/Livewire/Admin/Posts/Editor.php`
- `resources/views/livewire/admin/posts/index.blade.php`
- `resources/views/livewire/admin/posts/editor.blade.php`
- `tests/Feature/Posts/PostIndexTest.php`
- `tests/Feature/Posts/PostEditorTest.php`
- `tests/Feature/Navigation/AdminNavigationTest.php`
- sibling verification:
  - `/Users/amitsharma/Herd/widewebblog/service/app/Http/Resources/Api/V1/PostResource.php`

## Files To Change

- `.agent/tasks/current-task.md`
- `routes/web.php`
- `app/Support/Navigation/AdminNavigation.php`
- `app/Data/Posts/PostEditorData.php`
- `app/Livewire/Admin/Posts/Index.php`
- `app/Livewire/Admin/Posts/Editor.php`
- `resources/views/livewire/admin/posts/index.blade.php`
- `resources/views/livewire/admin/posts/editor.blade.php`
- `tests/Feature/Posts/PostIndexTest.php`
- `tests/Feature/Posts/PostEditorTest.php`
- `tests/Feature/Navigation/AdminNavigationTest.php`

## Implementation Steps

1. Add dedicated Draft Review routes under AI Content using the existing post index/editor components in AI-review mode.
2. Filter the draft-review listing to AI-generated draft posts only and surface source topic, source brief, and AI job provenance.
3. Extend the post editor to show AI review context, source links, and generated suggestion panels while preserving the existing publish and SEO flows.
4. Add focused route, listing, editor, and navigation tests for the new AI draft-review experience.
5. Run narrow validation and record residual risk.

## Acceptance Criteria

- Admin can review AI-generated draft posts.
- Admin can edit generated content before publishing.
- Admin can see source topic and brief context.
- Admin can manually publish using the existing post workflow.
- AI generation never bypasses manual review.

## Validation Commands

- `php artisan test tests/Feature/Posts/PostIndexTest.php tests/Feature/Posts/PostEditorTest.php tests/Feature/Navigation/AdminNavigationTest.php`

## Risks

- The post contract exposes source ids and AI metadata, but not fully hydrated source title payloads, so draft review may need to link by id rather than render richer source summaries.
- Suggestion metadata is stored in `meta`, so the review UI must stay tolerant of missing or variably shaped arrays.

## Completion Notes

- Added dedicated AI Content Draft Review routes:
  - `draft-review.index`
  - `draft-review.show`
- Reused the existing post index/editor components in AI-review mode instead of creating a second editor stack.
- Draft Review list now filters to Service-backed AI-generated draft posts and surfaces source brief, source topic, generating AI job, and generator metadata.
- Post editor now shows AI review context and suggestion panels for:
  - suggested tags
  - FAQ suggestions
  - image placement notes
  - alt text suggestions
- Existing manual edit, SEO edit, publish, schedule, unpublish, and delete flows were preserved.
- Added focused route, navigation, listing, and editor coverage.
- Validation passed:
  - `php artisan test tests/Feature/Posts/PostIndexTest.php tests/Feature/Posts/PostEditorTest.php tests/Feature/Navigation/AdminNavigationTest.php`
  - `git diff --check`
