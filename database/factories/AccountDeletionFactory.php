<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\User;
use Carbon\Carbon;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\AccountRequest>
 */
class AccountRequestFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'type' => $this->faker->randomElement(['data', 'deletion']),
            'status' => $this->faker->randomElement(['created', 'processing', 'completed']),
            'expired' => Carbon::now()->addDays($this->faker->numberBetween(1, 30)), // Срок действия запроса
        ];
    }
}
