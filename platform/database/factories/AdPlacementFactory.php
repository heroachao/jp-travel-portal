<?php

namespace Database\Factories;

use App\Models\AdPlacement;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<AdPlacement>
 */
class AdPlacementFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'key' => 'article.body.middle.'.fake()->unique()->numberBetween(1, 99999),
            'name' => '文章正文中段广告',
            'page_type' => 'article',
            'position' => 'body_middle',
            'code' => '<ins class="adsbygoogle"></ins>',
            'is_enabled' => false,
            'notes' => 'Demo placement.',
        ];
    }
}
