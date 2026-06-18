# Task: Draft review rewrite action

Status: Completed

## Goal

Add admin support for the new `POST /api/v1/admin/posts/{post}/rewrite` contract from the draft review flow so editors can queue full-draft rewrites and targeted section or paragraph regeneration jobs.

## Background

The service added a review-only rewrite job endpoint for existing draft posts. Admin already supports AI draft review and AI job inspection, so this work should extend the existing post editor and API client patterns without changing unrelated post flows.

## Required Context

- `AGENTS.md`
- `.agent/INDEX.md`
- `.agent/MEMORY.md`
- `.agent/tasks/current-task.md`
- `.agent/agents/SHARED-INSTRUCTIONS.md`
- `.agent/TASK-WORKFLOW.md`
- `.agent/API-CONTRACT.md`
- `.agent/skills/api-client-integration.md`
- `.agent/skills/post-editor.md`
- `docs/API_INTEGRATION.md`
- `openapi.json`

## Files To Inspect

- `app/Services/WideWebBlogApi/Clients/PostClient.php`
- `app/Livewire/Admin/Posts/Editor.php`
- `resources/views/livewire/admin/posts/editor.blade.php`
- `app/Data/Posts/PostBlockData.php`

## Files To Change

- `.agent/tasks/current-task.md`
- `app/Services/WideWebBlogApi/Clients/PostClient.php`
- `app/Livewire/Admin/Posts/Editor.php`
- `resources/views/livewire/admin/posts/editor.blade.php`
- `docs/API_INTEGRATION.md`

## Implementation Steps

1. Confirm the rewrite endpoint contract from the service OpenAPI source.
2. Add a `rewrite` method to the post API client.
3. Extend the draft review Livewire editor with rewrite dialog state, validation, payload building, and AI job redirect/alerts.
4. Add draft review UI for full-draft rewrite and targeted section/paragraph regeneration using existing block IDs.
5. Update API integration docs for the new post rewrite endpoint.

## Acceptance Criteria

- [ ] Admin can queue a full draft rewrite from draft review.
- [ ] Admin can queue targeted section regeneration with selected post block IDs.
- [ ] Admin can queue targeted paragraph regeneration with exactly one selected paragraph block.
- [ ] Success redirects to the created AI job when an ID is returned.
- [ ] Validation and API errors are surfaced consistently with existing admin patterns.

## Validation Commands

- `php artisan test --filter=Post`
- `php artisan test --filter=PostEditorTest`
- `php artisan test --filter=PostSeo`

## Risks

- Targeted regeneration depends on block IDs being present in the post payload; UI should avoid exposing targeted actions when IDs are missing.
- The service validates contiguous section selection at execution time, so admin can guide selection but cannot prove contiguity beyond current block order.

## Completion Notes

- Added `PostClient::rewrite()` for the new `POST /admin/posts/{post}/rewrite` contract.
- Extended the draft review editor with rewrite dialog state, local scope and block-target validation, and AI job redirect/flash behavior.
- Added draft review UI actions for full draft rewrite plus targeted section and paragraph regeneration.
- Updated `docs/API_INTEGRATION.md` to include the new posts rewrite endpoint and payload notes.
- Validation passed with `php artisan test --filter=Post`, `php artisan test --filter=PostEditorTest`, `php artisan test --filter=PostSeo`, and `php artisan test --filter=ContentBrief`.
