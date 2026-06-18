<?php

namespace Database\Factories;

use App\Models\TravelCategory;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<TravelCategory>
 */
class TravelCategoryFactory extends Factory
{
    public function definition(): array
    {
        $title = fake()->unique()->words(2, true);

        return [
            'title' => Str::title($title),
            'display_name' => Str::title($title),
            'slug' => Str::slug($title),
            'excerpt' => fake()->sentence(14),
            'body' => '<p>'.fake()->paragraph().'</p>',
            'seo_title' => Str::title($title).' Japan Travel Guides',
            'meta_description' => fake()->sentence(14),
            'is_indexable' => true,
            'is_visible' => true,
            'sort_order' => fake()->numberBetween(1, 50),
        ];
    }
}
