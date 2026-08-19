<?php

namespace Database\Factories;

use App\Models\Article;
use App\Models\ArticleFaq;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<ArticleFaq>
 */
class ArticleFaqFactory extends Factory
{
    public function definition(): array
    {
        return [
            'article_id' => Article::factory(),
            'question' => fake()->sentence().'?',
            'answer' => '<p>'.fake()->paragraph().'</p>',
            'sort_order' => fake()->numberBetween(1, 20),
            'is_enabled' => true,
        ];
    }
}
