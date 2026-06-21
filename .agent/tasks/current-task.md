# Task: Fix Content Briefs Pagination and Improve Table Readability

Status: Completed

## Goal

Fix the Content Briefs index pagination so it follows the service-backed admin pattern and improve the table layout so editors can scan titles, topics, and metadata without clipping or cramped rows.

## Background

The current screen exposes search, filters, sorting, summary cards, and a pagination footer, but pagination behavior is not aligned with the API-backed listing pattern and the table hierarchy is visually crowded. The first content column also appears clipped on the left in the current layout.

## Required Context

- `.agent/INDEX.md`
- `.agent/MEMORY.md`
- `.agent/agents/SHARED-INSTRUCTIONS.md`
- `.agent/TASK-WORKFLOW.md`
- `.agent/API-CONTRACT.md`
- `.agent/UI-UX-RULES.md`
- `.agent/skills/laravel-livewire.md`
- `.agent/skills/blade-components.md`
- `.agent/skills/tables-filters-pagination.md`
- `.agent/skills/api-client-integration.md`
- `docs/API_INTEGRATION.md`
- `docs/UI_UX_GUIDELINES.md`

## Files To Inspect

- `app/Livewire/Admin/ContentBriefs/Index.php`
- `resources/views/livewire/admin/content-briefs/index.blade.php`
- `app/Services/WideWebBlogApi/Clients/ContentBriefClient.php`
- `resources/views/components/ui/pagination.blade.php`
- `tests/Feature/ContentBriefs/ContentBriefScreensTest.php`

## Files To Change

- `.agent/tasks/current-task.md`
- `app/Livewire/Admin/ContentBriefs/Index.php`
- `resources/views/livewire/admin/content-briefs/index.blade.php`
- `tests/Feature/ContentBriefs/ContentBriefScreensTest.php` if focused regression coverage is needed

## Implementation Steps

1. Confirm the current API-backed pagination pattern and the response shape used by content briefs.
2. Replace local table slicing with service-backed pagination state and keep search, filters, and sorting aligned with query-string state.
3. Adjust the table column widths and cell content hierarchy to remove left clipping and improve scanability.
4. Add narrow regression coverage for pagination wiring and filter reset behavior.
5. Run narrow validation and record residual risks.

## Acceptance Criteria

- The table renders only the current page of briefs and Next/Previous navigate correctly.
- Search and filters reset to page 1 and remain consistent with pagination metadata.
- The first content column is no longer clipped.
- Title/topic/keyword content is easier to scan without making rows excessively tall.
- Shared row actions and existing routes/actions remain intact.

## Validation Commands

- `php -l app/Livewire/Admin/ContentBriefs/Index.php`
- `php artisan test --filter=ContentBriefScreensTest`
- `php artisan view:cache`

## Risks

- The service pagination metadata may differ from the implicit conventions used elsewhere, so the component should preserve a safe fallback when metadata is incomplete.
- Table density changes must stay aligned with existing admin table patterns and avoid destabilizing shared dropdown behavior.

## Completion Notes

- Replaced local `forPage()` slicing with service-backed pagination state by sending `page` and `per_page` to the content briefs API, storing API pagination metadata, and refreshing data on Previous and Next actions.
- Kept a safe fallback for pagination totals and page bounds when the service omits some metadata, while preferring `meta.current_page`, `meta.last_page`, `meta.total`, `meta.from`, and `meta.to` when present.
- Reworked the table so the first column becomes a wider `Brief` column that carries title, slug, and topic context together, reducing the crowded multi-column layout that caused poor scanability and left-edge clipping pressure.
- Tightened metadata columns for keyword, intent, status, created, approved, and actions so important editorial context stays visible without overly tall rows.
- Added focused regression coverage for service pagination rendering, page navigation, and filter reset behavior.
- Validation passed with `php -l app/Livewire/Admin/ContentBriefs/Index.php`, `php artisan test --filter=ContentBriefScreensTest`, and `php artisan view:cache`.
