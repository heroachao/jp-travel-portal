<section>
    <h1 class="text-2xl font-semibold">站点设置</h1>

    @if(session('status'))
        <p class="mt-4 rounded border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-800">{{ session('status') }}</p>
    @endif

    <form wire:submit="save" class="mt-6 grid gap-5 rounded-lg border bg-white p-6">
        <div class="grid gap-5 md:grid-cols-2">
            <div>
                <label class="block text-sm font-medium" for="site_name">站点名称</label>
                <input id="site_name" wire:model="site_name" class="mt-2 w-full rounded border px-3 py-2">
                @error('site_name')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
            </div>
            <div>
                <label class="block text-sm font-medium" for="seo_title_suffix">SEO 标题后缀</label>
                <input id="seo_title_suffix" wire:model="seo_title_suffix" class="mt-2 w-full rounded border px-3 py-2">
                @error('seo_title_suffix')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
            </div>
        </div>

        <div>
            <label class="block text-sm font-medium" for="default_meta_description">默认 Meta Description</label>
            <textarea id="default_meta_description" wire:model="default_meta_description" rows="3" class="mt-2 w-full rounded border px-3 py-2"></textarea>
            @error('default_meta_description')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
        </div>

        <div class="grid gap-5 border-t pt-5 md:grid-cols-2">
            <div>
                <label class="block text-sm font-medium" for="ga4_measurement_id">GA4 Measurement ID</label>
                <input id="ga4_measurement_id" wire:model="ga4_measurement_id" placeholder="G-ABC123DEF4" class="mt-2 w-full rounded border px-3 py-2">
                @error('ga4_measurement_id')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
            </div>
            <div>
                <label class="block text-sm font-medium" for="adsense_publisher_id">AdSense Publisher ID</label>
                <input id="adsense_publisher_id" wire:model="adsense_publisher_id" placeholder="ca-pub-1234567890123456" class="mt-2 w-full rounded border px-3 py-2">
                @error('adsense_publisher_id')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
            </div>
        </div>

        <div class="grid gap-3 rounded border border-amber-200 bg-amber-50 p-4 text-sm text-amber-900">
            <label class="flex items-center gap-2">
                <input type="checkbox" wire:model="analytics_enabled">
                启用 Google Analytics 输出
            </label>
            <label class="flex items-center gap-2">
                <input type="checkbox" wire:model="ads_enabled">
                启用 Google AdSense 与广告位输出
            </label>
            <p>本地开发可以先保存 ID 但不启用。只有开关启用且 ID 存在时，前台才会输出对应脚本。</p>
        </div>

        <div>
            <button type="submit" class="rounded bg-slate-900 px-5 py-2 text-sm font-medium text-white">保存设置</button>
        </div>
    </form>
</section>
