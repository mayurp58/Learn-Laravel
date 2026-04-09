# P3 — Setup

**Read first:** `00-spec.md`
**Project state:** P2 finished, deployed, on `main`.

## Step 1 — Branch off P2

```bash
cd ~/Sites/blog
git checkout main
git pull
git checkout -b feature/api
```

## Step 2 — Verify Sanctum is installed

P2's `php artisan install:api` (in `ch20-build.md`) already installed Sanctum. Confirm:

```bash
composer show laravel/sanctum
```

If you somehow skipped that step:
```bash
php artisan install:api
```

## Step 3 — Add the `HasApiTokens` trait to User

`app/Models/User.php`:

```php
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;
    // ...
}
```

## Step 4 — Verify the personal_access_tokens table

```bash
psql blog -c "\dt"
```

You should see `personal_access_tokens` (Sanctum's migration created it during `install:api`).

## Step 5 — Plan the directory restructure

We're going to move the existing `Api/PostController` into `Api/V1/PostController`. Don't do it yet — just be aware. We'll do the move in `ch24-build.md` when we add versioning.

## What's next

➡️ `ch21-build.md` — Breeze is already installed; this chapter just verifies the auth scaffolding and explains the role of Sanctum vs. Breeze.
