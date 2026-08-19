<section>
    <h1 class="text-2xl font-semibold">服务入口管理</h1>

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
            <span>入口类型</span>
            <select wire:model="type" class="rounded border px-3 py-2 text-sm">
                <option value="guide">攻略</option>
                <option value="activity">活动</option>
                <option value="hotel">酒店</option>
                <option value="flight">机票</option>
                <option value="rail">铁路</option>
                <option value="shop">购物</option>
                <option value="community">社区</option>
                <option value="exchange_rate">汇率</option>
                <option value="advertising">广告</option>
                <option value="custom">自定义</option>
            </select>
            @error('type') <span class="text-xs text-red-700">{{ $message }}</span> @enderror
        </label>
        <label class="grid gap-1 text-sm">
            <span>入口名称</span>
            <input wire:model="label" class="rounded border px-3 py-2 text-sm" placeholder="JR Pass 预约">
            @error('label') <span class="text-xs text-red-700">{{ $message }}</span> @enderror
        </label>
        <label class="grid gap-1 text-sm">
            <span>展示位置</span>
            <select wire:model="placement" class="rounded border px-3 py-2 text-sm">
                <option value="header">顶部</option>
                <option value="footer">底部</option>
            </select>
            @error('placement') <span class="text-xs text-red-700">{{ $message }}</span> @enderror
        </label>
        <label class="grid gap-1 text-sm">
            <span>排序</span>
            <input type="number" min="0" max="9999" wire:model="sort_order" class="rounded border px-3 py-2 text-sm">
            @error('sort_order') <span class="text-xs text-red-700">{{ $message }}</span> @enderror
        </label>
        <label class="grid gap-1 text-sm md:col-span-2">
            <span>链接地址</span>
            <input wire:model="url" class="rounded border px-3 py-2 text-sm" placeholder="https://example.com">
            @error('url') <span class="text-xs text-red-700">{{ $message }}</span> @enderror
        </label>
        <label class="grid gap-1 text-sm md:col-span-2">
            <span>追踪标识</span>
            <input wire:model="tracking_key" class="rounded border px-3 py-2 text-sm" placeholder="jr_pass_header">
            @error('tracking_key') <span class="text-xs text-red-700">{{ $message }}</span> @enderror
        </label>
        <label class="grid gap-1 text-sm md:col-span-4">
            <span>备注</span>
            <textarea wire:model="notes" class="rounded border px-3 py-2 text-sm" rows="3" placeholder="内部备注"></textarea>
            @error('notes') <span class="text-xs text-red-700">{{ $message }}</span> @enderror
        </label>
        <label class="flex items-center gap-2 text-sm">
            <input type="checkbox" wire:model="is_enabled">
            启用
        </label>
        <button class="rounded bg-slate-900 px-4 py-2 text-sm text-white md:col-start-4">保存服务入口</button>
    </form>

    <div class="mt-6 overflow-hidden rounded-lg border bg-white">
        <table class="min-w-full divide-y divide-slate-200 text-sm">
            <thead class="bg-slate-50 text-left text-slate-600">
                <tr>
                    <th class="px-4 py-3 font-medium">入口</th>
                    <th class="px-4 py-3 font-medium">类型</th>
                    <th class="px-4 py-3 font-medium">位置</th>
                    <th class="px-4 py-3 font-medium">状态</th>
                    <th class="px-4 py-3 font-medium">排序</th>
                    <th class="px-4 py-3 font-medium">操作</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
                @forelse($serviceLinks as $serviceLink)
                    <tr>
                        <td class="px-4 py-3">
                            <div class="font-medium">{{ $serviceLink->label }}</div>
                            <div class="text-xs text-slate-500">{{ $serviceLink->url }}</div>
                        </td>
                        <td class="px-4 py-3">{{ $serviceLink->type }}</td>
                        <td class="px-4 py-3">{{ $serviceLink->placement === 'header' ? '顶部' : '底部' }}</td>
                        <td class="px-4 py-3">{{ $serviceLink->is_enabled ? '启用' : '停用' }}</td>
                        <td class="px-4 py-3">{{ $serviceLink->sort_order }}</td>
                        <td class="px-4 py-3">
                            <div class="space-x-3">
                                <button type="button" wire:click="edit({{ $serviceLink->id }})" class="underline">编辑</button>
                                <button type="button" wire:click="delete({{ $serviceLink->id }})" wire:confirm="确认删除这个服务入口吗？" class="text-red-700 underline">删除</button>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="px-4 py-6 text-center text-slate-500">暂无服务入口</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</section>
