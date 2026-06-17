<?php

namespace Tests\Feature\Seo;

use App\Livewire\Admin\Posts\Editor;
use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Http;
use Livewire\Livewire;
use Tests\TestCase;

class PostSeoMetadataEditorTest extends TestCase
{
    protected string $apiBaseUrl;

    protected function setUp(): void
    {
        parent::setUp();

        $this->apiBaseUrl = rtrim(config('widewebblog.api.base_url'), '/');
    }

    public function test_existing_post_editor_loads_per_entity_seo_metadata(): void
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
                return Http::response(['data' => $this->postResource()], 200);
            }

            if ($request->method() === 'GET' && $request->url() === $this->apiBaseUrl.'/admin/seo/post/1') {
                return Http::response(['data' => $this->seoMetadataResource()], 200);
            }

            return Http::response(['message' => 'Unexpected request.'], 500);
        });

        Livewire::test(Editor::class, ['post' => 1])
            ->assertSet('metaTitle', 'Editorial SEO Title')
            ->assertSet('metaDescription', 'Search description for the editorial post.')
            ->assertSet('canonicalUrl', 'https://example.com/posts/post-title')
            ->assertSet('robotsIndex', true)
            ->assertSet('robotsFollow', false)
            ->assertSet('ogTitle', 'Editorial OpenGraph Title')
            ->assertSet('ogDescription', 'Social share description.')
            ->assertSet('ogImageMediaId', '12')
            ->assertSet('focusKeyword', 'editorial seo')
            ->assertSee('Update SEO');
    }

    public function test_existing_post_seo_metadata_can_be_updated_from_editor(): void
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
                return Http::response(['data' => $this->postResource()], 200);
            }

            if ($request->method() === 'GET' && $request->url() === $this->apiBaseUrl.'/admin/seo/post/1') {
                return Http::response(['data' => $this->seoMetadataResource()], 200);
            }

            if ($request->method() === 'PUT' && $request->url() === $this->apiBaseUrl.'/admin/seo/post/1') {
                $this->assertSame('Updated SEO Title', $request['meta_title']);
                $this->assertSame('Updated SEO description.', $request['meta_description']);
                $this->assertSame('https://example.com/posts/updated-post-title', $request['canonical_url']);
                $this->assertFalse($request['robots_index']);
                $this->assertTrue($request['robots_follow']);
                $this->assertSame('Updated OG Title', $request['og_title']);
                $this->assertSame('Updated OG description.', $request['og_description']);
                $this->assertSame(12, $request['og_image_media_id']);
                $this->assertSame('updated keyword', $request['focus_keyword']);

                return Http::response([
                    'data' => $this->seoMetadataResource([
                        'meta_title' => 'Updated SEO Title',
                        'meta_description' => 'Updated SEO description.',
                        'canonical_url' => 'https://example.com/posts/updated-post-title',
                        'robots_index' => false,
                        'robots_follow' => true,
                        'og_title' => 'Updated OG Title',
                        'og_description' => 'Updated OG description.',
                        'focus_keyword' => 'updated keyword',
                    ]),
                ], 200);
            }

            return Http::response(['message' => 'Unexpected request.'], 500);
        });

        Livewire::test(Editor::class, ['post' => 1])
            ->set('metaTitle', 'Updated SEO Title')
            ->set('metaDescription', 'Updated SEO description.')
            ->set('canonicalUrl', 'https://example.com/posts/updated-post-title')
            ->set('robotsIndex', false)
            ->set('robotsFollow', true)
            ->set('ogTitle', 'Updated OG Title')
            ->set('ogDescription', 'Updated OG description.')
            ->set('ogImageMediaId', '12')
            ->set('focusKeyword', 'updated keyword')
            ->call('saveSeo')
            ->assertHasNoErrors()
            ->assertSet('metaTitle', 'Updated SEO Title')
            ->assertSet('robotsIndex', false)
            ->assertSet('robotsFollow', true);
    }

    public function test_create_post_editor_does_not_fake_global_seo_settings(): void
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
            ->assertSee('Sitewide defaults are not shown because the service does not expose them here.')
            ->assertSee('SEO editing starts after the post exists.');
    }

    public function test_post_editor_maps_seo_validation_errors(): void
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
                return Http::response(['data' => $this->postResource()], 200);
            }

            if ($request->method() === 'GET' && $request->url() === $this->apiBaseUrl.'/admin/seo/post/1') {
                return Http::response(['data' => $this->seoMetadataResource()], 200);
            }

            if ($request->method() === 'PUT' && $request->url() === $this->apiBaseUrl.'/admin/seo/post/1') {
                return Http::response([
                    'message' => 'The given data was invalid.',
                    'errors' => [
                        'meta_title' => ['The meta title has already been taken for this entity.'],
                        'canonical_url' => ['The canonical url conflicts with another canonical target.'],
                    ],
                ], 422);
            }

            return Http::response(['message' => 'Unexpected request.'], 500);
        });

        Livewire::test(Editor::class, ['post' => 1])
            ->set('metaTitle', 'Valid SEO Title')
            ->set('canonicalUrl', 'https://example.com/posts/post-title')
            ->call('saveSeo')
            ->assertHasErrors(['metaTitle', 'canonicalUrl'])
            ->assertSee('The given data was invalid.')
            ->assertSee('The meta title has already been taken for this entity.')
            ->assertSee('The canonical url conflicts with another canonical target.');
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

    protected function seoMetadataResource(array $overrides = []): array
    {
        return array_replace([
            'id' => '10',
            'seoable_type' => 'post',
            'seoable_id' => '1',
            'meta_title' => 'Editorial SEO Title',
            'meta_description' => 'Search description for the editorial post.',
            'canonical_url' => 'https://example.com/posts/post-title',
            'robots_index' => true,
            'robots_follow' => false,
            'og_title' => 'Editorial OpenGraph Title',
            'og_description' => 'Social share description.',
            'og_image_media' => [
                'id' => '12',
                'ulid' => '01J00000000000000000000012',
                'original_filename' => 'hero-image.webp',
                'mime_type' => 'image/webp',
                'alt_text' => 'Hero image',
                'caption' => null,
                'url' => 'https://example.com/hero-image.webp',
            ],
            'schema_type' => '',
            'schema_payload' => [],
            'focus_keyword' => 'editorial seo',
            'created_at' => '2026-06-10T09:00:00Z',
            'updated_at' => '2026-06-12T09:00:00Z',
        ], $overrides);
    }
}
