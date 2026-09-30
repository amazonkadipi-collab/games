<?php
declare(strict_types=1);

function gps_discovery_normalize_text(string $text): string
{
    return trim((string)preg_replace('/\s+/u', ' ', html_entity_decode(strip_tags($text), ENT_QUOTES | ENT_HTML5, 'UTF-8')));
}

function gps_discovery_first_sentence(string $text, int $fallbackLength = 180): string
{
    $text = gps_discovery_normalize_text($text);
    if ($text === '') {
        return '';
    }

    if (preg_match('/^(.+?[.!?])(?:\s|$)/us', $text, $match)) {
        return trim($match[1]);
    }

    $length = function_exists('mb_strlen') ? mb_strlen($text, 'UTF-8') : strlen($text);
    if ($length <= $fallbackLength) {
        return $text;
    }

    $short = function_exists('mb_substr')
        ? mb_substr($text, 0, $fallbackLength, 'UTF-8')
        : substr($text, 0, $fallbackLength);
    $short = preg_replace('/\s+\S*$/u', '', $short) ?: $short;
    return rtrim($short, " \t\n\r\0\x0B,;:-") . '…';
}

function gps_discovery_remove_sentence_from_html(string $html, string $sentence): string
{
    if ($html === '' || $sentence === '' || !class_exists('DOMDocument')) {
        return $html;
    }

    $document = new DOMDocument('1.0', 'UTF-8');
    $previous = libxml_use_internal_errors(true);
    $loaded = $document->loadHTML(
        '<?xml encoding="utf-8" ?><div id="gps-description-root">' . $html . '</div>',
        LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD
    );
    libxml_clear_errors();
    libxml_use_internal_errors($previous);
    if (!$loaded) {
        return $html;
    }

    $root = $document->getElementById('gps-description-root');
    if (!$root) {
        return $html;
    }

    $xpath = new DOMXPath($document);
    $nodes = $xpath->query('.//text()[normalize-space() and not(ancestor::h1) and not(ancestor::h2) and not(ancestor::h3) and not(ancestor::h4) and not(ancestor::h5) and not(ancestor::h6) and not(ancestor::script) and not(ancestor::style)]', $root);
    if ($nodes) {
        $quoted = preg_quote($sentence, '/');
        $quoted = str_replace(['\\ ', '\\ '], ['\\s+', '\\s+'], $quoted);
        $pattern = '/^\s*' . $quoted . '\s*/u';
        foreach ($nodes as $node) {
            $value = (string)$node->nodeValue;
            $updated = preg_replace($pattern, '', $value, 1, $count);
            if ($count > 0) {
                $node->nodeValue = (string)$updated;
                break;
            }
        }
    }

    $emptyParagraphs = $xpath->query('.//p[not(normalize-space())]', $root);
    if ($emptyParagraphs) {
        $remove = [];
        foreach ($emptyParagraphs as $paragraph) {
            $remove[] = $paragraph;
        }
        foreach ($remove as $paragraph) {
            $paragraph->parentNode?->removeChild($paragraph);
        }
    }

    $result = '';
    foreach ($root->childNodes as $child) {
        $result .= (string)$document->saveHTML($child);
    }
    return trim($result);
}

function gps_discovery_split_description(string $html): array
{
    $cleanHtml = trim(htmlspecialchars_decode($html, ENT_QUOTES));
    $withoutHeadings = (string)preg_replace('/<h[1-6][^>]*>.*?<\/h[1-6]>/is', '', $cleanHtml);
    $sentence = gps_discovery_first_sentence($withoutHeadings);

    return [
        'header' => htmlspecialchars($sentence, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8'),
        'footer' => gps_discovery_remove_sentence_from_html($cleanHtml, $sentence),
    ];
}

function gps_discovery_assign_page_description(array &$themeData, string $pageName): void
{
    $footerDescription = getFooterDescription($pageName);
    $description = isset($footerDescription->description)
        ? htmlspecialchars_decode((string)$footerDescription->description)
        : '';
    $parts = gps_discovery_split_description($description);

    $themeData['footer_description'] = $description;
    $themeData['header_desc'] = $parts['header'];
    $themeData['footer_description_modified'] = $parts['footer'];
}
