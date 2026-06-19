# Wide Web Blog Admin — AI Workflow Integration Tasks

## Purpose

This document defines the Admin-side implementation tasks for connecting the **Wide Web Blog Admin application** to the completed **Wide Web Blog Service AI Content Engine**.

The Service application already provides the backend foundation for:

- AI Jobs
- AI Generation Steps
- AI token/cost tracking
- Prompt Templates and Prompt Versions
- Topic Queue
- Topic Discovery
- Content Briefs
- Blog Draft Generation
- Knowledge Base context retrieval
- AI workflow orchestration
- Laravel MCP support

The Admin application already has:

- Knowledge Base CRUD implemented
- Topic Queue placeholder
- AI Jobs placeholder

The next step is to replace placeholders with real Admin screens and connect them to the Service APIs.

---

## Core Admin Flow

```txt
Knowledge Base
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
Review Draft Post
    ↓
Manual Publish
```

---

## Admin Rules

- This phase is Admin application only.
- Do not implement Service-side business logic in Admin.
- Admin must consume Service APIs only.
- Do not call AI providers directly from Admin.
- Do not auto-publish AI-generated content.
- AI-generated posts must remain draft until manually published.
- Admin user must review and approve topics, briefs, and posts.
- Images remain manual in this MVP phase.
- Admin may display image ideas, image placement notes, and alt text suggestions.
- Admin must show AI job status clearly.
- Admin must support retrying failed jobs only through Service API.
- Admin must not duplicate retry logic locally.
- Keep UI professional, clean, and suitable for an editorial dashboard.

---

## Suggested Admin Navigation

Add or complete the following Admin sections:

```txt
AI Content
├── Topic Queue
├── Content Briefs
├── Draft Review
├── AI Jobs
└── Prompt Templates
```

Knowledge Base may remain as an existing separate module or be linked inside AI Content.

---

# Phase 4 — Admin AI Workflow Integration Tasks

---

## WB-ADMIN-AI-001 — Connect Admin API client to Service AI endpoints

**Phase:** Phase 4 — Admin AI Workflow Integration  
**Story Points:** `5`  
**Priority:** Must Have  
**Status:** Backlog

### Description

Create or extend the Admin API client layer so the Admin application can consume the completed Service AI APIs.

### Deliverables

- API client methods for AI Jobs
- API client methods for Topic Queue
- API client methods for Content Briefs
- API client methods for Prompt Templates
- API client methods for draft generation
- Centralized error handling for AI workflow responses
- Loading and empty state support

### Suggested Files / Areas

```txt
app/Services/
app/Clients/
app/Livewire/
resources/views/
config/services.php
```

Adjust paths according to the existing Admin repository structure.

### Required API Areas

```txt
AI Jobs
Topic Queue
Content Briefs
Prompt Templates
Draft Generation
```

### Acceptance Criteria

- Admin uses a centralized Service API client.
- Admin does not duplicate Service business rules.
- API errors are displayed in a user-friendly way.
- API base URL and auth configuration are environment-based.
- Existing Admin patterns are followed.

### Validation

```bash
php artisan test
npm run build
```

---

## WB-ADMIN-AI-002 — Implement Topic Queue listing page

**Phase:** Phase 4 — Admin AI Workflow Integration  
**Story Points:** `8`  
**Priority:** Must Have  
**Status:** Backlog

### Description

Replace the Topic Queue placeholder with a real listing page that displays topics returned by the Service API.

### Deliverables

- Topic Queue page
- Table/list layout
- Search/filter support
- Status filter
- Cluster filter
- Pagination
- Topic detail link/action
- Empty state
- Loading state

### Suggested Columns

```txt
Title
Cluster
Primary Keyword
Search Intent
Priority Score
Status
Source
Created At
Actions
```

### Suggested Filters

```txt
status: suggested, approved, rejected, used
cluster: ai_tools, ai_for_blogging, seo, content_marketing, productivity_automation, developer_ai
source: ai, manual
```

### Acceptance Criteria

- Admin can view suggested, approved, rejected, and used topics.
- Admin can filter topics by status and cluster.
- Admin can search topics by title or keyword.
- Page uses existing Admin UI components and layout.
- Placeholder content is removed.

### Validation

```bash
php artisan test
npm run build
```

---

## WB-ADMIN-AI-003 — Implement Topic Queue detail and status actions

**Phase:** Phase 4 — Admin AI Workflow Integration  
**Story Points:** `8`  
**Priority:** Must Have  
**Status:** Backlog

