# Task: Fix knowledge base edit ArgumentCountError

Status: Completed

## Goal

Fix the runtime error on the knowledge base edit route.

## Background

The knowledge base edit route is throwing an `ArgumentCountError` in the editor when linked posts and topics are normalized from the API response.

## Required Context

- `.agent/skills/laravel-livewire.md`
- `.agent/skills/forms-validation.md`

## Files To Inspect

- `app/Livewire/Admin/KnowledgeBase/Editor.php`
- `tests/Feature/KnowledgeBase/KnowledgeBaseEditorTest.php`

## Files To Change

- `app/Livewire/Admin/KnowledgeBase/Editor.php`
- `tests/Feature/KnowledgeBase/KnowledgeBaseEditorTest.php`

## Implementation Steps

1. Replace the invalid collection filter callback in the editor.
2. Run focused knowledge base editor validation.

## Acceptance Criteria

- [x] Knowledge base edit screen loads without the runtime error

## Validation Commands

- `php artisan test tests/Feature/KnowledgeBase/KnowledgeBaseEditorTest.php`

## Validation Results

- `php artisan test tests/Feature/KnowledgeBase/KnowledgeBaseEditorTest.php`

## Risks

- None beyond the exact linked item response shape.

## Completion Notes

- Replaced the invalid string callback in knowledge base linked-item normalization with explicit array checks.
- Added test coverage for mixed-shape `linked_posts` and `linked_topics` payloads so the edit route is protected against this failure.
