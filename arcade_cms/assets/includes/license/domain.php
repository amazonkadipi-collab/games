<?php
declare(strict_types=1);

function gps_license_normalize_hostname(string $host): string
{
    $host = strtolower(trim($host));
    if ($host === '') {
        return '';
    }

    if ($host[0] === '[') {
        $end = strpos($host, ']');
        if ($end !== false) {
            $host = substr($host, 1, $end - 1);
        }
    } else {
        $host = preg_replace('/:\d+$/', '', $host) ?? $host;
    }

    $host = rtrim($host, '.');
    if (str_starts_with($host, 'www.')) {
        $host = substr($host, 4);
    }
    return preg_match('/^[a-z0-9.-]+$/', $host) ? $host : '';
}

function gps_license_exact_hostname(): string
{
    return gps_license_normalize_hostname(
        (string)($_SERVER['HTTP_HOST'] ?? $_SERVER['SERVER_NAME'] ?? '')
    );
}

function gps_license_domains_equivalent(string $left, string $right): bool
{
    $left = gps_license_normalize_hostname($left);
    $right = gps_license_normalize_hostname($right);
    return $left !== '' && $right !== '' && hash_equals($left, $right);
}
