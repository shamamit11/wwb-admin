<?php

namespace Tests\Feature\ContactSubmissions;

use App\Livewire\Admin\ContactSubmissions\Show;
use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Http;
use Livewire\Livewire;
use Tests\TestCase;

class ContactSubmissionsShowTest extends TestCase
{
    protected string $apiBaseUrl;

    protected function setUp(): void
    {
        parent::setUp();
        $this->apiBaseUrl = rtrim(config('widewebblog.api.base_url'), '/');
    }

    public function test_contact_submission_detail_loads_full_submission_context(): void
    {
        Http::fake([
            $this->apiBaseUrl.'/admin/contact-submissions/sub_1' => Http::response([
                'data' => $this->submissionResource(),
            ], 200),
        ]);

        $response = $this->withSession($this->authenticatedSession())
            ->get(route('contact-submissions.show', ['contactSubmission' => 'sub_1']));

        $response
            ->assertOk()
            ->assertSee('Contact Submission')
            ->assertSee('Amit Sharma')
            ->assertSee('We would like to discuss a partnership.')
            ->assertSee('affiliate')
            ->assertSee('Admin reviewed initial message.');
    }

    public function test_contact_submission_detail_can_patch_status_and_admin_notes(): void
    {
        session($this->authenticatedSession());

        Http::fake(function (Request $request) {
            if ($request->method() === 'GET' && $request->url() === $this->apiBaseUrl.'/admin/contact-submissions/sub_1') {
                return Http::response(['data' => $this->submissionResource()], 200);
            }

            if ($request->method() === 'PATCH' && $request->url() === $this->apiBaseUrl.'/admin/contact-submissions/sub_1') {
                $this->assertSame('read', $request['status']);
                $this->assertSame('Handled by the partnerships inbox.', $request['admin_notes']);
                $this->assertSame(['source' => 'affiliate', 'campaign' => 'summer-2026'], $request['metadata']);

                return Http::response([
                    'data' => array_replace($this->submissionResource(), [
                        'status' => 'read',
                        'admin_notes' => 'Handled by the partnerships inbox.',
                        'reviewed_at' => '2026-06-20T12:30:00Z',
                        'reviewed_by' => ['id' => '1', 'name' => 'Admin User'],
                        'updated_at' => '2026-06-20T12:30:00Z',
                    ]),
                ], 200);
            }

            return Http::response(['message' => 'Unexpected request.'], 500);
        });

        Livewire::test(Show::class, ['contactSubmission' => 'sub_1'])
            ->set('status', 'read')
            ->set('admin_notes', 'Handled by the partnerships inbox.')
            ->call('save')
            ->assertHasNoErrors()
            ->assertSet('status', 'read')
            ->assertSet('admin_notes', 'Handled by the partnerships inbox.')
            ->assertSet('reviewed_by', ['id' => '1', 'name' => 'Admin User']);
    }

    public function test_contact_submission_detail_maps_api_validation_errors(): void
    {
        session($this->authenticatedSession());

        Http::fake(function (Request $request) {
            if ($request->method() === 'GET' && $request->url() === $this->apiBaseUrl.'/admin/contact-submissions/sub_1') {
                return Http::response(['data' => $this->submissionResource()], 200);
            }

            if ($request->method() === 'PATCH' && $request->url() === $this->apiBaseUrl.'/admin/contact-submissions/sub_1') {
                return Http::response([
                    'message' => 'The given data was invalid.',
                    'errors' => [
                        'status' => ['The selected status is invalid.'],
                        'admin_notes' => ['The admin notes field must be a string.'],
                    ],
                ], 422);
            }

            return Http::response(['message' => 'Unexpected request.'], 500);
        });

        Livewire::test(Show::class, ['contactSubmission' => 'sub_1'])
            ->set('status', 'read')
            ->set('admin_notes', 'Temporary note')
            ->call('save')
            ->assertHasErrors(['status', 'admin_notes'])
            ->assertSee('The given data was invalid.')
            ->assertSee('The selected status is invalid.')
            ->assertSee('The admin notes field must be a string.');
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

    protected function submissionResource(): array
    {
        return [
            'id' => 'sub_1',
            'name' => 'Amit Sharma',
            'email' => 'amit@example.com',
            'topic' => 'Partnership',
            'message' => 'We would like to discuss a partnership.',
            'status' => 'new',
            'admin_notes' => 'Admin reviewed initial message.',
            'metadata' => [
                'source' => 'affiliate',
                'campaign' => 'summer-2026',
            ],
            'submitted_at' => '2026-06-20T11:00:00Z',
            'reviewed_at' => null,
            'reviewed_by' => null,
            'created_at' => '2026-06-20T11:00:00Z',
            'updated_at' => '2026-06-20T11:00:00Z',
        ];
    }
}
