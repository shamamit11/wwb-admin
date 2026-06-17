# Task: Reorder post editor SEO section below Blocks

Status: Completed

## Goal

Move the SEO section in the post editor so it appears directly below the Blocks section and reduces perceived page length.

## Background

The current post edit screen places SEO below taxonomy and media in the right sidebar, which makes the page feel longer and pushes a frequently reviewed section too far down.

## Required Context

- `.agent/skills/laravel-livewire.md`
- `.agent/skills/post-editor.md`
- `docs/UI_UX_GUIDELINES.md`

## Files To Inspect

- `resources/views/livewire/admin/posts/editor.blade.php`
- related post editor Livewire file only if needed

## Files To Change

- `resources/views/livewire/admin/posts/editor.blade.php`

## Implementation Steps

1. Inspect the current post editor layout markup.
2. Reorder the SEO section so it appears before taxonomy/media in the side panel.
3. Run narrow validation for the affected Blade/feature flow if needed.

## Acceptance Criteria

- [x] SEO section appears below Blocks in the editor flow
- [x] no post editor behavior changes beyond layout order

## Validation Commands

- `php artisan test --filter=Post`

## Risks

- The page uses a responsive grid, so section order must remain sensible on smaller breakpoints.

## Completion Notes

- Moved the full SEO and metadata section out of the sidebar and into the main editor column directly after Blocks.
- Kept status and taxonomy/media in the side panel.
- Adjusted the SEO section's internal grids to use the wider main-column layout without changing field behavior.
- Validation run: `php artisan test --filter=Post`
