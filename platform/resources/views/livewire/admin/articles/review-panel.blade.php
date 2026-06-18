<section>
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-semibold">审核文章</h1>
            <p class="mt-1 text-sm text-slate-600">{{ $article->title }} · {{ $article->status->label() }}</p>
        </div>
        <a href="{{ route('admin.articles.edit', $article) }}" class="text-sm underline">返回编辑</a>
    </div>

    @if(session('status'))
        <p class="mt-4 rounded border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-800">{{ session('status') }}</p>
    @endif

    <div class="mt-6 grid gap-5 lg:grid-cols-[1fr_320px]">
        <article class="rounded-lg border bg-white p-6">
            <h2 class="text-xl font-semibold">{{ $article->title }}</h2>
            <p class="mt-3 text-slate-600">{{ $article->excerpt }}</p>
            <div class="mt-5 border-t pt-5 text-sm text-slate-700">
                {!! $article->body !!}
            </div>
        </article>

        <aside class="rounded-lg border bg-white p-6">
            <h2 class="font-semibold">审核操作</h2>
            <div class="mt-4 grid gap-3">
                <button wire:click="submitForReview" class="rounded border px-4 py-2 text-sm font-medium">提交审核</button>
                <button wire:click="publish" class="rounded bg-slate-900 px-4 py-2 text-sm font-medium text-white">立即发布</button>
            </div>

            <div class="mt-6 border-t pt-5">
                <label class="block text-sm font-medium" for="scheduledFor">定时发布时间</label>
                <input id="scheduledFor" type="datetime-local" wire:model="scheduledFor" class="mt-2 w-full rounded border px-3 py-2 text-sm">
                @error('scheduledFor')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                <button wire:click="schedule" class="mt-3 w-full rounded border px-4 py-2 text-sm font-medium">定时发布</button>
            </div>

            <div class="mt-6 border-t pt-5">
                <label class="block text-sm font-medium" for="rejectionReason">退回原因</label>
                <textarea id="rejectionReason" wire:model="rejectionReason" rows="4" class="mt-2 w-full rounded border px-3 py-2 text-sm"></textarea>
                @error('rejectionReason')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                <button wire:click="reject" class="mt-3 w-full rounded border border-red-300 px-4 py-2 text-sm font-medium text-red-700">退回修改</button>
            </div>
        </aside>
    </div>
</section>
