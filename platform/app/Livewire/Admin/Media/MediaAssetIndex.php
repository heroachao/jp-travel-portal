<?php

namespace App\Livewire\Admin\Media;

use App\Models\Article;
use App\Models\Destination;
use App\Models\MediaAsset;
use App\Models\Topic;
use App\Services\Media\SafeImageUpload;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Livewire\Component;
use Livewire\Features\SupportFileUploads\TemporaryUploadedFile;
use Livewire\WithFileUploads;
use Livewire\WithPagination;

class MediaAssetIndex extends Component
{
    use WithFileUploads;
    use WithPagination;

    public ?TemporaryUploadedFile $file = null;

    public ?string $alt_text = null;

    public ?string $source_note = null;

    public string $search = '';

    public function updatedSearch(): void
    {
        $this->resetPage();
    }

    public function upload(SafeImageUpload $uploader): void
    {
        abort_unless(auth()->user()?->can('admin.access'), 403);

        $this->normalizeNullableStrings();

        $this->validate([
            'file' => ['required', 'file', 'mimes:jpg,jpeg,png,webp', 'max:4096'],
            'alt_text' => ['nullable', 'string', 'max:255'],
            'source_note' => ['nullable', 'string', 'max:1000'],
        ]);

        $uploader->store($this->file, auth()->user(), $this->alt_text, $this->source_note);

        $this->reset(['file', 'alt_text', 'source_note']);
        $this->resetPage();
        session()->flash('status', '图片已上传');
    }

    public function delete(int $id): void
    {
        abort_unless(auth()->user()?->can('admin.access'), 403);

        $fileToDelete = DB::transaction(function () use ($id): ?array {
            $asset = MediaAsset::query()
                ->whereKey($id)
                ->lockForUpdate()
                ->firstOrFail();

            if ($this->isReferenced($asset)) {
                return null;
            }

            $fileToDelete = [
                'disk' => $asset->disk,
                'path' => $asset->path,
            ];

            $asset->delete();

            return $fileToDelete;
        });

        if ($fileToDelete === null) {
            session()->flash('error', '图片正在被内容使用，不能删除');

            return;
        }

        Storage::disk($fileToDelete['disk'])->delete($fileToDelete['path']);
        session()->flash('status', '图片已删除');
    }

    public function render(): View
    {
        $search = trim($this->search);

        $assets = MediaAsset::query()
            ->with('uploader')
            ->when($search !== '', function ($query) use ($search): void {
                $query->where(function ($query) use ($search): void {
                    $query
                        ->where('path', 'like', "%{$search}%")
                        ->orWhere('mime_type', 'like', "%{$search}%")
                        ->orWhere('alt_text', 'like', "%{$search}%");
                });
            })
            ->latest()
            ->paginate(24);

        return view('livewire.admin.media.media-asset-index', [
            'assets' => $assets,
        ])->layout('layouts.admin', ['title' => '媒体库']);
    }

    private function normalizeNullableStrings(): void
    {
        foreach (['alt_text', 'source_note'] as $field) {
            if ($this->{$field} !== null && trim($this->{$field}) === '') {
                $this->{$field} = null;
            }
        }
    }

    private function isReferenced(MediaAsset $asset): bool
    {
        return Article::withTrashed()
            ->where('cover_media_id', $asset->id)
            ->orWhere('og_media_id', $asset->id)
            ->exists()
            || Topic::withTrashed()
                ->where('cover_media_id', $asset->id)
                ->exists()
            || Destination::withTrashed()
                ->where('cover_media_id', $asset->id)
                ->exists();
    }
}
