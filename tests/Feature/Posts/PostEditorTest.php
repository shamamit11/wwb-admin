<?php

namespace Tests\Feature\Posts;

use App\Livewire\Admin\Posts\Editor;
use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Http;
use Livewire\Livewire;
use Tests\TestCase;

class PostEditorTest extends TestCase
{
    protected string $apiBaseUrl;

    protected function setUp(): void
    {
        parent::setUp();

        $this->apiBaseUrl = rtrim(config('widewebblog.api.base_url'), '/');
    }

    public function test_post_create_route_renders_editor_shell(): void
    {
        Http::fake([
            $this->apiBaseUrl.'/admin/categories' => Http::response(['data' => [$this->categoryResource()]], 200),
            $this->apiBaseUrl.'/admin/tags' => Http::response(['data' => [$this->tagResource()]], 200),
            $this->apiBaseUrl.'/admin/templates' => Http::response(['data' => [$this->templateResource()]], 200),
            $this->apiBaseUrl.'/admin/media*' => Http::response(['data' => [$this->mediaResource()]], 200),
        ]);

        $response = $this->withSession($this->authenticatedSession())
            ->get(route('posts.create'));

        $response
            ->assertOk()
            ->assertSee('Create Post')
            ->assertSee('Core Content')
            ->assertSeeText('Taxonomy & Media');
    }

    public function test_post_can_be_created_from_editor_and_redirects_to_edit_route(): void
    {
        session($this->authenticatedSession());

        Http::fake(function (Request $request) {
            if ($request->method() === 'GET' && $request->url() === $this->apiBaseUrl.'/admin/categories') {
                return Http::response(['data' => [$this->categoryResource()]], 200);
            }

            if ($request->method() === 'GET' && $request->url() === $this->apiBaseUrl.'/admin/tags') {
                return Http::response(['data' => [$this->tagResource()]], 200);
            }

            if ($request->method() === 'GET' && $request->url() === $this->apiBaseUrl.'/admin/templates') {
                return Http::response(['data' => [$this->templateResource()]], 200);
            }

            if ($request->method() === 'GET' && str_starts_with($request->url(), $this->apiBaseUrl.'/admin/media')) {
                return Http::response(['data' => [$this->mediaResource()]], 200);
            }

            if ($request->method() === 'POST' && $request->url() === $this->apiBaseUrl.'/admin/posts') {
                $this->assertSame('Agent Editorial Blueprint', $request['title']);
                $this->assertSame(4, $request['category_id']);
                $this->assertSame([7], $request['tag_ids']);
                $this->assertSame('paragraph', $request['blocks'][0]['block_type']);
                $this->assertSame(['Lead paragraph', 'Second paragraph'], $request['blocks'][0]['content']);

                return Http::response([
                    'data' => $this->postResource([
                        'id' => 55,
                        'title' => 'Agent Editorial Blueprint',
                        'slug' => 'agent-editorial-blueprint',
                    ]),
                ], 201);
            }

            return Http::response(['message' => 'Unexpected request.'], 500);
        });

        Livewire::test(Editor::class)
            ->set('title', 'Agent Editorial Blueprint')
            ->set('slug', 'agent-editorial-blueprint')
            ->set('excerpt', 'Editorial summary')
            ->set('categoryId', '4')
            ->set('tagIds', ['7'])
            ->set('templateId', '8')
            ->set('featuredMediaId', '12')
            ->set('blocks.0.blockType', 'paragraph')
            ->set('blocks.0.contentText', "Lead paragraph\nSecond paragraph")
            ->call('save')
            ->assertRedirect(route('posts.edit', ['post' => 55]));
    }

