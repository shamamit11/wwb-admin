<?php

namespace Tests\Unit\WideWebBlogApi\Clients;

use App\Services\WideWebBlogApi\Clients\ContactPageClient;
use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class ContactPageClientTest extends TestCase
{
    protected string $apiBaseUrl;

    protected function setUp(): void
    {
        parent::setUp();

        $this->apiBaseUrl = rtrim(config('widewebblog.api.base_url'), '/');
    }

    public function test_it_can_load_and_update_contact_page_resource(): void
    {
        Http::fake(function (Request $request) {
            if ($request->method() === 'GET' && $request->url() === $this->apiBaseUrl.'/admin/contact-page') {
                return Http::response([
                    'data' => [
                        'hero' => ['title' => 'Contact hero', 'eyebrow' => 'Get in Touch', 'description' => 'Reach out.'],
                        'contact_form' => ['eyebrow' => 'Contact Form', 'title' => 'Tell us what you need', 'description' => 'Form description', 'submit_label' => 'Send Message', 'success_message' => 'Thanks!'],
                        'contact_reasons' => ['items' => [['title' => 'General', 'description' => 'General question']]],
                        'seo' => ['meta_title' => 'Contact', 'meta_description' => 'Contact page'],
                        'updated_at' => '2026-06-20T18:00:00Z',
                    ],
                ], 200);
            }

            if ($request->method() === 'PUT' && $request->url() === $this->apiBaseUrl.'/admin/contact-page') {
                $this->assertSame('Contact hero', $request['hero']['title']);
                $this->assertSame('Send Message', $request['contact_form']['submit_label']);
                $this->assertSame([['title' => 'General', 'description' => 'General question']], $request['contact_reasons']['items']);

                return Http::response([
                    'data' => $request->data() + ['updated_at' => '2026-06-20T19:00:00Z'],
                ], 200);
            }

            return Http::response(['message' => 'Unexpected request.'], 500);
        });

        $client = app(ContactPageClient::class);

        $loaded = $client->show('test-token', 'Bearer');
        $updated = $client->update('test-token', 'Bearer', [
            'hero' => ['eyebrow' => 'Get in Touch', 'title' => 'Contact hero', 'description' => 'Reach out.'],
            'contact_form' => ['eyebrow' => 'Contact Form', 'title' => 'Tell us what you need', 'description' => 'Form description', 'submit_label' => 'Send Message', 'success_message' => 'Thanks!'],
            'contact_reasons' => ['items' => [['title' => 'General', 'description' => 'General question']]],
            'seo' => ['meta_title' => 'Contact', 'meta_description' => 'Contact page'],
        ]);

        $this->assertSame('Contact hero', $loaded['data']['hero']['title']);
        $this->assertSame('2026-06-20T19:00:00Z', $updated['data']['updated_at']);
    }
}
