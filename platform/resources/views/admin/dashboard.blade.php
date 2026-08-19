@extends('layouts.admin', ['title' => '工作台'])

@php
    use App\Enums\ArticleStatus;
    use App\Models\AdPlacement;
    use App\Models\Article;
    use App\Models\Destination;
    use App\Models\HomepageModule;
    use App\Models\MediaAsset;
    use App\Models\ServiceLink;
    use App\Models\TravelCategory;

    $articleCounts = Article::query()
        ->selectRaw('status, count(*) as total')
        ->groupBy('status')
        ->pluck('total', 'status');
    $publishedCount = (int) ($articleCounts[ArticleStatus::Published->value] ?? 0);
    $reviewCount = (int) ($articleCounts[ArticleStatus::Review->value] ?? 0);
    $draftCount = (int) ($articleCounts[ArticleStatus::Draft->value] ?? 0);
    $scheduledCount = (int) ($articleCounts[ArticleStatus::Scheduled->value] ?? 0);
    $articleTotal = max(1, Article::query()->count());
    $seoReadyCount = Article::query()
        ->whereNotNull('seo_title')
        ->where('seo_title', '!=', '')
        ->whereNotNull('meta_description')
        ->where('meta_description', '!=', '')
        ->count();
    $seoReadyRate = round(($seoReadyCount / $articleTotal) * 100);
    $indexableDestinations = Destination::query()->where('is_indexable', true)->count();
    $channelDestinations = Destination::query()->where('is_channel', true)->count();
    $visibleCategories = TravelCategory::query()->where('is_visible', true)->count();
    $mediaCount = MediaAsset::query()->count();
    $enabledAds = AdPlacement::query()->where('is_enabled', true)->count();
    $enabledServices = ServiceLink::query()->where('is_enabled', true)->count();
    $enabledModules = HomepageModule::query()->where('is_enabled', true)->count();
    $latestArticles = Article::query()->with('author')->latest('updated_at')->take(5)->get();
    $attentionArticles = Article::query()
        ->whereIn('status', [ArticleStatus::Review->value, ArticleStatus::Draft->value, ArticleStatus::Scheduled->value])
        ->latest('updated_at')
        ->take(4)
        ->get();
    $metricCards = [
        ['label' => '已发布文章', 'value' => $publishedCount, 'sub' => '待审 '.$reviewCount.' / 草稿 '.$draftCount, 'tone' => 'blue', 'bars' => [34, 46, 38, 58, 44, 70, 62]],
        ['label' => 'SEO 完成率', 'value' => $seoReadyRate.'%', 'sub' => '已完善 '.$seoReadyCount.' 篇', 'tone' => 'green', 'bars' => [28, 36, 44, 40, 58, 64, 76]],
        ['label' => '地区频道', 'value' => $channelDestinations, 'sub' => '可索引目的地 '.$indexableDestinations, 'tone' => 'violet', 'bars' => [24, 30, 42, 52, 48, 56, 66]],
        ['label' => '媒体素材', 'value' => $mediaCount, 'sub' => '封面图与 OG 图来源', 'tone' => 'amber', 'bars' => [20, 30, 28, 46, 52, 48, 60]],
        ['label' => '服务入口', 'value' => $enabledServices, 'sub' => '首页模块 '.$enabledModules, 'tone' => 'cyan', 'bars' => [18, 24, 38, 34, 50, 58, 54]],
        ['label' => '广告位启用', 'value' => $enabledAds, 'sub' => 'AdSense 输出受开关控制', 'tone' => 'rose', 'bars' => [16, 22, 20, 32, 44, 38, 50]],
    ];
@endphp

