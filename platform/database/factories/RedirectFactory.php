<?php

namespace Database\Factories;

use App\Models\Redirect;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Redirect>
 */
class RedirectFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'from_path' => '/old/'.fake()->unique()->slug(),
            'to_path' => '/articles/'.fake()->slug(),
            'status_code' => 301,
        ];
    }
}
