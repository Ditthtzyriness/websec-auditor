<?php
declare(strict_types=1);

/**
 * index.php
 * Halaman utama (SSR). Merender seluruh UI via views/**.
 */

require_once __DIR__ . '/bootstrap.php';

use App\Controllers\AuditController;
use App\Helpers\HtmlHelper;

$target = isset($_GET['target']) ? (string) $_GET['target'] : '';
$report = null;
$error = null;
$httpStatus = null;

if (trim($target) !== '') {
    try {
        $controller = new AuditController();
        $report = $controller->audit($target);
        $httpStatus = $report['http']['status'] ?? null;
    } catch (Throwable $e) {
        $error = $e->getMessage();
    }
}

$pageTitle = 'WebSec Auditor — HTTP Security & Configuration Scanner';
$gradeColorClass = $report ? HtmlHelper::gradeColor($report['scoring']['grade']) : 'bg-slate-700 text-slate-200';
$gradeBorderClass = $report ? HtmlHelper::gradeBorder($report['scoring']['grade']) : 'border-slate-700';

require __DIR__ . '/views/layouts/header.php';
require __DIR__ . '/views/partials/scan_form.php';

if ($error !== null) {
    echo '<div class="max-w-5xl mx-auto mt-6 px-4">';
    echo '<div class="rounded-2xl border border-red-500/40 bg-red-500/10 text-red-200 p-5 shadow-lg">';
    echo '<div class="flex items-start gap-3">';
    echo '<svg class="w-6 h-6 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>';
    echo '<div>';
    echo '<p class="font-semibold">Audit gagal</p>';
    echo '<p class="text-sm mt-1 opacity-90">' . HtmlHelper::e($error) . '</p>';
    echo '</div></div></div></div>';
}

if ($report !== null) {
    require __DIR__ . '/views/partials/score_summary.php';
    require __DIR__ . '/views/partials/ssl_dns_cards.php';
    require __DIR__ . '/views/partials/headers_table.php';
} else {
    if ($error === null) {
        echo '<div class="max-w-5xl mx-auto mt-10 px-4 text-center text-slate-400 text-sm">';
        echo 'Masukkan domain untuk memulai audit. Coba salah satu quick test di atas.';
        echo '</div>';
    }
}

require __DIR__ . '/views/layouts/footer.php';
