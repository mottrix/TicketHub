<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

/**
 * Manages the session-based login/logout routes, not token or API authentication.
 */
class AuthenticatedSessionController extends Controller
{
    /**
     * Render the login form; the POST action below performs authentication.
     */
    public function create(): View
    {
        return view('Auth.login');
    }

    /**
     * Authenticate the submitted credentials and replace the session ID.
     *
     * A failed attempt returns an email-field error without authenticating;
     * successful login regenerates the session to prevent reusing its old ID.
     * The login route rate limits repeated attempts.
     */
    public function store(Request $request): RedirectResponse
    {
        $credentials = $request->validate([
            'email' => ['required', 'string', 'email', 'max:255'],
            'password' => ['required', 'string', 'max:4096'],
        ]);
        if (! Auth::attempt($credentials)) {
            return back()->withErrors([
                'email' => 'The provided credentials do not match our records.',
            ]);
        }

        $request->session()->regenerate();

        return redirect()->route('index');

    }

    /**
     * End login, discard session data, and rotate the CSRF token before redirecting.
     */
    public function destroy(Request $request): RedirectResponse
    {
        Auth::logout();

        $request->session()->invalidate();

        $request->session()->regenerateToken();

        return redirect()->route('login');
    }
}
