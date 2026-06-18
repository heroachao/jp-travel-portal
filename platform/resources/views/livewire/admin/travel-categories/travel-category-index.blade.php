<section>
    <h1 class="text-2xl font-semibold">分类频道管理</h1>

    @if (session('status'))
        <div class="mt-4 rounded border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-800">
            {{ session('status') }}
        </div>
    @endif
    @error('delete')
        <div class="mt-4 rounded border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-800">
            {{ $message }}
        </div>
    @enderror

    <form wire:submit="save" class="mt-6 grid gap-3 rounded-lg border bg-white p-5 md:grid-cols-4">
        <label class="grid gap-1 text-sm">
            <span>频道标题</span>
            <input wire:model="title" class="rounded border px-3 py-2 text-sm" placeholder="东京赏樱">
            @error('title') <span class="text-xs text-red-700">{{ $message }}</span> @enderror
        </label>
        <label class="grid gap-1 text-sm">
            <span>展示名称</span>
            <input wire:model="display_name" class="rounded border px-3 py-2 text-sm" placeholder="东京樱花">
            @error('display_name') <span class="text-xs text-red-700">{{ $message }}</span> @enderror
        </label>
        <label class="grid gap-1 text-sm">
            <span>Slug</span>
            <input wire:model="slug" class="rounded border px-3 py-2 text-sm" placeholder="tokyo-sakura">
            @error('slug') <span class="text-xs text-red-700">{{ $message }}</span> @enderror
        </label>
        <label class="grid gap-1 text-sm">
            <span>父级频道</span>
            <select wire:model="parent_id" class="rounded border px-3 py-2 text-sm">
                <option value="">无父级</option>
                @foreach($parentOptions as $parentOption)
                    <option value="{{ $parentOption->id }}">{{ $parentOption->title }}</option>
                @endforeach
            </select>
            @error('parent_id') <span class="text-xs text-red-700">{{ $message }}</span> @enderror
        </label>
        <label class="grid gap-1 text-sm md:col-span-2">
            <span>简介</span>
            <textarea wire:model="excerpt" class="rounded border px-3 py-2 text-sm" rows="3" placeholder="频道简介"></textarea>
            @error('excerpt') <span class="text-xs text-red-700">{{ $message }}</span> @enderror
        </label>
        <label class="grid gap-1 text-sm md:col-span-2">
            <span>正文</span>
            <textarea wire:model="body" class="rounded border px-3 py-2 text-sm" rows="3" placeholder="频道正文"></textarea>
            @error('body') <span class="text-xs text-red-700">{{ $message }}</span> @enderror
        </label>
        <label class="grid gap-1 text-sm md:col-span-2">
            <span>SEO 标题</span>
            <input wire:model="seo_title" class="rounded border px-3 py-2 text-sm" placeholder="搜索标题">
            @error('seo_title') <span class="text-xs text-red-700">{{ $message }}</span> @enderror
        </label>
        <label class="grid gap-1 text-sm md:col-span-2">
            <span>Meta 描述</span>
            <input wire:model="meta_description" class="rounded border px-3 py-2 text-sm" placeholder="搜索描述">
            @error('meta_description') <span class="text-xs text-red-700">{{ $message }}</span> @enderror
        </label>
        <label class="grid gap-1 text-sm">
            <span>排序</span>
            <input type="number" min="0" max="9999" wire:model="sort_order" class="rounded border px-3 py-2 text-sm">
            @error('sort_order') <span class="text-xs text-red-700">{{ $message }}</span> @enderror
        </label>
        <label class="flex items-center gap-2 text-sm">
            <input type="checkbox" wire:model="is_indexable">
            允许索引
        </label>
        <label class="flex items-center gap-2 text-sm">
            <input type="checkbox" wire:model="is_visible">
            前台可见
        </label>
        <button class="rounded bg-slate-900 px-4 py-2 text-sm text-white md:col-start-4">保存分类频道</button>
    </form>

    <div class="mt-6 overflow-hidden rounded-lg border bg-white">
        <table class="min-w-full divide-y divide-slate-200 text-sm">
            <thead class="bg-slate-50 text-left text-slate-600">
                <tr>
                    <th class="px-4 py-3 font-medium">频道</th>
                    <th class="px-4 py-3 font-medium">父级</th>
                    <th class="px-4 py-3 font-medium">状态</th>
                    <th class="px-4 py-3 font-medium">排序</th>
                    <th class="px-4 py-3 font-medium">操作</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
                @forelse($categories as $category)
                    <tr>
                        <td class="px-4 py-3">
                            <div class="font-medium">{{ $category->title }}</div>
                            <div class="text-xs text-slate-500">{{ $category->slug }}</div>
                        </td>
                        <td class="px-4 py-3">{{ $category->parent?->title ?? '无' }}</td>
                        <td class="px-4 py-3">
                            {{ $category->is_visible ? '可见' : '隐藏' }} · {{ $category->is_indexable ? '可索引' : '不索引' }}
                        </td>
                        <td class="px-4 py-3">{{ $category->sort_order }}</td>
                        <td class="px-4 py-3">
                            <div class="space-x-3">
                                <button type="button" wire:click="edit({{ $category->id }})" class="underline">编辑</button>
                                <button type="button" wire:click="delete({{ $category->id }})" wire:confirm="确认删除这个分类频道吗？" class="text-red-700 underline">删除</button>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="px-4 py-6 text-center text-slate-500">暂无分类频道</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</section>