### Description

Allow Admin users to review individual topic suggestions and approve, reject, edit, or mark them as used.

### Deliverables

- Topic detail drawer/page/modal
- Topic edit form
- Approve topic action
- Reject topic action
- Mark as used action
- Status transition confirmation
- Success/error notifications

### Suggested Fields

```txt
Title
Slug
Cluster
Primary Keyword
Secondary Keywords
Search Intent
Priority Score
Difficulty Note
Notes
Status
Approved At
Rejected At
Used At
```

### Acceptance Criteria

- Admin can review full topic details.
- Admin can approve suggested topics.
- Admin can reject unsuitable topics.
- Admin can edit topic metadata before approval.
- Admin cannot generate a content brief from unapproved topics.
- Status actions call Service APIs.

### Validation

```bash
php artisan test
npm run build
```

---

## WB-ADMIN-AI-004 — Implement Run Topic Discovery action

**Phase:** Phase 4 — Admin AI Workflow Integration  
**Story Points:** `8`  
**Priority:** Must Have  
**Status:** Backlog

### Description

Allow Admin users to manually start AI topic discovery from the Admin UI.

### Deliverables

- Run Topic Discovery button/action
- Topic discovery form
- Cluster selector
- Target count input
- Audience/context input if supported by API
- Job creation response handling
- Link to AI Job detail
- Clear warning that topics are suggestions only

### Suggested Form Fields

```txt
Cluster
Target Count
Audience
Optional Notes
```

### Acceptance Criteria

- Admin can trigger topic discovery through Service API.
- Topic discovery creates an AI job.
- Admin can navigate to the related AI job status.
- Generated topics are saved as suggested only.
- Admin UI does not auto-approve topics.

### Validation

```bash
php artisan test
npm run build
```

---

## WB-ADMIN-AI-005 — Implement Content Briefs listing page

**Phase:** Phase 4 — Admin AI Workflow Integration  
**Story Points:** `8`  
**Priority:** Must Have  
**Status:** Backlog

### Description

Create an Admin page for reviewing AI-generated content briefs.

### Deliverables

- Content Briefs listing page
- Status filter
- Search support
- Pagination
- Topic relationship display
- Action buttons
- Empty/loading states

### Suggested Columns

```txt
Title
Topic
Primary Keyword
Search Intent
Status
Created At
Approved At
Actions
```

### Statuses

```txt
draft
approved
rejected
used
```

### Acceptance Criteria

- Admin can view all content briefs.
- Admin can filter briefs by status.
- Admin can open brief details for review.
- Admin can see which topic generated the brief.
- Page follows existing Admin layout and components.

### Validation

```bash
php artisan test
npm run build
```

---

## WB-ADMIN-AI-006 — Implement Generate Content Brief flow

**Phase:** Phase 4 — Admin AI Workflow Integration  
**Story Points:** `8`  
**Priority:** Must Have  
**Status:** Backlog

### Description

Allow Admin users to generate a content brief from an approved topic.

### Deliverables

- Generate Brief action on approved topic
- Confirmation modal
- API integration
- Job response handling
- Link to AI Jobs page/detail
- Disable action for unapproved topics

### Acceptance Criteria

- Generate Brief action is available only for approved topics.
- Service API handles actual generation.
- Admin shows job created/in-progress state.
- Admin does not create content brief data locally.
- Admin can navigate to the generated brief once available.

### Validation

```bash
php artisan test
npm run build
```

---

## WB-ADMIN-AI-007 — Implement Content Brief detail, edit, approve, and reject screen

**Phase:** Phase 4 — Admin AI Workflow Integration  
**Story Points:** `13`  
**Priority:** Must Have  
**Status:** Backlog

### Description

Create a professional editorial review screen for content briefs.

### Deliverables

- Content brief detail screen
- Editable brief fields
- Outline display/editor
- Heading structure display/editor
- FAQ suggestions display/editor
- Internal link suggestions display
- Image ideas display
- Alt text suggestions display
- Approve brief action
- Reject brief action
- Save changes action

### Suggested Sections

```txt
Brief Overview
SEO Metadata
Outline
Heading Structure
FAQ Suggestions
Internal Link Suggestions
Image Ideas
Alt Text Suggestions
Review Actions
```

### Acceptance Criteria

- Admin can review full structured brief.
- Admin can edit brief content before approval.
- Admin can approve or reject draft briefs.
- Image ideas are shown as suggestions only.
- Approved briefs can be used for draft generation.
- Rejected briefs cannot generate drafts.

