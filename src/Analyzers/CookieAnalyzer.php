<?php
declare(strict_types=1);

namespace App\Analyzers;

/**
 * CookieAnalyzer
 * Evaluasi flag Secure, HttpOnly, dan SameSite pada setiap Set-Cookie.
 */
class CookieAnalyzer
{
    /**
     * @param array<string,mixed> $headers
     * @return array{score:float, total:int, cookies:array<int,array<string,mixed>>}
     */
    public function analyze(array $headers): array
    {
        $setCookies = $headers['set-cookie'] ?? [];
        if (is_string($setCookies)) {
            $setCookies = [$setCookies];
        }
        if (!is_array($setCookies)) {
            $setCookies = [];
        }

        $cookies = [];
        $totalScore = 0.0;

        foreach ($setCookies as $raw) {
            if (!is_string($raw) || trim($raw) === '') {
                continue;
            }
            $parsed = $this->parseCookie($raw);
            $cookies[] = $parsed;
            $totalScore += $parsed['score'];
        }

        $total = count($cookies);
        $score = $total > 0 ? round($totalScore / $total, 2) : 100.0;

        return [
            'score' => $score,
            'total' => $total,
            'cookies' => $cookies,
        ];
    }

    /**
     * @return array<string,mixed>
     */
    private function parseCookie(string $raw): array
    {
        $parts = explode(';', $raw);
        $first = trim((string) array_shift($parts));

        $name = '';
        $value = '';
        if (str_contains($first, '=')) {
            [$name, $value] = explode('=', $first, 2);
        } else {
            $name = $first;
        }
        $name = trim($name);
        $value = trim($value);

        $attrs = [];
        foreach ($parts as $attr) {
            $attr = trim($attr);
            if ($attr === '') {
                continue;
            }
            if (str_contains($attr, '=')) {
                [$k, $v] = explode('=', $attr, 2);
                $attrs[strtolower(trim($k))] = trim($v);
            } else {
                $attrs[strtolower($attr)] = true;
            }
        }

        $secure = array_key_exists('secure', $attrs);
        $httpOnly = array_key_exists('httponly', $attrs);
        $sameSiteRaw = $attrs['samesite'] ?? null;
        $sameSite = is_string($sameSiteRaw) ? $sameSiteRaw : null;

        $score = 0.0;
        if ($secure)        $score += 33.34;
        if ($httpOnly)      $score += 33.33;
        if ($sameSite !== null) $score += 33.33;

        return [
            'name' => $name,
            'secure' => $secure,
            'http_only' => $httpOnly,
            'same_site' => $sameSite,
            'score' => round($score, 2),
        ];
    }
}
