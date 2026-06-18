<section>
    <h1 class="text-2xl font-semibold">首页模块管理</h1>

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
            <span>模块位置键</span>
            <input wire:model="placement_key" class="rounded border px-3 py-2 text-sm" placeholder="home_featured">
            @error('placement_key') <span class="text-xs text-red-700">{{ $message }}</span> @enderror
        </label>
        <label class="grid gap-1 text-sm">
            <span>模块类型</span>
            <select wire:model="type" class="rounded border px-3 py-2 text-sm">
                <option value="featured_articles">精选文章</option>
                <option value="latest_articles">最新文章</option>
                <option value="popular_articles">热门文章</option>
                <option value="region_grid">地区宫格</option>
                <option value="category_grid">分类宫格</option>
                <option value="service_highlights">服务推荐</option>
                <option value="travel_tools">旅行工具</option>
            </select>
            @error('type') <span class="text-xs text-red-700">{{ $message }}</span> @enderror
        </label>
        <label class="grid gap-1 text-sm">
            <span>模块标题</span>
            <input wire:model="title" class="rounded border px-3 py-2 text-sm" placeholder="本周推荐">
            @error('title') <span class="text-xs text-red-700">{{ $message }}</span> @enderror
        </label>
        <label class="grid gap-1 text-sm">
            <span>排序</span>
            <input type="number" min="0" max="9999" wire:model="sort_order" class="rounded border px-3 py-2 text-sm">
            @error('sort_order') <span class="text-xs text-red-700">{{ $message }}</span> @enderror
        </label>
        <label class="grid gap-1 text-sm md:col-span-4">
            <span>副标题</span>
            <textarea wire:model="subtitle" class="rounded border px-3 py-2 text-sm" rows="3" placeholder="模块副标题"></textarea>
            @error('subtitle') <span class="text-xs text-red-700">{{ $message }}</span> @enderror
        </label>
        <label class="flex items-center gap-2 text-sm">
            <input type="checkbox" wire:model="is_enabled">
            启用
        </label>
        <button class="rounded bg-slate-900 px-4 py-2 text-sm text-white md:col-start-4">保存首页模块</button>
    </form>

    <div class="mt-6 overflow-hidden rounded-lg border bg-white">
        <table class="min-w-full divide-y divide-slate-200 text-sm">
            <thead class="bg-slate-50 text-left text-slate-600">
                <tr>
                    <th class="px-4 py-3 font-medium">模块</th>
                    <th class="px-4 py-3 font-medium">类型</th>
                    <th class="px-4 py-3 font-medium">状态</th>
                    <th class="px-4 py-3 font-medium">排序</th>
                    <th class="px-4 py-3 font-medium">操作</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
                @forelse($modules as $module)
                    <tr>
                        <td class="px-4 py-3">
                            <div class="font-medium">{{ $module->title }}</div>
                            <div class="text-xs text-slate-500">{{ $module->placement_key }}</div>
                        </td>
                        <td class="px-4 py-3">{{ $module->type }}</td>
                        <td class="px-4 py-3">{{ $module->is_enabled ? '启用' : '停用' }}</td>
                        <td class="px-4 py-3">{{ $module->sort_order }}</td>
                        <td class="px-4 py-3">
                            <div class="space-x-3">
                                <button type="button" wire:click="edit({{ $module->id }})" class="underline">编辑</button>
                                <button type="button" wire:click="delete({{ $module->id }})" wire:confirm="确认删除这个首页模块吗？" class="text-red-700 underline">删除</button>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="px-4 py-6 text-center text-slate-500">暂无首页模块</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</section>
