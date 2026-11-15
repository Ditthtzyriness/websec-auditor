<?php
declare(strict_types=1);
use App\Helpers\HtmlHelper;

/** @var array<string,mixed> $report */
/** @var string $gradeColorClass */
/** @var string $gradeBorderClass */

$score = (int) $report['scoring']['score'];
$grade = (string) $report['scoring']['grade'];
$breakdown = $report['scoring']['breakdown'];
$http = $report['http'];
$target = $report['target'];
$badge = $report['badge'];

$categories = [
    ['key' => 'headers', 'label' => 'Security Headers', 'weight' => '40%'],
    ['key' => 'ssl',     'label' => 'SSL / TLS',        'weight' => '20%'],
    ['key' => 'cookies', 'label' => 'Cookie Flags',     'weight' => '15%'],
    ['key' => 'cors',    'label' => 'CORS / Leakage',   'weight' => '15%'],
    ['key' => 'dns',     'label' => 'DNS Security',     'weight' => '10%'],
];
?>
<section class="max-w-5xl mx-auto px-4 mt-8">
    <div class="rounded-2xl border <?= HtmlHelper::e($gradeBorderClass) ?> bg-slate-900/70 backdrop-blur shadow-xl overflow-hidden">
        <div class="p-6 grid gap-6 md:grid-cols-[auto_1fr] items-center">
            <div class="flex flex-col items-center text-center gap-2">
                <div class="text-xs font-semibold uppercase tracking-wider text-slate-400">Grade</div>
                <div class="w-28 h-28 rounded-full <?= HtmlHelper::e($gradeColorClass) ?> flex items-center justify-center text-4xl font-black shadow-2xl">
                    <?= HtmlHelper::e($grade) ?>
                </div>
                <div class="mt-1">
                    <div class="text-3xl font-bold <?= HtmlHelper::scoreRing($score) ?>"><?= $score ?><span class="text-base text-slate-500">/100</span></div>
                </div>
            </div>

            <div class="min-w-0">
                <div class="flex flex-wrap items-center gap-2 text-xs mb-3">
                    <span class="px-2 py-0.5 rounded-md bg-emerald-500/15 text-emerald-300 border border-emerald-500/30 font-mono">HTTP <?= HtmlHelper::e((string)$http['status']) ?></span>
                    <span class="px-2 py-0.5 rounded-md bg-sky-500/15 text-sky-300 border border-sky-500/30 font-mono">TTFB <?= HtmlHelper::e((string)$http['ttfb_ms']) ?>ms</span>
                    <span class="px-2 py-0.5 rounded-md bg-indigo-500/15 text-indigo-300 border border-indigo-500/30 font-mono"><?= HtmlHelper::e(strtoupper((string)$target['scheme'])) ?></span>
                    <span class="px-2 py-0.5 rounded-md bg-slate-500/15 text-slate-300 border border-slate-500/30 font-mono">IP <?= HtmlHelper::e((string)$target['pinned_ip']) ?></span>
                </div>

                <h3 class="text-lg font-semibold truncate">
                    <?= HtmlHelper::e((string)$target['host']) ?>
                </h3>
                <p class="text-xs text-slate-500 font-mono truncate"><?= HtmlHelper::e((string)$target['url']) ?></p>

                <div class="mt-4 grid grid-cols-2 sm:grid-cols-5 gap-2">
                    <?php foreach ($categories as $cat): ?>
                        <?php
                        $rawScore = 0.0;
                        if ($cat['key'] === 'headers') $rawScore = (float) ($report['headers']['score'] ?? 0);
                        elseif ($cat['key'] === 'ssl') $rawScore = (float) ($report['ssl']['score'] ?? 0);
                        elseif ($cat['key'] === 'cookies') $rawScore = (float) ($report['cookies']['score'] ?? 0);
                        elseif ($cat['key'] === 'cors') $rawScore = (float) ($report['cors']['score'] ?? 0);
                        elseif ($cat['key'] === 'dns') $rawScore = (float) ($report['dns']['score'] ?? 0);
                        $pct = (int) round($rawScore);
                        $barColor = $pct >= 85 ? 'bg-emerald-500' : ($pct >= 60 ? 'bg-yellow-500' : 'bg-red-500');
                        ?>
                        <div class="rounded-lg bg-slate-950/60 border border-slate-800 p-2.5">
                            <div class="flex items-center justify-between text-[10px] text-slate-400 uppercase tracking-wide">
                                <span class="truncate"><?= HtmlHelper::e($cat['label']) ?></span>
                                <span class="text-slate-600"><?= HtmlHelper::e($cat['weight']) ?></span>
                            </div>
                            <div class="mt-1.5 flex items-baseline gap-1">
                                <span class="text-lg font-bold"><?= $pct ?></span>
                                <span class="text-[10px] text-slate-500">/100</span>
                            </div>
                            <div class="mt-1 h-1 rounded-full bg-slate-800 overflow-hidden">
                                <div class="h-full <?= $barColor ?>" style="width: <?= $pct ?>%"></div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>

                <div class="mt-4 rounded-lg bg-slate-950/60 border border-slate-800 p-3">
                    <div class="flex items-center justify-between gap-2 flex-wrap">
                        <span class="text-[11px] uppercase tracking-wide text-slate-400 font-semibold">Badge (shields.io)</span>
                        <button type="button" onclick="navigator.clipboard.writeText(<?= json_encode($badge['markdown'], JSON_UNESCAPED_SLASHES | JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT) ?>).then(()=>{this.textContent='Tersalin!';setTimeout(()=>this.textContent='Salin Markdown',1500)})"
                            class="text-[11px] px-2.5 py-1 rounded-md bg-slate-800 hover:bg-slate-700 border border-slate-700 transition">
                            Salin Markdown
                        </button>
                    </div>
                    <div class="mt-2 flex items-center gap-3 flex-wrap">
                        <img src="<?= HtmlHelper::e((string)$badge['url']) ?>" alt="security grade badge" class="h-8">
                        <code class="text-[11px] text-slate-400 break-all"><?= HtmlHelper::e((string)$badge['markdown']) ?></code>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>
