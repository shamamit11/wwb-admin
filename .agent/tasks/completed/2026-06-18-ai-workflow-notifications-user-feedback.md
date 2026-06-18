# Task: WB-ADMIN-AI-015 Add AI workflow notifications and user feedback

Status: Completed

## Goal

Improve feedback around long-running AI workflow actions so admins see clear success or failure messages, disabled submitting states, and an easy path to the related AI job.

## Background

- Task source: `WB_ADMIN_AI_WORKFLOW_TASKS.md`
- Scope was limited to the existing Admin AI workflow UI.
- Existing shared feedback pattern was `x-admin.flash-stack`.
- Existing AI actions already flashed simple status strings and sometimes redirected to AI job detail.

## Required Context

- `.agent/skills/laravel-livewire.md`
- `.agent/skills/blade-components.md`
- `.agent/skills/shadcn-inspired-ui.md`
- `.agent/COMPONENT-SYSTEM.md`
- `.agent/UI-UX-RULES.md`

## Files To Inspect

- `WB_ADMIN_AI_WORKFLOW_TASKS.md`
- `resources/views/components/admin/flash-stack.blade.php`
- `resources/views/layouts/admin.blade.php`
- `app/Livewire/Admin/TopicQueue/Index.php`
- `app/Livewire/Admin/ContentBriefs/Show.php`
- `app/Livewire/Admin/AiJobs/Show.php`
- `app/Livewire/Admin/TopicQueue/Show.php`
- `resources/views/livewire/admin/topic-queue/index.blade.php`
- `resources/views/livewire/admin/topic-queue/show.blade.php`
- `resources/views/livewire/admin/content-briefs/show.blade.php`
- `resources/views/livewire/admin/ai-jobs/show.blade.php`

## Files To Change

- `resources/views/components/admin/flash-stack.blade.php`
- `app/Livewire/Admin/TopicQueue/Index.php`
- `app/Livewire/Admin/ContentBriefs/Show.php`
- `app/Livewire/Admin/AiJobs/Show.php`
- `app/Livewire/Admin/TopicQueue/Show.php`
- `resources/views/livewire/admin/topic-queue/index.blade.php`
- `resources/views/livewire/admin/topic-queue/show.blade.php`
- `resources/views/livewire/admin/content-briefs/show.blade.php`
- `resources/views/livewire/admin/ai-jobs/show.blade.php`

## Implementation Steps

1. Extend flash rendering to support richer alert payloads and optional AI job links.
2. Update AI action handlers to flash clearer success and failure messages using the shared structure.
3. Add disabled and loading states to the relevant AI action buttons and confirm actions.
4. Add a manual refresh action for AI job status on the detail screen.
5. Run focused validation and record residual risk.

## Acceptance Criteria

- Admin user gets clear feedback after starting AI actions.
- Admin user can find the related AI job easily.
- Failed actions show useful messages.
- Long-running jobs do not make the UI feel frozen.

## Validation Commands

- `php artisan test --filter=TopicQueue`
- `php artisan test --filter=ContentBrief`
- `php artisan test --filter=AiJob`
- `npm run build`

## Risks

- Existing tests may not cover session flash payload structure changes.
- Some AI actions redirect immediately, so flash messaging must survive redirects cleanly.

## Completion Notes

- Added richer shared flash alerts with optional CTA links for AI jobs and generated briefs.
- Added loading and disabled states across the AI workflow action buttons and confirm actions.
- Added a manual `Refresh Status` action on the AI job detail screen.
- Validation passed:
- `php artisan test tests/Feature/TopicQueue/TopicQueueScreensTest.php tests/Feature/ContentBriefs/ContentBriefScreensTest.php tests/Feature/AiJobs/AiJobScreensTest.php`
- `npm run build` completed successfully, but Vite reported the local Node.js version warning: `22.1.0`; it recommends `20.19+` or `22.12+`.
