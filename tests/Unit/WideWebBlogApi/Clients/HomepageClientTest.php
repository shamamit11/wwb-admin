<?php

namespace Tests\Unit\WideWebBlogApi\Clients;

use App\Services\WideWebBlogApi\Clients\HomepageClient;
use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class HomepageClientTest extends TestCase
{
    protected string $apiBaseUrl;

    protected function setUp(): void
    {
        parent::setUp();

        $this->apiBaseUrl = rtrim(config('widewebblog.api.base_url'), '/');
    }

    public function test_it_can_load_and_update_homepage_resource(): void
    {
        Http::fake(function (Request $request) {
            if ($request->method() === 'GET' && $request->url() === $this->apiBaseUrl.'/admin/homepage') {
                return Http::response([
                    'data' => [
                        'hero' => ['title' => 'Homepage hero'],
                        'featured_editorial' => ['mode' => 'manual', 'post_ids' => [1], 'category_ids' => [], 'limit' => 3],
                        'guide_section' => ['mode' => 'manual', 'post_ids' => [2], 'category_ids' => [], 'limit' => 4],
                        'topic_section' => ['category_ids' => [5]],
                        'promo_section' => ['enabled' => true, 'bullet_points' => ['One'], 'stats' => [['label' => 'Creators', 'value' => '25k+']]],
                        'newsletter_section' => ['enabled' => true],
                        'seo' => ['meta_title' => 'Wide Web Blog'],
                        'updated_at' => '2026-06-17T10:00:00Z',
                    ],
                ], 200);
            }

            if ($request->method() === 'PUT' && $request->url() === $this->apiBaseUrl.'/admin/homepage') {
                $this->assertSame('manual', $request['featured_editorial']['mode']);
                $this->assertSame([1, 2, 3], $request['featured_editorial']['post_ids']);
                $this->assertSame([['label' => 'Creators', 'value' => '25k+']], $request['promo_section']['stats']);

                return Http::response([
                    'data' => $request->data() + ['updated_at' => '2026-06-17T11:00:00Z'],
                ], 200);
            }

            return Http::response(['message' => 'Unexpected request.'], 500);
        });

        $client = app(HomepageClient::class);

        $loaded = $client->show('test-token', 'Bearer');
        $updated = $client->update('test-token', 'Bearer', [
            'hero' => ['title' => 'Homepage hero'],
            'featured_editorial' => ['mode' => 'manual', 'post_ids' => [1, 2, 3], 'category_ids' => [], 'limit' => 3],
            'guide_section' => ['mode' => 'manual', 'post_ids' => [2], 'category_ids' => [], 'limit' => 4],
            'topic_section' => ['category_ids' => [5]],
            'promo_section' => ['enabled' => true, 'bullet_points' => ['One'], 'stats' => [['label' => 'Creators', 'value' => '25k+']]],
            'newsletter_section' => ['enabled' => true],
            'seo' => ['meta_title' => 'Wide Web Blog'],
        ]);

        $this->assertSame('Homepage hero', $loaded['data']['hero']['title']);
        $this->assertSame('2026-06-17T11:00:00Z', $updated['data']['updated_at']);
    }
}
