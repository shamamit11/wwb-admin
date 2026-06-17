<?php

namespace App\Livewire\Admin\Seo;

use App\Services\WideWebBlogApi\Clients\PostClient;
use App\Services\WideWebBlogApi\Clients\SeoClient;
use App\Services\WideWebBlogApi\Exceptions\WideWebBlogApiAuthenticationException;
use App\Services\WideWebBlogApi\Exceptions\WideWebBlogApiAuthorizationException;
use App\Services\WideWebBlogApi\Exceptions\WideWebBlogApiException;
use App\Support\Auth\AdminSessionManager;
use App\Support\Seo\SeoInsightsPresenter;
use Illuminate\Support\Arr;
use Livewire\Attributes\Url;
use Livewire\Component;

class Index extends Component
{
    #[Url(as: 'q', except: '')]
    public string $search = '';

    #[Url(as: 'status', except: 'all')]
    public string $statusFilter = 'all';

    #[Url(as: 'post', except: '')]
    public string $selectedPostId = '';

    public array $posts = [];

    public array $selectedPost = [];

    public array $score = [];

    public array $schema = [];

    public ?string $pageError = null;

    public ?string $scoreError = null;

    public ?string $schemaError = null;

    public function mount(AdminSessionManager $session, PostClient $posts, SeoClient $seo): mixed
    {
        $result = $this->loadPosts($posts, $session);

        if ($result !== null || $this->pageError !== null) {
            return $result;
        }

        return $this->loadSelectedSeo($seo, $session);
    }

    public function updated(string $property): void
    {
        if (in_array($property, ['search', 'statusFilter'], true)) {
            $result = $this->loadPosts(app(PostClient::class), app(AdminSessionManager::class));

            if ($result === null && $this->pageError === null) {
                $this->loadSelectedSeo(app(SeoClient::class), app(AdminSessionManager::class));
            }

            return;
        }

        if ($property === 'selectedPostId') {
            $this->syncSelectedPost();

            if ($this->selectedPost !== []) {
                $this->loadSelectedSeo(app(SeoClient::class), app(AdminSessionManager::class));
            }
        }
    }

    public function selectPost(int $postId): void
    {
        $this->selectedPostId = (string) $postId;
        $this->syncSelectedPost();
        $this->loadSelectedSeo(app(SeoClient::class), app(AdminSessionManager::class));
    }

    public function render()
    {
        return view('livewire.admin.seo.index', [
            'posts' => $this->posts,
            'selectedPost' => $this->selectedPost,
            'scoreValue' => $this->presenter()->scoreValue($this->score),
            'scoreGrade' => $this->presenter()->scoreGrade($this->score),
            'scoreSubscores' => $this->presenter()->scoreSubscores($this->score),
            'recommendations' => $this->presenter()->recommendations($this->score),
            'schemaSummary' => $this->presenter()->schemaSummary($this->schema),
            'schemaJson' => $this->presenter()->prettySchema($this->schema),
        ])->layout('layouts.admin', [
            'title' => 'SEO',
        ]);
    }

    protected function loadPosts(PostClient $posts, AdminSessionManager $session): mixed
    {
        $this->pageError = null;

        try {
            $response = $posts->index($this->token($session), $session->tokenType(), $this->postFilters());

            $this->posts = collect(Arr::get($response, 'data', []))
                ->map(fn (array $post): array => [
                    'id' => (int) Arr::get($post, 'id'),
                    'title' => (string) Arr::get($post, 'title', 'Untitled post'),
                    'slug' => (string) Arr::get($post, 'slug', ''),
                    'status' => (string) Arr::get($post, 'status', 'draft'),
                    'category_name' => Arr::get($post, 'category.name'),
                    'updated_at' => Arr::get($post, 'updated_at'),
                ])
                ->values()
                ->all();

            $this->syncSelectedPost();

            return null;
        } catch (WideWebBlogApiAuthenticationException) {
            return $this->expireSession($session);
        } catch (WideWebBlogApiAuthorizationException) {
            return $this->forbidden($session);
        } catch (WideWebBlogApiException $exception) {
            $this->posts = [];
            $this->selectedPost = [];
            $this->score = [];
            $this->schema = [];
            $this->pageError = $exception->getMessage() ?: 'SEO entities could not be loaded.';

            return null;
        }
    }

    protected function loadSelectedSeo(SeoClient $seo, AdminSessionManager $session): mixed
    {
        $this->scoreError = null;
        $this->schemaError = null;
        $this->score = [];
        $this->schema = [];

        if ($this->selectedPost === []) {
            return null;
        }

        $postId = (int) $this->selectedPost['id'];

        try {
            $scoreResponse = $seo->score($this->token($session), $session->tokenType(), 'post', $postId);
            $this->score = Arr::get($scoreResponse, 'data', []);
        } catch (WideWebBlogApiAuthenticationException) {
            return $this->expireSession($session);
        } catch (WideWebBlogApiAuthorizationException) {
            return $this->forbidden($session);
        } catch (WideWebBlogApiException $exception) {
            $this->scoreError = $exception->getMessage() ?: 'SEO score could not be loaded.';
        }

        try {
            $schemaResponse = $seo->schema($this->token($session), $session->tokenType(), 'post', $postId);
            $this->schema = Arr::get($schemaResponse, 'data', []);
        } catch (WideWebBlogApiAuthenticationException) {
            return $this->expireSession($session);
        } catch (WideWebBlogApiAuthorizationException) {
            return $this->forbidden($session);
        } catch (WideWebBlogApiException $exception) {
            $this->schemaError = $exception->getMessage() ?: 'Schema output could not be loaded.';
        }

        return null;
    }

    protected function postFilters(): array
    {
        return [
            'search' => trim($this->search) !== '' ? trim($this->search) : null,
            'status' => $this->statusFilter !== 'all' ? $this->statusFilter : null,
        ];
    }

    protected function syncSelectedPost(): void
    {
        $selected = collect($this->posts)
            ->first(fn (array $post): bool => (string) $post['id'] === $this->selectedPostId);

        if (! is_array($selected)) {
            $selected = $this->posts[0] ?? [];
            $this->selectedPostId = is_array($selected) ? (string) ($selected['id'] ?? '') : '';
        }

        $this->selectedPost = is_array($selected) ? $selected : [];
    }

    protected function presenter(): SeoInsightsPresenter
    {
        return app(SeoInsightsPresenter::class);
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
