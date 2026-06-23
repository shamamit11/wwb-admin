<?php

namespace App\Livewire\Admin\Dashboard;

use App\Services\WideWebBlogApi\Clients\AiJobClient;
use App\Services\WideWebBlogApi\Clients\ContentTopicClient;
use App\Services\WideWebBlogApi\Clients\PostClient;
use App\Services\WideWebBlogApi\Exceptions\WideWebBlogApiException;
use App\Support\Auth\AdminSessionManager;
use Illuminate\Support\Arr;
use Illuminate\Support\Carbon;
use Livewire\Component;

class Index extends Component
{
    private const REVIEW_THRESHOLD = 70.0;

    private const AUTO_DRAFT_THRESHOLD = 85.0;

    public array $draftPosts = [];

    public array $publishedPosts = [];

    public array $recentAiJobs = [];

    public array $overviewCards = [];

    public array $pipelineSteps = [];

    public array $quickActions = [];

    public array $editorialQueues = [];

    public array $jobStatusSummary = [];

    public ?string $dashboardError = null;

    public function mount(
        AdminSessionManager $session,
        PostClient $posts,
        ContentTopicClient $topics,
        AiJobClient $jobs,
    ): void {
        if (! $session->hasToken()) {
            return;
        }

        try {
            $draftPosts = Arr::get($posts->index($session->token(), $session->tokenType(), [
                'status' => 'draft',
                'sort' => '-updated_at',
            ]), 'data', []);

            $publishedPosts = Arr::get($posts->index($session->token(), $session->tokenType(), [
                'status' => 'published',
                'sort' => '-published_at',
            ]), 'data', []);

            $allTopics = Arr::get($topics->index($session->token(), $session->tokenType(), [
                'sort' => '-created_at',
            ]), 'data', []);

            $failedJobs = Arr::get($jobs->index($session->token(), $session->tokenType(), [
                'status' => 'failed',
                'sort' => '-failed_at',
            ]), 'data', []);

            $recentJobs = Arr::get($jobs->index($session->token(), $session->tokenType(), [
                'sort' => '-created_at',
            ]), 'data', []);

            $this->draftPosts = $this->mapPosts($draftPosts);
            $this->publishedPosts = $this->mapPosts($publishedPosts);

            $topicCollection = collect($allTopics)->map(fn (array $topic): array => [
                'id' => Arr::get($topic, 'id'),
                'title' => (string) Arr::get($topic, 'title', 'Untitled topic'),
                'priority_score' => is_numeric(Arr::get($topic, 'priority_score')) ? (float) Arr::get($topic, 'priority_score') : null,
                'status' => (string) Arr::get($topic, 'status', 'suggested'),
            ]);

            $highScoreTopics = $topicCollection->filter(fn (array $topic): bool => ($topic['priority_score'] ?? 0) >= self::AUTO_DRAFT_THRESHOLD)->count();
            $reviewBandTopics = $topicCollection->filter(fn (array $topic): bool => ($topic['priority_score'] ?? -1) >= self::REVIEW_THRESHOLD && ($topic['priority_score'] ?? 0) < self::AUTO_DRAFT_THRESHOLD)->count();
            $lowScoreTopics = $topicCollection->filter(fn (array $topic): bool => ($topic['priority_score'] ?? 0) < self::REVIEW_THRESHOLD)->count();

            $this->overviewCards = [
                $this->makeCard('Drafts Pending Review', count($this->draftPosts), route('draft-review.index'), 'Generated drafts that still need manual editorial review.', 'warning'),
                $this->makeCard('Topics At 85+', $highScoreTopics, route('topic-queue.index'), 'Topics that should auto-queue blog draft generation.', 'success'),
                $this->makeCard('Topics In Review Band', $reviewBandTopics, route('topic-queue.index'), 'Topics that should stay in Topic Queue for editorial review.', 'warning'),
                $this->makeCard('Topics Below 70', $lowScoreTopics, route('topic-queue.index'), 'Topics that the backend automation is expected to prune.', 'muted'),
                $this->makeCard('Published Posts', count($this->publishedPosts), route('posts.index', ['status' => 'published']), 'Articles already live on the site.', 'default'),
                $this->makeCard('Failed AI Jobs', count($failedJobs), route('ai-jobs.index', ['status' => 'failed']), 'Failures that still need investigation or retry.', count($failedJobs) > 0 ? 'danger' : 'muted'),
            ];

            $this->pipelineSteps = [
                $this->makePipelineStep('Topic Discovery', count($allTopics), 'Scored', route('topic-queue.index'), 'Categories and knowledge base feed topic generation.', 'default'),
                $this->makePipelineStep('Auto-Draft Band', $highScoreTopics, '85+ passes', route('topic-queue.index'), 'High-score topics move straight into blog draft generation.', 'success'),
                $this->makePipelineStep('Editorial Review Band', $reviewBandTopics, '70-84.99', route('topic-queue.index'), 'Mid-score topics remain available for editorial review.', 'warning'),
                $this->makePipelineStep('Auto Prune', $lowScoreTopics, '< 70 removed', route('topic-queue.index'), 'Low-score topics are deleted by backend automation.', 'muted'),
                $this->makePipelineStep('Draft Review', count($this->draftPosts), 'Manual', route('draft-review.index'), 'Editors review and revise generated article drafts.', 'warning'),
                $this->makePipelineStep('Publish', count($this->publishedPosts), 'Manual', route('posts.index', ['status' => 'published']), 'Only reviewed posts are published.', 'success'),
            ];

            $this->editorialQueues = [
                $this->makeQueueItem('Topic Queue', count($allTopics), route('topic-queue.index'), 'Score visibility and automation state for discovered topics.', 'default'),
                $this->makeQueueItem('Draft Review', count($this->draftPosts), route('draft-review.index'), 'Article-first draft review before publishing.', 'warning'),
                $this->makeQueueItem('Standard Prompts', 2, route('ai-prompts.index'), 'Versioned prompt families for topic and blog generation.', 'default'),
            ];

            $this->quickActions = [
                $this->makeQuickAction('Create Post', 'Open the article editor and write a post manually.', route('posts.create'), 'primary'),
                $this->makeQuickAction('Review Drafts', 'Open generated drafts that still need editorial approval.', route('draft-review.index'), 'secondary'),
                $this->makeQuickAction('Inspect Topics', 'Review score thresholds and automation outcomes.', route('topic-queue.index'), 'secondary'),
                $this->makeQuickAction('Open AI Jobs', 'Inspect automation activity and job details.', route('ai-jobs.index'), 'secondary'),
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
            'overviewCards' => $this->overviewCards,
            'recentAiJobs' => collect($this->recentAiJobs)->take(6)->values()->all(),
            'pipelineSteps' => $this->pipelineSteps,
            'quickActions' => $this->quickActions,
            'editorialQueues' => $this->editorialQueues,
            'jobStatusSummary' => $this->jobStatusSummary,
        ])->layout('layouts.admin', [
            'title' => 'Dashboard',
        ]);
    }

