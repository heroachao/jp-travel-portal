# Japan Trip Tools 统计系统配置说明

本项目使用 Google Analytics 4 采集用户进入网站后的行为，用 Google Search Console 采集用户进入网站前的 Google 搜索表现。两者配合后，才能同时看到访问人数、页面访问、停留、访问深度、来源和搜索关键词。

## 后台配置

进入后台 `系统 -> 站点设置`：

1. 填写 `Google Analytics 4 衡量 ID`，格式类似 `G-ABC123DEF4`。
2. 勾选 `启用 Google Analytics 输出`。
3. 在 Google Search Console 添加网站属性后，把 HTML meta 验证码填入 `Google Search Console 验证码`。可以粘贴完整 meta 标签，也可以只粘贴 `content` 里的验证码。
4. 保存设置，重新导出并发布静态网站。
5. 回到 Search Console 点击验证。

## 可以看到的数据

GA4 负责统计：

- 访问人数、会话数、页面浏览量。
- 每次会话访问页数、平均互动时间、跳出相关指标。
- 来源渠道，例如 Google organic、广告、社交媒体、外部推荐网站。
- 站内搜索词，对应 GA4 事件 `search` 和参数 `search_term`。
- 搜索结果页浏览，对应事件 `view_search_results`。
- 外部服务入口点击，对应事件 `outbound_click`。
- 页面滚动深度，对应事件 `scroll_depth`，参数 `percent_scrolled`。
- 阅读停留检查点，对应事件 `article_engagement`，参数 `engagement_seconds`。

Search Console 负责统计：

- Google 搜索查询词。
- 展现次数、点击次数、CTR、平均排名。
- 哪些页面从 Google 搜索获得点击。
- 国家、设备、日期维度下的自然搜索表现。

## SEO 优化用法

- 有展现但 CTR 低的页面：优先优化标题、meta description 和首屏摘要。
- 排名在第 8-20 位的关键词：补充更完整的问答、路线、票务、费用、营业时间和交通信息，争取进入第一页上半区。
- GA4 停留短但 Search Console 点击多的页面：检查内容是否满足搜索意图，补充更直接的结论和目录。
- 站内搜索频繁但结果不足的词：新增专题页或文章，作为内容选题池。
- 外部服务入口点击高的页面：适合放置更明确的商业转化入口和广告位。

## 注意事项

Google 自然搜索的真实查询词主要在 Search Console 查看，GA4 的自然搜索关键词通常不会完整显示。GA4 和 Search Console 的点击、会话、时间区间和归因口径不同，数字不需要完全一致，趋势更重要。
