<?php

namespace App\Livewire\Admin\Dashboard;

use App\Services\WideWebBlogApi\Clients\AiJobClient;
use App\Services\WideWebBlogApi\Clients\ContentBriefClient;
use App\Services\WideWebBlogApi\Clients\ContentTopicClient;
use App\Services\WideWebBlogApi\Clients\PostClient;
use App\Services\WideWebBlogApi\Exceptions\WideWebBlogApiException;
use App\Support\Auth\AdminSessionManager;
use Illuminate\Support\Arr;
use Illuminate\Support\Carbon;
use Livewire\Component;

class Index extends Component
{
    public array $currentAdmin = [];

    public array $draftPosts = [];

    public array $publishedPosts = [];

    public array $recentAiJobs = [];

    public array $aiWorkflowCards = [];

    public array $pipelineSteps = [];

    public array $quickActions = [];

    public array $editorialQueues = [];

    public array $jobStatusSummary = [];

    public ?string $dashboardError = null;

    public function mount(
        AdminSessionManager $session,
        PostClient $posts,
        ContentTopicClient $topics,
        ContentBriefClient $briefs,
        AiJobClient $jobs,
    ): void
    {
        $this->currentAdmin = $session->user() ?? [];

        if (! $session->hasToken()) {
            return;
        }

        try {
            $this->draftPosts = $this->mapPosts(
                $posts->index($session->token(), $session->tokenType(), [
                    'status' => 'draft',
                    'sort' => '-updated_at',
                ])['data'] ?? []
            );

            $this->publishedPosts = $this->mapPosts(
                $posts->index($session->token(), $session->tokenType(), [
                    'status' => 'published',
                    'sort' => '-published_at',
                ])['data'] ?? []
            );

            $suggestedTopics = Arr::get($topics->index($session->token(), $session->tokenType(), [
                'status' => 'suggested',
                'sort' => '-created_at',
            ]), 'data', []);

            $approvedTopics = Arr::get($topics->index($session->token(), $session->tokenType(), [
                'status' => 'approved',
                'sort' => '-updated_at',
            ]), 'data', []);

            $draftBriefs = Arr::get($briefs->index($session->token(), $session->tokenType(), [
                'status' => 'draft',
                'sort' => '-created_at',
            ]), 'data', []);

            $approvedBriefs = Arr::get($briefs->index($session->token(), $session->tokenType(), [
                'status' => 'approved',
                'sort' => '-approved_at',
            ]), 'data', []);

            $failedJobs = Arr::get($jobs->index($session->token(), $session->tokenType(), [
                'status' => 'failed',
                'sort' => '-failed_at',
            ]), 'data', []);

            $recentJobs = Arr::get($jobs->index($session->token(), $session->tokenType(), [
                'sort' => '-created_at',
            ]), 'data', []);

            $this->aiWorkflowCards = [
                $this->makeCard('Suggested Topics', count($suggestedTopics), route('topic-queue.index', ['status' => 'suggested']), 'Editorial triage queue for topic ideas.', 'warning'),
                $this->makeCard('Approved Topics', count($approvedTopics), route('topic-queue.index', ['status' => 'approved']), 'Ready for downstream SEO and brief work.', 'success'),
                $this->makeCard('Draft Briefs', count($draftBriefs), route('content-briefs.index', ['status' => 'draft']), 'Briefs waiting for approval before drafting.', 'warning'),
                $this->makeCard('Approved Briefs', count($approvedBriefs), route('content-briefs.index', ['status' => 'approved']), 'Approved briefs ready for draft generation.', 'success'),
                $this->makeCard('Draft Posts Pending Review', count($this->draftPosts), route('posts.index', ['status' => 'draft']), 'Draft posts currently in editorial review.', 'warning'),
                $this->makeCard('Failed AI Jobs', count($failedJobs), route('ai-jobs.index', ['status' => 'failed']), 'Failures that need investigation or retry.', count($failedJobs) > 0 ? 'danger' : 'muted'),
            ];

            $this->pipelineSteps = [
                $this->makePipelineStep('Topic Discovery', count($suggestedTopics), 'Waiting review', route('topic-queue.index', ['status' => 'suggested']), 'Suggestions needing triage.', 'warning'),
                $this->makePipelineStep('SEO Research', count($approvedTopics), 'Ready', route('topic-queue.index', ['status' => 'approved']), 'Approved topics in queue.', 'success'),
                $this->makePipelineStep('Content Brief', count($draftBriefs), 'Waiting review', route('content-briefs.index', ['status' => 'draft']), 'Briefs awaiting approval.', 'warning'),
                $this->makePipelineStep('Blog Writer', count($approvedBriefs), 'Ready', route('content-briefs.index', ['status' => 'approved']), 'Approved briefs ready.', 'default'),
                $this->makePipelineStep('Editor', count($this->draftPosts), 'Needs review', route('draft-review.index'), 'Drafts queued for review.', 'warning'),
                $this->makePipelineStep('Publish', count($this->publishedPosts), 'Live', route('posts.index', ['status' => 'published']), 'Recent posts now live.', 'success'),
            ];

            $this->editorialQueues = [
                $this->makeQueueItem('Suggested Topics', count($suggestedTopics), route('topic-queue.index', ['status' => 'suggested']), 'Topic ideas waiting for review.', 'warning'),
                $this->makeQueueItem('Draft Briefs', count($draftBriefs), route('content-briefs.index', ['status' => 'draft']), 'Briefs that still need approval.', 'warning'),
                $this->makeQueueItem('Approved Briefs', count($approvedBriefs), route('content-briefs.index', ['status' => 'approved']), 'Briefs ready for draft generation.', 'success'),
            ];

            $this->quickActions = [
                $this->makeQuickAction('Create Post', 'Open the editor and start a new article.', route('posts.create'), 'primary'),
                $this->makeQuickAction('Review Topics', 'Triage suggested ideas and approve what should move forward.', route('topic-queue.index', ['status' => 'suggested']), 'secondary'),
                $this->makeQuickAction('Review Briefs', 'Approve or refine generated content briefs.', route('content-briefs.index', ['status' => 'draft']), 'secondary'),
                $this->makeQuickAction('Open AI Jobs', 'Inspect current automation activity and job details.', route('ai-jobs.index'), 'secondary'),
                $this->makeQuickAction('View Failed Jobs', 'Jump directly into failures that need intervention.', route('ai-jobs.index', ['status' => 'failed']), 'secondary'),
            ];

            $this->recentAiJobs = $this->mapJobs($recentJobs);
            $this->jobStatusSummary = $this->summarizeJobStatuses($recentJobs);
        } catch (WideWebBlogApiException) {
            $this->dashboardError = 'Dashboard data could not be loaded from the service API. You can still continue into the module screens.';
        }
    }

