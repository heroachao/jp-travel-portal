<?php

namespace Database\Factories;

use App\Models\HomepageModule;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<HomepageModule>
 */
class HomepageModuleFactory extends Factory
{
    public function definition(): array
    {
        $type = fake()->randomElement(['featured_articles', 'latest_articles', 'popular_articles', 'region_grid', 'category_grid', 'service_highlights', 'travel_tools']);

        return [
            'placement_key' => $type.'-'.fake()->unique()->numberBetween(1, 9999),
            'type' => $type,
            'title' => fake()->sentence(3),
            'subtitle' => fake()->sentence(12),
            'is_enabled' => true,
            'sort_order' => fake()->numberBetween(1, 50),
        ];
    }
}
