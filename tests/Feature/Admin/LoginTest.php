<?php

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;

uses(RefreshDatabase::class);

test('login page is accessible to guests', function () {
    $this->get('/admin/login')->assertStatus(200);
});

test('authenticated users are redirected away from login page', function () {
    $user = User::factory()->create();

    $this->actingAs($user)->get('/admin/login')->assertRedirect();
});

test('admin routes require authentication', function () {
    $this->get('/admin/countries')->assertRedirect('/admin/login');
    $this->get('/admin')->assertRedirect('/admin/login');
});

test('login component shows authentication form', function () {
    Livewire::test('admin.login')
        ->assertSee('Se connecter');
});

test('login fails with invalid credentials', function () {
    User::factory()->create(['email' => 'admin@test.com', 'password' => bcrypt('correct')]);

    Livewire::test('admin.login')
        ->set('email', 'admin@test.com')
        ->set('password', 'wrong')
        ->call('authenticate')
        ->assertHasErrors('email');
});

test('login succeeds with valid credentials', function () {
    User::factory()->create(['email' => 'admin@test.com', 'password' => bcrypt('password')]);

    Livewire::test('admin.login')
        ->set('email', 'admin@test.com')
        ->set('password', 'password')
        ->call('authenticate')
        ->assertRedirect(route('admin.countries'));
});

test('logout redirects to login page', function () {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->post('/admin/logout')
        ->assertRedirect(route('login'));
});
