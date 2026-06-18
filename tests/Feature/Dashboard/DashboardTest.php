<?php

namespace Tests\Feature\Dashboard;

use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class DashboardTest extends TestCase
{
    protected string $apiBaseUrl;

    protected function setUp(): void
    {
        parent::setUp();

        $this->apiBaseUrl = rtrim(config('widewebblog.api.base_url'), '/');
    }

    public function test_dashboard_loads_recent_drafts_and_published_posts_from_service_api(): void
    {
        Http::fake([
            $this->apiBaseUrl.'/admin/posts?status=draft&sort=-updated_at' => Http::response([
                'data' => [
                    $this->postResource([
                        'id' => 10,
                        'title' => 'Draft editorial plan',
                        'status' => 'draft',
                        'updated_at' => '2026-06-15T10:00:00Z',
                    ]),
                ],
            ], 200),
            $this->apiBaseUrl.'/admin/posts?status=published&sort=-published_at' => Http::response([
                'data' => [
                    $this->postResource([
                        'id' => 20,
                        'title' => 'Published industry recap',
                        'status' => 'published',
                        'published_at' => '2026-06-14T09:00:00Z',
                        'updated_at' => '2026-06-14T09:00:00Z',
                    ]),
                ],
            ], 200),
            $this->apiBaseUrl.'/admin/content-topics?status=suggested&sort=-created_at' => Http::response([
                'data' => [
                    ['id' => 31, 'title' => 'Suggested topic'],
                ],
            ], 200),
            $this->apiBaseUrl.'/admin/content-topics?status=approved&sort=-updated_at' => Http::response([
                'data' => [
                    ['id' => 32, 'title' => 'Approved topic'],
                ],
            ], 200),
            $this->apiBaseUrl.'/admin/content-briefs?status=draft&sort=-created_at' => Http::response([
                'data' => [
                    ['id' => 41, 'title' => 'Draft brief'],
                ],
            ], 200),
            $this->apiBaseUrl.'/admin/content-briefs?status=approved&sort=-approved_at' => Http::response([
                'data' => [
                    ['id' => 42, 'title' => 'Approved brief'],
                ],
            ], 200),
            $this->apiBaseUrl.'/admin/ai-jobs?status=failed&sort=-failed_at' => Http::response([
                'data' => [
                    [
                        'id' => 51,
                        'type' => 'content_brief',
                        'status' => 'failed',
                        'provider' => 'openai',
                        'model' => 'gpt-5',
                        'created_at' => '2026-06-15T11:00:00Z',
                        'failed_at' => '2026-06-15T11:05:00Z',
                    ],
                ],
            ], 200),
            $this->apiBaseUrl.'/admin/ai-jobs?sort=-created_at' => Http::response([
                'data' => [
                    [
                        'id' => 52,
                        'type' => 'topic_discovery',
                        'status' => 'completed',
                        'provider' => 'openai',
                        'model' => 'gpt-5',
                        'created_at' => '2026-06-15T10:00:00Z',
                        'completed_at' => '2026-06-15T10:04:00Z',
                    ],
                ],
            ], 200),
        ]);

        $response = $this
            ->withSession($this->authenticatedSession())
            ->get(route('dashboard'));

        $response
            ->assertOk()
            ->assertSee('Draft editorial plan')
            ->assertSee('Published industry recap')
            ->assertSee('Operational Snapshot')
            ->assertSee('Suggested Topics')
            ->assertSee('Recent AI Jobs')
            ->assertSee('Job #52');
    }

    public function test_dashboard_shows_safe_error_message_when_service_data_fails(): void
    {
        Http::fake([
            $this->apiBaseUrl.'/admin/posts*' => Http::response([
                'message' => 'Service unavailable.',
            ], 500),
        ]);

        $response = $this
            ->withSession($this->authenticatedSession())
            ->get(route('dashboard'));

        $response
            ->assertOk()
            ->assertSee('Dashboard data could not be loaded from the service API.')
            ->assertSee('Recent AI Jobs')
            ->assertSee('No recent AI jobs returned');
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

    protected function postResource(array $overrides = []): array
    {
        return array_replace_recursive([
            'id' => 1,
            'ulid' => '01J00000000000000000000000',
            'title' => 'Post title',
            'slug' => 'post-title',
            'excerpt' => 'Summary',
            'status' => 'draft',
            'visibility' => 'public',
            'published_at' => '2026-06-10T09:00:00Z',
            'scheduled_for' => '2026-06-10T09:00:00Z',
            'canonical_url' => 'https://example.com/post-title',
            'content_version' => 1,
            'reading_time_minutes' => 4,
            'word_count' => 850,
            'is_featured' => false,
            'meta' => [],
            'author' => [
                'id' => 7,
                'name' => 'Editor One',
                'email' => 'editor@example.com',
            ],
            'category' => [
                'id' => 3,
                'name' => 'Editorial',
                'slug' => 'editorial',
            ],
            'template' => null,
            'featured_media' => null,
            'tags' => [],
            'blocks' => [],
            'created_at' => '2026-06-10T09:00:00Z',
            'updated_at' => '2026-06-12T09:00:00Z',
        ], $overrides);
    }
}
