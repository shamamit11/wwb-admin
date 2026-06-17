# Task: Refine post editor block UX

Status: Completed

## Goal

Make block editing clearer and more editorially usable without changing the post API contract.

## Background

The current post editor used one generic textarea for every block type. That matched the transport layer, but it did not explain how editors should use different block types and it left `source_template_block_id` too exposed for manual editing.

## Required Context

- `.agent/UI-UX-RULES.md`
- `.agent/API-CONTRACT.md`
- `.agent/skills/post-editor.md`
- `.agent/skills/forms-validation.md`
- `docs/UI_UX_GUIDELINES.md`
- `docs/API_INTEGRATION.md`
- `openapi.json`

## Files To Inspect

- `app/Livewire/Admin/Posts/Editor.php`
- `app/Data/Posts/PostBlockData.php`
- `resources/views/livewire/admin/posts/editor.blade.php`
- `tests/Feature/Posts/PostEditorTest.php`

## Files To Change

- `app/Livewire/Admin/Posts/Editor.php`
- `resources/views/livewire/admin/posts/editor.blade.php`
- `tests/Feature/Posts/PostEditorTest.php`

## Implementation Steps

- add block-type-specific guidance, placeholders, and content labels
- add a minimal markdown formatting toolbar for text-oriented block editing
- stop treating source template linkage as a normal editable field in manual flows
- add narrow tests for the new block editing helpers

## Acceptance Criteria

- block editing guidance is clearer for different block types
- editors have minimal formatting affordances without introducing a heavy rich text editor
- template linkage is preserved without encouraging manual ID entry

## Validation Commands

- `php artisan test tests/Feature/Posts/PostEditorTest.php`

## Risks

- block content remains contract-shaped as string arrays, so block-specific semantics are still intentionally lightweight

## Completion Notes

- added block-type-specific labels, placeholders, and save-guidance in the post editor
- added a lightweight markdown snippet toolbar for text-oriented blocks
- replaced manual source template block editing with read-only template linkage display
- validated with `php artisan test tests/Feature/Posts/PostEditorTest.php`
