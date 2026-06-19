import { mkdir, readdir, readFile, rm, stat, writeFile, copyFile } from 'node:fs/promises';
import path from 'node:path';

const args = new Map();

for (let index = 2; index < process.argv.length; index += 2) {
    const key = process.argv[index];
    const value = process.argv[index + 1];

    if (key?.startsWith('--') && value) {
        args.set(key.slice(2), value);
    }
}

const root = process.cwd();
const localBase = normalizeBase(args.get('local-base') || process.env.LOCAL_BASE || 'http://127.0.0.1:8000');
const siteUrl = normalizeBase(args.get('site-url') || process.env.SITE_URL || 'https://japantriptools.com');
const outDir = path.resolve(args.get('out-dir') || process.env.OUT_DIR || path.join(root, 'dist-static'));
const publicDir = path.join(root, 'public');
const maxPages = Number(args.get('max-pages') || process.env.MAX_PAGES || 2000);

const fetched = new Set();
const queued = [];
const missing = [];
const exported = [];

function normalizeBase(value) {
    return value.replace(/\/+$/, '');
}

function toLocalUrl(siteHref) {
    const url = new URL(siteHref, siteUrl);

    return new URL(`${url.pathname}${url.search}`, localBase);
}

function normalizedPathname(href, basePath = '/') {
    if (! href || href.startsWith('#')) {
        return null;
    }

    if (/^(mailto|tel|javascript):/i.test(href)) {
        return null;
    }

    const url = new URL(href, `${siteUrl}${basePath}`);

    if (url.origin !== siteUrl) {
        return null;
    }

    if (url.search && url.pathname !== '/search') {
        return null;
    }

    return url.pathname;
}

function outputPathFor(pathname) {
    if (pathname === '/') {
        return path.join(outDir, 'index.html');
    }

    const clean = pathname.replace(/^\/+/, '');
    const basename = path.basename(clean);

    if (basename.includes('.')) {
        return path.join(outDir, clean);
    }

    return path.join(outDir, clean, 'index.html');
}

function isHtmlPath(pathname) {
    return pathname === '/' || ! path.basename(pathname).includes('.');
}

async function emptyOutputDirectory() {
    await mkdir(outDir, { recursive: true });

    for (const entry of await readdir(outDir)) {
        if (entry === '.git') {
            continue;
        }

        await rm(path.join(outDir, entry), { recursive: true, force: true });
    }

    await writeFile(path.join(outDir, '.nojekyll'), '');
    await writeFile(path.join(outDir, 'CNAME'), new URL(siteUrl).hostname);
}

async function copyPublicAssets(from = publicDir, to = outDir) {
    await mkdir(to, { recursive: true });

    for (const entry of await readdir(from)) {
        if (['index.php', '.htaccess'].includes(entry)) {
            continue;
        }

        const sourcePath = path.join(from, entry);
        const targetPath = path.join(to, entry);
        const sourceStat = await stat(sourcePath);

        if (sourceStat.isDirectory()) {
            await copyPublicAssets(sourcePath, targetPath);
            continue;
        }

        await mkdir(path.dirname(targetPath), { recursive: true });
        await copyFile(sourcePath, targetPath);
    }
}

async function fetchText(pathname) {
    const response = await fetch(toLocalUrl(`${siteUrl}${pathname}`), { redirect: 'follow' });

    if (! response.ok) {
        missing.push({ pathname, status: response.status });

        return null;
    }

    return {
        body: await response.text(),
        contentType: response.headers.get('content-type') || '',
        finalPath: new URL(response.url).pathname,
    };
}

function enqueue(pathname) {
    if (! pathname || fetched.has(pathname) || queued.includes(pathname)) {
        return;
    }

    if (queued.length + fetched.size >= maxPages) {
        return;
    }

    queued.push(pathname);
}

