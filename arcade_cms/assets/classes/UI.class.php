<?php

namespace GameMonetize;

class UI {
    private $filename;

    public static function view()
    {
        global $config;

        $params = func_get_args();
        $file = isset($params[0]) ? (string) $params[0] : '';

        if ($file === '') {
            return '';
        }

        $theme = isset($config['site_theme']) ? trim((string) $config['site_theme']) : '';
        if ($theme === '') {
            error_log('[Arcade CMS] UI::view skipped: site_theme is empty for template ' . $file);
            return '';
        }

        $page = dirname(dirname(dirname(__FILE__))) . '/templates/' . $theme . '/content/' . $file . '.php';

        // Keep the configured theme intact. A missing/unreadable partial must
        // not kill the whole public request with fread(false, ...).
        if (!is_file($page) || !is_readable($page)) {
            error_log('[Arcade CMS] UI template unavailable: ' . $page . ' (requested: ' . $file . ')');
            return '';
        }

        $size = filesize($page);
        if ($size === false) {
            error_log('[Arcade CMS] UI template size unavailable: ' . $page);
            return '';
        }

        $contentOpen = fopen($page, 'rb');
        if ($contentOpen === false) {
            error_log('[Arcade CMS] UI template could not be opened: ' . $page);
            return '';
        }

        $content = fread($contentOpen, $size);
        fclose($contentOpen);

        if ($content === false) {
            error_log('[Arcade CMS] UI template could not be read: ' . $page);
            return '';
        }

        if (!empty($params[1]))
        {
            $uikey = $params[1];

            unset($params[0]);
            unset($params[1]);

            if (!isset($params[2]))
            {
                $params[2] = array();
            }

            if (!isset($params[3]))
            {
                $params[3] = array();
            }
        }

        $content = preg_replace_callback(
            '/@([a-zA-Z0-9_]+)@/',
            function ($matches)
            {
                global $lang;
                $matches[1] = strtolower($matches[1]);
                return (isset($lang[$matches[1]]) ? $lang[$matches[1]] : "");
            },
            $content
        );

        $content = preg_replace_callback(
            '/{{([A-Z0-9_]+)}}/',
            function ($matches)
            {
                global $themeData;
                $matches[1] = strtolower($matches[1]);
                return (isset($themeData[$matches[1]]) ? $themeData[$matches[1]] : "");
            },
            $content
        );

        return $content;
    }
}