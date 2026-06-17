<?php

namespace Tests\Feature\Navigation;

use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class AdminNavigationTest extends TestCase
{
    protected string $apiBaseUrl;

    protected function setUp(): void
    {
        parent::setUp();

        $this->apiBaseUrl = rtrim(config('widewebblog.api.base_url'), '/');
    }

    public function test_authenticated_admin_can_access_mvp_placeholder_routes(): void
    {
        $this->fakeModuleIndexRequests();

        $session = $this->authenticatedSession();

        foreach ([
            'posts.index',
            'pages.index',
            'pages.create',
            'categories.index',
            'tags.index',
            'media.index',
            'templates.index',
            'knowledge-base.index',
            'seo.index',
            'settings.index',
        ] as $route) {
            $this->withSession($session)
                ->get(route($route))
                ->assertOk();
        }
    }

    public function test_sidebar_navigation_highlights_the_current_section(): void
    {
        $this->fakeModuleIndexRequests();

        $response = $this->withSession($this->authenticatedSession())
            ->get(route('categories.index'));

        $response
            ->assertOk()
            ->assertSee('Overview')
            ->assertSee('Publishing')
            ->assertSee('Operations')
            ->assertSee('Roadmap')
            ->assertSee('href="'.route('categories.index').'"', false)
            ->assertSee('bg-[var(--color-accent-soft)]', false);
    }

    public function test_roadmap_modules_are_rendered_as_placeholders(): void
    {
        $response = $this->withSession($this->authenticatedSession())
            ->get(route('topic-queue.index'));

        $response
            ->assertOk()
            ->assertSee('Roadmap placeholder')
            ->assertSee('API Not Available Yet')
            ->assertSee('Placeholder');
    }

    public function test_pages_module_is_rendered_as_a_service_backed_publishing_screen(): void
    {
        Http::fake([
            $this->apiBaseUrl.'/admin/pages*' => Http::response(['data' => []], 200),
        ]);

        $response = $this->withSession($this->authenticatedSession())
            ->get(route('pages.index'));

        $response
            ->assertOk()
            ->assertSee('Pages')
            ->assertSee('Create Page')
            ->assertSee('Manage static and evergreen page content');
    }

    protected function authenticatedSession(): array
    {
        return [
            config('widewebblog.session.token_key') => 'test-token',
            config('widewebblog.session.token_type_key') => 'Bearer',
            config('widewebblog.session.user_key') => [
                'id' => 1,
                'name' => 'Admin User',
                'email' => 'admin@example.com',
            ],
        ];
    }

    protected function fakeModuleIndexRequests(): void
    {
        Http::fake(function (Request $request) {
            $url = $request->url();

            if ($request->method() === 'GET' && str_starts_with($url, $this->apiBaseUrl.'/admin/posts')) {
                return Http::response(['data' => []], 200);
            }

            if ($request->method() === 'GET' && str_starts_with($url, $this->apiBaseUrl.'/admin/categories')) {
                return Http::response(['data' => []], 200);
            }

            if ($request->method() === 'GET' && str_starts_with($url, $this->apiBaseUrl.'/admin/tags')) {
                return Http::response(['data' => []], 200);
            }

            if ($request->method() === 'GET' && str_starts_with($url, $this->apiBaseUrl.'/admin/media')) {
                return Http::response(['data' => []], 200);
            }

            if ($request->method() === 'GET' && str_starts_with($url, $this->apiBaseUrl.'/admin/templates')) {
                return Http::response(['data' => []], 200);
            }

            if ($request->method() === 'GET' && str_starts_with($url, $this->apiBaseUrl.'/admin/pages')) {
                return Http::response(['data' => []], 200);
            }

            if ($request->method() === 'GET' && str_starts_with($url, $this->apiBaseUrl.'/admin/knowledge-base')) {
                return Http::response(['data' => []], 200);
            }

            return Http::response(['message' => 'Unexpected request.'], 500);
        });
    }
}
