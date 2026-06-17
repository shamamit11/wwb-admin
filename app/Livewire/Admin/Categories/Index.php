<?php

namespace App\Livewire\Admin\Categories;

use App\Services\WideWebBlogApi\Clients\CategoryClient;
use App\Services\WideWebBlogApi\Exceptions\WideWebBlogApiAuthenticationException;
use App\Services\WideWebBlogApi\Exceptions\WideWebBlogApiAuthorizationException;
use App\Services\WideWebBlogApi\Exceptions\WideWebBlogApiException;
use App\Services\WideWebBlogApi\Exceptions\WideWebBlogApiValidationException;
use App\Support\Auth\AdminSessionManager;
use Illuminate\Support\Arr;
use Illuminate\Support\Carbon;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Livewire\Attributes\Url;
use Livewire\Component;

class Index extends Component
{
    #[Url(as: 'q', except: '')]
    public string $search = '';

    #[Url(as: 'status', except: 'all')]
    public string $statusFilter = 'all';

    #[Url(as: 'sort', except: 'sort_order')]
    public string $sortColumn = 'sort_order';

    #[Url(as: 'dir', except: 'asc')]
    public string $sortDirection = 'asc';

    public array $categories = [];

    public bool $drawerOpen = false;

    public ?int $editingCategoryId = null;

    public string $name = '';

    public string $slug = '';

    public string $description = '';

    public string $parentId = '';

    public bool $isActive = true;

    public string $sortOrder = '0';

    public bool $deleteDialogOpen = false;

    public ?int $deleteCategoryId = null;

    public string $deleteCategoryName = '';

    public ?string $pageError = null;

    public ?string $formError = null;

