<?php

namespace Tests\Unit\WideWebBlogApi\Clients;

use App\Services\WideWebBlogApi\Clients\NewsItemClient;
use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class NewsItemClientTest extends TestCase
{
    protected string $apiBaseUrl;

    protected function setUp(): void
    {
        parent::setUp();

        $this->apiBaseUrl = rtrim(config('widewebblog.api.base_url'), '/');
    }

    public function test_it_can_load_news_items_with_documented_filters(): void
    {
        Http::fake(function (Request $request) {
            $this->assertSame('GET', $request->method());
            $this->assertStringContainsString('/admin/news-items?', $request->url());
            $this->assertStringContainsString('search=agent', $request->url());
            $this->assertStringContainsString('status=screened', $request->url());
            $this->assertStringContainsString('category_id=5', $request->url());
            $this->assertStringContainsString('decision=topic', $request->url());
            $this->assertStringContainsString('route=knowledge_base', $request->url());
            $this->assertStringContainsString('sort=-published_at', $request->url());

            return Http::response([
                'data' => [
                    ['id' => 1, 'title' => 'Agent workflow article'],
                ],
            ], 200);
        });

        $response = app(NewsItemClient::class)->index('test-token', 'Bearer', [
            'search' => 'agent',
            'status' => 'screened',
            'category_id' => 5,
            'decision' => 'topic',
            'route' => 'knowledge_base',
            'sort' => '-published_at',
        ]);

        $this->assertSame('Agent workflow article', $response['data'][0]['title']);
    }

    public function test_it_can_show_and_mutate_news_items(): void
    {
        Http::fake(function (Request $request) {
            if ($request->method() === 'GET' && $request->url() === $this->apiBaseUrl.'/admin/news-items/8') {
                return Http::response([
                    'data' => ['id' => 8, 'status' => 'discovered'],
                ], 200);
            }

            if ($request->method() === 'POST' && $request->url() === $this->apiBaseUrl.'/admin/news-items/discover') {
                $this->assertSame(5, $request['category_id']);
                $this->assertSame(12, $request['limit']);
                $this->assertTrue($request['sync']);

                return Http::response([
                    'data' => [
                        ['id' => 18, 'title' => 'Fresh intake article'],
                    ],
                ], 201);
            }

            if ($request->method() === 'POST' && $request->url() === $this->apiBaseUrl.'/admin/news-items/8/score') {
                return Http::response([
                    'data' => ['id' => 8, 'status' => 'screened'],
                ], 202);
            }

            if ($request->method() === 'POST' && $request->url() === $this->apiBaseUrl.'/admin/news-items/8/extract') {
                return Http::response([
                    'data' => ['id' => 8, 'status' => 'extracted'],
                ], 202);
            }

            if ($request->method() === 'POST' && $request->url() === $this->apiBaseUrl.'/admin/news-items/8/route') {
                return Http::response([
                    'data' => ['id' => 8, 'status' => 'routed'],
                ], 202);
            }

            return Http::response(['message' => 'Unexpected request.'], 500);
        });

        $item = app(NewsItemClient::class)->show('test-token', 'Bearer', 8);
        $discovered = app(NewsItemClient::class)->discover('test-token', 'Bearer', [
            'category_id' => 5,
            'limit' => 12,
            'sync' => true,
        ]);
        $scored = app(NewsItemClient::class)->score('test-token', 'Bearer', 8);
        $extracted = app(NewsItemClient::class)->extract('test-token', 'Bearer', 8);
        $routed = app(NewsItemClient::class)->route('test-token', 'Bearer', 8);

        $this->assertSame('discovered', $item['data']['status']);
        $this->assertSame('Fresh intake article', $discovered['data'][0]['title']);
        $this->assertSame('screened', $scored['data']['status']);
        $this->assertSame('extracted', $extracted['data']['status']);
        $this->assertSame('routed', $routed['data']['status']);
    }
}
