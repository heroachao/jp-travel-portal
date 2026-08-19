<?php

declare(strict_types=1);

use App\Models\Article;
use Illuminate\Http\Request;

const ADS_TXT_CONTENT = "google.com, pub-3754179629894278, DIRECT, f08c47fec0942fa0\n";

require __DIR__.'/../vendor/autoload.php';

$app = require __DIR__.'/../bootstrap/app.php';

$siteUrl = rtrim(getenv('SITE_URL') ?: 'https://japantriptools.com', '/');
$outDir = getenv('OUT_DIR') ?: dirname(__DIR__).'/dist-static';
$publicDir = dirname(__DIR__).'/public';
$maxPages = (int) (getenv('MAX_PAGES') ?: 2000);
$fetched = [];
$queued = [];
$missing = [];
$exported = [];

function normalizePathname(string $href, string $siteUrl, string $basePath = '/'): ?string
{
    if ($href === '' || str_starts_with($href, '#') || preg_match('/^(mailto|tel|javascript):/i', $href)) {
        return null;
    }

    if (str_starts_with($href, 'http')) {
        $absolute = $href;
    } elseif (str_starts_with($href, '/')) {
        $absolute = $siteUrl.$href;
    } else {
        $absolute = $siteUrl.rtrim(dirname($basePath), '/').'/'.$href;
    }

    $url = parse_url($absolute);
    $site = parse_url($siteUrl);

    if (($url['host'] ?? null) !== ($site['host'] ?? null)) {
        return null;
    }

    $path = $url['path'] ?? '/';
    if (($url['query'] ?? '') !== '' && $path !== '/search') {
        return null;
    }

    return $path === '//' ? '/' : $path;
}

function outputPathFor(string $outDir, string $pathname): string
{
    if ($pathname === '/') {
        return $outDir.'/index.html';
    }

    $clean = ltrim($pathname, '/');
    if (str_contains(basename($clean), '.')) {
        return $outDir.'/'.$clean;
    }

    return $outDir.'/'.$clean.'/index.html';
}

function recursiveCopy(string $source, string $target): void
{
    if (! is_dir($target)) {
        mkdir($target, 0777, true);
    }

    foreach (scandir($source) ?: [] as $entry) {
        if (in_array($entry, ['.', '..', 'index.php', '.htaccess'], true)) {
            continue;
        }

        $from = $source.'/'.$entry;
        $to = $target.'/'.$entry;

        if (is_dir($from)) {
            recursiveCopy($from, $to);
            continue;
        }

        if (! is_dir(dirname($to))) {
            mkdir(dirname($to), 0777, true);
        }
        copy($from, $to);
    }
}

function readRequiredAdsTxt(string $publicDir): string
{
    $sourcePath = $publicDir.'/ads.txt';
    $body = is_file($sourcePath) ? file_get_contents($sourcePath) : false;

    if ($body === false) {
        throw new RuntimeException("Required root file is missing: {$sourcePath}");
    }

    if ($body !== ADS_TXT_CONTENT) {
        throw new RuntimeException('public/ads.txt does not contain the approved AdSense authorization record.');
    }

    return $body;
}

function preserveRequiredRootFiles(string $outDir, string $adsTxtBody): void
{
    $outputPath = $outDir.'/ads.txt';

    if (file_put_contents($outputPath, $adsTxtBody) === false || file_get_contents($outputPath) !== ADS_TXT_CONTENT) {
        throw new RuntimeException('Static export is missing the approved ads.txt authorization record.');
    }
}

function emptyOutputDirectory(string $outDir, string $siteUrl): void
{
    if (! is_dir($outDir)) {
        mkdir($outDir, 0777, true);
    }

    foreach (scandir($outDir) ?: [] as $entry) {
        if (in_array($entry, ['.', '..', '.git'], true)) {
            continue;
        }

        $path = $outDir.'/'.$entry;
        if (is_dir($path)) {
            $iterator = new RecursiveIteratorIterator(
                new RecursiveDirectoryIterator($path, FilesystemIterator::SKIP_DOTS),
                RecursiveIteratorIterator::CHILD_FIRST
            );
            foreach ($iterator as $item) {
                $item->isDir() ? rmdir((string) $item) : unlink((string) $item);
            }
            rmdir($path);
        } else {
            unlink($path);
        }
    }

    file_put_contents($outDir.'/.nojekyll', '');
    file_put_contents($outDir.'/CNAME', parse_url($siteUrl, PHP_URL_HOST));
}

