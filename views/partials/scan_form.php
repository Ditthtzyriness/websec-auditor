<?php
declare(strict_types=1);
use App\Helpers\HtmlHelper;

/** @var string $target */
$quickTests = ['example.com', 'google.com', 'github.com', 'cloudflare.com', 'wikipedia.org'];
?>
<section class="max-w-5xl mx-auto px-4 pt-12 pb-6">
    <div class="text-center mb-8">
        <div class="inline-flex items-center gap-2 text-xs font-medium px-3 py-1 rounded-full bg-sky-500/10 text-sky-300 border border-sky-500/30 mb-4">
            <span class="w-1.5 h-1.5 rounded-full bg-sky-400 animate-pulse"></span>
            Passive Security Audit &middot; SSRF-Protected
        </div>
        <h2 class="text-3xl sm:text-4xl font-extrabold tracking-tight bg-gradient-to-r from-white via-sky-100 to-indigo-200 bg-clip-text text-transparent">
            Audit Keamanan HTTP &amp; Konfigurasi Web
        </h2>
        <p class="text-slate-400 mt-3 max-w-2xl mx-auto text-sm">
            Analisis 7 security header wajib, sertifikat SSL/TLS, cookie flags, konfigurasi CORS, dan DNS security (CAA/SPF/DMARC) dalam satu laporan.
        </p>
    </div>

    <form method="get" action="/" class="glow rounded-2xl border border-slate-800 bg-slate-900/70 backdrop-blur p-5">
        <label for="target" class="block text-xs font-semibold text-slate-300 uppercase tracking-wide mb-2">
            Target Domain / URL
        </label>
        <div class="flex flex-col sm:flex-row gap-3">
            <div class="relative flex-1">
                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                    <svg class="w-5 h-5 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 11-18 0 9 9 0 0118 0zM3.6 9h16.8M3.6 15h16.8M12 3a15 15 0 010 18M12 3a15 15 0 000 18"/>
                    </svg>
                </div>
                <input
                    type="text"
                    id="target"
                    name="target"
                    required
                    autocomplete="off"
                    spellcheck="false"
                    placeholder="example.com atau https://example.com"
                    value="<?= HtmlHelper::e($target) ?>"
                    class="w-full pl-10 pr-4 py-3 rounded-xl bg-slate-950 border border-slate-700 text-slate-100 placeholder-slate-500 focus:outline-none focus:ring-2 focus:ring-sky-500 focus:border-transparent font-mono text-sm"
                >
            </div>
            <button type="submit" class="inline-flex items-center justify-center gap-2 px-6 py-3 rounded-xl bg-gradient-to-r from-sky-500 to-indigo-600 hover:from-sky-400 hover:to-indigo-500 text-white font-semibold text-sm shadow-lg shadow-sky-500/30 transition">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/>
                </svg>
                Audit Sekarang
            </button>
        </div>

        <div class="mt-4 flex flex-wrap items-center gap-2">
            <span class="text-xs text-slate-500 mr-1">Quick test:</span>
            <?php foreach ($quickTests as $qt): ?>
                <a href="/?target=<?= urlencode($qt) ?>"
                   class="text-xs px-2.5 py-1 rounded-md bg-slate-800/60 hover:bg-slate-700 text-slate-300 border border-slate-700/60 hover:border-sky-500/50 transition font-mono">
                    <?= HtmlHelper::e($qt) ?>
                </a>
            <?php endforeach; ?>
        </div>
    </form>
</section>
