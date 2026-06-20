<?php

namespace App\Livewire\Admin\Pages;

use App\Services\WideWebBlogApi\Clients\PageClient;
use App\Services\WideWebBlogApi\Exceptions\WideWebBlogApiAuthenticationException;
use App\Services\WideWebBlogApi\Exceptions\WideWebBlogApiAuthorizationException;
use App\Services\WideWebBlogApi\Exceptions\WideWebBlogApiException;
use App\Support\Auth\AdminSessionManager;
use Illuminate\Support\Arr;
use Illuminate\Support\Carbon;
use Livewire\Attributes\Url;
use Livewire\Component;

class Index extends Component
{
    private const PAGE_TYPES = [
        'legal',
        'marketing',
        'support',
        'faq',
        'standard',
    ];

    private const PAGE_STATUSES = [
        'draft',
        'scheduled',
        'published',
        'unpublished',
        'archived',
    ];

    private const PAGE_VISIBILITIES = [
        'public',
        'private',
        'internal',
    ];

    #[Url(as: 'q', except: '')]
    public string $search = '';

    #[Url(as: 'status', except: 'all')]
    public string $statusFilter = 'all';

    #[Url(as: 'type', except: 'all')]
    public string $typeFilter = 'all';

    #[Url(as: 'visibility', except: 'all')]
    public string $visibilityFilter = 'all';

    #[Url(as: 'sort', except: 'updated_at')]
    public string $sortColumn = 'updated_at';

    #[Url(as: 'dir', except: 'desc')]
    public string $sortDirection = 'desc';

    public array $pages = [];

    public bool $deleteDialogOpen = false;

    public ?int $deletePageId = null;

    public string $deletePageTitle = '';

    public ?string $pageError = null;

    public ?string $deleteError = null;

    public function mount(AdminSessionManager $session, PageClient $pages): mixed
    {
        return $this->loadPages($pages, $session);
    }

    public function updated(string $property): void
    {
        if (in_array($property, ['search', 'statusFilter', 'typeFilter', 'visibilityFilter'], true)) {
            $this->refreshPages();
        }
    }

    public function sortBy(string $column): void
    {
        if (! in_array($column, ['title', 'created_at', 'updated_at', 'published_at'], true)) {
            return;
        }

        if ($this->sortColumn === $column) {
            $this->sortDirection = $this->sortDirection === 'asc' ? 'desc' : 'asc';
        } else {
            $this->sortColumn = $column;
            $this->sortDirection = $column === 'title' ? 'asc' : 'desc';
        }

        $this->refreshPages();
    }

    public function confirmDelete(int $pageId): void
    {
        $page = collect($this->pages)->firstWhere('id', $pageId);

        if (! is_array($page)) {
            return;
        }

        $this->deleteDialogOpen = true;
        $this->deletePageId = $pageId;
        $this->deletePageTitle = (string) ($page['title'] ?? 'this page');
        $this->deleteError = null;
    }

    public function closeDeleteDialog(): void
    {
        $this->deleteDialogOpen = false;
        $this->deletePageId = null;
        $this->deletePageTitle = '';
        $this->deleteError = null;
    }

    public function delete(PageClient $pages, AdminSessionManager $session): mixed
    {
        if (! $this->deletePageId) {
            return null;
        }

        $this->deleteError = null;

        try {
            $pages->delete($this->token($session), $session->tokenType(), $this->deletePageId);
            session()->flash('status', 'Page deleted.');
            $this->closeDeleteDialog();

            return $this->loadPages($pages, $session);
        } catch (WideWebBlogApiAuthenticationException) {
            return $this->expireSession($session);
        } catch (WideWebBlogApiAuthorizationException) {
            return $this->forbidden($session);
        } catch (WideWebBlogApiException $exception) {
            $this->deleteError = $exception->getMessage() ?: 'Page could not be deleted.';

            return null;
        }
    }

