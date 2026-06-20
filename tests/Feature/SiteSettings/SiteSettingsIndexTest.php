<?php

namespace Tests\Feature\SiteSettings;

use App\Livewire\Admin\SiteSettings\Index;
use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Http;
use Livewire\Livewire;
use Tests\TestCase;

class SiteSettingsIndexTest extends TestCase
{
    protected string $apiBaseUrl;

    protected function setUp(): void
    {
        parent::setUp();
        $this->apiBaseUrl = rtrim(config('widewebblog.api.base_url'), '/');
    }

    public function test_site_settings_screen_loads_service_backed_singleton_data(): void
    {
        Http::fake([
            $this->apiBaseUrl.'/admin/site-settings' => Http::response([
                'data' => $this->siteSettingsResource(),
            ], 200),
        ]);

        $response = $this->withSession($this->authenticatedSession())
            ->get(route('site-settings.index'));

        $response
            ->assertOk()
            ->assertSee('Site Settings')
            ->assertSee('Footer Overview')
            ->assertSee('Wide Web Blog')
            ->assertSee('Social Links')
            ->assertSee('Legal Links');
    }

    public function test_site_settings_screen_can_update_footer_content_without_forcing_strict_url_validation(): void
    {
        session($this->authenticatedSession());

        Http::fake(function (Request $request) {
            if ($request->method() === 'GET' && $request->url() === $this->apiBaseUrl.'/admin/site-settings') {
                return Http::response(['data' => $this->siteSettingsResource()], 200);
            }

            if ($request->method() === 'PUT' && $request->url() === $this->apiBaseUrl.'/admin/site-settings') {
                $this->assertSame('Footer copy for the modern web.', $request['footer']['description']);
                $this->assertSame('mailto:hello@widewebblog.test', $request['footer']['social_links'][0]['url']);
                $this->assertSame('/terms', $request['footer']['legal_links'][0]['url']);
                $this->assertSame('privacy-policy', $request['footer']['legal_links'][1]['slug']);

                return Http::response([
                    'data' => array_replace_recursive($this->siteSettingsResource(), $request->data(), [
                        'updated_at' => '2026-06-20T18:45:00Z',
                    ]),
                ], 200);
            }

            return Http::response(['message' => 'Unexpected request.'], 500);
        });

        Livewire::test(Index::class)
            ->set('footer.description', 'Footer copy for the modern web.')
            ->set('footer.social_links', [
                ['label' => 'Email', 'url' => 'mailto:hello@widewebblog.test', 'icon' => 'email'],
                ['label' => 'Share', 'url' => 'https://widewebblog.test/share', 'icon' => 'share'],
            ])
            ->set('footer.legal_links', [
                ['label' => 'Terms', 'slug' => '', 'url' => '/terms'],
                ['label' => 'Privacy Policy', 'slug' => 'privacy-policy', 'url' => ''],
            ])
            ->call('save')
            ->assertHasNoErrors()
            ->assertSet('updated_at', '2026-06-20 18:45');
    }

    public function test_site_settings_screen_maps_nested_api_validation_errors(): void
    {
        session($this->authenticatedSession());

        Http::fake(function (Request $request) {
            if ($request->method() === 'GET' && $request->url() === $this->apiBaseUrl.'/admin/site-settings') {
                return Http::response(['data' => $this->siteSettingsResource()], 200);
            }

            if ($request->method() === 'PUT' && $request->url() === $this->apiBaseUrl.'/admin/site-settings') {
                return Http::response([
                    'message' => 'The given data was invalid.',
                    'errors' => [
                        'footer.social_links.0.label' => ['The footer social links.0.label field is required.'],
                        'footer.legal_links.0.label' => ['The footer legal links.0.label field is required.'],
                    ],
                ], 422);
            }

            return Http::response(['message' => 'Unexpected request.'], 500);
        });

        Livewire::test(Index::class)
            ->set('footer.social_links', [['label' => 'Share', 'url' => 'mailto:hello@widewebblog.test', 'icon' => 'share']])
            ->set('footer.legal_links', [['label' => 'Privacy Policy', 'slug' => 'privacy-policy', 'url' => '']])
            ->call('save')
            ->assertHasErrors(['footer.social_links.0.label', 'footer.legal_links.0.label'])
            ->assertSee('The given data was invalid.')
            ->assertSee('The footer social links.0.label field is required.')
            ->assertSee('The footer legal links.0.label field is required.');
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

    protected function siteSettingsResource(): array
    {
        return [
            'footer' => [
                'brand_name' => 'Wide Web Blog',
                'description' => 'An authoritative digital editorial focused on technical SEO, AI implementation, and content architecture for the modern web.',
                'social_links' => [
                    ['label' => 'Share', 'url' => 'https://widewebblog.test/share', 'icon' => 'share'],
                    ['label' => 'Email', 'url' => 'mailto:hello@widewebblog.test', 'icon' => 'email'],
                ],
                'legal_links' => [
                    ['label' => 'Privacy Policy', 'slug' => 'privacy-policy', 'url' => null],
                    ['label' => 'Terms', 'slug' => null, 'url' => 'https://widewebblog.test/terms'],
                ],
            ],
            'updated_at' => '2026-06-20T17:00:00.000000Z',
            'updated_by' => [
                'id' => 1,
                'name' => 'Admin User',
                'email' => 'admin@example.com',
            ],
        ];
    }
}
