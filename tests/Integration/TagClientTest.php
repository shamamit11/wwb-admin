<?php

namespace Tests\Integration;

use App\Services\WideWebBlogApi\Clients\TagClient;
use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class TagClientTest extends TestCase
{
    protected string $apiBaseUrl;

    protected function setUp(): void
    {
        parent::setUp();

        $this->apiBaseUrl = rtrim(config('widewebblog.api.base_url'), '/');
    }

    public function test_tag_client_uses_documented_crud_endpoints_and_payloads(): void
    {
        Http::fake([
            $this->apiBaseUrl.'/admin/tags' => Http::response(['data' => []], 200),
            $this->apiBaseUrl.'/admin/tags/10' => Http::response(['data' => ['id' => 10]], 200),
            $this->apiBaseUrl.'/admin/tags/11' => Http::response(['data' => ['id' => 11]], 200),
            $this->apiBaseUrl.'/admin/tags/12' => Http::response([], 204),
        ]);

        $client = app(TagClient::class);

        $client->index('test-token');
        $client->show('test-token', 'Bearer', 10);
        $client->store('test-token', 'Bearer', [
            'name' => 'AI Agents',
            'slug' => 'ai-agents',
            'description' => 'Technical content about agents.',
            'is_active' => true,
        ]);
        $client->update('test-token', 'Bearer', 11, [
            'name' => 'Architecture',
            'slug' => 'architecture',
            'description' => 'Updated description.',
            'is_active' => false,
        ]);
        $client->delete('test-token', 'Bearer', 12);

        Http::assertSent(function (Request $request): bool {
            return $request->method() === 'GET'
                && $request->url() === $this->apiBaseUrl.'/admin/tags'
                && $request->hasHeader('Authorization', 'Bearer test-token');
        });

        Http::assertSent(function (Request $request): bool {
            return $request->method() === 'GET'
                && $request->url() === $this->apiBaseUrl.'/admin/tags/10';
        });

        Http::assertSent(function (Request $request): bool {
            return $request->method() === 'POST'
                && $request->url() === $this->apiBaseUrl.'/admin/tags'
                && $request['name'] === 'AI Agents'
                && $request['is_active'] === true;
        });

        Http::assertSent(function (Request $request): bool {
            return $request->method() === 'PUT'
                && $request->url() === $this->apiBaseUrl.'/admin/tags/11'
                && $request['slug'] === 'architecture'
                && $request['is_active'] === false;
        });

        Http::assertSent(function (Request $request): bool {
            return $request->method() === 'DELETE'
                && $request->url() === $this->apiBaseUrl.'/admin/tags/12';
        });
    }
}
