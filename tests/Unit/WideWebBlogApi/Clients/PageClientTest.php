<?php

namespace Tests\Unit\WideWebBlogApi\Clients;

use App\Services\WideWebBlogApi\Clients\PageClient;
use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class PageClientTest extends TestCase
{
    protected string $apiBaseUrl;

    protected function setUp(): void
    {
        parent::setUp();

        $this->apiBaseUrl = rtrim(config('widewebblog.api.base_url'), '/');
    }

    public function test_it_can_load_pages_with_documented_filters(): void
    {
        Http::fake(function (Request $request) {
            $this->assertSame('GET', $request->method());
            $this->assertStringContainsString('/admin/pages?', $request->url());
            $this->assertStringContainsString('search=privacy', $request->url());
            $this->assertStringContainsString('status=published', $request->url());
            $this->assertStringContainsString('type=legal', $request->url());
            $this->assertStringContainsString('visibility=public', $request->url());
            $this->assertStringContainsString('sort=-updated_at', $request->url());

            return Http::response([
                'data' => [
                    ['id' => 1, 'title' => 'Privacy Policy'],
                ],
            ], 200);
        });

        $response = app(PageClient::class)->index('test-token', 'Bearer', [
            'search' => 'privacy',
            'status' => 'published',
            'type' => 'legal',
            'visibility' => 'public',
            'sort' => '-updated_at',
        ]);

        $this->assertSame('Privacy Policy', $response['data'][0]['title']);
    }

    public function test_it_can_store_update_and_delete_pages(): void
    {
        Http::fake(function (Request $request) {
            if ($request->method() === 'POST' && $request->url() === $this->apiBaseUrl.'/admin/pages') {
                $this->assertSame('Privacy Policy', $request['title']);
                $this->assertSame('legal', $request['type']);
                $this->assertSame('draft', $request['status']);
                $this->assertSame('# Policy', $request['content_markdown']);
                $this->assertSame(['legal', 'footer-link'], $request['meta']);

                return Http::response([
                    'data' => ['id' => 8, 'title' => 'Privacy Policy'],
                ], 201);
            }

            if ($request->method() === 'PUT' && $request->url() === $this->apiBaseUrl.'/admin/pages/8') {
                $this->assertSame('Updated Privacy Policy', $request['title']);
                $this->assertSame('published', $request['status']);

                return Http::response([
                    'data' => ['id' => 8, 'title' => 'Updated Privacy Policy'],
                ], 200);
            }

            if ($request->method() === 'DELETE' && $request->url() === $this->apiBaseUrl.'/admin/pages/8') {
                return Http::response([], 204);
            }

            return Http::response(['message' => 'Unexpected request.'], 500);
        });

        $created = app(PageClient::class)->store('test-token', 'Bearer', [
            'title' => 'Privacy Policy',
            'type' => 'legal',
            'status' => 'draft',
            'content_markdown' => '# Policy',
            'visibility' => 'public',
            'meta' => ['legal', 'footer-link'],
        ]);

        $updated = app(PageClient::class)->update('test-token', 'Bearer', 8, [
            'title' => 'Updated Privacy Policy',
            'type' => 'legal',
            'status' => 'published',
            'content_markdown' => '# Policy',
            'visibility' => 'public',
        ]);

        app(PageClient::class)->delete('test-token', 'Bearer', 8);

        $this->assertSame(8, $created['data']['id']);
        $this->assertSame('Updated Privacy Policy', $updated['data']['title']);
    }
}
