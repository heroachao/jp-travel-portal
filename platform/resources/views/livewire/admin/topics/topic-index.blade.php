<section>
    <h1 class="text-2xl font-semibold">专题管理</h1>
    <form wire:submit="save" class="mt-6 grid gap-3 rounded-lg border bg-white p-5 md:grid-cols-3">
        <input wire:model="title" class="rounded border px-3 py-2 text-sm" placeholder="专题标题">
        <input wire:model="slug" class="rounded border px-3 py-2 text-sm" placeholder="slug">
        <button class="rounded bg-slate-900 px-4 py-2 text-sm text-white">保存</button>
        <textarea wire:model="excerpt" class="rounded border px-3 py-2 text-sm md:col-span-3" placeholder="简介"></textarea>
    </form>
    <div class="mt-6 rounded-lg border bg-white">
        @foreach($topics as $topic)
            <div class="flex items-center justify-between border-b px-4 py-3 last:border-b-0">
                <span>{{ $topic->title }} · {{ $topic->slug }}</span>
                <div class="space-x-3 text-sm">
                    <button wire:click="edit({{ $topic->id }})" class="underline">编辑</button>
                    <button wire:click="delete({{ $topic->id }})" class="text-red-700 underline">删除</button>
                </div>
            </div>
        @endforeach
    </div>
</section>
