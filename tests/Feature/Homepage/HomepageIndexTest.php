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
                $this->assertSame([14, 19], $request['featured_editorial']['post_ids']);
                $this->assertSame(['AI Blog Post Checklist', 'SEO Article Structure Template'], $request['promo_section']['bullet_points']);

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
            ->set('featured_editorial.post_ids', ['14', '19'])
            ->set('promo_section.bullet_points', ['AI Blog Post Checklist', 'SEO Article Structure Template'])
            ->call('save')
            ->assertHasNoErrors()
            ->assertSet('updated_at', '2026-06-17 12:30');
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
                        'post_ids' => null,
                        'category_ids' => null,
                    ],
                    'guide_section' => [
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
                'mode' => 'manual',
                'post_ids' => [14, 19],
                'category_ids' => [],
                'limit' => 3,
            ],
            'guide_section' => [
                'title' => 'Practical Wisdom for Builders',
                'description' => 'Curated guides for technical creators.',
                'mode' => 'manual',
                'post_ids' => [31, 32, 33, 34],
                'category_ids' => [],
                'limit' => 4,
            ],
            'topic_section' => [
                'title' => 'Browse by Topic',
                'description' => 'Explore curated collections.',
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
