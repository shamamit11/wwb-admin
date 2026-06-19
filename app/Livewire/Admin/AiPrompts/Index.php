<?php

namespace App\Livewire\Admin\AiPrompts;

use App\Services\WideWebBlogApi\Clients\AiPromptClient;
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
    private const PROMPT_TYPES = [
        'topic_discovery',
        'content_brief',
        'blog_writer',
        'editor',
        'seo_optimizer',
        'publishing',
    ];

    private const PROMPT_STATUSES = [
        'draft',
        'active',
        'archived',
    ];

    private const PER_PAGE = 10;

    #[Url(as: 'q', except: '')]
    public string $search = '';

    #[Url(as: 'type', except: 'all')]
    public string $typeFilter = 'all';

    #[Url(as: 'status', except: 'all')]
    public string $statusFilter = 'all';

    #[Url(as: 'sort', except: '-updated_at')]
    public string $sort = '-updated_at';

    #[Url(as: 'page', except: 1)]
    public int $page = 1;

    public array $prompts = [];

    public ?string $pageError = null;

    public function mount(AdminSessionManager $session, AiPromptClient $prompts): mixed
    {
        return $this->loadPrompts($prompts, $session);
    }

    public function updated(string $property): void
    {
        if (in_array($property, ['search', 'typeFilter', 'statusFilter'], true)) {
            $this->page = 1;
            $this->refreshPrompts();
        }
    }

    public function sortBy(string $sort): void
    {
        if (! in_array($sort, ['name', '-name', 'key', '-key', 'type', '-type', 'created_at', '-created_at', 'updated_at', '-updated_at'], true)) {
            return;
        }

        $this->sort = $sort;
        $this->page = 1;
        $this->refreshPrompts();
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
        return view('livewire.admin.ai-prompts.index', [
            'prompts' => $this->paginatedPrompts(),
            'typeOptions' => self::PROMPT_TYPES,
            'statusOptions' => self::PROMPT_STATUSES,
            'pagination' => $this->paginationSummary(),
            'stats' => $this->stats(),
        ])->layout('layouts.admin', [
            'title' => 'Prompt Templates',
        ]);
    }

    protected function loadPrompts(AiPromptClient $prompts, AdminSessionManager $session): mixed
    {
        $this->pageError = null;

        try {
            $response = $prompts->index($this->token($session), $session->tokenType(), $this->promptFilters());

            $this->prompts = collect(Arr::get($response, 'data', []))
                ->map(fn (array $prompt): array => $this->mapPrompt($prompt))
                ->values()
                ->all();

            $this->page = min(max($this->page, 1), $this->lastPage());

            return null;
        } catch (WideWebBlogApiAuthenticationException) {
            return $this->expireSession($session);
        } catch (WideWebBlogApiAuthorizationException) {
            return $this->forbidden($session);
        } catch (WideWebBlogApiException $exception) {
            $this->prompts = [];
            $this->pageError = $exception->getMessage() ?: 'AI prompt templates could not be loaded from the service API.';

            return null;
        }
    }

    protected function refreshPrompts(): void
    {
        $this->loadPrompts(app(AiPromptClient::class), app(AdminSessionManager::class));
    }

    protected function promptFilters(): array
    {
        return [
            'search' => trim($this->search) !== '' ? trim($this->search) : null,
            'type' => $this->typeFilter !== 'all' ? $this->typeFilter : null,
            'status' => $this->statusFilter !== 'all' ? $this->statusFilter : null,
            'sort' => $this->sort,
        ];
    }

    protected function mapPrompt(array $prompt): array
    {
        $activeVersion = Arr::get($prompt, 'active_version');

        return [
            'id' => Arr::get($prompt, 'id'),
            'name' => Arr::get($prompt, 'name', 'Untitled prompt'),
            'key' => Arr::get($prompt, 'key', ''),
            'type' => Arr::get($prompt, 'type', ''),
            'description' => Arr::get($prompt, 'description'),
            'status' => Arr::get($prompt, 'status', 'draft'),
            'active_version_id' => Arr::get($prompt, 'active_version_id'),
            'versions_count' => (int) Arr::get($prompt, 'versions_count', count(Arr::get($prompt, 'versions', []))),
            'active_version_number' => is_array($activeVersion) ? Arr::get($activeVersion, 'version') : null,
            'updated_at' => $this->formatTimestamp(Arr::get($prompt, 'updated_at')),
            'created_at' => $this->formatTimestamp(Arr::get($prompt, 'created_at')),
        ];
    }

    protected function paginatedPrompts(): array
    {
        return collect($this->prompts)
            ->forPage($this->page, self::PER_PAGE)
            ->values()
            ->all();
    }

    protected function paginationSummary(): array
    {
        $total = count($this->prompts);
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
        return max(1, (int) ceil(max(count($this->prompts), 1) / self::PER_PAGE));
    }

    protected function stats(): array
    {
        return [
            [
                'label' => 'Active Prompts',
                'value' => collect($this->prompts)->where('status', 'active')->count(),
                'tone' => 'success',
            ],
            [
                'label' => 'Draft Prompts',
                'value' => collect($this->prompts)->where('status', 'draft')->count(),
                'tone' => 'warning',
            ],
            [
                'label' => 'Archived Prompts',
                'value' => collect($this->prompts)->where('status', 'archived')->count(),
                'tone' => 'muted',
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
