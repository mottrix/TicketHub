<?php

use App\Models\User;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

uses(LazilyRefreshDatabase::class);

beforeEach(function () {
    $this->withoutVite();
});

test('registration creates an ordinary authenticated user and opens the dashboard', function () {
    $password = Str::random(8);

    $this->post(route('register.post'), [
        'name' => 'Deployment smoke test',
        'email' => 'deployment-smoke@example.test',
        'password' => $password,
        'password_confirmation' => $password,
        'role' => 'admin',
    ])->assertRedirect(route('index'));

    $user = User::where('email', 'deployment-smoke@example.test')->firstOrFail();
    $this->assertAuthenticatedAs($user);
    expect($user->role)->toBe('user');
    expect(Hash::check($password, $user->password))->toBeTrue();

    $this->get(route('index'))->assertOk();
});

test('login authenticates an existing account', function () {
    $password = Str::random(8);
    $user = User::factory()->create(['password' => Hash::make($password)]);

    $this->post(route('login.post'), [
        'email' => $user->email,
        'password' => $password,
    ])->assertRedirect(route('index'));

    $this->assertAuthenticatedAs($user);
});

test('login rejects an incorrect password', function () {
    $user = User::factory()->create();

    $this->from(route('login'))->post(route('login.post'), [
        'email' => $user->email,
        'password' => Str::random(12),
    ])->assertRedirect(route('login'))->assertSessionHasErrors('email');

    $this->assertGuest();
});

test('logout ends authentication and protects the dashboard', function () {
    $user = User::factory()->create();

    $this->actingAs($user)->post(route('logout.destroy'))->assertRedirect(route('login'));

    $this->assertGuest();
    $this->get(route('index'))->assertRedirect(route('login'));
});
