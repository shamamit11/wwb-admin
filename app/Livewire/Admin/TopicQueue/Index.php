<?php

namespace App\Livewire\Admin\TopicQueue;

use App\Services\WideWebBlogApi\Clients\AiJobClient;
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
    private const TOPIC_STATUSES = [
        'suggested',
        'approved',
        'rejected',
        'used',
    ];

    private const TOPIC_CLUSTERS = [
        'ai_tools',
        'ai_for_blogging',
        'seo',
        'content_marketing',
        'productivity_automation',
        'developer_ai',
    ];

    private const TOPIC_SOURCES = [
        'manual',
        'ai_suggested',
    ];

    private const PER_PAGE = 10;

    #[Url(as: 'q', except: '')]
    public string $search = '';

    #[Url(as: 'status', except: 'all')]
    public string $statusFilter = 'all';

    #[Url(as: 'cluster', except: 'all')]
    public string $clusterFilter = 'all';

    #[Url(as: 'source', except: 'all')]
    public string $sourceFilter = 'all';

    #[Url(as: 'sort', except: '-created_at')]
    public string $sort = '-created_at';

    #[Url(as: 'page', except: 1)]
    public int $page = 1;

    public array $topics = [];

    public bool $discoveryDialogOpen = false;

    public string $discoveryCluster = 'ai_tools';

    public string $discoveryCount = '10';

    public string $discoveryAudience = '';

    public string $discoveryPromptTemplateKey = '';

    public string $discoveryMetadata = '';

    public ?string $pageError = null;

    public ?string $discoveryError = null;

    public function mount(AdminSessionManager $session, ContentTopicClient $topics): mixed
    {
        return $this->loadTopics($topics, $session);
    }

    public function updated(string $property): void
    {
        if (in_array($property, ['discoveryCluster', 'discoveryCount', 'discoveryAudience', 'discoveryPromptTemplateKey', 'discoveryMetadata'], true)) {
            $this->validateOnly($property, $this->discoveryRules());

            return;
        }

        if (in_array($property, ['search', 'statusFilter', 'clusterFilter', 'sourceFilter'], true)) {
            $this->page = 1;
            $this->refreshTopics();
        }
    }

    public function sortBy(string $sort): void
    {
        if (! in_array($sort, ['created_at', '-created_at', 'updated_at', '-updated_at', 'approved_at', '-approved_at', 'used_at', '-used_at', 'priority_score', '-priority_score', 'title', '-title'], true)) {
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
        $this->discoveryCluster = $this->clusterFilter !== 'all' ? $this->clusterFilter : 'ai_tools';
        $this->discoveryCount = '10';
        $this->discoveryAudience = '';
        $this->discoveryPromptTemplateKey = '';
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
            $this->flashJobAlert('Topic discovery job created.', $jobId);

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
            'sourceOptions' => self::TOPIC_SOURCES,
            'pagination' => $this->paginationSummary(),
            'stats' => $this->stats(),
        ])->layout('layouts.admin', [
            'title' => 'Topic Queue',
        ]);
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

    protected function flashJobAlert(string $message, mixed $jobId): void
    {
        if (is_int($jobId) || ctype_digit((string) $jobId)) {
            session()->flash('status', [
                'message' => $message,
                'link_href' => route('ai-jobs.show', ['aiJob' => (int) $jobId]),
                'link_label' => 'Open AI Job',
            ]);

            return;
        }

        session()->flash('status', $message);
    }

    protected function discoveryRules(): array
    {
        return [
            'discoveryCluster' => ['required', 'in:'.implode(',', self::TOPIC_CLUSTERS)],
            'discoveryCount' => ['nullable', 'integer', 'min:1', 'max:25'],
            'discoveryAudience' => ['nullable', 'string', 'max:255'],
            'discoveryPromptTemplateKey' => ['nullable', 'string', 'max:190'],
            'discoveryMetadata' => ['nullable', 'string'],
        ];
    }

    protected function topicFilters(): array
    {
        return [
            'search' => trim($this->search) !== '' ? trim($this->search) : null,
            'status' => $this->statusFilter !== 'all' ? $this->statusFilter : null,
            'cluster' => $this->clusterFilter !== 'all' ? $this->clusterFilter : null,
            'source' => $this->sourceFilter !== 'all' ? $this->sourceFilter : null,
            'sort' => $this->sort,
        ];
    }

    protected function mapTopic(array $topic): array
    {
        $priorityScore = Arr::get($topic, 'priority_score');

        return [
            'id' => Arr::get($topic, 'id'),
            'title' => Arr::get($topic, 'title', 'Untitled topic'),
            'slug' => Arr::get($topic, 'slug', ''),
            'cluster' => (string) Arr::get($topic, 'cluster', ''),
            'primary_keyword' => Arr::get($topic, 'primary_keyword'),
            'secondary_keywords' => Arr::get($topic, 'secondary_keywords', []),
            'search_intent' => Arr::get($topic, 'search_intent'),
            'priority_score' => $priorityScore,
            'priority_score_label' => is_numeric($priorityScore) ? number_format((float) $priorityScore, 2) : 'TBC',
            'difficulty_note' => Arr::get($topic, 'difficulty_note'),
            'source' => (string) Arr::get($topic, 'source', 'manual'),
            'status' => (string) Arr::get($topic, 'status', 'suggested'),
            'notes' => Arr::get($topic, 'notes'),
            'can_generate_content_brief' => (bool) Arr::get($topic, 'can_generate_content_brief', false),
            'approved_at' => $this->formatTimestamp(Arr::get($topic, 'approved_at')),
            'rejected_at' => $this->formatTimestamp(Arr::get($topic, 'rejected_at')),
            'used_at' => $this->formatTimestamp(Arr::get($topic, 'used_at')),
            'created_at' => $this->formatTimestamp(Arr::get($topic, 'created_at')),
            'created_at_raw' => Arr::get($topic, 'created_at'),
        ];
    }

    protected function topicDiscoveryPayload(array $validated): array
    {
        return array_filter([
            'cluster' => $validated['discoveryCluster'],
            'count' => filled($validated['discoveryCount']) ? (int) $validated['discoveryCount'] : null,
            'audience' => filled($validated['discoveryAudience']) ? trim($validated['discoveryAudience']) : null,
            'prompt_template_key' => filled($validated['discoveryPromptTemplateKey']) ? trim($validated['discoveryPromptTemplateKey']) : null,
            'metadata' => $this->metadataList($validated['discoveryMetadata'] ?? ''),
        ], fn (mixed $value): bool => $value !== null && $value !== '' && $value !== []);
    }

    protected function metadataList(string $metadata): array
    {
        return collect(explode(',', $metadata))
            ->map(fn (string $item): string => trim($item))
            ->filter()
            ->values()
            ->all();
    }

    protected function paginatedTopics(): array
    {
        return collect($this->topics)
            ->forPage($this->page, self::PER_PAGE)
            ->values()
            ->all();
    }

    protected function paginationSummary(): array
    {
        $total = count($this->topics);
        $first = $total === 0 ? 0 : (($this->page - 1) * self::PER_PAGE) + 1;
        $last = min($this->page * self::PER_PAGE, $total);

        return [
            'page' => $this->page,
            'last_page' => $this->lastPage(),
            'per_page' => self::PER_PAGE,
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
                'label' => 'Suggested Topics',
                'value' => collect($this->topics)->where('status', 'suggested')->count(),
                'tone' => 'warning',
            ],
            [
                'label' => 'Approved Topics',
                'value' => collect($this->topics)->where('status', 'approved')->count(),
                'tone' => 'success',
            ],
            [
                'label' => 'Used Topics',
                'value' => collect($this->topics)->where('status', 'used')->count(),
                'tone' => 'info',
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

    protected function normalizeApiErrors(array $errors): array
    {
        $mapped = [];

        foreach ($errors as $field => $messages) {
            $property = match ($field) {
                'cluster' => 'discoveryCluster',
                'count' => 'discoveryCount',
                'audience' => 'discoveryAudience',
                'prompt_template_key' => 'discoveryPromptTemplateKey',
                'metadata' => 'discoveryMetadata',
                default => $field,
            };

            $mapped[$property] = $messages;
        }

        return $mapped;
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
