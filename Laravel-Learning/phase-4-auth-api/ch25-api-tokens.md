# Chapter 25 — Token Patterns and Best Practices

A short, practical chapter on API auth in real production apps.

## Pattern 1: Mobile/SPA Login → Long-Lived Token

User logs in once, gets a token, stores it (Keychain on iOS, EncryptedSharedPreferences on Android, httpOnly cookie for SPAs). Token sent on every request.

Revoke on logout or device removal:
```php
$user->tokens()->where('name', 'iPhone-12')->delete();
```

## Pattern 2: Token Abilities (Scopes)

Issue tokens with limited capabilities:
```php
$user->createToken('cms-publisher', ['posts:write', 'posts:publish']);
$user->createToken('readonly', ['posts:read']);
```

Check:
```php
$request->user()->tokenCan('posts:publish');
```

Or as middleware:
```php
Route::middleware('ability:posts:publish')->post('/posts', ...);
```

## Pattern 3: Token Expiration

In `config/sanctum.php`:
```php
'expiration' => 60 * 24 * 7,   // 1 week in minutes
```

## Pattern 4: Refresh Tokens (when needed)

Sanctum doesn't ship refresh tokens. If you need them, build manually or use Passport.

## Security checklist

- ✅ HTTPS only
- ✅ Hash tokens before storing (Sanctum does this)
- ✅ Use abilities to scope down permissions
- ✅ Set expirations
- ✅ Rate-limit auth endpoints
- ✅ Log auth failures
- ❌ Never log tokens
- ❌ Never put tokens in URLs

## Hands-on Task

Modify the `/api/login` endpoint to accept a `device_name` parameter and use it as the token name. Build `/api/logout-everywhere` that revokes all of a user's tokens.

🔨 **Build it for real:** Apply this chapter to project P3 — see [`build/p3-blog-api/ch25-build.md`](../build/p3-blog-api/ch25-build.md).

➡️ **End of Phase 4.** Wrap up P3 by following [`build/p3-blog-api/99-finish.md`](../build/p3-blog-api/99-finish.md). Then move to Phase 5, which begins **project P4 — Projectly** (the multi-tenant SaaS centerpiece).
