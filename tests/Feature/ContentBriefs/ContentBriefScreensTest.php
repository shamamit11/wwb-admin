<?php

namespace Tests\Feature\ContentBriefs;

use App\Livewire\Admin\ContentBriefs\Index;
use App\Livewire\Admin\ContentBriefs\Show;
use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Http;
use Livewire\Livewire;
use Tests\TestCase;

class ContentBriefScreensTest extends TestCase
{
    protected string $apiBaseUrl;

    protected function setUp(): void
    {
        parent::setUp();

        $this->apiBaseUrl = rtrim(config('widewebblog.api.base_url'), '/');
    }

    public function test_content_briefs_index_renders_service_backed_briefs(): void
    {
        $pageOneBriefs = collect(range(1, 10))
            ->map(fn (int $id): array => $this->briefResource([
                'id' => $id,
                'title' => "Brief {$id}",
                'status' => $id <= 5 ? 'draft' : 'approved',
            ]))
            ->all();

        Http::fake([
            $this->apiBaseUrl.'/admin/content-briefs*' => Http::response([
                'data' => $pageOneBriefs,
                'meta' => $this->paginationMeta(total: 15, page: 1, lastPage: 2, from: 1, to: 10),
            ], 200),
        ]);

        $response = $this->withSession($this->authenticatedSession())
            ->get(route('content-briefs.index'));

        $response
            ->assertOk()
            ->assertSee('Content Briefs')
            ->assertSee('Brief 1')
            ->assertSee('Brief 10')
            ->assertDontSee('Brief 11')
            ->assertSee('Draft Briefs')
            ->assertSee('Approved Briefs')
            ->assertSee('Showing 1-10 of 15 briefs')
            ->assertSee('Page 1 of 2');
    }

    public function test_content_briefs_index_uses_service_pagination_for_navigation_and_filter_reset(): void
    {
        session($this->authenticatedSession());

        Http::fake(function (Request $request) {
            if ($request->method() !== 'GET' || ! str_starts_with($request->url(), $this->apiBaseUrl.'/admin/content-briefs')) {
                return Http::response(['message' => 'Unexpected request.'], 500);
            }

            parse_str(parse_url($request->url(), PHP_URL_QUERY) ?? '', $query);

            $this->assertSame('10', (string) ($query['per_page'] ?? null));

            if (($query['search'] ?? null) === 'seo') {
                $this->assertSame('1', (string) ($query['page'] ?? null));

                return Http::response([
                    'data' => [
                        $this->briefResource([
                            'id' => 31,
                            'title' => 'SEO Refresh Brief',
                            'status' => 'approved',
                        ]),
                    ],
                    'meta' => $this->paginationMeta(total: 1, page: 1, lastPage: 1, from: 1, to: 1),
                ], 200);
            }

            if (($query['page'] ?? '1') === '2') {
                return Http::response([
                    'data' => [
                        $this->briefResource([
                            'id' => 22,
                            'title' => 'Second Page Brief',
                            'status' => 'used',
                        ]),
                    ],
                    'meta' => $this->paginationMeta(total: 11, page: 2, lastPage: 2, from: 11, to: 11),
                ], 200);
            }

            $this->assertSame('1', (string) ($query['page'] ?? null));

            return Http::response([
                'data' => [
                    $this->briefResource([
                        'id' => 12,
                        'title' => 'First Page Brief',
                        'status' => 'draft',
                    ]),
                ],
                'meta' => $this->paginationMeta(total: 11, page: 1, lastPage: 2, from: 1, to: 10),
            ], 200);
        });

        Livewire::withQueryParams(['page' => 2])
            ->test(Index::class)
            ->assertSet('page', 2)
            ->assertSee('Second Page Brief')
            ->assertDontSee('First Page Brief')
            ->call('previousPage')
            ->assertSet('page', 1)
            ->assertSee('First Page Brief')
            ->assertDontSee('Second Page Brief')
            ->set('search', 'seo')
            ->assertSet('page', 1)
            ->assertSee('SEO Refresh Brief')
            ->assertDontSee('First Page Brief');
    }

