<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LoginTest extends TestCase
{
    use RefreshDatabase;

    public function test_login_page_can_be_rendered(): void
    {
        $response = $this->get('/login');

        $response->assertStatus(200);
        $response->assertViewIs('auth.login');
    }

    public function test_login_with_valid_credentials(): void
    {
        $user = User::factory()->create([
            'email' => 'valid@example.com',
            'password' => 'Password123',
        ]);

        $response = $this->postJson('/api/auth/login', [
            'email' => 'valid@example.com',
            'password' => 'Password123',
        ]);

        $response->assertStatus(200)
            ->assertJsonStructure(['message', 'redirect']);

        $this->assertAuthenticatedAs($user);
    }

    public function test_login_with_wrong_password(): void
    {
        User::factory()->create([
            'email' => 'valid@example.com',
            'password' => 'Password123',
        ]);

        $response = $this->postJson('/api/auth/login', [
            'email' => 'valid@example.com',
            'password' => 'WrongPassword',
        ]);

        $response->assertStatus(422)
            ->assertJson(['message' => 'Email atau password salah.']);

        $this->assertGuest();
    }

    public function test_login_with_unregistered_email(): void
    {
        $response = $this->postJson('/api/auth/login', [
            'email' => 'nobody@example.com',
            'password' => 'Password123',
        ]);

        $response->assertStatus(422);
        $this->assertGuest();
    }

    public function test_login_with_empty_fields(): void
    {
        $response = $this->postJson('/api/auth/login', [
            'email' => '',
            'password' => '',
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['email', 'password']);
    }

    public function test_login_with_remember_me(): void
    {
        $user = User::factory()->create([
            'email' => 'remember@example.com',
            'password' => 'Password123',
        ]);

        $response = $this->postJson('/api/auth/login', [
            'email' => 'remember@example.com',
            'password' => 'Password123',
            'remember' => true,
        ]);

        $response->assertStatus(200);
        $this->assertAuthenticatedAs($user);

        // Verify that the remember token was set on the user
        $user->refresh();
        $this->assertNotNull($user->remember_token);
    }

    public function test_authenticated_user_redirected_from_login(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get('/login');

        $response->assertRedirect('/');
    }
}
