<?php

namespace Tests\Feature\Pages;

use App\Livewire\Admin\Pages\Index;
use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Http;
use Livewire\Livewire;
use Tests\TestCase;

class PageIndexTest extends TestCase
{
    protected string $apiBaseUrl;

    protected function setUp(): void
    {
        parent::setUp();
        $this->apiBaseUrl = rtrim(config('widewebblog.api.base_url'), '/');
    }

    public function test_pages_index_renders_service_backed_pages(): void
    {
        Http::fake([
            $this->apiBaseUrl.'/admin/pages*' => Http::response([
                'data' => [
                    $this->pageResource([
                        'id' => 1,
                        'title' => 'Privacy Policy',
                        'slug' => 'privacy-policy',
                        'type' => 'legal',
                        'status' => 'published',
                    ]),
                    $this->pageResource([
                        'id' => 2,
                        'title' => 'FAQ',
                        'slug' => 'faq',
                        'type' => 'faq',
                    ]),
                ],
            ], 200),
        ]);

        $response = $this->withSession($this->authenticatedSession())
            ->get(route('pages.index'));

        $response
            ->assertOk()
            ->assertSee('Create Page')
            ->assertSee('Privacy Policy')
            ->assertSee('FAQ')
            ->assertSee('Pages');
    }

    public function test_page_can_be_deleted_from_index(): void
    {
        session($this->authenticatedSession());

        $initial = $this->pageResource([
            'id' => 1,
            'title' => 'Privacy Policy',
        ]);

        Http::fake(function (Request $request) use ($initial) {
            if ($request->method() === 'GET' && str_starts_with($request->url(), $this->apiBaseUrl.'/admin/pages')) {
                static $count = 0;
                $count++;

                return Http::response([
                    'data' => $count === 1 ? [$initial] : [],
                ], 200);
            }

            if ($request->method() === 'DELETE' && $request->url() === $this->apiBaseUrl.'/admin/pages/1') {
                return Http::response([], 204);
            }

            return Http::response(['message' => 'Unexpected request.'], 500);
        });

        Livewire::test(Index::class)
            ->assertSee('Privacy Policy')
            ->call('confirmDelete', 1)
            ->assertSet('deleteDialogOpen', true)
            ->call('delete')
            ->assertDontSee('Privacy Policy');
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

    protected function pageResource(array $overrides = []): array
    {
        return array_replace([
            'id' => 1,
            'ulid' => '01J00000000000000000000000',
            'title' => 'Static page',
            'slug' => 'static-page',
            'type' => 'standard',
            'status' => 'draft',
            'summary' => 'Short summary.',
            'content_markdown' => "# Heading\n\nPage content",
            'visibility' => 'public',
            'published_at' => null,
            'scheduled_for' => null,
            'canonical_url' => 'https://example.com/static-page',
            'meta' => ['footer-link'],
            'created_by' => [
                'id' => 4,
                'name' => 'Editorial Lead',
                'email' => 'editor@example.com',
            ],
            'updated_by' => null,
            'created_at' => '2026-06-10T09:00:00Z',
            'updated_at' => '2026-06-12T09:00:00Z',
        ], $overrides);
    }
}