function renderPath($app, string $pathname): array
{
    $request = Request::create($pathname, 'GET', [], [], [], [
        'HTTP_HOST' => 'japantriptools.com',
        'HTTPS' => 'on',
    ]);

    $response = $app->handle($request);
    $content = $response->getContent();
    $status = $response->getStatusCode();
    $headers = $response->headers;
    $app->terminate($request, $response);

    return [$status, (string) $content, (string) $headers->get('content-type', ''), (string) $headers->get('location', '')];
}

$enqueue = function (?string $pathname) use (&$queued, &$fetched, $maxPages): void {
    if ($pathname === null || isset($fetched[$pathname]) || in_array($pathname, $queued, true)) {
        return;
    }
    if (count($queued) + count($fetched) >= $maxPages) {
        return;
    }
    $queued[] = $pathname;
};

$requiredAdsTxt = readRequiredAdsTxt($publicDir);

emptyOutputDirectory($outDir, $siteUrl);
recursiveCopy($publicDir, $outDir);

[$status, $sitemap] = renderPath($app, '/sitemap.xml');
if ($status !== 200) {
    fwrite(STDERR, "Could not render /sitemap.xml, status {$status}\n");
    exit(1);
}

preg_match_all('/<loc>(.*?)<\/loc>/', $sitemap, $matches);
foreach (array_merge(['/'], $matches[1], ['/sitemap.xml', '/robots.txt', '/ads.txt', '/llms.txt']) as $href) {
    $enqueue(normalizePathname($href, $siteUrl));
}

// Keep published noindex articles available so old search results and saved
// links do not become 404s while Google phases them out of the index.
foreach (Article::query()->published()->orderBy('id')->pluck('slug') as $slug) {
    $enqueue('/articles/'.rawurlencode((string) $slug).'/');
}

while ($queued !== []) {
    $pathname = array_shift($queued);
    $fetched[$pathname] = true;
    [$status, $body, $contentType, $location] = renderPath($app, $pathname);

    if ($status >= 300 && $status < 400 && $location !== '') {
        $targetPath = parse_url($location, PHP_URL_PATH) ?: '/';
        $targetUrl = $siteUrl.$targetPath;
        $outputPath = outputPathFor($outDir, $pathname);
        if (! is_dir(dirname($outputPath))) {
            mkdir(dirname($outputPath), 0777, true);
        }
        file_put_contents($outputPath, '<!doctype html><html lang="en"><head><meta charset="utf-8"><meta name="robots" content="noindex,follow"><link rel="canonical" href="'.$targetUrl.'"><meta http-equiv="refresh" content="0; url='.$targetUrl.'"><title>Redirecting | Japan Trip Tools</title></head><body><p>This page moved to <a href="'.$targetUrl.'">'.$targetUrl.'</a>.</p></body></html>');
        $exported[] = $pathname;
        continue;
    }

    if ($status < 200 || $status >= 300) {
        $missing[] = ['pathname' => $pathname, 'status' => $status];
        continue;
    }

    $outputPath = outputPathFor($outDir, $pathname);
    if (! is_dir(dirname($outputPath))) {
        mkdir(dirname($outputPath), 0777, true);
    }
    file_put_contents($outputPath, $body);
    $exported[] = $pathname;

    if (str_contains($contentType, 'text/html')) {
        preg_match_all('/\bhref=(["\'])(.*?)\1/i', $body, $links);
        foreach ($links[2] as $href) {
            $linkedPath = normalizePathname($href, $siteUrl, $pathname);
            if ($linkedPath !== null && ($linkedPath === '/' || ! str_contains(basename($linkedPath), '.'))) {
                $enqueue($linkedPath);
            }
        }
    }
}

file_put_contents($outDir.'/404.html', '<!doctype html><html lang="en"><head><meta charset="utf-8"><meta name="robots" content="noindex,nofollow"><meta name="description" content="This Japan Trip Tools page is not available; use the guide index, region hub, travel tools, or search page to continue planning your Japan trip."><title>Page Not Found | Japan Trip Tools</title></head><body><main><h1>This Japan Trip Tools page is not available.</h1><p><a href="'.$siteUrl.'/articles/">Articles</a></p></main></body></html>');
preserveRequiredRootFiles($outDir, $requiredAdsTxt);

echo json_encode([
    'siteUrl' => $siteUrl,
    'outDir' => $outDir,
    'exported' => count($exported),
    'missing' => $missing,
], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES).PHP_EOL;

if ($missing !== []) {
    exit(1);
}
