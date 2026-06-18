<?php

namespace Tests\Integration;

use App\Services\WideWebBlogApi\Clients\PostClient;
use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class PostClientTest extends TestCase
{
    protected string $apiBaseUrl;

    protected function setUp(): void
    {
        parent::setUp();

        $this->apiBaseUrl = rtrim(config('widewebblog.api.base_url'), '/');
    }

    public function test_post_client_uses_documented_post_endpoints_and_payloads(): void
    {
        Http::fake(function (Request $request) {
            return match (true) {
                $request->method() === 'GET' && str_starts_with($request->url(), $this->apiBaseUrl.'/admin/posts?') => Http::response(['data' => []], 200),
                $request->method() === 'POST' && $request->url() === $this->apiBaseUrl.'/admin/posts' => Http::response(['data' => ['id' => 9]], 201),
                $request->method() === 'GET' && $request->url() === $this->apiBaseUrl.'/admin/posts/10' => Http::response(['data' => ['id' => 10]], 200),
                $request->method() === 'PUT' && $request->url() === $this->apiBaseUrl.'/admin/posts/10' => Http::response(['data' => ['id' => 10]], 200),
                $request->method() === 'POST' && $request->url() === $this->apiBaseUrl.'/admin/posts/11/publish' => Http::response(['data' => ['id' => 11]], 200),
                $request->method() === 'POST' && $request->url() === $this->apiBaseUrl.'/admin/posts/12/schedule' => Http::response(['data' => ['id' => 12]], 200),
                $request->method() === 'POST' && $request->url() === $this->apiBaseUrl.'/admin/posts/13/unpublish' => Http::response(['data' => ['id' => 13]], 200),
                $request->method() === 'POST' && $request->url() === $this->apiBaseUrl.'/admin/posts/15/suggest-metadata' => Http::response(['data' => ['id' => 15]], 202),
                $request->method() === 'POST' && $request->url() === $this->apiBaseUrl.'/admin/posts/16/refine-title-excerpt' => Http::response(['data' => ['id' => 16]], 202),
                $request->method() === 'DELETE' && $request->url() === $this->apiBaseUrl.'/admin/posts/14' => Http::response([], 204),
                default => Http::response(['message' => 'Unexpected request.'], 500),
            };
        });

        $client = app(PostClient::class);

        $client->index('test-token', 'Bearer', [
            'search' => 'architecture',
            'status' => 'draft',
            'visibility' => 'private',
            'is_featured' => 1,
            'sort' => '-updated_at',
        ]);
        $client->store('test-token', 'Bearer', [
            'title' => 'Architecture Review',
            'category_id' => 4,
            'status' => 'draft',
            'visibility' => 'private',
            'tag_ids' => [7],
            'blocks' => [
                [
                    'block_type' => 'paragraph',
                    'sort_order' => 1,
                    'content' => ['Lead paragraph'],
                ],
            ],
        ]);
        $client->show('test-token', 'Bearer', 10);
        $client->update('test-token', 'Bearer', 10, [
            'title' => 'Architecture Review Updated',
            'category_id' => 4,
            'status' => 'published',
            'visibility' => 'public',
            'blocks' => [
                [
                    'block_type' => 'paragraph',
                    'sort_order' => 1,
                    'content' => ['Updated lead'],
                ],
            ],
        ]);
        $client->publish('test-token', 'Bearer', 11);
        $client->schedule('test-token', 'Bearer', 12, [
            'scheduled_for' => '2026-06-18T10:00:00Z',
        ]);
        $client->unpublish('test-token', 'Bearer', 13);
        $client->delete('test-token', 'Bearer', 14);
        $client->suggestMetadata('test-token', 'Bearer', 15, [
            'instructions' => 'Prioritize search intent clarity.',
            'prompt_template_key' => 'post_metadata_review_default',
        ]);
        $client->refineTitleExcerpt('test-token', 'Bearer', 16, [
            'instructions' => 'Tighten the title and excerpt.',
            'prompt_template_key' => 'post_title_excerpt_refinement_default',
        ]);

        Http::assertSent(function (Request $request): bool {
            $url = $request->url();

            return $request->method() === 'GET'
                && str_starts_with($url, $this->apiBaseUrl.'/admin/posts')
                && str_contains($url, 'search=architecture')
                && str_contains($url, 'status=draft')
                && str_contains($url, 'visibility=private')
                && str_contains($url, 'is_featured=1')
                && str_contains($url, 'sort=-updated_at')
                && $request->hasHeader('Authorization', 'Bearer test-token');
        });

        Http::assertSent(function (Request $request): bool {
            return $request->method() === 'POST'
                && $request->url() === $this->apiBaseUrl.'/admin/posts'
                && $request['title'] === 'Architecture Review'
                && $request['category_id'] === 4
                && $request['blocks'][0]['content'][0] === 'Lead paragraph';
        });

        Http::assertSent(function (Request $request): bool {
            return $request->method() === 'GET'
                && $request->url() === $this->apiBaseUrl.'/admin/posts/10';
        });

        Http::assertSent(function (Request $request): bool {
            return $request->method() === 'PUT'
                && $request->url() === $this->apiBaseUrl.'/admin/posts/10'
                && $request['title'] === 'Architecture Review Updated'
                && $request['status'] === 'published';
        });

        Http::assertSent(function (Request $request): bool {
            return $request->method() === 'POST'
                && $request->url() === $this->apiBaseUrl.'/admin/posts/11/publish';
        });

        Http::assertSent(function (Request $request): bool {
            return $request->method() === 'POST'
                && $request->url() === $this->apiBaseUrl.'/admin/posts/12/schedule'
                && $request['scheduled_for'] === '2026-06-18T10:00:00Z';
        });

        Http::assertSent(function (Request $request): bool {
            return $request->method() === 'POST'
                && $request->url() === $this->apiBaseUrl.'/admin/posts/13/unpublish';
        });

        Http::assertSent(function (Request $request): bool {
            return $request->method() === 'DELETE'
                && $request->url() === $this->apiBaseUrl.'/admin/posts/14';
        });

        Http::assertSent(function (Request $request): bool {
            return $request->method() === 'POST'
                && $request->url() === $this->apiBaseUrl.'/admin/posts/15/suggest-metadata'
                && $request['instructions'] === 'Prioritize search intent clarity.'
                && $request['prompt_template_key'] === 'post_metadata_review_default';
        });

        Http::assertSent(function (Request $request): bool {
            return $request->method() === 'POST'
                && $request->url() === $this->apiBaseUrl.'/admin/posts/16/refine-title-excerpt'
                && $request['instructions'] === 'Tighten the title and excerpt.'
                && $request['prompt_template_key'] === 'post_title_excerpt_refinement_default';
        });
    }
}
