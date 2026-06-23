<?php

namespace App\Livewire\Admin\News;

use App\Services\WideWebBlogApi\Clients\NewsItemClient;
use App\Services\WideWebBlogApi\Exceptions\WideWebBlogApiAuthenticationException;
use App\Services\WideWebBlogApi\Exceptions\WideWebBlogApiAuthorizationException;
use App\Services\WideWebBlogApi\Exceptions\WideWebBlogApiException;
use App\Support\Auth\AdminSessionManager;
use Illuminate\Support\Arr;
use Illuminate\Support\Carbon;
use Livewire\Component;

class Show extends Component
{
    public int $newsId;

    public array $newsItem = [];

    public ?string $pageError = null;

    public ?string $actionError = null;

    public bool $notFound = false;

    public function mount(int $news, AdminSessionManager $session, NewsItemClient $client): mixed
    {
        $this->newsId = $news;

        return $this->loadNews($client, $session);
    }

    public function refreshNews(NewsItemClient $client, AdminSessionManager $session): mixed
    {
        $this->actionError = null;

        return $this->loadNews($client, $session);
    }

    public function score(NewsItemClient $client, AdminSessionManager $session): mixed
    {
        return $this->runAction($client, $session, 'score', 'News item scored and refreshed.');
    }

    public function extract(NewsItemClient $client, AdminSessionManager $session): mixed
    {
        return $this->runAction($client, $session, 'extract', 'Extraction completed and route job queued.');
    }

    public function routeNews(NewsItemClient $client, AdminSessionManager $session): mixed
    {
        return $this->runAction($client, $session, 'route', 'Routing completed and review data refreshed.');
    }

    public function render()
    {
        return view('livewire.admin.news.show', [
            'scoreCards' => $this->scoreCards(),
            'linkedRecords' => $this->linkedRecords(),
            'articleMetadataJson' => $this->jsonPayload($this->newsItem['metadata'] ?? []),
            'extractionMetadataJson' => $this->jsonPayload(data_get($this->newsItem, 'latest_extraction.metadata', [])),
            'routeMetadataJson' => $this->jsonPayload(data_get($this->newsItem, 'latest_route.metadata', [])),
        ])->layout('layouts.admin', [
            'title' => 'News Review Detail',
        ]);
    }

    protected function runAction(NewsItemClient $client, AdminSessionManager $session, string $action, string $message): mixed
    {
        $this->actionError = null;

        try {
            $response = $client->{$action}($this->token($session), $session->tokenType(), $this->newsId);
            $this->newsItem = $this->mapNews(Arr::get($response, 'data', []));
            session()->flash('status', $message);

            return null;
        } catch (WideWebBlogApiAuthenticationException) {
            return $this->expireSession($session);
        } catch (WideWebBlogApiAuthorizationException) {
            return $this->forbidden($session);
        } catch (WideWebBlogApiException $exception) {
            $this->actionError = $exception->getMessage() ?: 'The news action could not be completed.';

            return null;
        }
    }

    protected function loadNews(NewsItemClient $client, AdminSessionManager $session): mixed
    {
        $this->pageError = null;
        $this->notFound = false;

        try {
            $response = $client->show($this->token($session), $session->tokenType(), $this->newsId);
            $this->newsItem = $this->mapNews(Arr::get($response, 'data', []));

            return null;
        } catch (WideWebBlogApiAuthenticationException) {
            return $this->expireSession($session);
        } catch (WideWebBlogApiAuthorizationException) {
            return $this->forbidden($session);
        } catch (WideWebBlogApiException $exception) {
            if ($exception->status() === 404) {
                $this->notFound = true;
                $this->pageError = 'This news item could not be found in the service API.';

                return null;
            }

            $this->pageError = $exception->getMessage() ?: 'News details could not be loaded.';

            return null;
        }
    }

