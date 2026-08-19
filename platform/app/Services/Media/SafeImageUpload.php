<?php

namespace App\Services\Media;

use App\Models\MediaAsset;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Validation\ValidationException;

class SafeImageUpload
{
    private const ALLOWED_MIMES = ['image/jpeg', 'image/png', 'image/webp'];

    private const MAX_BYTES = 4_194_304;

    public function store(UploadedFile $file, User $user, ?string $altText, ?string $sourceNote): MediaAsset
    {
        if (! in_array($file->getMimeType(), self::ALLOWED_MIMES, true)) {
            throw ValidationException::withMessages(['file' => '仅允许 JPG、PNG、WebP 图片。']);
        }

        if ($file->getSize() > self::MAX_BYTES) {
            throw ValidationException::withMessages(['file' => '图片大小不能超过 4MB。']);
        }

        $dimensions = @getimagesize($file->getRealPath());

        if ($dimensions === false) {
            throw ValidationException::withMessages(['file' => '无法读取图片尺寸，请上传有效图片。']);
        }

        $path = $file->store('media/'.now()->format('Y/m'), 'public');

        return MediaAsset::create([
            'uploaded_by' => $user->id,
            'disk' => 'public',
            'path' => $path,
            'mime_type' => $file->getMimeType(),
            'size' => $file->getSize(),
            'width' => $dimensions[0],
            'height' => $dimensions[1],
            'alt_text' => $altText,
            'source_note' => $sourceNote,
        ]);
    }
}
