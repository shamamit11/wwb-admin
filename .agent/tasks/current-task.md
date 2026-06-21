# Task: Improve AI Job Detail Page UI/UX

Status: Completed

## Goal

Improve the AI Job Detail screen so admins can review lifecycle, status, steps, usage, and payload data quickly without changing routes, backend behavior, or removing any debugging payload information.

## Background

The current detail screen is serviceable for debugging but reads too much like a raw payload dump. Large JSON blocks dominate the page, the step section is hard to scan, and lifecycle state is not summarized clearly near the top.

## Required Context

- `.agent/INDEX.md`
- `.agent/MEMORY.md`
- `.agent/agents/SHARED-INSTRUCTIONS.md`
- `.agent/TASK-WORKFLOW.md`
- `.agent/UI-UX-RULES.md`
- `.agent/COMPONENT-SYSTEM.md`
- `.agent/TESTING.md`
- `.agent/skills/laravel-livewire.md`
- `.agent/skills/shadcn-inspired-ui.md`
- `.agent/skills/testing.md`
- `docs/UI_UX_GUIDELINES.md`

## Files To Inspect

- `app/Livewire/Admin/AiJobs/Show.php`
- `resources/views/livewire/admin/ai-jobs/show.blade.php`
- `tests/Feature/AiJobs/AiJobScreensTest.php`

## Files To Change

- `.agent/tasks/current-task.md`
- `app/Livewire/Admin/AiJobs/Show.php`
- `resources/views/livewire/admin/ai-jobs/show.blade.php`
- `tests/Feature/AiJobs/AiJobScreensTest.php` if focused regression coverage is needed

## Implementation Steps

1. Add minimal presenter-style helpers in the Livewire component for lifecycle states, compact payload summaries, and usage formatting.
2. Rework the AI Job detail layout so the summary, lifecycle, token/cost data, and step cards are easy to scan before opening raw JSON.
3. Make top-level and per-step payloads collapsible with simple copy actions while preserving full formatted JSON visibility on demand.
4. Keep retry, refresh, related entity links, and existing payload data intact.
5. Run narrow validation and record any residual risk.

## Acceptance Criteria

- Main payload sections are collapsed or summary-first by default.
- Job summary and lifecycle are easy to scan near the top.
- Generation steps surface status, timing, and compact summaries before raw payload dumps.
- Token and cost usage remain visible but more structured.
- Existing retry, refresh, related entity link, and payload data remain intact.

## Validation Commands

- `php -l app/Livewire/Admin/AiJobs/Show.php`
- `php artisan test --filter=AiJobScreensTest`
- `php artisan view:cache`

## Risks

- Payload summarization must stay generic enough to avoid hiding useful debugging fields or implying domain-specific meaning not present in the payload.
- Collapsible sections should remain lightweight and client-side where possible so the detail screen does not gain unnecessary Livewire mutation complexity.

## Completion Notes

- Added presenter helpers for lifecycle items, compact payload summaries, step summaries, duration labels, and structured token/cost metrics without changing any backend API behavior.
- Reworked the AI Job detail view into an operations-style layout with a top lifecycle strip, a cleaner summary card, summary-first step cards, and structured cost usage cards.
- Converted main payload sections and per-step payloads into collapsible cards with summary text, `View JSON`, and `Copy` actions while keeping full formatted JSON available on demand.
- Removed the default side-by-side raw step payload dump in favor of scan-friendly summaries and expandable debugging detail.
- Validation passed with `php -l app/Livewire/Admin/AiJobs/Show.php`, `php artisan test --filter=AiJobScreensTest`, and `php artisan view:cache`.
