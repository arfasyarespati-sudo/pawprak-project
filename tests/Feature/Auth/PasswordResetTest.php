<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Password;
use Tests\TestCase;

class PasswordResetTest extends TestCase
{
    use RefreshDatabase;

    public function test_forgot_password_can_request_reset_link(): void
    {
        $user = User::factory()->create(['email' => 'resetme@example.com']);

        $response = $this->postJson('/api/auth/forgot-password', [
            'email' => 'resetme@example.com',
        ]);

        $response->assertStatus(200)
            ->assertJson([
                'message' => 'Link reset password telah dikirim ke email Anda.',
            ]);
    }

    public function test_forgot_password_fails_with_invalid_email(): void
    {
        $response = $this->postJson('/api/auth/forgot-password', [
            'email' => 'invalid-email',
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['email']);
    }

    public function test_forgot_password_fails_for_nonexistent_user(): void
    {
        $response = $this->postJson('/api/auth/forgot-password', [
            'email' => 'notfound@example.com',
        ]);

        $response->assertStatus(422)
            ->assertJsonStructure(['message', 'errors']);
    }

    public function test_password_can_be_reset(): void
    {
        $user = User::factory()->create(['email' => 'change@example.com']);
        $token = Password::createToken($user);

        $response = $this->postJson('/api/auth/reset-password', [
            'token' => $token,
            'email' => 'change@example.com',
            'password' => 'NewPassword123',
            'password_confirmation' => 'NewPassword123',
        ]);

        $response->assertStatus(200)
            ->assertJson([
                'message' => 'Password berhasil direset.',
            ]);
    }
}
