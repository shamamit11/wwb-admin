# Skill: Media Upload

## When To Use

Use this skill for media library screens, single or batch upload flows, and media metadata editing.

## Files To Inspect

- `.agent/API-CONTRACT.md`
- `.agent/UI-UX-RULES.md`
- `docs/API_INTEGRATION.md`
- `docs/UI_UX_GUIDELINES.md`
- `docs/TESTING.md`

## Implementation Rules

- support single upload and batch upload against the documented media endpoints
- build multipart requests in the API client layer
- expose progress or loading feedback
- support alt text, caption, source type, source URL, and attribution metadata
- surface usage count before delete when available

## Common Mistakes To Avoid

- mixing upload transport logic into Blade views
- ignoring multipart specifics for batch uploads
- deleting media without clear safety messaging

## Validation Checklist

- single upload request shape is correct
- batch upload request shape is correct
- metadata updates persist correctly
- delete flow shows confirmation and usage context where available
