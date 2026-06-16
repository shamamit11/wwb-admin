# Skill: Blade Components

## When To Use

Use this skill when building or updating reusable Blade UI or admin components.

## Files To Inspect

- `.agent/COMPONENT-SYSTEM.md`
- `.agent/UI-UX-RULES.md`
- `docs/COMPONENT_SYSTEM.md`
- `docs/UI_UX_GUIDELINES.md`

## Implementation Rules

- keep primitives reusable and consistent
- use slots and attributes deliberately
- centralize repeated classes and variants
- support accessible labels, focus states, and semantics
- support error, disabled, and loading states where relevant

## Common Mistakes To Avoid

- one-off component variants with no reuse
- icon-only controls without accessible labels
- embedding large business rules into Blade components

## Validation Checklist

- variants render consistently
- accessibility attributes are present
- error and disabled states render correctly
- components compose cleanly in Livewire views
