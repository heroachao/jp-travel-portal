<section>
    <h1 class="text-2xl font-semibold">广告管理</h1>
    <p class="mt-2 text-sm text-slate-600">广告代码只允许管理员维护，建议先保存为停用状态，上线确认后再启用。</p>

    @if (session('status'))
        <div class="mt-4 rounded border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-800">
            {{ session('status') }}
        </div>
    @endif

    <form wire:submit="save" class="mt-6 grid gap-3 rounded-lg border bg-white p-5 md:grid-cols-4">
        <label class="grid gap-1 text-sm">
            <span>广告位标识</span>
            <input wire:model="key" class="rounded border px-3 py-2 text-sm" placeholder="article-body-middle">
            @error('key') <span class="text-xs text-red-700">{{ $message }}</span> @enderror
        </label>
        <label class="grid gap-1 text-sm">
            <span>广告位名称</span>
            <input wire:model="name" class="rounded border px-3 py-2 text-sm" placeholder="文章正文中段广告">
            @error('name') <span class="text-xs text-red-700">{{ $message }}</span> @enderror
        </label>
        <label class="grid gap-1 text-sm">
            <span>页面类型</span>
            <input wire:model="page_type" class="rounded border px-3 py-2 text-sm" placeholder="article">
            @error('page_type') <span class="text-xs text-red-700">{{ $message }}</span> @enderror
        </label>
        <label class="grid gap-1 text-sm">
            <span>位置</span>
            <input wire:model="position" class="rounded border px-3 py-2 text-sm" placeholder="body_middle">
            @error('position') <span class="text-xs text-red-700">{{ $message }}</span> @enderror
        </label>
        <label class="grid gap-1 text-sm md:col-span-4">
            <span>广告代码</span>
            <textarea wire:model="code" class="rounded border px-3 py-2 font-mono text-sm" rows="5" placeholder="Google AdSense 代码"></textarea>
            @error('code') <span class="text-xs text-red-700">{{ $message }}</span> @enderror
        </label>
        <label class="grid gap-1 text-sm md:col-span-4">
            <span>内部备注</span>
            <textarea wire:model="notes" class="rounded border px-3 py-2 text-sm" rows="3" placeholder="内部备注，例如投放说明、尺寸、页面位置"></textarea>
            @error('notes') <span class="text-xs text-red-700">{{ $message }}</span> @enderror
        </label>
        <label class="flex items-center gap-2 text-sm">
            <input type="checkbox" wire:model="is_enabled">
            启用
            @error('is_enabled') <span class="text-xs text-red-700">{{ $message }}</span> @enderror
        </label>
        <button class="rounded bg-slate-900 px-4 py-2 text-sm text-white md:col-start-4">保存</button>
    </form>
    <div class="mt-6 overflow-hidden rounded-lg border bg-white">
        <table class="min-w-full divide-y divide-slate-200 text-sm">
            <thead class="bg-slate-50 text-left text-slate-600">
                <tr>
                    <th class="px-4 py-3 font-medium">广告位</th>
                    <th class="px-4 py-3 font-medium">页面类型</th>
                    <th class="px-4 py-3 font-medium">位置</th>
                    <th class="px-4 py-3 font-medium">备注</th>
                    <th class="px-4 py-3 font-medium">状态</th>
                    <th class="px-4 py-3 font-medium">操作</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
                @forelse($placements as $placement)
                    <tr>
                        <td class="px-4 py-3">
                            <div class="font-medium">{{ $placement->name }}</div>
                            <div class="text-xs text-slate-500">{{ $placement->key }}</div>
                        </td>
                        <td class="px-4 py-3">{{ $placement->page_type }}</td>
                        <td class="px-4 py-3">{{ $placement->position }}</td>
                        <td class="px-4 py-3 text-slate-600">{{ $placement->notes ?: '—' }}</td>
                        <td class="px-4 py-3">{{ $placement->is_enabled ? '启用' : '停用' }}</td>
                        <td class="px-4 py-3">
                            <div class="space-x-3">
                                <button type="button" wire:click="edit({{ $placement->id }})" class="underline">编辑</button>
                                <button type="button" wire:click="delete({{ $placement->id }})" wire:confirm="确认删除这个广告位吗？" class="text-red-700 underline">删除</button>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="px-4 py-6 text-center text-slate-500">暂无广告位</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</section>
