<?php
declare(strict_types=1);

namespace App\Core;

use InvalidArgumentException;
use RuntimeException;

/**
 * SecurityValidator
 * Normalisasi URL, validasi skema, resolusi DNS, dan blokir SSRF
 * (private, loopback, link-local, reserved, metadata endpoint).
 * Mengembalikan IP publik terpilih untuk di-pin ke cURL via CURLOPT_RESOLVE.
 */
class SecurityValidator
{
    private const BLOCKED_HOSTS = [
        '169.254.169.254',
        'metadata.google.internal',
        'metadata',
        'instance-data',
    ];

    private const BLOCKED_IPV4_RANGES = [
        ['100.64.0.0', '100.127.255.255'],   // CGN
        ['192.0.0.0', '192.0.0.255'],        // IETF
        ['192.88.99.0', '192.88.99.255'],    // 6to4 relay
    ];

    /**
     * @return array{
     *   scheme:string, host:string, port:int,
     *   path:string, query:string, url:string, ip:string
     * }
     */
    public function normalize(string $input): array
    {
        $url = trim($input);
        if ($url === '') {
            throw new InvalidArgumentException('Target URL tidak boleh kosong.');
        }

        if (!preg_match('#^https?://#i', $url)) {
            $url = 'https://' . $url;
        }

        $parts = parse_url($url);
        if ($parts === false || empty($parts['host'])) {
            throw new InvalidArgumentException('Format URL tidak valid.');
        }

        $scheme = strtolower($parts['scheme'] ?? '');
        if (!in_array($scheme, ['http', 'https'], true)) {
            throw new InvalidArgumentException('Hanya skema http/https yang diizinkan.');
        }

        $host = strtolower(rtrim($parts['host'], '.'));
        if ($host === '') {
            throw new InvalidArgumentException('Host tidak valid.');
        }

        if (in_array($host, self::BLOCKED_HOSTS, true)) {
            throw new InvalidArgumentException('Cloud metadata endpoint diblokir.');
        }

        $defaultPort = $scheme === 'https' ? 443 : 80;
        $port = isset($parts['port']) ? (int) $parts['port'] : $defaultPort;
        if ($port < 1 || $port > 65535) {
            throw new InvalidArgumentException('Port di luar rentang valid.');
        }

        $path  = $parts['path'] ?? '/';
        $query = isset($parts['query']) ? '?' . $parts['query'] : '';

        // Kasus host = IP literal
        if (filter_var($host, FILTER_VALIDATE_IP)) {
            $this->assertPublicIp($host);
            return [
                'scheme' => $scheme,
                'host'   => $host,
                'port'   => $port,
                'path'   => $path,
                'query'  => $query,
                'url'    => $url,
                'ip'     => $host,
            ];
        }

        // Resolusi DNS A / AAAA
        $ips = $this->resolve($host);
        if (empty($ips)) {
            throw new RuntimeException("Gagal resolve hostname: {$host}");
        }

        $publicIps = [];
        foreach ($ips as $ip) {
            if ($this->isPublicIp($ip)) {
                $publicIps[] = $ip;
            }
        }
        if (empty($publicIps)) {
            throw new InvalidArgumentException('Hostname hanya resolve ke IP private/reserved (SSRF blocked).');
        }

        return [
            'scheme' => $scheme,
            'host'   => $host,
            'port'   => $port,
            'path'   => $path,
            'query'  => $query,
            'url'    => $url,
            'ip'     => $publicIps[0],
        ];
    }

    /**
     * @return string[]
     */
    public function resolve(string $host): array
    {
        $ips = [];

        $aRecords = @dns_get_record($host, DNS_A);
        if (is_array($aRecords)) {
            foreach ($aRecords as $record) {
                if (!empty($record['ip'])) {
                    $ips[] = (string) $record['ip'];
                }
            }
        }

        $aaaaRecords = @dns_get_record($host, DNS_AAAA);
        if (is_array($aaaaRecords)) {
            foreach ($aaaaRecords as $record) {
                if (!empty($record['ipv6'])) {
                    $ips[] = (string) $record['ipv6'];
                }
            }
        }

        return array_values(array_unique($ips));
    }

    public function isPublicIp(string $ip): bool
    {
        $flag = FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE;
        if (filter_var($ip, FILTER_VALIDATE_IP, $flag) === false) {
            return false;
        }

        // Blokir cloud metadata (defense-in-depth)
        if ($ip === '169.254.169.254') {
            return false;
        }

        // Blokir tambahan IPv4 (CGN, IETF, 6to4)
        $long = ip2long($ip);
        if ($long !== false) {
            foreach (self::BLOCKED_IPV4_RANGES as [$start, $end]) {
                $s = ip2long($start);
                $e = ip2long($end);
                if ($s !== false && $e !== false && $long >= $s && $long <= $e) {
                    return false;
                }
            }
        }

        return true;
    }

    private function assertPublicIp(string $ip): void
    {
        if (!$this->isPublicIp($ip)) {
            throw new InvalidArgumentException('Target IP adalah private/reserved/loopback/metadata (SSRF blocked).');
        }
    }
}
