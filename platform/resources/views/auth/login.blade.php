<!doctype html>
<html lang="zh-CN">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>登录 | 日本旅游发布后台</title>
    @unless(app()->environment('testing'))
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    @endunless
</head>
<body class="min-h-screen bg-slate-50 text-slate-900">
    <main class="mx-auto flex min-h-screen max-w-md items-center px-6">
        <form method="post" action="{{ route('login.store') }}" class="w-full rounded-lg border bg-white p-8 shadow-sm">
            @csrf
            <h1 class="text-2xl font-semibold">后台登录</h1>
            <label class="mt-6 block text-sm font-medium" for="email">邮箱</label>
            <input id="email" name="email" type="email" value="{{ old('email') }}" required autofocus class="mt-2 w-full rounded border px-3 py-2">
            @error('email')<p class="mt-2 text-sm text-red-600">{{ $message }}</p>@enderror

            <label class="mt-4 block text-sm font-medium" for="password">密码</label>
            <input id="password" name="password" type="password" required class="mt-2 w-full rounded border px-3 py-2">

            <label class="mt-4 flex items-center gap-2 text-sm">
                <input name="remember" type="checkbox" value="1">
                记住登录状态
            </label>

            <button class="mt-6 w-full rounded bg-slate-900 px-4 py-2 font-medium text-white" type="submit">登录</button>
        </form>
    </main>
</body>
</html>
