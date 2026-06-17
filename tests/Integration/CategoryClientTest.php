<?php

namespace Tests\Integration;

use App\Services\WideWebBlogApi\Clients\CategoryClient;
use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class CategoryClientTest extends TestCase
{
    protected string $apiBaseUrl;

    protected function setUp(): void
    {
        parent::setUp();

        $this->apiBaseUrl = rtrim(config('widewebblog.api.base_url'), '/');
    }

    public function test_category_client_uses_documented_crud_endpoints_and_payloads(): void
    {
        Http::fake([
            $this->apiBaseUrl.'/admin/categories' => Http::response(['data' => []], 200),
            $this->apiBaseUrl.'/admin/categories/10' => Http::response(['data' => ['id' => 10]], 200),
            $this->apiBaseUrl.'/admin/categories/11' => Http::response(['data' => ['id' => 11]], 200),
            $this->apiBaseUrl.'/admin/categories/12' => Http::response([], 204),
        ]);

        $client = app(CategoryClient::class);

        $client->index('test-token');
        $client->show('test-token', 'Bearer', 10);
        $client->store('test-token', 'Bearer', [
            'name' => 'AI Agents',
            'slug' => 'ai-agents',
            'description' => 'Technical content about agents.',
            'parent_id' => null,
            'is_active' => true,
            'sort_order' => 10,
        ]);
        $client->update('test-token', 'Bearer', 11, [
            'name' => 'AI Systems',
            'slug' => 'ai-systems',
            'description' => 'Updated description.',
            'parent_id' => null,
            'is_active' => false,
            'sort_order' => 2,
        ]);
        $client->delete('test-token', 'Bearer', 12);

        Http::assertSent(function (Request $request): bool {
            return $request->method() === 'GET'
                && $request->url() === $this->apiBaseUrl.'/admin/categories'
                && $request->hasHeader('Authorization', 'Bearer test-token');
        });

        Http::assertSent(function (Request $request): bool {
            return $request->method() === 'GET'
                && $request->url() === $this->apiBaseUrl.'/admin/categories/10';
        });

        Http::assertSent(function (Request $request): bool {
            return $request->method() === 'POST'
                && $request->url() === $this->apiBaseUrl.'/admin/categories'
                && $request['name'] === 'AI Agents'
                && $request['sort_order'] === 10;
        });

        Http::assertSent(function (Request $request): bool {
            return $request->method() === 'PUT'
                && $request->url() === $this->apiBaseUrl.'/admin/categories/11'
                && $request['slug'] === 'ai-systems'
                && $request['is_active'] === false;
        });

        Http::assertSent(function (Request $request): bool {
            return $request->method() === 'DELETE'
                && $request->url() === $this->apiBaseUrl.'/admin/categories/12';
        });
    }
}
