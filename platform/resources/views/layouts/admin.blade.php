<!doctype html>
<html lang="zh-CN">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $title ?? '日本旅游发布后台' }}</title>
    @unless(app()->environment('testing'))
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    @endunless
</head>
@php
    $currentTitle = $title ?? '工作台';
    $isActive = fn ($patterns) => collect((array) $patterns)->contains(fn ($pattern) => request()->routeIs($pattern));
    $primaryNav = [
        ['label' => '首页', 'route' => 'admin.dashboard', 'active' => 'admin.dashboard'],
        ['label' => '内容', 'route' => 'admin.articles.index', 'active' => ['admin.articles.*', 'admin.destinations.*', 'admin.topics.*', 'admin.tags.*', 'admin.travel-categories.*']],
        ['label' => '增长', 'route' => 'admin.homepage-modules.index', 'active' => ['admin.homepage-modules.*', 'admin.service-links.*']],
        ['label' => '商业', 'route' => 'admin.ads.index', 'active' => ['admin.ads.*', 'admin.media.*']],
        ['label' => '系统', 'route' => 'admin.settings.index', 'active' => 'admin.settings.*'],
    ];
    $railItems = [
        ['label' => '常用', 'mark' => '常', 'route' => 'admin.dashboard', 'active' => 'admin.dashboard'],
        ['label' => '内容', 'mark' => '文', 'route' => 'admin.articles.index', 'active' => ['admin.articles.*', 'admin.destinations.*', 'admin.topics.*', 'admin.tags.*', 'admin.travel-categories.*']],
        ['label' => '增长', 'mark' => '增', 'route' => 'admin.homepage-modules.index', 'active' => ['admin.homepage-modules.*', 'admin.service-links.*']],
        ['label' => '商业', 'mark' => '商', 'route' => 'admin.ads.index', 'active' => ['admin.ads.*', 'admin.media.*']],
        ['label' => '系统', 'mark' => '设', 'route' => 'admin.settings.index', 'active' => 'admin.settings.*'],
    ];
    $menuGroups = [
        [
            'title' => '内容运营',
            'items' => [
                ['label' => '工作台', 'route' => 'admin.dashboard', 'active' => 'admin.dashboard'],
                ['label' => '文章管理', 'route' => 'admin.articles.index', 'active' => 'admin.articles.*'],
                ['label' => '目的地管理', 'route' => 'admin.destinations.index', 'active' => 'admin.destinations.*'],
                ['label' => '专题管理', 'route' => 'admin.topics.index', 'active' => 'admin.topics.*'],
                ['label' => '标签管理', 'route' => 'admin.tags.index', 'active' => 'admin.tags.*'],
                ['label' => '分类频道', 'route' => 'admin.travel-categories.index', 'active' => 'admin.travel-categories.*'],
            ],
        ],
        [
            'title' => '增长工具',
            'items' => [
                ['label' => '首页模块', 'route' => 'admin.homepage-modules.index', 'active' => 'admin.homepage-modules.*'],
                ['label' => '服务入口', 'route' => 'admin.service-links.index', 'active' => 'admin.service-links.*'],
                ['label' => '广告管理', 'route' => 'admin.ads.index', 'active' => 'admin.ads.*'],
                ['label' => '媒体库', 'route' => 'admin.media.index', 'active' => 'admin.media.*'],
            ],
        ],
        [
            'title' => '系统设置',
            'items' => [
                ['label' => '站点设置', 'route' => 'admin.settings.index', 'active' => 'admin.settings.*'],
            ],
        ],
    ];
@endphp
<body class="min-h-screen bg-[#edf4fb] text-slate-900 antialiased">
    <div class="admin-shell min-h-screen md:grid md:grid-cols-[74px_136px_minmax(0,1fr)]">
        <aside class="admin-rail">
            <a href="{{ route('admin.dashboard') }}" class="admin-rail-brand" aria-label="日本旅游后台">
                <span>JT</span>
            </a>
            <nav class="admin-rail-nav" aria-label="后台主导航">
                @foreach($railItems as $item)
                    <a
                        href="{{ route($item['route']) }}"
                        class="admin-rail-link {{ $isActive($item['active']) ? 'is-active' : '' }}"
                        title="{{ $item['label'] }}"
                    >
                        <span>{{ $item['mark'] }}</span>
                        <small>{{ $item['label'] }}</small>
                    </a>
                @endforeach
            </nav>
        </aside>

        <aside class="admin-side-menu">
            <div class="px-4 py-5">
                <a href="{{ route('admin.dashboard') }}" class="block">
                    <span class="text-sm font-semibold text-slate-900">日本旅游发布系统</span>
                    <span class="mt-1 block text-xs text-slate-500">Japan Travel CMS</span>
                </a>
            </div>
            <nav class="space-y-5 px-3 pb-6 text-sm" aria-label="后台二级导航">
                @foreach($menuGroups as $group)
                    <div>
                        <div class="admin-menu-heading">{{ $group['title'] }}</div>
                        <div class="mt-2 space-y-1">
                            @foreach($group['items'] as $item)
                                <a
                                    href="{{ route($item['route']) }}"
                                    class="admin-menu-link {{ $isActive($item['active']) ? 'is-active' : '' }}"
                                >
                                    {{ $item['label'] }}
                                </a>
                            @endforeach
                        </div>
                    </div>
                @endforeach
            </nav>
        </aside>

        <main class="min-w-0">
            <header class="admin-topbar">
                <div class="flex min-w-0 items-center gap-6">
                    <div class="min-w-0">
                        <p class="text-xs font-medium text-blue-600">AI 内容运营台</p>
                        <h1 class="truncate text-base font-semibold text-slate-950">{{ $currentTitle }}</h1>
                    </div>
                    <nav class="hidden items-center gap-1 md:flex" aria-label="后台顶部导航">
                        @foreach($primaryNav as $item)
                            <a
                                href="{{ route($item['route']) }}"
                                class="admin-top-link {{ $isActive($item['active']) ? 'is-active' : '' }}"
                            >
                                {{ $item['label'] }}
                            </a>
                        @endforeach
                    </nav>
                </div>
                <div class="flex items-center gap-3">
                    <a href="{{ route('home') }}" class="admin-ghost-button">查看前台</a>
                    <div class="hidden text-right sm:block">
                        <p class="text-xs font-medium text-slate-700">{{ auth()->user()?->name ?? '管理员' }}</p>
                        <p class="text-[11px] text-slate-400">{{ auth()->user()?->email }}</p>
                    </div>
                    <form method="post" action="{{ route('logout') }}">
                        @csrf
                        <button class="admin-primary-button" type="submit">退出</button>
                    </form>
                </div>
            </header>

            <div class="admin-notice-bar">
                <span class="font-semibold text-blue-700">公告</span>
                <span>用 AI 辅助内容规划、SEO 检查、广告投放和日本旅游服务入口运营。</span>
            </div>

            <div class="admin-content px-4 py-5 sm:px-6 lg:px-8">
                {{ $slot ?? '' }}
                @yield('content')
            </div>
        </main>
    </div>
</body>
</html>
