<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Closure;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

/**
 * Registers ordinary users, signs them in, and starts a fresh authenticated session.
 *
 * The database supplies the default user role; submitted role values are not
 * included in User::create(), so registration cannot grant admin privileges.
 */
class AuthController extends Controller
{
    /**
     * Render the public registration form used by tickethub-register.js.
     */
    public function create(): View
    {
        return view('Auth.register');

    }

    /**
     * Validate registration fields, hash the password, and log in the new account.
     *
     * Confirm passwords on the server and enforce bcrypt's byte limit before hashing.
     */
    public function store(Request $request): RedirectResponse
    {

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users,email'],
            'password' => [
                'bail', 'required', 'string', 'min:8', 'confirmed',
                function (string $attribute, string $value, Closure $fail): void {
                    if (strlen($value) > 72) {
                        $fail('The password must not exceed 72 bytes.');
                    } elseif (str_contains($value, "\0")) {
                        $fail('The password must not contain null characters.');
                    }
                },
            ],
        ]);
        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),

        ]);
        Auth::login($user);
        $request->session()->regenerate();

        return redirect()->route('index')
            ->with('success', 'User created successfully.');

    }
}
