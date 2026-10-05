<?php
// Fast, cacheable SEO endpoints for the public Vercel deployment.
$arcadeRoot = dirname(__DIR__) . '/arcade_cms';
chdir($arcadeRoot);
require $arcadeRoot . '/gm-load.php';

$siteUrl = 'https://pokicrazygames.vercel.app';
$path = trim((string)parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH), '/');

header('Cache-Control: public, max-age=300, s-maxage=3600, stale-while-revalidate=86400');
header('CDN-Cache-Control: public, max-age=3600, stale-while-revalidate=86400');

function seo_xml($value) {
    return htmlspecialchars((string)$value, ENT_XML1 | ENT_QUOTES, 'UTF-8');
}

function seo_output($xml) {
    header('Content-Type: application/xml; charset=UTF-8');
    echo $xml;
    exit;
}

if ($path === 'robots.txt') {
    header('Content-Type: text/plain; charset=UTF-8');
    echo "User-agent: *\nAllow: /\nDisallow: /admin\nDisallow: /assets/includes/\n\nSitemap: {$siteUrl}/sitemap.xml\n";
    exit;
}

if ($path === 'llms.txt') {
    header('Content-Type: text/plain; charset=UTF-8');
    echo "# PlayGrid Games\n\n> Free online browser games portal with a large catalog of playable HTML5 games.\n\n## Key URLs\n- Home: {$siteUrl}/\n- Sitemap: {$siteUrl}/sitemap.xml\n- Categories: {$siteUrl}/categories\n- New games: {$siteUrl}/new-games\n- Best games: {$siteUrl}/best-games\n- Popular games: {$siteUrl}/popular\n- All games: {$siteUrl}/all-games\n\n## Crawling\n- Public game pages use stable /game/{slug} URLs.\n- Use the XML sitemap for complete game URL discovery.\n- Do not crawl /admin or internal include paths.\n";
    exit;
}

if ($path === 'sitemap.xml') {
    $xml = '<?xml version="1.0" encoding="UTF-8"?>';
    $xml .= '<sitemapindex xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">';
    $xml .= '<sitemap><loc>' . seo_xml($siteUrl . '/sitemaps/static.xml') . '</loc></sitemap>';

    $gameCount = 0;
    if (isset($GameMonetizeConnect)) {
        $result = $GameMonetizeConnect->query("SELECT COUNT(*) AS total FROM " . GAMES . " WHERE published='1' AND game_id IS NOT NULL AND name IS NOT NULL AND TRIM(name) <> ''");
        if ($result && ($row = $result->fetch_assoc())) {
            $gameCount = max(0, (int)($row['total'] ?? 0));
        }
    }

    $pages = max(1, (int)ceil($gameCount / 5000));
    for ($i = 1; $i <= $pages; $i++) {
        $xml .= '<sitemap><loc>' . seo_xml($siteUrl . '/sitemaps/games-' . $i . '.xml') . '</loc></sitemap>';
    }
    $xml .= '</sitemapindex>';
    seo_output($xml);
}

if ($path === 'sitemaps/static.xml') {
    $urls = ['/', '/categories', '/new-games', '/popular', '/all-games', '/random', '/featured-games', '/about', '/privacy', '/terms', '/tags'];

    if (isset($GameMonetizeConnect)) {
        $catResult = $GameMonetizeConnect->query("SELECT name FROM " . CATEGORIES . " ORDER BY id ASC");
        if ($catResult) {
            while ($cat = $catResult->fetch_assoc()) {
                $slug = slugify((string)($cat['name'] ?? ''));
                if ($slug !== '') $urls[] = '/category/' . $slug;
            }
        }
        $tagResult = $GameMonetizeConnect->query("SELECT name, url FROM " . TAGS . " ORDER BY id ASC");
        if ($tagResult) {
            while ($tag = $tagResult->fetch_assoc()) {
                $slug = trim((string)($tag['url'] ?? ''));
                if ($slug === '') $slug = slugify((string)($tag['name'] ?? ''));
                if ($slug !== '') $urls[] = '/tag/' . $slug;
            }
        }
    }

    $urls = array_values(array_unique($urls));
    $xml = '<?xml version="1.0" encoding="UTF-8"?><urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">';
    foreach ($urls as $url) {
        $xml .= '<url><loc>' . seo_xml($siteUrl . $url) . '</loc></url>';
    }
    $xml .= '</urlset>';
    seo_output($xml);
}

if (preg_match('~^sitemaps/games-(\\d+)\\.xml$~', $path, $match)) {
    $page = max(1, (int)$match[1]);
    $offset = ($page - 1) * 5000;
    $xml = '<?xml version="1.0" encoding="UTF-8"?><urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">';

    if (isset($GameMonetizeConnect)) {
        $query = $GameMonetizeConnect->query("SELECT game_id, name, date_added FROM " . GAMES . " WHERE published='1' AND game_id IS NOT NULL AND name IS NOT NULL AND TRIM(name) <> '' ORDER BY game_id ASC LIMIT 5000 OFFSET {$offset}");
        if ($query) {
            while ($game = $query->fetch_assoc()) {
                $name = trim((string)($game['name'] ?? ''));
                $id = (int)($game['game_id'] ?? 0);
                if ($id <= 0 || $name === '') continue;
                $slug = slugify($name);
                if ($slug === '') continue;
                $xml .= '<url><loc>' . seo_xml($siteUrl . '/game/' . rawurlencode($slug)) . '</loc>';
                $date = $game['date_added'] ?? null;
                if ($date !== null && $date !== '' && is_numeric($date)) {
                    $xml .= '<lastmod>' . seo_xml(date('c', (int)$date)) . '</lastmod>';
                }
                $xml .= '</url>';
            }
        }
    }

    $xml .= '</urlset>';
    seo_output($xml);
}

http_response_code(404);
header('Content-Type: text/plain; charset=UTF-8');
echo 'Not found';
