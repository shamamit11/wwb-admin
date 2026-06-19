<?php

namespace Tests\Feature\Auth;

use App\Livewire\Admin\Password\Index;
use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Http;
use Livewire\Livewire;
use Tests\TestCase;

class AdminPasswordTest extends TestCase
{
    protected string $apiBaseUrl;

    protected function setUp(): void
    {
        parent::setUp();

        $this->apiBaseUrl = rtrim(config('widewebblog.api.base_url'), '/');
    }

    public function test_authenticated_admin_can_open_password_page(): void
    {
        $response = $this->withSession($this->authenticatedSession())
            ->get(route('password.index'));

        $response
            ->assertOk()
            ->assertSee('Admin Password')
            ->assertSee('Update Password');
    }

    public function test_admin_password_can_be_changed_through_service_api(): void
    {
        session($this->authenticatedSession());

        Http::fake(function (Request $request) {
            if ($request->method() === 'POST' && $request->url() === $this->apiBaseUrl.'/admin/change-password') {
                $this->assertSame('old-secret', $request['current_password']);
                $this->assertSame('new-secret-value', $request['password']);
                $this->assertSame('new-secret-value', $request['password_confirmation']);

                return Http::response([
                    'data' => [
                        'password_changed' => true,
                    ],
                ], 200);
            }

            return Http::response(['message' => 'Unexpected request.'], 500);
        });

        Livewire::test(Index::class)
            ->set('currentPassword', 'old-secret')
            ->set('password', 'new-secret-value')
            ->set('passwordConfirmation', 'new-secret-value')
            ->call('save')
            ->assertHasNoErrors()
            ->assertSet('currentPassword', '')
            ->assertSet('password', '')
            ->assertSet('passwordConfirmation', '');
    }

    public function test_admin_password_maps_api_validation_errors(): void
    {
        session($this->authenticatedSession());

        Http::fake(function (Request $request) {
            if ($request->method() === 'POST' && $request->url() === $this->apiBaseUrl.'/admin/change-password') {
                return Http::response([
                    'message' => 'The given data was invalid.',
                    'errors' => [
                        'current_password' => ['The current password is incorrect.'],
                        'password' => ['The password field must be at least 12 characters.'],
                    ],
                ], 422);
            }

            return Http::response(['message' => 'Unexpected request.'], 500);
        });

        Livewire::test(Index::class)
            ->set('currentPassword', 'wrong-secret')
            ->set('password', 'short')
            ->set('passwordConfirmation', 'short')
            ->call('save')
            ->assertHasErrors(['currentPassword', 'password'])
            ->assertSee('The given data was invalid.')
            ->assertSee('The current password is incorrect.')
            ->assertSee('The password field must be at least 12 characters.');
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
}
