<?php

namespace Tests\Feature\Homepage;

use App\Livewire\Admin\Homepage\Index;
use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Http;
use Livewire\Livewire;
use Tests\TestCase;

class HomepageIndexTest extends TestCase
{
    protected string $apiBaseUrl;

    protected function setUp(): void
    {
        parent::setUp();
        $this->apiBaseUrl = rtrim(config('widewebblog.api.base_url'), '/');
    }

    public function test_homepage_screen_loads_service_backed_homepage_data(): void
    {
        Http::fake([
            $this->apiBaseUrl.'/admin/homepage' => Http::response([
                'data' => $this->homepageResource(),
            ], 200),
        ]);

        $response = $this->withSession($this->authenticatedSession())
            ->get(route('homepage.index'));

        $response
            ->assertOk()
            ->assertSee('Homepage')
            ->assertSee('Save All Changes')
            ->assertSee('Featured Editorial')
            ->assertSee('Recent Articles')
            ->assertSee('Free Digital Creator Kit')
            ->assertSee('Homepage SEO');
    }

    public function test_homepage_screen_can_update_nested_homepage_sections(): void
    {
        session($this->authenticatedSession());

        Http::fake(function (Request $request) {
            if ($request->method() === 'GET' && $request->url() === $this->apiBaseUrl.'/admin/homepage') {
                return Http::response(['data' => $this->homepageResource()], 200);
            }

            if ($request->method() === 'PUT' && $request->url() === $this->apiBaseUrl.'/admin/homepage') {
                $this->assertSame('Learn AI, SEO, Blogging, and Digital Growth', $request['hero']['title']);
                $this->assertSame(2, $request['featured_editorial']['limit']);
                $this->assertSame(6, $request['guide_section']['limit']);
                $this->assertSame(['AI Blog Post Checklist', 'SEO Article Structure Template'], $request['promo_section']['bullet_points']);
                $this->assertArrayNotHasKey('mode', $request['featured_editorial']);
                $this->assertArrayNotHasKey('post_ids', $request['featured_editorial']);
                $this->assertArrayNotHasKey('category_ids', $request['featured_editorial']);
                $this->assertArrayNotHasKey('mode', $request['guide_section']);
                $this->assertArrayNotHasKey('post_ids', $request['guide_section']);
                $this->assertArrayNotHasKey('category_ids', $request['guide_section']);
                $this->assertArrayNotHasKey('category_ids', $request['topic_section']);

                return Http::response([
                    'data' => array_replace_recursive($this->homepageResource(), $request->data(), [
                        'updated_at' => '2026-06-17T12:30:00Z',
                    ]),
                ], 200);
            }

            return Http::response(['message' => 'Unexpected request.'], 500);
        });

        Livewire::test(Index::class)
            ->set('hero.title', 'Learn AI, SEO, Blogging, and Digital Growth')
            ->set('featured_editorial.limit', '2')
            ->set('guide_section.limit', '6')
            ->set('promo_section.bullet_points', ['AI Blog Post Checklist', 'SEO Article Structure Template'])
            ->call('save')
            ->assertHasNoErrors()
            ->assertSet('updated_at', '2026-06-17 12:30');
    }

    public function test_homepage_screen_encodes_hero_media_url_path_before_update(): void
    {
        session($this->authenticatedSession());

        $rawMediaUrl = 'https://media.widewebblog.com/06UTC2019pm/2026/06/01KVJ+20268Q+00:00JunPM25C+2026J842026JunUTC6Q16UTC9E-66b-bpm66UTCSat, 20 Jun 2026 16:19:20 +0000.6UTCbZ';
        $encodedMediaUrl = 'https://media.widewebblog.com/06UTC2019pm/2026/06/01KVJ%2B20268Q%2B00%3A00JunPM25C%2B2026J842026JunUTC6Q16UTC9E-66b-bpm66UTCSat%2C%2020%20Jun%202026%2016%3A19%3A20%20%2B0000.6UTCbZ';

        Http::fake(function (Request $request) use ($rawMediaUrl, $encodedMediaUrl) {
            if ($request->method() === 'GET' && $request->url() === $this->apiBaseUrl.'/admin/homepage') {
                return Http::response(['data' => $this->homepageResource()], 200);
            }

            if ($request->method() === 'PUT' && $request->url() === $this->apiBaseUrl.'/admin/homepage') {
                $this->assertSame($encodedMediaUrl, $request['hero']['media_url']);

                return Http::response([
                    'data' => array_replace_recursive($this->homepageResource(), $request->data(), [
                        'updated_at' => '2026-06-20T16:30:00Z',
                    ]),
                ], 200);
            }

            return Http::response(['message' => 'Unexpected request.'], 500);
        });

        Livewire::test(Index::class)
            ->set('hero.media_url', $rawMediaUrl)
            ->call('save')
            ->assertHasNoErrors()
            ->assertSet('hero.media_url', $encodedMediaUrl);
    }

