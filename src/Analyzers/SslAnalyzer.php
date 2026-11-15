<?php
declare(strict_types=1);

namespace App\Analyzers;

/**
 * SslAnalyzer
 * Ambil detail sertifikat TLS via stream_socket_client + openssl_x509_parse.
 */
class SslAnalyzer
{
    /**
     * @return array<string,mixed>
     */
    public function analyze(string $host, int $port = 443): array
    {
        $result = [
            'ok' => false,
            'score' => 0.0,
            'subject' => null,
            'issuer' => null,
            'valid_from' => null,
            'valid_to' => null,
            'days_remaining' => null,
            'tls_version' => null,
            'error' => null,
        ];

        $context = stream_context_create([
            'ssl' => [
                'capture_peer_cert' => true,
                'verify_peer' => true,
                'verify_peer_name' => true,
                'SNI_enabled' => true,
                'peer_name' => $host,
            ],
        ]);

        $errno = 0;
        $errstr = '';

        $client = @stream_socket_client(
            "ssl://{$host}:{$port}",
            $errno,
            $errstr,
            6,
            STREAM_CLIENT_CONNECT,
            $context
        );

        if ($client === false) {
            $result['error'] = "Koneksi SSL gagal: {$errstr} ({$errno})";
            return $result;
        }

        $meta = stream_get_meta_data($client);
        if (!empty($meta['crypto']['protocol'])) {
            $result['tls_version'] = (string) $meta['crypto']['protocol'];
        }

        $params = stream_context_get_params($client);
        if (!empty($params['options']['ssl']['peer_certificate'])) {
            $parsed = openssl_x509_parse($params['options']['ssl']['peer_certificate']);
            if (is_array($parsed)) {
                $result['subject'] = $parsed['subject']['CN']
                    ?? ($parsed['subject']['commonName'] ?? null);
                $result['issuer'] = $parsed['issuer']['O']
                    ?? ($parsed['issuer']['CN'] ?? null)
                    ?? ($parsed['issuer']['commonName'] ?? null);
                if (isset($parsed['validFrom_time_t'])) {
                    $result['valid_from'] = gmdate('Y-m-d', (int) $parsed['validFrom_time_t']);
                }
                if (isset($parsed['validTo_time_t'])) {
                    $result['valid_to'] = gmdate('Y-m-d', (int) $parsed['validTo_time_t']);
                    $result['days_remaining'] = (int) floor(((int) $parsed['validTo_time_t'] - time()) / 86400);
                }
            }
        }

        fclose($client);

        // Skor sederhana (0–100)
        $score = 0.0;
        if ($result['subject'] !== null) $score += 25.0;
        if ($result['issuer'] !== null)  $score += 15.0;

        $days = $result['days_remaining'];
        if ($days !== null) {
            if ($days >= 30)      $score += 20.0;
            elseif ($days > 0)    $score += 10.0;
        }

        if ($result['tls_version'] !== null) {
            $v = strtolower($result['tls_version']);
            if (str_contains($v, 'tlsv1.3'))      $score += 25.0;
            elseif (str_contains($v, 'tlsv1.2'))  $score += 20.0;
            elseif (str_contains($v, 'tlsv1.1'))  $score += 8.0;
        }

        $result['ok'] = $score > 0;
        $result['score'] = round(min(100.0, $score), 2);

        return $result;
    }
}