    public function test_ai_draft_review_route_renders_source_context_and_suggestions(): void
    {
        Http::fake(function (Request $request) {
            if ($request->method() === 'GET' && $request->url() === $this->apiBaseUrl.'/admin/categories') {
                return Http::response(['data' => [$this->categoryResource()]], 200);
            }

            if ($request->method() === 'GET' && $request->url() === $this->apiBaseUrl.'/admin/tags') {
                return Http::response(['data' => [$this->tagResource()]], 200);
            }

            if ($request->method() === 'GET' && $request->url() === $this->apiBaseUrl.'/admin/templates') {
                return Http::response(['data' => [$this->templateResource()]], 200);
            }

            if ($request->method() === 'GET' && str_starts_with($request->url(), $this->apiBaseUrl.'/admin/media')) {
                return Http::response(['data' => [$this->mediaResource()]], 200);
            }

            if ($request->method() === 'GET' && $request->url() === $this->apiBaseUrl.'/admin/posts/1') {
                return Http::response([
                    'data' => $this->postResource([
                        'id' => 1,
                        'title' => 'AI Draft',
                        'is_ai_generated' => true,
                        'source_content_brief_id' => 14,
                        'source_content_topic_id' => 8,
                        'generated_by_ai_job_id' => 22,
                        'generated_by' => 'BlogWriterAgent',
                        'meta' => [
                            'source_content_brief_id' => 14,
                            'source_content_topic_id' => 8,
                            'ai_job_id' => 22,
                            'generated_by' => 'BlogWriterAgent',
                            'suggested_tags' => ['Editorial Ops', 'AI Governance'],
                            'faq_suggestions' => [
                                ['question' => 'What should be reviewed first?', 'answer_focus' => 'Source accuracy'],
                            ],
                            'image_placement_notes' => ['Add a workflow diagram after the introduction.'],
                            'alt_text_suggestions' => ['Workflow diagram showing AI editorial review stages'],
                        ],
                    ]),
                ], 200);
            }

            if ($request->method() === 'GET' && str_contains($request->url(), '/admin/seo/')) {
                return Http::response(['data' => []], 200);
            }

            return Http::response(['message' => 'Unexpected request.'], 500);
        });

        $response = $this->withSession($this->authenticatedSession())
            ->get(route('draft-review.show', ['post' => 1]));

        $response
            ->assertOk()
            ->assertSee('Review AI Draft')
            ->assertSee('Back to Draft Review')
            ->assertSee('Brief #14')
            ->assertSee('Topic #8')
            ->assertSee('Job #22')
            ->assertSee('Suggested Tags')
            ->assertSee('Editorial Ops')
            ->assertSee('AI Review Actions')
            ->assertSee('Suggest Metadata')
            ->assertSeeText('Refine Title & Excerpt')
            ->assertSee('FAQ Suggestions')
            ->assertSee('What should be reviewed first?')
            ->assertSee('Image Placement Notes')
            ->assertSee('Add a workflow diagram after the introduction.')
            ->assertSee('Alt Text Suggestions')
            ->assertSee('Workflow diagram showing AI editorial review stages');
    }

