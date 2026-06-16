# Agent Memory

This memory belongs to `widewebblog/admin`.

Store only stable, reusable admin knowledge here. Do not write temporary task notes, speculative ideas, or one-off debugging details.

## Stable Project Knowledge

- This repository is the admin panel for Wide Web Blog.
- Stack: Laravel 13, Livewire, Blade, Tailwind CSS.
- The repository now contains a bootstrapped Laravel 13 application with Livewire 4 installed.
- The UI system is shadcn/ui-inspired Blade and Livewire-compatible components.
- This is not a React or Next.js application.
- The service API owns business logic and persistence.
- The admin consumes the service API.
- Admin auth uses a server-side Laravel session bridge for bearer token, token type, abilities, and current-user state.
- The OpenAPI contract is the source of truth for API endpoints and payloads.
- The admin UX is publishing-focused rather than generic CMS-heavy.
- MVP modules are auth, dashboard, categories, tags, media, templates, posts, SEO, and knowledge base.
- Future modules include topic queue, AI jobs, and advanced settings.
- Agents must avoid unrelated refactors and unrelated improvements.
- Agents should work task-first and avoid broad repository scanning.

## Update Policy

Add entries only when all are true:

- the fact is likely to remain true across multiple tasks
- it reduces repeated context loading
- it affects implementation, architecture, or validation decisions

Do not add:

- temporary task details
- unresolved questions
- speculative architecture
- partial implementation details likely to change soon
