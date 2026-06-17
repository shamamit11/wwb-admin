<?php

namespace Tests\Feature\Seo;

use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class SeoIndexTest extends TestCase
{
    protected string $apiBaseUrl;

    protected function setUp(): void
    {
        parent::setUp();

        $this->apiBaseUrl = rtrim(config('widewebblog.api.base_url'), '/');
    }

    public function test_seo_screen_surfaces_post_score_and_schema_visibility(): void
    {
        Http::fake(function (Request $request) {
            if ($request->method() === 'GET' && str_starts_with($request->url(), $this->apiBaseUrl.'/admin/posts')) {
                return Http::response([
                    'data' => [
                        $this->postResource(['id' => 1, 'title' => 'Agent Systems Playbook', 'slug' => 'agent-systems-playbook']),
                    ],
                ], 200);
            }

            if ($request->method() === 'GET' && $request->url() === $this->apiBaseUrl.'/admin/seo/score/post/1') {
                return Http::response([
                    'data' => $this->scoreResource(),
                ], 200);
            }

            if ($request->method() === 'GET' && $request->url() === $this->apiBaseUrl.'/admin/seo/schema/post/1') {
                return Http::response([
                    'data' => $this->schemaResource(),
                ], 200);
            }

            return Http::response(['message' => 'Unexpected request.'], 500);
        });

        $response = $this->withSession($this->authenticatedSession())
            ->get(route('seo.index'));

        $response
            ->assertOk()
            ->assertSee('SEO')
            ->assertSee('The current service supports per-entity reads. Broader review endpoints remain out of scope.')
            ->assertSee('Agent Systems Playbook')
            ->assertSee('SEO 78')
            ->assertSee('Good')
            ->assertSee('Metadata')
            ->assertSee('Tighten the meta description.')
            ->assertSee('Schema Output')
            ->assertSee('TechArticle')
            ->assertSee('Generated JSON-LD');
    }

    public function test_seo_screen_handles_empty_post_inventory_without_inventing_sitewide_endpoints(): void
    {
        Http::fake(function (Request $request) {
            if ($request->method() === 'GET' && str_starts_with($request->url(), $this->apiBaseUrl.'/admin/posts')) {
                return Http::response(['data' => []], 200);
            }

            return Http::response(['message' => 'Unexpected request.'], 500);
        });

        $response = $this->withSession($this->authenticatedSession())
            ->get(route('seo.index'));

        $response
            ->assertOk()
            ->assertSee('No posts available for SEO review.')
            ->assertSee('Select a post to inspect score and schema output.');
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
            'status' => 'published',
            'updated_at' => '2026-06-17T09:00:00Z',
            'category' => [
                'id' => 4,
                'name' => 'AI Systems',
                'slug' => 'ai-systems',
            ],
        ], $overrides);
    }

    protected function scoreResource(): array
    {
        return [
            'seoable_type' => 'post',
            'seoable_id' => 1,
            'total_score' => 78,
            'max_score' => 100,
            'grade' => 'good',
            'advisory' => true,
            'subscores' => [
                'metadata' => ['score' => 30, 'max_score' => 35, 'suggestion_count' => 1],
                'content' => ['score' => 22, 'max_score' => 30, 'suggestion_count' => 0],
                'schema' => ['score' => 16, 'max_score' => 20, 'suggestion_count' => 0],
                'internal_linking' => ['score' => 10, 'max_score' => 15, 'suggestion_count' => 2],
            ],
            'recommendations' => [
                'Tighten the meta description.',
                'Add one more contextual internal link.',
            ],
        ];
    }

    protected function schemaResource(): array
    {
        return [
            '@context' => 'https://schema.org',
            '@graph' => [
                ['@type' => 'Organization'],
                ['@type' => 'WebSite'],
                ['@type' => 'BreadcrumbList'],
                ['@type' => 'TechArticle', 'headline' => 'Agent Systems Playbook'],
            ],
        ];
    }
}
