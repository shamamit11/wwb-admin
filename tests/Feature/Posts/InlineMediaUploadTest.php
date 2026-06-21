<?php

namespace Tests\Feature\Posts;

use Illuminate\Http\Client\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class InlineMediaUploadTest extends TestCase
{
    protected string $apiBaseUrl;

    protected function setUp(): void
    {
        parent::setUp();

        $this->apiBaseUrl = rtrim(config('widewebblog.api.base_url'), '/');
    }

    public function test_inline_media_upload_proxies_through_backend_media_api_and_returns_editor_shape(): void
    {
        Http::fake(function (Request $request) {
            if ($request->method() === 'POST' && $request->url() === $this->apiBaseUrl.'/admin/media') {
                $body = $request->body();

                $this->assertStringContainsString('name="file"; filename="inline-diagram.webp"', $body);
                $this->assertStringContainsString('name="alt_text"', $body);
                $this->assertStringContainsString('Architecture diagram', $body);
                $this->assertStringContainsString('name="caption"', $body);
                $this->assertStringContainsString('Queue worker flow', $body);
                $this->assertStringContainsString('name="source_type"', $body);
                $this->assertStringContainsString('uploaded', $body);
                $this->assertTrue($request->hasHeader('Authorization', 'Bearer test-token'));

                return Http::response([
                    'data' => [
                        'id' => 123,
                        'original_filename' => 'inline-diagram.webp',
                        'alt_text' => 'Architecture diagram',
                        'caption' => 'Queue worker flow',
                        'mime_type' => 'image/webp',
                        'url' => 'uploads/media/inline-diagram.webp',
                    ],
                ], 201);
            }

            return Http::response(['message' => 'Unexpected request.'], 500);
        });

        $response = $this->withSession($this->authenticatedSession())
            ->post(route('posts.inline-media.store'), [
                'file' => UploadedFile::fake()->image('inline-diagram.webp', 1200, 800),
                'alt_text' => 'Architecture diagram',
                'caption' => 'Queue worker flow',
            ], [
                'Accept' => 'application/json',
            ]);

        $response
            ->assertCreated()
            ->assertJsonPath('data.id', 123)
            ->assertJsonPath('data.url', rtrim((string) config('widewebblog.media.base_url'), '/').'/uploads/media/inline-diagram.webp')
            ->assertJsonPath('data.alt_text', 'Architecture diagram')
            ->assertJsonPath('data.is_image', true);
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
}
