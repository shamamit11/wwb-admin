<?php

namespace App\Livewire\Admin\TopicQueue;

use App\Services\WideWebBlogApi\Clients\AiJobClient;
use App\Services\WideWebBlogApi\Clients\CategoryClient;
use App\Services\WideWebBlogApi\Clients\ContentTopicClient;
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
    private const REVIEW_THRESHOLD = 70.0;

    private const AUTO_DRAFT_THRESHOLD = 85.0;

    private const TOPIC_STATUSES = ['suggested', 'approved', 'rejected', 'used'];

    private const TOPIC_CLUSTERS = ['ai_tools', 'ai_for_blogging', 'seo', 'content_marketing', 'productivity_automation', 'developer_ai'];

    private const TOPIC_SOURCES = ['manual', 'ai_suggested'];

    private const PER_PAGE = 10;

    #[Url(as: 'q', except: '')]
    public string $search = '';

    #[Url(as: 'status', except: 'all')]
    public string $statusFilter = 'all';

    #[Url(as: 'cluster', except: 'all')]
    public string $clusterFilter = 'all';

    #[Url(as: 'category', except: 'all')]
    public string $categoryFilter = 'all';

    #[Url(as: 'source', except: 'all')]
    public string $sourceFilter = 'all';

    #[Url(as: 'sort', except: '-created_at')]
    public string $sort = '-created_at';

    #[Url(as: 'page', except: 1)]
    public int $page = 1;

    public array $topics = [];

    public bool $discoveryDialogOpen = false;

    public string $discoveryCategoryId = '';

    public string $discoveryCount = '10';

    public string $discoveryAudience = '';

    public string $discoveryMetadata = '';

    public array $categoryOptions = [];

    public ?string $pageError = null;

    public ?string $categoryLoadError = null;

    public ?string $discoveryError = null;

    public function mount(AdminSessionManager $session, ContentTopicClient $topics, CategoryClient $categories): mixed
    {
        $categoryResult = $this->loadCategories($categories, $session);

        if ($categoryResult !== null) {
            return $categoryResult;
        }

        return $this->loadTopics($topics, $session);
    }

    public function updated(string $property): void
    {
        if (in_array($property, ['discoveryCategoryId', 'discoveryCount', 'discoveryAudience', 'discoveryMetadata'], true)) {
            $this->validateOnly($property, $this->discoveryRules());

            return;
        }

        if (in_array($property, ['search', 'statusFilter', 'categoryFilter', 'clusterFilter', 'sourceFilter'], true)) {
            $this->page = 1;
            $this->refreshTopics();
        }
    }

    public function sortBy(string $sort): void
    {
        if (! in_array($sort, ['created_at', '-created_at', 'updated_at', '-updated_at', 'priority_score', '-priority_score', 'title', '-title'], true)) {
            return;
        }

        $this->sort = $sort;
        $this->page = 1;
        $this->refreshTopics();
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
        $this->discoveryCount = '10';
        $this->discoveryAudience = '';
        $this->discoveryMetadata = '';
        $this->discoveryError = null;
    }

    public function closeDiscoveryDialog(): void
    {
        $this->resetValidation();
        $this->discoveryDialogOpen = false;
        $this->discoveryError = null;
    }

    public function runTopicDiscovery(AiJobClient $jobs, AdminSessionManager $session): mixed
    {
        $validated = $this->validate($this->discoveryRules());
        $this->discoveryError = null;

        try {
            $response = $jobs->topicDiscovery($this->token($session), $session->tokenType(), $this->topicDiscoveryPayload($validated));
            $jobId = Arr::get($response, 'data.id');

            $this->closeDiscoveryDialog();
            session()->flash('status', 'Topic discovery job created.');

            if (is_int($jobId) || ctype_digit((string) $jobId)) {
                return $this->redirectRoute('ai-jobs.show', ['aiJob' => (int) $jobId], navigate: true);
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
            $this->discoveryError = $exception->getMessage() ?: 'Topic discovery could not be started.';

            return null;
        }
    }

    public function render()
    {
        return view('livewire.admin.topic-queue.index', [
            'topics' => $this->paginatedTopics(),
            'statusOptions' => self::TOPIC_STATUSES,
            'clusterOptions' => self::TOPIC_CLUSTERS,
            'categoryOptions' => $this->categoryOptions,
            'sourceOptions' => self::TOPIC_SOURCES,
            'pagination' => $this->paginationSummary(),
            'stats' => $this->stats(),
        ])->layout('layouts.admin', [
            'title' => 'Topic Queue',
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
                    'slug' => (string) Arr::get($category, 'slug', ''),
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
            $this->categoryLoadError = $exception->getMessage() ?: 'Categories could not be loaded for topic discovery.';

            return null;
        }
    }

    protected function loadTopics(ContentTopicClient $topics, AdminSessionManager $session): mixed
    {
        $this->pageError = null;

        try {
            $response = $topics->index($this->token($session), $session->tokenType(), $this->topicFilters());

            $this->topics = collect(Arr::get($response, 'data', []))
                ->map(fn (array $topic): array => $this->mapTopic($topic))
                ->values()
                ->all();

            $this->page = min(max($this->page, 1), $this->lastPage());

            return null;
        } catch (WideWebBlogApiAuthenticationException) {
            return $this->expireSession($session);
        } catch (WideWebBlogApiAuthorizationException) {
            return $this->forbidden($session);
        } catch (WideWebBlogApiException $exception) {
            $this->topics = [];
            $this->pageError = $exception->getMessage() ?: 'Topic queue data could not be loaded from the service API.';

            return null;
        }
    }

    protected function refreshTopics(): void
    {
        $this->loadTopics(app(ContentTopicClient::class), app(AdminSessionManager::class));
    }

    protected function discoveryRules(): array
    {
        return [
            'discoveryCategoryId' => ['required', 'integer'],
            'discoveryCount' => ['nullable', 'integer', 'min:1', 'max:25'],
            'discoveryAudience' => ['nullable', 'string', 'max:255'],
            'discoveryMetadata' => ['nullable', 'string'],
        ];
    }

    protected function topicDiscoveryPayload(array $validated): array
    {
        return [
            'category_id' => (int) $validated['discoveryCategoryId'],
            'count' => filled($validated['discoveryCount']) ? (int) $validated['discoveryCount'] : null,
            'audience' => filled($validated['discoveryAudience']) ? trim($validated['discoveryAudience']) : null,
            'metadata' => collect(explode(',', (string) ($validated['discoveryMetadata'] ?? '')))
                ->map(fn (string $item): string => trim($item))
                ->filter()
                ->values()
                ->all(),
        ];
    }

    protected function topicFilters(): array
    {
        return [
            'search' => trim($this->search) !== '' ? trim($this->search) : null,
            'status' => $this->statusFilter !== 'all' ? $this->statusFilter : null,
            'category_id' => $this->categoryFilter !== 'all' ? (int) $this->categoryFilter : null,
            'cluster' => $this->clusterFilter !== 'all' ? $this->clusterFilter : null,
            'source' => $this->sourceFilter !== 'all' ? $this->sourceFilter : null,
            'sort' => $this->sort,
        ];
    }

    protected function mapTopic(array $topic): array
    {
        $rawScore = Arr::get($topic, 'priority_score', Arr::get($topic, 'score'));
        $score = is_numeric($rawScore) ? (float) $rawScore : null;
        $scoreBreakdown = $this->normalizeScoreBreakdown(Arr::get($topic, 'score_breakdown'));

        return [
            'id' => (int) Arr::get($topic, 'id'),
            'category_id' => Arr::get($topic, 'category_id'),
            'category_name' => (string) Arr::get($topic, 'category.name', 'Unassigned'),
            'category_slug' => (string) Arr::get($topic, 'category.slug', ''),
            'title' => (string) Arr::get($topic, 'title', 'Untitled topic'),
            'slug' => (string) Arr::get($topic, 'slug', ''),
            'cluster' => (string) Arr::get($topic, 'cluster', ''),
            'primary_keyword' => (string) Arr::get($topic, 'primary_keyword', ''),
            'search_intent' => (string) Arr::get($topic, 'search_intent', ''),
            'priority_score' => $score,
            'priority_score_label' => $score !== null ? number_format($score, $score === floor($score) ? 0 : 2) : 'Not scored',
            'score_breakdown' => $scoreBreakdown,
            'score_breakdown_summary' => $this->scoreBreakdownSummary($scoreBreakdown),
            'source' => (string) Arr::get($topic, 'source', 'manual'),
            'status' => (string) Arr::get($topic, 'status', 'suggested'),
            'created_at' => $this->formatTimestamp(Arr::get($topic, 'created_at')),
            'updated_at' => $this->formatTimestamp(Arr::get($topic, 'updated_at')),
            'automation_state' => $this->automationState($score),
            'automation_tone' => $this->automationTone($score),
        ];
    }

    protected function paginatedTopics(): array
    {
        return collect($this->topics)->forPage($this->page, self::PER_PAGE)->values()->all();
    }

    protected function paginationSummary(): array
    {
        $total = count($this->topics);
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
        return max(1, (int) ceil(max(count($this->topics), 1) / self::PER_PAGE));
    }

    protected function stats(): array
    {
        return [
            [
                'label' => 'Auto-Draft 85+',
                'value' => collect($this->topics)->filter(fn (array $topic): bool => ($topic['priority_score'] ?? 0) >= self::AUTO_DRAFT_THRESHOLD)->count(),
                'tone' => 'success',
            ],
            [
                'label' => 'Review 70-84.99',
                'value' => collect($this->topics)->filter(fn (array $topic): bool => ($topic['priority_score'] ?? -1) >= self::REVIEW_THRESHOLD && ($topic['priority_score'] ?? 0) < self::AUTO_DRAFT_THRESHOLD)->count(),
                'tone' => 'warning',
            ],
            [
                'label' => 'Categories In Queue',
                'value' => collect($this->topics)->pluck('category_id')->filter()->unique()->count(),
                'tone' => 'default',
            ],
        ];
    }

    protected function normalizeScoreBreakdown(mixed $scoreBreakdown): array
    {
        if (is_array($scoreBreakdown)) {
            return $scoreBreakdown;
        }

        if (is_string($scoreBreakdown) && $scoreBreakdown !== '') {
            $decoded = json_decode($scoreBreakdown, true);

            return is_array($decoded) ? $decoded : [];
        }

        return [];
    }

    protected function scoreBreakdownSummary(array $scoreBreakdown): ?string
    {
        $labels = [
            'trend_score' => 'Trend',
            'knowledge_base_fit' => 'KB Fit',
            'business_value' => 'Value',
            'originality_gap' => 'Gap',
            'execution_confidence' => 'Confidence',
        ];

        $parts = collect($labels)
            ->map(function (string $label, string $key) use ($scoreBreakdown): ?string {
                $value = Arr::get($scoreBreakdown, $key);

                return is_numeric($value) ? $label.' '.$value : null;
            })
            ->filter()
            ->values()
            ->take(3)
            ->all();

        return $parts === [] ? null : implode(' · ', $parts);
    }

    protected function automationState(?float $score): string
    {
        if ($score === null) {
            return 'Score pending';
        }

        if ($score >= self::AUTO_DRAFT_THRESHOLD) {
            return 'Auto-queues draft generation';
        }

        if ($score >= self::REVIEW_THRESHOLD) {
            return 'Editorial review band';
        }

        return 'Auto-pruned below 70';
    }

    protected function automationTone(?float $score): string
    {
        if ($score === null) {
            return 'muted';
        }

        if ($score >= self::AUTO_DRAFT_THRESHOLD) {
            return 'success';
        }

        if ($score >= self::REVIEW_THRESHOLD) {
            return 'warning';
        }

        return 'muted';
    }

    protected function defaultDiscoveryCategoryId(): string
    {
        $activeCategory = collect($this->categoryOptions)->firstWhere('is_active', true);
        $activeCategoryId = is_array($activeCategory) ? ($activeCategory['id'] ?? null) : null;

        if (is_int($activeCategoryId)) {
            return (string) $activeCategoryId;
        }

        $firstCategoryId = $this->categoryOptions[0]['id'] ?? null;

        return is_int($firstCategoryId) ? (string) $firstCategoryId : '';
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

    protected function normalizeApiErrors(array $errors): array
    {
        return collect($errors)
            ->mapWithKeys(fn (array $messages, string $key): array => [
                match ($key) {
                    'category_id' => 'discoveryCategoryId',
                    'count' => 'discoveryCount',
                    'audience' => 'discoveryAudience',
                    'metadata' => 'discoveryMetadata',
                    default => $key,
                } => $messages,
            ])
            ->all();
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
        session()->flash('auth.error', 'You no longer have access to the admin.');

        return $this->redirectRoute('auth.forbidden', navigate: true);
    }
}
