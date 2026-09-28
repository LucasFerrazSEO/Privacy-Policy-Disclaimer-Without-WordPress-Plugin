# Changelog

## 2.0.0 (2026-09-28)

Rewritten from scratch as a complete consent tool, still a single file
loaded from the theme's `functions.php`.

- Opt-in (LGPD, GDPR), opt-out (CCPA/CPRA) and automatic consent models, by visitor country.
- Categories (necessary, preferences, statistics, marketing), preferences dialog and floating button.
- Google Consent Mode v2 (advanced and basic), optional GTM and GA4 loaders.
- Global Privacy Control support.
- Blocking of pasted code, enqueued handles, marked tags and third-party embeds until consent.
- Consent log with CSV export, retention cleanup and the WordPress personal data exporter and eraser.
- Settings page (Settings > Cookies), texts in English and Brazilian Portuguese.
- End-to-end tests (83 checks), axe-core pass, PHP_CodeSniffer with the WordPress standard.

Removed: `Code.txt`, the 1.x notice ("by using this site, you accept..."). It is still in the Git history.

## 1.x

Simple cookie notice with an OK button, pasted into `footer.php`.
