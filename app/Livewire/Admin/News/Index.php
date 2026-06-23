<?php

namespace App\Livewire\Admin\News;

use App\Services\WideWebBlogApi\Clients\CategoryClient;
use App\Services\WideWebBlogApi\Clients\NewsItemClient;
use App\Services\WideWebBlogApi\Exceptions\WideWebBlogApiAuthenticationException;
use App\Services\WideWebBlogApi\Exceptions\WideWebBlogApiAuthorizationException;
use App\Services\WideWebBlogApi\Exceptions\WideWebBlogApiException;
use App\Services\WideWebBlogApi\Exceptions\WideWebBlogApiValidationException;
use App\Support\Auth\AdminSessionManager;
use Illuminate\Support\Arr;
use Illuminate\Support\Carbon;
use Illuminate\Validation\ValidationException;
use Livewire\Attributes\Url;
use Livewire\Component;

class Index extends Component
{
    private const STATUSES = ['discovered', 'screened', 'extracted', 'routed', 'ignored', 'failed'];

    private const DECISIONS = ['ignore', 'knowledge_base', 'topic', 'knowledge_base_and_topic'];

    private const ROUTES = ['ignore', 'knowledge_base', 'topic', 'knowledge_base_and_topic'];

    private const PER_PAGE = 10;

    #[Url(as: 'q', except: '')]
    public string $search = '';

    #[Url(as: 'status', except: 'all')]
    public string $statusFilter = 'all';

    #[Url(as: 'category', except: 'all')]
    public string $categoryFilter = 'all';

    #[Url(as: 'decision', except: 'all')]
    public string $decisionFilter = 'all';

    #[Url(as: 'route', except: 'all')]
    public string $routeFilter = 'all';

    #[Url(as: 'sort', except: '-published_at')]
    public string $sort = '-published_at';

    #[Url(as: 'page', except: 1)]
    public int $page = 1;

    public array $items = [];

    public array $categoryOptions = [];

    public bool $discoveryDialogOpen = false;

    public string $discoveryCategoryId = '';

    public string $discoveryLimit = '10';

    public bool $discoverySync = false;

    public ?string $pageError = null;

    public ?string $categoryLoadError = null;

    public ?string $discoveryError = null;

    public function mount(AdminSessionManager $session, NewsItemClient $news, CategoryClient $categories): mixed
    {
        $categoryResult = $this->loadCategories($categories, $session);

        if ($categoryResult !== null) {
            return $categoryResult;
        }

        return $this->loadItems($news, $session);
    }

    public function updated(string $property): void
    {
        if (in_array($property, ['discoveryCategoryId', 'discoveryLimit', 'discoverySync'], true)) {
            $this->validateOnly($property, $this->discoveryRules());

            return;
        }

        if (in_array($property, ['search', 'statusFilter', 'categoryFilter', 'decisionFilter', 'routeFilter'], true)) {
            $this->page = 1;
            $this->refreshItems();
        }
    }

    public function sortBy(string $sort): void
    {
        if (! in_array($sort, ['published_at', '-published_at', 'discovered_at', '-discovered_at', 'created_at', '-created_at', 'updated_at', '-updated_at', 'title', '-title'], true)) {
            return;
        }

        $this->sort = $sort;
        $this->page = 1;
        $this->refreshItems();
    }

    public function previousPage(): void
    {
        if ($this->page > 1) {
            $this->page--;
        }
    }

    public function nextPage(): void
    {
        if ($this->page < $this->lastPage()) {
            $this->page++;
        }
    }

    public function openDiscoveryDialog(): void
    {
        $this->resetValidation();
        $this->discoveryDialogOpen = true;
        $this->discoveryCategoryId = $this->categoryFilter !== 'all' ? $this->categoryFilter : $this->defaultDiscoveryCategoryId();
        $this->discoveryLimit = '10';
        $this->discoverySync = false;
        $this->discoveryError = null;
    }

