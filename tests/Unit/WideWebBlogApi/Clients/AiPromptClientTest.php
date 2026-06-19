<?php

namespace Tests\Unit\WideWebBlogApi\Clients;

use App\Services\WideWebBlogApi\Clients\AiPromptClient;
use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class AiPromptClientTest extends TestCase
{
    protected string $apiBaseUrl;

    protected function setUp(): void
    {
        parent::setUp();

        $this->apiBaseUrl = rtrim(config('widewebblog.api.base_url'), '/');
    }

    public function test_it_can_load_ai_prompts_with_documented_filters(): void
    {
        Http::fake(function (Request $request) {
            $this->assertSame('GET', $request->method());
            $this->assertStringStartsWith($this->apiBaseUrl.'/admin/ai-prompts', $request->url());
            $this->assertStringContainsString('search=agent', $request->url());
            $this->assertStringContainsString('type=content_brief', $request->url());
            $this->assertStringContainsString('status=active', $request->url());
            $this->assertStringContainsString('sort=-updated_at', $request->url());

            return Http::response(['data' => []], 200);
        });

        $response = app(AiPromptClient::class)->index('token', 'Bearer', [
            'search' => 'agent',
            'type' => 'content_brief',
            'status' => 'active',
            'sort' => '-updated_at',
        ]);

        $this->assertSame([], $response['data']);
    }

    public function test_it_can_store_update_version_and_activate_ai_prompts(): void
    {
        $storePayload = [
            'name' => 'Content Brief Prompt',
            'key' => 'content-brief-main',
            'type' => 'content_brief',
            'status' => 'active',
            'initial_version' => [
                'system_prompt' => 'You are a brief writer.',
                'user_prompt' => 'Generate a content brief.',
                'output_schema' => ['title', 'sections'],
                'variables' => ['topic', 'audience'],
                'status' => 'active',
            ],
        ];

        $updatePayload = [
            'name' => 'Updated Content Brief Prompt',
            'key' => 'content-brief-main',
            'type' => 'content_brief',
            'status' => 'draft',
            'description' => 'Editorial brief guidance.',
        ];

        $versionPayload = [
            'system_prompt' => 'System v2',
            'user_prompt' => 'User v2',
            'output_schema' => ['summary'],
            'variables' => ['topic'],
            'status' => 'draft',
        ];

        Http::fake(function (Request $request) use ($storePayload, $updatePayload, $versionPayload) {
            if ($request->method() === 'POST' && $request->url() === $this->apiBaseUrl.'/admin/ai-prompts') {
                $this->assertSame($storePayload, $request->data());

                return Http::response(['data' => ['id' => 9]], 201);
            }

            if ($request->method() === 'PATCH' && $request->url() === $this->apiBaseUrl.'/admin/ai-prompts/9') {
                $this->assertSame($updatePayload, $request->data());

                return Http::response(['data' => ['id' => 9]], 200);
            }

            if ($request->method() === 'POST' && $request->url() === $this->apiBaseUrl.'/admin/ai-prompts/9/versions') {
                $this->assertSame($versionPayload, $request->data());

                return Http::response(['data' => ['id' => 22]], 201);
            }

            if ($request->method() === 'POST' && $request->url() === $this->apiBaseUrl.'/admin/ai-prompts/9/activate-version/22') {
                return Http::response(['data' => ['id' => 9, 'active_version_id' => 22]], 200);
            }

            return Http::response(['message' => 'Unexpected request.'], 500);
        });

        $client = app(AiPromptClient::class);

        $this->assertSame(9, $client->store('token', 'Bearer', $storePayload)['data']['id']);
        $this->assertSame(9, $client->update('token', 'Bearer', 9, $updatePayload)['data']['id']);
        $this->assertSame(22, $client->storeVersion('token', 'Bearer', 9, $versionPayload)['data']['id']);
        $this->assertSame(22, $client->activateVersion('token', 'Bearer', 9, 22)['data']['active_version_id']);
    }
}
