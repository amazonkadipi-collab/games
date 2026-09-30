<?php
declare(strict_types=1);

function gps_license_api_post(string $endpoint, array $payload): array
{
    $url = rtrim(GPS_LICENSE_API_BASE, '/') . '/' . ltrim($endpoint, '/');
    $body = json_encode($payload, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);

    if (!is_string($body)) {
        return ['ok' => false, 'status' => 0, 'data' => null, 'error' => 'Could not encode request.'];
    }

    if (function_exists('curl_init')) {
        $ch = curl_init($url);
        curl_setopt_array($ch, [
            CURLOPT_POST => true,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HTTPHEADER => ['Content-Type: application/json', 'Accept: application/json'],
            CURLOPT_POSTFIELDS => $body,
            CURLOPT_CONNECTTIMEOUT => GPS_LICENSE_HTTP_TIMEOUT,
            CURLOPT_TIMEOUT => GPS_LICENSE_HTTP_TIMEOUT,
            CURLOPT_SSL_VERIFYPEER => true,
            CURLOPT_SSL_VERIFYHOST => 2,
            CURLOPT_USERAGENT => 'GamePortalScript-CMS-License/1.0',
        ]);

        $raw = curl_exec($ch);
        $error = curl_error($ch);
        $status = (int)curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if (!is_string($raw)) {
            return ['ok' => false, 'status' => $status, 'data' => null, 'error' => $error ?: 'License API request failed.'];
        }
    } else {
        $context = stream_context_create([
            'http' => [
                'method' => 'POST',
                'header' => "Content-Type: application/json\r\nAccept: application/json\r\nUser-Agent: GamePortalScript-CMS-License/1.0\r\n",
                'content' => $body,
                'timeout' => GPS_LICENSE_HTTP_TIMEOUT,
                'ignore_errors' => true,
            ],
            'ssl' => ['verify_peer' => true, 'verify_peer_name' => true],
        ]);
        $raw = @file_get_contents($url, false, $context);
        $status = 0;
        if (!empty($http_response_header[0]) && preg_match('/\s(\d{3})\s/', $http_response_header[0], $match)) {
            $status = (int)$match[1];
        }
        if (!is_string($raw)) {
            return ['ok' => false, 'status' => $status, 'data' => null, 'error' => 'License API request failed.'];
        }
    }

    $data = json_decode($raw, true);
    if (!is_array($data)) {
        return ['ok' => false, 'status' => $status, 'data' => null, 'error' => 'License API returned invalid JSON.'];
    }

    return ['ok' => $status >= 200 && $status < 300, 'status' => $status, 'data' => $data, 'error' => null];
}
