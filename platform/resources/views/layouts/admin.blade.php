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
<body class="min-h-screen bg-slate-50 text-slate-900">
    <div class="flex min-h-screen">
        <aside class="w-64 border-r bg-white p-5">
            <a href="{{ route('admin.dashboard') }}" class="block text-lg font-semibold">日本旅游发布系统</a>
            <nav class="mt-8 space-y-2 text-sm">
                <a class="block rounded px-3 py-2 hover:bg-slate-100" href="{{ route('admin.dashboard') }}">工作台</a>
                <a class="block rounded px-3 py-2 hover:bg-slate-100" href="{{ route('admin.articles.index') }}">文章管理</a>
                <a class="block rounded px-3 py-2 hover:bg-slate-100" href="{{ route('admin.destinations.index') }}">目的地管理</a>
                <a class="block rounded px-3 py-2 hover:bg-slate-100" href="{{ route('admin.topics.index') }}">专题管理</a>
                <a class="block rounded px-3 py-2 hover:bg-slate-100" href="{{ route('admin.tags.index') }}">标签管理</a>
                <a class="block rounded px-3 py-2 hover:bg-slate-100" href="{{ route('admin.travel-categories.index') }}">分类频道</a>
                <a class="block rounded px-3 py-2 hover:bg-slate-100" href="{{ route('admin.service-links.index') }}">服务入口</a>
                <a class="block rounded px-3 py-2 hover:bg-slate-100" href="{{ route('admin.homepage-modules.index') }}">首页模块</a>
                <a class="block rounded px-3 py-2 hover:bg-slate-100" href="{{ route('admin.ads.index') }}">广告管理</a>
                <a class="block rounded px-3 py-2 hover:bg-slate-100" href="{{ route('admin.settings.index') }}">站点设置</a>
            </nav>
        </aside>
        <main class="flex-1 p-8">
            {{ $slot ?? '' }}
            @yield('content')
        </main>
    </div>
</body>
</html>
