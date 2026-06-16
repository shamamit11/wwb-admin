# Wide Web Blog Admin Panel Folder Structure

## Target Structure

```txt
admin/
├── app/
│   ├── Data/
│   ├── Http/
│   │   └── Middleware/
│   ├── Livewire/
│   │   └── Admin/
│   ├── Providers/
│   ├── Services/
│   │   └── WideWebBlogApi/
│   ├── Support/
│   └── View/
│       └── Components/
├── bootstrap/
├── config/
│   └── widewebblog.php
├── docs/
├── public/
├── resources/
│   ├── css/
│   ├── js/
│   └── views/
│       ├── components/
│       │   ├── admin/
│       │   └── ui/
│       ├── layouts/
│       └── livewire/
│           └── admin/
├── routes/
│   ├── web.php
│   └── auth.php
├── storage/
├── tests/
│   ├── Feature/
│   ├── Integration/
│   └── Unit/
└── composer.json
```

## Directory Responsibilities

### `app/Livewire/Admin`

Page and interaction components for the admin UI.

Suggested subfolders:

- `Auth`
- `Dashboard`
- `Categories`
- `Posts`
- `Media`
- `Templates`
- `KnowledgeBase`
- `Seo`
- `Tags`
- `Settings`

### `app/Services/WideWebBlogApi`

All code that talks to the service API.

Suggested structure:

```txt
app/Services/WideWebBlogApi/
├── Clients/
├── Concerns/
├── Exceptions/
├── Requests/
└── Responses/
```

### `app/Data`

Typed request and response helpers, plus lightweight view-model objects where they reduce array misuse.

Suggested grouping by module, not by technical suffix only.

### `app/Support`

Cross-cutting helpers and app-specific infrastructure:

- session token store
- navigation definition
- filter parsing
- string or date helpers
- admin guard helpers

### `app/View/Components`

PHP classes for Blade components when constructor logic or attribute shaping is needed.

### `resources/views/components/ui`

Framework-agnostic visual primitives:

- button
- input
- dialog
- table
- tabs
- badge

### `resources/views/components/admin`

Admin-specific composed presentation elements:

- shell
- sidebar
- page header
- filter toolbar
- stats cards
- row action menu

### `resources/views/livewire/admin`

Blade views for Livewire admin pages. Mirror the namespace structure of `app/Livewire/Admin`.

### `routes/web.php`

All admin routes can live here initially because this app is web-only and internal. Group routes by guest/authenticated middleware and name them consistently under an `admin.` prefix.

## File Naming Guidance

- Livewire classes: `Index.php`, `Create.php`, `Edit.php`, `Show.php` where the pattern is clear
- Blade views: kebab-case matching the component intent
- Service clients: singular resource clients such as `PostClient.php`
- Data objects: explicit names like `StorePostData.php`, `PostFilters.php`, `SeoMetadataData.php`

## Keep Out Of Page Classes

Do not let page components accumulate:

- raw HTTP request construction
- response schema interpretation
- shared status-badge mapping
- duplicate navigation definitions
- large utility methods for unrelated modules

Move those concerns into the dedicated folders above as soon as they repeat.