    public function closeDiscoveryDialog(): void
    {
        $this->resetValidation();
        $this->discoveryDialogOpen = false;
        $this->discoveryError = null;
    }

    public function discoverNews(NewsItemClient $news, AdminSessionManager $session): mixed
    {
        $validated = $this->validate($this->discoveryRules());
        $this->discoveryError = null;

        try {
            $response = $news->discover($this->token($session), $session->tokenType(), [
                'category_id' => (int) $validated['discoveryCategoryId'],
                'limit' => (int) ($validated['discoveryLimit'] ?? 10),
                'sync' => (bool) ($validated['discoverySync'] ?? false),
            ]);

            $this->closeDiscoveryDialog();
            $this->refreshItems();

            if ((bool) ($validated['discoverySync'] ?? false)) {
                $count = count(Arr::get($response, 'data', []));
                session()->flash('status', sprintf('%d %s discovered and loaded for review.', $count, str('item')->plural($count)));
            } else {
                session()->flash('status', 'News discovery queued.');
            }

            return null;
        } catch (WideWebBlogApiValidationException $exception) {
            $this->discoveryError = $exception->getMessage();

            throw ValidationException::withMessages($this->normalizeApiErrors($exception->errors()));
        } catch (WideWebBlogApiAuthenticationException) {
            return $this->expireSession($session);
        } catch (WideWebBlogApiAuthorizationException) {
            return $this->forbidden($session);
        } catch (WideWebBlogApiException $exception) {
            $this->discoveryError = $exception->getMessage() ?: 'News discovery could not be started.';

            return null;
        }
    }

    public function render()
    {
        return view('livewire.admin.news.index', [
            'items' => $this->paginatedItems(),
            'statusOptions' => self::STATUSES,
            'decisionOptions' => self::DECISIONS,
            'routeOptions' => self::ROUTES,
            'pagination' => $this->paginationSummary(),
            'stats' => $this->stats(),
        ])->layout('layouts.admin', [
            'title' => 'News Review',
        ]);
    }

    protected function loadCategories(CategoryClient $categories, AdminSessionManager $session): mixed
    {
        $this->categoryLoadError = null;

        try {
            $response = $categories->index($this->token($session), $session->tokenType());

            $this->categoryOptions = collect(Arr::get($response, 'data', []))
                ->map(fn (array $category): array => [
                    'id' => (int) Arr::get($category, 'id'),
                    'name' => (string) Arr::get($category, 'name', 'Category'),
                    'is_active' => (bool) Arr::get($category, 'is_active', true),
                ])
                ->values()
                ->all();

            return null;
        } catch (WideWebBlogApiAuthenticationException) {
            return $this->expireSession($session);
        } catch (WideWebBlogApiAuthorizationException) {
            return $this->forbidden($session);
        } catch (WideWebBlogApiException $exception) {
            $this->categoryOptions = [];
            $this->categoryLoadError = $exception->getMessage() ?: 'Categories could not be loaded for news discovery.';

            return null;
        }
    }

    protected function loadItems(NewsItemClient $news, AdminSessionManager $session): mixed
    {
        $this->pageError = null;

        try {
            $response = $news->index($this->token($session), $session->tokenType(), $this->filters());

            $this->items = collect(Arr::get($response, 'data', []))
                ->map(fn (array $item): array => $this->mapItem($item))
                ->values()
                ->all();

            $this->page = min(max($this->page, 1), $this->lastPage());

            return null;
        } catch (WideWebBlogApiAuthenticationException) {
            return $this->expireSession($session);
        } catch (WideWebBlogApiAuthorizationException) {
            return $this->forbidden($session);
        } catch (WideWebBlogApiException $exception) {
            $this->items = [];
            $this->pageError = $exception->getMessage() ?: 'News items could not be loaded from the service API.';

            return null;
        }
    }

    protected function refreshItems(): void
    {
        $this->loadItems(app(NewsItemClient::class), app(AdminSessionManager::class));
    }