    public function test_ai_draft_review_can_queue_metadata_and_title_excerpt_jobs(): void
    {
        session($this->authenticatedSession());

        Http::fake(function (Request $request) {
            if ($request->method() === 'GET' && $request->url() === $this->apiBaseUrl.'/admin/categories') {
                return Http::response(['data' => [$this->categoryResource()]], 200);
            }

            if ($request->method() === 'GET' && $request->url() === $this->apiBaseUrl.'/admin/tags') {
                return Http::response(['data' => [$this->tagResource()]], 200);
            }

            if ($request->method() === 'GET' && $request->url() === $this->apiBaseUrl.'/admin/templates') {
                return Http::response(['data' => [$this->templateResource()]], 200);
            }

            if ($request->method() === 'GET' && str_starts_with($request->url(), $this->apiBaseUrl.'/admin/media')) {
                return Http::response(['data' => [$this->mediaResource()]], 200);
            }

            if ($request->method() === 'GET' && $request->url() === $this->apiBaseUrl.'/admin/posts/1') {
                return Http::response([
                    'data' => $this->postResource([
                        'id' => 1,
                        'title' => 'AI Draft',
                        'status' => 'draft',
                        'is_ai_generated' => true,
                        'source_content_brief_id' => 14,
                        'source_content_topic_id' => 8,
                    ]),
                ], 200);
            }

            if ($request->method() === 'GET' && str_contains($request->url(), '/admin/seo/')) {
                return Http::response(['data' => []], 200);
            }

            if ($request->method() === 'POST' && $request->url() === $this->apiBaseUrl.'/admin/posts/1/suggest-metadata') {
                $this->assertSame('Prioritize keyword clarity.', $request['instructions']);
                $this->assertSame('post_metadata_review_default', $request['prompt_template_key']);

                return Http::response(['data' => ['id' => 31, 'status' => 'queued']], 202);
            }

            if ($request->method() === 'POST' && $request->url() === $this->apiBaseUrl.'/admin/posts/1/refine-title-excerpt') {
                $this->assertSame('Make the hook sharper.', $request['instructions']);
                $this->assertSame('post_title_excerpt_refinement_default', $request['prompt_template_key']);

                return Http::response(['data' => ['id' => 32, 'status' => 'queued']], 202);
            }

            return Http::response(['message' => 'Unexpected request.'], 500);
        });

        Livewire::test(Editor::class, ['post' => 1])
            ->set('aiReviewMode', true)
            ->call('openReviewActionDialog', 'suggest_metadata')
            ->set('reviewActionInstructions', 'Prioritize keyword clarity.')
            ->set('reviewActionPromptTemplateKey', 'post_metadata_review_default')
            ->call('queueReviewAction')
            ->assertRedirect(route('ai-jobs.show', ['aiJob' => 31]));

        Livewire::test(Editor::class, ['post' => 1])
            ->set('aiReviewMode', true)
            ->call('openReviewActionDialog', 'refine_title_excerpt')
            ->set('reviewActionInstructions', 'Make the hook sharper.')
            ->set('reviewActionPromptTemplateKey', 'post_title_excerpt_refinement_default')
            ->call('queueReviewAction')
            ->assertRedirect(route('ai-jobs.show', ['aiJob' => 32]));
    }

    public function test_existing_post_can_be_loaded_and_updated_from_editor(): void
    {
        session($this->authenticatedSession());

        Http::fake(function (Request $request) {
            if ($request->method() === 'GET' && $request->url() === $this->apiBaseUrl.'/admin/categories') {
                return Http::response(['data' => [$this->categoryResource()]], 200);
            }

            if ($request->method() === 'GET' && $request->url() === $this->apiBaseUrl.'/admin/tags') {
                return Http::response(['data' => [$this->tagResource()]], 200);
            }

            if ($request->method() === 'GET' && $request->url() === $this->apiBaseUrl.'/admin/templates') {
                return Http::response(['data' => [$this->templateResource()]], 200);
            }

            if ($request->method() === 'GET' && str_starts_with($request->url(), $this->apiBaseUrl.'/admin/media')) {
                return Http::response(['data' => [$this->mediaResource()]], 200);
            }

            if ($request->method() === 'GET' && $request->url() === $this->apiBaseUrl.'/admin/posts/1') {
                return Http::response([
                    'data' => $this->postResource([
                        'id' => 1,
                        'title' => 'Existing Post',
                        'slug' => 'existing-post',
                        'blocks' => [
                            [
                                'id' => 100,
                                'block_key' => 'intro',
                                'block_type' => 'paragraph',
                                'sort_order' => 1,
                                'content_markdown' => 'Existing lead',
                                'content_html_cache' => null,
                                'plain_text_cache' => 'Existing lead',
                                'settings' => [],
                                'source_template_block_id' => 5,
                                'created_at' => '2026-06-10T09:00:00Z',
                                'updated_at' => '2026-06-10T09:00:00Z',
                            ],
                        ],
                    ]),
                ], 200);
            }

            if ($request->method() === 'PUT' && $request->url() === $this->apiBaseUrl.'/admin/posts/1') {
                $this->assertSame('Updated Post', $request['title']);
                $this->assertSame(5, $request['blocks'][0]['source_template_block_id']);
                $this->assertSame(['Updated lead'], $request['blocks'][0]['content']);

                return Http::response([
                    'data' => $this->postResource([
                        'id' => 1,
                        'title' => 'Updated Post',
                        'slug' => 'updated-post',
                        'blocks' => [
                            [
                                'id' => 100,
                                'block_key' => 'intro',
                                'block_type' => 'paragraph',
                                'sort_order' => 1,
                                'content_markdown' => 'Updated lead',
                                'content_html_cache' => null,
                                'plain_text_cache' => 'Updated lead',
                                'settings' => [],
                                'source_template_block_id' => 5,
                                'created_at' => '2026-06-10T09:00:00Z',
                                'updated_at' => '2026-06-17T09:00:00Z',
                            ],
                        ],
                    ]),
                ], 200);
            }

            return Http::response(['message' => 'Unexpected request.'], 500);
        });

        Livewire::test(Editor::class, ['post' => 1])
            ->assertSet('title', 'Existing Post')
            ->set('title', 'Updated Post')
            ->set('slug', 'updated-post')
            ->set('blocks.0.contentText', 'Updated lead')
            ->call('save')
            ->assertHasNoErrors()
            ->assertSet('title', 'Updated Post');
    }

