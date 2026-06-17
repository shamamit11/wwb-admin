<?php

namespace Tests\Feature\Templates;

use App\Livewire\Admin\Templates\Index;
use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Http;
use Livewire\Livewire;
use Tests\TestCase;

class TemplateIndexTest extends TestCase
{
    protected string $apiBaseUrl;

    protected function setUp(): void
    {
        parent::setUp();

        $this->apiBaseUrl = rtrim(config('widewebblog.api.base_url'), '/');
    }

    public function test_templates_index_renders_service_backed_templates(): void
    {
        Http::fake([
            $this->apiBaseUrl.'/admin/templates' => Http::response([
                'data' => [
                    $this->templateResource([
                        'id' => 1,
                        'name' => 'Agent Tutorial Layout',
                        'slug' => 'agent-tutorial-layout',
                        'template_type' => 'tutorial',
                    ]),
                    $this->templateResource([
                        'id' => 2,
                        'name' => 'Decision Brief Layout',
                        'slug' => 'decision-brief-layout',
                        'template_type' => 'comparison',
                        'status' => 'draft',
                    ]),
                ],
            ], 200),
        ]);

        $response = $this->withSession($this->authenticatedSession())
            ->get(route('templates.index'));

        $response
            ->assertOk()
            ->assertSee('Create Template')
            ->assertSee('Agent Tutorial Layout')
            ->assertSee('Decision Brief Layout')
            ->assertSee('Templates');
    }

    public function test_template_form_maps_api_validation_errors_including_nested_blocks(): void
    {
        session($this->authenticatedSession());

        Http::fake(function (Request $request) {
            if ($request->method() === 'GET' && $request->url() === $this->apiBaseUrl.'/admin/templates') {
                return Http::response(['data' => []], 200);
            }

            if ($request->method() === 'POST' && $request->url() === $this->apiBaseUrl.'/admin/templates') {
                return Http::response([
                    'message' => 'The given data was invalid.',
                    'errors' => [
                        'default_meta' => ['The default meta field must be an array.'],
                        'blocks.0.block_type' => ['The selected blocks.0.block type is invalid.'],
                        'blocks.0.settings' => ['The blocks.0.settings field must be an array.'],
                    ],
                ], 422);
            }

            return Http::response(['message' => 'Unexpected request.'], 500);
        });

        Livewire::test(Index::class)
            ->call('openCreateDrawer')
            ->set('name', 'Broken template')
            ->set('defaultMetaJson', '{"seo_rules":true}')
            ->set('blocks.0.blockType', 'heading')
            ->set('blocks.0.settingsJson', '{"level":1}')
            ->call('save')
            ->assertHasErrors(['defaultMetaJson', 'blocks.0.blockType', 'blocks.0.settingsJson'])
            ->assertSee('The given data was invalid.')
            ->assertSee('The default meta field must be an array.')
            ->assertSee('The selected blocks.0.block type is invalid.');
    }

    public function test_template_block_markdown_snippet_can_be_inserted(): void
    {
        session($this->authenticatedSession());

        Http::fake([
            $this->apiBaseUrl.'/admin/templates' => Http::response(['data' => []], 200),
        ]);

        Livewire::test(Index::class)
            ->call('openCreateDrawer')
            ->set('blocks.0.blockType', 'paragraph')
            ->set('blocks.0.defaultMarkdown', 'Starter copy')
            ->call('insertBlockSnippet', 0, 'bold')
            ->assertSet('blocks.0.defaultMarkdown', "Starter copy\n**Bold text**");
    }

    public function test_template_drawer_renders_block_guidance_and_markdown_tools(): void
    {
        Http::fake([
            $this->apiBaseUrl.'/admin/templates' => Http::response(['data' => []], 200),
        ]);

        $response = $this->withSession($this->authenticatedSession())
            ->get(route('templates.index'));

        $response
            ->assertOk()
            ->assertSee('Templates');

        Livewire::test(Index::class)
            ->call('openCreateDrawer')
            ->assertSee('Markdown tools')
            ->assertSee('How seeded posts use this block')
            ->assertSee('Default Heading Markdown');
    }

