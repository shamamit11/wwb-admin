<?php

namespace Tests\Feature\Categories;

use App\Livewire\Admin\Categories\Index;
use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Http;
use Livewire\Livewire;
use Tests\TestCase;

class CategoryIndexTest extends TestCase
{
    protected string $apiBaseUrl;

    protected function setUp(): void
    {
        parent::setUp();

        $this->apiBaseUrl = rtrim(config('widewebblog.api.base_url'), '/');
    }

    public function test_categories_index_renders_service_backed_categories(): void
    {
        Http::fake([
            $this->apiBaseUrl.'/admin/categories' => Http::response([
                'data' => [
                    $this->categoryResource(['id' => 1, 'name' => 'AI Agents', 'slug' => 'ai-agents']),
                    $this->categoryResource(['id' => 2, 'name' => 'SEO', 'slug' => 'seo', 'parent_id' => 1]),
                ],
            ], 200),
        ]);

        $response = $this->withSession($this->authenticatedSession())
            ->get(route('categories.index'));

        $response
            ->assertOk()
            ->assertSee('Create Category')
            ->assertSee('AI Agents')
            ->assertSee('SEO')
            ->assertSee('Top level');
    }

    public function test_category_form_maps_api_validation_errors(): void
    {
        session($this->authenticatedSession());

        Http::fake(function (Request $request) {
            if ($request->method() === 'GET' && $request->url() === $this->apiBaseUrl.'/admin/categories') {
                return Http::response(['data' => []], 200);
            }

            if ($request->method() === 'POST' && $request->url() === $this->apiBaseUrl.'/admin/categories') {
                return Http::response([
                    'message' => 'The given data was invalid.',
                    'errors' => [
                        'name' => ['The name has already been taken.'],
                        'sort_order' => ['The sort order must be at least 0.'],
                    ],
                ], 422);
            }

            return Http::response(['message' => 'Unexpected request.'], 500);
        });

        Livewire::test(Index::class)
            ->call('openCreateDrawer')
            ->set('name', 'Duplicate')
            ->set('sortOrder', '1')
            ->call('save')
            ->assertHasErrors(['name'])
            ->assertSee('The given data was invalid.')
            ->assertSee('The name has already been taken.');
    }

    public function test_categories_can_be_created_updated_and_deleted_from_the_screen(): void
    {
        session($this->authenticatedSession());

        $initial = $this->categoryResource([
            'id' => 1,
            'name' => 'AI Agents',
            'slug' => 'ai-agents',
            'sort_order' => 1,
        ]);

        $created = $this->categoryResource([
            'id' => 2,
            'name' => 'Automation',
            'slug' => 'automation',
            'sort_order' => 4,
        ]);

        $updated = $this->categoryResource([
            'id' => 1,
            'name' => 'AI Systems',
            'slug' => 'ai-systems',
            'sort_order' => 3,
            'is_active' => false,
        ]);

        $getIndexCount = 0;

        Http::fake(function (Request $request) use (&$getIndexCount, $initial, $created, $updated) {
            if ($request->method() === 'GET' && $request->url() === $this->apiBaseUrl.'/admin/categories') {
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

            if ($request->method() === 'POST' && $request->url() === $this->apiBaseUrl.'/admin/categories') {
                return Http::response(['data' => $created], 201);
            }

            if ($request->method() === 'GET' && $request->url() === $this->apiBaseUrl.'/admin/categories/1') {
                return Http::response(['data' => $initial], 200);
            }

            if ($request->method() === 'PUT' && $request->url() === $this->apiBaseUrl.'/admin/categories/1') {
                return Http::response(['data' => $updated], 200);
            }

            if ($request->method() === 'DELETE' && $request->url() === $this->apiBaseUrl.'/admin/categories/2') {
                return Http::response([], 204);
            }

            return Http::response(['message' => 'Unexpected request.'], 500);
        });

        Livewire::test(Index::class)
            ->assertSee('AI Agents')
            ->call('openCreateDrawer')
            ->set('name', 'Automation')
            ->set('description', 'Workflow automation articles.')
            ->set('sortOrder', '4')
            ->call('save')
            ->assertHasNoErrors()
            ->assertSee('Automation')
            ->call('openEditDrawer', 1)
            ->set('name', 'AI Systems')
            ->set('slug', 'ai-systems')
            ->set('isActive', false)
            ->set('sortOrder', '3')
            ->call('save')
            ->assertSee('AI Systems')
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

    protected function categoryResource(array $overrides = []): array
    {
        return array_replace([
            'id' => 1,
            'ulid' => '01J00000000000000000000000',
            'parent_id' => null,
            'name' => 'Category',
            'slug' => 'category',
            'description' => 'Category description',
            'is_active' => true,
            'canonical_url' => 'https://example.com/category',
            'sort_order' => 0,
            'created_at' => '2026-06-10T09:00:00Z',
            'updated_at' => '2026-06-12T09:00:00Z',
        ], $overrides);
    }
}
