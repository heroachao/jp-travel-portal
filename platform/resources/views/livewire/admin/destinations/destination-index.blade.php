<section>
    <h1 class="text-2xl font-semibold">目的地管理</h1>

    @if (session('status'))
        <div class="mt-4 rounded border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-800">
            {{ session('status') }}
        </div>
    @endif

    <form wire:submit="save" class="mt-6 grid gap-3 rounded-lg border bg-white p-5 md:grid-cols-4">
        <label class="grid gap-1 text-sm">
            <span>名称</span>
            <input wire:model="name" class="rounded border px-3 py-2 text-sm" placeholder="Tokyo">
            @error('name') <span class="text-xs text-red-700">{{ $message }}</span> @enderror
        </label>
        <label class="grid gap-1 text-sm">
            <span>前台显示名</span>
            <input wire:model="display_name" class="rounded border px-3 py-2 text-sm" placeholder="Tokyo Region">
            @error('display_name') <span class="text-xs text-red-700">{{ $message }}</span> @enderror
        </label>
        <label class="grid gap-1 text-sm">
            <span>Slug</span>
            <input wire:model="slug" class="rounded border px-3 py-2 text-sm" placeholder="tokyo">
            @error('slug') <span class="text-xs text-red-700">{{ $message }}</span> @enderror
        </label>
        <label class="grid gap-1 text-sm">
            <span>上级地区</span>
            <select wire:model="parent_id" class="rounded border px-3 py-2 text-sm">
                <option value="">无上级</option>
                @foreach($parentOptions as $parentOption)
                    <option value="{{ $parentOption->id }}">{{ $parentOption->display_name ?: $parentOption->name }}</option>
                @endforeach
            </select>
            @error('parent_id') <span class="text-xs text-red-700">{{ $message }}</span> @enderror
        </label>
        <label class="grid gap-1 text-sm">
            <span>类型</span>
            <input wire:model="type" class="rounded border px-3 py-2 text-sm" placeholder="region">
            @error('type') <span class="text-xs text-red-700">{{ $message }}</span> @enderror
        </label>
        <label class="grid gap-1 text-sm">
            <span>排序</span>
            <input type="number" min="0" max="9999" wire:model="sort_order" class="rounded border px-3 py-2 text-sm">
            @error('sort_order') <span class="text-xs text-red-700">{{ $message }}</span> @enderror
        </label>
        <label class="flex items-center gap-2 text-sm">
            <input type="checkbox" wire:model="is_channel">
            作为地区频道显示
        </label>
        <label class="flex items-center gap-2 text-sm">
            <input type="checkbox" wire:model="is_indexable">
            允许索引
        </label>
        <label class="grid gap-1 text-sm md:col-span-2">
            <span>简介</span>
            <textarea wire:model="excerpt" class="rounded border px-3 py-2 text-sm" rows="3" placeholder="目的地简介"></textarea>
            @error('excerpt') <span class="text-xs text-red-700">{{ $message }}</span> @enderror
        </label>
        <label class="grid gap-1 text-sm md:col-span-2">
            <span>正文</span>
            <textarea wire:model="body" class="rounded border px-3 py-2 text-sm" rows="3" placeholder="目的地正文"></textarea>
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
        <button class="rounded bg-slate-900 px-4 py-2 text-sm text-white md:col-start-4">保存目的地</button>
    </form>

    <div class="mt-6 overflow-hidden rounded-lg border bg-white">
        <table class="min-w-full divide-y divide-slate-200 text-sm">
            <thead class="bg-slate-50 text-left text-slate-600">
                <tr>
                    <th class="px-4 py-3 font-medium">目的地</th>
                    <th class="px-4 py-3 font-medium">上级地区</th>
                    <th class="px-4 py-3 font-medium">类型</th>
                    <th class="px-4 py-3 font-medium">频道</th>
                    <th class="px-4 py-3 font-medium">索引</th>
                    <th class="px-4 py-3 font-medium">排序</th>
                    <th class="px-4 py-3 font-medium">操作</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
                @forelse($destinations as $destination)
                    <tr>
                        <td class="px-4 py-3">
                            <div class="font-medium">{{ $destination->display_name ?: $destination->name }}</div>
                            <div class="text-xs text-slate-500">{{ $destination->slug }}</div>
                        </td>
                        <td class="px-4 py-3">{{ $destination->parent ? ($destination->parent->display_name ?: $destination->parent->name) : '无' }}</td>
                        <td class="px-4 py-3">{{ $destination->type }}</td>
                        <td class="px-4 py-3">{{ $destination->is_channel ? '频道' : '普通' }}</td>
                        <td class="px-4 py-3">{{ $destination->is_indexable ? '允许' : '禁止' }}</td>
                        <td class="px-4 py-3">{{ $destination->sort_order }}</td>
                        <td class="px-4 py-3">
                            <div class="space-x-3">
                                <button type="button" wire:click="edit({{ $destination->id }})" class="underline">编辑</button>
                                <button type="button" wire:click="delete({{ $destination->id }})" wire:confirm="确认删除这个目的地吗？" class="text-red-700 underline">删除</button>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="px-4 py-6 text-center text-slate-500">暂无目的地</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</section>
