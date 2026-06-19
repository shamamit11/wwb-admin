# Wide Web Blog Admin AI Workflow

## Purpose

This document describes the AI workflow implemented in the Admin application.

Admin is an editorial client for Service-owned AI workflows. It does not run AI providers directly, does not own orchestration logic, and does not persist workflow state locally beyond the Laravel session used for authenticated API access.

## Core Rules

- Admin consumes Service APIs only.
- Admin must not call AI providers directly.
- Admin must not duplicate Service-side retry or orchestration logic.
- Admin users must review workflow outputs before promoting them to the next stage.
- AI-generated posts remain drafts until a human publishes them.
- Image suggestions are advisory only in MVP. Image creation, selection, and placement remain manual.

## Workflow Sequence

```txt
Knowledge Base / Editorial Context
    ↓
Run Topic Discovery
    ↓
Review Suggested Topics
    ↓
Approve Topic
    ↓
Generate Content Brief
    ↓
Review / Edit / Approve Brief
    ↓
Generate Draft Post
    ↓
Manual Draft Review
    ↓
Manual Publish
```

## Implemented Admin Modules

### Topic Queue

- Routes:
  - `/topic-queue`
  - `/topic-queue/{topic}`
- Purpose:
  - review suggested topics
  - update topic metadata
  - approve, reject, and mark-used state transitions
  - start topic discovery jobs
  - generate content briefs from approved topics

See [TOPIC_QUEUE_ADMIN.md](./TOPIC_QUEUE_ADMIN.md).

### Content Briefs

- Routes:
  - `/content-briefs`
  - `/content-briefs/{contentBrief}`
- Purpose:
  - review structured brief content from the Service
  - edit brief fields and suggestion payloads
  - approve briefs
  - generate blog draft jobs from approved briefs

Notes:

- Draft generation is still Service-owned.
- Admin provides required inputs such as `category_id`, optional `template_id`, visibility, and optional prompt key override.

### Prompt Templates

- Routes:
  - `/ai-prompts`
  - `/ai-prompts/create`
  - `/ai-prompts/{aiPrompt}`
- Purpose:
  - manage prompt template metadata
  - review version history
  - create new prompt versions
  - activate a specific version for future generations

See [PROMPT_TEMPLATES_ADMIN.md](./PROMPT_TEMPLATES_ADMIN.md).

### AI Jobs

- Routes:
  - `/ai-jobs`
  - `/ai-jobs/{aiJob}`
- Purpose:
  - monitor workflow execution
  - inspect job payloads, lifecycle state, generation steps, and cost summaries
  - retry failed jobs through the Service API

See [AI_JOBS_ADMIN.md](./AI_JOBS_ADMIN.md).

## Navigation

Admin exposes these workflow modules under the `AI Content` navigation section:

- Topic Queue
- Content Briefs
- Prompt Templates
- AI Jobs

## API Boundary

The AI workflow is backed by module-specific Service clients:

- `ContentTopicClient`
- `ContentBriefClient`
- `AiPromptClient`
- `AiJobClient`

Livewire screens call these clients. They should not issue raw `Http::` calls directly.

## Implemented Feedback and Handoffs

- Long-running AI actions show loading and disabled button states.
- AI job creation actions flash success messages and can include direct links to the related AI job detail screen.
- Topic-to-brief generation can link straight to the generated brief.
- AI job detail includes a manual `Refresh Status` action and `Retry Failed Job` when the Service marks the job retryable.

## Current Contract Gap

`WB-ADMIN-AI-009` draft review remains only partially represented in Admin documentation terms.

Current constraint:

- the documented Admin post contract does not expose AI-only provenance fields or a dedicated AI-draft review filter

Practical result:

- Admin can generate draft posts from approved briefs
- Admin dashboard can show general draft-post review volume
- Admin cannot yet provide a clean AI-only draft review screen without broader contract support

## Related Docs

- [TOPIC_QUEUE_ADMIN.md](./TOPIC_QUEUE_ADMIN.md)
- [AI_JOBS_ADMIN.md](./AI_JOBS_ADMIN.md)
- [PROMPT_TEMPLATES_ADMIN.md](./PROMPT_TEMPLATES_ADMIN.md)
- [API_INTEGRATION.md](./API_INTEGRATION.md)
