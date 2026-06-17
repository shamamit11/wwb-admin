<?php

namespace Tests\Feature\KnowledgeBase;

use App\Livewire\Admin\KnowledgeBase\Index;
use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Http;
use Livewire\Livewire;
use Tests\TestCase;

class KnowledgeBaseIndexTest extends TestCase
{
    protected string $apiBaseUrl;

    protected function setUp(): void
    {
        parent::setUp();
        $this->apiBaseUrl = rtrim(config('widewebblog.api.base_url'), '/');
    }

    public function test_knowledge_base_index_renders_service_backed_entries(): void
    {
        Http::fake([
            $this->apiBaseUrl.'/admin/knowledge-base*' => Http::response([
                'data' => [
                    $this->entryResource([
                        'id' => 1,
                        'title' => 'Agent Memory Research',
                        'slug' => 'agent-memory-research',
                        'entry_type' => 'research',
                        'status' => 'active',
                    ]),
                    $this->entryResource([
                        'id' => 2,
                        'title' => 'Prompting Reference',
                        'slug' => 'prompting-reference',
                        'entry_type' => 'reference',
                    ]),
                ],
            ], 200),
        ]);

        $response = $this->withSession($this->authenticatedSession())
            ->get(route('knowledge-base.index'));

        $response
            ->assertOk()
            ->assertSee('Create Knowledge Entry')
            ->assertSee('Agent Memory Research')
            ->assertSee('Prompting Reference')
            ->assertSee('Knowledge Base');
    }

    public function test_knowledge_base_entry_can_be_deleted_from_index(): void
    {
        session($this->authenticatedSession());

        $initial = $this->entryResource([
            'id' => 1,
            'title' => 'Agent Memory Research',
        ]);

        Http::fake(function (Request $request) use ($initial) {
            if ($request->method() === 'GET' && str_starts_with($request->url(), $this->apiBaseUrl.'/admin/knowledge-base')) {
                static $count = 0;
                $count++;

                return Http::response([
                    'data' => $count === 1 ? [$initial] : [],
                ], 200);
            }

            if ($request->method() === 'DELETE' && $request->url() === $this->apiBaseUrl.'/admin/knowledge-base/1') {
                return Http::response([], 204);
            }

            return Http::response(['message' => 'Unexpected request.'], 500);
        });

        Livewire::test(Index::class)
            ->assertSee('Agent Memory Research')
            ->call('confirmDelete', 1)
            ->assertSet('deleteDialogOpen', true)
            ->call('delete')
            ->assertDontSee('Agent Memory Research');
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

    protected function entryResource(array $overrides = []): array
    {
        return array_replace([
            'id' => 1,
            'ulid' => '01J00000000000000000000000',
            'title' => 'Knowledge entry',
            'slug' => 'knowledge-entry',
            'entry_type' => 'note',
            'status' => 'draft',
            'summary' => 'Short summary.',
            'content_markdown' => "# Heading\n\nReference content",
            'source_url' => 'https://example.com/source',
            'metadata' => ['research'],
            'linked_posts' => [],
            'linked_topics' => [],
            'created_at' => '2026-06-10T09:00:00Z',
            'updated_at' => '2026-06-12T09:00:00Z',
        ], $overrides);
    }
}
