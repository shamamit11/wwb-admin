# Admin Folder Structure Rules

## Recommended Locations

- Livewire admin pages: `app/Livewire/Admin`
- API clients and related support: `app/Services/WideWebBlogApi`
- data or DTO classes: `app/Data`
- Blade UI components: `resources/views/components/ui`
- admin composite components: `resources/views/components/admin`
- layouts: `resources/views/layouts`
- Livewire views: `resources/views/livewire/admin`
- tests: `tests/Feature`, `tests/Integration`, `tests/Unit`
- config: `config/`
- durable project docs: `docs/`
- agent workflow files: `.agent/`

## Folder Guardrails

- do not invent new root folders without checking this file first
- if a new folder is needed, update this document and the architecture docs
- prefer the documented structure over ad hoc placement

Read `docs/FOLDER_STRUCTURE.md` before structural work.
