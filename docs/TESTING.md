# Wide Web Blog Admin Panel Testing

## Testing Goal

Verify that the admin panel behaves correctly as a UI client over the service API contract. The most important failures to catch are broken admin flows, invalid API integration assumptions, inconsistent validation mapping, and regressions in editorial workflows.

## Test Layers

### Unit Tests

Use unit tests for:

- data objects
- payload builders
- filter/sort parameter builders
- status or badge mapping helpers
- token/session support classes
- API exception translation logic

### Integration Tests

Use integration-style tests for the API client layer:

- fake service responses with Laravel HTTP fakes
- verify request methods, paths, headers, and payload shape
- verify multipart upload request construction
- verify error translation for `401`, `403`, `404`, `422`, and `500`

### Feature Tests

Use feature tests for admin screens and full Livewire interactions:

- guest redirect behavior
- login and logout flows
- dashboard load
- CRUD screens
- publish/schedule/unpublish flows
- validation display
- destructive action confirmation behavior

## Core Feature Coverage

### Authentication

- login succeeds with valid credentials
- invalid credentials show generic auth error
- expired session redirects to login
- unauthorized admin access is blocked
- logout clears session state

### Categories

- list loads
- create and edit forms map API validation errors correctly
- delete flow requires confirmation

### Posts

- index filters/sort/search state persists
- create draft works
- update draft works
- publish action works
- schedule action requires date-time
- unpublish action works
- block validation errors are shown correctly

### Media

- single upload sends multipart payload
- batch upload sends multipart payload
- metadata edit works
- delete respects confirmation flow

### Templates

- create/update template works
- block ordering payload is correct
- preview endpoint output renders safely
- seed-post action is wired correctly

### Knowledge Base

- create and edit entry works
- markdown content field is preserved
- link-to-post and link-to-topic actions call the correct endpoints

### SEO

- metadata get/update works
- score fetch renders correctly
- schema fetch renders correctly
- sitemap and RSS inspection views load

## API Contract Assumptions To Test

- login request uses service field names exactly
- service validation error shape maps cleanly into Livewire
- bearer token is attached to authenticated requests
- token removal happens on `401`
- route/model identifiers are passed exactly as expected by the service

## Test Data Strategy

- prefer fixtures or factory-like arrays based on OpenAPI resource shapes
- keep reusable response fixtures per module
- cover both minimal and realistic payloads
- include edge cases for null optional fields and empty arrays

## Tooling Recommendations

- PHPUnit or Pest for the main test runner
- Laravel HTTP fake utilities for service client tests
- Livewire testing helpers for component behavior
- snapshot testing only where it adds signal, not for every view fragment

## What Not To Over-Test Early

- raw Tailwind class strings
- every visual permutation of a primitive
- service business logic already owned by the backend

The admin test suite should focus on client correctness, UX-critical state transitions, and contract fidelity.
