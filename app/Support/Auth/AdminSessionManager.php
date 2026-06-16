<?php

namespace App\Support\Auth;

use Illuminate\Contracts\Session\Session;

class AdminSessionManager
{
    public function __construct(
        protected Session $session,
    ) {
    }

    public function token(): ?string
    {
        return $this->session->get(config('widewebblog.session.token_key'));
    }

    public function tokenType(): ?string
    {
        return $this->session->get(config('widewebblog.session.token_type_key'));
    }

    public function abilities(): array
    {
        return $this->session->get(config('widewebblog.session.abilities_key'), []);
    }

    public function user(): ?array
    {
        return $this->session->get(config('widewebblog.session.user_key'));
    }

    public function hasToken(): bool
    {
        return filled($this->token());
    }

    public function putToken(string $token, ?string $tokenType = 'Bearer', array $abilities = []): void
    {
        $this->session->put(config('widewebblog.session.token_key'), $token);
        $this->session->put(config('widewebblog.session.token_type_key'), $tokenType ?: 'Bearer');
        $this->session->put(config('widewebblog.session.abilities_key'), $abilities);
    }

    public function putUser(array $user): void
    {
        $this->session->put(config('widewebblog.session.user_key'), $user);
    }

    public function clear(): void
    {
        $this->session->forget([
            config('widewebblog.session.token_key'),
            config('widewebblog.session.token_type_key'),
            config('widewebblog.session.abilities_key'),
            config('widewebblog.session.user_key'),
        ]);
    }
}