### Validation

```bash
php artisan test
npm run build
```

---

## WB-ADMIN-AI-008 — Implement Generate Blog Draft flow

**Phase:** Phase 4 — Admin AI Workflow Integration  
**Story Points:** `8`  
**Priority:** Must Have  
**Status:** Backlog

### Description

Allow Admin users to generate a draft blog post from an approved content brief.

### Deliverables

- Generate Draft action on approved brief
- Confirmation modal
- API integration
- Job response handling
- Link to AI Job detail
- Link to generated draft post when available
- Disable action for unapproved/rejected briefs

### Acceptance Criteria

- Generate Draft action is available only for approved briefs.
- Draft generation runs through Service API.
- Generated post is saved as draft.
- Admin UI clearly states that manual review is required before publish.
- Retry/status is handled through AI Jobs flow.

### Validation

```bash
php artisan test
npm run build
```

---

## WB-ADMIN-AI-009 — Implement Draft Review page for AI-generated posts

**Phase:** Phase 4 — Admin AI Workflow Integration  
**Story Points:** `13`  
**Priority:** Must Have  
**Status:** Backlog

### Description

Create a review experience for AI-generated draft posts before manual publishing.

### Deliverables

- AI Draft Review listing
- Draft detail/review screen
- Source topic display
- Source content brief display
- SEO metadata display/edit
- Content/body display/edit using existing post editor pattern
- FAQ suggestions display
- Suggested tags display
- Image placement notes display
- Alt text suggestions display
- Manual publish path using existing post publishing flow

### Suggested Sections

```txt
Draft Overview
Source Topic
Source Content Brief
Generated Content
SEO Metadata
Suggested Tags
FAQ Suggestions
Image Placement Notes
Alt Text Suggestions
Manual Review Actions
```

### Acceptance Criteria

- Admin can review AI-generated draft posts.
- Admin can edit generated content before publishing.
- Admin can see source topic and brief context.
- Admin can manually publish using existing post workflow.
- AI generation never bypasses manual review.

### Validation

```bash
php artisan test
npm run build
```

---

## WB-ADMIN-AI-010 — Implement AI Jobs listing page

**Phase:** Phase 4 — Admin AI Workflow Integration  
**Story Points:** `8`  
**Priority:** Must Have  
**Status:** Backlog

### Description

Replace the AI Jobs placeholder with a real job monitoring page.

### Deliverables

- AI Jobs listing page
- Status filter
- Type filter
- Provider/model display
- Entity type/entity link
- Started/completed/failed timestamps
- Pagination
- Empty/loading states

### Suggested Columns

```txt
Job ID
Type
Status
Entity
Provider
Model
Started At
Completed At
Failed At
Actions
```

### Suggested Filters

```txt
status: pending, running, completed, failed
job type: topic_discovery, content_brief, blog_draft
provider
model
```

### Acceptance Criteria

- Admin can view AI job history.
- Admin can filter jobs by status and type.
- Admin can see failed jobs clearly.
- Admin can open job details.
- Placeholder content is removed.

### Validation

```bash
php artisan test
npm run build
```

---

## WB-ADMIN-AI-011 — Implement AI Job detail page with generation steps

**Phase:** Phase 4 — Admin AI Workflow Integration  
**Story Points:** `13`  
**Priority:** Must Have  
**Status:** Backlog

### Description

Create a detailed AI job view showing job lifecycle, agent steps, payload summaries, errors, and cost/token usage.

### Deliverables

- AI Job detail page
- Job summary card
- Generation steps timeline
- Input payload viewer
- Output payload viewer
- Error message display
- Token/cost usage display
- Related entity links
- Retry failed job action

### Suggested Sections

```txt
Job Summary
Status Timeline
Generation Steps
Token & Cost Usage
Input Payload
Output Payload
Errors
Related Entity
Actions
```

### Acceptance Criteria

- Admin can inspect AI job execution details.
- Admin can see every generation step.
- Admin can see token/cost usage when available.
- Failed jobs show clear error information.
- Retry action is only available for failed jobs.
- Retry calls Service API and does not duplicate local logic.

### Validation

```bash
php artisan test
npm run build
```

---

## WB-ADMIN-AI-012 — Implement Prompt Templates listing page

**Phase:** Phase 4 — Admin AI Workflow Integration  
**Story Points:** `8`  
**Priority:** Should Have  
**Status:** Backlog

### Description

