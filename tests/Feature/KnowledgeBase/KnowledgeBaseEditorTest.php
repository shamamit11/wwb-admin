<?php

namespace Tests\Feature\KnowledgeBase;

use App\Livewire\Admin\KnowledgeBase\Editor;
use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Http;
use Livewire\Livewire;
use Tests\TestCase;

class KnowledgeBaseEditorTest extends TestCase
{
    protected string $apiBaseUrl;

    protected function setUp(): void
    {
        parent::setUp();
        $this->apiBaseUrl = rtrim(config('widewebblog.api.base_url'), '/');
    }

    public function test_knowledge_base_create_route_renders_editor_shell(): void
    {
        $response = $this->withSession($this->authenticatedSession())
            ->get(route('knowledge-base.create'));

        $response
            ->assertOk()
            ->assertSee('Create Knowledge Entry')
            ->assertSee('Markdown Content')
            ->assertSee('Metadata');
    }

    public function test_knowledge_base_entry_can_be_created_and_redirects_to_edit_route(): void
    {
        session($this->authenticatedSession());

        Http::fake(function (Request $request) {
            if ($request->method() === 'POST' && $request->url() === $this->apiBaseUrl.'/admin/knowledge-base') {
                $this->assertSame('Agent Memory Research', $request['title']);
                $this->assertSame('research', $request['entry_type']);
                $this->assertSame('draft', $request['status']);
                $this->assertSame("## Context\n\n- Retrieval\n- Memory", $request['content_markdown']);
                $this->assertSame(['agent-memory', 'research'], $request['metadata']);

                return Http::response([
                    'data' => $this->entryResource([
                        'id' => 12,
                        'title' => 'Agent Memory Research',
                        'slug' => 'agent-memory-research',
                        'entry_type' => 'research',
                    ]),
                ], 201);
            }

            return Http::response(['message' => 'Unexpected request.'], 500);
        });

        Livewire::test(Editor::class)
            ->set('title', 'Agent Memory Research')
            ->set('slug', 'agent-memory-research')
            ->set('entryType', 'research')
            ->set('status', 'draft')
            ->set('summary', 'Research summary')
            ->set('contentMarkdown', "## Context\n\n- Retrieval\n- Memory")
            ->set('sourceUrl', 'https://example.com/source')
            ->set('metadataJson', '["agent-memory","research"]')
            ->call('save')
            ->assertRedirect(route('knowledge-base.edit', ['knowledgeBaseEntry' => 12]));
    }

    public function test_existing_knowledge_base_entry_can_be_loaded_and_updated(): void
    {
        session($this->authenticatedSession());

        Http::fake(function (Request $request) {
            if ($request->method() === 'GET' && $request->url() === $this->apiBaseUrl.'/admin/knowledge-base/1') {
                return Http::response([
                    'data' => $this->entryResource([
                        'id' => 1,
                        'title' => 'Existing Entry',
                        'metadata' => ['research'],
                        'linked_posts' => [
                            ['id' => 10, 'title' => 'Related Post'],
                            'invalid-shape',
                        ],
                        'linked_topics' => [
                            ['id' => 11, 'title' => 'Related Topic'],
                            42,
                        ],
                    ]),
                ], 200);
            }

            if ($request->method() === 'PUT' && $request->url() === $this->apiBaseUrl.'/admin/knowledge-base/1') {
                $this->assertSame('Updated Entry', $request['title']);
                $this->assertSame("# Updated\n\nPreserved markdown", $request['content_markdown']);

                return Http::response([
                    'data' => $this->entryResource([
                        'id' => 1,
                        'title' => 'Updated Entry',
                        'content_markdown' => "# Updated\n\nPreserved markdown",
                    ]),
                ], 200);
            }

            return Http::response(['message' => 'Unexpected request.'], 500);
        });

        Livewire::test(Editor::class, ['knowledgeBaseEntry' => 1])
            ->assertSet('title', 'Existing Entry')
            ->assertSet('metadataJson', "[\n    \"research\"\n]")
            ->assertSet('linkedPosts', [['id' => 10, 'title' => 'Related Post']])
            ->assertSet('linkedTopics', [['id' => 11, 'title' => 'Related Topic']])
            ->set('title', 'Updated Entry')
            ->set('contentMarkdown', "# Updated\n\nPreserved markdown")
            ->call('save')
            ->assertHasNoErrors()
            ->assertSet('title', 'Updated Entry');
    }

    public function test_knowledge_base_editor_maps_api_validation_errors(): void
    {
        session($this->authenticatedSession());

        Http::fake(function (Request $request) {
            if ($request->method() === 'POST' && $request->url() === $this->apiBaseUrl.'/admin/knowledge-base') {
                return Http::response([
                    'message' => 'The given data was invalid.',
                    'errors' => [
                        'content_markdown' => ['The content markdown field is required.'],
                        'metadata' => ['The metadata field must be an array.'],
                    ],
                ], 422);
            }

            return Http::response(['message' => 'Unexpected request.'], 500);
        });

        Livewire::test(Editor::class)
            ->set('title', 'Broken Entry')
            ->set('entryType', 'note')
            ->set('status', 'draft')
            ->set('contentMarkdown', 'Valid local content')
            ->set('metadataJson', '["broken"]')
            ->call('save')
            ->assertHasErrors(['contentMarkdown', 'metadataJson'])
            ->assertSee('The given data was invalid.')
            ->assertSee('The content markdown field is required.')
            ->assertSee('The metadata field must be an array.');
    }

