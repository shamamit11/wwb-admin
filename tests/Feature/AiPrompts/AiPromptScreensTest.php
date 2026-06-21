<?php

namespace Tests\Feature\AiPrompts;

use App\Livewire\Admin\AiPrompts\Show;
use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Http;
use Livewire\Livewire;
use Tests\TestCase;

class AiPromptScreensTest extends TestCase
{
    protected string $apiBaseUrl;

    protected function setUp(): void
    {
        parent::setUp();

        $this->apiBaseUrl = rtrim(config('widewebblog.api.base_url'), '/');
    }

    public function test_ai_prompt_index_renders_service_backed_prompts(): void
    {
        Http::fake([
            $this->apiBaseUrl.'/admin/ai-prompts*' => Http::response([
                'data' => [
                    $this->promptResource([
                        'id' => 9,
                        'name' => 'Content Brief Prompt',
                        'key' => 'content-brief-main',
                        'type' => 'content_brief',
                    ]),
                    $this->promptResource([
                        'id' => 10,
                        'name' => 'Topic Discovery Prompt',
                        'key' => 'topic-discovery-main',
                        'type' => 'topic_discovery',
                        'status' => 'draft',
                    ]),
                ],
            ], 200),
        ]);

        $response = $this->withSession($this->authenticatedSession())
            ->get(route('ai-prompts.index'));

        $response
            ->assertOk()
            ->assertSee('Prompt Templates')
            ->assertSee('Create Prompt Template')
            ->assertSee('Content Brief Prompt')
            ->assertSee('Topic Discovery Prompt');
    }

    public function test_ai_prompt_create_and_detail_screen_can_manage_versions(): void
    {
        session($this->authenticatedSession());

        $created = $this->promptResource([
            'id' => 9,
            'name' => 'Content Brief Prompt',
            'key' => 'content-brief-main',
            'type' => 'content_brief',
            'status' => 'active',
            'active_version_id' => 12,
            'versions_count' => 1,
            'active_version' => $this->versionResource([
                'id' => 12,
                'prompt_template_id' => 9,
                'version' => 1,
                'status' => 'active',
                'variables' => ['topic', 'audience'],
            ]),
            'versions' => [
                $this->versionResource([
                    'id' => 12,
                    'prompt_template_id' => 9,
                    'version' => 1,
                    'status' => 'active',
                    'variables' => ['topic', 'audience'],
                ]),
            ],
        ]);

        $updated = array_replace($created, [
            'name' => 'Updated Content Brief Prompt',
            'status' => 'draft',
            'description' => 'Editorial prompt guidance.',
        ]);

        $afterVersionCreate = array_replace($updated, [
            'active_version_id' => 12,
            'versions_count' => 2,
            'versions' => [
                $this->versionResource([
                    'id' => 18,
                    'prompt_template_id' => 9,
                    'version' => 2,
                    'status' => 'draft',
                    'system_prompt' => 'System v2',
                    'user_prompt' => 'User v2',
                    'variables' => ['topic'],
                ]),
                $this->versionResource([
                    'id' => 12,
                    'prompt_template_id' => 9,
                    'version' => 1,
                    'status' => 'active',
                    'variables' => ['topic', 'audience'],
                ]),
            ],
        ]);

        $afterActivate = array_replace($afterVersionCreate, [
            'active_version_id' => 18,
            'active_version' => $this->versionResource([
                'id' => 18,
                'prompt_template_id' => 9,
                'version' => 2,
                'status' => 'active',
                'system_prompt' => 'System v2',
                'user_prompt' => 'User v2',
                'variables' => ['topic'],
            ]),
            'versions' => [
                $this->versionResource([
                    'id' => 18,
                    'prompt_template_id' => 9,
                    'version' => 2,
                    'status' => 'active',
                    'system_prompt' => 'System v2',
                    'user_prompt' => 'User v2',
                    'variables' => ['topic'],
                ]),
                $this->versionResource([
                    'id' => 12,
                    'prompt_template_id' => 9,
                    'version' => 1,
                    'status' => 'active',
                    'variables' => ['topic', 'audience'],
                ]),
            ],
        ]);

        Http::fake(function (Request $request) use ($created, $updated, $afterVersionCreate, $afterActivate) {
            if ($request->method() === 'POST' && $request->url() === $this->apiBaseUrl.'/admin/ai-prompts') {
                $this->assertSame('Content Brief Prompt', $request['name']);
                $this->assertSame('content-brief-main', $request['key']);
                $this->assertSame('content_brief', $request['type']);
                $this->assertSame('System v1', $request['initial_version']['system_prompt']);
                $this->assertSame(['topic', 'audience'], $request['initial_version']['variables']);

                return Http::response(['data' => $created], 201);
            }

            if ($request->method() === 'GET' && $request->url() === $this->apiBaseUrl.'/admin/ai-prompts/9') {
                static $showCount = 0;
                $showCount++;

                return Http::response([
                    'data' => match ($showCount) {
                        1 => $created,
                        2 => $afterVersionCreate,
                        default => $afterActivate,
                    },
                ], 200);
            }

            if ($request->method() === 'PATCH' && $request->url() === $this->apiBaseUrl.'/admin/ai-prompts/9') {
                $this->assertSame('Updated Content Brief Prompt', $request['name']);
                $this->assertSame('draft', $request['status']);

                return Http::response(['data' => $updated], 200);
            }

            if ($request->method() === 'POST' && $request->url() === $this->apiBaseUrl.'/admin/ai-prompts/9/versions') {
                $this->assertSame('System v2', $request['system_prompt']);
                $this->assertSame('User v2', $request['user_prompt']);
                $this->assertSame(['topic'], $request['variables']);

                return Http::response([
                    'data' => $this->versionResource([
                        'id' => 18,
                        'prompt_template_id' => 9,
                        'version' => 2,
                        'status' => 'draft',
                    ]),
                ], 201);
            }

            if ($request->method() === 'POST' && $request->url() === $this->apiBaseUrl.'/admin/ai-prompts/9/activate-version/18') {
                return Http::response(['data' => $afterActivate], 200);
            }

            return Http::response(['message' => 'Unexpected request.'], 500);
        });

        Livewire::test(Show::class)
            ->set('name', 'Content Brief Prompt')
            ->set('key', 'content-brief-main')
            ->set('type', 'content_brief')
            ->set('status', 'active')
            ->set('initialSystemPrompt', 'System v1')
            ->set('initialUserPrompt', 'User v1')
            ->set('initialOutputSchemaJson', '["title","sections"]')
            ->set('initialVariablesText', "topic\naudience")
            ->set('initialVersionStatus', 'active')
            ->call('save')
            ->assertRedirect(route('ai-prompts.show', ['aiPrompt' => 9]));

        Livewire::test(Show::class, ['aiPrompt' => 9])
            ->assertSet('name', 'Content Brief Prompt')
            ->set('name', 'Updated Content Brief Prompt')
            ->set('status', 'draft')
            ->set('description', 'Editorial prompt guidance.')
            ->call('save')
            ->assertHasNoErrors()
            ->set('versionSystemPrompt', 'System v2')
            ->set('versionUserPrompt', 'User v2')
            ->set('versionOutputSchemaJson', '["summary"]')
            ->set('versionVariablesText', 'topic')
            ->set('versionStatus', 'draft')
            ->call('createVersion')
            ->assertHasNoErrors()
            ->call('activateVersion', 18)
            ->assertSet('prompt.active_version_id', 18)
            ->assertSee('Version History');
    }

