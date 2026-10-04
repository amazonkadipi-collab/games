<?php
// Vercel PHP entrypoint for the Arcade CMS.
// Run from the CMS root so all legacy relative paths (templates/, assets/, language/) resolve correctly.
// Runtime is provided by vercel-php 0.9.
$arcadeRoot = dirname(__DIR__) . '/arcade_cms';
chdir($arcadeRoot);
require $arcadeRoot . '/index.php';
