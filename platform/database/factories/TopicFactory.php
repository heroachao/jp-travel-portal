<?php

namespace Database\Factories;

use App\Models\Topic;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Topic>
 */
class TopicFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $title = fake()->unique()->sentence(4);

        return [
            'title' => $title,
            'slug' => Str::slug($title),
            'excerpt' => fake()->sentence(14),
            'body' => '<p>'.fake()->paragraph().'</p>',
            'seo_title' => $title.' | Japan Travel Guide',
            'meta_description' => fake()->sentence(12),
            'is_indexable' => true,
        ];
    }
}
