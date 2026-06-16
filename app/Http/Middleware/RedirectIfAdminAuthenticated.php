<?php

namespace App\Http\Middleware;

use App\Support\Auth\AdminSessionManager;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class RedirectIfAdminAuthenticated
{
    public function __construct(
        protected AdminSessionManager $session,
    ) {
    }

    public function handle(Request $request, Closure $next): Response
    {
        if ($this->session->hasToken()) {
            return redirect(config('widewebblog.auth.home_path', '/'));
        }

        return $next($request);
    }
}
