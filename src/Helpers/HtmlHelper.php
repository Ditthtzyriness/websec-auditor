<?php
declare(strict_types=1);

namespace App\Helpers;

/**
 * HtmlHelper
 * Sanitasi XSS + mapping warna Tailwind CSS.
 */
class HtmlHelper
{
    public static function e(mixed $value): string
    {
        if ($value === null) {
            return '';
        }
        if (is_array($value) || is_object($value)) {
            return htmlspecialchars((string) json_encode($value, JSON_UNESCAPED_SLASHES), ENT_QUOTES | ENT_HTML5, 'UTF-8');
        }
        return htmlspecialchars((string) $value, ENT_QUOTES | ENT_HTML5, 'UTF-8');
    }

    public static function gradeColor(string $grade): string
    {
        return match (strtoupper($grade)) {
            'A+' => 'bg-emerald-500 text-emerald-950',
            'A'  => 'bg-green-500 text-green-950',
            'B'  => 'bg-lime-500 text-lime-950',
            'C'  => 'bg-yellow-500 text-yellow-950',
            'D'  => 'bg-orange-500 text-orange-950',
            default => 'bg-red-500 text-red-950',
        };
    }

    public static function gradeBorder(string $grade): string
    {
        return match (strtoupper($grade)) {
            'A+' => 'border-emerald-500/40 shadow-emerald-500/20',
            'A'  => 'border-green-500/40 shadow-green-500/20',
            'B'  => 'border-lime-500/40 shadow-lime-500/20',
            'C'  => 'border-yellow-500/40 shadow-yellow-500/20',
            'D'  => 'border-orange-500/40 shadow-orange-500/20',
            default => 'border-red-500/40 shadow-red-500/20',
        };
    }

    public static function severityColor(string $severity): string
    {
        return match (strtolower($severity)) {
            'high' => 'bg-red-500/15 text-red-300 border-red-500/40',
            'medium' => 'bg-amber-500/15 text-amber-300 border-amber-500/40',
            'low' => 'bg-sky-500/15 text-sky-300 border-sky-500/40',
            default => 'bg-slate-500/15 text-slate-300 border-slate-500/40',
        };
    }

    public static function scoreRing(int $score): string
    {
        return match (true) {
            $score >= 85 => 'text-emerald-400',
            $score >= 70 => 'text-green-400',
            $score >= 55 => 'text-yellow-400',
            $score >= 40 => 'text-orange-400',
            default => 'text-red-400',
        };
    }
}
