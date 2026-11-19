<?php

namespace Tests\Feature\Auth;

use App\Models\Player;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RegistrationTest extends TestCase
{
    use RefreshDatabase;

    public function test_register_page_can_be_rendered(): void
    {
        $response = $this->get('/register');

        $response->assertStatus(200);
        $response->assertViewIs('auth.register');
    }

    public function test_register_with_valid_data(): void
    {
        $response = $this->postJson('/api/auth/register', [
            'nickname' => 'TestPlayer',
            'email' => 'test@example.com',
            'password' => 'Password123',
        ]);

        $response->assertStatus(201)
            ->assertJsonStructure(['message', 'redirect']);

        $this->assertDatabaseHas('users', ['email' => 'test@example.com']);
        $this->assertDatabaseHas('players', ['nickname' => 'TestPlayer']);

        $user = User::where('email', 'test@example.com')->first();
        $this->assertNotNull($user->player_id);
        $this->assertAuthenticatedAs($user);
    }

    public function test_register_creates_player_and_user_in_transaction(): void
    {
        $this->postJson('/api/auth/register', [
            'nickname' => 'TransPlayer',
            'email' => 'trans@example.com',
            'password' => 'Password123',
        ]);

        $player = Player::where('nickname', 'TransPlayer')->first();
        $user = User::where('email', 'trans@example.com')->first();

        $this->assertNotNull($player);
        $this->assertNotNull($user);
        $this->assertEquals($player->id, $user->player_id);
    }

    public function test_register_with_duplicate_email(): void
    {
        User::factory()->create(['email' => 'taken@example.com']);

        $response = $this->postJson('/api/auth/register', [
            'nickname' => 'AnotherUser',
            'email' => 'taken@example.com',
            'password' => 'Password123',
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['email']);
    }

    public function test_register_with_invalid_email(): void
    {
        $response = $this->postJson('/api/auth/register', [
            'nickname' => 'TestPlayer',
            'email' => 'not-an-email',
            'password' => 'Password123',
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['email']);
    }

    public function test_register_without_nickname(): void
    {
        $response = $this->postJson('/api/auth/register', [
            'nickname' => '',
            'email' => 'test@example.com',
            'password' => 'Password123',
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['nickname']);
    }

    public function test_register_with_weak_password(): void
    {
        $response = $this->postJson('/api/auth/register', [
            'nickname' => 'TestPlayer',
            'email' => 'test@example.com',
            'password' => 'short',
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['password']);
    }

    public function test_register_with_password_missing_numbers(): void
    {
        $response = $this->postJson('/api/auth/register', [
            'nickname' => 'TestPlayer',
            'email' => 'test@example.com',
            'password' => 'PasswordOnly',
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['password']);
    }

    public function test_authenticated_user_redirected_from_register(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get('/register');

        $response->assertRedirect('/');
    }
}
