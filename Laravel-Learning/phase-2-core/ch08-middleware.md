# Chapter 8 — Middleware

Middleware is code that runs **before** or **after** a request reaches your controller. Think: filters, gates, request modifiers.

## Examples of what middleware does

- Check if user is authenticated → if not, redirect to login
- Verify CSRF token
- Log every request
- Inject CORS headers
- Throttle requests (rate limiting)
- Force HTTPS

## Anatomy of a middleware

```bash
php artisan make:middleware EnsureUserIsAdmin
```

Creates `app/Http/Middleware/EnsureUserIsAdmin.php`:

```php
<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class EnsureUserIsAdmin
{
    public function handle(Request $request, Closure $next)
    {
        if (! $request->user() || ! $request->user()->is_admin) {
            abort(403);
        }

        return $next($request);   // pass to next middleware / controller
    }
}
```

The pattern:
1. Look at the request.
2. Decide: pass it on (`return $next($request)`) or short-circuit (return a redirect, abort, etc.).
3. Optionally modify the response after `$next` runs:

```php
public function handle(Request $request, Closure $next)
{
    $response = $next($request);
    $response->headers->set('X-Custom', 'value');
    return $response;
}
```

## Registering middleware (Laravel 11+ / 13)

In Laravel 11+ (including L13), middleware is registered in `bootstrap/app.php`:

```php
->withMiddleware(function (Middleware $middleware) {
    $middleware->alias([
        'admin' => \App\Http\Middleware\EnsureUserIsAdmin::class,
    ]);

    // global middleware (runs on EVERY request)
    $middleware->append(\App\Http\Middleware\LogRequests::class);
})
```

## Applying middleware

In a route file:
```php
Route::get('/admin', [AdminController::class, 'index'])->middleware('admin');

Route::middleware(['auth', 'admin'])->group(function () {
    Route::get('/admin/users', ...);
});
```

In a controller:
```php
class AdminController extends Controller
{
    public function __construct()
    {
        $this->middleware('admin');
    }
}
```

## Attribute-based middleware (Laravel 13)

Laravel 13 lets you declare middleware directly on a controller class or method using PHP attributes — no need to register in routes or constructors:

```php
use Illuminate\Routing\Attributes\Middleware;
use Illuminate\Routing\Attributes\Authorize;

#[Middleware('auth')]
class CommentController
{
    #[Middleware('subscribed')]
    #[Authorize('create', [Comment::class, 'post'])]
    public function store(Post $post)
    {
        // ...
    }
}
```

This is purely declarative — the framework reads the attributes when resolving the route. Use it when you want middleware/authorization rules to live next to the action they protect, instead of being scattered across `routes/web.php`. Both styles (route-level and attribute-level) coexist; pick whichever reads better for the controller in question.

## Built-in middleware you should know

- `auth` — require authentication
- `auth:sanctum` — auth via Sanctum (API token)
- `guest` — only allow non-authenticated users (login/register pages)
- `throttle:60,1` — 60 requests per minute
- `verified` — email verified
- `signed` — signed URL (used for email verification, magic links)

## CodeIgniter comparison

CI3 had hooks (`pre_controller`) which were a single global function. Laravel middleware is composable, per-route, and can modify both request and response. It's not even close — Laravel's model is far more useful.

## Common Mistakes

1. **Forgetting to call `$next($request)`** — your route will silently return null.
2. **Putting business logic in middleware.** Middleware = cross-cutting concerns. Auth, logging, headers. Not "calculate discount."
3. **Registering middleware in the wrong place.** In Laravel 11+ (and 13), it's `bootstrap/app.php`, not `app/Http/Kernel.php` (that file no longer exists).

## Hands-on Task

1. Create a middleware that logs every incoming request URL to `storage/logs/laravel.log`:
   ```bash
   php artisan make:middleware LogRequests
   ```
2. In the `handle` method:
   ```php
   \Log::info('Request: ' . $request->method() . ' ' . $request->fullUrl());
   return $next($request);
   ```
3. Register it in `bootstrap/app.php` as global middleware.
4. Visit a few pages, then check `storage/logs/laravel.log`.

## Self-check

1. What does `$next($request)` do?
2. Where do you register middleware in Laravel 13?
3. Name three pieces of work that belong in middleware.

🔨 **Build it for real:** Apply this chapter to project P1 — see [`build/p1-bookmarks/ch08-build.md`](../build/p1-bookmarks/ch08-build.md).

➡️ Next: `ch09-validation.md`
