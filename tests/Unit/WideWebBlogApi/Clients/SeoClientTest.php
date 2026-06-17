<?php

namespace Tests\Unit\WideWebBlogApi\Clients;

use App\Services\WideWebBlogApi\Clients\SeoClient;
use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class SeoClientTest extends TestCase
{
    protected string $apiBaseUrl;

    protected function setUp(): void
    {
        parent::setUp();

        $this->apiBaseUrl = rtrim(config('widewebblog.api.base_url'), '/');
    }

    public function test_it_can_load_seo_metadata_for_a_supported_entity(): void
    {
        Http::fake(function (Request $request) {
            $this->assertSame('GET', $request->method());
            $this->assertSame($this->apiBaseUrl.'/admin/seo/post/42', $request->url());

            return Http::response([
                'data' => [
                    'id' => '10',
                    'seoable_type' => 'post',
                    'seoable_id' => '42',
                    'meta_title' => 'SEO Title',
                    'meta_description' => 'SEO description',
                    'canonical_url' => 'https://example.com/posts/seo-title',
                    'robots_index' => true,
                    'robots_follow' => true,
                    'og_title' => 'OG Title',
                    'og_description' => 'OG description',
                    'og_image_media' => null,
                    'schema_type' => '',
                    'schema_payload' => [],
                    'focus_keyword' => 'seo title',
                    'created_at' => '2026-06-17T08:00:00Z',
                    'updated_at' => '2026-06-17T08:00:00Z',
                ],
            ], 200);
        });

        $response = app(SeoClient::class)->show('test-token', 'Bearer', 'post', 42);

        $this->assertSame('SEO Title', $response['data']['meta_title']);
        $this->assertSame('post', $response['data']['seoable_type']);
    }

    public function test_it_can_update_seo_metadata_for_a_supported_entity(): void
    {
        Http::fake(function (Request $request) {
            $this->assertSame('PUT', $request->method());
            $this->assertSame($this->apiBaseUrl.'/admin/seo/post/42', $request->url());
            $this->assertSame('SEO Title', $request['meta_title']);
            $this->assertSame('https://example.com/posts/seo-title', $request['canonical_url']);
            $this->assertFalse($request['robots_follow']);
            $this->assertSame(12, $request['og_image_media_id']);

            return Http::response([
                'data' => [
                    'id' => '10',
                    'seoable_type' => 'post',
                    'seoable_id' => '42',
                    'meta_title' => 'SEO Title',
                    'meta_description' => 'SEO description',
                    'canonical_url' => 'https://example.com/posts/seo-title',
                    'robots_index' => true,
                    'robots_follow' => false,
                    'og_title' => 'OG Title',
                    'og_description' => 'OG description',
                    'og_image_media' => [
                        'id' => '12',
                        'ulid' => '01hzexample',
                        'original_filename' => 'hero-image.webp',
                        'mime_type' => 'image/webp',
                        'alt_text' => 'Hero image',
                        'caption' => '',
                        'url' => 'https://cdn.example.com/hero-image.webp',
                    ],
                    'schema_type' => '',
                    'schema_payload' => [],
                    'focus_keyword' => 'seo title',
                    'created_at' => '2026-06-17T08:00:00Z',
                    'updated_at' => '2026-06-17T09:00:00Z',
                ],
            ], 200);
        });

        $response = app(SeoClient::class)->update('test-token', 'Bearer', 'post', 42, [
            'meta_title' => 'SEO Title',
            'meta_description' => 'SEO description',
            'canonical_url' => 'https://example.com/posts/seo-title',
            'robots_index' => true,
            'robots_follow' => false,
            'og_title' => 'OG Title',
            'og_description' => 'OG description',
            'og_image_media_id' => 12,
            'focus_keyword' => 'seo title',
        ]);

        $this->assertSame('12', $response['data']['og_image_media']['id']);
        $this->assertFalse($response['data']['robots_follow']);
    }

    public function test_it_can_load_seo_score_for_a_supported_entity(): void
    {
        Http::fake(function (Request $request) {
            $this->assertSame('GET', $request->method());
            $this->assertSame($this->apiBaseUrl.'/admin/seo/score/post/42', $request->url());

            return Http::response([
                'data' => [
                    'seoable_type' => 'post',
                    'seoable_id' => 42,
                    'total_score' => 78,
                    'max_score' => 100,
                    'grade' => 'good',
                    'advisory' => true,
                    'subscores' => [
                        'metadata' => ['score' => 30, 'max_score' => 35, 'suggestion_count' => 1],
                    ],
                    'recommendations' => ['Tighten the meta description.'],
                ],
            ], 200);
        });

        $response = app(SeoClient::class)->score('test-token', 'Bearer', 'post', 42);

        $this->assertSame(78, $response['data']['total_score']);
        $this->assertSame('good', $response['data']['grade']);
    }

    public function test_it_can_load_generated_schema_for_a_supported_entity(): void
    {
        Http::fake(function (Request $request) {
            $this->assertSame('GET', $request->method());
            $this->assertSame($this->apiBaseUrl.'/admin/seo/schema/post/42', $request->url());

            return Http::response([
                'data' => [
                    '@context' => 'https://schema.org',
                    '@graph' => [
                        ['@type' => 'Organization'],
                        ['@type' => 'TechArticle', 'headline' => 'SEO Title'],
                    ],
                ],
            ], 200);
        });

        $response = app(SeoClient::class)->schema('test-token', 'Bearer', 'post', 42);

        $this->assertSame('https://schema.org', $response['data']['@context']);
        $this->assertSame('TechArticle', $response['data']['@graph'][1]['@type']);
    }
}
