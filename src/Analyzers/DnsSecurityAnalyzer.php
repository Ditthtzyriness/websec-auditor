<?php
declare(strict_types=1);

namespace App\Analyzers;

/**
 * DnsSecurityAnalyzer
 * Cek pasif: CAA, SPF (TXT v=spf1), DMARC (_dmarc.<domain>).
 */
class DnsSecurityAnalyzer
{
    /**
     * @return array<string,mixed>
     */
    public function analyze(string $host): array
    {
        $host = strtolower($host);

        $caa = $this->checkCaa($host);
        $spf = $this->checkSpf($host);
        $dmarc = $this->checkDmarc($host);

        $hit = 0;
        foreach ([$caa, $spf, $dmarc] as $item) {
            if (!empty($item['present'])) {
                $hit++;
            }
        }
        $score = round(($hit / 3) * 100.0, 2);

        return [
            'score' => $score,
            'caa' => $caa,
            'spf' => $spf,
            'dmarc' => $dmarc,
        ];
    }

    /**
     * @return array{present:bool, records:string[]}
     */
    private function checkCaa(string $host): array
    {
        $records = @dns_get_record($host, DNS_CAA);
        $values = [];

        if (is_array($records)) {
            foreach ($records as $r) {
                if (isset($r['value'])) {
                    $tag = isset($r['tag']) ? (string) $r['tag'] : '';
                    $values[] = trim($tag . ' ' . $r['value']);
                }
            }
        }

        return [
            'present' => !empty($values),
            'records' => $values,
        ];
    }

    /**
     * @return array{present:bool, records:string[]}
     */
    private function checkSpf(string $host): array
    {
        $records = @dns_get_record($host, DNS_TXT);
        $spf = [];

        if (is_array($records)) {
            foreach ($records as $r) {
                $txt = (string) ($r['txt'] ?? '');
                if ($txt !== '' && stripos($txt, 'v=spf1') === 0) {
                    $spf[] = $txt;
                }
            }
        }

        return [
            'present' => !empty($spf),
            'records' => $spf,
        ];
    }

    /**
     * @return array{present:bool, records:string[]}
     */
    private function checkDmarc(string $host): array
    {
        $dmarcHost = '_dmarc.' . $host;
        $records = @dns_get_record($dmarcHost, DNS_TXT);
        $dmarc = [];

        if (is_array($records)) {
            foreach ($records as $r) {
                $txt = (string) ($r['txt'] ?? '');
                if ($txt !== '' && stripos($txt, 'v=DMARC1') === 0) {
                    $dmarc[] = $txt;
                }
            }
        }

        return [
            'present' => !empty($dmarc),
            'records' => $dmarc,
        ];
    }
}