    public function test_templates_can_be_created_updated_and_deleted_from_the_screen_with_ordered_blocks(): void
    {
        session($this->authenticatedSession());

        $initial = $this->templateResource([
            'id' => 1,
            'name' => 'Agent Tutorial Layout',
            'slug' => 'agent-tutorial-layout',
            'template_type' => 'tutorial',
            'status' => 'active',
            'blocks' => [
                $this->blockResource([
                    'id' => 10,
                    'block_type' => 'heading',
                    'sort_order' => 1,
                    'label' => 'Title',
                ]),
            ],
        ]);

        $created = $this->templateResource([
            'id' => 2,
            'name' => 'Decision Brief Layout',
            'slug' => 'decision-brief-layout',
            'template_type' => 'comparison',
            'status' => 'draft',
            'blocks' => [
                $this->blockResource([
                    'id' => 20,
                    'block_type' => 'heading',
                    'sort_order' => 1,
                    'label' => 'Title',
                ]),
                $this->blockResource([
                    'id' => 21,
                    'block_type' => 'callout',
                    'sort_order' => 2,
                    'label' => 'Decision summary',
                    'settings' => ['variant' => 'info'],
                ]),
            ],
        ]);

        $updated = $this->templateResource([
            'id' => 1,
            'name' => 'Narrative Blueprint',
            'slug' => 'narrative-blueprint',
            'template_type' => 'standard',
            'status' => 'draft',
            'blocks' => [
                $this->blockResource([
                    'id' => 30,
                    'block_type' => 'paragraph',
                    'sort_order' => 1,
                    'label' => 'Lead',
                ]),
                $this->blockResource([
                    'id' => 31,
                    'block_type' => 'heading',
                    'sort_order' => 2,
                    'label' => 'Section heading',
                    'settings' => ['level' => 2],
                ]),
            ],
        ]);

        $getIndexCount = 0;

        Http::fake(function (Request $request) use (&$getIndexCount, $initial, $created, $updated) {
            if ($request->method() === 'GET' && $request->url() === $this->apiBaseUrl.'/admin/templates') {
                $getIndexCount++;

                return Http::response([
                    'data' => match ($getIndexCount) {
                        1 => [$initial],
                        2 => [$initial, $created],
                        3 => [$updated, $created],
                        default => [$updated],
                    },
                ], 200);
            }

            if ($request->method() === 'POST' && $request->url() === $this->apiBaseUrl.'/admin/templates') {
                $this->assertSame('heading', $request['blocks'][0]['block_type']);
                $this->assertSame('callout', $request['blocks'][1]['block_type']);
                $this->assertSame(1, $request['blocks'][0]['sort_order']);
                $this->assertSame(2, $request['blocks'][1]['sort_order']);

                return Http::response(['data' => $created], 201);
            }

            if ($request->method() === 'GET' && $request->url() === $this->apiBaseUrl.'/admin/templates/1') {
                return Http::response(['data' => $initial], 200);
            }

            if ($request->method() === 'PUT' && $request->url() === $this->apiBaseUrl.'/admin/templates/1') {
                $this->assertSame('paragraph', $request['blocks'][0]['block_type']);
                $this->assertSame('heading', $request['blocks'][1]['block_type']);
                $this->assertSame(1, $request['blocks'][0]['sort_order']);
                $this->assertSame(2, $request['blocks'][1]['sort_order']);

                return Http::response(['data' => $updated], 200);
            }

            if ($request->method() === 'DELETE' && $request->url() === $this->apiBaseUrl.'/admin/templates/2') {
                return Http::response([], 204);
            }

            return Http::response(['message' => 'Unexpected request.'], 500);
        });

        Livewire::test(Index::class)
            ->assertSee('Agent Tutorial Layout')
            ->call('openCreateDrawer')
            ->set('name', 'Decision Brief Layout')
            ->set('templateType', 'comparison')
            ->set('status', 'draft')
            ->set('description', 'Tradeoff-oriented structure.')
            ->set('defaultExcerptPrompt', 'Summarize the recommendation.')
            ->set('defaultMetaJson', "{\"recommended_sections\":[\"problem\",\"options\",\"recommendation\"]}")
            ->set('blocks.0.blockType', 'callout')
            ->set('blocks.0.label', 'Decision summary')
            ->set('blocks.0.settingsJson', '{"variant":"info"}')
            ->call('moveBlockUp', 0)
            ->call('addBlock')
            ->set('blocks.1.blockType', 'heading')
            ->set('blocks.1.label', 'Title')
            ->set('blocks.1.defaultMarkdown', '# {{title}}')
            ->call('moveBlockUp', 1)
            ->call('save')
            ->assertHasNoErrors()
            ->assertSee('Decision Brief Layout')
            ->call('openEditDrawer', 1)
            ->set('name', 'Narrative Blueprint')
            ->set('slug', 'narrative-blueprint')
            ->set('templateType', 'standard')
            ->set('status', 'draft')
            ->set('blocks.0.blockType', 'heading')
            ->set('blocks.0.label', 'Section heading')
            ->set('blocks.0.settingsJson', '{"level":2}')
            ->call('addBlock')
            ->set('blocks.1.blockType', 'paragraph')
            ->set('blocks.1.label', 'Lead')
            ->set('blocks.1.defaultMarkdown', 'Lead with context.')
            ->call('moveBlockUp', 1)
            ->call('save')
            ->assertSee('Narrative Blueprint')
            ->call('confirmDelete', 2)
            ->assertSet('deleteDialogOpen', true)
            ->call('delete')
            ->assertDontSee('Decision Brief Layout');
    }

