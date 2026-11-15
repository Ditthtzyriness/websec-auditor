<?php
declare(strict_types=1);
use App\Helpers\HtmlHelper;

/** @var array<string,mixed> $report */
$ssl = $report['ssl'];
$dns = $report['dns'];

$sslBadge = !empty($ssl['ok'])
    ? 'bg-emerald-500/15 text-emerald-300 border-emerald-500/30'
    : 'bg-red-500/15 text-red-300 border-red-500/30';
$sslBadgeText = !empty($ssl['ok']) ? 'VALID' : 'INVALID';

$renderDnsRow = static function (string $label, array $node): void {
    $present = !empty($node['present']);
    $records = $node['records'] ?? [];
    ?>
    <div class="flex items-start justify-between gap-3 py-2 border-b border-slate-800 last:border-b-0">
        <div class="min-w-0">
            <div class="text-sm font-medium text-slate-200"><?= HtmlHelper::e($label) ?></div>
            <?php if (!empty($records)): ?>
                <code class="text-[11px] text-slate-500 break-all block mt-0.5"><?= HtmlHelper::e(implode(' | ', $records)) ?></code>
            <?php else: ?>
                <span class="text-[11px] text-slate-500">Tidak ditemukan</span>
            <?php endif; ?>
        </div>
        <span class="flex-shrink-0 text-[10px] px-2 py-0.5 rounded-md border font-semibold
            <?= $present ? 'bg-emerald-500/15 text-emerald-300 border-emerald-500/30' : 'bg-red-500/15 text-red-300 border-red-500/30' ?>">
            <?= $present ? 'OK' : 'MISSING' ?>
        </span>
    </div>
    <?php
};
?>
<section class="max-w-5xl mx-auto px-4 mt-6 grid gap-4 md:grid-cols-2">
    <div class="rounded-2xl border border-slate-800 bg-slate-900/70 backdrop-blur p-5">
        <div class="flex items-center justify-between mb-3">
            <h3 class="text-sm font-bold uppercase tracking-wide text-slate-300 flex items-center gap-2">
                <svg class="w-4 h-4 text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                </svg>
                SSL / TLS
            </h3>
            <span class="text-[10px] px-2 py-0.5 rounded-md border font-semibold <?= HtmlHelper::e($sslBadge) ?>">
                <?= HtmlHelper::e($sslBadgeText) ?>
            </span>
        </div>

        <dl class="grid grid-cols-1 gap-2 text-sm">
            <div class="flex justify-between gap-2 py-1.5 border-b border-slate-800">
                <dt class="text-slate-400">Subject CN</dt>
                <dd class="font-mono text-slate-200 truncate text-right"><?= HtmlHelper::e((string)($ssl['subject'] ?? '—')) ?></dd>
            </div>
            <div class="flex justify-between gap-2 py-1.5 border-b border-slate-800">
                <dt class="text-slate-400">Issuer</dt>
                <dd class="font-mono text-slate-200 truncate text-right"><?= HtmlHelper::e((string)($ssl['issuer'] ?? '—')) ?></dd>
            </div>
            <div class="flex justify-between gap-2 py-1.5 border-b border-slate-800">
                <dt class="text-slate-400">Valid From</dt>
                <dd class="font-mono text-slate-200 text-right"><?= HtmlHelper::e((string)($ssl['valid_from'] ?? '—')) ?></dd>
            </div>
            <div class="flex justify-between gap-2 py-1.5 border-b border-slate-800">
                <dt class="text-slate-400">Valid To</dt>
                <dd class="font-mono text-slate-200 text-right"><?= HtmlHelper::e((string)($ssl['valid_to'] ?? '—')) ?></dd>
            </div>
            <div class="flex justify-between gap-2 py-1.5 border-b border-slate-800">
                <dt class="text-slate-400">Days Remaining</dt>
                <dd class="font-mono <?= ((int)($ssl['days_remaining'] ?? 0) >= 30) ? 'text-emerald-300' : 'text-amber-300' ?> text-right">
                    <?= $ssl['days_remaining'] !== null ? HtmlHelper::e((string)$ssl['days_remaining']) : '—' ?>
                </dd>
            </div>
            <div class="flex justify-between gap-2 py-1.5">
                <dt class="text-slate-400">TLS Version</dt>
                <dd class="font-mono text-slate-200 text-right"><?= HtmlHelper::e((string)($ssl['tls_version'] ?? '—')) ?></dd>
            </div>
        </dl>

        <?php if (!empty($ssl['error'])): ?>
            <div class="mt-3 rounded-lg border border-red-500/30 bg-red-500/10 text-red-200 text-xs p-2.5">
                <?= HtmlHelper::e((string)$ssl['error']) ?>
            </div>
        <?php endif; ?>
    </div>

    <div class="rounded-2xl border border-slate-800 bg-slate-900/70 backdrop-blur p-5">
        <div class="flex items-center justify-between mb-3">
            <h3 class="text-sm font-bold uppercase tracking-wide text-slate-300 flex items-center gap-2">
                <svg class="w-4 h-4 text-indigo-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3.055 11H5a2 2 0 012 2v1a2 2 0 002 2 2 2 0 012 2v2.945M8 3.935V5.5A2.5 2.5 0 0010.5 8h.5a2 2 0 012 2 2 2 0 104 0 2 2 0 012-2h1.064M15 20.488V18a2 2 0 012-2h3.064M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
                DNS Security
            </h3>
            <span class="text-[10px] px-2 py-0.5 rounded-md border border-slate-500/30 bg-slate-500/15 text-slate-300 font-semibold">
                SCORE <?= HtmlHelper::e((string)$dns['score']) ?>
            </span>
        </div>

        <?php
        $renderDnsRow('CAA (Certificate Authority Authorization)', (array)$dns['caa']);
        $renderDnsRow('SPF (Sender Policy Framework)', (array)$dns['spf']);
        $renderDnsRow('DMARC (Domain-based Message Authentication)', (array)$dns['dmarc']);
        ?>
    </div>
</section>
