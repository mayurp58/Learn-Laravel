# P4 · Chapter 41 — Apply: Monitoring

**Read first:** `phase-6-testing-deploy/ch41-monitoring.md`

## What you're building this chapter

Three observability primitives:

1. **Structured logging** with named channels
2. **Telescope** for local debugging (don't enable in prod)
3. **A health check endpoint** that uptime services can ping

## Step 1 — Log channels

`config/logging.php` — add a custom channel for billing-related logs (we don't have billing yet, but this is the pattern):

```php
'channels' => [
    // existing 'stack', 'single', 'daily', etc.

    'audit' => [
        'driver' => 'daily',
        'path'   => storage_path('logs/audit.log'),
        'level'  => 'info',
        'days'   => 30,
    ],
],
```

Use it:
```php
Log::channel('audit')->info('User invited team member', [
    'inviter' => auth()->id(),
    'email'   => $email,
]);
```

> Senior Laravel apps separate concerns into channels: `audit`, `slow_queries`, `external_api`, etc. Makes log analysis sane.

## Step 2 — Telescope (local-only)

```bash
composer require laravel/telescope --dev
php artisan telescope:install
php artisan migrate
```

In `app/Providers/TelescopeServiceProvider.php`, the gate already restricts access to local. Confirm it's also gated in `bootstrap/providers.php` so it doesn't load in production:

```php
// AppServiceProvider::register
if ($this->app->environment('local')) {
    $this->app->register(\App\Providers\TelescopeServiceProvider::class);
}
```

Visit `http://localhost:8000/telescope` while running the app locally. You'll see every request, query, mail, job, and exception that has happened. **This is the single best Laravel debugging tool.** Use it constantly.

> Make sure Telescope is NOT installed in production. It captures everything including request payloads — a security and performance hazard. Adding it as `--dev` and gating it via env is enough.

## Step 3 — Health check endpoint

Laravel 11+ ships with a built-in `/up` route via the `withRouting(health: '/up')` config in `bootstrap/app.php`. Confirm it's there:

```bash
curl -i http://localhost:8000/up
```

Should return `200 OK`. Point an uptime monitor (UptimeRobot, Better Stack, Pingdom — all have free tiers) at `https://projectly.YOURDOMAIN.com/up`.

## Step 4 — Custom DB-included health check

If you want a richer check (DB connectivity, Redis, queue depth):

```bash
php artisan make:controller HealthController
```

```php
<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Redis;

class HealthController extends Controller
{
    public function __invoke()
    {
        $checks = [
            'app'   => 'ok',
            'db'    => $this->dbOk()    ? 'ok' : 'down',
            'redis' => $this->redisOk() ? 'ok' : 'down',
        ];

        $status = in_array('down', $checks, true) ? 503 : 200;
        return response()->json($checks, $status);
    }

    private function dbOk(): bool
    {
        try { DB::connection()->getPdo(); return true; }
        catch (\Throwable) { return false; }
    }

    private function redisOk(): bool
    {
        try { Redis::ping(); return true; }
        catch (\Throwable) { return false; }
    }
}
```

```php
Route::get('/health', \App\Http\Controllers\HealthController::class);
```

Now `/health` gives you a per-dependency status JSON, returning 503 if anything is down.

## Verify it works

- ✅ Telescope works locally, doesn't load in production
- ✅ `/up` returns 200
- ✅ `/health` returns JSON with all checks
- ✅ Killing Postgres returns 503 from `/health` (test: `brew services stop postgresql@16` → curl → `brew services start postgresql@16`)

## Commit

```bash
git add .
git commit -m "feat: monitoring (audit log channel, telescope local, /up + /health)"
```

## What's next

➡️ `ch42-build.md` — Livewire task board (drag-and-drop kanban).
