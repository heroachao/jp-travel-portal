<section>
    <div class="flex items-center justify-between">
        <h1 class="text-2xl font-semibold">{{ $articleId ? '编辑文章' : '新建文章' }}</h1>
        <a href="{{ route('admin.articles.index') }}" class="text-sm underline">返回文章列表</a>
    </div>

    @if(session('status'))
        <p class="mt-4 rounded border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-800">{{ session('status') }}</p>
    @endif

    <form wire:submit="save" class="mt-6 grid gap-5 rounded-lg border bg-white p-6">
        <div>
            <label class="block text-sm font-medium" for="title">英文标题</label>
            <input id="title" wire:model="title" class="mt-2 w-full rounded border px-3 py-2">
            @error('title')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
        </div>

        <div>
            <label class="block text-sm font-medium" for="slug">URL Slug</label>
            <input id="slug" wire:model="slug" class="mt-2 w-full rounded border px-3 py-2">
            @error('slug')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
        </div>

        <div>
            <label class="block text-sm font-medium" for="excerpt">摘要</label>
            <textarea id="excerpt" wire:model="excerpt" rows="3" class="mt-2 w-full rounded border px-3 py-2"></textarea>
            @error('excerpt')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
        </div>

        <div>
            <label class="block text-sm font-medium" for="body">正文 HTML</label>
            <textarea id="body" wire:model="body" rows="12" class="mt-2 w-full rounded border px-3 py-2 font-mono text-sm"></textarea>
            @error('body')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
        </div>

        <div class="grid gap-5 border-t pt-5 md:grid-cols-2">
            <div>
                <label class="block text-sm font-medium" for="seo_title">SEO 标题</label>
                <input id="seo_title" wire:model="seo_title" class="mt-2 w-full rounded border px-3 py-2">
                @error('seo_title')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
            </div>
            <div>
                <label class="block text-sm font-medium" for="canonical_url">Canonical URL</label>
                <input id="canonical_url" wire:model="canonical_url" class="mt-2 w-full rounded border px-3 py-2">
                @error('canonical_url')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
            </div>
        </div>

        <div>
            <label class="block text-sm font-medium" for="meta_description">Meta Description</label>
            <textarea id="meta_description" wire:model="meta_description" rows="3" class="mt-2 w-full rounded border px-3 py-2"></textarea>
            @error('meta_description')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
        </div>

        <label class="flex items-center gap-2 text-sm">
            <input type="checkbox" wire:model="is_indexable">
            允许搜索引擎索引
        </label>

        <div class="flex gap-3">
            <button type="submit" class="rounded bg-slate-900 px-5 py-2 text-sm font-medium text-white">保存</button>
            @if($articleId)
                <a href="{{ route('admin.articles.review', $articleId) }}" class="rounded border px-5 py-2 text-sm font-medium">进入审核</a>
            @endif
        </div>
    </form>
</section>
