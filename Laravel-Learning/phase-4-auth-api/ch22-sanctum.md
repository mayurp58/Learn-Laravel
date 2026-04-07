# Chapter 22 — Sanctum (API Tokens & SPA Auth)

For APIs, you don't use sessions. You use tokens. **Sanctum** is Laravel's official, lightweight token auth.

## Two modes

1. **API tokens** — issue a token (long string) per user, store hashed in `personal_access_tokens` table. Client sends `Authorization: Bearer <token>`.
2. **SPA auth** — for SPAs on the same domain (e.g. Vue/React frontend). Uses Laravel's session cookie + CSRF.

We'll focus on mode 1 — most common.

## Install

```bash
composer require laravel/sanctum
php artisan vendor:publish --provider="Laravel\Sanctum\SanctumServiceProvider"
php artisan migrate
```

In `app/Models/User.php`:
```php
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;
}
```

## Issuing tokens

```php
// In a login API endpoint
public function login(Request $request)
{
    $request->validate([
        'email' => 'required|email',
        'password' => 'required',
    ]);

    $user = User::where('email', $request->email)->first();

    if (! $user || ! Hash::check($request->password, $user->password)) {
        return response()->json(['message' => 'Invalid credentials'], 401);
    }

    $token = $user->createToken('mobile-app')->plainTextToken;

    return response()->json(['token' => $token, 'user' => $user]);
}
```

## Protecting API routes

In `routes/api.php` (if not present, install with `php artisan install:api`):

```php
Route::middleware('auth:sanctum')->group(function () {
    Route::get('/user', fn(Request $r) => $r->user());
    Route::apiResource('posts', PostController::class);
});
```

## Logging out (revoke token)

```php
$request->user()->currentAccessToken()->delete();
```

## Token abilities (scopes)

```php
$user->createToken('mobile', ['posts:read', 'posts:write']);

// Check
if ($request->user()->tokenCan('posts:write')) { ... }
```

## Sanctum vs Passport

- Sanctum: simple, lightweight, perfect for first-party APIs and SPAs.
- Passport: full OAuth2 server. Use only when you need third-party app integration with proper OAuth flows.

**Default to Sanctum.** Job interviews ask the difference often.

## Hands-on Task

1. Install Sanctum.
2. Build `/api/login` and `/api/logout` endpoints.
3. Build `/api/posts` (auth-protected).
4. Test with Postman: login → save token → use token in `Authorization: Bearer ...` header to call `/api/posts`.

➡️ Next: `ch23-policies.md`
