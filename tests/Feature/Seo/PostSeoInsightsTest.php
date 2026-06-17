<?php

namespace Tests\Feature\Seo;

use App\Livewire\Admin\Posts\Editor;
use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Http;
use Livewire\Livewire;
use Tests\TestCase;

class PostSeoInsightsTest extends TestCase
{
    protected string $apiBaseUrl;

    protected function setUp(): void
    {
        parent::setUp();

        $this->apiBaseUrl = rtrim(config('widewebblog.api.base_url'), '/');
    }

    public function test_post_editor_surfaces_seo_score_and_schema_output(): void
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

            if ($request->method() === 'GET' && $request->url() === $this->apiBaseUrl.'/admin/seo/score/post/1') {
                return Http::response(['data' => $this->scoreResource()], 200);
            }

            if ($request->method() === 'GET' && $request->url() === $this->apiBaseUrl.'/admin/seo/schema/post/1') {
                return Http::response(['data' => $this->schemaResource()], 200);
            }

            return Http::response(['message' => 'Unexpected request.'], 500);
        });

        Livewire::test(Editor::class, ['post' => 1])
            ->assertSee('SEO 78')
            ->assertSee('Good')
            ->assertSee('Tighten the meta description.')
            ->assertSee('Generated JSON-LD')
            ->assertSee('TechArticle');
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

    protected function postResource(): array
    {
        return [
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
            'tags' => [],
            'blocks' => [],
            'created_at' => '2026-06-10T09:00:00Z',
            'updated_at' => '2026-06-12T09:00:00Z',
        ];
    }

    protected function seoMetadataResource(): array
    {
        return [
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
            'og_image_media' => null,
            'schema_type' => 'TechArticle',
            'schema_payload' => [],
            'focus_keyword' => 'editorial seo',
            'created_at' => '2026-06-10T09:00:00Z',
            'updated_at' => '2026-06-12T09:00:00Z',
        ];
    }

    protected function scoreResource(): array
    {
        return [
            'seoable_type' => 'post',
            'seoable_id' => 1,
            'total_score' => 78,
            'max_score' => 100,
            'grade' => 'good',
            'advisory' => true,
            'subscores' => [
                'metadata' => ['score' => 30, 'max_score' => 35, 'suggestion_count' => 1],
                'content' => ['score' => 22, 'max_score' => 30, 'suggestion_count' => 0],
            ],
            'recommendations' => [
                'Tighten the meta description.',
            ],
        ];
    }

    protected function schemaResource(): array
    {
        return [
            '@context' => 'https://schema.org',
            '@graph' => [
                ['@type' => 'Organization'],
                ['@type' => 'WebSite'],
                ['@type' => 'TechArticle', 'headline' => 'Post title'],
            ],
        ];
    }
}
