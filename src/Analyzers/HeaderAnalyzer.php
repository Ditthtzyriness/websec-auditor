<?php
declare(strict_types=1);

namespace App\Analyzers;

/**
 * HeaderAnalyzer
 * Evaluasi 7 security header dari config/security_rules.php,
 * sertakan snippet perbaikan dari config/remediations.php.
 */
class HeaderAnalyzer
{
    /** @var array<string,array<string,mixed>> */
    private array $rules;

    /** @var array<string,array<string,string>> */
    private array $remediations;

    public function __construct()
    {
        $this->rules = require __DIR__ . '/../../config/security_rules.php';
        $this->remediations = require __DIR__ . '/../../config/remediations.php';
    }

    /**
     * @param array<string,mixed> $headers
     * @return array{
     *   score:float, present:int, missing:int,
     *   results:array<int,array<string,mixed>>
     * }
     */
    public function analyze(array $headers): array
    {
        $results = [];
        $scoreRaw = 0.0;
        $totalWeight = 0;
        $present = 0;
        $missing = 0;

        foreach ($this->rules as $key => $rule) {
            $totalWeight += (int) $rule['weight'];
            $value = $this->getHeader($headers, $key);
            $ok = $value !== null && $value !== '';

            if ($ok) {
                $present++;
                $scoreRaw += (float) $rule['weight'];
            } else {
                $missing++;
            }

            $results[] = [
                'key'         => $key,
                'label'       => $rule['label'],
                'weight'      => $rule['weight'],
                'severity'    => $rule['severity'],
                'description' => $rule['description'],
                'expected'    => $rule['expected'],
                'present'     => $ok,
                'value'       => $value,
                'remediation' => $this->remediations[$key] ?? [],
            ];
        }

        $normalized = $totalWeight > 0 ? ($scoreRaw / $totalWeight) * 100.0 : 0.0;

        return [
            'score'   => round($normalized, 2),
            'present' => $present,
            'missing' => $missing,
            'results' => $results,
        ];
    }

    /**
     * @param array<string,mixed> $headers
     */
    private function getHeader(array $headers, string $name): ?string
    {
        if (!isset($headers[$name])) {
            return null;
        }
        $value = $headers[$name];
        if (is_array($value)) {
            return implode(', ', array_map('strval', $value));
        }
        return (string) $value;
    }
}
