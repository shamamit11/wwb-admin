<?php

namespace App\Livewire\Admin\Posts;

use App\Services\WideWebBlogApi\Clients\PostClient;
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
    private const POST_STATUSES = [
        'draft',
        'scheduled',
        'published',
        'unpublished',
        'archived',
    ];

    private const POST_VISIBILITIES = [
        'public',
        'private',
        'internal',
    ];

    #[Url(as: 'q', except: '')]
    public string $search = '';

    #[Url(as: 'status', except: 'all')]
    public string $statusFilter = 'all';

    #[Url(as: 'visibility', except: 'all')]
    public string $visibilityFilter = 'all';

    #[Url(as: 'featured', except: 'all')]
    public string $featuredFilter = 'all';

    #[Url(as: 'sort', except: 'updated_at')]
    public string $sortColumn = 'updated_at';

    #[Url(as: 'dir', except: 'desc')]
    public string $sortDirection = 'desc';

    public bool $aiReviewMode = false;

    public array $posts = [];

    public bool $actionDialogOpen = false;

    public string $actionMode = 'publish';

    public ?int $actionPostId = null;

    public string $actionPostTitle = '';

    public string $scheduleFor = '';

    public ?string $pageError = null;

    public ?string $actionError = null;

    public function mount(AdminSessionManager $session, PostClient $posts): mixed
    {
        $this->aiReviewMode = request()->routeIs('draft-review.*');

        if ($this->aiReviewMode) {
            $this->statusFilter = 'draft';
            $this->featuredFilter = 'all';
            $this->visibilityFilter = 'all';
        }

        return $this->loadPosts($posts, $session);
    }

    public function updated(string $property): void
    {
        if ($property === 'scheduleFor' && $this->actionMode === 'schedule') {
            $this->validateOnly('scheduleFor', $this->scheduleRules());

            return;
        }

        if (in_array($property, ['search', 'statusFilter', 'visibilityFilter', 'featuredFilter'], true)) {
            $this->refreshPosts();
        }
    }

    public function sortBy(string $column): void
    {
        if (! in_array($column, ['title', 'published_at', 'updated_at', 'created_at'], true)) {
            return;
        }

        if ($this->sortColumn === $column) {
            $this->sortDirection = $this->sortDirection === 'asc' ? 'desc' : 'asc';
        } else {
            $this->sortColumn = $column;
            $this->sortDirection = $column === 'title' ? 'asc' : 'desc';
        }

        $this->refreshPosts();
    }

    public function openActionDialog(string $mode, int $postId): void
    {
        if (! in_array($mode, ['publish', 'schedule', 'unpublish', 'delete'], true)) {
            return;
        }

        $post = collect($this->posts)->firstWhere('id', $postId);

        if (! is_array($post)) {
            return;
        }

        $this->resetValidation();
        $this->actionDialogOpen = true;
        $this->actionMode = $mode;
        $this->actionPostId = $postId;
        $this->actionPostTitle = (string) ($post['title'] ?? 'this post');
        $this->actionError = null;
        $this->scheduleFor = $mode === 'schedule'
            ? $this->defaultScheduleInput($post['scheduled_for_raw'] ?? null)
            : '';
    }

    public function closeActionDialog(): void
    {
        $this->resetValidation();
        $this->actionDialogOpen = false;
        $this->actionMode = 'publish';
        $this->actionPostId = null;
        $this->actionPostTitle = '';
        $this->scheduleFor = '';
        $this->actionError = null;
    }

    public function executeAction(PostClient $posts, AdminSessionManager $session): mixed
    {
        if (! $this->actionPostId) {
            return null;
        }

        $this->actionError = null;

        if ($this->actionMode === 'schedule') {
            $this->validate($this->scheduleRules());
        }

        try {
            $message = match ($this->actionMode) {
                'publish' => $this->publishPost($posts, $session),
                'schedule' => $this->schedulePost($posts, $session),
                'unpublish' => $this->unpublishPost($posts, $session),
                'delete' => $this->deletePost($posts, $session),
                default => null,
            };

            if ($message) {
                session()->flash('status', $message);
            }

            $this->closeActionDialog();

            return $this->loadPosts($posts, $session);
        } catch (WideWebBlogApiValidationException $exception) {
            $this->actionError = $exception->getMessage();

            throw ValidationException::withMessages($this->normalizeApiErrors($exception->errors()));
        } catch (WideWebBlogApiAuthenticationException) {
            return $this->expireSession($session);
        } catch (WideWebBlogApiAuthorizationException) {
            return $this->forbidden($session);
        } catch (WideWebBlogApiException $exception) {
            $this->actionError = $exception->getMessage() ?: 'The post action could not be completed.';

            return null;
        }
    }

    public function render()
    {
        return view('livewire.admin.posts.index', [
            'posts' => $this->posts,
            'stats' => $this->stats(),
            'statusOptions' => self::POST_STATUSES,
            'visibilityOptions' => self::POST_VISIBILITIES,
            'aiReviewMode' => $this->aiReviewMode,
        ])->layout('layouts.admin', [
            'title' => $this->aiReviewMode ? 'Draft Review' : 'Posts',
        ]);
    }

    protected function loadPosts(PostClient $posts, AdminSessionManager $session): mixed
    {
        $this->pageError = null;

        try {
            $response = $posts->index($this->token($session), $session->tokenType(), $this->postFilters());

            $this->posts = collect(Arr::get($response, 'data', []))
                ->map(fn (array $post): array => $this->mapPost($post))
                ->values()
                ->all();

            return null;
        } catch (WideWebBlogApiAuthenticationException) {
            return $this->expireSession($session);
        } catch (WideWebBlogApiAuthorizationException) {
            return $this->forbidden($session);
        } catch (WideWebBlogApiException $exception) {
            $this->posts = [];
            $this->pageError = $exception->getMessage() ?: 'Posts could not be loaded from the service API.';

            return null;
        }
    }

    protected function refreshPosts(): void
    {
        $this->loadPosts(app(PostClient::class), app(AdminSessionManager::class));
    }

    protected function postFilters(): array
    {
        return [
            'search' => trim($this->search) !== '' ? trim($this->search) : null,
            'status' => $this->aiReviewMode ? 'draft' : ($this->statusFilter !== 'all' ? $this->statusFilter : null),
            'visibility' => $this->visibilityFilter !== 'all' ? $this->visibilityFilter : null,
            'is_featured' => match ($this->featuredFilter) {
                'featured' => 1,
                'standard' => 0,
                default => null,
            },
            'is_ai_generated' => $this->aiReviewMode ? 1 : null,
            'sort' => $this->apiSort(),
        ];
    }

    protected function apiSort(): string
    {
        return $this->sortDirection === 'desc'
            ? '-'.$this->sortColumn
            : $this->sortColumn;
    }

    protected function mapPost(array $post): array
    {
        $status = (string) Arr::get($post, 'status', 'draft');
        $publishedAtRaw = Arr::get($post, 'published_at');
        $scheduledForRaw = Arr::get($post, 'scheduled_for');
        $updatedAtRaw = Arr::get($post, 'updated_at');
        $createdAtRaw = Arr::get($post, 'created_at');

        return [
            'id' => Arr::get($post, 'id'),
            'title' => Arr::get($post, 'title', 'Untitled post'),
            'slug' => Arr::get($post, 'slug', ''),
            'excerpt' => Arr::get($post, 'excerpt'),
            'status' => $status,
            'visibility' => (string) Arr::get($post, 'visibility', 'public'),
            'category_name' => Arr::get($post, 'category.name'),
            'author_name' => Arr::get($post, 'author.name'),
            'is_featured' => (bool) Arr::get($post, 'is_featured', false),
            'published_at' => $this->formatTimestamp($publishedAtRaw),
            'published_at_raw' => $publishedAtRaw,
            'scheduled_for' => $this->formatTimestamp($scheduledForRaw),
            'scheduled_for_raw' => $scheduledForRaw,
            'updated_at' => $this->formatTimestamp($updatedAtRaw),
            'updated_at_raw' => $updatedAtRaw,
            'created_at' => $this->formatTimestamp($createdAtRaw),
            'created_at_raw' => $createdAtRaw,
            'reading_time_minutes' => Arr::get($post, 'reading_time_minutes'),
            'word_count' => Arr::get($post, 'word_count'),
            'can_publish' => ! in_array($status, ['published', 'archived'], true),
            'can_schedule' => in_array($status, ['draft', 'unpublished', 'scheduled'], true),
            'can_unpublish' => in_array($status, ['published', 'scheduled'], true),
            'is_ai_generated' => (bool) Arr::get($post, 'is_ai_generated', false),
            'source_content_brief_id' => Arr::get($post, 'source_content_brief_id'),
            'source_content_topic_id' => Arr::get($post, 'source_content_topic_id'),
            'generated_by_ai_job_id' => Arr::get($post, 'generated_by_ai_job_id'),
            'generated_by' => Arr::get($post, 'generated_by'),
        ];
    }

    protected function stats(): array
    {
        if ($this->aiReviewMode) {
            $drafts = collect($this->posts);

            $withSourceBrief = $drafts->filter(fn (array $post): bool => filled($post['source_content_brief_id'] ?? null))->count();
            $withSourceTopic = $drafts->filter(fn (array $post): bool => filled($post['source_content_topic_id'] ?? null))->count();
            $withAiJob = $drafts->filter(fn (array $post): bool => filled($post['generated_by_ai_job_id'] ?? null))->count();

            return [
                [
                    'label' => 'AI Drafts Pending Review',
                    'value' => $drafts->count(),
                    'suffix' => str('draft')->plural($drafts->count()),
                    'tone' => 'info',
                ],
                [
                    'label' => 'Linked To Briefs',
                    'value' => $withSourceBrief,
                    'suffix' => str('draft')->plural($withSourceBrief),
                    'tone' => 'success',
                ],
                [
                    'label' => 'Linked To Jobs',
                    'value' => $withAiJob,
                    'suffix' => str('draft')->plural($withAiJob),
                    'tone' => $withSourceTopic > 0 ? 'warning' : 'info',
                ],
            ];
        }

        $now = now();
        $nextWeek = $now->copy()->addDays(7);

        $publishedCount = collect($this->posts)
            ->where('status', 'published')
            ->count();

        $scheduledNextWeekCount = collect($this->posts)
            ->filter(function (array $post) use ($now, $nextWeek): bool {
                if (($post['status'] ?? null) !== 'scheduled' || ! is_string($post['scheduled_for_raw'] ?? null)) {
                    return false;
                }

                try {
                    $scheduledAt = Carbon::parse($post['scheduled_for_raw']);
                } catch (\Throwable) {
                    return false;
                }

                return $scheduledAt->betweenIncluded($now, $nextWeek);
            })
            ->count();

        $featuredCount = collect($this->posts)
            ->where('is_featured', true)
            ->count();

        return [
            [
                'label' => 'Total Published',
                'value' => $publishedCount,
                'suffix' => str('post')->plural($publishedCount),
                'tone' => 'success',
            ],
            [
                'label' => 'Scheduled Next 7 Days',
                'value' => $scheduledNextWeekCount,
                'suffix' => str('post')->plural($scheduledNextWeekCount),
                'tone' => 'info',
            ],
            [
                'label' => 'Featured Posts',
                'value' => $featuredCount,
                'suffix' => str('post')->plural($featuredCount),
                'tone' => 'warning',
            ],
        ];
    }

    protected function normalizeApiErrors(array $errors): array
    {
        $mapped = [];

        foreach ($errors as $field => $messages) {
            $property = match ($field) {
                'scheduled_for' => 'scheduleFor',
                default => $field,
            };

            $mapped[$property] = $messages;
        }

        return $mapped;
    }

    protected function scheduleRules(): array
    {
        return [
            'scheduleFor' => ['required', 'date', 'after:now'],
        ];
    }

    protected function publishPost(PostClient $posts, AdminSessionManager $session): string
    {
        $posts->publish($this->token($session), $session->tokenType(), $this->actionPostId ?? 0);

        return 'Post published.';
    }

    protected function schedulePost(PostClient $posts, AdminSessionManager $session): string
    {
        $posts->schedule($this->token($session), $session->tokenType(), $this->actionPostId ?? 0, [
            'scheduled_for' => Carbon::parse($this->scheduleFor)->toISOString(),
        ]);

        return 'Post scheduled.';
    }

    protected function unpublishPost(PostClient $posts, AdminSessionManager $session): string
    {
        $posts->unpublish($this->token($session), $session->tokenType(), $this->actionPostId ?? 0);

        return 'Post unpublished.';
    }

    protected function deletePost(PostClient $posts, AdminSessionManager $session): string
    {
        $posts->delete($this->token($session), $session->tokenType(), $this->actionPostId ?? 0);

        return 'Post deleted.';
    }

    protected function defaultScheduleInput(mixed $value): string
    {
        if (! is_string($value) || $value === '') {
            return now()->addDay()->startOfHour()->format('Y-m-d\TH:i');
        }

        try {
            return Carbon::parse($value)->format('Y-m-d\TH:i');
        } catch (\Throwable) {
            return now()->addDay()->startOfHour()->format('Y-m-d\TH:i');
        }
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
