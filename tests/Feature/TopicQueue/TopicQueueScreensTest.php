<?php

namespace Tests\Feature\TopicQueue;

use App\Livewire\Admin\TopicQueue\Show;
use App\Livewire\Admin\TopicQueue\Index;
use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Http;
use Livewire\Livewire;
use Tests\TestCase;

class TopicQueueScreensTest extends TestCase
{
    protected string $apiBaseUrl;

    protected function setUp(): void
    {
        parent::setUp();

        $this->apiBaseUrl = rtrim(config('widewebblog.api.base_url'), '/');
    }

    public function test_topic_queue_index_renders_service_backed_topics(): void
    {
        Http::fake([
            $this->apiBaseUrl.'/admin/content-topics*' => Http::response([
                'data' => [
                    $this->topicResource(['id' => 1, 'title' => 'AI Agent Orchestration', 'status' => 'suggested']),
                    $this->topicResource(['id' => 2, 'title' => 'Editorial Prompt Hygiene', 'status' => 'approved']),
                ],
            ], 200),
        ]);

        $response = $this->withSession($this->authenticatedSession())
            ->get(route('topic-queue.index'));

        $response
            ->assertOk()
            ->assertSee('Topic Queue')
            ->assertSee('AI Agent Orchestration')
            ->assertSee('Editorial Prompt Hygiene')
            ->assertSee('Suggested Topics')
            ->assertSee('Approved Topics');
    }

    public function test_topic_review_screen_can_save_and_approve_topic(): void
    {
        session($this->authenticatedSession());

        $topic = $this->topicResource([
            'id' => 8,
            'title' => 'Workflow Topic',
            'status' => 'suggested',
            'source' => 'ai_suggested',
            'secondary_keywords' => ['workflow'],
        ]);

        $updated = array_replace($topic, [
            'title' => 'Updated Workflow Topic',
            'secondary_keywords' => ['workflow', 'automation'],
        ]);

        $approved = array_replace($updated, [
            'status' => 'approved',
            'approved_at' => now()->toISOString(),
            'can_generate_content_brief' => true,
        ]);

        Http::fake(function (Request $request) use ($topic, $updated, $approved) {
            if ($request->method() === 'GET' && $request->url() === $this->apiBaseUrl.'/admin/content-topics/8') {
                return Http::response(['data' => $topic], 200);
            }

            if ($request->method() === 'PATCH' && $request->url() === $this->apiBaseUrl.'/admin/content-topics/8') {
                $this->assertSame('Updated Workflow Topic', $request['title']);
                $this->assertSame(['workflow', 'automation'], $request['secondary_keywords']);

                return Http::response(['data' => $updated], 200);
            }

            if ($request->method() === 'POST' && $request->url() === $this->apiBaseUrl.'/admin/content-topics/8/approve') {
                $this->assertSame('Ready for the next editorial phase.', $request['notes']);

                return Http::response(['data' => $approved], 200);
            }

            return Http::response(['message' => 'Unexpected request.'], 500);
        });

        Livewire::test(Show::class, ['topic' => 8])
            ->set('title', 'Updated Workflow Topic')
            ->set('secondaryKeywords', 'workflow, automation')
            ->call('save')
            ->assertHasNoErrors()
            ->call('openTransitionDialog', 'approve')
            ->set('transitionNotes', 'Ready for the next editorial phase.')
            ->call('executeTransition')
            ->assertHasNoErrors()
            ->assertSet('status', 'approved')
            ->assertSee('Yes');
    }

    public function test_topic_queue_can_start_topic_discovery_job(): void
    {
        session($this->authenticatedSession());

        Http::fake(function (Request $request) {
            if ($request->method() === 'GET' && str_starts_with($request->url(), $this->apiBaseUrl.'/admin/content-topics')) {
                return Http::response(['data' => []], 200);
            }

            if ($request->method() === 'POST' && $request->url() === $this->apiBaseUrl.'/admin/ai-jobs/topic-discovery') {
                $this->assertSame('ai_tools', $request['cluster']);
                $this->assertSame(9, $request['count']);
                $this->assertSame('Technical founders', $request['audience']);
                $this->assertSame(['newsletter', 'q3'], $request['metadata']);

                return Http::response([
                    'data' => [
                        'id' => 22,
                        'type' => 'topic_discovery',
                        'status' => 'queued',
                    ],
                ], 202);
            }

            return Http::response(['message' => 'Unexpected request.'], 500);
        });

        Livewire::test(Index::class)
            ->call('openDiscoveryDialog')
            ->set('discoveryCluster', 'ai_tools')
            ->set('discoveryCount', '9')
            ->set('discoveryAudience', 'Technical founders')
            ->set('discoveryMetadata', 'newsletter, q3')
            ->call('runTopicDiscovery')
            ->assertRedirect(route('ai-jobs.show', ['aiJob' => 22]));
    }

    public function test_topic_review_screen_can_generate_content_brief(): void
    {
        session($this->authenticatedSession());

        $topic = $this->topicResource([
            'id' => 8,
            'title' => 'Workflow Topic',
            'status' => 'approved',
            'can_generate_content_brief' => true,
        ]);

        Http::fake(function (Request $request) use ($topic) {
            if ($request->method() === 'GET' && $request->url() === $this->apiBaseUrl.'/admin/content-topics/8') {
                return Http::response(['data' => $topic], 200);
            }

            if ($request->method() === 'POST' && $request->url() === $this->apiBaseUrl.'/admin/content-topics/8/generate-brief') {
                return Http::response([
                    'data' => ['id' => 14, 'title' => 'Generated Brief'],
                ], 200);
            }

            return Http::response(['message' => 'Unexpected request.'], 500);
        });

        Livewire::test(\App\Livewire\Admin\TopicQueue\Show::class, ['topic' => 8])
            ->call('openBriefDialog')
            ->call('generateBrief')
            ->assertRedirect(route('content-briefs.show', ['contentBrief' => 14]));
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

    protected function topicResource(array $overrides = []): array
    {
        return array_replace([
            'id' => 1,
            'title' => 'Topic title',
            'slug' => 'topic-title',
            'cluster' => 'ai_tools',
            'primary_keyword' => 'AI agents',
            'secondary_keywords' => [],
            'search_intent' => 'informational',
            'priority_score' => '71.50',
            'difficulty_note' => 'Moderate competition.',
            'source' => 'manual',
            'status' => 'suggested',
            'notes' => null,
            'can_generate_content_brief' => false,
            'approved_at' => null,
            'rejected_at' => null,
            'used_at' => null,
            'created_at' => now()->subDay()->toISOString(),
            'updated_at' => now()->toISOString(),
        ], $overrides);
    }
}
