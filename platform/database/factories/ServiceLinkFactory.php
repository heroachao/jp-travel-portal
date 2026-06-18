<?php

namespace Database\Factories;

use App\Models\ServiceLink;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<ServiceLink>
 */
class ServiceLinkFactory extends Factory
{
    public function definition(): array
    {
        return [
            'type' => fake()->randomElement(['guide', 'activity', 'hotel', 'flight', 'rail', 'shop', 'community', 'exchange_rate', 'advertising']),
            'label' => fake()->words(2, true),
            'url' => 'https://example.com/'.fake()->slug(),
            'placement' => fake()->randomElement(['header', 'footer']),
            'tracking_key' => fake()->unique()->slug(),
            'notes' => fake()->sentence(),
            'is_enabled' => true,
            'sort_order' => fake()->numberBetween(1, 50),
        ];
    }
}
