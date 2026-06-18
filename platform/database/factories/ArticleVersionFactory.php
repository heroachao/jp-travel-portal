<?php

namespace Database\Factories;

use App\Models\ArticleVersion;
use App\Models\Article;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<ArticleVersion>
 */
class ArticleVersionFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'article_id' => Article::factory(),
            'user_id' => User::factory(),
            'event' => 'saved',
            'snapshot' => ['title' => fake()->sentence(5)],
        ];
    }
}
