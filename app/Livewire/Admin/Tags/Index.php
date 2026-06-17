<?php

namespace App\Livewire\Admin\Tags;

use App\Services\WideWebBlogApi\Clients\TagClient;
use App\Services\WideWebBlogApi\Exceptions\WideWebBlogApiAuthenticationException;
use App\Services\WideWebBlogApi\Exceptions\WideWebBlogApiAuthorizationException;
use App\Services\WideWebBlogApi\Exceptions\WideWebBlogApiException;
use App\Services\WideWebBlogApi\Exceptions\WideWebBlogApiValidationException;
use App\Support\Auth\AdminSessionManager;
use Illuminate\Support\Arr;
use Illuminate\Validation\ValidationException;
use Livewire\Attributes\Url;
use Livewire\Component;

class Index extends Component
{
    #[Url(as: 'q', except: '')]
    public string $search = '';

    #[Url(as: 'status', except: 'all')]
    public string $statusFilter = 'all';

    #[Url(as: 'sort', except: 'name')]
    public string $sortColumn = 'name';

    #[Url(as: 'dir', except: 'asc')]
    public string $sortDirection = 'asc';

    public array $tags = [];

    public bool $drawerOpen = false;

    public ?int $editingTagId = null;

    public string $name = '';

    public string $slug = '';

    public string $description = '';

    public bool $isActive = true;

    public bool $deleteDialogOpen = false;

    public ?int $deleteTagId = null;

    public string $deleteTagName = '';

    public ?string $pageError = null;

    public ?string $formError = null;

