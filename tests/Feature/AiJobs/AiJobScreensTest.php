<?php

namespace Tests\Feature\AiJobs;

use App\Livewire\Admin\AiJobs\Show;
use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Http;
use Livewire\Livewire;
use Tests\TestCase;

class AiJobScreensTest extends TestCase
{
    protected string $apiBaseUrl;

    protected function setUp(): void
    {
        parent::setUp();

        $this->apiBaseUrl = rtrim(config('widewebblog.api.base_url'), '/');
    }

    public function test_ai_jobs_index_renders_service_backed_jobs(): void
    {
        Http::fake([
            $this->apiBaseUrl.'/admin/ai-jobs*' => Http::response([
                'data' => [
                    $this->jobResource(['id' => 11, 'type' => 'topic_discovery', 'status' => 'failed']),
                    $this->jobResource(['id' => 12, 'type' => 'content_brief', 'status' => 'completed']),
                ],
            ], 200),
        ]);

        $response = $this->withSession($this->authenticatedSession())
            ->get(route('ai-jobs.index'));

        $response
            ->assertOk()
            ->assertSee('AI Jobs')
            ->assertSee('#11')
            ->assertSee('Topic Discovery')
            ->assertSee('Content Brief')
            ->assertSee('Failed Jobs');
    }

    public function test_ai_job_detail_screen_can_retry_failed_job(): void
    {
        session($this->authenticatedSession());

        $failed = $this->jobResource([
            'id' => 11,
            'status' => 'failed',
            'can_retry' => true,
            'steps' => [
                [
                    'id' => 51,
                    'ai_job_id' => 11,
                    'agent_name' => 'TopicDiscoveryAgent',
                    'status' => 'failed',
                    'input_payload' => ['cluster' => 'ai_tools'],
                    'output_payload' => null,
                    'usage_payload' => null,
                    'error_message' => 'Provider timeout.',
                    'costs' => [],
                    'started_at' => now()->subMinutes(5)->toISOString(),
                    'completed_at' => null,
                    'failed_at' => now()->subMinutes(4)->toISOString(),
                    'created_at' => now()->subMinutes(5)->toISOString(),
                    'updated_at' => now()->subMinutes(4)->toISOString(),
                ],
            ],
        ]);

        $retried = array_replace($failed, [
            'status' => 'queued',
            'can_retry' => false,
            'error_message' => null,
        ]);

        Http::fake(function (Request $request) use ($failed, $retried) {
            if ($request->method() === 'GET' && $request->url() === $this->apiBaseUrl.'/admin/ai-jobs/11') {
                return Http::response(['data' => $failed], 200);
            }

            if ($request->method() === 'POST' && $request->url() === $this->apiBaseUrl.'/admin/ai-jobs/11/retry') {
                return Http::response(['data' => $retried], 202);
            }

            return Http::response(['message' => 'Unexpected request.'], 500);
        });

        Livewire::test(Show::class, ['aiJob' => 11])
            ->assertSee('Retry Failed Job')
            ->call('retry')
            ->assertHasNoErrors()
            ->assertSet('job.status', 'queued');
    }

    public function test_ai_job_detail_screen_renders_summary_first_operational_sections(): void
    {
        Http::fake([
            $this->apiBaseUrl.'/admin/ai-jobs/15' => Http::response([
                'data' => $this->jobResource([
                    'id' => 15,
                    'type' => 'content_brief',
                    'status' => 'completed',
                    'steps' => [
                        [
                            'id' => 71,
                            'ai_job_id' => 15,
                            'agent_name' => 'ContentBriefAgent',
                            'status' => 'completed',
                            'input_payload' => ['content_topic_id' => 52, 'prompt_template_key' => 'content_brief_default'],
                            'output_payload' => ['brief_id' => 15, 'title' => 'AI Code Generation for Developers'],
                            'usage_payload' => ['total_tokens' => 4190],
                            'error_message' => null,
                            'costs' => [],
                            'started_at' => now()->subMinutes(3)->toISOString(),
                            'completed_at' => now()->subMinutes(2)->toISOString(),
                            'failed_at' => null,
                            'created_at' => now()->subMinutes(3)->toISOString(),
                            'updated_at' => now()->subMinutes(2)->toISOString(),
                        ],
                    ],
                ]),
            ], 200),
        ]);

        $response = $this->withSession($this->authenticatedSession())
            ->get(route('ai-jobs.show', ['aiJob' => 15]));

        $response
            ->assertOk()
            ->assertSee('Job Summary')
            ->assertSee('Queued')
            ->assertSee('Completed')
            ->assertSee('Generation Steps')
            ->assertSee('Input Summary')
            ->assertSee('Output Summary')
            ->assertSee('View JSON')
            ->assertSee('Copy')
            ->assertSeeText('Token & Cost Usage');
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

    protected function jobResource(array $overrides = []): array
    {
        return array_replace([
            'id' => 1,
            'type' => 'topic_discovery',
            'status' => 'pending',
            'entity_type' => 'App\\Models\\ContentTopic',
            'entity_id' => 8,
            'provider' => 'openai',
            'model' => 'gpt-5',
            'input_payload' => ['cluster' => 'ai_tools'],
            'output_payload' => ['topics' => []],
            'usage_payload' => ['prompt_tokens' => 120],
            'error_message' => null,
            'attempts' => 1,
            'retry_of_ai_job_id' => null,
            'can_retry' => false,
            'steps_count' => 1,
            'cost_summary' => [
                'input_tokens' => 120,
                'output_tokens' => 30,
                'total_tokens' => 150,
                'estimated_cost' => '0.12',
                'actual_cost' => '0.10',
                'currency' => 'USD',
            ],
            'started_at' => now()->subMinutes(6)->toISOString(),
            'completed_at' => now()->subMinutes(4)->toISOString(),
            'failed_at' => null,
            'created_at' => now()->subMinutes(7)->toISOString(),
            'updated_at' => now()->subMinutes(4)->toISOString(),
            'retry_of' => null,
            'retries' => [],
            'steps' => [],
            'costs' => [],
        ], $overrides);
    }
}
