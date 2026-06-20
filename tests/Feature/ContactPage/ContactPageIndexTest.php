<?php

namespace Tests\Feature\ContactPage;

use App\Livewire\Admin\ContactPage\Index;
use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Http;
use Livewire\Livewire;
use Tests\TestCase;

class ContactPageIndexTest extends TestCase
{
    protected string $apiBaseUrl;

    protected function setUp(): void
    {
        parent::setUp();
        $this->apiBaseUrl = rtrim(config('widewebblog.api.base_url'), '/');
    }

    public function test_contact_page_screen_loads_service_backed_singleton_data(): void
    {
        Http::fake([
            $this->apiBaseUrl.'/admin/contact-page' => Http::response([
                'data' => $this->contactPageResource(),
            ], 200),
        ]);

        $response = $this->withSession($this->authenticatedSession())
            ->get(route('contact-page.index'));

        $response
            ->assertOk()
            ->assertSee('Contact Page')
            ->assertSee('Get in Touch')
            ->assertSee('Contact Form')
            ->assertSee('Contact Reasons')
            ->assertSee('SEO');
    }

    public function test_contact_page_screen_can_update_nested_page_content(): void
    {
        session($this->authenticatedSession());

        Http::fake(function (Request $request) {
            if ($request->method() === 'GET' && $request->url() === $this->apiBaseUrl.'/admin/contact-page') {
                return Http::response(['data' => $this->contactPageResource()], 200);
            }

            if ($request->method() === 'PUT' && $request->url() === $this->apiBaseUrl.'/admin/contact-page') {
                $this->assertSame('Talk to the Wide Web Blog team', $request['hero']['title']);
                $this->assertSame('Send Message', $request['contact_form']['submit_label']);
                $this->assertSame([
                    ['title' => 'Partnerships', 'description' => 'Discuss sponsorships, integrations, and collaboration ideas.'],
                    ['title' => 'Editorial Questions', 'description' => 'Ask about articles, corrections, or publishing requests.'],
                ], $request['contact_reasons']['items']);

                return Http::response([
                    'data' => array_replace_recursive($this->contactPageResource(), $request->data(), [
                        'updated_at' => '2026-06-20T20:15:00Z',
                    ]),
                ], 200);
            }

            return Http::response(['message' => 'Unexpected request.'], 500);
        });

        Livewire::test(Index::class)
            ->set('hero.title', 'Talk to the Wide Web Blog team')
            ->set('contact_form.submit_label', 'Send Message')
            ->set('contact_reasons.items', [
                ['title' => 'Editorial Questions', 'description' => 'Ask about articles, corrections, or publishing requests.'],
                ['title' => 'Partnerships', 'description' => 'Discuss sponsorships, integrations, and collaboration ideas.'],
            ])
            ->call('moveListItem', 'contact_reasons', 'items', 1, 'up')
            ->call('save')
            ->assertHasNoErrors()
            ->assertSet('updated_at', '2026-06-20 20:15');
    }

    public function test_contact_page_screen_maps_nested_api_validation_errors(): void
    {
        session($this->authenticatedSession());

        Http::fake(function (Request $request) {
            if ($request->method() === 'GET' && $request->url() === $this->apiBaseUrl.'/admin/contact-page') {
                return Http::response(['data' => $this->contactPageResource()], 200);
            }

            if ($request->method() === 'PUT' && $request->url() === $this->apiBaseUrl.'/admin/contact-page') {
                return Http::response([
                    'message' => 'The given data was invalid.',
                    'errors' => [
                        'contact_form.submit_label' => ['The contact form submit label field is required.'],
                        'contact_reasons.items.0.title' => ['The contact reasons items.0.title field is required.'],
                    ],
                ], 422);
            }

            return Http::response(['message' => 'Unexpected request.'], 500);
        });

        Livewire::test(Index::class)
            ->set('contact_form.submit_label', 'Send Message')
            ->set('contact_reasons.items', [['title' => 'General Questions', 'description' => 'Ask a general editorial question.']])
            ->call('save')
            ->assertHasErrors(['contact_form.submit_label', 'contact_reasons.items.0.title'])
            ->assertSee('The given data was invalid.')
            ->assertSee('The contact form submit label field is required.')
            ->assertSee('The contact reasons items.0.title field is required.');
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

    protected function contactPageResource(): array
    {
        return [
            'hero' => [
                'eyebrow' => 'Get in Touch',
                'title' => 'Talk to the Wide Web Blog team',
                'description' => 'Reach out with editorial questions, partnerships, or general inquiries.',
            ],
            'contact_form' => [
                'eyebrow' => 'Contact Form',
                'title' => 'Tell us what you need',
                'description' => 'Share the context we need so the right person can respond.',
                'submit_label' => 'Send Message',
                'success_message' => 'Thanks for reaching out. We’ll get back to you soon.',
            ],
            'contact_reasons' => [
                'items' => [
                    ['title' => 'Editorial Questions', 'description' => 'Ask about articles, corrections, or publishing requests.'],
                    ['title' => 'Partnerships', 'description' => 'Discuss sponsorships, integrations, and collaboration ideas.'],
                ],
            ],
            'seo' => [
                'meta_title' => 'Contact Wide Web Blog',
                'meta_description' => 'Reach out to Wide Web Blog for editorial questions, partnerships, and general inquiries.',
            ],
            'updated_at' => '2026-06-20T18:00:00Z',
            'updated_by' => [
                'id' => '1',
                'name' => 'Admin User',
            ],
        ];
    }
}
