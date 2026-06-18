# Wide Web Blog Admin Topic Queue

## Purpose

The Topic Queue is the first editorial review surface in the Admin AI workflow.

It allows editors to review suggested topics returned by the Service, adjust topic metadata, change topic status, trigger topic discovery, and generate content briefs from approved topics.

## Routes

- `/topic-queue`
- `/topic-queue/{topic}`

## Service Client

- `App\Services\WideWebBlogApi\Clients\ContentTopicClient`
- `App\Services\WideWebBlogApi\Clients\AiJobClient` for topic discovery

## Supported Actions

### Topic Queue Index

- search topics
- filter by status
- filter by cluster
- filter by source
- sort by title, priority score, and timestamps
- run topic discovery from a confirmation dialog

Important note:

- the current Service list endpoint exposes filtering and sorting, but not explicit server-side pagination params
- Admin therefore paginates the returned collection locally for UI consistency

### Topic Review

- edit topic metadata
- approve topic
- reject topic
- mark topic as used
- generate a content brief from an approved topic

## Topic Discovery

Topic discovery is a Service-side AI job started from the Topic Queue index.

Admin collects:

- cluster
- target count
- audience
- optional prompt template key
- optional metadata tags

Admin then calls the Service topic discovery endpoint and, when the response includes a job id, redirects to the related AI job detail screen.

Topic discovery creates suggested topics only. It does not auto-approve topics and does not bypass editorial review.

## Status Rules

The Topic Queue UI currently works with these statuses:

- `suggested`
- `approved`
- `rejected`
- `used`

Expected flow:

- suggested topics can be approved or rejected
- approved topics can be marked used and can generate briefs
- rejected topics can be revisited and approved later

## UI Behavior

- topic discovery uses an explicit confirmation dialog
- topic transitions use an explicit confirmation dialog
- topic review exposes structured editing fields without inventing Service-side business rules
- long-running actions show loading and disabled states
- job creation success can include a direct `Open AI Job` link

## Guardrails

- Admin must not generate topics directly with an AI provider
- Admin must not create local retry logic for topic discovery
- brief generation remains downstream and requires an approved topic
- topic suggestions are editorial inputs, not publishable outputs
