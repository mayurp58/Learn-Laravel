# Chapter 5 — The Request Lifecycle

Before you write any controllers, understand how a request travels through Laravel. This is the single concept that separates "I copy-paste tutorials" devs from "I can debug anything" devs.

## The 30-second version

```
Browser
  ↓
public/index.php
  ↓
bootstrap/app.php  (creates Application instance)
  ↓
HTTP Kernel
  ↓
Global Middleware (CORS, trim strings, ...)
  ↓
Router  (matches URL to a route)
  ↓
Route Middleware (auth, throttle, ...)
  ↓
Controller method  (your code)
  ↓
Response
  ↓
Reverse middleware
  ↓
Browser
```

## Step by step

### 1. `public/index.php` is the entry point

Every request to your Laravel app — regardless of URL — hits `public/index.php`. This is what your web server (Apache/Nginx/MAMP) is configured to point at.

It does three things:
1. Loads Composer's autoloader
2. Bootstraps the Laravel application
3. Hands the incoming request to the HTTP Kernel and sends back the response

### 2. The Kernel

The HTTP Kernel is the heart of Laravel's request handling. It:
- Boots service providers (Phase 5 chapter)
- Runs **global middleware** (things that apply to every request)
- Hands the request to the router

### 3. The Router

The router looks at the request method (`GET`, `POST`, etc.) and URL, and tries to match it against routes you defined in `routes/web.php` or `routes/api.php`.

If no match → 404.
If match → the router runs any **route-level middleware**, then calls your controller (or closure).

### 4. Your controller

Your code runs. It returns either a `Response`, a `View`, JSON, a redirect, or an Eloquent collection (Laravel will JSON-encode it for you).

### 5. The response travels back out

Through the same middleware stack — but in reverse, so middleware can modify outgoing responses too.

## Why this matters

Knowing this helps you answer:
- "Why is my session not persisting in `routes/api.php`?" → API routes don't have session middleware by default.
- "Why is `Auth::user()` null?" → because the auth middleware isn't on this route.
- "Where do I add a header to every response?" → middleware.
- "Where do I add a custom binding for a service?" → service provider.

## Demonstration: see middleware in action

```bash
php artisan route:list -v
```

The `-v` shows you every middleware applied to every route. Look at it. The `web` group has `EncryptCookies`, `StartSession`, `PreventRequestForgery` (the CSRF middleware — renamed from `VerifyCsrfToken` in Laravel 13, and now origin-aware via the `Sec-Fetch-Site` header), and more. The `api` group is much shorter.

## CodeIgniter comparison

CI3 had "hooks" (`pre_controller`, `post_controller`, etc.) and that was about it. Laravel's middleware is far more flexible because it's a chain — you can short-circuit, you can modify the response, you can group, you can apply per route.

## Hands-on Task

1. Open `routes/web.php`. Add this:
   ```php
   Route::get('/lifecycle', function () {
       return 'Hello from a closure route!';
   });
   ```
2. Visit http://127.0.0.1:8000/lifecycle in your browser. Confirm.
3. Run `php artisan route:list` — find your new route.
4. Read `bootstrap/app.php`. This is where Laravel 11+ (including L13) wires middleware and exception handling. Don't change anything yet — just observe.

## Self-check

1. What file is the entry point for every Laravel HTTP request?
2. What's the difference between global and route-level middleware?
3. Why can't you call `session()` in `routes/api.php` by default?

➡️ Next: `ch06-routing.md`
