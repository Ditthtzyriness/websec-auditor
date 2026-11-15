<?php
declare(strict_types=1);

namespace App\Core;

/**
 * ScoringEngine
 * Menggabungkan skor antar-analyzer dengan bobot kategori:
 * Headers 40% | SSL 20% | Cookies 15% | CORS/Leakage 15% | DNS 10%
 * Output: skor 0–100 + Grade (A+, A, B, C, D, F).
 */
class ScoringEngine
{
    private const WEIGHTS = [
        'headers' => 0.40,
        'ssl'     => 0.20,
        'cookies' => 0.15,
        'cors'    => 0.15,
        'dns'     => 0.10,
    ];

    /**
     * @param array<string,array{score?:float|int}> $results
     * @return array{score:int, grade:string, breakdown:array<string,float>}
     */
    public function evaluate(array $results): array
    {
        $breakdown = [];
        $total = 0.0;

        foreach (self::WEIGHTS as $key => $weight) {
            $raw = (float) ($results[$key]['score'] ?? 0.0);
            $raw = max(0.0, min(100.0, $raw));
            $contribution = $raw * $weight;
            $breakdown[$key] = round($contribution, 2);
            $total += $contribution;
        }

        $final = (int) round($total);
        $final = max(0, min(100, $final));

        return [
            'score' => $final,
            'grade' => $this->grade($final),
            'breakdown' => $breakdown,
        ];
    }

    private function grade(int $score): string
    {
        if ($score >= 95) return 'A+';
        if ($score >= 85) return 'A';
        if ($score >= 75) return 'B';
        if ($score >= 60) return 'C';
        if ($score >= 45) return 'D';
        return 'F';
    }
}
