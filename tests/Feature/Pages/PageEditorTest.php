<?php

namespace Tests\Feature\Pages;

use App\Livewire\Admin\Pages\Editor;
use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Http;
use Livewire\Livewire;
use Tests\TestCase;

class PageEditorTest extends TestCase
{
    protected string $apiBaseUrl;

    protected function setUp(): void
    {
        parent::setUp();
        $this->apiBaseUrl = rtrim(config('widewebblog.api.base_url'), '/');
    }

    public function test_page_create_route_renders_editor_shell(): void
    {
        $response = $this->withSession($this->authenticatedSession())
            ->get(route('pages.create'));

        $response
            ->assertOk()
            ->assertSee('Create Page')
            ->assertSee('Markdown Content')
            ->assertSee('Metadata');
    }

    public function test_page_can_be_created_and_redirects_to_edit_route(): void
    {
        session($this->authenticatedSession());

        Http::fake(function (Request $request) {
            if ($request->method() === 'POST' && $request->url() === $this->apiBaseUrl.'/admin/pages') {
                $this->assertSame('Privacy Policy', $request['title']);
                $this->assertSame('legal', $request['type']);
                $this->assertSame('draft', $request['status']);
                $this->assertSame("## Overview\n\nPolicy body", $request['content_markdown']);
                $this->assertSame(['legal', 'footer-link'], $request['meta']);

                return Http::response([
                    'data' => $this->pageResource([
                        'id' => 12,
                        'title' => 'Privacy Policy',
                        'slug' => 'privacy-policy',
                        'type' => 'legal',
                    ]),
                ], 201);
            }

            return Http::response(['message' => 'Unexpected request.'], 500);
        });

        Livewire::test(Editor::class)
            ->set('title', 'Privacy Policy')
            ->set('slug', 'privacy-policy')
            ->set('pageType', 'legal')
            ->set('status', 'draft')
            ->set('summary', 'Policy summary')
            ->set('contentMarkdown', "## Overview\n\nPolicy body")
            ->set('visibility', 'public')
            ->set('metaJson', '["legal","footer-link"]')
            ->call('save')
            ->assertRedirect(route('pages.edit', ['page' => 12]));
    }

    public function test_existing_page_can_be_loaded_and_updated(): void
    {
        session($this->authenticatedSession());

        Http::fake(function (Request $request) {
            if ($request->method() === 'GET' && $request->url() === $this->apiBaseUrl.'/admin/pages/1') {
                return Http::response([
                    'data' => $this->pageResource([
                        'id' => 1,
                        'title' => 'Existing Page',
                        'meta' => ['legal'],
                        'updated_by' => [
                            'id' => 9,
                            'name' => 'Approver',
                            'email' => 'approver@example.com',
                        ],
                    ]),
                ], 200);
            }

            if ($request->method() === 'PUT' && $request->url() === $this->apiBaseUrl.'/admin/pages/1') {
                $this->assertSame('Updated Page', $request['title']);
                $this->assertSame("# Updated\n\nPreserved markdown", $request['content_markdown']);

                return Http::response([
                    'data' => $this->pageResource([
                        'id' => 1,
                        'title' => 'Updated Page',
                        'content_markdown' => "# Updated\n\nPreserved markdown",
                    ]),
                ], 200);
            }

            return Http::response(['message' => 'Unexpected request.'], 500);
        });

        Livewire::test(Editor::class, ['page' => 1])
            ->assertSet('title', 'Existing Page')
            ->assertSet('metaJson', "[\n    \"legal\"\n]")
            ->assertSet('updatedBy', ['id' => 9, 'name' => 'Approver', 'email' => 'approver@example.com'])
            ->set('title', 'Updated Page')
            ->set('contentMarkdown', "# Updated\n\nPreserved markdown")
            ->call('save')
            ->assertHasNoErrors()
            ->assertSet('title', 'Updated Page');
    }

    public function test_page_editor_maps_api_validation_errors(): void
    {
        session($this->authenticatedSession());

        Http::fake(function (Request $request) {
            if ($request->method() === 'POST' && $request->url() === $this->apiBaseUrl.'/admin/pages') {
                return Http::response([
                    'message' => 'The given data was invalid.',
                    'errors' => [
                        'content_markdown' => ['The content markdown field is required.'],
                        'meta' => ['The meta field must be an array.'],
                    ],
                ], 422);
            }

            return Http::response(['message' => 'Unexpected request.'], 500);
        });

        Livewire::test(Editor::class)
            ->set('title', 'Broken Page')
            ->set('pageType', 'standard')
            ->set('status', 'draft')
            ->set('visibility', 'public')
            ->set('contentMarkdown', 'Valid local content')
            ->set('metaJson', '["broken"]')
            ->call('save')
            ->assertHasErrors(['contentMarkdown', 'metaJson'])
            ->assertSee('The given data was invalid.')
            ->assertSee('The content markdown field is required.')
            ->assertSee('The meta field must be an array.');
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
            'published_at' => '2026-06-10T09:00:00Z',
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
