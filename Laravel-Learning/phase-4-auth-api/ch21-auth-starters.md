# Chapter 21 — Authentication: Breeze, Jetstream, Fortify

Laravel doesn't ship with auth UI by default anymore. You install a "starter kit" depending on what you want.

## The options

| Starter | What it gives you | When to use |
|---|---|---|
| **Breeze** | Login, register, password reset, email verification, profile, optional 2FA. Blade or Inertia/React/Vue. Minimal & easy to customize. | 90% of new projects. Start here. |
| **Jetstream** | All of Breeze + teams, API tokens, 2FA. Heavier, harder to undo. | Multi-tenant SaaS with teams from day one. |
| **Fortify** | Backend-only. No views. You build your own UI. | When you need auth logic but custom UI. |

## Installing Breeze (Blade version)

```bash
composer require laravel/breeze --dev
php artisan breeze:install blade
npm install
npm run dev
php artisan migrate
```

You now have:
- `/login`, `/register`, `/forgot-password`, `/dashboard`
- All routes, controllers, requests, views
- Tailwind already wired

Look at `routes/auth.php` and the controllers in `app/Http/Controllers/Auth/`. Read through them — this is the best Laravel-by-example you'll get.

## Manual auth basics (under the hood)

```php
use Illuminate\Support\Facades\Auth;

// Check
Auth::check();
Auth::user();
auth()->user();      // helper

// Login
if (Auth::attempt(['email' => $email, 'password' => $password])) {
    $request->session()->regenerate();
}

// Logout
Auth::logout();
$request->session()->invalidate();
$request->session()->regenerateToken();
```

## Protecting routes

```php
Route::middleware('auth')->group(function () {
    Route::get('/dashboard', ...);
});
```

In a Blade view:
```blade
@auth
    Hello, {{ auth()->user()->name }}
@endauth
```

## Hands-on Task

1. Install Breeze on a fresh Laravel app.
2. Register a user, log in, log out, reset password.
3. Read `LoginRequest::authenticate()` in the generated code. Understand it line by line.

🔨 **Build it for real:** Apply this chapter to project P3 (Blog API) — see [`build/p3-blog-api/ch21-build.md`](../build/p3-blog-api/ch21-build.md). P3 *extends* P2's blog (same repo, new branch), not a fresh project. Read [`build/p3-blog-api/00-spec.md`](../build/p3-blog-api/00-spec.md) and [`01-setup.md`](../build/p3-blog-api/01-setup.md) first.

➡️ Next: `ch22-sanctum.md`
