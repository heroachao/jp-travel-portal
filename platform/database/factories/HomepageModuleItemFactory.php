<?php

namespace Database\Factories;

use App\Models\Article;
use App\Models\HomepageModule;
use App\Models\HomepageModuleItem;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<HomepageModuleItem>
 */
class HomepageModuleItemFactory extends Factory
{
    public function definition(): array
    {
        return [
            'homepage_module_id' => HomepageModule::factory(),
            'item_type' => Article::class,
            'item_id' => Article::factory(),
            'label' => fake()->sentence(3),
            'summary' => fake()->sentence(12),
            'sort_order' => fake()->numberBetween(1, 50),
            'is_enabled' => true,
        ];
    }
}
