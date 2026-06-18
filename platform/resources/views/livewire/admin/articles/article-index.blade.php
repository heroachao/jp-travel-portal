<section>
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-semibold">文章管理</h1>
            <p class="mt-1 text-sm text-slate-600">管理英文旅游文章、状态和审核入口。</p>
        </div>
        <a href="{{ route('admin.articles.create') }}" class="rounded bg-slate-900 px-4 py-2 text-sm font-medium text-white">新建文章</a>
    </div>

    <div class="mt-6 flex gap-3 rounded-lg border bg-white p-4">
        <input wire:model.live.debounce.300ms="search" class="w-80 rounded border px-3 py-2 text-sm" placeholder="搜索标题">
        <select wire:model.live="status" class="rounded border px-3 py-2 text-sm">
            <option value="">全部状态</option>
            @foreach($statuses as $status)
                <option value="{{ $status->value }}">{{ $status->label() }}</option>
            @endforeach
        </select>
    </div>

    <div class="mt-6 overflow-hidden rounded-lg border bg-white">
        <table class="w-full text-left text-sm">
            <thead class="bg-slate-100 text-slate-600">
                <tr>
                    <th class="px-4 py-3">标题</th>
                    <th class="px-4 py-3">状态</th>
                    <th class="px-4 py-3">作者</th>
                    <th class="px-4 py-3">更新时间</th>
                    <th class="px-4 py-3">操作</th>
                </tr>
            </thead>
            <tbody class="divide-y">
                @forelse($articles as $article)
                    <tr>
                        <td class="px-4 py-3 font-medium">{{ $article->title }}</td>
                        <td class="px-4 py-3">{{ $article->status->label() }}</td>
                        <td class="px-4 py-3">{{ $article->author?->name }}</td>
                        <td class="px-4 py-3">{{ $article->updated_at?->format('Y-m-d H:i') }}</td>
                        <td class="space-x-3 px-4 py-3">
                            <a class="text-slate-900 underline" href="{{ route('admin.articles.edit', $article) }}">编辑</a>
                            <a class="text-slate-900 underline" href="{{ route('admin.articles.review', $article) }}">审核</a>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="px-4 py-8 text-center text-slate-500">暂无文章</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-4">
        {{ $articles->links() }}
    </div>
</section>