    protected function mapNews(array $item): array
    {
        return [
            'id' => (int) Arr::get($item, 'id'),
            'title' => (string) Arr::get($item, 'title', 'Untitled article'),
            'normalized_title' => (string) Arr::get($item, 'normalized_title', ''),
            'provider' => (string) Arr::get($item, 'provider', ''),
            'external_id' => Arr::get($item, 'external_id'),
            'status' => (string) Arr::get($item, 'status', 'discovered'),
            'url' => (string) Arr::get($item, 'url', ''),
            'canonical_url' => (string) Arr::get($item, 'canonical_url', ''),
            'description' => (string) Arr::get($item, 'description', ''),
            'author' => (string) Arr::get($item, 'author', ''),
            'publisher_name' => (string) Arr::get($item, 'publisher_name', ''),
            'language' => (string) Arr::get($item, 'language', ''),
            'country' => (string) Arr::get($item, 'country', ''),
            'metadata' => Arr::get($item, 'metadata', []),
            'published_at' => $this->formatTimestamp(Arr::get($item, 'published_at')),
            'discovered_at' => $this->formatTimestamp(Arr::get($item, 'discovered_at')),
            'created_at' => $this->formatTimestamp(Arr::get($item, 'created_at')),
            'updated_at' => $this->formatTimestamp(Arr::get($item, 'updated_at')),
            'category' => Arr::get($item, 'category'),
            'source' => Arr::get($item, 'source'),
            'latest_score' => Arr::get($item, 'latest_score'),
            'latest_extraction' => Arr::get($item, 'latest_extraction'),
            'latest_route' => Arr::get($item, 'latest_route'),
        ];
    }

    protected function scoreCards(): array
    {
        $score = $this->newsItem['latest_score'] ?? [];

        return [
            ['label' => 'Relevance', 'value' => $this->formatMetric(Arr::get($score, 'relevance_score'))],
            ['label' => 'Freshness', 'value' => $this->formatMetric(Arr::get($score, 'freshness_score'))],
            ['label' => 'Credibility', 'value' => $this->formatMetric(Arr::get($score, 'credibility_score'))],
            ['label' => 'Pillar Fit', 'value' => $this->formatMetric(Arr::get($score, 'pillar_fit_score'))],
            ['label' => 'Evergreen', 'value' => $this->formatMetric(Arr::get($score, 'evergreen_potential_score'))],
            ['label' => 'Novelty', 'value' => $this->formatMetric(Arr::get($score, 'novelty_score'))],
            ['label' => 'Business Value', 'value' => $this->formatMetric(Arr::get($score, 'business_value_score'))],
            ['label' => 'Total', 'value' => $this->formatMetric(Arr::get($score, 'total_score'))],
        ];
    }

    protected function linkedRecords(): array
    {
        $route = $this->newsItem['latest_route'] ?? [];
        $records = [];

        if (filled(Arr::get($route, 'knowledge_base_entry.id'))) {
            $records[] = [
                'label' => 'Knowledge Base',
                'title' => (string) Arr::get($route, 'knowledge_base_entry.title', 'Knowledge Base Entry'),
                'meta' => (string) Arr::get($route, 'knowledge_base_entry.slug', ''),
                'href' => route('knowledge-base.edit', ['knowledgeBaseEntry' => (int) Arr::get($route, 'knowledge_base_entry.id')]),
            ];
        }

        if (filled(Arr::get($route, 'content_topic.id'))) {
            $records[] = [
                'label' => 'Topic Queue',
                'title' => (string) Arr::get($route, 'content_topic.title', 'Content Topic'),
                'meta' => (string) Arr::get($route, 'content_topic.status', ''),
                'href' => route('topic-queue.show', ['topic' => (int) Arr::get($route, 'content_topic.id')]),
            ];
        }

        if (filled(Arr::get($route, 'post.id'))) {
            $records[] = [
                'label' => 'Post',
                'title' => (string) Arr::get($route, 'post.title', 'Post'),
                'meta' => (string) Arr::get($route, 'post.status', ''),
                'href' => route('posts.edit', ['post' => (int) Arr::get($route, 'post.id')]),
            ];
        }

        return $records;
    }

    protected function formatMetric(mixed $value): string
    {
        if (! is_numeric($value)) {
            return 'Pending';
        }

        $number = (float) $value;

        return number_format($number, $number === floor($number) ? 0 : 1);
    }

    protected function jsonPayload(mixed $payload): string
    {
        if ($payload === null || $payload === [] || $payload === '') {
            return '{}';
        }

        $encoded = json_encode($payload, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);

        return is_string($encoded) ? $encoded : '{}';
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
