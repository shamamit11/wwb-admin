<?php

namespace Tests\Unit\WideWebBlogApi\Clients;

use App\Services\WideWebBlogApi\Clients\ContentBriefClient;
use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class ContentBriefClientTest extends TestCase
{
    protected string $apiBaseUrl;

    protected function setUp(): void
    {
        parent::setUp();

        $this->apiBaseUrl = rtrim(config('widewebblog.api.base_url'), '/');
    }

    public function test_it_can_load_content_briefs_with_documented_filters(): void
    {
        Http::fake(function (Request $request) {
            $this->assertSame('GET', $request->method());
            $this->assertStringContainsString('/admin/content-briefs?', $request->url());
            $this->assertStringContainsString('search=agent', $request->url());
            $this->assertStringContainsString('status=draft', $request->url());
            $this->assertStringContainsString('content_topic_id=8', $request->url());
            $this->assertStringContainsString('sort=-approved_at', $request->url());

            return Http::response([
                'data' => [
                    ['id' => 14, 'title' => 'Agent Brief'],
                ],
            ], 200);
        });

        $response = app(ContentBriefClient::class)->index('test-token', 'Bearer', [
            'search' => 'agent',
            'status' => 'draft',
            'content_topic_id' => 8,
            'sort' => '-approved_at',
        ]);

        $this->assertSame(14, $response['data'][0]['id']);
    }

    public function test_it_can_update_approve_and_generate_drafts_from_briefs(): void
    {
        Http::fake(function (Request $request) {
            if ($request->method() === 'PATCH' && $request->url() === $this->apiBaseUrl.'/admin/content-briefs/14') {
                $this->assertSame('Updated Brief', $request['title']);
                $this->assertSame(['Heading one', 'Heading two'], $request['headings']);
                $this->assertSame('rejected', $request['status']);

                return Http::response([
                    'data' => ['id' => 14, 'title' => 'Updated Brief', 'status' => 'rejected'],
                ], 200);
            }

            if ($request->method() === 'POST' && $request->url() === $this->apiBaseUrl.'/admin/content-briefs/14/approve') {
                return Http::response([
                    'data' => ['id' => 14, 'status' => 'approved'],
                ], 200);
            }

            if ($request->method() === 'POST' && $request->url() === $this->apiBaseUrl.'/admin/content-briefs/14/generate-draft') {
                $this->assertSame(3, $request['category_id']);
                $this->assertSame(9, $request['template_id']);
                $this->assertSame('public', $request['visibility']);
                $this->assertSame('blog-writer-editorial', $request['prompt_template_key']);
                $this->assertSame('comparison', $request['generation_mode']);

                return Http::response([
                    'data' => ['id' => 33, 'status' => 'queued'],
                ], 202);
            }

            return Http::response(['message' => 'Unexpected request.'], 500);
        });

        $updated = app(ContentBriefClient::class)->update('test-token', 'Bearer', 14, [
            'title' => 'Updated Brief',
            'status' => 'rejected',
            'headings' => ['Heading one', 'Heading two'],
        ]);

        $approved = app(ContentBriefClient::class)->approve('test-token', 'Bearer', 14);

        $draft = app(ContentBriefClient::class)->generateDraft('test-token', 'Bearer', 14, [
            'category_id' => 3,
            'template_id' => 9,
            'visibility' => 'public',
            'prompt_template_key' => 'blog-writer-editorial',
            'generation_mode' => 'comparison',
        ]);

        $this->assertSame('Updated Brief', $updated['data']['title']);
        $this->assertSame('approved', $approved['data']['status']);
        $this->assertSame(33, $draft['data']['id']);
    }
}