    public function test_content_brief_review_screen_can_save_approve_and_generate_draft(): void
    {
        session($this->authenticatedSession());

        $brief = $this->briefResource([
            'id' => 14,
            'title' => 'Agent Brief',
            'status' => 'draft',
            'can_generate_draft' => false,
        ]);

        $updated = array_replace($brief, [
            'title' => 'Updated Agent Brief',
            'status' => 'rejected',
            'headings' => ['Heading One', 'Heading Two'],
        ]);

        $approved = array_replace($updated, [
            'status' => 'approved',
            'can_generate_draft' => true,
            'approved_at' => now()->toISOString(),
        ]);

        Http::fake(function (Request $request) use ($brief, $updated, $approved) {
            if ($request->method() === 'GET' && $request->url() === $this->apiBaseUrl.'/admin/content-briefs/14') {
                return Http::response(['data' => $brief], 200);
            }

            if ($request->method() === 'GET' && $request->url() === $this->apiBaseUrl.'/admin/categories') {
                return Http::response(['data' => [
                    ['id' => 3, 'name' => 'AI'],
                ]], 200);
            }

            if ($request->method() === 'GET' && $request->url() === $this->apiBaseUrl.'/admin/templates') {
                return Http::response(['data' => [
                    ['id' => 9, 'name' => 'Tutorial'],
                ]], 200);
            }

            if ($request->method() === 'PATCH' && $request->url() === $this->apiBaseUrl.'/admin/content-briefs/14') {
                $this->assertSame('Updated Agent Brief', $request['title']);
                $this->assertSame('rejected', $request['status']);
                $this->assertSame(['Heading One', 'Heading Two'], $request['headings']);

                return Http::response(['data' => $updated], 200);
            }

            if ($request->method() === 'POST' && $request->url() === $this->apiBaseUrl.'/admin/content-briefs/14/approve') {
                return Http::response(['data' => $approved], 200);
            }

            if ($request->method() === 'POST' && $request->url() === $this->apiBaseUrl.'/admin/content-briefs/14/generate-draft') {
                $this->assertSame(3, $request['category_id']);
                $this->assertSame(9, $request['template_id']);
                $this->assertSame('public', $request['visibility']);
                $this->assertSame('checklist', $request['generation_mode']);

                return Http::response(['data' => ['id' => 42, 'status' => 'queued']], 202);
            }

            return Http::response(['message' => 'Unexpected request.'], 500);
        });

        Livewire::test(Show::class, ['contentBrief' => 14])
            ->set('title', 'Updated Agent Brief')
            ->set('status', 'rejected')
            ->set('headingsText', "Heading One\nHeading Two")
            ->call('save')
            ->assertHasNoErrors()
            ->call('approve')
            ->assertSet('status', 'approved')
            ->call('openDraftDialog')
            ->set('draftCategoryId', '3')
            ->set('draftTemplateId', '9')
            ->set('draftVisibility', 'public')
            ->set('draftGenerationMode', 'checklist')
            ->call('generateDraft')
            ->assertRedirect(route('ai-jobs.show', ['aiJob' => 42]));
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

    protected function briefResource(array $overrides = []): array
    {
        return array_replace([
            'id' => 14,
            'content_topic_id' => 8,
            'title' => 'Brief title',
            'slug' => 'brief-title',
            'meta_title' => 'Brief meta title',
            'meta_description' => 'Brief meta description',
            'primary_keyword' => 'AI agents',
            'secondary_keywords' => ['agents', 'workflow'],
            'search_intent' => 'informational',
            'outline' => [['Problem', 'Angle']],
            'headings' => ['Heading One'],
            'faq_suggestions' => [['What is it?', 'Define it clearly']],
            'internal_link_suggestions' => [['/guides/agents', 'Link existing guide']],
            'image_suggestions' => [['Dashboard screenshot', 'Operations context']],
            'status' => 'draft',
            'can_generate_draft' => false,
            'approved_at' => null,
            'topic' => [
                'id' => 8,
                'title' => 'Topic title',
                'slug' => 'topic-title',
                'cluster' => 'ai_tools',
                'status' => 'approved',
            ],
            'created_at' => now()->subDay()->toISOString(),
            'updated_at' => now()->toISOString(),
        ], $overrides);
    }

    protected function paginationMeta(int $total, int $page, int $lastPage, int $from, int $to, int $perPage = 10): array
    {
        return [
            'current_page' => $page,
            'last_page' => $lastPage,
            'per_page' => $perPage,
            'total' => $total,
            'from' => $from,
            'to' => $to,
        ];
    }
}
