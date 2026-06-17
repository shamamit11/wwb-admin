<?php

namespace Tests\Integration;

use App\Services\WideWebBlogApi\Clients\MediaClient;
use Illuminate\Http\Client\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class MediaClientTest extends TestCase
{
    protected string $apiBaseUrl;

    protected function setUp(): void
    {
        parent::setUp();

        $this->apiBaseUrl = rtrim(config('widewebblog.api.base_url'), '/');
    }

    public function test_media_client_uses_documented_list_show_update_and_delete_endpoints(): void
    {
        Http::fake([
            $this->apiBaseUrl.'/admin/media*' => Http::response(['data' => []], 200),
            $this->apiBaseUrl.'/admin/media/10' => Http::response(['data' => ['id' => 10]], 200),
            $this->apiBaseUrl.'/admin/media/11' => Http::response(['data' => ['id' => 11]], 200),
            $this->apiBaseUrl.'/admin/media/12' => Http::response([], 204),
        ]);

        $client = app(MediaClient::class);

        $client->index('test-token', 'Bearer', [
            'search' => 'architecture',
            'source_type' => 'uploaded',
            'used' => 1,
            'is_image' => 1,
            'status' => 'ready',
        ]);
        $client->show('test-token', 'Bearer', 10);
        $client->update('test-token', 'Bearer', 11, [
            'alt_text' => 'Updated alt text',
            'caption' => 'Updated caption',
            'source_type' => 'stock',
            'source_url' => 'https://example.com/original',
            'attribution_text' => 'Example Provider',
        ]);
        $client->delete('test-token', 'Bearer', 12);

        Http::assertSent(function (Request $request): bool {
            $url = $request->url();

            return $request->method() === 'GET'
                && str_starts_with($url, $this->apiBaseUrl.'/admin/media')
                && str_contains($url, 'search=architecture')
                && str_contains($url, 'source_type=uploaded')
                && str_contains($url, 'used=1')
                && str_contains($url, 'is_image=1')
                && str_contains($url, 'status=ready')
                && $request->hasHeader('Authorization', 'Bearer test-token');
        });

        Http::assertSent(function (Request $request): bool {
            return $request->method() === 'GET'
                && $request->url() === $this->apiBaseUrl.'/admin/media/10';
        });

        Http::assertSent(function (Request $request): bool {
            return $request->method() === 'PUT'
                && $request->url() === $this->apiBaseUrl.'/admin/media/11'
                && $request['alt_text'] === 'Updated alt text'
                && $request['source_type'] === 'stock'
                && $request['source_url'] === 'https://example.com/original';
        });

        Http::assertSent(function (Request $request): bool {
            return $request->method() === 'DELETE'
                && $request->url() === $this->apiBaseUrl.'/admin/media/12';
        });
    }

    public function test_media_client_uses_documented_single_upload_multipart_payload(): void
    {
        Http::fake([
            $this->apiBaseUrl.'/admin/media' => Http::response(['data' => ['id' => 20]], 201),
        ]);

        $file = UploadedFile::fake()->image('diagram.webp', 1600, 900);

        app(MediaClient::class)->store('test-token', $file, [
            'alt_text' => 'Architecture diagram',
            'caption' => 'Agent memory map',
            'source_type' => 'uploaded',
        ], 'Bearer');

        Http::assertSent(function (Request $request): bool {
            $body = $request->body();

            return $request->method() === 'POST'
                && $request->url() === $this->apiBaseUrl.'/admin/media'
                && ! $request->hasHeader('Content-Type', 'application/json')
                && str_contains($body, 'name="file"; filename="diagram.webp"')
                && str_contains($body, 'name="source_type"')
                && str_contains($body, 'uploaded')
                && str_contains($body, 'name="alt_text"')
                && $request->hasHeader('Authorization', 'Bearer test-token');
        });
    }

    public function test_media_client_uses_documented_batch_upload_multipart_payload(): void
    {
        Http::fake([
            $this->apiBaseUrl.'/admin/media/batch' => Http::response(['data' => [['id' => 21], ['id' => 22]]], 200),
        ]);

        $files = [
            UploadedFile::fake()->image('first.png', 800, 600),
            UploadedFile::fake()->image('second.png', 640, 480),
        ];

        app(MediaClient::class)->batchStore('test-token', $files, [
            'source_type' => 'uploaded',
            'attribution_text' => 'Example Provider',
        ], 'Bearer');

        Http::assertSent(function (Request $request): bool {
            $body = $request->body();

            return $request->method() === 'POST'
                && $request->url() === $this->apiBaseUrl.'/admin/media/batch'
                && ! $request->hasHeader('Content-Type', 'application/json')
                && str_contains($body, 'name="files[]"; filename="first.png"')
                && str_contains($body, 'name="files[]"; filename="second.png"')
                && str_contains($body, 'name="source_type"')
                && str_contains($body, 'uploaded')
                && str_contains($body, 'name="attribution_text"')
                && $request->hasHeader('Authorization', 'Bearer test-token');
        });
    }
}
