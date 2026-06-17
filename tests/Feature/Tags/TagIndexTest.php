<?php

namespace Tests\Feature\Tags;

use App\Livewire\Admin\Tags\Index;
use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Http;
use Livewire\Livewire;
use Tests\TestCase;

class TagIndexTest extends TestCase
{
    protected string $apiBaseUrl;

    protected function setUp(): void
    {
        parent::setUp();

        $this->apiBaseUrl = rtrim(config('widewebblog.api.base_url'), '/');
    }

    public function test_tags_index_renders_service_backed_tags(): void
    {
        Http::fake([
            $this->apiBaseUrl.'/admin/tags' => Http::response([
                'data' => [
                    $this->tagResource(['id' => 1, 'name' => 'AI Agents', 'slug' => 'ai-agents']),
                    $this->tagResource(['id' => 2, 'name' => 'Automation', 'slug' => 'automation']),
                ],
            ], 200),
        ]);

        $response = $this->withSession($this->authenticatedSession())
            ->get(route('tags.index'));

        $response
            ->assertOk()
            ->assertSee('Create Tag')
            ->assertSee('AI Agents')
            ->assertSee('Automation');
    }

    public function test_tag_form_maps_api_validation_errors(): void
    {
        session($this->authenticatedSession());

        Http::fake(function (Request $request) {
            if ($request->method() === 'GET' && $request->url() === $this->apiBaseUrl.'/admin/tags') {
                return Http::response(['data' => []], 200);
            }

            if ($request->method() === 'POST' && $request->url() === $this->apiBaseUrl.'/admin/tags') {
                return Http::response([
                    'message' => 'The given data was invalid.',
                    'errors' => [
                        'name' => ['The name has already been taken.'],
                        'slug' => ['The slug has already been taken.'],
                    ],
                ], 422);
            }

            return Http::response(['message' => 'Unexpected request.'], 500);
        });

        Livewire::test(Index::class)
            ->call('openCreateDrawer')
            ->set('name', 'Duplicate')
            ->set('slug', 'duplicate')
            ->call('save')
            ->assertHasErrors(['name', 'slug'])
            ->assertSee('The given data was invalid.')
            ->assertSee('The name has already been taken.')
            ->assertSee('The slug has already been taken.');
    }

    public function test_tags_can_be_created_updated_and_deleted_from_the_screen(): void
    {
        session($this->authenticatedSession());

        $initial = $this->tagResource([
            'id' => 1,
            'name' => 'AI Agents',
            'slug' => 'ai-agents',
        ]);

        $created = $this->tagResource([
            'id' => 2,
            'name' => 'Automation',
            'slug' => 'automation',
        ]);

        $updated = $this->tagResource([
            'id' => 1,
            'name' => 'Architecture',
            'slug' => 'architecture',
            'is_active' => false,
        ]);

        $getIndexCount = 0;

        Http::fake(function (Request $request) use (&$getIndexCount, $initial, $created, $updated) {
            if ($request->method() === 'GET' && $request->url() === $this->apiBaseUrl.'/admin/tags') {
                $getIndexCount++;

                return Http::response([
                    'data' => match ($getIndexCount) {
                        1 => [$initial],
                        2 => [$initial, $created],
                        3 => [$updated, $created],
                        default => [$updated],
                    },
                ], 200);
            }

            if ($request->method() === 'POST' && $request->url() === $this->apiBaseUrl.'/admin/tags') {
                return Http::response(['data' => $created], 201);
            }

            if ($request->method() === 'GET' && $request->url() === $this->apiBaseUrl.'/admin/tags/1') {
                return Http::response(['data' => $initial], 200);
            }

            if ($request->method() === 'PUT' && $request->url() === $this->apiBaseUrl.'/admin/tags/1') {
                return Http::response(['data' => $updated], 200);
            }

            if ($request->method() === 'DELETE' && $request->url() === $this->apiBaseUrl.'/admin/tags/2') {
                return Http::response([], 204);
            }

            return Http::response(['message' => 'Unexpected request.'], 500);
        });

        Livewire::test(Index::class)
            ->assertSee('AI Agents')
            ->call('openCreateDrawer')
            ->set('name', 'Automation')
            ->set('description', 'Workflow automation topics.')
            ->call('save')
            ->assertHasNoErrors()
            ->assertSee('Automation')
            ->call('openEditDrawer', 1)
            ->set('name', 'Architecture')
            ->set('slug', 'architecture')
            ->set('isActive', false)
            ->call('save')
            ->assertSee('Architecture')
            ->call('confirmDelete', 2)
            ->assertSet('deleteDialogOpen', true)
            ->call('delete')
            ->assertDontSee('Automation');
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

    protected function tagResource(array $overrides = []): array
    {
        return array_replace([
            'id' => 1,
            'ulid' => '01J00000000000000000000000',
            'name' => 'Tag',
            'slug' => 'tag',
            'description' => 'Tag description',
            'is_active' => true,
            'created_at' => '2026-06-10T09:00:00Z',
            'updated_at' => '2026-06-12T09:00:00Z',
        ], $overrides);
    }
}
