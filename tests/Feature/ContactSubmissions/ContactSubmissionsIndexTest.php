<?php

namespace Tests\Feature\ContactSubmissions;

use App\Livewire\Admin\ContactSubmissions\Index;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class ContactSubmissionsIndexTest extends TestCase
{
    protected string $apiBaseUrl;

    protected function setUp(): void
    {
        parent::setUp();
        $this->apiBaseUrl = rtrim(config('widewebblog.api.base_url'), '/');
    }

    public function test_contact_submissions_index_loads_service_backed_rows(): void
    {
        Http::fake([
            $this->apiBaseUrl.'/admin/contact-submissions' => Http::response([
                'data' => [
                    $this->submissionResource(['id' => 'sub_1', 'name' => 'Amit Sharma', 'topic' => 'Partnership']),
                    $this->submissionResource(['id' => 'sub_2', 'name' => 'Nina Vermeer', 'topic' => 'Editorial Question']),
                ],
            ], 200),
        ]);

        $response = $this->withSession($this->authenticatedSession())
            ->get(route('contact-submissions.index'));

        $response
            ->assertOk()
            ->assertSee('Contact Submissions')
            ->assertSee('Amit Sharma')
            ->assertSee('Partnership')
            ->assertSee('Nina Vermeer');
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

    protected function submissionResource(array $overrides = []): array
    {
        return array_replace([
            'id' => 'sub_1',
            'name' => 'Reader Name',
            'email' => 'reader@example.com',
            'topic' => 'General',
            'message' => 'Hello from a reader.',
            'status' => 'new',
            'admin_notes' => null,
            'metadata' => ['origin' => 'contact-form'],
            'submitted_at' => '2026-06-20T10:00:00Z',
            'reviewed_at' => null,
            'reviewed_by' => null,
            'created_at' => '2026-06-20T10:00:00Z',
            'updated_at' => '2026-06-20T10:00:00Z',
        ], $overrides);
    }
}