@section('content')
    <section class="space-y-5">
        <div class="admin-campaign-banner">
            <div>
                <p class="text-xs font-semibold uppercase tracking-[0.24em] text-rose-600">Japan Travel Growth</p>
                <h2 class="mt-2 text-2xl font-bold text-slate-950">内容增长引擎已就绪，优先完善高价值旅游工具与英文 SEO 页面</h2>
                <p class="mt-2 max-w-3xl text-sm text-slate-600">围绕目的地、交通、行程、购物和广告变现，把内容发布、媒体素材、服务入口与数据设置放在同一个工作流里。</p>
            </div>
            <div class="flex flex-wrap gap-2">
                <a href="{{ route('admin.articles.create') }}" class="admin-primary-button">新建文章</a>
                <a href="{{ route('admin.settings.index') }}" class="admin-ghost-button">检查站点设置</a>
            </div>
        </div>

        <div class="admin-panel">
            <div class="admin-panel-head">
                <div>
                    <h2>数据汇总</h2>
                    <p>按内容、SEO、媒体、服务和广告运营状态实时汇总。</p>
                </div>
                <div class="admin-segmented">
                    <span class="is-active">今日</span>
                    <span>7 天</span>
                    <span>30 天</span>
                </div>
            </div>
            <div class="grid gap-0 border-t border-slate-100 sm:grid-cols-2 xl:grid-cols-3 2xl:grid-cols-6">
                @foreach($metricCards as $card)
                    <article class="admin-metric-card">
                        <div class="flex items-start justify-between gap-3">
                            <div>
                                <p class="text-xs text-slate-500">{{ $card['label'] }}</p>
                                <p class="mt-2 text-2xl font-bold text-slate-950">{{ $card['value'] }}</p>
                                <p class="mt-1 text-xs text-slate-500">{{ $card['sub'] }}</p>
                            </div>
                            <div class="admin-sparkline is-{{ $card['tone'] }}" aria-hidden="true">
                                @foreach($card['bars'] as $bar)
                                    <span style="height: {{ $bar }}%"></span>
                                @endforeach
                            </div>
                        </div>
                    </article>
                @endforeach
            </div>
        </div>

        <div class="grid gap-5 xl:grid-cols-[1fr_300px]">
            <div class="admin-panel">
                <div class="admin-panel-head">
                    <div>
                        <h2>SEO 方法论</h2>
                        <p>用频道、专题、标签和服务入口形成可持续的英文旅游流量结构。</p>
                    </div>
                    <a href="{{ route('admin.travel-categories.index') }}" class="text-sm font-medium text-blue-600">管理频道</a>
                </div>
                <div class="grid gap-5 border-t border-slate-100 p-5 lg:grid-cols-[380px_minmax(0,1fr)]">
                    <div class="admin-besto-map">
                        <div class="grid grid-cols-4 gap-2 text-xs font-semibold text-slate-500">
                            <span><b>B</b>rand</span>
                            <span><b>E</b>xact</span>
                            <span><b>S</b>earch</span>
                            <span><b>T</b>rend</span>
                        </div>
                        <div class="relative mt-5 h-48 overflow-hidden rounded-lg border border-blue-100 bg-white">
                            <div class="admin-flow-band one"></div>
                            <div class="admin-flow-band two"></div>
                            <div class="absolute bottom-4 left-5 right-5 flex items-end justify-between">
                                @foreach([28, 36, 44, 52, 58, 66, 74] as $height)
                                    <span class="w-7 rounded-t bg-blue-500/40" style="height: {{ $height }}px"></span>
                                @endforeach
                            </div>
                        </div>
                        <div class="mt-2 flex justify-between text-xs text-slate-400">
                            <span>精准关键词</span>
                            <span>高价值流量</span>
                        </div>
                    </div>

                    <div class="space-y-3">
                        <div class="rounded-lg bg-[#f3f6ff] p-4">
                            <div class="flex items-center gap-2">
                                <h3 class="font-semibold text-slate-900">日本旅游内容矩阵</h3>
                                <span class="rounded bg-rose-100 px-2 py-0.5 text-xs font-medium text-rose-600">待优化</span>
                            </div>
                            <p class="mt-2 text-sm text-slate-600">优先把目的地页面、交通工具页、行程页和购物服务页互相连接，形成稳定入口。</p>
                        </div>
                        <div class="grid gap-3 lg:grid-cols-2">
                            <div class="rounded-lg border border-slate-100 bg-white p-4">
                                <p class="text-sm font-semibold text-slate-900">核心指标洞察</p>
                                <p class="mt-3 text-2xl font-bold text-slate-950">{{ $seoReadyRate }}%</p>
                                <p class="mt-1 text-xs text-slate-500">文章 SEO 完成率</p>
                            </div>
                            <div class="rounded-lg border border-slate-100 bg-white p-4">
                                <p class="text-sm font-semibold text-slate-900">频道覆盖</p>
                                <p class="mt-3 text-2xl font-bold text-slate-950">{{ $visibleCategories }}</p>
                                <p class="mt-1 text-xs text-slate-500">可见分类频道</p>
                            </div>
                        </div>
                        <div class="rounded-lg border border-blue-100 bg-blue-50/70 p-4">
                            <p class="text-sm font-semibold text-blue-900">下一步建议</p>
                            <p class="mt-2 text-sm text-blue-800">先补齐 Tokyo、Kyoto、JR Pass、SIM/eSIM、酒店区域和购物退税等高搜索意图页面，再逐步接广告与联盟入口。</p>
                        </div>
                    </div>
                </div>
            </div>

            <aside class="admin-panel">
                <div class="admin-panel-head">
                    <div>
                        <h2>提升小妙招</h2>
                        <p>把内容、服务和广告放进同一个增长节奏。</p>
                    </div>
                </div>
                <div class="space-y-3 border-t border-slate-100 p-5">
                    <div class="rounded-lg bg-indigo-50 p-4">
                        <p class="text-xs font-semibold text-indigo-600">智能优化</p>
                        <p class="mt-2 text-sm font-medium text-slate-900">把“搜索卡位”页面放到首页模块首屏。</p>
                        <a href="{{ route('admin.homepage-modules.index') }}" class="mt-3 inline-flex text-sm font-medium text-indigo-600">创建首页模块</a>
                    </div>
                    <div class="rounded-lg bg-amber-50 p-4">
                        <p class="text-xs font-semibold text-amber-600">商业化</p>
                        <p class="mt-2 text-sm font-medium text-slate-900">服务入口先做工具价值，再接广告和联盟追踪。</p>
                        <a href="{{ route('admin.service-links.index') }}" class="mt-3 inline-flex text-sm font-medium text-amber-700">查看服务入口</a>
                    </div>
                    <div class="rounded-lg bg-emerald-50 p-4">
                        <p class="text-xs font-semibold text-emerald-600">安全上线</p>
                        <p class="mt-2 text-sm font-medium text-slate-900">广告代码默认停用，确认前台效果后再启用。</p>
                        <a href="{{ route('admin.ads.index') }}" class="mt-3 inline-flex text-sm font-medium text-emerald-700">管理广告位</a>
                    </div>
                </div>
            </aside>
        </div>

        <div class="admin-panel">
            <div class="admin-panel-head">
                <div>
                    <h2>内容雷达</h2>
                    <p>近期更新与需要处理的内容会显示在这里。</p>
                </div>
                <a href="{{ route('admin.articles.index') }}" class="text-sm font-medium text-blue-600">查看全部文章</a>
            </div>
            <div class="grid gap-5 border-t border-slate-100 p-5 xl:grid-cols-[1fr_360px]">
                <div>
                    <div class="mb-3 flex gap-6 border-b border-slate-100 text-sm">
                        <span class="border-b-2 border-blue-600 pb-3 font-semibold text-blue-600">计划（{{ $scheduledCount + $reviewCount }}）</span>
                        <span class="pb-3 text-slate-500">草稿（{{ $draftCount }}）</span>
                    </div>
                    <div class="divide-y divide-slate-100 rounded-lg border border-slate-100 bg-white">
                        @forelse($attentionArticles as $article)
                            <div class="flex items-center justify-between gap-4 px-4 py-3">
                                <div class="min-w-0">
                                    <p class="truncate text-sm font-medium text-slate-900">{{ $article->title }}</p>
                                    <p class="mt-1 text-xs text-slate-500">{{ $article->status->label() }} · 更新于 {{ $article->updated_at?->format('Y-m-d H:i') }}</p>
                                </div>
                                <a href="{{ route('admin.articles.edit', $article) }}" class="shrink-0 text-sm font-medium text-blue-600">编辑</a>
                            </div>
                        @empty
                            <div class="px-4 py-8 text-center text-sm text-slate-500">暂无待处理文章</div>
                        @endforelse
                    </div>
                </div>

                <div class="rounded-lg border border-slate-100 bg-slate-50 p-4">
                    <div class="flex items-center justify-between">
                        <h3 class="font-semibold text-slate-900">最近更新</h3>
                        <span class="text-xs text-slate-400">{{ $latestArticles->count() }} 条</span>
                    </div>
                    <div class="mt-3 space-y-3">
                        @forelse($latestArticles as $article)
                            <a href="{{ route('admin.articles.edit', $article) }}" class="block rounded-lg bg-white px-3 py-3 text-sm shadow-sm">
                                <span class="block truncate font-medium text-slate-900">{{ $article->title }}</span>
                                <span class="mt-1 block text-xs text-slate-500">{{ $article->author?->name ?? '系统' }} · {{ $article->updated_at?->diffForHumans() }}</span>
                            </a>
                        @empty
                            <p class="rounded-lg bg-white px-3 py-6 text-center text-sm text-slate-500">暂无文章更新</p>
                        @endforelse
                    </div>
                </div>
            </div>
        </div>
    </section>
@endsection
