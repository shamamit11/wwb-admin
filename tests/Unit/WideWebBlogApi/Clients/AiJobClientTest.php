<?php

namespace Tests\Unit\WideWebBlogApi\Clients;

use App\Services\WideWebBlogApi\Clients\AiJobClient;
use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class AiJobClientTest extends TestCase
{
    protected string $apiBaseUrl;

    protected function setUp(): void
    {
        parent::setUp();

        $this->apiBaseUrl = rtrim(config('widewebblog.api.base_url'), '/');
    }

    public function test_it_can_load_ai_jobs_with_documented_filters(): void
    {
        Http::fake(function (Request $request) {
            $this->assertSame('GET', $request->method());
            $this->assertStringContainsString('/admin/ai-jobs?', $request->url());
            $this->assertStringContainsString('status=failed', $request->url());
            $this->assertStringContainsString('type=topic_discovery', $request->url());
            $this->assertStringContainsString('provider=openai', $request->url());
            $this->assertStringContainsString('model=gpt-5', $request->url());
            $this->assertStringContainsString('sort=-failed_at', $request->url());

            return Http::response([
                'data' => [
                    ['id' => 11, 'type' => 'topic_discovery'],
                ],
            ], 200);
        });

        $response = app(AiJobClient::class)->index('test-token', 'Bearer', [
            'status' => 'failed',
            'type' => 'topic_discovery',
            'provider' => 'openai',
            'model' => 'gpt-5',
            'sort' => '-failed_at',
        ]);

        $this->assertSame(11, $response['data'][0]['id']);
    }

    public function test_it_can_show_and_retry_ai_jobs(): void
    {
        Http::fake(function (Request $request) {
            if ($request->method() === 'GET' && $request->url() === $this->apiBaseUrl.'/admin/ai-jobs/11') {
                return Http::response([
                    'data' => ['id' => 11, 'status' => 'failed'],
                ], 200);
            }

            if ($request->method() === 'POST' && $request->url() === $this->apiBaseUrl.'/admin/ai-jobs/11/retry') {
                return Http::response([
                    'data' => ['id' => 11, 'status' => 'queued'],
                ], 202);
            }

            return Http::response(['message' => 'Unexpected request.'], 500);
        });

        $job = app(AiJobClient::class)->show('test-token', 'Bearer', 11);
        $retried = app(AiJobClient::class)->retry('test-token', 'Bearer', 11);

        $this->assertSame('failed', $job['data']['status']);
        $this->assertSame('queued', $retried['data']['status']);
    }

    public function test_it_can_start_topic_discovery_jobs(): void
    {
        Http::fake(function (Request $request) {
            if ($request->method() === 'POST' && $request->url() === $this->apiBaseUrl.'/admin/ai-jobs/topic-discovery') {
                $this->assertSame('ai_tools', $request['cluster']);
                $this->assertSame(12, $request['count']);
                $this->assertSame('Technical founders', $request['audience']);
                $this->assertSame('topic-discovery-editorial', $request['prompt_template_key']);
                $this->assertSame(['newsletter', 'q3'], $request['metadata']);

                return Http::response([
                    'data' => ['id' => 18, 'status' => 'queued'],
                ], 202);
            }

            return Http::response(['message' => 'Unexpected request.'], 500);
        });

        $response = app(AiJobClient::class)->topicDiscovery('test-token', 'Bearer', [
            'cluster' => 'ai_tools',
            'count' => 12,
            'audience' => 'Technical founders',
            'prompt_template_key' => 'topic-discovery-editorial',
            'metadata' => ['newsletter', 'q3'],
        ]);

        $this->assertSame(18, $response['data']['id']);
    }
}
