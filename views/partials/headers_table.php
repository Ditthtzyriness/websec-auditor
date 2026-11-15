<?php
declare(strict_types=1);
use App\Helpers\HtmlHelper;

/** @var array<string,mixed> $report */
$headerResults = $report['headers']['results'];
$cors = $report['cors'];
$cookies = $report['cookies'];
?>
<section class="max-w-5xl mx-auto px-4 mt-6">
    <div class="rounded-2xl border border-slate-800 bg-slate-900/70 backdrop-blur overflow-hidden">
        <div class="p-5 border-b border-slate-800 flex items-center justify-between flex-wrap gap-2">
            <div>
                <h3 class="text-sm font-bold uppercase tracking-wide text-slate-300">Security Headers Audit</h3>
                <p class="text-xs text-slate-500 mt-0.5">
                    <span class="text-emerald-400 font-semibold"><?= (int)$report['headers']['present'] ?> present</span> &middot;
                    <span class="text-red-400 font-semibold"><?= (int)$report['headers']['missing'] ?> missing</span>
                </p>
            </div>
            <span class="text-[10px] px-2 py-0.5 rounded-md border border-slate-500/30 bg-slate-500/15 text-slate-300 font-semibold">
                SCORE <?= HtmlHelper::e((string)$report['headers']['score']) ?>
            </span>
        </div>

        <div class="divide-y divide-slate-800">
            <?php foreach ($headerResults as $idx => $row): ?>
                <?php
                $present = (bool) $row['present'];
                $sevClass = HtmlHelper::severityColor((string)$row['severity']);
                $tabsId = 'tabs-' . $idx;
                $rem = (array) $row['remediation'];
                $hasRem = !empty($rem['nginx']) || !empty($rem['apache']) || !empty($rem['php']);
                ?>
                <div class="p-4" x-data>
                    <div class="flex flex-col md:flex-row md:items-start gap-3">
                        <div class="flex-shrink-0 mt-0.5">
                            <?php if ($present): ?>
                                <span class="inline-flex items-center justify-center w-8 h-8 rounded-lg bg-emerald-500/15 text-emerald-400 border border-emerald-500/30">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>
                                </span>
                            <?php else: ?>
                                <span class="inline-flex items-center justify-center w-8 h-8 rounded-lg bg-red-500/15 text-red-400 border border-red-500/30">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18L18 6M6 6l12 12"/></svg>
                                </span>
                            <?php endif; ?>
                        </div>

                        <div class="flex-1 min-w-0">
                            <div class="flex flex-wrap items-center gap-2">
                                <code class="text-sm font-semibold text-slate-100"><?= HtmlHelper::e((string)$row['label']) ?></code>
                                <span class="text-[10px] px-1.5 py-0.5 rounded border font-semibold <?= HtmlHelper::e($sevClass) ?>">
                                    <?= HtmlHelper::e((string)$row['severity']) ?>
                                </span>
                                <span class="text-[10px] text-slate-500 font-mono">weight <?= HtmlHelper::e((string)$row['weight']) ?></span>
                            </div>
                            <p class="text-xs text-slate-400 mt-1 leading-relaxed"><?= HtmlHelper::e((string)$row['description']) ?></p>

                            <?php if ($present): ?>
                                <div class="mt-2 rounded-lg bg-slate-950/60 border border-slate-800 p-2.5">
                                    <div class="text-[10px] uppercase text-slate-500 mb-1 font-semibold">Current Value</div>
                                    <code class="text-[11px] text-emerald-300 break-all"><?= HtmlHelper::e((string)$row['value']) ?></code>
                                </div>
                            <?php else: ?>
                                <div class="mt-2 rounded-lg bg-slate-950/60 border border-amber-500/30 p-2.5">
                                    <div class="text-[10px] uppercase text-amber-400 mb-1 font-semibold">Rekomendasi Nilai</div>
                                    <code class="text-[11px] text-amber-200 break-all"><?= HtmlHelper::e((string)$row['expected']) ?></code>
                                </div>
                            <?php endif; ?>

                            <?php if ($hasRem): ?>
                                <div class="mt-3">
                                    <div class="flex items-center gap-1 mb-2" data-tab-group="<?= HtmlHelper::e($tabsId) ?>">
                                        <button type="button" data-tab-btn data-tab="nginx" class="tab-btn px-2.5 py-1 text-[11px] rounded-md border border-slate-700 bg-slate-800 text-slate-200 font-medium">Nginx</button>
                                        <button type="button" data-tab-btn data-tab="apache" class="tab-btn px-2.5 py-1 text-[11px] rounded-md border border-slate-700 text-slate-400 font-medium hover:text-slate-200">Apache</button>
                                        <button type="button" data-tab-btn data-tab="php" class="tab-btn px-2.5 py-1 text-[11px] rounded-md border border-slate-700 text-slate-400 font-medium hover:text-slate-200">PHP</button>
                                    </div>
                                    <?php foreach (['nginx', 'apache', 'php'] as $stack): ?>
                                        <pre data-tab-panel="<?= HtmlHelper::e($stack) ?>" class="tab-panel <?= $stack === 'nginx' ? '' : 'hidden' ?> text-[11px] leading-relaxed bg-slate-950/80 border border-slate-800 rounded-lg p-2.5 overflow-x-auto text-sky-200"><code><?= HtmlHelper::e((string)($rem[$stack] ?? '')) ?></code></pre>
                                    <?php endforeach; ?>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<section class="max-w-5xl mx-auto px-4 mt-6 grid gap-4 md:grid-cols-2">
    <div class="rounded-2xl border border-slate-800 bg-slate-900/70 backdrop-blur p-5">
        <div class="flex items-center justify-between mb-3">
            <h3 class="text-sm font-bold uppercase tracking-wide text-slate-300">CORS &amp; Info Leakage</h3>
            <span class="text-[10px] px-2 py-0.5 rounded-md border border-slate-500/30 bg-slate-500/15 text-slate-300 font-semibold">
                SCORE <?= HtmlHelper::e((string)$cors['score']) ?>
            </span>
        </div>

        <div class="space-y-2">
            <?php if (!empty($cors['leaks'])): ?>
                <?php foreach ($cors['leaks'] as $leak): ?>
                    <div class="rounded-lg border border-amber-500/30 bg-amber-500/10 p-2.5">
                        <div class="text-[11px] uppercase text-amber-400 font-semibold"><?= HtmlHelper::e((string)$leak['header']) ?></div>
                        <code class="text-[11px] text-amber-200 break-all"><?= HtmlHelper::e((string)$leak['value']) ?></code>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <div class="rounded-lg border border-emerald-500/30 bg-emerald-500/10 p-2.5 text-xs text-emerald-200">
                    Tidak ada header yang membocorkan teknologi server.
                </div>
            <?php endif; ?>

            <div class="rounded-lg border <?= !empty($cors['cors']['risky']) ? 'border-red-500/30 bg-red-500/10' : 'border-slate-800 bg-slate-950/60' ?> p-2.5">
                <div class="text-[11px] uppercase <?= !empty($cors['cors']['risky']) ? 'text-red-400' : 'text-slate-400' ?> font-semibold">CORS Configuration</div>
                <div class="mt-1 text-xs space-y-0.5">
                    <div><span class="text-slate-500">Allow-Origin:</span> <code class="text-slate-200"><?= HtmlHelper::e((string)($cors['cors']['allow_origin'] ?? '—')) ?></code></div>
                    <div><span class="text-slate-500">Allow-Credentials:</span> <code class="text-slate-200"><?= HtmlHelper::e((string)($cors['cors']['allow_credentials'] ?? '—')) ?></code></div>
                    <div><span class="text-slate-500">Allow-Methods:</span> <code class="text-slate-200"><?= HtmlHelper::e((string)($cors['cors']['allow_methods'] ?? '—')) ?></code></div>
                </div>
                <?php if (!empty($cors['cors']['notes'])): ?>
                    <ul class="mt-2 list-disc list-inside text-[11px] text-amber-200 space-y-0.5">
                        <?php foreach ($cors['cors']['notes'] as $note): ?>
                            <li><?= HtmlHelper::e((string)$note) ?></li>
                        <?php endforeach; ?>
                    </ul>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <div class="rounded-2xl border border-slate-800 bg-slate-900/70 backdrop-blur p-5">
        <div class="flex items-center justify-between mb-3">
            <h3 class="text-sm font-bold uppercase tracking-wide text-slate-300">Cookie Flags</h3>
            <span class="text-[10px] px-2 py-0.5 rounded-md border border-slate-500/30 bg-slate-500/15 text-slate-300 font-semibold">
                <?= (int)$cookies['total'] ?> COOKIES
            </span>
        </div>

        <?php if (empty($cookies['cookies'])): ?>
            <div class="rounded-lg border border-slate-800 bg-slate-950/60 p-2.5 text-xs text-slate-400">
                Tidak ada header <code>Set-Cookie</code> pada respons.
            </div>
        <?php else: ?>
            <div class="space-y-2">
                <?php foreach ($cookies['cookies'] as $c): ?>
                    <div class="rounded-lg border border-slate-800 bg-slate-950/60 p-2.5">
                        <div class="text-xs font-semibold text-slate-200 truncate"><?= HtmlHelper::e((string)$c['name']) ?></div>
                        <div class="mt-1.5 flex flex-wrap gap-1">
                            <span class="text-[10px] px-1.5 py-0.5 rounded border font-semibold <?= $c['secure'] ? 'bg-emerald-500/15 text-emerald-300 border-emerald-500/30' : 'bg-red-500/15 text-red-300 border-red-500/30' ?>">
                                Secure: <?= $c['secure'] ? 'Yes' : 'No' ?>
                            </span>
                            <span class="text-[10px] px-1.5 py-0.5 rounded border font-semibold <?= $c['http_only'] ? 'bg-emerald-500/15 text-emerald-300 border-emerald-500/30' : 'bg-red-500/15 text-red-300 border-red-500/30' ?>">
                                HttpOnly: <?= $c['http_only'] ? 'Yes' : 'No' ?>
                            </span>
                            <span class="text-[10px] px-1.5 py-0.5 rounded border font-semibold <?= $c['same_site'] !== null ? 'bg-emerald-500/15 text-emerald-300 border-emerald-500/30' : 'bg-amber-500/15 text-amber-300 border-amber-500/30' ?>">
                                SameSite: <?= HtmlHelper::e((string)($c['same_site'] ?? 'None')) ?>
                            </span>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
</section>

<script>
(function () {
    document.querySelectorAll('[data-tab-group]').forEach(function (group) {
        var btns = group.querySelectorAll('[data-tab-btn]');
        var container = group.parentElement;
        var panels = container.querySelectorAll('[data-tab-panel]');

        btns.forEach(function (btn) {
            btn.addEventListener('click', function () {
                var target = btn.getAttribute('data-tab');
                btns.forEach(function (b) {
                    var isActive = b === btn;
                    b.classList.toggle('bg-slate-800', isActive);
                    b.classList.toggle('text-slate-200', isActive);
                    b.classList.toggle('text-slate-400', !isActive);
                });
                panels.forEach(function (p) {
                    p.classList.toggle('hidden', p.getAttribute('data-tab-panel') !== target);
                });
            });
        });
    });
})();
</script>