    public function test_ai_prompt_detail_screen_renders_workflow_and_collapsible_prompt_sections(): void
    {
        Http::fake([
            $this->apiBaseUrl.'/admin/ai-prompts/9' => Http::response([
                'data' => $this->promptResource(),
            ], 200),
        ]);

        $response = $this->withSession($this->authenticatedSession())
            ->get(route('ai-prompts.show', ['aiPrompt' => 9]));

        $response
            ->assertOk()
            ->assertSee('Template Details')
            ->assertSee('Future Generation Prompt Update')
            ->assertSee('Version Workflow')
            ->assertSee('Active Version')
            ->assertSee('Expand')
            ->assertSee('Copy')
            ->assertSee('View JSON')
            ->assertSee('Version History');
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

    protected function promptResource(array $overrides = []): array
    {
        return array_replace([
            'id' => 9,
            'name' => 'Prompt Template',
            'key' => 'prompt-template',
            'type' => 'content_brief',
            'description' => 'Prompt description.',
            'status' => 'active',
            'active_version_id' => 12,
            'versions_count' => 1,
            'active_version' => $this->versionResource(),
            'versions' => [$this->versionResource()],
            'created_at' => now()->subDay()->toISOString(),
            'updated_at' => now()->toISOString(),
        ], $overrides);
    }

    protected function versionResource(array $overrides = []): array
    {
        return array_replace([
            'id' => 12,
            'prompt_template_id' => 9,
            'version' => 1,
            'system_prompt' => 'System prompt',
            'user_prompt' => 'User prompt',
            'output_schema' => ['title', 'sections'],
            'variables' => ['topic', 'audience'],
            'status' => 'active',
            'created_at' => now()->subDay()->toISOString(),
            'updated_at' => now()->toISOString(),
        ], $overrides);
    }
}
