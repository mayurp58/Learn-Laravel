# P1 · Chapter 8 — Apply: Auth + middleware

**Read first:** `phase-2-core/ch08-middleware.md`
**Project state:** CRUD works with hardcoded user.

## What you're building this chapter

Real authentication via Breeze, then locking down all bookmark routes behind the `auth` middleware. After this chapter, anonymous users get redirected to login when they try to access `/bookmarks`.

## Step 1 — Install Breeze

```bash
composer require laravel/breeze --dev
php artisan breeze:install blade
npm install
npm run build
php artisan migrate
```

When prompted, pick:
- **Stack:** Blade with Alpine
- **Dark mode:** your call (no for simplicity)
- **Tests:** Pest

Breeze adds `/login`, `/register`, `/logout`, `/forgot-password`, plus a `dashboard` route gated by `auth`. It also creates `app/Http/Controllers/Auth/*` and views in `resources/views/auth/`.

## Step 2 — Lock down bookmark routes

Open `routes/web.php`:

```php
<?php

use App\Http\Controllers\BookmarkController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
})->name('home');

Route::middleware(['auth', 'verified'])->group(function () {
    Route::resource('bookmarks', BookmarkController::class);
});

require __DIR__.'/auth.php';
```

Now every `bookmarks.*` route requires a logged-in user. Visiting `/bookmarks` while logged out → redirect to `/login`.

## Step 3 — Revert the temporary workaround

In `BookmarkController.php`, change anything you patched to `User::first()` back to `$request->user()` and `auth()->id()`. The hardcoded user is no longer needed because middleware guarantees `$request->user()` is non-null.

## Step 4 — Wire the nav

Update `resources/views/welcome.blade.php` so the home page links to login or bookmarks based on auth state. Find the existing `@if (Route::has('login'))` block (Breeze added it) and confirm it shows "Log in / Register" when logged out and "Dashboard" when logged in. Add a "My Bookmarks" link inside the logged-in branch:

```blade
@auth
    <a href="{{ route('bookmarks.index') }}">My Bookmarks</a>
@endauth
```

## Step 5 — Try it

```bash
php artisan serve
```

1. Visit http://localhost:8000 → click "Register" → create an account
2. After registration you land on `/dashboard`
3. Click "My Bookmarks" → you're on `/bookmarks` (empty list)
4. Add a bookmark via the form
5. Log out → try to visit `/bookmarks` → you're redirected to `/login` ✅

## Verify it works

- ✅ Anonymous users hitting `/bookmarks/*` are redirected to `/login`
- ✅ Logged-in users only see *their own* bookmarks (test by creating two accounts, adding bookmarks in each, and switching between them)
- ✅ Trying to visit `/bookmarks/{someoneElsesId}/edit` returns 403

## Commit

```bash
git add .
git commit -m "feat: install Breeze and gate bookmark routes behind auth"
```

## Common pitfalls

- **`Route [login] not defined`** → you forgot `require __DIR__.'/auth.php';` at the bottom of `web.php`.
- **The `verified` middleware redirects forever** → that middleware requires the user's email to be verified. For local dev, remove `'verified'` from the route group, or click the verification link in `storage/logs/laravel.log` (Laravel logs emails to disk in local env by default).
- **Breeze install asks about Inertia/Vue** → pick **Blade** for P1. We use Inertia/Vue in P4.

## What's next

➡️ `ch09-build.md` — replace inline `$request->validate()` with proper Form Request classes, plus better error display.
