<?php

namespace Tests\Feature\AboutPage;

use App\Livewire\Admin\AboutPage\Index;
use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Http;
use Livewire\Livewire;
use Tests\TestCase;

class AboutPageIndexTest extends TestCase
{
    protected string $apiBaseUrl;

    protected function setUp(): void
    {
        parent::setUp();
        $this->apiBaseUrl = rtrim(config('widewebblog.api.base_url'), '/');
    }

    public function test_about_page_screen_loads_service_backed_singleton_data(): void
    {
        Http::fake([
            $this->apiBaseUrl.'/admin/about-page' => Http::response([
                'data' => $this->aboutPageResource(),
            ], 200),
        ]);

        $response = $this->withSession($this->authenticatedSession())
            ->get(route('about-page.index'));

        $response
            ->assertOk()
            ->assertSee('About Us')
            ->assertSee('Our Mission')
            ->assertSee('Our Values')
            ->assertSee('Meet the Team')
            ->assertSee('SEO');
    }

    public function test_about_page_screen_saves_singleton_payload_with_ordered_arrays(): void
    {
        session($this->authenticatedSession());

        Http::fake(function (Request $request) {
            if ($request->method() === 'GET' && $request->url() === $this->apiBaseUrl.'/admin/about-page') {
                return Http::response(['data' => $this->aboutPageResource()], 200);
            }

            if ($request->method() === 'PUT' && $request->url() === $this->apiBaseUrl.'/admin/about-page') {
                $this->assertSame('Building practical publishing systems for modern creators', $request['hero']['title']);
                $this->assertSame([
                    ['label' => 'Newsletter readers', 'value' => '25k+'],
                    ['label' => 'Articles published', 'value' => '180'],
                ], $request['stats_section']['items']);
                $this->assertSame([
                    ['icon' => 'compass', 'title' => 'Clarity first', 'description' => 'We turn complex systems into practical guidance.'],
                    ['icon' => null, 'title' => 'Operator mindset', 'description' => 'Everything we publish should be immediately usable.'],
                ], $request['values_section']['items']);
                $this->assertSame([
                    ['name' => 'Amit Sharma', 'role' => 'Editor in Chief', 'image_url' => 'https://cdn.example.com/amit.jpg', 'image_alt' => 'Portrait of Amit Sharma'],
                    ['name' => 'Nina Vermeer', 'role' => 'Research Editor', 'image_url' => null, 'image_alt' => null],
                ], $request['team_section']['members']);

                return Http::response([
                    'data' => array_replace_recursive($this->aboutPageResource(), $request->data(), [
                        'updated_at' => '2026-06-20T18:15:00Z',
                    ]),
                ], 200);
            }

            return Http::response(['message' => 'Unexpected request.'], 500);
        });

        Livewire::test(Index::class)
            ->set('hero.title', 'Building practical publishing systems for modern creators')
            ->set('stats_section.items', [
                ['label' => 'Articles published', 'value' => '180'],
                ['label' => 'Newsletter readers', 'value' => '25k+'],
            ])
            ->call('moveListItem', 'stats_section', 'items', 1, 'up')
            ->set('values_section.items', [
                ['icon' => 'compass', 'title' => 'Clarity first', 'description' => 'We turn complex systems into practical guidance.'],
                ['icon' => '', 'title' => 'Operator mindset', 'description' => 'Everything we publish should be immediately usable.'],
            ])
            ->set('team_section.members', [
                ['name' => 'Amit Sharma', 'role' => 'Editor in Chief', 'image_url' => 'https://cdn.example.com/amit.jpg', 'image_alt' => 'Portrait of Amit Sharma'],
                ['name' => 'Nina Vermeer', 'role' => 'Research Editor', 'image_url' => '', 'image_alt' => ''],
            ])
            ->call('save')
            ->assertHasNoErrors()
            ->assertSet('updated_at', '2026-06-20 18:15');
    }

