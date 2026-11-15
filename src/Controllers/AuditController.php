<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Analyzers\CookieAnalyzer;
use App\Analyzers\CorsAndLeakageAnalyzer;
use App\Analyzers\DnsSecurityAnalyzer;
use App\Analyzers\HeaderAnalyzer;
use App\Analyzers\SslAnalyzer;
use App\Core\HttpClient;
use App\Core\ScoringEngine;
use App\Core\SecurityValidator;
use App\Helpers\BadgeGenerator;
use InvalidArgumentException;
use RuntimeException;
use Throwable;

/**
 * AuditController
 * Orkestrasi: validasi → HTTP fetch → 5 analyzer → scoring → badge.
 */
class AuditController
{
    private SecurityValidator $validator;
    private HttpClient $http;
    private HeaderAnalyzer $headers;
    private SslAnalyzer $ssl;
    private CookieAnalyzer $cookies;
    private CorsAndLeakageAnalyzer $cors;
    private DnsSecurityAnalyzer $dns;
    private ScoringEngine $scoring;
    private BadgeGenerator $badge;

    public function __construct()
    {
        $this->validator = new SecurityValidator();
        $this->http = new HttpClient();
        $this->headers = new HeaderAnalyzer();
        $this->ssl = new SslAnalyzer();
        $this->cookies = new CookieAnalyzer();
        $this->cors = new CorsAndLeakageAnalyzer();
        $this->dns = new DnsSecurityAnalyzer();
        $this->scoring = new ScoringEngine();
        $this->badge = new BadgeGenerator();
    }

    /**
     * @return array<string,mixed>
     * @throws InvalidArgumentException
     * @throws RuntimeException
     */
    public function audit(string $target): array
    {
        $startedAt = microtime(true);

        $meta = $this->validator->normalize($target);
        $host = $meta['host'];
        $port = (int) $meta['port'];
        $scheme = $meta['scheme'];

        $response = $this->http->fetch($meta['url'], $meta['ip'], $host, $port);
        $headers = $response['headers'];

        $headerResult = $this->headers->analyze($headers);
        $cookieResult = $this->cookies->analyze($headers);
        $corsResult   = $this->cors->analyze($headers);
        $dnsResult    = $this->dns->analyze($host);

        $sslResult = $scheme === 'https'
            ? $this->ssl->analyze($host, $port)
            : [
                'ok' => false,
                'score' => 0.0,
                'subject' => null,
                'issuer' => null,
                'valid_from' => null,
                'valid_to' => null,
                'days_remaining' => null,
                'tls_version' => null,
                'error' => 'Target menggunakan skema HTTP (tanpa TLS).',
            ];

        $scoring = $this->scoring->evaluate([
            'headers' => $headerResult,
            'ssl'     => $sslResult,
            'cookies' => $cookieResult,
            'cors'    => $corsResult,
            'dns'     => $dnsResult,
        ]);

        $badge = $this->badge->generate($host, $scoring['score'], $scoring['grade']);

        $elapsed = round((microtime(true) - $startedAt) * 1000, 2);

        return [
            'target' => [
                'input' => $target,
                'url' => $meta['url'],
                'host' => $host,
                'port' => $port,
                'scheme' => $scheme,
                'pinned_ip' => $meta['ip'],
            ],
            'http' => [
                'status' => $response['status'],
                'ttfb_ms' => $response['ttfb'],
                'effective_url' => $response['effective_url'],
                'redirect_blocks' => count($response['raw_headers']),
            ],
            'headers' => $headerResult,
            'ssl' => $sslResult,
            'cookies' => $cookieResult,
            'cors' => $corsResult,
            'dns' => $dnsResult,
            'scoring' => $scoring,
            'badge' => $badge,
            'meta' => [
                'scanned_at' => gmdate('c'),
                'duration_ms' => $elapsed,
                'version' => '1.0.0',
            ],
        ];
    }

    /**
     * Wrapper aman untuk endpoint: memetakan exception ke status HTTP.
     *
     * @return array{status:int, body:array<string,mixed>}
     */
    public function handle(string $target): array
    {
        try {
            $result = $this->audit($target);
            return ['status' => 200, 'body' => ['ok' => true, 'data' => $result]];
        } catch (InvalidArgumentException $e) {
            return ['status' => 400, 'body' => ['ok' => false, 'error' => 'invalid_request', 'message' => $e->getMessage()]];
        } catch (RuntimeException $e) {
            return ['status' => 502, 'body' => ['ok' => false, 'error' => 'upstream_error', 'message' => $e->getMessage()]];
        } catch (Throwable $e) {
            return ['status' => 422, 'body' => ['ok' => false, 'error' => 'unprocessable', 'message' => $e->getMessage()]];
        }
    }
}