    public function test_template_preview_action_uses_documented_endpoint_and_renders_preview_payload(): void
    {
        session($this->authenticatedSession());

        $template = $this->templateResource([
            'id' => 1,
            'name' => 'Agent Tutorial Layout',
            'slug' => 'agent-tutorial-layout',
            'template_type' => 'tutorial',
        ]);

        Http::fake(function (Request $request) use ($template) {
            if ($request->method() === 'GET' && $request->url() === $this->apiBaseUrl.'/admin/templates') {
                return Http::response(['data' => [$template]], 200);
            }

            if ($request->method() === 'POST' && $request->url() === $this->apiBaseUrl.'/admin/templates/1/preview') {
                $this->assertSame('How AI Agent Memory Works', $request['title']);
                $this->assertSame('AI agent memory', $request['topic']);

                return Http::response([
                    'data' => [
                        'template' => $template,
                        'preview' => [
                            'title' => 'How AI Agent Memory Works',
                            'topic' => 'AI agent memory',
                            'meta' => [
                                'seo_rules' => ['preferred_schema' => 'Article'],
                            ],
                            'blocks' => [
                                [
                                    'block_type' => 'heading',
                                    'sort_order' => 1,
                                    'label' => 'Title',
                                    'is_required' => true,
                                    'content' => [
                                        'text' => 'How AI Agent Memory Works',
                                        'level' => 1,
                                    ],
                                ],
                                [
                                    'block_type' => 'paragraph',
                                    'sort_order' => 2,
                                    'label' => 'Introduction',
                                    'is_required' => true,
                                    'content' => [
                                        'markdown' => 'Introduce AI agent memory with practical context.',
                                    ],
                                ],
                            ],
                        ],
                    ],
                ], 200);
            }

            return Http::response(['message' => 'Unexpected request.'], 500);
        });

        Livewire::test(Index::class)
            ->call('openActionDrawer', 'preview', 1)
            ->set('actionContextTitle', 'How AI Agent Memory Works')
            ->set('actionContextTopic', 'AI agent memory')
            ->call('runTemplateAction')
            ->assertHasNoErrors()
            ->assertSee('How AI Agent Memory Works')
            ->assertSee('preferred_schema')
            ->assertSee('Introduce AI agent memory with practical context.');
    }

    public function test_template_seed_post_action_uses_documented_endpoint_and_renders_seed_payload(): void
    {
        session($this->authenticatedSession());

        $template = $this->templateResource([
            'id' => 1,
            'name' => 'Agent Tutorial Layout',
            'slug' => 'agent-tutorial-layout',
            'template_type' => 'tutorial',
        ]);

        Http::fake(function (Request $request) use ($template) {
            if ($request->method() === 'GET' && $request->url() === $this->apiBaseUrl.'/admin/templates') {
                return Http::response(['data' => [$template]], 200);
            }

            if ($request->method() === 'POST' && $request->url() === $this->apiBaseUrl.'/admin/templates/1/seed-post') {
                $this->assertSame('AI Agent Memory Patterns', $request['title']);
                $this->assertSame('AI agent memory', $request['topic']);

                return Http::response([
                    'data' => [
                        'template' => $template,
                        'post' => [
                            'title' => 'AI Agent Memory Patterns',
                            'slug' => 'ai-agent-memory-patterns',
                            'status' => 'draft',
                            'template_id' => 1,
                            'template_type' => 'tutorial',
                            'excerpt_prompt' => 'Summarize the steps and outcomes.',
                            'meta' => [
                                'recommended_sections' => ['introduction', 'steps', 'faq'],
                            ],
                            'blocks' => [
                                [
                                    'block_type' => 'heading',
                                    'sort_order' => 1,
                                    'label' => 'Title',
                                    'is_required' => true,
                                    'content' => [
                                        'text' => 'AI Agent Memory Patterns',
                                        'level' => 1,
                                    ],
                                ],
                            ],
                        ],
                    ],
                ], 200);
            }

            return Http::response(['message' => 'Unexpected request.'], 500);
        });

        Livewire::test(Index::class)
            ->call('openActionDrawer', 'seed', 1)
            ->set('actionContextTitle', 'AI Agent Memory Patterns')
            ->set('actionContextTopic', 'AI agent memory')
            ->call('runTemplateAction')
            ->assertHasNoErrors()
            ->assertSee('ai-agent-memory-patterns')
            ->assertSee('recommended_sections')
            ->assertSee('AI Agent Memory Patterns');
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

    protected function templateResource(array $overrides = []): array
    {
        return array_replace([
            'id' => 1,
            'ulid' => '01J00000000000000000000000',
            'name' => 'Template',
            'slug' => 'template',
            'template_type' => 'standard',
            'description' => 'Template description',
            'status' => 'active',
            'default_excerpt_prompt' => 'Summarize the key ideas.',
            'default_meta' => [
                'recommended_sections' => ['intro', 'body'],
            ],
            'blocks' => [
                $this->blockResource(),
            ],
            'created_at' => '2026-06-10T09:00:00Z',
            'updated_at' => '2026-06-12T09:00:00Z',
        ], $overrides);
    }

    protected function blockResource(array $overrides = []): array
    {
        return array_replace([
            'id' => 1,
            'block_key' => 'heading',
            'block_type' => 'heading',
            'sort_order' => 1,
            'label' => 'Heading',
            'default_markdown' => '# {{title}}',
            'settings' => ['level' => 1],
            'is_required' => true,
            'created_at' => '2026-06-10T09:00:00Z',
            'updated_at' => '2026-06-12T09:00:00Z',
        ], $overrides);
    }
}
