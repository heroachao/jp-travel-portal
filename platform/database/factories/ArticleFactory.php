<?php

namespace Database\Factories;

use App\Enums\ArticleStatus;
use App\Models\Article;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Article>
 */
class ArticleFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $title = fake()->sentence(5);

        return [
            'author_id' => User::factory(),
            'title' => $title,
            'slug' => Str::slug($title).'-'.fake()->unique()->numberBetween(100, 99999),
            'excerpt' => fake()->paragraph(),
            'body' => '<p>'.fake()->paragraph().'</p>',
            'status' => ArticleStatus::Draft,
            'published_at' => null,
            'scheduled_for' => null,
            'seo_title' => $title.' | Japan Travel Guide',
            'meta_description' => fake()->sentence(12),
            'is_indexable' => true,
            'structured_data_type' => 'Article',
        ];
    }
}
