<section>
    <h1 class="text-2xl font-semibold">广告管理</h1>
    <form wire:submit="save" class="mt-6 grid gap-3 rounded-lg border bg-white p-5 md:grid-cols-4">
        <input wire:model="key" class="rounded border px-3 py-2 text-sm" placeholder="广告位标识">
        <input wire:model="name" class="rounded border px-3 py-2 text-sm" placeholder="广告位名称">
        <input wire:model="page_type" class="rounded border px-3 py-2 text-sm" placeholder="页面类型">
        <input wire:model="position" class="rounded border px-3 py-2 text-sm" placeholder="位置">
        <textarea wire:model="code" class="rounded border px-3 py-2 font-mono text-sm md:col-span-4" rows="5" placeholder="Google AdSense 代码"></textarea>
        <label class="flex items-center gap-2 text-sm">
            <input type="checkbox" wire:model="is_enabled">
            启用
        </label>
        <button class="rounded bg-slate-900 px-4 py-2 text-sm text-white md:col-start-4">保存</button>
    </form>
    <div class="mt-6 rounded-lg border bg-white">
        @foreach($placements as $placement)
            <div class="flex items-center justify-between border-b px-4 py-3 last:border-b-0">
                <span>{{ $placement->name }} · {{ $placement->key }} · {{ $placement->is_enabled ? '启用' : '停用' }}</span>
                <div class="space-x-3 text-sm">
                    <button wire:click="edit({{ $placement->id }})" class="underline">编辑</button>
                    <button wire:click="delete({{ $placement->id }})" class="text-red-700 underline">删除</button>
                </div>
            </div>
        @endforeach
    </div>
</section>
