<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\LinkHistory>
 */
class LinkHistoryFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'ip_address' => $this->faker->ipv4(),
            'user_agent' => $this->faker->userAgent(),
            'country_name' => $this->faker->country(),
            'browser' => $this->faker->randomElement([
                'Google Chrome',
                'Mozilla Firefox',
                'Safari',
                'Microsoft Edge',
                'Opera',
                'Brave',
                'Internet Explorer',
                'Vivaldi',
            ]),
            'os' => $this->faker->randomElement(['Windows', 'macOS', 'Linux', 'Android', 'iOS']),
        ];
    }
}
