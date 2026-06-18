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
                $this->makeCard('Suggested Topics', count($suggestedTopics), route('topic-queue.index', ['status' => 'suggested']), 'Review topic suggestions awaiting editorial triage.', 'warning'),
                $this->makeCard('Approved Topics', count($approvedTopics), route('topic-queue.index', ['status' => 'approved']), 'Open approved topics ready for downstream AI workflow steps.', 'success'),
                $this->makeCard('Draft Briefs', count($draftBriefs), route('content-briefs.index', ['status' => 'draft']), 'Review and approve generated briefs before draft creation.', 'warning'),
                $this->makeCard('Approved Briefs', count($approvedBriefs), route('content-briefs.index', ['status' => 'approved']), 'Approved briefs are ready to generate draft posts.', 'success'),
                $this->makeCard('Draft Posts Pending Review', count($this->draftPosts), route('posts.index', ['status' => 'draft']), 'Current contract exposes draft posts but not AI-only provenance filters.', 'muted'),
                $this->makeCard('Failed AI Jobs', count($failedJobs), route('ai-jobs.index', ['status' => 'failed']), 'Investigate failed AI jobs and trigger retry from the job detail screen.', 'danger'),
            ];

            $this->recentAiJobs = $this->mapJobs($recentJobs);
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
            'recentAiJobs' => collect($this->recentAiJobs)->take(5)->values()->all(),
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
                    'updated_at' => $this->formatTimestamp($updatedAt),
                    'published_at' => $this->formatTimestamp($publishedAt),
                    'word_count' => Arr::get($post, 'word_count'),
                    'visibility' => Arr::get($post, 'visibility'),
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