Create an Admin page for viewing and managing AI prompt templates.

### Deliverables

- Prompt Templates listing page
- Type filter
- Status filter
- Active version display
- Search support
- Pagination
- Create/edit actions

### Suggested Columns

```txt
Name
Key
Type
Status
Active Version
Updated At
Actions
```

### Prompt Types

```txt
topic_discovery
content_brief
blog_writer
editor
seo_optimizer
publishing
```

### Acceptance Criteria

- Admin can view prompt templates.
- Admin can filter prompts by type and status.
- Admin can see active prompt version.
- Admin can open prompt template detail screen.

### Validation

```bash
php artisan test
npm run build
```

---

## WB-ADMIN-AI-013 — Implement Prompt Template detail and version management

**Phase:** Phase 4 — Admin AI Workflow Integration  
**Story Points:** `13`  
**Priority:** Should Have  
**Status:** Backlog

### Description

Allow Admin users to manage prompt templates and prompt versions safely.

### Deliverables

- Prompt template detail page
- Prompt metadata edit form
- Prompt version list
- Create new version form
- System prompt editor
- User prompt editor
- Output schema editor/viewer
- Variables editor/viewer
- Activate version action
- Version status indicator

### Suggested Sections

```txt
Template Details
Active Version
Version History
System Prompt
User Prompt
Output Schema
Variables
Actions
```

### Acceptance Criteria

- Admin can create and edit prompt templates.
- Admin can create prompt versions.
- Admin can activate a specific prompt version.
- Only one active version is shown as active.
- Prompt editing does not affect already completed AI jobs.
- UI clearly warns that prompt changes affect future generations only.

### Validation

```bash
php artisan test
npm run build
```

---

## WB-ADMIN-AI-014 — Add AI workflow dashboard cards

**Phase:** Phase 4 — Admin AI Workflow Integration  
**Story Points:** `5`  
**Priority:** Should Have  
**Status:** Backlog

### Description

Add useful AI workflow summary cards to the Admin dashboard or AI Content dashboard.

### Deliverables

- Suggested topics count
- Approved topics count
- Draft briefs count
- Approved briefs count
- Draft posts pending review count
- Failed AI jobs count
- Recent AI jobs list

### Acceptance Criteria

- Admin can quickly see AI workflow status.
- Failed jobs are highlighted.
- Drafts pending human review are visible.
- Cards link to the relevant pages.
- Dashboard uses existing Admin design system.

### Validation

```bash
php artisan test
npm run build
```

---

## WB-ADMIN-AI-015 — Add AI workflow notifications and user feedback

**Phase:** Phase 4 — Admin AI Workflow Integration  
**Story Points:** `5`  
**Priority:** Should Have  
**Status:** Backlog

### Description

Improve user feedback around long-running AI operations.

### Deliverables

- Toast/alert for job created
- Toast/alert for failed API action
- Clear disabled states while submitting
- Polling or refresh action for job status if existing Admin pattern supports it
- Link from success notification to AI Job detail

### Acceptance Criteria

- Admin user gets clear feedback after starting AI actions.
- Admin user can find the related AI job easily.
- Failed actions show useful messages.
- Long-running jobs do not make the UI feel frozen.

### Validation

```bash
php artisan test
npm run build
```

---

## WB-ADMIN-AI-016 — Add Admin AI workflow documentation

**Phase:** Phase 4 — Admin AI Workflow Integration  
**Story Points:** `3`  
**Priority:** Must Have  
**Status:** Backlog

### Description

Update Admin documentation so future agents understand the AI workflow UI and API integration.

### Deliverables

- Admin AI workflow documentation
- Topic Queue admin documentation
- AI Jobs admin documentation
- Prompt Template admin documentation
- Update agent memory/context docs if present

### Suggested Files

```txt
docs/AI_WORKFLOW_ADMIN.md
docs/TOPIC_QUEUE_ADMIN.md
docs/AI_JOBS_ADMIN.md
docs/PROMPT_TEMPLATES_ADMIN.md
.agent/MEMORY.md
.agent/skills/ai-admin-workflow.md
.agent/tasks/current-task.md
```

### Acceptance Criteria

- Documentation explains Admin AI workflow clearly.
- Documentation states that Admin consumes Service APIs.
- Documentation states that Admin must not call AI providers directly.
- Documentation states that publishing remains manual.
- Documentation explains placeholder replacement work.

### Validation

```bash
php artisan test
npm run build
```

---

# MVP Build Order