function enqueueLinks(html, basePath) {
    for (const match of html.matchAll(/\bhref=(["'])(.*?)\1/gi)) {
        const pathname = normalizedPathname(match[2], basePath);

        if (! pathname || ! isHtmlPath(pathname)) {
            continue;
        }

        enqueue(pathname);
    }
}

async function seedFromSitemap() {
    const sitemap = await fetchText('/sitemap.xml');

    if (! sitemap) {
        throw new Error('Could not fetch /sitemap.xml from local server.');
    }

    const urls = [...sitemap.body.matchAll(/<loc>(.*?)<\/loc>/g)]
        .map((match) => normalizedPathname(match[1]))
        .filter(Boolean);

    for (const pathname of ['/', ...urls, '/sitemap.xml', '/robots.txt', '/ads.txt', '/llms.txt']) {
        enqueue(pathname);
    }
}

async function exportPage(pathname) {
    fetched.add(pathname);

    const result = await fetchText(pathname);

    if (! result) {
        return;
    }

    const outputPath = outputPathFor(result.finalPath);
    const body = result.body.replaceAll(localBase, siteUrl);

    await mkdir(path.dirname(outputPath), { recursive: true });
    await writeFile(outputPath, body);
    exported.push(result.finalPath);

    if (result.contentType.includes('text/html')) {
        enqueueLinks(body, result.finalPath);
    }
}

async function assertNoLocalBaseLeaks() {
    const files = await listFiles(outDir);
    const leaked = [];

    for (const file of files) {
        if (! /\.(html|xml|txt|json|js|css)$/.test(file)) {
            continue;
        }

        const body = await readFile(path.join(outDir, file), 'utf8');

        if (body.includes(localBase)) {
            leaked.push(file);
        }
    }

    if (leaked.length > 0) {
        throw new Error(`Local base leaked into export: ${leaked.slice(0, 10).join(', ')}`);
    }
}

async function writeNotFoundPage() {
    await writeFile(path.join(outDir, '404.html'), `<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <meta name="robots" content="noindex,nofollow">
  <title>Page Not Found | Japan Trip Tools</title>
  <style>
    body{margin:0;background:#f4f6fa;color:#111827;font-family:Inter,ui-sans-serif,system-ui,-apple-system,BlinkMacSystemFont,"Segoe UI",sans-serif}
    main{display:grid;min-height:100vh;place-items:center;padding:2rem}
    section{max-width:42rem;border:1px solid #d9dde6;border-radius:.75rem;background:#fff;padding:2rem;box-shadow:0 18px 44px rgb(15 23 42 / .08)}
    p{color:#64748b;line-height:1.65}
    a{display:inline-flex;margin:.25rem .5rem .25rem 0;border:1px solid #d9dde6;border-radius:999px;padding:.7rem 1rem;color:#5f01d1;font-weight:800;text-decoration:none}
  </style>
</head>
<body>
  <main>
    <section>
      <p>404</p>
      <h1>This Japan Trip Tools page is not available.</h1>
      <p>The guide may have moved. Use the main guide feed, search page, or travel tools to continue planning.</p>
      <a href="${siteUrl}/articles/">Articles</a>
      <a href="${siteUrl}/search/">Search</a>
      <a href="${siteUrl}/tools/">Travel tools</a>
    </section>
  </main>
</body>
</html>
`);
}

async function writeRedirectPage(pathname, targetPath) {
    const targetUrl = `${siteUrl}${targetPath}`;

    await mkdir(path.dirname(outputPathFor(pathname)), { recursive: true });
    await writeFile(outputPathFor(pathname), `<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <meta name="robots" content="noindex,follow">
  <link rel="canonical" href="${targetUrl}">
  <meta http-equiv="refresh" content="0; url=${targetUrl}">
  <title>Redirecting | Japan Trip Tools</title>
</head>
<body>
  <p>This page moved to <a href="${targetUrl}">${targetUrl}</a>.</p>
  <script>window.location.replace(${JSON.stringify(targetUrl)});</script>
</body>
</html>
`);
}

async function writeDestinationCompatibilityPages() {
    await exportPage('/destinations/');

    const regionsDir = path.join(outDir, 'regions');
    const entries = await readdir(regionsDir, { withFileTypes: true }).catch(() => []);

    for (const entry of entries) {
        if (! entry.isDirectory()) {
            continue;
        }

        const indexPath = path.join(regionsDir, entry.name, 'index.html');

        if (! await fileExists(indexPath)) {
            continue;
        }

        await writeRedirectPage(`/destinations/${entry.name}/`, `/regions/${entry.name}/`);
    }
}

async function fileExists(file) {
    try {
        await stat(file);

        return true;
    } catch {
        return false;
    }
}

async function listFiles(directory, prefix = '') {
    const entries = await readdir(directory, { withFileTypes: true });
    const files = [];

    for (const entry of entries) {
        if (entry.name === '.git') {
            continue;
        }

        const relative = path.join(prefix, entry.name);
        const absolute = path.join(directory, entry.name);

        if (entry.isDirectory()) {
            files.push(...await listFiles(absolute, relative));
            continue;
        }

        files.push(relative);
    }

    return files;
}

await emptyOutputDirectory();
await copyPublicAssets();
await seedFromSitemap();

while (queued.length > 0) {
    await exportPage(queued.shift());
}

await writeDestinationCompatibilityPages();
await writeNotFoundPage();
await assertNoLocalBaseLeaks();

console.log(JSON.stringify({
    localBase,
    siteUrl,
    outDir,
    exported: exported.length,
    missing,
}, null, 2));

if (missing.length > 0) {
    process.exitCode = 1;
}