    protected function filters(): array
    {
        return [
            'search' => trim($this->search) !== '' ? trim($this->search) : null,
            'status' => $this->statusFilter !== 'all' ? $this->statusFilter : null,
            'category_id' => $this->categoryFilter !== 'all' ? (int) $this->categoryFilter : null,
            'decision' => $this->decisionFilter !== 'all' ? $this->decisionFilter : null,
            'route' => $this->routeFilter !== 'all' ? $this->routeFilter : null,
            'sort' => $this->sort,
        ];
    }

    protected function discoveryRules(): array
    {
        return [
            'discoveryCategoryId' => ['required', 'integer'],
            'discoveryLimit' => ['nullable', 'integer', 'min:1', 'max:25'],
            'discoverySync' => ['boolean'],
        ];
    }

    protected function mapItem(array $item): array
    {
        $title = (string) Arr::get($item, 'title', 'Untitled article');
        $score = Arr::get($item, 'latest_score');
        $route = Arr::get($item, 'latest_route');

        return [
            'id' => (int) Arr::get($item, 'id'),
            'title' => $title,
            'description' => (string) Arr::get($item, 'description', ''),
            'publisher_name' => (string) Arr::get($item, 'publisher_name', ''),
            'status' => (string) Arr::get($item, 'status', 'discovered'),
            'published_at' => $this->formatTimestamp(Arr::get($item, 'published_at')),
            'category_name' => (string) Arr::get($item, 'category.name', 'Unassigned'),
            'latest_score_total' => is_numeric(Arr::get($score, 'total_score')) ? number_format((float) Arr::get($score, 'total_score'), 1) : null,
            'latest_score_total_raw' => is_numeric(Arr::get($score, 'total_score')) ? (float) Arr::get($score, 'total_score') : null,
            'latest_decision' => Arr::get($score, 'decision'),
            'latest_route' => Arr::get($route, 'route'),
        ];
    }

    protected function paginatedItems(): array
    {
        return collect($this->items)
            ->forPage($this->page, self::PER_PAGE)
            ->values()
            ->all();
    }

    protected function paginationSummary(): array
    {
        $total = count($this->items);
        $first = $total === 0 ? 0 : (($this->page - 1) * self::PER_PAGE) + 1;
        $last = min($this->page * self::PER_PAGE, $total);

        return [
            'page' => $this->page,
            'last_page' => $this->lastPage(),
            'total' => $total,
            'first_item' => $first,
            'last_item' => $last,
            'has_pages' => $total > self::PER_PAGE,
        ];
    }

    protected function lastPage(): int
    {
        return max(1, (int) ceil(max(count($this->items), 1) / self::PER_PAGE));
    }

    protected function stats(): array
    {
        return [
            [
                'label' => 'Discovered',
                'value' => collect($this->items)->where('status', 'discovered')->count(),
                'tone' => 'warning',
            ],
            [
                'label' => 'Routed',
                'value' => collect($this->items)->where('status', 'routed')->count(),
                'tone' => 'success',
            ],
            [
                'label' => 'Ignored',
                'value' => collect($this->items)->where('latest_decision', 'ignore')->count(),
                'tone' => 'muted',
            ],
        ];
    }

    protected function defaultDiscoveryCategoryId(): string
    {
        $first = collect($this->categoryOptions)->firstWhere('is_active', true);

        return $first ? (string) $first['id'] : '';
    }

    protected function normalizeApiErrors(array $errors): array
    {
        $mapped = [];

        foreach ($errors as $field => $messages) {
            $key = match ($field) {
                'category_id' => 'discoveryCategoryId',
                'limit' => 'discoveryLimit',
                'sync' => 'discoverySync',
                default => $field,
            };

            $mapped[$key] = is_array($messages) ? $messages : [(string) $messages];
        }

        return $mapped;
    }

    protected function formatTimestamp(mixed $value): ?string
    {
        if (! is_string($value) || $value === '') {
            return null;
        }

        try {
            return Carbon::parse($value)->format('M j, Y g:i A');
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
