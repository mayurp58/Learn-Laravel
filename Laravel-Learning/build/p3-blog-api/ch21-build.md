# P3 · Chapter 21 — Apply: Auth starter kits

**Read first:** `phase-4-auth-api/ch21-auth-starters.md`

## What you're building this chapter

Almost nothing — this chapter is mostly conceptual confirmation. You've already installed Breeze in P2; you've already got Sanctum from `php artisan install:api`. The point of this build file is to verify both are in place and explain what each one is for, because the distinction trips up a lot of devs.

## Step 1 — Verify Breeze is providing web auth

```bash
php artisan route:list | grep -E "login|register|logout"
```

You should see the Breeze web routes (the ones that return Blade views, set session cookies, redirect on success).

## Step 2 — Verify Sanctum is providing API auth

```bash
php artisan route:list | grep sanctum
composer show laravel/sanctum
```

Sanctum doesn't ship default routes — you build them yourself. We'll do that in `ch22-build.md`.

## Step 3 — Understand the split

| Concern | Provided by | Used by |
|---|---|---|
| Web login form, session, CSRF | **Breeze** | The blog's `/dashboard` (browser users) |
| API token issue / revoke / verify | **Sanctum** | The blog's `/api/v1/...` (mobile / SPA / curl) |
| Password hashing, user model, registration logic | Laravel core | Both |

The same User model, the same `users` table — two different ways of *proving* you're that user.

## Step 4 — Why not Jetstream / Fortify?

Quick decision matrix to commit to memory:

- **Breeze** — minimal, you own the views, Blade or Inertia. Default for new projects. **What we use.**
- **Jetstream** — Breeze + teams + 2FA + profile management. Heavier. Skip unless the spec demands it.
- **Fortify** — backend-only (no views). Use when you build your own UI from scratch and just want the auth logic. Niche.

You'll get asked about the difference in interviews. The right answer is "Breeze for most projects, Jetstream when you need teams + 2FA out of the box, Fortify when you want pure backend logic."

## Step 5 — Confirm a registered user can hit a Sanctum-protected endpoint

This is just to feel the connection before we wire real endpoints. Add a temporary test route to `routes/api.php`:

```php
Route::get('/whoami', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');
```

Then in tinker:

```bash
php artisan tinker
```
```php
$user = \App\Models\User::where('email', 'demo@example.com')->first();
$token = $user->createToken('test')->plainTextToken;
echo $token;
```

Copy the token, then in another terminal:

```bash
curl -H "Authorization: Bearer YOUR_TOKEN" http://localhost:8000/api/whoami
```

You should see the user JSON. Without the token, you'll get a 401.

**Delete the `/whoami` route after this test** — it was just to verify Sanctum works. We'll build the real endpoints in `ch22-build.md`.

## Verify it works

- ✅ Breeze web routes exist
- ✅ Sanctum is installed and `personal_access_tokens` table exists
- ✅ A token-authenticated request returns the user; an unauthenticated one 401s

## Commit

(No code changes besides the temporary `/whoami` route which you've now deleted. Skip this commit.)

## What's next

➡️ `ch22-build.md` — build the real Sanctum-authenticated API endpoints.
