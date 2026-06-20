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

    public function test_authenticated_admin_can_access_service_backed_admin_routes(): void
    {
        $this->fakeModuleIndexRequests();

        $session = $this->authenticatedSession();

        foreach ([
            'homepage.index',
            'about-page.index',
            'contact-page.index',
            'posts.index',
            'pages.index',
            'pages.create',
            'categories.index',
            'tags.index',
            'media.index',
            'templates.index',
            'knowledge-base.index',
            'contact-submissions.index',
            'contact-submissions.show',
            'seo.index',
            'settings.index',
            'password.index',
            'topic-queue.index',
            'content-briefs.index',
            'draft-review.index',
            'ai-prompts.index',
            'ai-jobs.index',
        ] as $route) {
            $parameters = $route === 'contact-submissions.show'
                ? ['contactSubmission' => 'sub_1']
                : [];

            $this->withSession($session)
                ->get(route($route, $parameters))
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
            ->assertSee('CMS')
            ->assertSee('Publishing')
            ->assertSee('Operations')
            ->assertSee('Contact Page')
            ->assertSee('Contact Submissions')
            ->assertSee('AI Content')
            ->assertSee('href="'.route('categories.index').'"', false)
            ->assertSee('bg-[var(--color-accent-soft)]', false);
    }

    public function test_topic_queue_route_is_rendered_as_a_service_backed_screen(): void
    {
        Http::fake([
            $this->apiBaseUrl.'/admin/content-topics*' => Http::response(['data' => []], 200),
        ]);

        $response = $this->withSession($this->authenticatedSession())
            ->get(route('topic-queue.index'));

        $response
            ->assertOk()
            ->assertSee('Topic Queue')
            ->assertSee('Suggested Topics')
            ->assertSee('Review suggested topic');
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

            if ($request->method() === 'GET' && $url === $this->apiBaseUrl.'/admin/homepage') {
                return Http::response([
                    'data' => [
                        'hero' => [],
                        'featured_editorial' => ['mode' => 'automatic', 'post_ids' => [], 'category_ids' => [], 'limit' => 3],
                        'guide_section' => ['mode' => 'automatic', 'post_ids' => [], 'category_ids' => [], 'limit' => 4],
                        'topic_section' => ['category_ids' => [1]],
                        'promo_section' => ['enabled' => true, 'bullet_points' => ['One'], 'stats' => [['label' => 'L', 'value' => 'V']]],
                        'newsletter_section' => ['enabled' => true],
                        'seo' => [],
                        'updated_at' => '2026-06-17T10:00:00Z',
                    ],
                ], 200);
            }

            if ($request->method() === 'GET' && $url === $this->apiBaseUrl.'/admin/about-page') {
                return Http::response([
                    'data' => [
                        'hero' => [],
                        'mission_section' => [],
                        'stats_section' => ['items' => []],
                        'values_section' => ['title' => '', 'items' => []],
                        'team_section' => ['title' => '', 'description' => '', 'primary_cta_label' => '', 'primary_cta_url' => '', 'members' => []],
                        'seo' => [],
                        'updated_at' => '2026-06-20T15:00:00Z',
                    ],
                ], 200);
            }

            if ($request->method() === 'GET' && $url === $this->apiBaseUrl.'/admin/contact-page') {
                return Http::response([
                    'data' => [
                        'hero' => ['eyebrow' => '', 'title' => '', 'description' => ''],
                        'contact_form' => ['eyebrow' => '', 'title' => '', 'description' => '', 'submit_label' => '', 'success_message' => ''],
                        'contact_reasons' => ['items' => []],
                        'seo' => ['meta_title' => '', 'meta_description' => ''],
                        'updated_at' => '2026-06-20T20:00:00Z',
                        'updated_by' => ['id' => '1', 'name' => 'Admin User'],
                    ],
                ], 200);
            }

            if ($request->method() === 'GET' && $url === $this->apiBaseUrl.'/admin/contact-submissions') {
                return Http::response([
                    'data' => [
                        [
                            'id' => 'sub_1',
                            'name' => 'Amit Sharma',
                            'email' => 'amit@example.com',
                            'topic' => 'Partnership',
                            'message' => 'Hello',
                            'status' => 'new',
                            'admin_notes' => null,
                            'metadata' => [],
                            'submitted_at' => '2026-06-20T10:00:00Z',
                            'reviewed_at' => null,
                            'reviewed_by' => null,
                            'created_at' => '2026-06-20T10:00:00Z',
                            'updated_at' => '2026-06-20T10:00:00Z',
                        ],
                    ],
                ], 200);
            }

            if ($request->method() === 'GET' && $url === $this->apiBaseUrl.'/admin/contact-submissions/sub_1') {
                return Http::response([
                    'data' => [
                        'id' => 'sub_1',
                        'name' => 'Amit Sharma',
                        'email' => 'amit@example.com',
                        'topic' => 'Partnership',
                        'message' => 'Hello',
                        'status' => 'new',
                        'admin_notes' => null,
                        'metadata' => [],
                        'submitted_at' => '2026-06-20T10:00:00Z',
                        'reviewed_at' => null,
                        'reviewed_by' => null,
                        'created_at' => '2026-06-20T10:00:00Z',
                        'updated_at' => '2026-06-20T10:00:00Z',
                    ],
                ], 200);
            }

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

            if ($request->method() === 'GET' && str_starts_with($url, $this->apiBaseUrl.'/admin/content-topics')) {
                return Http::response(['data' => []], 200);
            }

            if ($request->method() === 'GET' && str_starts_with($url, $this->apiBaseUrl.'/admin/content-briefs')) {
                return Http::response(['data' => []], 200);
            }

            if ($request->method() === 'GET' && str_starts_with($url, $this->apiBaseUrl.'/admin/ai-jobs')) {
                return Http::response(['data' => []], 200);
            }

            if ($request->method() === 'GET' && str_starts_with($url, $this->apiBaseUrl.'/admin/ai-prompts')) {
                return Http::response(['data' => []], 200);
            }

            return Http::response(['message' => 'Unexpected request.'], 500);
        });
    }
}
