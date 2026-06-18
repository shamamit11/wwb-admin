<?php

namespace Tests\Feature\ContentBriefs;

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
        Http::fake([
            $this->apiBaseUrl.'/admin/content-briefs*' => Http::response([
                'data' => [
                    $this->briefResource(['id' => 14, 'title' => 'Agent Brief', 'status' => 'draft']),
                    $this->briefResource(['id' => 15, 'title' => 'SEO Brief', 'status' => 'approved']),
                ],
            ], 200),
        ]);

        $response = $this->withSession($this->authenticatedSession())
            ->get(route('content-briefs.index'));

        $response
            ->assertOk()
            ->assertSee('Content Briefs')
            ->assertSee('Agent Brief')
            ->assertSee('SEO Brief')
            ->assertSee('Draft Briefs')
            ->assertSee('Approved Briefs');
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
}
