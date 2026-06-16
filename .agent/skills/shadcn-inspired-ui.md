# Skill: Shadcn-Inspired UI

## When To Use

Use this skill when translating shadcn/ui patterns into Blade and Tailwind for the admin panel.

## Files To Inspect

- `.agent/COMPONENT-SYSTEM.md`
- `.agent/UI-UX-RULES.md`
- `docs/COMPONENT_SYSTEM.md`
- `docs/UI_UX_GUIDELINES.md`

## Implementation Rules

- treat shadcn/ui as a visual and interaction reference only
- translate patterns into Blade component naming and Tailwind classes
- keep visual consistency across cards, forms, tables, tabs, sidebar, badges, and dialogs
- prefer restrained publishing-dashboard styling over generic CMS heaviness
- do not introduce React components

## Common Mistakes To Avoid

- copying React patterns directly
- over-decorated UI that conflicts with the publishing-focused spec
- inconsistent spacing, border, or state conventions across components

## Validation Checklist

- UI matches the established admin tone
- components feel consistent across screens
- dialogs, forms, and tables follow shared interaction rules
- no React-specific implementation assumptions were introduced
