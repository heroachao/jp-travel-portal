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
                <p class="mt-1 text-xs text-slate-500">用于后台识别与前台默认品牌展示。</p>
                @error('site_name')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
            </div>
            <div>
                <label class="block text-sm font-medium" for="seo_title_suffix">SEO 标题后缀</label>
                <input id="seo_title_suffix" wire:model="seo_title_suffix" class="mt-2 w-full rounded border px-3 py-2">
                <p class="mt-1 text-xs text-slate-500">追加在页面标题末尾，建议保持简短。</p>
                @error('seo_title_suffix')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
            </div>
        </div>

        <div>
            <label class="block text-sm font-medium" for="tagline">站点副标题</label>
            <input id="tagline" wire:model="tagline" class="mt-2 w-full rounded border px-3 py-2">
            <p class="mt-1 text-xs text-slate-500">一句话介绍站点定位，可用于首页、结构化数据或分享摘要。</p>
            @error('tagline')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
        </div>

        <div>
            <label class="block text-sm font-medium" for="default_meta_description">默认页面描述</label>
            <textarea id="default_meta_description" wire:model="default_meta_description" rows="3" class="mt-2 w-full rounded border px-3 py-2"></textarea>
            <p class="mt-1 text-xs text-slate-500">当页面没有单独 SEO 描述时使用，最多 255 个字符。</p>
            @error('default_meta_description')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
        </div>

        <div class="grid gap-5 border-t pt-5 md:grid-cols-2">
            <div>
                <label class="block text-sm font-medium" for="ga4_measurement_id">Google Analytics 4 衡量 ID</label>
                <input id="ga4_measurement_id" wire:model="ga4_measurement_id" placeholder="G-ABC123DEF4" class="mt-2 w-full rounded border px-3 py-2">
                <p class="mt-1 text-xs text-slate-500">格式示例：G-ABC123DEF4。只有启用统计输出后才会在前台加载。</p>
                @error('ga4_measurement_id')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
            </div>
            <div>
                <label class="block text-sm font-medium" for="adsense_publisher_id">Google AdSense 发布商 ID</label>
                <input id="adsense_publisher_id" wire:model="adsense_publisher_id" placeholder="ca-pub-1234567890123456" class="mt-2 w-full rounded border px-3 py-2">
                <p class="mt-1 text-xs text-slate-500">格式示例：ca-pub-1234567890123456。只有启用广告输出后才会在前台加载。</p>
                @error('adsense_publisher_id')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
            </div>
        </div>

        <div class="grid gap-5 border-t pt-5 md:grid-cols-2">
            <div>
                <label class="block text-sm font-medium" for="contact_email">联系邮箱</label>
                <input id="contact_email" type="email" wire:model="contact_email" placeholder="hello@example.com" class="mt-2 w-full rounded border px-3 py-2">
                <p class="mt-1 text-xs text-slate-500">可用于组织结构化数据或公开联系方式。</p>
                @error('contact_email')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
            </div>
            <div>
                <label class="block text-sm font-medium" for="social_links">社交链接 JSON</label>
                <textarea id="social_links" wire:model="social_links" rows="4" placeholder='{"x":"https://x.com/example"}' class="mt-2 w-full rounded border px-3 py-2 font-mono text-sm"></textarea>
                <p class="mt-1 text-xs text-slate-500">填写键值形式的 JSON；留空时不保存社交链接。</p>
                @error('social_links')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
            </div>
        </div>

        <div>
            <label class="block text-sm font-medium" for="robots_extra_rules">robots.txt 追加规则</label>
            <textarea id="robots_extra_rules" wire:model="robots_extra_rules" rows="5" placeholder="Disallow: /private" class="mt-2 w-full rounded border px-3 py-2 font-mono text-sm"></textarea>
            <p class="mt-1 text-xs text-slate-500">追加到 robots.txt 的自定义规则，最多 2000 个字符。</p>
            @error('robots_extra_rules')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
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
            <label class="flex items-center gap-2">
                <input type="checkbox" wire:model="organization_schema_enabled">
                启用组织结构化数据
            </label>
            <p>本地开发可以先保存 ID 与组织信息但不启用。只有对应开关启用且必要字段存在时，前台才会输出相关内容。</p>
        </div>

        <div>
            <button type="submit" class="rounded bg-slate-900 px-5 py-2 text-sm font-medium text-white">保存设置</button>
        </div>
    </form>
</section>