    public function test_markdown_snippet_can_be_inserted_without_overbuilt_editor_behavior(): void
    {
        Livewire::test(Editor::class)
            ->set('contentMarkdown', 'Existing note')
            ->call('insertMarkdownSnippet', 'quote')
            ->assertSet('contentMarkdown', "Existing note\n> Key excerpt");
    }

    public function test_existing_knowledge_base_entry_can_be_linked_to_a_post_and_topic(): void
    {
        session($this->authenticatedSession());

        Http::fake(function (Request $request) {
            if ($request->method() === 'GET' && $request->url() === $this->apiBaseUrl.'/admin/knowledge-base/1') {
                return Http::response([
                    'data' => $this->entryResource([
                        'id' => 1,
                        'title' => 'Existing Entry',
                    ]),
                ], 200);
            }

            if ($request->method() === 'POST' && $request->url() === $this->apiBaseUrl.'/admin/knowledge-base/1/link-post') {
                $this->assertSame(10, $request['post_id']);

                return Http::response([
                    'data' => $this->entryResource([
                        'id' => 1,
                        'linked_posts' => [
                            ['id' => 10, 'title' => 'Related Post'],
                        ],
                    ]),
                ], 200);
            }

            if ($request->method() === 'POST' && $request->url() === $this->apiBaseUrl.'/admin/knowledge-base/1/link-topic') {
                $this->assertSame(11, $request['topic_id']);

                return Http::response([
                    'data' => $this->entryResource([
                        'id' => 1,
                        'linked_posts' => [
                            ['id' => 10, 'title' => 'Related Post'],
                        ],
                        'linked_topics' => [
                            ['id' => 11, 'title' => 'Related Topic'],
                        ],
                    ]),
                ], 200);
            }

            return Http::response(['message' => 'Unexpected request.'], 500);
        });

        Livewire::test(Editor::class, ['knowledgeBaseEntry' => 1])
            ->set('linkPostId', '10')
            ->call('linkPost')
            ->assertHasNoErrors()
            ->assertSet('linkPostId', '')
            ->assertSet('linkedPosts', [['id' => 10, 'title' => 'Related Post']])
            ->set('linkTopicId', '11')
            ->call('linkTopic')
            ->assertHasNoErrors()
            ->assertSet('linkTopicId', '')
            ->assertSet('linkedTopics', [['id' => 11, 'title' => 'Related Topic']]);
    }

    public function test_knowledge_base_link_actions_map_api_validation_errors(): void
    {
        session($this->authenticatedSession());

        Http::fake(function (Request $request) {
            if ($request->method() === 'GET' && $request->url() === $this->apiBaseUrl.'/admin/knowledge-base/1') {
                return Http::response([
                    'data' => $this->entryResource([
                        'id' => 1,
                        'title' => 'Existing Entry',
                    ]),
                ], 200);
            }

            if ($request->method() === 'POST' && $request->url() === $this->apiBaseUrl.'/admin/knowledge-base/1/link-post') {
                return Http::response([
                    'message' => 'The given data was invalid.',
                    'errors' => [
                        'post_id' => ['The selected post id is invalid.'],
                    ],
                ], 422);
            }

            if ($request->method() === 'POST' && $request->url() === $this->apiBaseUrl.'/admin/knowledge-base/1/link-topic') {
                return Http::response([
                    'message' => 'The given data was invalid.',
                    'errors' => [
                        'topic_id' => ['The selected topic id is invalid.'],
                    ],
                ], 422);
            }

            return Http::response(['message' => 'Unexpected request.'], 500);
        });

        Livewire::test(Editor::class, ['knowledgeBaseEntry' => 1])
            ->set('linkPostId', '999')
            ->call('linkPost')
            ->assertHasErrors(['linkPostId'])
            ->assertSee('The selected post id is invalid.')
            ->set('linkTopicId', '888')
            ->call('linkTopic')
            ->assertHasErrors(['linkTopicId'])
            ->assertSee('The selected topic id is invalid.');
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

    protected function entryResource(array $overrides = []): array
    {
        return array_replace([
            'id' => 1,
            'ulid' => '01J00000000000000000000000',
            'title' => 'Knowledge entry',
            'slug' => 'knowledge-entry',
            'entry_type' => 'note',
            'status' => 'draft',
            'summary' => 'Short summary.',
            'content_markdown' => "# Heading\n\nReference content",
            'source_url' => 'https://example.com/source',
            'metadata' => ['research'],
            'linked_posts' => [],
            'linked_topics' => [],
            'created_at' => '2026-06-10T09:00:00Z',
            'updated_at' => '2026-06-12T09:00:00Z',
        ], $overrides);
    }
}
