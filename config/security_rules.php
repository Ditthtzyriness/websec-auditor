<?php
declare(strict_types=1);

/**
 * config/security_rules.php
 * Daftar 7 HTTP security header wajib + bobot + severity + deskripsi risiko.
 * Total weight = 100 (untuk normalisasi skor internal HeaderAnalyzer).
 */

return [
    'strict-transport-security' => [
        'label'       => 'Strict-Transport-Security',
        'weight'      => 20,
        'severity'    => 'High',
        'description' => 'Memaksa browser hanya berkomunikasi via HTTPS, mencegah SSL stripping & MITM.',
        'expected'    => 'max-age=31536000; includeSubDomains',
    ],
    'content-security-policy' => [
        'label'       => 'Content-Security-Policy',
        'weight'      => 20,
        'severity'    => 'High',
        'description' => 'Membatasi resource loading ke origin terpercaya; mitigasi XSS, clickjacking, dan data injection.',
        'expected'    => "default-src 'self'",
    ],
    'x-frame-options' => [
        'label'       => 'X-Frame-Options',
        'weight'      => 12,
        'severity'    => 'Medium',
        'description' => 'Mencegah halaman dibingkai pada iframe, memblokir clickjacking.',
        'expected'    => 'DENY atau SAMEORIGIN',
    ],
    'x-content-type-options' => [
        'label'       => 'X-Content-Type-Options',
        'weight'      => 12,
        'severity'    => 'Medium',
        'description' => 'Menonaktifkan MIME sniffing sehingga file berbahaya tidak dieksekusi sebagai tipe lain.',
        'expected'    => 'nosniff',
    ],
    'referrer-policy' => [
        'label'       => 'Referrer-Policy',
        'weight'      => 12,
        'severity'    => 'Medium',
        'description' => 'Mengontrol informasi referrer yang dikirim ke pihak ketiga, melindungi privasi user.',
        'expected'    => 'strict-origin-when-cross-origin',
    ],
    'permissions-policy' => [
        'label'       => 'Permissions-Policy',
        'weight'      => 12,
        'severity'    => 'Low',
        'description' => 'Membatasi fitur browser (kamera, mikrofon, geolocation) yang bisa dipakai halaman.',
        'expected'    => 'geolocation=(), microphone=(), camera=()',
    ],
    'cross-origin-opener-policy' => [
        'label'       => 'Cross-Origin-Opener-Policy',
        'weight'      => 12,
        'severity'    => 'Low',
        'description' => 'Mengisolasi browsing context dari window cross-origin, mitigasi serangan XS-Leaks.',
        'expected'    => 'same-origin',
    ],
];