    public function test_post_editor_maps_nested_api_validation_errors(): void
    {
        session($this->authenticatedSession());

        Http::fake(function (Request $request) {
            if ($request->method() === 'GET' && $request->url() === $this->apiBaseUrl.'/admin/categories') {
                return Http::response(['data' => [$this->categoryResource()]], 200);
            }

            if ($request->method() === 'GET' && $request->url() === $this->apiBaseUrl.'/admin/tags') {
                return Http::response(['data' => [$this->tagResource()]], 200);
            }

            if ($request->method() === 'GET' && $request->url() === $this->apiBaseUrl.'/admin/templates') {
                return Http::response(['data' => [$this->templateResource()]], 200);
            }

            if ($request->method() === 'GET' && str_starts_with($request->url(), $this->apiBaseUrl.'/admin/media')) {
                return Http::response(['data' => [$this->mediaResource()]], 200);
            }

            if ($request->method() === 'POST' && $request->url() === $this->apiBaseUrl.'/admin/posts') {
                return Http::response([
                    'message' => 'The given data was invalid.',
                    'errors' => [
                        'blocks.0.block_type' => ['The selected block type is invalid.'],
                        'blocks.0.content' => ['The content field must contain at least one item.'],
                        'category_id' => ['The selected category is invalid.'],
                    ],
                ], 422);
            }

            return Http::response(['message' => 'Unexpected request.'], 500);
        });

        Livewire::test(Editor::class)
            ->set('title', 'Broken Post')
            ->set('categoryId', '4')
            ->set('blocks.0.blockType', 'paragraph')
            ->set('blocks.0.contentText', 'Draft copy')
            ->call('save')
            ->assertHasErrors(['blocks.0.blockType', 'blocks.0.contentText', 'categoryId'])
            ->assertSee('The given data was invalid.')
            ->assertSee('The selected block type is invalid.')
            ->assertSee('The content field must contain at least one item.');
    }

    public function test_featured_media_can_be_selected_and_cleared_from_picker(): void
    {
        session($this->authenticatedSession());

        Http::fake([
            $this->apiBaseUrl.'/admin/categories' => Http::response(['data' => [$this->categoryResource()]], 200),
            $this->apiBaseUrl.'/admin/tags' => Http::response(['data' => [$this->tagResource()]], 200),
            $this->apiBaseUrl.'/admin/templates' => Http::response(['data' => [$this->templateResource()]], 200),
            $this->apiBaseUrl.'/admin/media*' => Http::response(['data' => [$this->mediaResource()]], 200),
        ]);

        Livewire::test(Editor::class)
            ->assertSet('featuredMediaId', '')
            ->call('openMediaPicker')
            ->assertSet('mediaPickerOpen', true)
            ->set('mediaSearch', 'hero')
            ->call('selectFeaturedMedia', 12)
            ->assertSet('featuredMediaId', '12')
            ->assertSet('mediaPickerOpen', false)
            ->call('clearFeaturedMedia')
            ->assertSet('featuredMediaId', '');
    }

