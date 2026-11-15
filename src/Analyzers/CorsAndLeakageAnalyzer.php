<?php
declare(strict_types=1);

namespace App\Analyzers;

/**
 * CorsAndLeakageAnalyzer
 * Deteksi kebocoran info teknologi server + konfigurasi CORS berisiko.
 */
class CorsAndLeakageAnalyzer
{
    private const LEAK_HEADERS = [
        'server',
        'x-powered-by',
        'x-aspnet-version',
        'x-aspnetmvc-version',
        'x-generator',
    ];

    /**
     * @param array<string,mixed> $headers
     * @return array{
     *   score:float,
     *   leaks:array<int,array{header:string,value:string}>,
     *   cors:array<string,mixed>
     * }
     */
    public function analyze(array $headers): array
    {
        $leaks = [];
        foreach (self::LEAK_HEADERS as $key) {
            $value = $this->get($headers, $key);
            if ($value !== null && $value !== '') {
                $leaks[] = ['header' => $key, 'value' => $value];
            }
        }

        $acao = $this->get($headers, 'access-control-allow-origin');
        $acac = $this->get($headers, 'access-control-allow-credentials');
        $acam = $this->get($headers, 'access-control-allow-methods');

        $notes = [];
        $risky = false;

        if ($acao === '*' && $acac !== null && strtolower($acac) === 'true') {
            $risky = true;
            $notes[] = 'Wildcard ACAO dikombinasikan dengan credentials: origin apa pun bisa membaca respons terautentikasi.';
        }
        if ($acao === '*' && !$risky) {
            $notes[] = 'Wildcard Access-Control-Allow-Origin mengizinkan origin mana pun.';
        }

        $score = 100.0;
        $score -= min(60.0, count($leaks) * 15.0);
        if ($risky) {
            $score -= 30.0;
        } elseif ($acao === '*') {
            $score -= 10.0;
        }
        $score = max(0.0, $score);

        return [
            'score' => round($score, 2),
            'leaks' => $leaks,
            'cors' => [
                'allow_origin' => $acao,
                'allow_credentials' => $acac,
                'allow_methods' => $acam,
                'risky' => $risky,
                'notes' => $notes,
            ],
        ];
    }

    /**
     * @param array<string,mixed> $headers
     */
    private function get(array $headers, string $key): ?string
    {
        if (!isset($headers[$key])) {
            return null;
        }
        $value = $headers[$key];
        if (is_array($value)) {
            $value = implode(', ', array_map('strval', $value));
        }
        return (string) $value;
    }
}
