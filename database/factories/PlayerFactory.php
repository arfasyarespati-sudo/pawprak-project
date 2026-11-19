<?php

namespace Database\Factories;

use App\Models\Player;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Player>
 */
class PlayerFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'nickname' => fake()->userName(),
            'avatar' => null,
            'guest_token_hash' => null,
        ];
    }

    /**
     * Indicate that the player is a guest.
     */
    public function guest(): static
    {
        return $this->state(fn (): array => [
            'guest_token_hash' => hash('sha256', fake()->uuid()),
        ]);
    }
}
