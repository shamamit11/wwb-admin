<?php

namespace App\Http\Middleware;

use App\Services\WideWebBlogApi\Clients\AuthClient;
use App\Services\WideWebBlogApi\Exceptions\WideWebBlogApiAuthenticationException;
use App\Services\WideWebBlogApi\Exceptions\WideWebBlogApiAuthorizationException;
use App\Support\Auth\AdminSessionManager;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureAdminAuthenticated
{
    public function __construct(
        protected AdminSessionManager $session,
        protected AuthClient $authClient,
    ) {
    }

    public function handle(Request $request, Closure $next): Response
    {
        if (! $this->session->hasToken()) {
            return redirect()->route('login');
        }

        if (! $this->session->user()) {
            try {
                $userResponse = $this->authClient->adminMe(
                    $this->session->token(),
                    $this->session->tokenType(),
                );

                $this->session->putUser($userResponse['data'] ?? []);
            } catch (WideWebBlogApiAuthenticationException) {
                $this->session->clear();

                $request->session()->invalidate();
                $request->session()->regenerateToken();

                return redirect()
                    ->route('login')
                    ->with('auth.error', 'Your session has expired. Please sign in again.');
            } catch (WideWebBlogApiAuthorizationException) {
                $this->session->clear();

                return redirect()
                    ->route('auth.forbidden')
                    ->with('auth.error', 'Your account is not authorized for the admin panel.');
            }
        }

        return $next($request);
    }
}
