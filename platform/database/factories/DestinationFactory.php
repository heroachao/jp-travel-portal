<?php

namespace Database\Factories;

use App\Models\Destination;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Destination>
 */
class DestinationFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $name = fake()->unique()->city();

        return [
            'type' => 'city',
            'name' => $name,
            'slug' => Str::slug($name),
            'excerpt' => fake()->sentence(14),
            'body' => '<p>'.fake()->paragraph().'</p>',
            'latitude' => fake()->latitude(24, 46),
            'longitude' => fake()->longitude(123, 146),
            'seo_title' => $name.' Travel Guide',
            'meta_description' => fake()->sentence(12),
            'is_indexable' => true,
        ];
    }
}