    public function test_homepage_screen_maps_nested_api_validation_errors(): void
    {
        session($this->authenticatedSession());

        Http::fake(function (Request $request) {
            if ($request->method() === 'GET' && $request->url() === $this->apiBaseUrl.'/admin/homepage') {
                return Http::response(['data' => $this->homepageResource()], 200);
            }

            if ($request->method() === 'PUT' && $request->url() === $this->apiBaseUrl.'/admin/homepage') {
                return Http::response([
                    'message' => 'The given data was invalid.',
                    'errors' => [
                        'hero.title' => ['The hero title field is required.'],
                        'promo_section.stats.0.label' => ['The promo section stats.0.label field is required.'],
                    ],
                ], 422);
            }

            return Http::response(['message' => 'Unexpected request.'], 500);
        });

        Livewire::test(Index::class)
            ->set('promo_section.stats', [['label' => 'Creators', 'value' => '25k+']])
            ->call('save')
            ->assertHasErrors(['hero.title', 'promo_section.stats.0.label'])
            ->assertSee('The given data was invalid.')
            ->assertSee('The hero title field is required.')
            ->assertSee('The promo section stats.0.label field is required.');
    }

    public function test_homepage_screen_handles_null_nested_arrays_from_service_response(): void
    {
        Http::fake([
            $this->apiBaseUrl.'/admin/homepage' => Http::response([
                'data' => array_replace_recursive($this->homepageResource(), [
                    'featured_editorial' => [
                        'mode' => 'automatic',
                        'post_ids' => null,
                        'category_ids' => null,
                    ],
                    'guide_section' => [
                        'mode' => 'automatic',
                        'post_ids' => null,
                        'category_ids' => null,
                    ],
                    'topic_section' => [
                        'category_ids' => null,
                    ],
                    'promo_section' => [
                        'bullet_points' => null,
                        'stats' => null,
                    ],
                ]),
            ], 200),
        ]);

        $response = $this->withSession($this->authenticatedSession())
            ->get(route('homepage.index'));

        $response
            ->assertOk()
            ->assertSee('Homepage')
            ->assertSee('Featured Editorial')
            ->assertSee('Recent Articles')
            ->assertSee('Promo Section');
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

    protected function homepageResource(): array
    {
        return [
            'hero' => [
                'eyebrow' => 'The Knowledge Hub',
                'title' => 'Learn AI, SEO, Blogging, and Digital Growth',
                'description' => 'Authority-led insights and practical tutorials.',
                'primary_cta_label' => 'Start Reading',
                'primary_cta_url' => '/guides',
                'secondary_cta_label' => 'View AI Tools',
                'secondary_cta_url' => '/tools',
                'media_url' => 'https://example.com/home-hero.jpg',
                'media_alt' => 'Analytics dashboard on laptop',
            ],
            'featured_editorial' => [
                'title' => 'Featured Editorial',
                'description' => 'Expert analysis on the evolving digital landscape.',
                'mode' => 'automatic',
                'post_ids' => [],
                'category_ids' => [],
                'limit' => 2,
            ],
            'guide_section' => [
                'title' => 'Recent Articles',
                'description' => 'Fresh analysis and practical reads from the latest published work.',
                'mode' => 'automatic',
                'post_ids' => [],
                'category_ids' => [],
                'limit' => 6,
            ],
            'topic_section' => [
                'title' => 'Explore Core Topics',
                'description' => 'Explore live category collections across the site’s active topics.',
                'category_ids' => [1, 2, 3, 4],
            ],
            'promo_section' => [
                'enabled' => true,
                'eyebrow' => 'Exclusive Resources',
                'title' => 'Free Digital Creator Kit',
                'description' => 'Download our professional hub of free assets.',
                'bullet_points' => ['AI Blog Post Checklist', 'SEO Article Structure Template'],
                'primary_cta_label' => 'Claim Your Free Kit',
                'primary_cta_url' => '/resources/creator-kit',
                'stats' => [
                    ['label' => 'Creators', 'value' => '25k+'],
                    ['label' => 'Rating', 'value' => '4.9/5'],
                ],
            ],
            'newsletter_section' => [
                'enabled' => true,
                'title' => 'Stay Ahead of the Curve',
                'description' => 'Join 25,000+ digital architects.',
            ],
            'seo' => [
                'meta_title' => 'Wide Web Blog',
                'meta_description' => 'Technical SEO, AI, and digital growth insights.',
            ],
            'updated_at' => '2026-06-17T10:00:00Z',
            'updated_by' => [
                'id' => 1,
                'name' => 'Admin User',
                'email' => 'admin@example.com',
            ],
        ];
    }
}
