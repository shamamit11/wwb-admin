<?php

namespace Tests\Unit\WideWebBlogApi\Clients;

use App\Services\WideWebBlogApi\Clients\ContentTopicClient;
use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class ContentTopicClientTest extends TestCase
{
    protected string $apiBaseUrl;

    protected function setUp(): void
    {
        parent::setUp();

        $this->apiBaseUrl = rtrim(config('widewebblog.api.base_url'), '/');
    }

    public function test_it_can_load_topics_with_documented_filters(): void
    {
        Http::fake(function (Request $request) {
            $this->assertSame('GET', $request->method());
            $this->assertStringContainsString('/admin/content-topics?', $request->url());
            $this->assertStringContainsString('search=agent', $request->url());
            $this->assertStringContainsString('status=suggested', $request->url());
            $this->assertStringContainsString('category_id=5', $request->url());
            $this->assertStringContainsString('cluster=ai_tools', $request->url());
            $this->assertStringContainsString('source=ai_suggested', $request->url());
            $this->assertStringContainsString('sort=-priority_score', $request->url());

            return Http::response([
                'data' => [
                    ['id' => 1, 'title' => 'Agent workflow topic'],
                ],
            ], 200);
        });

        $response = app(ContentTopicClient::class)->index('test-token', 'Bearer', [
            'search' => 'agent',
            'status' => 'suggested',
            'category_id' => 5,
            'cluster' => 'ai_tools',
            'source' => 'ai_suggested',
            'sort' => '-priority_score',
        ]);

        $this->assertSame('Agent workflow topic', $response['data'][0]['title']);
    }

    public function test_it_can_update_and_transition_topics(): void
    {
        Http::fake(function (Request $request) {
            if ($request->method() === 'PATCH' && $request->url() === $this->apiBaseUrl.'/admin/content-topics/8') {
                $this->assertSame('Updated Topic', $request['title']);
                $this->assertSame(5, $request['category_id']);
                $this->assertSame('ai_tools', $request['cluster']);
                $this->assertSame('manual', $request['source']);
                $this->assertSame(['automation', 'workflow'], $request['secondary_keywords']);

                return Http::response([
                    'data' => ['id' => 8, 'title' => 'Updated Topic', 'status' => 'suggested'],
                ], 200);
            }

            if ($request->method() === 'POST' && $request->url() === $this->apiBaseUrl.'/admin/content-topics/8/approve') {
                $this->assertSame('Looks aligned.', $request['notes']);

                return Http::response([
                    'data' => ['id' => 8, 'title' => 'Updated Topic', 'status' => 'approved'],
                ], 200);
            }

            if ($request->method() === 'POST' && $request->url() === $this->apiBaseUrl.'/admin/content-topics/8/reject') {
                $this->assertSame('Needs a sharper angle.', $request['notes']);

                return Http::response([
                    'data' => ['id' => 8, 'title' => 'Updated Topic', 'status' => 'rejected'],
                ], 200);
            }

            if ($request->method() === 'POST' && $request->url() === $this->apiBaseUrl.'/admin/content-topics/8/mark-used') {
                $this->assertSame('Assigned to the editorial calendar.', $request['notes']);

                return Http::response([
                    'data' => ['id' => 8, 'title' => 'Updated Topic', 'status' => 'used'],
                ], 200);
            }

            if ($request->method() === 'POST' && $request->url() === $this->apiBaseUrl.'/admin/content-topics/8/generate-draft') {
                return Http::response([
                    'data' => ['id' => 55, 'type' => 'blog_writer', 'status' => 'queued'],
                ], 202);
            }

            return Http::response(['message' => 'Unexpected request.'], 500);
        });

        $updated = app(ContentTopicClient::class)->update('test-token', 'Bearer', 8, [
            'title' => 'Updated Topic',
            'category_id' => 5,
            'cluster' => 'ai_tools',
            'source' => 'manual',
            'secondary_keywords' => ['automation', 'workflow'],
        ]);

        $approved = app(ContentTopicClient::class)->approve('test-token', 'Bearer', 8, [
            'notes' => 'Looks aligned.',
        ]);

        $rejected = app(ContentTopicClient::class)->reject('test-token', 'Bearer', 8, [
            'notes' => 'Needs a sharper angle.',
        ]);
        $used = app(ContentTopicClient::class)->markUsed('test-token', 'Bearer', 8, [
            'notes' => 'Assigned to the editorial calendar.',
        ]);
        $draft = app(ContentTopicClient::class)->generateDraft('test-token', 'Bearer', 8);

        $this->assertSame('Updated Topic', $updated['data']['title']);
        $this->assertSame('approved', $approved['data']['status']);
        $this->assertSame('rejected', $rejected['data']['status']);
        $this->assertSame('used', $used['data']['status']);
        $this->assertSame(55, $draft['data']['id']);
    }
}
