<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Link>
 */
class LinkFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'custom_name' => $this->faker->optional()->bothify('??-???-??'),
            'destination' => $this->faker->url(),
            'short_name' => $this->faker->unique()->regexify('[A-Za-z0-9]{7}'),
            'available' => $this->faker->boolean(90),
        ];
    }
}
