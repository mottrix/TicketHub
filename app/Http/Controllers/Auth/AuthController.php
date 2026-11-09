<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    public function create()
    {
        return view('Auth.register');

    }

    public function store(Request $request)
    {

        $validated = $request->validate([
            'name' => ['required'],
            'email' => ['email', 'required', 'unique:users,email'],
            'password' => ['required', 'min:4', 'max:10'],
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

        // dd(User::all());

    }
}