    protected function mapPosts(array $posts): array
    {
        return collect($posts)
            ->map(function (array $post): array {
                return [
                    'id' => Arr::get($post, 'id'),
                    'title' => Arr::get($post, 'title', 'Untitled post'),
                    'status' => Arr::get($post, 'status', 'draft'),
                    'category' => Arr::get($post, 'category.name'),
                    'author' => Arr::get($post, 'author.name'),
                    'created_at' => $this->formatTimestamp(Arr::get($post, 'created_at')),
                    'updated_at' => $this->formatTimestamp(Arr::get($post, 'updated_at')),
                    'published_at' => $this->formatTimestamp(Arr::get($post, 'published_at')),
                    'visibility' => Arr::get($post, 'visibility'),
                    'word_count' => Arr::get($post, 'word_count'),
                    'seo_score' => Arr::get($post, 'seo_score'),
                    'is_ai_generated' => filter_var(Arr::get($post, 'is_ai_generated', false), FILTER_VALIDATE_BOOL),
                ];
            })
            ->values()
            ->all();
    }

    protected function mapJobs(array $jobs): array
    {
        return collect($jobs)
            ->map(fn (array $job): array => [
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
            ])
            ->values()
            ->all();
    }

    protected function makeCard(string $label, int $value, string $href, string $description, string $tone): array
    {
        return compact('label', 'value', 'href', 'description', 'tone');
    }

    protected function makePipelineStep(string $label, int $value, string $state, string $href, string $description, string $tone): array
    {
        return compact('label', 'value', 'state', 'href', 'description', 'tone');
    }

    protected function makeQueueItem(string $label, int $value, string $href, string $description, string $tone): array
    {
        return compact('label', 'value', 'href', 'description', 'tone');
    }

    protected function makeQuickAction(string $label, string $description, string $href, string $variant): array
    {
        return compact('label', 'description', 'href', 'variant');
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
            } elseif (in_array($status, ['completed', 'succeeded'], true)) {
                $summary['completed']++;
            } else {
                $summary['in_progress']++;
            }
        }

        return $summary;
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
}
