<?php

namespace Tests\Feature\Dashboard;

use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class DashboardIndexTest extends TestCase
{
    protected string $apiBaseUrl;

    protected function setUp(): void
    {
        parent::setUp();

        $this->apiBaseUrl = rtrim(config('widewebblog.api.base_url'), '/');
    }

    public function test_dashboard_renders_ai_workflow_cards_and_recent_jobs(): void
    {
        Http::fake(function (Request $request) {
            $url = $request->url();

            if ($request->method() === 'GET' && str_starts_with($url, $this->apiBaseUrl.'/admin/posts')) {
                if (str_contains($url, 'status=draft')) {
                    return Http::response(['data' => [
                        $this->postResource(['id' => 1, 'title' => 'Draft One', 'status' => 'draft']),
                        $this->postResource(['id' => 2, 'title' => 'Draft Two', 'status' => 'draft']),
                    ]], 200);
                }

                if (str_contains($url, 'status=published')) {
                    return Http::response(['data' => [
                        $this->postResource(['id' => 3, 'title' => 'Published One', 'status' => 'published']),
                    ]], 200);
                }
            }

            if ($request->method() === 'GET' && str_starts_with($url, $this->apiBaseUrl.'/admin/content-topics')) {
                if (str_contains($url, 'status=suggested')) {
                    return Http::response(['data' => [
                        ['id' => 8, 'title' => 'Suggested topic'],
                    ]], 200);
                }

                if (str_contains($url, 'status=approved')) {
                    return Http::response(['data' => [
                        ['id' => 9, 'title' => 'Approved topic'],
                        ['id' => 10, 'title' => 'Approved topic two'],
                    ]], 200);
                }
            }

            if ($request->method() === 'GET' && str_starts_with($url, $this->apiBaseUrl.'/admin/content-briefs')) {
                if (str_contains($url, 'status=draft')) {
                    return Http::response(['data' => [
                        ['id' => 14, 'title' => 'Draft brief'],
                    ]], 200);
                }

                if (str_contains($url, 'status=approved')) {
                    return Http::response(['data' => [
                        ['id' => 15, 'title' => 'Approved brief'],
                        ['id' => 16, 'title' => 'Approved brief two'],
                    ]], 200);
                }
            }

            if ($request->method() === 'GET' && str_starts_with($url, $this->apiBaseUrl.'/admin/ai-jobs')) {
                if (str_contains($url, 'status=failed')) {
                    return Http::response(['data' => [
                        $this->jobResource(['id' => 22, 'status' => 'failed', 'type' => 'content_brief']),
                    ]], 200);
                }

                return Http::response(['data' => [
                    $this->jobResource(['id' => 20, 'status' => 'completed', 'type' => 'topic_discovery']),
                    $this->jobResource(['id' => 21, 'status' => 'failed', 'type' => 'content_brief']),
                ]], 200);
            }

            return Http::response(['message' => 'Unexpected request.'], 500);
        });

        $response = $this->withSession($this->authenticatedSession())
            ->get(route('dashboard'));

        $response
            ->assertOk()
            ->assertSee('Operational Snapshot')
            ->assertSee('Suggested Topics')
            ->assertSee('Approved Topics')
            ->assertSee('Draft Briefs')
            ->assertSee('Approved Briefs')
            ->assertSee('Draft Posts Pending Review')
            ->assertSee('Failed AI Jobs')
            ->assertSee('Recent AI Jobs')
            ->assertSee('Job #20')
            ->assertSee('Draft One');
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
        return array_replace([
            'id' => 1,
            'title' => 'Post title',
            'slug' => 'post-title',
            'status' => 'draft',
            'visibility' => 'public',
            'published_at' => now()->toISOString(),
            'scheduled_for' => null,
            'canonical_url' => 'https://example.com/post-title',
            'content_version' => 1,
            'reading_time_minutes' => 3,
            'word_count' => 900,
            'is_featured' => false,
            'meta' => [],
            'author' => [
                'id' => 1,
                'name' => 'Admin User',
                'email' => 'admin@example.com',
            ],
            'category' => [
                'id' => 4,
                'name' => 'AI',
                'slug' => 'ai',
            ],
            'blocks' => [],
            'created_at' => now()->subDay()->toISOString(),
            'updated_at' => now()->toISOString(),
        ], $overrides);
    }

    protected function jobResource(array $overrides = []): array
    {
        return array_replace([
            'id' => 20,
            'type' => 'topic_discovery',
            'status' => 'completed',
            'entity_type' => 'content_topic',
            'entity_id' => 8,
            'provider' => 'openai',
            'model' => 'gpt-5',
            'created_at' => now()->subHour()->toISOString(),
            'completed_at' => now()->toISOString(),
            'failed_at' => null,
        ], $overrides);
    }
}
