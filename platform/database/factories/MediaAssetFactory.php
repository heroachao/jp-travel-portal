<?php

namespace Database\Factories;

use App\Models\MediaAsset;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<MediaAsset>
 */
class MediaAssetFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'uploaded_by' => User::factory(),
            'disk' => 'public',
            'path' => 'media/2026/06/example.jpg',
            'mime_type' => 'image/jpeg',
            'size' => 128000,
            'width' => 1600,
            'height' => 900,
            'alt_text' => fake()->sentence(6),
            'source_note' => 'Demo image metadata.',
        ];
    }
}
