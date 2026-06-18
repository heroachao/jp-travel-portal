<?php

namespace Tests\Feature\Admin;

use App\Models\User;
use App\Services\Media\SafeImageUpload;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;

class MediaUploadTest extends TestCase
{
    use RefreshDatabase;

    public function test_safe_image_upload_accepts_jpg_images(): void
    {
        Storage::fake('public');
        $user = User::factory()->create();

        $asset = app(SafeImageUpload::class)->store(
            UploadedFile::fake()->image('kyoto.jpg', 1200, 800),
            $user,
            'Kyoto temple approach',
            'Local demo image'
        );

        Storage::disk('public')->assertExists($asset->path);
        $this->assertSame('image/jpeg', $asset->mime_type);
        $this->assertSame('Kyoto temple approach', $asset->alt_text);
    }

    public function test_safe_image_upload_rejects_non_images(): void
    {
        $this->expectException(ValidationException::class);

        app(SafeImageUpload::class)->store(
            UploadedFile::fake()->create('payload.php', 1, 'text/plain'),
            User::factory()->create(),
            null,
            null
        );
    }

    public function test_safe_image_upload_rejects_images_that_cannot_be_parsed(): void
    {
        Storage::fake('public');
        $user = User::factory()->create();

        try {
            app(SafeImageUpload::class)->store(
                UploadedFile::fake()->create('fake.jpg', 1, 'image/jpeg'),
                $user,
                'Broken image',
                null
            );

            $this->fail('Expected image dimension validation to fail.');
        } catch (ValidationException $exception) {
            $this->assertArrayHasKey('file', $exception->errors());
        }

        $this->assertDatabaseMissing('media_assets', [
            'alt_text' => 'Broken image',
        ]);
        Storage::disk('public')->assertMissing('media');
    }
}
