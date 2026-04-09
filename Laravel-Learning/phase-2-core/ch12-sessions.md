# Chapter 12 — Sessions, Cookies, CSRF

## Sessions

```php
session(['key' => 'value']);          // set
$value = session('key');              // get
$value = session('key', 'default');   // get with default
session()->forget('key');             // remove
session()->flush();                   // clear all
session()->has('key');                // check
```

Or via the request:
```php
$request->session()->put('cart', $cart);
$request->session()->get('cart');
```

## Flash data (one-request only)

```php
return redirect('/')->with('success', 'Saved!');
```

In Blade:
```blade
@if (session('success'))
    <div class="alert">{{ session('success') }}</div>
@endif
```

## Session drivers

Configured in `config/session.php`:
- `file` (default)
- `database`
- `redis`
- `cookie`
- `array` (testing)

For multi-server production apps, use `database` or `redis`.

## CSRF protection

Every POST/PUT/PATCH/DELETE through `routes/web.php` requires a CSRF token. Laravel auto-validates it. You add the token to forms with `@csrf`:

```blade
<form method="POST" action="/posts">
    @csrf
    ...
</form>
```

For AJAX, add to your meta tag and include it in the request header `X-CSRF-TOKEN`.

To exclude a route from CSRF (e.g., webhooks), in `bootstrap/app.php`:

```php
->withMiddleware(function (Middleware $middleware) {
    $middleware->preventRequestForgery(except: [
        'webhooks/*',
    ]);
})
```

> **Laravel 13 note:** The CSRF middleware is now `PreventRequestForgery` (was `VerifyCsrfToken` / `ValidateCsrfToken` in L12 and earlier). It still validates tokens, but additionally checks the `Sec-Fetch-Site` request header to verify origin — a defence-in-depth improvement. The fluent helper was renamed from `validateCsrfTokens()` to `preventRequestForgery()` to match.

## Cookies

```php
return response('Hello')->cookie('name', 'value', 60);  // 60 minutes

$value = $request->cookie('name');
```

Laravel encrypts cookies by default.

## Common Mistakes

1. **Forgetting `@csrf`** → 419 page expired error.
2. **Storing huge objects in session** → slow page loads.
3. **Trying to use sessions in `api.php`** → not available by default; APIs are stateless.

## Hands-on Task

1. Create a "click counter" page: a button that POSTs to `/click`, increments `session('clicks')`, redirects back, and displays the current count.
2. Add a "Reset" button that calls `session()->forget('clicks')`.

## Self-check

1. What's flash data?
2. Why does `web.php` have CSRF but `api.php` doesn't?
3. How do you exclude a webhook URL from CSRF?

➡️ **End of Phase 2.** Now go build **Mini Project 1: Task Manager** — see `projects/01-task-manager.md`. Then move to Phase 3.
