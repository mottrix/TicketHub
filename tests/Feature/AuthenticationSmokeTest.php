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

test('registration remains renderable after rejecting array input', function () {
    $this->from(route('register'))->post(route('register.post'), [
        'name' => ['invalid'], 'email' => ['invalid'],
        'password' => 'valid-password-123', 'password_confirmation' => 'valid-password-123',
    ])->assertSessionHasErrors(['name', 'email']);

    $this->get(route('register'))->assertOk();
});

test('logout ends authentication and protects the dashboard', function () {
    $user = User::factory()->create();

    $this->actingAs($user)->post(route('logout.destroy'))->assertRedirect(route('login'));

    $this->assertGuest();
    $this->get(route('index'))->assertRedirect(route('login'));
});

test('registration rejects invalid fields without creating an account', function (array $invalid, string $field) {
    $payload = array_replace([
        'name' => 'New customer', 'email' => 'new@example.test',
        'password' => 'valid-password-123', 'password_confirmation' => 'valid-password-123',
    ], $invalid);

    $this->post(route('register.post'), $payload)->assertSessionHasErrors($field);

    $this->assertDatabaseCount('users', 0);
    $this->assertGuest();
})->with([
    'name must be text' => [['name' => ['invalid']], 'name'],
    'name is bounded' => [['name' => str_repeat('a', 256)], 'name'],
    'password must be text' => [['password' => ['invalid']], 'password'],
    'short password' => [['password' => 'short', 'password_confirmation' => 'short'], 'password'],
    'confirmation required' => [['password_confirmation' => null], 'password'],
    'confirmation must match' => [['password_confirmation' => 'different-password'], 'password'],
    'bcrypt byte limit' => [['password' => str_repeat('é', 37), 'password_confirmation' => str_repeat('é', 37)], 'password'],
    'bcrypt null character' => [['password' => 'abcdefgh'.chr(0).'x', 'password_confirmation' => 'abcdefgh'.chr(0).'x'], 'password'],
]);

test('registration accepts a confirmed password longer than ten characters', function () {
    $this->post(route('register.post'), [
        'name' => 'New customer', 'email' => 'new@example.test',
        'password' => 'a-longer-password-123', 'password_confirmation' => 'a-longer-password-123',
    ])->assertRedirect(route('index'));

    $this->assertDatabaseHas('users', ['email' => 'new@example.test', 'role' => 'user']);
});

test('repeated failed logins are rate limited', function () {
    $user = User::factory()->create();

    for ($attempt = 0; $attempt < 5; $attempt++) {
        $this->post(route('login.post'), ['email' => $user->email, 'password' => 'incorrect'])->assertSessionHasErrors('email');
    }

    $this->post(route('login.post'), ['email' => $user->email, 'password' => 'incorrect'])->assertTooManyRequests();
    $this->assertGuest();
});