    public function mount(AdminSessionManager $session, CategoryClient $categories): mixed
    {
        return $this->loadCategories($categories, $session);
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:120'],
            'slug' => ['nullable', 'string', 'max:160'],
            'description' => ['nullable', 'string'],
            'parentId' => ['nullable', 'integer', Rule::notIn(array_filter([$this->editingCategoryId]))],
            'isActive' => ['required', 'boolean'],
            'sortOrder' => ['nullable', 'integer', 'min:0'],
        ];
    }

    public function updated(string $property): void
    {
        if (in_array($property, ['name', 'slug', 'description', 'parentId', 'isActive', 'sortOrder'], true)) {
            $this->validateOnly($property);
        }
    }

    public function sortBy(string $column): void
    {
        if (! in_array($column, ['name', 'slug', 'sort_order', 'updated_at', 'is_active'], true)) {
            return;
        }

        if ($this->sortColumn === $column) {
            $this->sortDirection = $this->sortDirection === 'asc' ? 'desc' : 'asc';

            return;
        }

        $this->sortColumn = $column;
        $this->sortDirection = $column === 'updated_at' ? 'desc' : 'asc';
    }

    public function openCreateDrawer(): void
    {
        $this->resetForm();
        $this->drawerOpen = true;
    }

    public function openEditDrawer(int $categoryId, CategoryClient $categories, AdminSessionManager $session): mixed
    {
        $this->resetValidation();
        $this->formError = null;

        try {
            $response = $categories->show($this->token($session), $session->tokenType(), $categoryId);

            $this->fillForm($this->mapCategory(Arr::get($response, 'data', [])));
            $this->drawerOpen = true;

            return null;
        } catch (WideWebBlogApiAuthenticationException) {
            return $this->expireSession($session);
        } catch (WideWebBlogApiAuthorizationException) {
            return $this->forbidden($session);
        } catch (WideWebBlogApiException $exception) {
            $this->pageError = $exception->getMessage() ?: 'Category details could not be loaded.';

            return null;
        }
    }

    public function closeDrawer(): void
    {
        $this->resetForm();
    }

    public function save(CategoryClient $categories, AdminSessionManager $session): mixed
    {
        $validated = $this->validate();
        $payload = $this->categoryPayload($validated);
        $this->formError = null;

        try {
            if ($this->editingCategoryId) {
                $categories->update($this->token($session), $session->tokenType(), $this->editingCategoryId, $payload);
                session()->flash('status', 'Category updated.');
            } else {
                $categories->store($this->token($session), $session->tokenType(), $payload);
                session()->flash('status', 'Category created.');
            }

            $this->loadCategories($categories, $session);
            $this->resetForm();

            return null;
        } catch (WideWebBlogApiValidationException $exception) {
            $this->formError = $exception->getMessage();

            throw ValidationException::withMessages($this->normalizeApiErrors($exception->errors()));
        } catch (WideWebBlogApiAuthenticationException) {
            return $this->expireSession($session);
        } catch (WideWebBlogApiAuthorizationException) {
            return $this->forbidden($session);
        } catch (WideWebBlogApiException $exception) {
            $this->formError = $exception->getMessage() ?: 'Category changes could not be saved.';

            return null;
        }
    }

    public function confirmDelete(int $categoryId): void
    {
        $category = collect($this->categories)->firstWhere('id', $categoryId);

        $this->deleteCategoryId = $categoryId;
        $this->deleteCategoryName = (string) ($category['name'] ?? 'this category');
        $this->deleteDialogOpen = true;
        $this->pageError = null;
    }

    public function cancelDelete(): void
    {
        $this->deleteDialogOpen = false;
        $this->deleteCategoryId = null;
        $this->deleteCategoryName = '';
    }

    public function delete(CategoryClient $categories, AdminSessionManager $session): mixed
    {
        if (! $this->deleteCategoryId) {
            return null;
        }

        try {
            $categories->delete($this->token($session), $session->tokenType(), $this->deleteCategoryId);

            session()->flash('status', 'Category deleted.');

            $deletedCategoryId = $this->deleteCategoryId;

            $this->cancelDelete();
            $this->loadCategories($categories, $session);

            if ($this->editingCategoryId === $deletedCategoryId) {
                $this->resetForm();
            }

            return null;
        } catch (WideWebBlogApiAuthenticationException) {
            return $this->expireSession($session);
        } catch (WideWebBlogApiAuthorizationException) {
            return $this->forbidden($session);
        } catch (WideWebBlogApiException $exception) {
            $this->pageError = $exception->getMessage() ?: 'Category deletion failed.';

            return null;
        }
    }

    public function render()
    {
        return view('livewire.admin.categories.index', [
            'categories' => $this->visibleCategories(),
            'parentOptions' => $this->parentOptions(),
        ])->layout('layouts.admin', [
            'title' => 'Categories',
        ]);
    }

    protected function loadCategories(CategoryClient $categories, AdminSessionManager $session): mixed
    {
        $this->pageError = null;

        try {
            $response = $categories->index($this->token($session), $session->tokenType());
            $this->categories = $this->decorateCategories(
                collect(Arr::get($response, 'data', []))
                    ->map(fn (array $category): array => $this->mapCategory($category))
                    ->values()
                    ->all()
            );
            return null;
        } catch (WideWebBlogApiAuthenticationException) {
            return $this->expireSession($session);
        } catch (WideWebBlogApiAuthorizationException) {
            return $this->forbidden($session);
        } catch (WideWebBlogApiException $exception) {
            $this->categories = [];
            $this->pageError = $exception->getMessage() ?: 'Categories could not be loaded from the service API.';

            return null;
        }
    }

    protected function visibleCategories(): array
    {
        $filtered = collect($this->categories)
            ->filter(function (array $category): bool {
                if ($this->statusFilter === 'active' && ! $category['is_active']) {
                    return false;
                }

                if ($this->statusFilter === 'inactive' && $category['is_active']) {
                    return false;
                }

                if ($this->search === '') {
                    return true;
                }

                $needle = mb_strtolower(trim($this->search));
                $haystack = mb_strtolower(implode(' ', array_filter([
                    $category['name'],
                    $category['slug'],
                    $category['description'],
                    $category['parent_name'],
                ])));

                return str_contains($haystack, $needle);
            });

        return $filtered
            ->sortBy(
                fn (array $category): mixed => $this->sortValue($category),
                SORT_NATURAL,
                $this->sortDirection === 'desc'
            )
            ->values()
            ->all();
    }

    protected function parentOptions(): array
    {
        return collect($this->categories)
            ->reject(fn (array $category): bool => $category['id'] === $this->editingCategoryId)
            ->sortBy(fn (array $category): string => mb_strtolower($category['name']))
            ->values()
            ->all();
    }

    protected function decorateCategories(array $categories): array
    {
        $byId = collect($categories)->keyBy('id');

        return collect($categories)
            ->map(function (array $category) use ($byId): array {
                $parent = $byId->get($category['parent_id']);

                $category['parent_name'] = $parent['name'] ?? null;

                return $category;
            })
            ->values()
            ->all();
    }

    protected function mapCategory(array $category): array
    {
        $updatedAtRaw = Arr::get($category, 'updated_at');

        return [
            'id' => Arr::get($category, 'id'),
            'parent_id' => Arr::get($category, 'parent_id'),
            'name' => Arr::get($category, 'name', 'Untitled category'),
            'slug' => Arr::get($category, 'slug', ''),
            'description' => Arr::get($category, 'description'),
            'is_active' => (bool) Arr::get($category, 'is_active', true),
            'sort_order' => (int) Arr::get($category, 'sort_order', 0),
            'updated_at' => $this->formatTimestamp($updatedAtRaw),
            'updated_at_raw' => $updatedAtRaw,
        ];
    }

    protected function fillForm(array $category): void
    {
        $this->resetValidation();
        $this->editingCategoryId = $category['id'];
        $this->name = $category['name'];
        $this->slug = $category['slug'];
        $this->description = (string) ($category['description'] ?? '');
        $this->parentId = $category['parent_id'] ? (string) $category['parent_id'] : '';
        $this->isActive = (bool) $category['is_active'];
        $this->sortOrder = (string) $category['sort_order'];
    }

    protected function resetForm(): void
    {
        $this->resetValidation();
        $this->drawerOpen = false;
        $this->editingCategoryId = null;
        $this->name = '';
        $this->slug = '';
        $this->description = '';
        $this->parentId = '';
        $this->isActive = true;
        $this->sortOrder = '0';
        $this->formError = null;
    }

    protected function categoryPayload(array $validated): array
    {
        return [
            'name' => trim($validated['name']),
            'slug' => filled($validated['slug']) ? trim($validated['slug']) : null,
            'description' => filled($validated['description']) ? trim($validated['description']) : null,
            'parent_id' => filled($validated['parentId']) ? (int) $validated['parentId'] : null,
            'is_active' => (bool) $validated['isActive'],
            'sort_order' => $validated['sortOrder'] === '' || $validated['sortOrder'] === null ? 0 : (int) $validated['sortOrder'],
        ];
    }

    protected function normalizeApiErrors(array $errors): array
    {
        $mapped = [];

        foreach ($errors as $field => $messages) {
            $property = match ($field) {
                'parent_id' => 'parentId',
                'is_active' => 'isActive',
                'sort_order' => 'sortOrder',
                default => $field,
            };

            $mapped[$property] = $messages;
        }

        return $mapped;
    }

    protected function sortValue(array $category): mixed
    {
        return match ($this->sortColumn) {
            'name' => mb_strtolower($category['name']),
            'slug' => mb_strtolower($category['slug']),
            'updated_at' => $category['updated_at_raw'] ?? '',
            'is_active' => $category['is_active'] ? 0 : 1,
            default => $category['sort_order'],
        };
    }

    protected function formatTimestamp(mixed $value): ?string
    {
        if (! is_string($value) || $value === '') {
            return null;
        }

        try {
            return Carbon::parse($value)->format('M j, Y');
        } catch (\Throwable) {
            return null;
        }
    }

    protected function token(AdminSessionManager $session): string
    {
        return $session->token() ?? '';
    }

    protected function expireSession(AdminSessionManager $session): mixed
    {
        $session->clear();
        request()->session()->invalidate();
        request()->session()->regenerateToken();
        session()->flash('auth.error', 'Your session has expired. Please sign in again.');

        return $this->redirectRoute('login', navigate: true);
    }

    protected function forbidden(AdminSessionManager $session): mixed
    {
        $session->clear();
        session()->flash('auth.error', 'Your account is not authorized for the admin panel.');

        return $this->redirectRoute('auth.forbidden', navigate: true);
    }
}
