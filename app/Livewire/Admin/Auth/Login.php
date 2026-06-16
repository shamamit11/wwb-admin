<?php

namespace App\Livewire\Admin\Auth;

use App\Services\WideWebBlogApi\Clients\AuthClient;
use App\Services\WideWebBlogApi\Exceptions\WideWebBlogApiAuthenticationException;
use App\Services\WideWebBlogApi\Exceptions\WideWebBlogApiAuthorizationException;
use App\Services\WideWebBlogApi\Exceptions\WideWebBlogApiValidationException;
use App\Support\Auth\AdminSessionManager;
use Illuminate\Validation\ValidationException;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.guest')]
class Login extends Component
{
    public string $email = '';

    public string $password = '';

    public bool $remember = false;

    public function rules(): array
    {
        return [
            'email' => ['required', 'email'],
            'password' => ['required', 'string'],
        ];
    }

    public function updated($property): void
    {
        $this->validateOnly($property);
    }

    public function authenticate(AuthClient $authClient, AdminSessionManager $session): mixed
    {
        $validated = $this->validate();

        try {
            $loginResponse = $authClient->login(
                $validated['email'],
                $validated['password'],
                config('widewebblog.auth.device_name'),
            );

            $tokenPayload = $loginResponse['data'] ?? [];

            $session->putToken(
                $tokenPayload['token'] ?? '',
                $tokenPayload['token_type'] ?? 'Bearer',
                $this->normalizeAbilities($tokenPayload['abilities'] ?? []),
            );

            $userResponse = $authClient->adminMe(
                $session->token(),
                $session->tokenType(),
            );

            $session->putUser($userResponse['data'] ?? []);

            session()->regenerate();

            return $this->redirect(config('widewebblog.auth.home_path', '/'), navigate: true);
        } catch (WideWebBlogApiValidationException|WideWebBlogApiAuthenticationException) {
            throw ValidationException::withMessages([
                'email' => 'Invalid credentials.',
            ]);
        } catch (WideWebBlogApiAuthorizationException) {
            $session->clear();

            return $this->redirectRoute('auth.forbidden', navigate: true);
        }
    }

    public function render()
    {
        return view('livewire.admin.auth.login', [
            'title' => 'Sign In',
        ]);
    }

    protected function normalizeAbilities(mixed $abilities): array
    {
        if (is_array($abilities)) {
            return $abilities;
        }

        if (is_string($abilities) && $abilities !== '') {
            return array_values(array_filter(array_map('trim', explode(',', $abilities))));
        }

        return [];
    }
}
