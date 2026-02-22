<?php

/**
 * Router file for PHP's built-in web server.
 *
 * This lets Laravel handle routes when running via:
 *   php -S 127.0.0.1:8000 server.php
 */

$uri = urldecode(parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH) ?? '');

if ($uri !== '/' && file_exists(__DIR__.'/public'.$uri)) {
    return false;
}

require_once __DIR__.'/public/index.php';
