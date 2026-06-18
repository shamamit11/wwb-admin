# Wide Web Blog Admin AI Jobs

## Purpose

The AI Jobs module is the monitoring and retry surface for Service-owned AI workflow execution.

It gives Admin users visibility into job type, status, related entity, generation steps, payloads, and token/cost summaries.

## Routes

- `/ai-jobs`
- `/ai-jobs/{aiJob}`

## Service Client

- `App\Services\WideWebBlogApi\Clients\AiJobClient`

## Supported Actions

### AI Jobs Index

- search jobs
- filter by status
- filter by job type
- filter by related entity type
- sort by timestamps, attempts, and type
- inspect recent failures and execution history

Important note:

- the current Service list endpoint exposes filtering and sorting, but not explicit server-side pagination params
- Admin paginates the returned collection locally

### AI Job Detail

- review job summary and status
- inspect provider and model metadata
- inspect related entity links when Admin can route to them
- inspect generation steps
- inspect input, output, and usage payloads
- inspect cost summaries
- refresh the current job state manually
- retry failed jobs only when the Service marks the job retryable

## Retry Model

Retry remains Service-owned.

Admin:

- does not reconstruct failed workflow state locally
- does not issue provider-level retries
- only calls the Service retry endpoint

When retry is accepted, Admin updates the shown job state and surfaces a success message.

## UI Behavior

- AI job creation handoffs from Topic Queue and Content Briefs can link directly into AI job detail
- failed jobs display error messaging clearly
- job detail exposes `Refresh Status`
- retry actions show disabled/loading states
- entity links should be shown only where Admin has a valid route for the related model

## Guardrails

- Admin must not call AI providers directly
- Admin must not compute token or cost summaries independently
- Admin must treat Service payloads as informational snapshots, not locally mutable workflow state