    public function test_block_markdown_snippet_can_be_inserted(): void
    {
        session($this->authenticatedSession());

        Http::fake([
            $this->apiBaseUrl.'/admin/categories' => Http::response(['data' => [$this->categoryResource()]], 200),
            $this->apiBaseUrl.'/admin/tags' => Http::response(['data' => [$this->tagResource()]], 200),
            $this->apiBaseUrl.'/admin/templates' => Http::response(['data' => [$this->templateResource()]], 200),
            $this->apiBaseUrl.'/admin/media*' => Http::response(['data' => [$this->mediaResource()]], 200),
        ]);

        Livewire::test(Editor::class)
            ->set('blocks.0.blockType', 'paragraph')
            ->set('blocks.0.contentText', 'Lead copy')
            ->call('insertBlockSnippet', 0, 'bold')
            ->assertSet('blocks.0.contentText', "Lead copy\n**Bold text**");
    }

    public function test_template_linkage_is_displayed_read_only_in_editor(): void
    {
        Http::fake(function (Request $request) {
            if ($request->method() === 'GET' && $request->url() === $this->apiBaseUrl.'/admin/categories') {
                return Http::response(['data' => [$this->categoryResource()]], 200);
            }

            if ($request->method() === 'GET' && $request->url() === $this->apiBaseUrl.'/admin/tags') {
                return Http::response(['data' => [$this->tagResource()]], 200);
            }

            if ($request->method() === 'GET' && $request->url() === $this->apiBaseUrl.'/admin/templates') {
                return Http::response(['data' => [$this->templateResource()]], 200);
            }

            if ($request->method() === 'GET' && str_starts_with($request->url(), $this->apiBaseUrl.'/admin/media')) {
                return Http::response(['data' => [$this->mediaResource()]], 200);
            }

            if ($request->method() === 'GET' && $request->url() === $this->apiBaseUrl.'/admin/posts/1') {
                return Http::response([
                    'data' => $this->postResource([
                        'id' => 1,
                        'title' => 'Templated Post',
                        'blocks' => [
                            [
                                'id' => 100,
                                'block_key' => 'intro',
                                'block_type' => 'paragraph',
                                'sort_order' => 1,
                                'content_markdown' => 'Existing lead',
                                'content_html_cache' => null,
                                'plain_text_cache' => 'Existing lead',
                                'settings' => [],
                                'source_template_block_id' => 5,
                                'created_at' => '2026-06-10T09:00:00Z',
                                'updated_at' => '2026-06-10T09:00:00Z',
                            ],
                        ],
                    ]),
                ], 200);
            }

            return Http::response(['message' => 'Unexpected request.'], 500);
        });

        $response = $this->withSession($this->authenticatedSession())
            ->get(route('posts.edit', ['post' => 1]));

        $response
            ->assertOk()
            ->assertSee('Markdown tools')
            ->assertSee('Template block #5')
            ->assertDontSee('Source Template Block ID');
    }

