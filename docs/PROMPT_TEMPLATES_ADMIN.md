# Wide Web Blog Admin Prompt Templates

## Purpose

The Prompt Templates module manages prompt definitions used by future Service-side AI generations.

This module is operational, not editorial content publishing. Changes here affect future workflow runs, not already completed generations.

## Routes

- `/ai-prompts`
- `/ai-prompts/create`
- `/ai-prompts/{aiPrompt}`

## Service Client

- `App\Services\WideWebBlogApi\Clients\AiPromptClient`

## Supported Actions

### Prompt Template Index

- search prompt templates
- filter by prompt type
- filter by status
- inspect active version metadata
- create a new prompt template

### Prompt Template Detail

- update prompt metadata
- review version history
- create a new version
- activate a specific version

## Editing Model

Prompt template editing stays contract-aligned and pragmatic.

Current approach:

- metadata fields are regular form inputs
- `variables` are handled as a simple line-based list
- `output_schema` is handled as JSON editing where needed

Admin should not invent richer typed builders unless the Service contract explicitly supports them.

## Activation Semantics

Version activation affects future generations only.

It does not:

- rewrite historical AI jobs
- mutate stored outputs from previous runs
- retroactively change already generated briefs or drafts

## UI Behavior

- prompt template create and update actions use standard flash feedback
- version creation and activation surface success messages
- version lists make the active version explicit
- screens should warn that changes affect future AI generations only

## Guardrails

- Admin must not treat prompt templates as direct provider prompts executed locally
- prompt version management remains a Service-backed administrative action
- prompt changes must not imply automatic regeneration of existing content