    public function render()
    {
        return view('livewire.admin.dashboard.index', [
            'recentDrafts' => collect($this->draftPosts)->take(5)->values()->all(),
            'recentPublishedPosts' => collect($this->publishedPosts)->take(5)->values()->all(),
            'aiWorkflowCards' => $this->aiWorkflowCards,
            'recentAiJobs' => collect($this->recentAiJobs)->take(6)->values()->all(),
            'pipelineSteps' => $this->pipelineSteps,
            'quickActions' => $this->quickActions,
            'editorialQueues' => $this->editorialQueues,
            'jobStatusSummary' => $this->jobStatusSummary,
        ])
            ->layout('layouts.admin', [
                'title' => 'Dashboard',
                'pageTitle' => null,
                'pageDescription' => null,
            ]);
    }

    protected function mapPosts(array $posts): array
    {
        return collect($posts)
            ->map(function (array $post): array {
                $updatedAt = Arr::get($post, 'updated_at');
                $publishedAt = Arr::get($post, 'published_at');

                return [
                    'id' => Arr::get($post, 'id'),
                    'title' => Arr::get($post, 'title', 'Untitled post'),
                    'slug' => Arr::get($post, 'slug'),
                    'status' => Arr::get($post, 'status', 'draft'),
                    'category' => Arr::get($post, 'category.name'),
                    'author' => Arr::get($post, 'author.name'),
                    'created_at' => $this->formatTimestamp(Arr::get($post, 'created_at')),
                    'updated_at' => $this->formatTimestamp($updatedAt),
                    'published_at' => $this->formatTimestamp($publishedAt),
                    'word_count' => Arr::get($post, 'word_count'),
                    'visibility' => Arr::get($post, 'visibility'),
                    'seo_score' => Arr::get($post, 'seo_score', Arr::get($post, 'seo.score')),
                ];
            })
            ->values()
            ->all();
    }

    protected function mapJobs(array $jobs): array
    {
        return collect($jobs)
            ->map(function (array $job): array {
                return [
                    'id' => Arr::get($job, 'id'),
                    'type' => (string) Arr::get($job, 'type', ''),
                    'status' => (string) Arr::get($job, 'status', 'pending'),
                    'provider' => Arr::get($job, 'provider'),
                    'model' => Arr::get($job, 'model'),
                    'entity_type' => Arr::get($job, 'entity_type'),
                    'entity_id' => Arr::get($job, 'entity_id'),
                    'created_at' => $this->formatTimestamp(Arr::get($job, 'created_at')),
                    'failed_at' => $this->formatTimestamp(Arr::get($job, 'failed_at')),
                    'completed_at' => $this->formatTimestamp(Arr::get($job, 'completed_at')),
                    'updated_at' => $this->formatTimestamp(Arr::get($job, 'updated_at')),
                ];
            })
            ->values()
            ->all();
    }

    protected function makeCard(string $label, int $value, string $href, string $description, string $tone): array
    {
        return [
            'label' => $label,
            'value' => $value,
            'href' => $href,
            'description' => $description,
            'tone' => $tone,
        ];
    }

    protected function makePipelineStep(string $label, int $value, string $state, string $href, string $description, string $tone): array
    {
        return [
            'label' => $label,
            'value' => $value,
            'state' => $state,
            'href' => $href,
            'description' => $description,
            'tone' => $tone,
        ];
    }

    protected function makeQueueItem(string $label, int $value, string $href, string $description, string $tone): array
    {
        return [
            'label' => $label,
            'value' => $value,
            'href' => $href,
            'description' => $description,
            'tone' => $tone,
        ];
    }

    protected function makeQuickAction(string $label, string $description, string $href, string $variant): array
    {
        return [
            'label' => $label,
            'description' => $description,
            'href' => $href,
            'variant' => $variant,
        ];
    }

    protected function summarizeJobStatuses(array $jobs): array
    {
        $summary = [
            'completed' => 0,
            'in_progress' => 0,
            'failed' => 0,
        ];

        foreach ($jobs as $job) {
            $status = str((string) Arr::get($job, 'status', 'pending'))
                ->lower()
                ->replace([' ', '-'], '_')
                ->value();

            if ($status === 'failed') {
                $summary['failed']++;

                continue;
            }

            if (in_array($status, ['completed', 'published'], true)) {
                $summary['completed']++;

                continue;
            }

            $summary['in_progress']++;
        }

        return $summary;
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
}