    public function test_post_can_be_published_scheduled_unpublished_and_deleted_from_editor(): void
    {
        session($this->authenticatedSession());

        $draft = $this->postResource([
            'id' => 1,
            'title' => 'Existing Post',
            'status' => 'draft',
            'published_at' => null,
            'scheduled_for' => null,
        ]);

        $published = array_replace($draft, [
            'status' => 'published',
            'published_at' => '2026-06-17T10:00:00Z',
        ]);

        $scheduled = array_replace($draft, [
            'status' => 'scheduled',
            'published_at' => null,
            'scheduled_for' => '2026-06-18T10:00:00Z',
        ]);

        $unpublished = array_replace($draft, [
            'status' => 'unpublished',
            'published_at' => null,
            'scheduled_for' => null,
        ]);

        Http::fake(function (Request $request) use ($draft, $published, $scheduled, $unpublished) {
            if ($request->method() === 'GET' && $request->url() === $this->apiBaseUrl.'/admin/categories') {
                return Http::response(['data' => [$this->categoryResource()]], 200);
            }

            if ($request->method() === 'GET' && $request->url() === $this->apiBaseUrl.'/admin/tags') {
                return Http::response(['data' => [$this->tagResource()]], 200);
            }

            if ($request->method() === 'GET' && $request->url() === $this->apiBaseUrl.'/admin/templates') {
                return Http::response(['data' => [$this->templateResource()]], 200);
            }

            if ($request->method() === 'GET' && str_starts_with($request->url(), $this->apiBaseUrl.'/admin/media')) {
                return Http::response(['data' => [$this->mediaResource()]], 200);
            }

            if ($request->method() === 'GET' && $request->url() === $this->apiBaseUrl.'/admin/posts/1') {
                return Http::response(['data' => $draft], 200);
            }

            if ($request->method() === 'POST' && $request->url() === $this->apiBaseUrl.'/admin/posts/1/publish') {
                return Http::response(['data' => $published], 200);
            }

            if ($request->method() === 'POST' && $request->url() === $this->apiBaseUrl.'/admin/posts/1/schedule') {
                $this->assertArrayHasKey('scheduled_for', $request->data());

                return Http::response(['data' => $scheduled], 200);
            }

            if ($request->method() === 'POST' && $request->url() === $this->apiBaseUrl.'/admin/posts/1/unpublish') {
                return Http::response(['data' => $unpublished], 200);
            }

            if ($request->method() === 'DELETE' && $request->url() === $this->apiBaseUrl.'/admin/posts/1') {
                return Http::response([], 204);
            }

            return Http::response(['message' => 'Unexpected request.'], 500);
        });

        Livewire::test(Editor::class, ['post' => 1])
            ->call('openActionDialog', 'publish')
            ->assertSet('actionDialogOpen', true)
            ->call('executeAction')
            ->assertSet('status', 'published')
            ->call('openActionDialog', 'schedule')
            ->set('scheduledFor', now()->addDay()->format('Y-m-d\TH:i'))
            ->call('executeAction')
            ->assertSet('status', 'scheduled')
            ->call('openActionDialog', 'unpublish')
            ->call('executeAction')
            ->assertSet('status', 'unpublished')
            ->call('openActionDialog', 'delete')
            ->call('executeAction')
            ->assertRedirect(route('posts.index'));
    }

