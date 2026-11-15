# 🛡️ WebSec Auditor

Web Security & HTTP Configuration Auditor — 100% PHP Native 8.2+, zero external dependencies, siap deploy ke **Railway** tanpa konfigurasi tambahan.

## ✨ Fitur

- 🔐 **7 Security Headers Audit** — HSTS, CSP, X-Frame-Options, X-Content-Type-Options, Referrer-Policy, Permissions-Policy, COOP
- 🔒 **SSL/TLS Inspection** — Subject CN, Issuer, valid window, days remaining, TLS version
- 🍪 **Cookie Flags** — Secure, HttpOnly, SameSite (Strict/Lax/None)
- 🌐 **CORS & Info Leakage** — Deteksi wildcard ACAO + credentials, header bocor teknologi
- 📡 **DNS Security** — CAA, SPF, DMARC
- 🎯 **Weighted Score (0–100)** + **Grade** (A+/A/B/C/D/F)
- 🖼️ **Badge Generator** — shields.io dynamic markdown/HTML
- 🛡️ **SSRF Protected** — DNS resolution + private/loopback/metadata IP blocking + `CURLOPT_RESOLVE` anti DNS rebinding
- ⚡ **REST API** — `GET /api.php?target=example.com`

## 📁 Struktur
