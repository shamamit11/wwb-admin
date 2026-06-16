# Skill: Tables, Filters, And Pagination

## When To Use

Use this skill for management screens with index tables, search, filters, sort, and pagination.

## Files To Inspect

- `.agent/UI-UX-RULES.md`
- `.agent/API-CONTRACT.md`
- `docs/UI_UX_GUIDELINES.md`
- `docs/API_INTEGRATION.md`

## Implementation Rules

- keep search, filter, sort, and page state explicit
- prefer query-string-backed state for management pages
- keep row actions on the right
- provide clear empty states and loading skeletons
- only add bulk actions when they are clearly useful

## Common Mistakes To Avoid

- filters that are not reflected in the URL when they should be
- inconsistent column sorting rules
- empty states hidden below the table instead of replacing it

## Validation Checklist

- search, filters, sort, and pagination work together
- row actions remain accessible
- empty state renders correctly
- loading and error states are clear