    public function test_post_schedule_action_in_editor_maps_validation_errors(): void
    {
        session($this->authenticatedSession());

        $draft = $this->postResource([
            'id' => 1,
            'title' => 'Existing Post',
            'status' => 'draft',
        ]);

        Http::fake(function (Request $request) use ($draft) {
            if ($request->method() === 'GET' && $request->url() === $this->apiBaseUrl.'/admin/categories') {
                return Http::response(['data' => [$this->categoryResource()]], 200);
            }

            if ($request->method() === 'GET' && $request->url() === $this->apiBaseUrl.'/admin/tags') {
                return Http::response(['data' => [$this->tagResource()]], 200);
            }

            if ($request->method() === 'GET' && $request->url() === $this->apiBaseUrl.'/admin/templates') {
                return Http::response(['data' => [$this->templateResource()]], 200);
            }

            if ($request->method() === 'GET' && str_starts_with($request->url(), $this->apiBaseUrl.'/admin/media')) {
                return Http::response(['data' => [$this->mediaResource()]], 200);
            }

            if ($request->method() === 'GET' && $request->url() === $this->apiBaseUrl.'/admin/posts/1') {
                return Http::response(['data' => $draft], 200);
            }

            if ($request->method() === 'POST' && $request->url() === $this->apiBaseUrl.'/admin/posts/1/schedule') {
                return Http::response([
                    'message' => 'The given data was invalid.',
                    'errors' => [
                        'scheduled_for' => ['The scheduled for field must be a date after now.'],
                    ],
                ], 422);
            }

            return Http::response(['message' => 'Unexpected request.'], 500);
        });

        Livewire::test(Editor::class, ['post' => 1])
            ->call('openActionDialog', 'schedule')
            ->set('scheduledFor', now()->addDay()->format('Y-m-d\TH:i'))
            ->call('executeAction')
            ->assertHasErrors(['scheduledFor'])
            ->assertSee('The given data was invalid.')
            ->assertSee('The scheduled for field must be a date after now.');
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

    protected function categoryResource(): array
    {
        return [
            'id' => 4,
            'name' => 'AI Systems',
            'slug' => 'ai-systems',
            'description' => 'Category description',
            'parent_id' => null,
            'is_active' => true,
            'sort_order' => 10,
            'created_at' => '2026-06-10T09:00:00Z',
            'updated_at' => '2026-06-12T09:00:00Z',
        ];
    }

    protected function tagResource(): array
    {
        return [
            'id' => 7,
            'name' => 'Automation',
            'slug' => 'automation',
            'description' => 'Tag description',
            'is_active' => true,
            'created_at' => '2026-06-10T09:00:00Z',
            'updated_at' => '2026-06-12T09:00:00Z',
        ];
    }

    protected function templateResource(): array
    {
        return [
            'id' => 8,
            'name' => 'Standard Layout',
            'slug' => 'standard-layout',
            'description' => 'Template description',
            'template_type' => 'standard',
            'status' => 'active',
            'default_excerpt_prompt' => null,
            'default_meta' => [],
            'blocks' => [],
            'created_at' => '2026-06-10T09:00:00Z',
            'updated_at' => '2026-06-12T09:00:00Z',
        ];
    }

    protected function mediaResource(): array
    {
        return [
            'id' => 12,
            'ulid' => '01J00000000000000000000012',
            'original_filename' => 'hero-image.webp',
            'mime_type' => 'image/webp',
            'alt_text' => 'Hero image',
            'caption' => null,
            'url' => 'https://example.com/hero-image.webp',
            'source_type' => 'uploaded',
            'source_url' => null,
            'attribution_text' => null,
            'width' => 1600,
            'height' => 900,
            'size_bytes' => 204800,
            'usage_count' => 0,
            'created_at' => '2026-06-10T09:00:00Z',
            'updated_at' => '2026-06-12T09:00:00Z',
        ];
    }

    protected function postResource(array $overrides = []): array
    {
        return array_replace([
            'id' => 1,
            'ulid' => '01J00000000000000000000000',
            'title' => 'Post title',
            'slug' => 'post-title',
            'excerpt' => 'Short post summary.',
            'status' => 'draft',
            'visibility' => 'public',
            'published_at' => null,
            'scheduled_for' => null,
            'canonical_url' => 'https://example.com/post-title',
            'content_version' => 1,
            'reading_time_minutes' => 6,
            'word_count' => 1100,
            'is_featured' => false,
            'is_ai_generated' => false,
            'source_content_brief_id' => null,
            'source_content_topic_id' => null,
            'generated_by_ai_job_id' => null,
            'generated_by' => null,
            'meta' => [],
            'author' => [
                'id' => 9,
                'name' => 'Editorial Lead',
                'email' => 'editor@example.com',
            ],
            'category' => [
                'id' => 4,
                'name' => 'AI Systems',
                'slug' => 'ai-systems',
            ],
            'template' => [
                'id' => 8,
                'name' => 'Standard Layout',
                'slug' => 'standard-layout',
                'template_type' => 'standard',
            ],
            'featured_media' => [
                'id' => 12,
                'ulid' => '01J00000000000000000000012',
                'original_filename' => 'hero-image.webp',
                'mime_type' => 'image/webp',
                'alt_text' => 'Hero image',
                'caption' => null,
                'url' => 'https://example.com/hero-image.webp',
            ],
            'tags' => [
                [
                    'id' => 7,
                    'name' => 'Automation',
                    'slug' => 'automation',
                ],
            ],
            'blocks' => [],
            'created_at' => '2026-06-10T09:00:00Z',
            'updated_at' => '2026-06-12T09:00:00Z',
        ], $overrides);
    }
}
