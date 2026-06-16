<?php

namespace App\Livewire\Admin\Dashboard;

use App\Services\WideWebBlogApi\Clients\PostClient;
use App\Services\WideWebBlogApi\Exceptions\WideWebBlogApiException;
use App\Support\Auth\AdminSessionManager;
use Illuminate\Support\Arr;
use Illuminate\Support\Carbon;
use Livewire\Component;

class Index extends Component
{
    public array $currentAdmin = [];

    public array $recentDrafts = [];

    public array $recentPublishedPosts = [];

    public ?string $dashboardError = null;

    public function mount(AdminSessionManager $session, PostClient $posts): void
    {
        $this->currentAdmin = $session->user() ?? [];

        if (! $session->hasToken()) {
            return;
        }

        try {
            $this->recentDrafts = $this->mapPosts(
                $posts->index($session->token(), $session->tokenType(), [
                    'status' => 'draft',
                    'sort' => '-updated_at',
                ])['data'] ?? []
            );

            $this->recentPublishedPosts = $this->mapPosts(
                $posts->index($session->token(), $session->tokenType(), [
                    'status' => 'published',
                    'sort' => '-published_at',
                ])['data'] ?? []
            );
        } catch (WideWebBlogApiException) {
            $this->dashboardError = 'Dashboard data could not be loaded from the service API. You can still continue into the module screens.';
        }
    }

    public function render()
    {
        return view('livewire.admin.dashboard.index')
            ->layout('layouts.admin', [
                'title' => 'Dashboard',
                'pageTitle' => 'Publishing operations should start with action, not noise.',
                'pageDescription' => 'Use the dashboard to jump into recent drafts, published posts, and the next editorial tasks. Placeholder widgets stay explicit where service support is not available yet.',
            ]);
    }

    protected function mapPosts(array $posts): array
    {
        return collect($posts)
            ->take(5)
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
