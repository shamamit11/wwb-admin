<?php

namespace App\Livewire\Admin\ContentBriefs;

use App\Services\WideWebBlogApi\Clients\ContentBriefClient;
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
    private const BRIEF_STATUSES = [
        'draft',
        'approved',
        'rejected',
        'used',
    ];

    private const PER_PAGE = 10;

    #[Url(as: 'q', except: '')]
    public string $search = '';

    #[Url(as: 'status', except: 'all')]
    public string $statusFilter = 'all';

    #[Url(as: 'topic', except: '')]
    public string $topicFilter = '';

    #[Url(as: 'sort', except: '-created_at')]
    public string $sort = '-created_at';

    #[Url(as: 'page', except: 1)]
    public int $page = 1;

    public array $briefs = [];

    public ?string $pageError = null;

    public function mount(AdminSessionManager $session, ContentBriefClient $briefs): mixed
    {
        return $this->loadBriefs($briefs, $session);
    }

    public function updated(string $property): void
    {
        if (in_array($property, ['search', 'statusFilter', 'topicFilter'], true)) {
            $this->page = 1;
            $this->refreshBriefs();
        }
    }

    public function sortBy(string $sort): void
    {
        if (! in_array($sort, ['created_at', '-created_at', 'updated_at', '-updated_at', 'approved_at', '-approved_at', 'title', '-title'], true)) {
            return;
        }

        $this->sort = $sort;
        $this->page = 1;
        $this->refreshBriefs();
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

    public function render()
    {
        return view('livewire.admin.content-briefs.index', [
            'briefs' => $this->paginatedBriefs(),
            'statusOptions' => self::BRIEF_STATUSES,
            'pagination' => $this->paginationSummary(),
            'stats' => $this->stats(),
        ])->layout('layouts.admin', [
            'title' => 'Content Briefs',
        ]);
    }

    protected function loadBriefs(ContentBriefClient $briefs, AdminSessionManager $session): mixed
    {
        $this->pageError = null;

        try {
            $response = $briefs->index($this->token($session), $session->tokenType(), $this->briefFilters());

            $this->briefs = collect(Arr::get($response, 'data', []))
                ->map(fn (array $brief): array => $this->mapBrief($brief))
                ->values()
                ->all();

            $this->page = min(max($this->page, 1), $this->lastPage());

            return null;
        } catch (WideWebBlogApiAuthenticationException) {
            return $this->expireSession($session);
        } catch (WideWebBlogApiAuthorizationException) {
            return $this->forbidden($session);
        } catch (WideWebBlogApiException $exception) {
            $this->briefs = [];
            $this->pageError = $exception->getMessage() ?: 'Content briefs could not be loaded from the service API.';

            return null;
        }
    }

    protected function refreshBriefs(): void
    {
        $this->loadBriefs(app(ContentBriefClient::class), app(AdminSessionManager::class));
    }

    protected function briefFilters(): array
    {
        return [
            'search' => trim($this->search) !== '' ? trim($this->search) : null,
            'status' => $this->statusFilter !== 'all' ? $this->statusFilter : null,
            'content_topic_id' => filled($this->topicFilter) ? (int) $this->topicFilter : null,
            'sort' => $this->sort,
        ];
    }

    protected function mapBrief(array $brief): array
    {
        return [
            'id' => (int) Arr::get($brief, 'id', 0),
            'content_topic_id' => (int) Arr::get($brief, 'content_topic_id', 0),
            'title' => (string) Arr::get($brief, 'title', 'Untitled brief'),
            'slug' => (string) Arr::get($brief, 'slug', ''),
            'primary_keyword' => Arr::get($brief, 'primary_keyword'),
            'search_intent' => Arr::get($brief, 'search_intent'),
            'status' => (string) Arr::get($brief, 'status', 'draft'),
            'can_generate_draft' => (bool) Arr::get($brief, 'can_generate_draft', false),
            'created_at' => $this->formatTimestamp(Arr::get($brief, 'created_at')),
            'approved_at' => $this->formatTimestamp(Arr::get($brief, 'approved_at')),
            'topic' => [
                'id' => (int) Arr::get($brief, 'topic.id', Arr::get($brief, 'content_topic_id', 0)),
                'title' => Arr::get($brief, 'topic.title'),
                'slug' => Arr::get($brief, 'topic.slug'),
                'cluster' => Arr::get($brief, 'topic.cluster'),
                'status' => Arr::get($brief, 'topic.status'),
            ],
        ];
    }

    protected function paginatedBriefs(): array
    {
        return collect($this->briefs)
            ->forPage($this->page, self::PER_PAGE)
            ->values()
            ->all();
    }

    protected function paginationSummary(): array
    {
        $total = count($this->briefs);
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
        return max(1, (int) ceil(max(count($this->briefs), 1) / self::PER_PAGE));
    }

    protected function stats(): array
    {
        return [
            [
                'label' => 'Draft Briefs',
                'value' => collect($this->briefs)->where('status', 'draft')->count(),
            ],
            [
                'label' => 'Approved Briefs',
                'value' => collect($this->briefs)->where('status', 'approved')->count(),
            ],
            [
                'label' => 'Used Briefs',
                'value' => collect($this->briefs)->where('status', 'used')->count(),
            ],
        ];
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
