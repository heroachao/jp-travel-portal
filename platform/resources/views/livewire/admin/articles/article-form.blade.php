<section>
    <div class="flex items-center justify-between">
        <h1 class="text-2xl font-semibold">{{ $articleId ? '编辑文章' : '新建文章' }}</h1>
        <a href="{{ route('admin.articles.index') }}" class="text-sm underline">返回文章列表</a>
    </div>

    @if(session('status'))
        <p class="mt-4 rounded border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-800">{{ session('status') }}</p>
    @endif

    <form wire:submit="save" class="mt-6 grid gap-5 rounded-lg border bg-white p-6">
        <div>
            <label class="block text-sm font-medium" for="title">英文标题</label>
            <input id="title" wire:model="title" class="mt-2 w-full rounded border px-3 py-2">
            @error('title')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
        </div>

        <div>
            <label class="block text-sm font-medium" for="slug">URL Slug</label>
            <input id="slug" wire:model="slug" class="mt-2 w-full rounded border px-3 py-2">
            @error('slug')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
        </div>

        <div>
            <label class="block text-sm font-medium" for="excerpt">摘要</label>
            <textarea id="excerpt" wire:model="excerpt" rows="3" class="mt-2 w-full rounded border px-3 py-2"></textarea>
            @error('excerpt')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
        </div>

        <div>
            <label class="block text-sm font-medium" for="body">正文 HTML</label>
            <textarea id="body" wire:model="body" rows="12" class="mt-2 w-full rounded border px-3 py-2 font-mono text-sm"></textarea>
            @error('body')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
        </div>

        <fieldset class="grid gap-4 border-t pt-5">
            <legend class="text-sm font-medium">文章图片</legend>
            <div class="grid gap-5 md:grid-cols-2">
                <div>
                    <label class="block text-sm font-medium" for="cover_media_id">封面图</label>
                    <select id="cover_media_id" wire:model="cover_media_id" class="mt-2 w-full rounded border px-3 py-2">
                        <option value="">不选择封面图</option>
                        @foreach($mediaOptions as $media)
                            <option value="{{ $media->id }}">{{ $media->alt_text ?: $media->path }}</option>
                        @endforeach
                    </select>
                    <p class="mt-1 text-xs text-slate-500">用于文章详情页标题下方展示。</p>
                    @error('cover_media_id')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror

                    @if($selectedCoverMedia)
                        <div class="mt-3 overflow-hidden rounded border border-slate-200 bg-slate-50">
                            <img src="{{ Storage::disk($selectedCoverMedia->disk)->url($selectedCoverMedia->path) }}" alt="{{ $selectedCoverMedia->alt_text ?: $selectedCoverMedia->path }}" class="h-40 w-full object-cover">
                            <div class="px-3 py-2 text-xs text-slate-600">
                                <p class="truncate">{{ $selectedCoverMedia->alt_text ?: $selectedCoverMedia->path }}</p>
                                @if($selectedCoverMedia->source_note)
                                    <p class="mt-1 truncate">{{ $selectedCoverMedia->source_note }}</p>
                                @endif
                            </div>
                        </div>
                    @endif
                </div>

                <div>
                    <label class="block text-sm font-medium" for="og_media_id">社交分享图</label>
                    <select id="og_media_id" wire:model="og_media_id" class="mt-2 w-full rounded border px-3 py-2">
                        <option value="">不选择社交分享图</option>
                        @foreach($mediaOptions as $media)
                            <option value="{{ $media->id }}">{{ $media->alt_text ?: $media->path }}</option>
                        @endforeach
                    </select>
                    <p class="mt-1 text-xs text-slate-500">优先用于文章详情页的 Open Graph 分享图。</p>
                    @error('og_media_id')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror

                    @if($selectedOgMedia)
                        <div class="mt-3 overflow-hidden rounded border border-slate-200 bg-slate-50">
                            <img src="{{ Storage::disk($selectedOgMedia->disk)->url($selectedOgMedia->path) }}" alt="{{ $selectedOgMedia->alt_text ?: $selectedOgMedia->path }}" class="h-40 w-full object-cover">
                            <div class="px-3 py-2 text-xs text-slate-600">
                                <p class="truncate">{{ $selectedOgMedia->alt_text ?: $selectedOgMedia->path }}</p>
                                @if($selectedOgMedia->source_note)
                                    <p class="mt-1 truncate">{{ $selectedOgMedia->source_note }}</p>
                                @endif
                            </div>
                        </div>
                    @endif
                </div>
            </div>
            @if($mediaOptions->isEmpty())
                <p class="text-sm text-slate-500">媒体库暂无可选图片</p>
            @endif
        </fieldset>

        <div class="grid gap-5 border-t pt-5 md:grid-cols-2">
            <div>
                <label class="block text-sm font-medium" for="source_name">来源名称</label>
                <input id="source_name" wire:model="source_name" class="mt-2 w-full rounded border px-3 py-2">
                @error('source_name')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
            </div>
            <div>
                <label class="block text-sm font-medium" for="source_url">来源链接</label>
                <input id="source_url" wire:model="source_url" class="mt-2 w-full rounded border px-3 py-2">
                @error('source_url')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
            </div>
            <div>
                <label class="block text-sm font-medium" for="display_updated_at">前台显示更新时间</label>
                <input id="display_updated_at" type="datetime-local" wire:model="display_updated_at" class="mt-2 w-full rounded border px-3 py-2">
                @error('display_updated_at')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
            </div>
            <div>
                <label class="block text-sm font-medium" for="reading_time_minutes">阅读时间（分钟）</label>
                <input id="reading_time_minutes" type="number" min="1" wire:model="reading_time_minutes" class="mt-2 w-full rounded border px-3 py-2">
                @error('reading_time_minutes')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
            </div>
            <div>
                <label class="block text-sm font-medium" for="popularity_score">热度分数</label>
                <input id="popularity_score" type="number" min="0" wire:model="popularity_score" class="mt-2 w-full rounded border px-3 py-2">
                @error('popularity_score')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
            </div>
            <label class="flex items-center gap-2 self-end text-sm">
                <input type="checkbox" wire:model="has_coupon">
                标记含优惠信息
            </label>
            @error('has_coupon')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
        </div>

        <fieldset class="grid gap-3 border-t pt-5">
            <legend class="text-sm font-medium">所属分类</legend>
            @if($categoryOptions->isEmpty())
                <p class="text-sm text-slate-500">暂无可选分类</p>
            @else
                <div class="grid gap-2 md:grid-cols-2">
                    @foreach($categoryOptions as $category)
                        <label class="flex items-center gap-2 rounded border px-3 py-2 text-sm">
                            <input type="checkbox" wire:model="selectedCategoryIds" value="{{ $category->id }}">
                            <span>{{ $category->display_name ?: $category->title }}</span>
                            <span class="text-xs text-slate-500">/{{ $category->slug }}</span>
                        </label>
                    @endforeach
                </div>
            @endif
            @error('selectedCategoryIds')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
            @error('selectedCategoryIds.*')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
        </fieldset>

        <fieldset class="grid gap-4 border-t pt-5">
            <legend class="sr-only">文章 FAQ</legend>
            <div class="flex items-center justify-between">
                <p class="text-sm font-medium">文章 FAQ</p>
                <button type="button" wire:click="addFaq" class="rounded border px-3 py-1.5 text-sm font-medium">新增 FAQ</button>
            </div>

            @forelse($faqs as $index => $faq)
                <div wire:key="article-faq-{{ $index }}" class="grid gap-3 rounded border border-slate-200 p-4">
                    <div class="flex items-center justify-between">
                        <p class="text-sm font-medium">FAQ {{ $index + 1 }}</p>
                        <button type="button" wire:click="removeFaq({{ $index }})" class="text-sm text-red-700 underline">移除</button>
                    </div>
                    <div>
                        <label class="block text-sm font-medium" for="faq_question_{{ $index }}">问题</label>
                        <input id="faq_question_{{ $index }}" wire:model="faqs.{{ $index }}.question" class="mt-2 w-full rounded border px-3 py-2">
                        @error("faqs.$index.question")<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                    </div>
                    <div>
                        <label class="block text-sm font-medium" for="faq_answer_{{ $index }}">答案 HTML</label>
                        <textarea id="faq_answer_{{ $index }}" wire:model="faqs.{{ $index }}.answer" rows="4" class="mt-2 w-full rounded border px-3 py-2 font-mono text-sm"></textarea>
                        @error("faqs.$index.answer")<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                    </div>
                    <div class="grid gap-3 md:grid-cols-2">
                        <div>
                            <label class="block text-sm font-medium" for="faq_sort_order_{{ $index }}">排序</label>
                            <input id="faq_sort_order_{{ $index }}" type="number" min="0" wire:model="faqs.{{ $index }}.sort_order" class="mt-2 w-full rounded border px-3 py-2">
                            @error("faqs.$index.sort_order")<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                        </div>
                        <label class="flex items-center gap-2 self-end text-sm">
                            <input type="checkbox" wire:model="faqs.{{ $index }}.is_enabled">
                            启用
                        </label>
                        @error("faqs.$index.is_enabled")<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                    </div>
                </div>
            @empty
                <p class="text-sm text-slate-500">尚未添加 FAQ</p>
            @endforelse

            @error('faqs')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
        </fieldset>

        <div class="grid gap-5 border-t pt-5 md:grid-cols-2">
            <div>
                <label class="block text-sm font-medium" for="seo_title">SEO 标题</label>
                <input id="seo_title" wire:model="seo_title" class="mt-2 w-full rounded border px-3 py-2">
                @error('seo_title')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
            </div>
            <div>
                <label class="block text-sm font-medium" for="canonical_url">Canonical URL</label>
                <input id="canonical_url" wire:model="canonical_url" class="mt-2 w-full rounded border px-3 py-2">
                @error('canonical_url')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
            </div>
        </div>

        <div>
            <label class="block text-sm font-medium" for="meta_description">Meta Description</label>
            <textarea id="meta_description" wire:model="meta_description" rows="3" class="mt-2 w-full rounded border px-3 py-2"></textarea>
            @error('meta_description')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
        </div>

        <label class="flex items-center gap-2 text-sm">
            <input type="checkbox" wire:model="is_indexable">
            允许搜索引擎索引
        </label>

        <div class="rounded border border-slate-200 bg-slate-50 p-4">
            <p class="text-sm font-medium">SEO 预览</p>
            <p class="mt-3 text-base font-semibold text-slate-950">{{ $seoPreview['title'] ?: '未填写标题' }}</p>
            @if($seoPreview['canonical'])
                <p class="mt-1 break-all text-sm text-emerald-700">{{ $seoPreview['canonical'] }}</p>
            @else
                <p class="mt-1 text-sm text-slate-500">保存 Slug 后生成 Canonical URL</p>
            @endif
            <p class="mt-2 text-sm text-slate-600">{{ $seoPreview['description'] ?: '未填写描述' }}</p>
            <p class="mt-3 text-xs font-medium {{ $seoPreview['indexable'] ? 'text-emerald-700' : 'text-red-700' }}">
                {{ $seoPreview['indexable'] ? '允许索引' : '不允许索引' }}
            </p>
        </div>

        <div class="flex gap-3">
            <button type="submit" class="rounded bg-slate-900 px-5 py-2 text-sm font-medium text-white">保存</button>
            @if($articleId)
                <a href="{{ route('admin.articles.review', $articleId) }}" class="rounded border px-5 py-2 text-sm font-medium">进入审核</a>
            @endif
        </div>
    </form>
</section>