    public function mount(AdminSessionManager $session, TagClient $tags): mixed
    {
        return $this->loadTags($tags, $session);
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:100'],
            'slug' => ['nullable', 'string', 'max:140'],
            'description' => ['nullable', 'string'],
            'isActive' => ['required', 'boolean'],
        ];
    }

    public function updated(string $property): void
    {
        if (in_array($property, ['name', 'slug', 'description', 'isActive'], true)) {
            $this->validateOnly($property);
        }
    }

    public function sortBy(string $column): void
    {
        if (! in_array($column, ['name', 'slug', 'is_active'], true)) {
            return;
        }

        if ($this->sortColumn === $column) {
            $this->sortDirection = $this->sortDirection === 'asc' ? 'desc' : 'asc';

            return;
        }

        $this->sortColumn = $column;
        $this->sortDirection = 'asc';
    }

    public function openCreateDrawer(): void
    {
        $this->resetForm();
        $this->drawerOpen = true;
    }

    public function openEditDrawer(int $tagId, TagClient $tags, AdminSessionManager $session): mixed
    {
        $this->resetValidation();
        $this->formError = null;

        try {
            $response = $tags->show($this->token($session), $session->tokenType(), $tagId);

            $this->fillForm($this->mapTag(Arr::get($response, 'data', [])));
            $this->drawerOpen = true;

            return null;
        } catch (WideWebBlogApiAuthenticationException) {
            return $this->expireSession($session);
        } catch (WideWebBlogApiAuthorizationException) {
            return $this->forbidden($session);
        } catch (WideWebBlogApiException $exception) {
            $this->pageError = $exception->getMessage() ?: 'Tag details could not be loaded.';

            return null;
        }
    }

    public function closeDrawer(): void
    {
        $this->resetForm();
    }

    public function save(TagClient $tags, AdminSessionManager $session): mixed
    {
        $validated = $this->validate();
        $payload = $this->tagPayload($validated);
        $this->formError = null;

        try {
            if ($this->editingTagId) {
                $tags->update($this->token($session), $session->tokenType(), $this->editingTagId, $payload);
                session()->flash('status', 'Tag updated.');
            } else {
                $tags->store($this->token($session), $session->tokenType(), $payload);
                session()->flash('status', 'Tag created.');
            }

            $this->loadTags($tags, $session);
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
            $this->formError = $exception->getMessage() ?: 'Tag changes could not be saved.';

            return null;
        }
    }

    public function confirmDelete(int $tagId): void
    {
        $tag = collect($this->tags)->firstWhere('id', $tagId);

        $this->deleteTagId = $tagId;
        $this->deleteTagName = (string) ($tag['name'] ?? 'this tag');
        $this->deleteDialogOpen = true;
        $this->pageError = null;
    }

    public function cancelDelete(): void
    {
        $this->deleteDialogOpen = false;
        $this->deleteTagId = null;
        $this->deleteTagName = '';
    }

    public function delete(TagClient $tags, AdminSessionManager $session): mixed
    {
        if (! $this->deleteTagId) {
            return null;
        }

        try {
            $tags->delete($this->token($session), $session->tokenType(), $this->deleteTagId);

            session()->flash('status', 'Tag deleted.');

            $deletedTagId = $this->deleteTagId;

            $this->cancelDelete();
            $this->loadTags($tags, $session);

            if ($this->editingTagId === $deletedTagId) {
                $this->resetForm();
            }

            return null;
        } catch (WideWebBlogApiAuthenticationException) {
            return $this->expireSession($session);
        } catch (WideWebBlogApiAuthorizationException) {
            return $this->forbidden($session);
        } catch (WideWebBlogApiException $exception) {
            $this->pageError = $exception->getMessage() ?: 'Tag deletion failed.';

            return null;
        }
    }

    public function render()
    {
        return view('livewire.admin.tags.index', [
            'tags' => $this->visibleTags(),
        ])->layout('layouts.admin', [
            'title' => 'Tags',
        ]);
    }

    protected function loadTags(TagClient $tags, AdminSessionManager $session): mixed
    {
        $this->pageError = null;

        try {
            $response = $tags->index($this->token($session), $session->tokenType());
            $this->tags = collect(Arr::get($response, 'data', []))
                ->map(fn (array $tag): array => $this->mapTag($tag))
                ->values()
                ->all();

            return null;
        } catch (WideWebBlogApiAuthenticationException) {
            return $this->expireSession($session);
        } catch (WideWebBlogApiAuthorizationException) {
            return $this->forbidden($session);
        } catch (WideWebBlogApiException $exception) {
            $this->tags = [];
            $this->pageError = $exception->getMessage() ?: 'Tags could not be loaded from the service API.';

            return null;
        }
    }

    protected function visibleTags(): array
    {
        $filtered = collect($this->tags)
            ->filter(function (array $tag): bool {
                if ($this->statusFilter === 'active' && ! $tag['is_active']) {
                    return false;
                }

                if ($this->statusFilter === 'inactive' && $tag['is_active']) {
                    return false;
                }

                if ($this->search === '') {
                    return true;
                }

                $needle = mb_strtolower(trim($this->search));
                $haystack = mb_strtolower(implode(' ', array_filter([
                    $tag['name'],
                    $tag['slug'],
                    $tag['description'],
                ])));

                return str_contains($haystack, $needle);
            });

        return $filtered
            ->sortBy(
                fn (array $tag): mixed => $this->sortValue($tag),
                SORT_NATURAL,
                $this->sortDirection === 'desc'
            )
            ->values()
            ->all();
    }

    protected function mapTag(array $tag): array
    {
        return [
            'id' => Arr::get($tag, 'id'),
            'name' => Arr::get($tag, 'name', 'Untitled tag'),
            'slug' => Arr::get($tag, 'slug', ''),
            'description' => Arr::get($tag, 'description'),
            'is_active' => (bool) Arr::get($tag, 'is_active', true),
        ];
    }

    protected function fillForm(array $tag): void
    {
        $this->resetValidation();
        $this->editingTagId = $tag['id'];
        $this->name = $tag['name'];
        $this->slug = $tag['slug'];
        $this->description = (string) ($tag['description'] ?? '');
        $this->isActive = (bool) $tag['is_active'];
    }

    protected function resetForm(): void
    {
        $this->resetValidation();
        $this->drawerOpen = false;
        $this->editingTagId = null;
        $this->name = '';
        $this->slug = '';
        $this->description = '';
        $this->isActive = true;
        $this->formError = null;
    }

    protected function tagPayload(array $validated): array
    {
        return [
            'name' => trim($validated['name']),
            'slug' => filled($validated['slug']) ? trim($validated['slug']) : null,
            'description' => filled($validated['description']) ? trim($validated['description']) : null,
            'is_active' => (bool) $validated['isActive'],
        ];
    }

    protected function normalizeApiErrors(array $errors): array
    {
        $mapped = [];

        foreach ($errors as $field => $messages) {
            $property = match ($field) {
                'is_active' => 'isActive',
                default => $field,
            };

            $mapped[$property] = $messages;
        }

        return $mapped;
    }

    protected function sortValue(array $tag): mixed
    {
        return match ($this->sortColumn) {
            'slug' => mb_strtolower($tag['slug']),
            'is_active' => $tag['is_active'] ? 0 : 1,
            default => mb_strtolower($tag['name']),
        };
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
