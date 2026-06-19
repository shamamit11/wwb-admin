<?php

namespace Tests\Unit\WideWebBlogApi\Clients;

use App\Services\WideWebBlogApi\Clients\KnowledgeBaseClient;
use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class KnowledgeBaseClientTest extends TestCase
{
    protected string $apiBaseUrl;

    protected function setUp(): void
    {
        parent::setUp();

        $this->apiBaseUrl = rtrim(config('widewebblog.api.base_url'), '/');
    }

    public function test_it_can_load_knowledge_base_entries_with_filters(): void
    {
        Http::fake(function (Request $request) {
            $this->assertSame('GET', $request->method());
            $this->assertStringContainsString('/admin/knowledge-base?', $request->url());
            $this->assertStringContainsString('search=memory', $request->url());
            $this->assertStringContainsString('status=active', $request->url());
            $this->assertStringContainsString('entry_type=research', $request->url());
            $this->assertStringContainsString('sort=-updated_at', $request->url());

            return Http::response([
                'data' => [
                    ['id' => 1, 'title' => 'Agent memory note'],
                ],
            ], 200);
        });

        $response = app(KnowledgeBaseClient::class)->index('test-token', 'Bearer', [
            'search' => 'memory',
            'status' => 'active',
            'entry_type' => 'research',
            'sort' => '-updated_at',
        ]);

        $this->assertSame('Agent memory note', $response['data'][0]['title']);
    }

    public function test_it_can_store_and_update_knowledge_base_entries(): void
    {
        Http::fake(function (Request $request) {
            if ($request->method() === 'POST' && $request->url() === $this->apiBaseUrl.'/admin/knowledge-base') {
                $this->assertSame('Research note', $request['title']);
                $this->assertSame('research', $request['entry_type']);
                $this->assertSame('draft', $request['status']);
                $this->assertSame("# Heading\n\nReference content", $request['content_markdown']);
                $this->assertSame(['agent-memory', 'research'], $request['metadata']);

                return Http::response([
                    'data' => ['id' => 10, 'title' => 'Research note'],
                ], 201);
            }

            if ($request->method() === 'PUT' && $request->url() === $this->apiBaseUrl.'/admin/knowledge-base/10') {
                $this->assertSame('Updated research note', $request['title']);
                $this->assertSame('active', $request['status']);

                return Http::response([
                    'data' => ['id' => 10, 'title' => 'Updated research note'],
                ], 200);
            }

            return Http::response(['message' => 'Unexpected request.'], 500);
        });

        $created = app(KnowledgeBaseClient::class)->store('test-token', 'Bearer', [
            'title' => 'Research note',
            'entry_type' => 'research',
            'status' => 'draft',
            'content_markdown' => "# Heading\n\nReference content",
            'metadata' => ['agent-memory', 'research'],
        ]);

        $updated = app(KnowledgeBaseClient::class)->update('test-token', 'Bearer', 10, [
            'title' => 'Updated research note',
            'entry_type' => 'research',
            'status' => 'active',
            'content_markdown' => "# Heading\n\nReference content",
        ]);

        $this->assertSame(10, $created['data']['id']);
        $this->assertSame('Updated research note', $updated['data']['title']);
    }

    public function test_it_can_link_knowledge_base_entries_to_posts_and_topics(): void
    {
        Http::fake(function (Request $request) {
            if ($request->method() === 'POST' && $request->url() === $this->apiBaseUrl.'/admin/knowledge-base/10/link-post') {
                $this->assertSame(55, $request['post_id']);

                return Http::response([
                    'data' => [
                        'id' => 10,
                        'linked_posts' => [
                            ['id' => 55, 'title' => 'Related Post'],
                        ],
                    ],
                ], 200);
            }

            if ($request->method() === 'POST' && $request->url() === $this->apiBaseUrl.'/admin/knowledge-base/10/link-topic') {
                $this->assertSame(77, $request['topic_id']);

                return Http::response([
                    'data' => [
                        'id' => 10,
                        'linked_topics' => [
                            ['id' => 77, 'title' => 'Related Topic'],
                        ],
                    ],
                ], 200);
            }

            return Http::response(['message' => 'Unexpected request.'], 500);
        });

        $linkedPost = app(KnowledgeBaseClient::class)->linkPost('test-token', 'Bearer', 10, [
            'post_id' => 55,
        ]);

        $linkedTopic = app(KnowledgeBaseClient::class)->linkTopic('test-token', 'Bearer', 10, [
            'topic_id' => 77,
        ]);

        $this->assertSame(55, $linkedPost['data']['linked_posts'][0]['id']);
        $this->assertSame(77, $linkedTopic['data']['linked_topics'][0]['id']);
    }
}
