<?php

namespace Tests\Unit\WideWebBlogApi\Clients;

use App\Services\WideWebBlogApi\Clients\SiteSettingsClient;
use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class SiteSettingsClientTest extends TestCase
{
    protected string $apiBaseUrl;

    protected function setUp(): void
    {
        parent::setUp();

        $this->apiBaseUrl = rtrim(config('widewebblog.api.base_url'), '/');
    }

    public function test_it_can_load_and_update_site_settings_resource(): void
    {
        Http::fake(function (Request $request) {
            if ($request->method() === 'GET' && $request->url() === $this->apiBaseUrl.'/admin/site-settings') {
                return Http::response([
                    'data' => [
                        'footer' => [
                            'brand_name' => 'Wide Web Blog',
                            'description' => 'Editorial footer copy.',
                            'social_links' => [['label' => 'Share', 'url' => 'mailto:hello@example.com', 'icon' => 'email']],
                            'legal_links' => [['label' => 'Privacy Policy', 'slug' => 'privacy-policy', 'url' => null]],
                        ],
                        'updated_at' => '2026-06-20T17:00:00Z',
                    ],
                ], 200);
            }

            if ($request->method() === 'PUT' && $request->url() === $this->apiBaseUrl.'/admin/site-settings') {
                $this->assertSame('Wide Web Blog', $request['footer']['brand_name']);
                $this->assertSame('mailto:hello@example.com', $request['footer']['social_links'][0]['url']);
                $this->assertSame('/terms', $request['footer']['legal_links'][1]['url']);

                return Http::response([
                    'data' => $request->data() + ['updated_at' => '2026-06-20T18:00:00Z'],
                ], 200);
            }

            return Http::response(['message' => 'Unexpected request.'], 500);
        });

        $client = app(SiteSettingsClient::class);

        $loaded = $client->show('test-token', 'Bearer');
        $updated = $client->update('test-token', 'Bearer', [
            'footer' => [
                'brand_name' => 'Wide Web Blog',
                'description' => 'Editorial footer copy.',
                'social_links' => [['label' => 'Share', 'url' => 'mailto:hello@example.com', 'icon' => 'email']],
                'legal_links' => [
                    ['label' => 'Privacy Policy', 'slug' => 'privacy-policy', 'url' => null],
                    ['label' => 'Terms', 'slug' => null, 'url' => '/terms'],
                ],
            ],
        ]);

        $this->assertSame('Wide Web Blog', $loaded['data']['footer']['brand_name']);
        $this->assertSame('2026-06-20T18:00:00Z', $updated['data']['updated_at']);
    }
}
