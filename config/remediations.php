<?php
declare(strict_types=1);

/**
 * config/remediations.php
 * Template perbaikan (copy-paste) untuk 3 stack: Nginx, Apache (.htaccess), PHP Native.
 */

return [
    'strict-transport-security' => [
        'nginx'  => 'add_header Strict-Transport-Security "max-age=31536000; includeSubDomains; preload" always;',
        'apache' => 'Header always set Strict-Transport-Security "max-age=31536000; includeSubDomains; preload"',
        'php'    => "header('Strict-Transport-Security: max-age=31536000; includeSubDomains; preload');",
    ],
    'content-security-policy' => [
        'nginx'  => "add_header Content-Security-Policy \"default-src 'self'; script-src 'self'; style-src 'self' 'unsafe-inline'; img-src 'self' data:; object-src 'none'; base-uri 'self'; frame-ancestors 'none'\" always;",
        'apache' => "Header always set Content-Security-Policy \"default-src 'self'; script-src 'self'; style-src 'self' 'unsafe-inline'; img-src 'self' data:; object-src 'none'; base-uri 'self'; frame-ancestors 'none'\"",
        'php'    => "header(\"Content-Security-Policy: default-src 'self'; script-src 'self'; style-src 'self' 'unsafe-inline'; img-src 'self' data:; object-src 'none'; base-uri 'self'; frame-ancestors 'none'\");",
    ],
    'x-frame-options' => [
        'nginx'  => 'add_header X-Frame-Options "SAMEORIGIN" always;',
        'apache' => 'Header always set X-Frame-Options "SAMEORIGIN"',
        'php'    => "header('X-Frame-Options: SAMEORIGIN');",
    ],
    'x-content-type-options' => [
        'nginx'  => 'add_header X-Content-Type-Options "nosniff" always;',
        'apache' => 'Header always set X-Content-Type-Options "nosniff"',
        'php'    => "header('X-Content-Type-Options: nosniff');",
    ],
    'referrer-policy' => [
        'nginx'  => 'add_header Referrer-Policy "strict-origin-when-cross-origin" always;',
        'apache' => 'Header always set Referrer-Policy "strict-origin-when-cross-origin"',
        'php'    => "header('Referrer-Policy: strict-origin-when-cross-origin');",
    ],
    'permissions-policy' => [
        'nginx'  => 'add_header Permissions-Policy "geolocation=(), microphone=(), camera=()" always;',
        'apache' => 'Header always set Permissions-Policy "geolocation=(), microphone=(), camera=()"',
        'php'    => "header('Permissions-Policy: geolocation=(), microphone=(), camera=()');",
    ],
    'cross-origin-opener-policy' => [
        'nginx'  => 'add_header Cross-Origin-Opener-Policy "same-origin" always;',
        'apache' => 'Header always set Cross-Origin-Opener-Policy "same-origin"',
        'php'    => "header('Cross-Origin-Opener-Policy: same-origin');",
    ],
];