    public function render()
    {
        return view('livewire.admin.pages.index', [
            'pages' => $this->pages,
            'pageTypes' => self::PAGE_TYPES,
            'pageStatuses' => self::PAGE_STATUSES,
            'pageVisibilities' => self::PAGE_VISIBILITIES,
            'legalPageCards' => $this->legalPageCards(),
        ])->layout('layouts.admin', [
            'title' => 'Pages',
        ]);
    }

    protected function loadPages(PageClient $pages, AdminSessionManager $session): mixed
    {
        $this->pageError = null;

        try {
            $response = $pages->index($this->token($session), $session->tokenType(), $this->filters());

            $this->pages = collect(Arr::get($response, 'data', []))
                ->map(fn (array $page): array => $this->mapPage($page))
                ->values()
                ->all();

            return null;
        } catch (WideWebBlogApiAuthenticationException) {
            return $this->expireSession($session);
        } catch (WideWebBlogApiAuthorizationException) {
            return $this->forbidden($session);
        } catch (WideWebBlogApiException $exception) {
            $this->pages = [];
            $this->pageError = $exception->getMessage() ?: 'Pages could not be loaded.';

            return null;
        }
    }

    protected function refreshPages(): void
    {
        $this->loadPages(app(PageClient::class), app(AdminSessionManager::class));
    }

    protected function filters(): array
    {
        return [
            'search' => trim($this->search) !== '' ? trim($this->search) : null,
            'status' => $this->statusFilter !== 'all' ? $this->statusFilter : null,
            'type' => $this->typeFilter !== 'all' ? $this->typeFilter : null,
            'visibility' => $this->visibilityFilter !== 'all' ? $this->visibilityFilter : null,
            'sort' => $this->apiSort(),
        ];
    }

    protected function apiSort(): string
    {
        return $this->sortDirection === 'desc'
            ? '-'.$this->sortColumn
            : $this->sortColumn;
    }

    protected function mapPage(array $page): array
    {
        return [
            'id' => (int) Arr::get($page, 'id'),
            'title' => (string) Arr::get($page, 'title', 'Untitled page'),
            'slug' => (string) Arr::get($page, 'slug', ''),
            'type' => (string) Arr::get($page, 'type', 'standard'),
            'status' => (string) Arr::get($page, 'status', 'draft'),
            'summary' => Arr::get($page, 'summary'),
            'visibility' => (string) Arr::get($page, 'visibility', 'public'),
            'published_at' => $this->formatTimestamp(Arr::get($page, 'published_at')),
            'updated_at' => $this->formatTimestamp(Arr::get($page, 'updated_at')),
            'created_by_name' => (string) (Arr::get($page, 'created_by.name') ?? ''),
        ];
    }

    protected function legalPageCards(): array
    {
        $definitions = [
            [
                'key' => 'privacy-policy',
                'title' => 'Privacy Policy',
                'slug' => 'privacy-policy',
                'description' => 'Public legal disclosure covering privacy, data handling, and tracking practices.',
            ],
            [
                'key' => 'terms-and-conditions',
                'title' => 'Terms and Conditions',
                'slug' => 'terms-and-conditions',
                'description' => 'Public legal page for usage terms, site rules, and service conditions.',
            ],
        ];

        return collect($definitions)
            ->map(function (array $definition): array {
                $match = collect($this->pages)->first(function (array $page) use ($definition): bool {
                    return $page['type'] === 'legal'
                        && (
                            $page['slug'] === $definition['slug']
                            || strcasecmp($page['title'], $definition['title']) === 0
                        );
                });

                return [
                    'key' => $definition['key'],
                    'title' => $definition['title'],
                    'slug' => $definition['slug'],
                    'description' => $definition['description'],
                    'page' => $match,
                ];
            })
            ->all();
    }

    protected function formatTimestamp(mixed $value): ?string
    {
        if (! is_string($value) || $value === '') {
            return null;
        }

        try {
            return Carbon::parse($value)->format('Y-m-d H:i');
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
