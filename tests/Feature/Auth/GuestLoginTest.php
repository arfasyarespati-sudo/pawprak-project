<?php

namespace Tests\Feature\Auth;

use App\Models\Player;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class GuestLoginTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_login_creates_player(): void
    {
        $response = $this->postJson('/api/auth/guest');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'message',
                'player' => ['id', 'nickname'],
                'guest_token',
                'redirect',
            ]);

        $this->assertDatabaseCount('players', 1);

        $player = Player::first();
        $this->assertNotNull($player->guest_token_hash);
        $this->assertStringStartsWith('Tamu_', $player->nickname);
    }

    public function test_guest_login_returns_unique_token(): void
    {
        $response1 = $this->postJson('/api/auth/guest');
        $response2 = $this->postJson('/api/auth/guest');

        $token1 = $response1->json('guest_token');
        $token2 = $response2->json('guest_token');

        $this->assertNotEquals($token1, $token2);
    }

    public function test_guest_token_is_hashed_in_database(): void
    {
        $response = $this->postJson('/api/auth/guest');

        $guestToken = $response->json('guest_token');
        $player = Player::first();

        // The raw token should NOT be stored in the database
        $this->assertNotEquals($guestToken, $player->guest_token_hash);

        // The hash of the token should match what's stored
        $this->assertEquals(hash('sha256', $guestToken), $player->guest_token_hash);
    }

    public function test_guest_token_can_be_validated(): void
    {
        $loginResponse = $this->postJson('/api/auth/guest');
        $guestToken = $loginResponse->json('guest_token');

        // Store token in session via same session context
        $response = $this->withSession([
            'guest_token' => $guestToken,
            'is_guest' => true,
        ])->postJson('/api/auth/guest/validate');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'player' => ['id', 'nickname', 'is_guest'],
            ]);
    }

    public function test_guest_validate_without_token_fails(): void
    {
        $response = $this->postJson('/api/auth/guest/validate');

        $response->assertStatus(401);
    }

    public function test_guest_logout_clears_session(): void
    {
        $loginResponse = $this->postJson('/api/auth/guest');

        $response = $this->withSession([
            'guest_token' => $loginResponse->json('guest_token'),
            'is_guest' => true,
        ])->postJson('/api/auth/guest/logout');

        $response->assertStatus(200)
            ->assertJson(['message' => 'Logout tamu berhasil.']);
    }
}
