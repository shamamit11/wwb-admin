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
