# Skill: Forms And Validation

## When To Use

Use this skill for Livewire forms, inline validation, submit validation, and API validation mapping.

## Files To Inspect

- `.agent/UI-UX-RULES.md`
- `.agent/API-CONTRACT.md`
- `docs/UI_UX_GUIDELINES.md`
- `docs/API_INTEGRATION.md`
- `docs/TESTING.md`

## Implementation Rules

- validate obvious field issues inline where appropriate
- run full validation on submit
- map service `422` errors into Livewire messages
- keep a global error banner for submit or transport failures
- preserve dirty input where possible after errors

## Common Mistakes To Avoid

- swallowing the top-level API validation message
- losing nested field errors for blocks or repeaters
- blocking all interaction with unnecessary full-page loaders

## Validation Checklist

- field errors appear beside the correct inputs
- submit failures show a clear global error state
- dirty/loading states are sensible
- nested or array validation errors are preserved
