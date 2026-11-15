<?php
declare(strict_types=1);

namespace App\Helpers;

/**
 * BadgeGenerator
 * Menghasilkan URL badge shields.io + markdown + HTML snippet berdasarkan grade.
 */
class BadgeGenerator
{
    /**
     * @return array{
     *   url:string, markdown:string, html:string,
     *   grade:string, score:int, color:string
     * }
     */
    public function generate(string $host, int $score, string $grade): array
    {
        $color = $this->colorFor($grade);
        $label = rawurlencode('security grade');
        $msg = rawurlencode($grade . ' (' . $score . '/100)');

        $url = "https://img.shields.io/badge/{$label}-{$msg}-{$color}?style=for-the-badge&logo=shield";
        $alt = "Security Grade: {$grade} ({$score}/100) — {$host}";

        $markdown = "![{$alt}]({$url})";
        $html = '<img src="' . htmlspecialchars($url, ENT_QUOTES | ENT_HTML5, 'UTF-8')
            . '" alt="' . htmlspecialchars($alt, ENT_QUOTES | ENT_HTML5, 'UTF-8') . '">';

        return [
            'url' => $url,
            'markdown' => $markdown,
            'html' => $html,
            'grade' => $grade,
            'score' => $score,
            'color' => $color,
        ];
    }

    private function colorFor(string $grade): string
    {
        return match (strtoupper($grade)) {
            'A+' => 'brightgreen',
            'A'  => 'green',
            'B'  => 'yellowgreen',
            'C'  => 'yellow',
            'D'  => 'orange',
            default => 'red',
        };
    }
}
