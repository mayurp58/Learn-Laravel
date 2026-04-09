# P1 · Chapter 5 — Apply: Explore the request lifecycle in your project

**Read first:** `phase-2-core/ch05-request-lifecycle.md`
**Project state going in:** Fresh scaffold from `01-setup.md`. On branch `feature/bookmarks`.

## What you're building this chapter

Almost nothing — and that's the point. Chapter 5 is about *understanding* the lifecycle, not adding features. We're going to:

1. Inspect what middleware and routes already exist in your scaffold
2. Add a simple home route so we have something concrete to trace
3. Add a tiny piece of logging to *see* the lifecycle in action

By the end, you'll have watched a request flow through your real project.

## Step 1 — List the routes that exist out of the box

```bash
php artisan route:list -v
```

Read the output. You'll see one route:

```
GET|HEAD  /  ........... Closure
```

That's the welcome page. The `-v` flag shows the middleware stack — note `web` group with `EncryptCookies`, `StartSession`, `PreventRequestForgery` (the L13 CSRF middleware), `ShareErrorsFromSession`, `SubstituteBindings`. Internalize that list — these are the things running on every web request before your code.

## Step 2 — Add a custom home route

Open `routes/web.php`. You'll see Laravel's default closure route. Replace the file with:

```php
<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Log;

Route::get('/', function () {
    Log::info('Home page hit', [
        'ip' => request()->ip(),
        'time' => now()->toIso8601String(),
    ]);

    return view('welcome');
});
```

We've kept the welcome view but added a `Log::info()` call so you can *see* the request hitting your code.

## Step 3 — Watch the log in real time

In a new terminal window:

```bash
cd ~/Sites/bookmarks
tail -f storage/logs/laravel.log
```

(If the file doesn't exist yet, create it: `touch storage/logs/laravel.log`.)

## Step 4 — Visit the page

In another terminal:
```bash
php artisan serve
```

Open http://localhost:8000 in your browser. Refresh a couple of times.

In the `tail -f` terminal you should see entries like:

```
[2026-04-08 10:14:22] local.INFO: Home page hit {"ip":"127.0.0.1","time":"2026-04-08T10:14:22+00:00"}
```

That's the request lifecycle made visible: the web server received the request, Laravel booted, the middleware stack ran, the router matched `/`, your closure executed, and `Log::info` wrote a line.

## Step 5 — Trace one piece of middleware in source

Open `vendor/laravel/framework/src/Illuminate/Foundation/Http/Middleware/PreventRequestForgery.php` in your editor. Look at the `handle()` method. This is the new L13 CSRF middleware — note how it both validates the token *and* (in L13) checks the `Sec-Fetch-Site` header. You're not editing this — just *reading*. Knowing your way around `vendor/` is a senior-level habit.

## Verify it works

- ✅ `php artisan route:list -v` shows `GET /` with the `web` middleware group
- ✅ Visiting http://localhost:8000 shows the welcome page
- ✅ `tail -f storage/logs/laravel.log` shows a "Home page hit" line on every refresh

If any of those fail, fix them before moving on.

## Commit

```bash
git add routes/web.php
git commit -m "feat: add home route with lifecycle logging"
```

Don't push yet — we'll push after a few more chapters.

## Common pitfalls

- **No log file appearing** → permissions on `storage/logs/`. Run `chmod -R 775 storage`.
- **`Log::info` not firing** → make sure you're hitting `/` (the new route), not a stale browser cache. Hard refresh with `Cmd+Shift+R`.
- **You see two log entries per refresh** → the browser is requesting `/favicon.ico` too, which 404s but doesn't hit your Log line. That's fine.
- **Adding `dd($request)` instead of `Log::info`** → also valid, more dramatic, but you'll have to remove it before the next step. Up to you.

## What's next

➡️ `ch06-build.md` — define the bookmark routes (resourceful routing) and start replacing the welcome page with a real layout.
