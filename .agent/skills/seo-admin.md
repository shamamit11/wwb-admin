# Skill: SEO Admin

## When To Use

Use this skill for SEO metadata editing, score display, schema viewing, and sitemap or RSS operational screens.

## Files To Inspect

- `.agent/API-CONTRACT.md`
- `.agent/UI-UX-RULES.md`
- `docs/API_INTEGRATION.md`
- `docs/UI_UX_GUIDELINES.md`

## Implementation Rules

- use the documented SEO endpoints as the source of truth
- support metadata fields, robots flags, focus keyword, and OpenGraph image selection where the API allows it
- keep schema, sitemap, and RSS views operational and readable
- avoid turning SEO screens into overly technical raw-data dumps

## Common Mistakes To Avoid

- inventing sitewide SEO settings not yet backed by the service
- conflating per-entity SEO metadata with future global defaults
- hiding low-score or missing-metadata signals

## Validation Checklist

- metadata fetch and update behavior is correct
- score display handles missing or partial data safely
- schema, sitemap, and RSS screens render meaningful read-only output
- unsupported global SEO features are marked `TBC` or placeholder-only
