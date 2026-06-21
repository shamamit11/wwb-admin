# Task: Improve Post Create/Edit Editor UX

Status: Completed

## Goal

Improve the Post create/edit editor UX so long-form posts with many content blocks remain easy to scan, navigate, and edit without changing existing save, publish, preview, or API behavior.

## Background

The current editor is functionally complete, but all post blocks and advanced SEO areas render expanded by default. This makes long posts visually noisy and forces editors to scroll excessively to move between content, metadata, and save actions.

## Required Context

- `.agent/INDEX.md`
- `.agent/MEMORY.md`
- `.agent/agents/SHARED-INSTRUCTIONS.md`
- `.agent/TASK-WORKFLOW.md`
- `.agent/UI-UX-RULES.md`
- `.agent/COMPONENT-SYSTEM.md`
- `.agent/TESTING.md`
- `.agent/skills/laravel-livewire.md`
- `.agent/skills/post-editor.md`
- `.agent/skills/shadcn-inspired-ui.md`
- `.agent/skills/seo-admin.md`
- `.agent/skills/testing.md`
- `docs/UI_UX_GUIDELINES.md`

## Files To Inspect

- `app/Livewire/Admin/Posts/Editor.php`
- `resources/views/livewire/admin/posts/editor.blade.php`
- `tests/Feature/Posts/PostEditorTest.php`

## Files To Change

- `.agent/tasks/current-task.md`
- `app/Livewire/Admin/Posts/Editor.php`
- `resources/views/livewire/admin/posts/editor.blade.php`
- `tests/Feature/Posts/PostEditorTest.php` if focused regression coverage is needed

## Implementation Steps

1. Inspect the existing post editor component and blade view for current block preview, sidebar, and SEO rendering patterns.
2. Add minimal Livewire state for collapsible block editing and batch expand/collapse controls without affecting block payloads.
3. Refine the block list, outline/navigation, sticky action visibility, and advanced SEO presentation using existing admin UI patterns.
4. Add or adjust narrow regression coverage only where behavior changes need protection.
5. Run narrow validation and record residual risks.

## Acceptance Criteria

- Content blocks can be expanded and collapsed individually.
- The editor provides `Expand All` and `Collapse All` controls.
- Collapsed blocks show useful compact previews using existing content.
- Advanced SEO output no longer dominates the default edit flow.
- Save and preview actions remain easy to access during long editing sessions.
- Existing create, edit, save, publish, preview, move, and remove actions continue to work.

## Validation Commands

- `php -l app/Livewire/Admin/Posts/Editor.php`
- `php artisan test --filter=PostEditorTest`
- `php artisan view:cache`

## Risks

- Livewire requests triggered by block actions must preserve collapse state consistently enough to avoid frustrating editors.
- The editor already contains a large amount of conditional UI, so view changes should stay incremental to avoid regressions in publish, AI-review, and SEO flows.

## Completion Notes

- Added Livewire-managed block expansion state with per-block collapse/expand, `Expand All`, `Collapse All`, and automatic focus on newly added blocks.
- Reworked the block cards into compact summaries by default, using block type, line count, template-link status, and truncated content previews from existing block data.
- Added a sidebar `Editor Actions` card and `Post Outline` navigator so save actions and long-post navigation stay visible while editing.
- Reduced SEO noise by keeping core fields visible and moving advanced OpenGraph/robots settings, diagnostics, and Meta JSON into expandable sections.
- Preserved existing save, publish, schedule, unpublish, delete, media, template, AI review, and SEO behaviors without changing the post payload contract.
- Validation passed with `php -l app/Livewire/Admin/Posts/Editor.php`, `php artisan test --filter=PostEditorTest`, `php artisan view:clear`, and `php artisan view:cache`.
