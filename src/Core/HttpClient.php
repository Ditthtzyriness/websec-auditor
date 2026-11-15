<?php
declare(strict_types=1);

namespace App\Core;

use RuntimeException;

/**
 * HttpClient
 * Eksekutor cURL dengan:
 * - CURLOPT_RESOLVE (pin IP publik → anti DNS rebinding)
 * - FOLLOWLOCATION maks 5x, timeout 6 detik
 * - Pengukuran TTFB
 * - Parser header yang HANYA mengambil blok HTTP terakhir setelah redirect
 * - Normalisasi nama header ke huruf kecil
 */
class HttpClient
{
    private const MAX_REDIRECTS = 5;
    private const TIMEOUT = 6;

    /**
     * @return array{
     *   status:int,
     *   headers:array<string,mixed>,
     *   raw_headers:string[],
     *   ttfb:float,
     *   effective_url:string
     * }
     */
    public function fetch(string $url, string $pinnedIp, string $host, int $port): array
    {
        $ch = curl_init();
        if ($ch === false) {
            throw new RuntimeException('Gagal inisialisasi cURL.');
        }

        $rawHeaders = '';
        $resolveEntry = "{$host}:{$port}:{$pinnedIp}";
        $start = microtime(true);

        curl_setopt_array($ch, [
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => false,
            CURLOPT_HEADER => false,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_MAXREDIRS => self::MAX_REDIRECTS,
            CURLOPT_TIMEOUT => self::TIMEOUT,
            CURLOPT_CONNECTTIMEOUT => self::TIMEOUT,
            CURLOPT_SSL_VERIFYPEER => true,
            CURLOPT_SSL_VERIFYHOST => 2,
            CURLOPT_RESOLVE => [$resolveEntry],
            CURLOPT_USERAGENT => 'WebSecAuditor/1.0 (+https://websec-auditor.local)',
            CURLOPT_HTTPHEADER => [
                'Accept: text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8',
                'Accept-Language: en-US,en;q=0.5',
                'Cache-Control: no-cache',
                'Connection: close',
            ],
            CURLOPT_WRITEFUNCTION => static function ($ch, string $chunk): int {
                // discard body, keep transferring
                return strlen($chunk);
            },
            CURLOPT_HEADERFUNCTION => static function ($ch, string $line) use (&$rawHeaders): int {
                $rawHeaders .= $line;
                return strlen($line);
            },
        ]);

        curl_exec($ch);

        $elapsed = microtime(true) - $start;
        $errno = curl_errno($ch);
        $error = curl_error($ch);
        $statusCode = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $effectiveUrl = (string) curl_getinfo($ch, CURLINFO_EFFECTIVE_URL);
        $startTransfer = (float) curl_getinfo($ch, CURLINFO_STARTTRANSFER_TIME);
        $totalTime = (float) curl_getinfo($ch, CURLINFO_TOTAL_TIME);
        curl_close($ch);

        // Timeout pada body tapi header sudah diterima → tetap lanjut
        if ($errno !== 0 && $statusCode === 0) {
            throw new RuntimeException("HTTP request gagal: {$error} (errno {$errno})");
        }

        $headers = $this->parseLastHeaderBlock($rawHeaders);
        $ttfb = $startTransfer > 0 ? $startTransfer : ($totalTime > 0 ? $totalTime : $elapsed);

        return [
            'status' => $statusCode,
            'headers' => $headers,
            'raw_headers' => $this->splitBlocks($rawHeaders),
            'ttfb' => round($ttfb * 1000, 2),
            'effective_url' => $effectiveUrl,
        ];
    }

    /**
     * @return string[]
     */
    private function splitBlocks(string $rawHeaders): array
    {
        $trimmed = trim($rawHeaders);
        if ($trimmed === '') {
            return [];
        }
        $parts = preg_split("/\r?\n\r?\n/", $trimmed);
        return is_array($parts) ? $parts : [];
    }

    /**
     * @return array<string,mixed>
     */
    private function parseLastHeaderBlock(string $rawHeaders): array
    {
        $blocks = $this->splitBlocks($rawHeaders);
        if (empty($blocks)) {
            return [];
        }

        // Ambil blok terakhir yang dimulai dengan baris status HTTP
        $lastBlock = '';
        foreach ($blocks as $block) {
            if (preg_match('#^HTTP/#i', $block)) {
                $lastBlock = $block;
            }
        }
        if ($lastBlock === '') {
            $lastBlock = (string) end($blocks);
        }

        $headers = [];
        $lines = preg_split("/\r?\n/", $lastBlock);
        if (!is_array($lines)) {
            return $headers;
        }

        foreach ($lines as $line) {
            if (strpos($line, ':') === false) {
                continue;
            }
            [$name, $value] = explode(':', $line, 2);
            $name = strtolower(trim($name));
            $value = trim($value);
            if ($name === '') {
                continue;
            }

            if ($name === 'set-cookie') {
                if (!isset($headers['set-cookie']) || !is_array($headers['set-cookie'])) {
                    $headers['set-cookie'] = [];
                }
                $headers['set-cookie'][] = $value;
                continue;
            }

            if (isset($headers[$name]) && is_string($headers[$name])) {
                $headers[$name] .= ', ' . $value;
            } else {
                $headers[$name] = $value;
            }
        }

        return $headers;
    }
}
