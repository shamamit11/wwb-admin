<?php

namespace App\Livewire\Admin\Password;

use App\Services\WideWebBlogApi\Clients\AuthClient;
use App\Services\WideWebBlogApi\Exceptions\WideWebBlogApiAuthenticationException;
use App\Services\WideWebBlogApi\Exceptions\WideWebBlogApiAuthorizationException;
use App\Services\WideWebBlogApi\Exceptions\WideWebBlogApiException;
use App\Services\WideWebBlogApi\Exceptions\WideWebBlogApiValidationException;
use App\Support\Auth\AdminSessionManager;
use Illuminate\Validation\ValidationException;
use Livewire\Component;

class Index extends Component
{
    public string $currentPassword = '';

    public string $password = '';

    public string $passwordConfirmation = '';

    public ?string $formError = null;

    public function rules(): array
    {
        return [
            'currentPassword' => ['required', 'string'],
            'password' => ['required', 'string'],
            'passwordConfirmation' => ['required', 'string', 'same:password'],
        ];
    }

    public function updated(string $property): void
    {
        if (in_array($property, ['currentPassword', 'password', 'passwordConfirmation'], true)) {
            $this->validateOnly($property);
        }
    }

    public function save(AuthClient $auth, AdminSessionManager $session): mixed
    {
        $validated = $this->validate();
        $this->formError = null;

        try {
            $auth->changePassword($this->token($session), $session->tokenType(), [
                'current_password' => $validated['currentPassword'],
                'password' => $validated['password'],
                'password_confirmation' => $validated['passwordConfirmation'],
            ]);

            $this->reset(['currentPassword', 'password', 'passwordConfirmation']);
            $this->resetValidation();
            session()->flash('status', 'Admin password changed.');

            return null;
        } catch (WideWebBlogApiValidationException $exception) {
            $this->formError = $exception->getMessage();

            throw ValidationException::withMessages($this->normalizeApiErrors($exception->errors()));
        } catch (WideWebBlogApiAuthenticationException) {
            return $this->expireSession($session);
        } catch (WideWebBlogApiAuthorizationException) {
            return $this->forbidden($session);
        } catch (WideWebBlogApiException $exception) {
            $this->formError = $exception->getMessage() ?: 'The password could not be changed.';

            return null;
        }
    }

    public function render()
    {
        return view('livewire.admin.password.index')
            ->layout('layouts.admin', [
                'title' => 'Admin Password',
            ]);
    }

    protected function normalizeApiErrors(array $errors): array
    {
        $mapped = [];

        foreach ($errors as $field => $messages) {
            $property = match ($field) {
                'current_password' => 'currentPassword',
                'password_confirmation' => 'passwordConfirmation',
                default => $field,
            };

            $mapped[$property] = $messages;
        }

        return $mapped;
    }

    protected function token(AdminSessionManager $session): string
    {
        return $session->token() ?? '';
    }

    protected function expireSession(AdminSessionManager $session): mixed
    {
        $session->clear();
        request()->session()->invalidate();
        request()->session()->regenerateToken();
        session()->flash('auth.error', 'Your session has expired. Please sign in again.');

        return $this->redirectRoute('login', navigate: true);
    }

    protected function forbidden(AdminSessionManager $session): mixed
    {
        $session->clear();
        session()->flash('auth.error', 'Your account is not authorized for the admin panel.');

        return $this->redirectRoute('auth.forbidden', navigate: true);
    }
}
