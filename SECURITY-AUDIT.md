# Security hardening and dependency update notes

## Implemented in this build

- Configurable adaptive rate limiting with per-IP and per-account buckets for authentication endpoints, plus moderate public/sensitive endpoint limits.
- Exponential backoff is temporary and cache-based; it does not permanently lock an account.
- Strict request schemas reject unexpected top-level input fields.
- Authentication, checkout, address, cart, profile, order, return, vendor, and public filter inputs were tightened.
- Email verification no longer accepts a user-supplied code when the stored code is missing.
- Unexpected server errors are logged server-side and replaced with generic user-facing responses.
- Payment receipts and vendor documents use private storage; public product assets are restricted to non-executable file types.
- Upload validation uses content/type validation and configurable size limits.
- SEO title/description support was added to both frontend layouts, with page-specific descriptions on major pages.
- Mobile navigation now includes a responsive search field and global overflow/image safeguards were added.
- `.env` is excluded from the deliverable; provider secrets are read from environment variables only.

## Dependency findings

The uploaded lockfile contained:

- `laravel/framework` v12.62.0
- `filament/filament` v5.6.7
- `livewire/livewire` v4.3.1
- `guzzlehttp/guzzle` 7.11.1
- `guzzlehttp/psr7` 2.11.0
- `axios` 1.11.0

Current security advisories checked during this audit identify patched releases including:

- Laravel 12.61.1+ for the temporary signed URL path-confusion issue; the locked Laravel 12.62.0 is already above that floor.
- Filament 5.7.6+ for the app-MFA code reuse issue; this project is configured for the 5.7.7+ line.
- Livewire 4.3.4+ for the DOM-based XSS issue; this project is configured for the 4.4.6+ line.
- Guzzle 7.15.2+ for the noncanonical-host issue and related July 2026 fixes.
- Guzzle PSR-7 2.12.3+ for host validation; later 2.12.x releases are preferable.
- Axios 1.18.0+ for the July/August 2026 Node HTTP-adapter/prototype-pollution advisories.

The source manifest constraints have been raised to patched release lines. The uploaded environment did not have Composer installed and could not reach npm's registry, so the Composer/NPM lockfiles could not be regenerated here. **Run the commands below on a machine with internet access before deploying.**

```bash
composer update --with-all-dependencies
composer audit

npm install
npm audit
npm run build
```

After the updates, commit the regenerated `composer.lock` and `package-lock.json`.

## Secrets

The uploaded `.env` contained real-looking credentials. It is intentionally not included in the hardened ZIP. Because those credentials were present in the uploaded archive, rotate any production database, mail, OAuth, AWS, eSewa, or other provider credentials before production use.

The original Git history also contained secret-like values in configuration examples. The hardened deliverable excludes `.git`; if those values exist in the real repository history, remove them from Git history with your repository's approved secret-removal process after rotating the affected credentials.

## Runtime verification limitation

PHP syntax checks passed for the application PHP/config/route files. The Vite production build passed after correcting executable permissions in the copied dependency tree.

Laravel Artisan commands could not be executed in this audit container because the PHP `dom` extension is not installed (`DOMDocument` is missing). Install/enable the DOM extension in the development/CI environment and run:

```bash
php artisan optimize:clear
php artisan route:list
php artisan test
```
