<section>
    <h1 class="text-2xl font-semibold">标签管理</h1>
    <form wire:submit="save" class="mt-6 grid gap-3 rounded-lg border bg-white p-5 md:grid-cols-3">
        <input wire:model="name" class="rounded border px-3 py-2 text-sm" placeholder="标签名">
        <input wire:model="slug" class="rounded border px-3 py-2 text-sm" placeholder="slug">
        <button class="rounded bg-slate-900 px-4 py-2 text-sm text-white">保存</button>
        <textarea wire:model="description" class="rounded border px-3 py-2 text-sm md:col-span-3" placeholder="说明"></textarea>
    </form>
    <div class="mt-6 rounded-lg border bg-white">
        @foreach($tags as $tag)
            <div class="flex items-center justify-between border-b px-4 py-3 last:border-b-0">
                <span>{{ $tag->name }} · {{ $tag->slug }}</span>
                <div class="space-x-3 text-sm">
                    <button wire:click="edit({{ $tag->id }})" class="underline">编辑</button>
                    <button wire:click="delete({{ $tag->id }})" class="text-red-700 underline">删除</button>
                </div>
            </div>
        @endforeach
    </div>
</section>
