<?php

namespace App\Http\Controllers\Admin\Auth;

use App\Http\Controllers\Controller;
use App\Services\WideWebBlogApi\Clients\AuthClient;
use App\Support\Auth\AdminSessionManager;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Throwable;

class LogoutController extends Controller
{
    public function __invoke(
        Request $request,
        AuthClient $authClient,
        AdminSessionManager $session,
    ): RedirectResponse {
        if ($session->hasToken()) {
            try {
                $authClient->logout($session->token(), $session->tokenType());
            } catch (Throwable) {
                // Clear the local session even if the remote logout call fails.
            }
        }

        $session->clear();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login');
    }
}
