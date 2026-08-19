<section>
    <div class="flex items-start justify-between gap-4">
        <div>
            <h1 class="text-2xl font-semibold">媒体库</h1>
            <p class="mt-1 text-sm text-slate-600">上传和管理后台内容使用的安全图片素材。</p>
        </div>
    </div>

    @if(session('status'))
        <p class="mt-4 rounded border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-800">{{ session('status') }}</p>
    @endif

    @if(session('error'))
        <p class="mt-4 rounded border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700">{{ session('error') }}</p>
    @endif

    <form wire:submit="upload" class="mt-6 grid gap-5 rounded-lg border bg-white p-6">
        <div class="grid gap-5 md:grid-cols-3">
            <div>
                <label class="block text-sm font-medium" for="media_file">图片文件</label>
                <input id="media_file" type="file" wire:model="file" accept=".jpg,.jpeg,.png,.webp,image/jpeg,image/png,image/webp" class="mt-2 w-full rounded border px-3 py-2 text-sm">
                @error('file')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
            </div>
            <div>
                <label class="block text-sm font-medium" for="media_alt_text">Alt 文本</label>
                <input id="media_alt_text" wire:model="alt_text" class="mt-2 w-full rounded border px-3 py-2 text-sm" placeholder="图片替代文本">
                @error('alt_text')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
            </div>
            <div>
                <label class="block text-sm font-medium" for="media_source_note">公开来源/授权说明</label>
                <input id="media_source_note" wire:model="source_note" class="mt-2 w-full rounded border px-3 py-2 text-sm" placeholder="公开署名、版权方或授权说明">
                @error('source_note')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
            </div>
        </div>

        <div>
            <button type="submit" class="rounded bg-slate-900 px-5 py-2 text-sm font-medium text-white" wire:loading.attr="disabled">
                上传图片
            </button>
        </div>
    </form>

    <div class="mt-6 rounded-lg border bg-white p-4">
        <input wire:model.live.debounce.300ms="search" class="w-full rounded border px-3 py-2 text-sm md:w-96" placeholder="搜索路径、类型或 Alt">
    </div>

    <div class="mt-6 grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
        @forelse($assets as $asset)
            <article class="overflow-hidden rounded-lg border bg-white">
                <div class="aspect-[4/3] bg-slate-100">
                    <img src="{{ Storage::disk($asset->disk)->url($asset->path) }}" alt="{{ $asset->alt_text ?? $asset->path }}" class="h-full w-full object-cover">
                </div>
                <div class="space-y-3 p-4 text-sm">
                    <div>
                        <p class="font-medium text-slate-900">{{ $asset->alt_text ?: '未填写 Alt' }}</p>
                        <p class="mt-1 break-all font-mono text-xs text-slate-500">{{ $asset->path }}</p>
                    </div>

                    <dl class="grid grid-cols-2 gap-2 text-xs text-slate-600">
                        <div>
                            <dt class="font-medium text-slate-500">类型</dt>
                            <dd>{{ $asset->mime_type }}</dd>
                        </div>
                        <div>
                            <dt class="font-medium text-slate-500">大小</dt>
                            <dd>{{ number_format($asset->size / 1024, 1) }} KB</dd>
                        </div>
                        <div>
                            <dt class="font-medium text-slate-500">尺寸</dt>
                            <dd>{{ $asset->width && $asset->height ? $asset->width.' x '.$asset->height : '未知尺寸' }}</dd>
                        </div>
                        <div>
                            <dt class="font-medium text-slate-500">上传者</dt>
                            <dd>{{ $asset->uploader?->name ?? '未知' }}</dd>
                        </div>
                    </dl>

                    @if($asset->source_note)
                        <p class="text-xs text-slate-500">{{ $asset->source_note }}</p>
                    @endif

                    <button type="button" wire:click="delete({{ $asset->id }})" class="text-sm font-medium text-red-700 underline">
                        删除
                    </button>
                </div>
            </article>
        @empty
            <div class="rounded-lg border bg-white p-8 text-center text-sm text-slate-500 sm:col-span-2 xl:col-span-4">
                暂无图片素材
            </div>
        @endforelse
    </div>

    <div class="mt-4">
        {{ $assets->links() }}
    </div>
</section>
