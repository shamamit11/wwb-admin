<?php

namespace Tests\Feature\TopicQueue;

use App\Livewire\Admin\TopicQueue\Index;
use App\Livewire\Admin\TopicQueue\Show;
use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Http;
use Livewire\Livewire;
use Tests\TestCase;

class TopicQueueCategoryWorkflowTest extends TestCase
{
    protected string $apiBaseUrl;

    protected function setUp(): void
    {
        parent::setUp();

        $this->apiBaseUrl = rtrim(config('widewebblog.api.base_url'), '/');
    }

    public function test_topic_queue_index_renders_category_aware_topics_and_filters(): void
    {
        Http::fake([
            $this->apiBaseUrl.'/admin/categories' => Http::response([
                'data' => [
                    $this->categoryResource(['id' => 5, 'name' => 'AI Tools', 'slug' => 'ai-tools']),
                    $this->categoryResource(['id' => 6, 'name' => 'SEO', 'slug' => 'seo']),
                ],
            ], 200),
            $this->apiBaseUrl.'/admin/content-topics*' => Http::response([
                'data' => [
                    $this->topicResource([
                        'id' => 21,
                        'title' => 'Laravel Queue Timeout Patterns',
                        'category_id' => 5,
                        'category' => ['id' => 5, 'name' => 'AI Tools', 'slug' => 'ai-tools'],
                        'cluster' => 'ai_tools',
                        'score_breakdown' => ['trend_score' => 31, 'knowledge_base_fit' => 19, 'business_value' => 18],
                    ]),
                ],
            ], 200),
        ]);

        $response = $this->withSession($this->authenticatedSession())
            ->get(route('topic-queue.index'));

        $response
            ->assertOk()
            ->assertSee('All categories')
            ->assertSee('Laravel Queue Timeout Patterns')
            ->assertSee('AI Tools')
            ->assertSee('All clusters')
            ->assertSee('Auto-queues draft generation');
    }

    public function test_topic_discovery_uses_category_id_payload(): void
    {
        session($this->authenticatedSession());

        Http::fake(function (Request $request) {
            if ($request->method() === 'GET' && $request->url() === $this->apiBaseUrl.'/admin/categories') {
                return Http::response([
                    'data' => [
                        $this->categoryResource(['id' => 5, 'name' => 'AI Tools', 'slug' => 'ai-tools']),
                    ],
                ], 200);
            }

            if ($request->method() === 'GET' && str_starts_with($request->url(), $this->apiBaseUrl.'/admin/content-topics')) {
                return Http::response(['data' => []], 200);
            }

            if ($request->method() === 'POST' && $request->url() === $this->apiBaseUrl.'/admin/ai-jobs/topic-discovery') {
                $this->assertSame(5, $request['category_id']);
                $this->assertSame(12, $request['count']);
                $this->assertSame('Editors', $request['audience']);
                $this->assertSame(['newsletter', 'q3-focus'], $request['metadata']);

                return Http::response([
                    'data' => ['id' => 44, 'type' => 'topic_discovery', 'status' => 'queued'],
                ], 202);
            }

            return Http::response(['message' => 'Unexpected request.'], 500);
        });

        Livewire::test(Index::class)
            ->call('openDiscoveryDialog')
            ->set('discoveryCategoryId', '5')
            ->set('discoveryCount', '12')
            ->set('discoveryAudience', 'Editors')
            ->set('discoveryMetadata', 'newsletter, q3-focus')
            ->call('runTopicDiscovery')
            ->assertRedirect(route('ai-jobs.show', ['aiJob' => 44]));
    }

    public function test_topic_detail_can_queue_draft_generation_from_saved_category(): void
    {
        session($this->authenticatedSession());

        Http::fake(function (Request $request) {
            if ($request->method() === 'GET' && $request->url() === $this->apiBaseUrl.'/admin/content-topics/8') {
                return Http::response([
                    'data' => $this->topicResource([
                        'id' => 8,
                        'title' => 'AI Agent Monitoring Ideas',
                        'category_id' => 5,
                        'category' => ['id' => 5, 'name' => 'AI Tools', 'slug' => 'ai-tools'],
                        'can_generate_draft' => true,
                    ]),
                ], 200);
            }

            if ($request->method() === 'POST' && $request->url() === $this->apiBaseUrl.'/admin/content-topics/8/generate-draft') {
                return Http::response([
                    'data' => ['id' => 77, 'type' => 'blog_writer', 'status' => 'queued'],
                ], 202);
            }

            return Http::response(['message' => 'Unexpected request.'], 500);
        });

        Livewire::test(Show::class, ['topic' => 8])
            ->assertSee('Generate Draft')
            ->assertSee('AI Tools')
            ->call('generateDraft')
            ->assertRedirect(route('ai-jobs.show', ['aiJob' => 77]));
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

    protected function categoryResource(array $overrides = []): array
    {
        return array_replace([
            'id' => 1,
            'ulid' => '01JTESTCATEGORY',
            'parent_id' => null,
            'name' => 'Category',
            'slug' => 'category',
            'description' => null,
            'is_active' => true,
            'canonical_url' => '/category',
            'sort_order' => 0,
            'created_at' => now()->subDay()->toISOString(),
            'updated_at' => now()->toISOString(),
        ], $overrides);
    }

    protected function topicResource(array $overrides = []): array
    {
        return array_replace([
            'id' => 1,
            'category_id' => 5,
            'category' => ['id' => 5, 'name' => 'AI Tools', 'slug' => 'ai-tools'],
            'title' => 'Topic',
            'slug' => 'topic',
            'cluster' => 'ai_tools',
            'primary_keyword' => 'agent workflow',
            'secondary_keywords' => ['automation', 'editorial'],
            'search_intent' => 'informational',
            'priority_score' => '91',
            'score_breakdown' => ['trend_score' => 30, 'knowledge_base_fit' => 18, 'business_value' => 17],
            'difficulty_note' => null,
            'source' => 'ai_suggested',
            'status' => 'suggested',
            'notes' => null,
            'can_generate_draft' => false,
            'approved_at' => '',
            'rejected_at' => '',
            'used_at' => '',
            'created_at' => now()->subHour()->toISOString(),
            'updated_at' => now()->toISOString(),
        ], $overrides);
    }
}
