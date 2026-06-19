<?php

namespace App\Livewire\Admin\AiJobs;

use App\Services\WideWebBlogApi\Clients\AiJobClient;
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
    private const JOB_STATUSES = [
        'pending',
        'queued',
        'processing',
        'completed',
        'failed',
        'cancelled',
        'reviewed',
    ];

    private const COMMON_JOB_TYPES = [
        'topic_discovery',
        'content_brief',
        'blog_writer',
    ];

    private const PER_PAGE = 10;

    #[Url(as: 'status', except: 'all')]
    public string $statusFilter = 'all';

    #[Url(as: 'type', except: 'all')]
    public string $typeFilter = 'all';

    #[Url(as: 'provider', except: '')]
    public string $providerFilter = '';

    #[Url(as: 'model', except: '')]
    public string $modelFilter = '';

    #[Url(as: 'sort', except: '-created_at')]
    public string $sort = '-created_at';

    #[Url(as: 'page', except: 1)]
    public int $page = 1;

    public array $jobs = [];

    public ?string $pageError = null;

    public function mount(AdminSessionManager $session, AiJobClient $jobs): mixed
    {
        return $this->loadJobs($jobs, $session);
    }

    public function updated(string $property): void
    {
        if (in_array($property, ['statusFilter', 'typeFilter', 'providerFilter', 'modelFilter'], true)) {
            $this->page = 1;
            $this->refreshJobs();
        }
    }

    public function sortBy(string $sort): void
    {
        if (! in_array($sort, ['attempts', '-attempts', 'created_at', '-created_at', 'updated_at', '-updated_at', 'started_at', '-started_at', 'completed_at', '-completed_at', 'failed_at', '-failed_at'], true)) {
            return;
        }

        $this->sort = $sort;
        $this->page = 1;
        $this->refreshJobs();
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
        return view('livewire.admin.ai-jobs.index', [
            'jobs' => $this->paginatedJobs(),
            'statusOptions' => self::JOB_STATUSES,
            'typeOptions' => self::COMMON_JOB_TYPES,
            'pagination' => $this->paginationSummary(),
            'stats' => $this->stats(),
        ])->layout('layouts.admin', [
            'title' => 'AI Jobs',
        ]);
    }

    protected function loadJobs(AiJobClient $jobs, AdminSessionManager $session): mixed
    {
        $this->pageError = null;

        try {
            $response = $jobs->index($this->token($session), $session->tokenType(), $this->jobFilters());

            $this->jobs = collect(Arr::get($response, 'data', []))
                ->map(fn (array $job): array => $this->mapJob($job))
                ->values()
                ->all();

            $this->page = min(max($this->page, 1), $this->lastPage());

            return null;
        } catch (WideWebBlogApiAuthenticationException) {
            return $this->expireSession($session);
        } catch (WideWebBlogApiAuthorizationException) {
            return $this->forbidden($session);
        } catch (WideWebBlogApiException $exception) {
            $this->jobs = [];
            $this->pageError = $exception->getMessage() ?: 'AI job data could not be loaded from the service API.';

            return null;
        }
    }

    protected function refreshJobs(): void
    {
        $this->loadJobs(app(AiJobClient::class), app(AdminSessionManager::class));
    }

    protected function jobFilters(): array
    {
        return [
            'status' => $this->statusFilter !== 'all' ? $this->statusFilter : null,
            'type' => $this->typeFilter !== 'all' ? $this->typeFilter : null,
            'provider' => trim($this->providerFilter) !== '' ? trim($this->providerFilter) : null,
            'model' => trim($this->modelFilter) !== '' ? trim($this->modelFilter) : null,
            'sort' => $this->sort,
        ];
    }

    protected function mapJob(array $job): array
    {
        return [
            'id' => Arr::get($job, 'id'),
            'type' => (string) Arr::get($job, 'type', ''),
            'status' => (string) Arr::get($job, 'status', 'pending'),
            'entity_type' => Arr::get($job, 'entity_type'),
            'entity_id' => Arr::get($job, 'entity_id'),
            'provider' => Arr::get($job, 'provider'),
            'model' => Arr::get($job, 'model'),
            'error_message' => Arr::get($job, 'error_message'),
            'attempts' => (int) Arr::get($job, 'attempts', 0),
            'can_retry' => (bool) Arr::get($job, 'can_retry', false),
            'steps_count' => (int) Arr::get($job, 'steps_count', 0),
            'cost_summary' => Arr::get($job, 'cost_summary'),
            'started_at' => $this->formatTimestamp(Arr::get($job, 'started_at')),
            'completed_at' => $this->formatTimestamp(Arr::get($job, 'completed_at')),
            'failed_at' => $this->formatTimestamp(Arr::get($job, 'failed_at')),
            'created_at' => $this->formatTimestamp(Arr::get($job, 'created_at')),
        ];
    }

    protected function paginatedJobs(): array
    {
        return collect($this->jobs)
            ->forPage($this->page, self::PER_PAGE)
            ->values()
            ->all();
    }

    protected function paginationSummary(): array
    {
        $total = count($this->jobs);
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
        return max(1, (int) ceil(max(count($this->jobs), 1) / self::PER_PAGE));
    }

    protected function stats(): array
    {
        return [
            [
                'label' => 'Processing Jobs',
                'value' => collect($this->jobs)->where('status', 'processing')->count(),
                'tone' => 'warning',
            ],
            [
                'label' => 'Failed Jobs',
                'value' => collect($this->jobs)->where('status', 'failed')->count(),
                'tone' => 'danger',
            ],
            [
                'label' => 'Completed Jobs',
                'value' => collect($this->jobs)->where('status', 'completed')->count(),
                'tone' => 'success',
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
