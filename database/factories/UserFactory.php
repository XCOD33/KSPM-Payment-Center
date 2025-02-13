<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;
use Ramsey\Uuid\Uuid;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\User>
 */
class UserFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => fake()->name(),
            'member_id' => fake()->unique()->randomNumber(9, true),
            'nim' => fake()->unique()->numerify('##########'),
            'uuid' => Uuid::uuid4()->toString(),
            'password' => bcrypt('password'), // password
            'year' => fake()->year(),
            'remember_token' => Str::random(10),
            'email' => fake()->email(),
            'phone' => '08' . fake()->numerify('##########'),
        ];
    }

    /**
     * Indicate that the model's email address should be unverified.
     */
    // public function unverified(): static
    // {
    //     return $this->state(fn (array $attributes) => [
    //         'email_verified_at' => null,
    //     ]);
    // }
}
