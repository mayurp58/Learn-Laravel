# Laravel 12 → 13 Upgrade Cheat Sheet

A condensed reference for upgrading existing Laravel 12 projects to Laravel 13 (released March 17, 2026). For full details see the relevant chapters in this course or the official upgrade guide at `laravel.com/docs/13.x/upgrade`.

## 1. Bump dependencies

In `composer.json`:

```json
{
  "require": {
    "php": "^8.3",
    "laravel/framework": "^13.0",
    "laravel/tinker": "^3.0"
  },
  "require-dev": {
    "pestphp/pest": "^4.0",
    "phpunit/phpunit": "^12.0"
  }
}
```

Then:

```bash
composer update
composer global update laravel/installer
```

## 2. PHP version

L13 requires **PHP 8.3 minimum** (supports 8.3 – 8.5). Update your CI matrix, Dockerfiles, Forge / DigitalOcean runtime, and local dev (MAMP / Valet / Herd).

## 3. Breaking change checklist

| # | Change | Action |
|---|---|---|
| 1 | CSRF middleware renamed `VerifyCsrfToken` → `PreventRequestForgery` | Update any imports / `withoutMiddleware([...])` calls. The fluent helper is now `preventRequestForgery(except: [...])` instead of `validateCsrfTokens(...)`. |
| 2 | New `serializable_classes` cache config | If you store PHP objects in cache, allowlist them in `config/cache.php`. Otherwise nothing to do. |
| 3 | `JobAttempted` event | `$exceptionOccurred` (bool) → `$exception` (object\|null). Update listeners. |
| 4 | `Container::call()` respects nullable defaults | If you relied on auto-injection despite `?Type $x = null`, change the signature. |
| 5 | Cache prefix / session cookie naming changed (hyphens / `Str::snake()`) | Pin `CACHE_PREFIX`, `REDIS_PREFIX`, `SESSION_COOKIE` in `.env` to avoid invalidating users' sessions on deploy. |
| 6 | `Js::from()` defaults to `JSON_UNESCAPED_UNICODE` | Audit any Blade-to-JS interop that depended on escaped `\u00xx` sequences. |
| 7 | Pagination view rename: `pagination::default` → `pagination::bootstrap-3` | Only matters if you reference these names directly. |
| 8 | `upsert()` validates non-empty `uniqueBy` | Now throws `InvalidArgumentException` instead of generating broken SQL — usually a bug-find, not a regression. |
| 9 | MySQL `DELETE … JOIN` now includes `ORDER BY` / `LIMIT` | Some MySQL forks may throw — verify on your prod variant. |
| 10 | `boot()` cannot instantiate the model | `(new static())` inside `boot()` now throws `LogicException`. Refactor to a static helper. |
| 11 | Polymorphic pivot table names now pluralize | Custom pivot classes affected — check generated names. |
| 12 | Default password reset notification subject changed | "Reset Password Notification" → "Reset your password". Override if you have email tests asserting the subject. |

## 4. Contract additions (custom implementations)

If you have your own implementations of these contracts, add the new methods:

- `Dispatcher::dispatchAfterResponse($command, $handler = null)`
- `ResponseFactory::eventStream()`
- `MustVerifyEmail::markEmailAsUnverified()`
- `Store` / `Repository::touch($key, $seconds)`
- `Queue::pendingSize()`, `delayedSize()`, `reservedSize()`, `creationTimeOfOldestPendingJob()`

## 5. New features worth adopting

| Feature | Where to learn it |
|---|---|
| Laravel AI SDK (text/image/audio/embeddings) | `phase-7-ecosystem/ch44b-ai-sdk.md` |
| First-party JSON:API Resources | `phase-3-eloquent/ch20-api-resources.md` |
| `Queue::route()` for centralized queue routing | `phase-5-advanced/ch30-queues.md` |
| Job attributes (`#[Tries]`, `#[Backoff]`, `#[Timeout]`, `#[FailOnTimeout]`) | `phase-5-advanced/ch30-queues.md` |
| Middleware/auth attributes (`#[Middleware]`, `#[Authorize]`) | `phase-2-core/ch08-middleware.md` |
| `Cache::touch()` for sliding TTLs | `phase-5-advanced/ch35-cache.md` |
| `whereVectorSimilarTo()` (pgvector) | `phase-3-eloquent/ch18-query-builder.md` |
| `PreventRequestForgery` origin verification | `phase-2-core/ch12-sessions.md` |

## 6. Recommended upgrade procedure

1. Branch off `main`. Tag your current state.
2. Bump PHP locally to 8.3+ and run the existing test suite — fix anything PHP-version-related first.
3. Bump `composer.json` per section 1, run `composer update`, fix any resolver errors.
4. Run the full test suite. Most failures will be CSRF middleware imports or `JobAttempted` listeners.
5. Walk the breaking-change checklist (section 3) line by line against your codebase. Many won't apply.
6. Deploy to staging. Smoke-test login flow specifically (CSRF + session cookie naming).
7. Only after staging is green: deploy to production with `CACHE_PREFIX` and `SESSION_COOKIE` pinned to their old values to keep users logged in across the cutover.

## 7. Tools

- **Laravel Boost** (official): `composer require laravel/boost:^2.0 --dev` then `/upgrade-laravel-v13` inside Claude Code or Cursor.
- **Shift** (paid, community-maintained): `laravelshift.com` — automated PR generation.

Both will get you 80% of the way there; you'll still need to walk section 3 manually.
