# Skill: Post Editor

## When To Use

Use this skill for post create/edit flows, block editing, metadata side panels, and publish-state actions.

## Files To Inspect

- `.agent/UI-UX-RULES.md`
- `.agent/API-CONTRACT.md`
- `docs/UI_UX_GUIDELINES.md`
- `docs/API_INTEGRATION.md`
- `docs/PROJECT_SCOPE.md`

## Implementation Rules

- keep the editor screen center-focused with a sticky metadata/status side panel where practical
- support category, tags, template, featured media, visibility, schedule date, and SEO state
- keep block editing structured and contract-aligned
- use explicit publish, schedule, unpublish, and delete confirmation flows

## Common Mistakes To Avoid

- mixing list-screen concerns into the editor component
- inventing unsupported autosave or workflow behavior
- hiding important publish-state transitions behind ambiguous UI

## Validation Checklist

- create and edit flows preserve form state correctly
- block payloads align to the API contract
- publish, schedule, and unpublish actions are explicit and validated
- confirmation flows are present for destructive changes
