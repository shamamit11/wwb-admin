<?php

namespace Tests\Unit\WideWebBlogApi\Clients;

use App\Services\WideWebBlogApi\Clients\AboutPageClient;
use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class AboutPageClientTest extends TestCase
{
    protected string $apiBaseUrl;

    protected function setUp(): void
    {
        parent::setUp();

        $this->apiBaseUrl = rtrim(config('widewebblog.api.base_url'), '/');
    }

    public function test_it_can_load_and_update_about_page_resource(): void
    {
        Http::fake(function (Request $request) {
            if ($request->method() === 'GET' && $request->url() === $this->apiBaseUrl.'/admin/about-page') {
                return Http::response([
                    'data' => [
                        'hero' => ['title' => 'About hero'],
                        'mission_section' => ['title' => 'Mission'],
                        'stats_section' => ['items' => [['label' => 'Readers', 'value' => '25k+']]],
                        'values_section' => ['title' => 'Values', 'items' => [['icon' => 'compass', 'title' => 'Clarity', 'description' => 'Useful advice']]],
                        'team_section' => ['title' => 'Team', 'members' => [['name' => 'Amit', 'role' => 'Editor', 'image_url' => null, 'image_alt' => null]]],
                        'seo' => ['meta_title' => 'About Wide Web Blog'],
                        'updated_at' => '2026-06-20T15:00:00Z',
                    ],
                ], 200);
            }

            if ($request->method() === 'PUT' && $request->url() === $this->apiBaseUrl.'/admin/about-page') {
                $this->assertSame('About hero', $request['hero']['title']);
                $this->assertSame([['label' => 'Readers', 'value' => '25k+']], $request['stats_section']['items']);
                $this->assertSame([['icon' => 'compass', 'title' => 'Clarity', 'description' => 'Useful advice']], $request['values_section']['items']);
                $this->assertSame([['name' => 'Amit', 'role' => 'Editor', 'image_url' => null, 'image_alt' => null]], $request['team_section']['members']);

                return Http::response([
                    'data' => $request->data() + ['updated_at' => '2026-06-20T16:00:00Z'],
                ], 200);
            }

            return Http::response(['message' => 'Unexpected request.'], 500);
        });

        $client = app(AboutPageClient::class);

        $loaded = $client->show('test-token', 'Bearer');
        $updated = $client->update('test-token', 'Bearer', [
            'hero' => ['title' => 'About hero'],
            'mission_section' => ['title' => 'Mission', 'description' => null, 'quote' => null],
            'stats_section' => ['items' => [['label' => 'Readers', 'value' => '25k+']]],
            'values_section' => ['title' => 'Values', 'items' => [['icon' => 'compass', 'title' => 'Clarity', 'description' => 'Useful advice']]],
            'team_section' => ['title' => 'Team', 'description' => null, 'primary_cta_label' => null, 'primary_cta_url' => null, 'members' => [['name' => 'Amit', 'role' => 'Editor', 'image_url' => null, 'image_alt' => null]]],
            'seo' => ['meta_title' => 'About Wide Web Blog', 'meta_description' => null],
        ]);

        $this->assertSame('About hero', $loaded['data']['hero']['title']);
        $this->assertSame('2026-06-20T16:00:00Z', $updated['data']['updated_at']);
    }
}