    public function test_about_page_screen_maps_nested_api_validation_errors(): void
    {
        session($this->authenticatedSession());

        Http::fake(function (Request $request) {
            if ($request->method() === 'GET' && $request->url() === $this->apiBaseUrl.'/admin/about-page') {
                return Http::response(['data' => $this->aboutPageResource()], 200);
            }

            if ($request->method() === 'PUT' && $request->url() === $this->apiBaseUrl.'/admin/about-page') {
                return Http::response([
                    'message' => 'The given data was invalid.',
                    'errors' => [
                        'values_section.items.0.title' => ['The values section items.0.title field is required.'],
                        'team_section.members.0.name' => ['The team section members.0.name field is required.'],
                    ],
                ], 422);
            }

            return Http::response(['message' => 'Unexpected request.'], 500);
        });

        Livewire::test(Index::class)
            ->set('values_section.items', [['icon' => '', 'title' => 'Clarity', 'description' => 'Useful description']])
            ->set('team_section.members', [['name' => 'Amit Sharma', 'role' => 'Editor', 'image_url' => '', 'image_alt' => '']])
            ->call('save')
            ->assertHasErrors(['values_section.items.0.title', 'team_section.members.0.name'])
            ->assertSee('The given data was invalid.')
            ->assertSee('The values section items.0.title field is required.')
            ->assertSee('The team section members.0.name field is required.');
    }

    public function test_about_page_screen_handles_null_nested_arrays_from_service_response(): void
    {
        Http::fake([
            $this->apiBaseUrl.'/admin/about-page' => Http::response([
                'data' => array_replace_recursive($this->aboutPageResource(), [
                    'stats_section' => ['items' => null],
                    'values_section' => ['items' => null],
                    'team_section' => ['members' => null],
                ]),
            ], 200),
        ]);

        $response = $this->withSession($this->authenticatedSession())
            ->get(route('about-page.index'));

        $response
            ->assertOk()
            ->assertSee('About Us')
            ->assertSee('Stats')
            ->assertSee('Values')
            ->assertSee('Team');
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

    protected function aboutPageResource(): array
    {
        return [
            'hero' => [
                'eyebrow' => 'Who We Are',
                'title' => 'Building practical publishing systems for modern creators',
                'description' => 'We publish deeply practical guidance for editorial operators navigating AI, search, and audience growth.',
                'media_url' => 'https://cdn.example.com/about-hero.jpg',
                'media_alt' => 'Editorial team collaborating',
            ],
            'mission_section' => [
                'title' => 'Our Mission',
                'description' => 'We help teams publish with more clarity, speed, and strategic confidence.',
                'quote' => 'Make the complicated useful.',
            ],
            'stats_section' => [
                'items' => [
                    ['label' => 'Newsletter readers', 'value' => '25k+'],
                    ['label' => 'Articles published', 'value' => '180'],
                ],
            ],
            'values_section' => [
                'title' => 'Our Values',
                'items' => [
                    ['icon' => 'compass', 'title' => 'Clarity first', 'description' => 'We turn complex systems into practical guidance.'],
                    ['icon' => 'spark', 'title' => 'Operator mindset', 'description' => 'Everything we publish should be immediately usable.'],
                ],
            ],
            'team_section' => [
                'title' => 'Meet the Team',
                'description' => 'A small editorial group with a strong systems and publishing focus.',
                'primary_cta_label' => 'Work With Us',
                'primary_cta_url' => 'https://example.com/contact',
                'members' => [
                    ['name' => 'Amit Sharma', 'role' => 'Editor in Chief', 'image_url' => 'https://cdn.example.com/amit.jpg', 'image_alt' => 'Portrait of Amit Sharma'],
                    ['name' => 'Nina Vermeer', 'role' => 'Research Editor', 'image_url' => 'https://cdn.example.com/nina.jpg', 'image_alt' => 'Portrait of Nina Vermeer'],
                ],
            ],
            'seo' => [
                'meta_title' => 'About Wide Web Blog',
                'meta_description' => 'Learn about the mission, editorial values, and team behind Wide Web Blog.',
            ],
            'updated_at' => '2026-06-20T15:00:00Z',
            'updated_by' => [
                'id' => 1,
                'name' => 'Admin User',
                'email' => 'admin@example.com',
            ],
        ];
    }
}