Use this order for the coding agent:

```txt
1. WB-ADMIN-AI-001 — Connect Admin API client to Service AI endpoints
2. WB-ADMIN-AI-002 — Implement Topic Queue listing page
3. WB-ADMIN-AI-003 — Implement Topic Queue detail and status actions
4. WB-ADMIN-AI-004 — Implement Run Topic Discovery action
5. WB-ADMIN-AI-010 — Implement AI Jobs listing page
6. WB-ADMIN-AI-011 — Implement AI Job detail page with generation steps
7. WB-ADMIN-AI-005 — Implement Content Briefs listing page
8. WB-ADMIN-AI-006 — Implement Generate Content Brief flow
9. WB-ADMIN-AI-007 — Implement Content Brief detail, edit, approve, and reject screen
10. WB-ADMIN-AI-008 — Implement Generate Blog Draft flow
11. WB-ADMIN-AI-009 — Implement Draft Review page for AI-generated posts
12. WB-ADMIN-AI-012 — Implement Prompt Templates listing page
13. WB-ADMIN-AI-013 — Implement Prompt Template detail and version management
14. WB-ADMIN-AI-014 — Add AI workflow dashboard cards
15. WB-ADMIN-AI-015 — Add AI workflow notifications and user feedback
16. WB-ADMIN-AI-016 — Add Admin AI workflow documentation
```

---

# Coding Agent Guardrails

```md
## Admin AI Workflow Rules

- This is the Laravel Admin application only.
- Do not implement Service-side AI logic in Admin.
- Do not call AI providers directly from Admin.
- Consume Service APIs through the existing API/client pattern.
- Do not implement Livewire components in the Service repository.
- Follow existing Admin layout, components, and page patterns.
- Keep screens professional, clean, and easy to navigate.
- Topic discovery creates suggested topics only.
- Only approved topics can generate content briefs.
- Only approved content briefs can generate draft posts.
- AI-generated posts must remain draft until manually published.
- Never auto-publish AI-generated content.
- Images are manual in MVP.
- Show AI image ideas, placement notes, and alt text as suggestions only.
- Retry failed AI jobs only through Service API.
- Do not duplicate retry or workflow logic in Admin.
- Show clear loading, empty, success, and error states.
- Add tests for API client behavior, Livewire/components, status actions, and page rendering.
```

---

# First Coding Agent Prompt

```md
# Task: Connect Admin Topic Queue and AI Jobs to Wide Web Blog Service AI APIs

Act as a senior Laravel 13 + Livewire admin engineer and product-focused UI developer.

You are working inside the `admin` repository of Wide Web Blog.

The Service application has completed the AI Content Engine implementation.
The Admin application already has:

- Knowledge Base CRUD implemented
- Topic Queue placeholder
- AI Jobs placeholder

Now replace the placeholders with real API-driven Admin screens.

## Task Scope

Implement the first Admin integration slice:

1. Connect Admin API client to Service AI endpoints
2. Replace Topic Queue placeholder with real listing page
3. Add Topic Queue filters and pagination
4. Add Topic detail/review action
5. Add approve/reject/mark-used actions
6. Replace AI Jobs placeholder with real listing page
7. Add AI Job detail page with generation steps
8. Add retry action for failed jobs if Service API supports it
9. Add loading, empty, success, and error states
10. Add tests for the implemented Admin behavior

## Important Rules

- This is Admin-only.
- Do not modify the Service repository.
- Do not implement AI provider calls in Admin.
- Do not duplicate Service workflow logic.
- Use the existing Admin API/client/service pattern.
- Follow existing Admin UI layout and component conventions.
- Keep the UI clean, professional, and editorial-dashboard friendly.
- Do not auto-publish AI-generated content.
- Retry failed jobs only through the Service API.

## Pages / Sections

Create or complete:

- AI Content > Topic Queue
- AI Content > AI Jobs
- AI Job Detail
- Topic Detail / Review

## Expected UI Behavior

Topic Queue:

- List topics
- Filter by status
- Filter by cluster
- Search by title or keyword
- View topic details
- Approve topic
- Reject topic
- Mark topic as used

AI Jobs:

- List jobs
- Filter by status
- Filter by type
- View job details
- View generation steps
- View error messages
- Retry failed jobs when supported

## Validation

Run:

php artisan test
npm run build

## Completion Notes

After implementation, update the task file with:

- changed files
- API endpoints used
- screens/components added
- tests added
- commands run
- risks/TBC items
```
