# P3 · Chapter 22 — Apply: Sanctum auth endpoints

**Read first:** `phase-4-auth-api/ch22-sanctum.md`

## What you're building this chapter

Three real auth endpoints: register, login, logout. Plus a `me` endpoint for fetching the authenticated user. All token-based.

## Step 1 — Generate AuthController

```bash
php artisan make:controller Api/AuthController
```

`app/Http/Controllers/Api/AuthController.php`:

```php
<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    public function register(Request $request)
    {
        $data = $request->validate([
            'name'     => ['required', 'string', 'max:255'],
            'email'    => ['required', 'email', 'unique:users,email'],
            'password' => ['required', 'confirmed', Password::defaults()],
        ]);

        $user = User::create([
            'name'     => $data['name'],
            'email'    => $data['email'],
            'password' => Hash::make($data['password']),
        ]);

        return response()->json([
            'user'  => $user->only('id', 'name', 'email'),
            'token' => $user->createToken($request->userAgent() ?? 'api')->plainTextToken,
        ], 201);
    }

    public function login(Request $request)
    {
        $data = $request->validate([
            'email'    => ['required', 'email'],
            'password' => ['required'],
        ]);

        $user = User::where('email', $data['email'])->first();

        if (! $user || ! Hash::check($data['password'], $user->password)) {
            throw ValidationException::withMessages([
                'email' => ['Invalid credentials.'],
            ]);
        }

        return response()->json([
            'user'  => $user->only('id', 'name', 'email'),
            'token' => $user->createToken($request->userAgent() ?? 'api')->plainTextToken,
        ]);
    }

    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json(['message' => 'Logged out.']);
    }

    public function me(Request $request)
    {
        return response()->json([
            'user' => $request->user()->only('id', 'name', 'email'),
        ]);
    }
}
```

> A few details to internalize:
>
> - `Password::defaults()` applies the rule set defined in `App\Providers\AppServiceProvider` (defaults: 8 chars min). If you want stricter rules globally, you set them once there.
> - `confirmed` requires a `password_confirmation` field in the request — standard Laravel.
> - Tokens are named after the user agent so users can see which device a token came from in a "manage devices" UI.
> - `currentAccessToken()` returns the token used to authenticate *this* request, so logout only kills the current session — not all of the user's tokens. There's also `tokens()->delete()` if you want a "log out everywhere".

## Step 2 — Routes

`routes/api.php`:

```php
use App\Http\Controllers\Api\AuthController;

Route::post('/auth/register', [AuthController::class, 'register']);
Route::post('/auth/login',    [AuthController::class, 'login']);

Route::middleware('auth:sanctum')->group(function () {
    Route::post('/auth/logout', [AuthController::class, 'logout']);
    Route::get('/auth/me',      [AuthController::class, 'me']);
});
```

> These are still under `/api/...`, not `/api/v1/...` yet. We add v1 versioning in `ch24-build.md`.

## Step 3 — Try it with curl

Register:
```bash
curl -X POST http://localhost:8000/api/auth/register \
  -H "Content-Type: application/json" \
  -H "Accept: application/json" \
  -d '{"name":"Test","email":"test@example.com","password":"secretpw","password_confirmation":"secretpw"}'
```

You should get back JSON with `user` and `token`. Save the token.

Hit `me`:
```bash
curl http://localhost:8000/api/auth/me \
  -H "Authorization: Bearer YOUR_TOKEN" \
  -H "Accept: application/json"
```

Logout:
```bash
curl -X POST http://localhost:8000/api/auth/logout \
  -H "Authorization: Bearer YOUR_TOKEN" \
  -H "Accept: application/json"
```

Then re-call `me` with the same token — you should get 401, because logout deleted that token.

> **`Accept: application/json` matters.** Without it, validation errors return an HTML 419 page instead of a JSON 422 response. Always send it on API requests.

## Step 4 — Inspect the personal_access_tokens table

```bash
psql blog -c "SELECT id, name, tokenable_id, abilities, last_used_at FROM personal_access_tokens;"
```

Each token is a row. After logout, the row is gone.

## Verify it works

- ✅ Register returns 201 + token
- ✅ Login returns 200 + token
- ✅ Wrong password returns 422 with error
- ✅ `me` works with bearer token
- ✅ Logout deletes the token, subsequent `me` returns 401

## Commit

```bash
git add .
git commit -m "feat(api): Sanctum register / login / logout / me endpoints"
```

## Common pitfalls

- **`The route api/auth/register could not be found`** → check that `routes/api.php` exists. Run `php artisan route:list | grep auth` to confirm.
- **Validation errors come back as HTML** → missing `Accept: application/json` header on the request.
- **`Method Illuminate\Auth\GenericUser::createToken does not exist`** → you forgot the `HasApiTokens` trait on `App\Models\User`.
- **Token works once then 401s** → you're calling `logout` between requests. Or you're using `currentAccessToken()` somewhere it shouldn't be.

## What's next

➡️ `ch23-build.md` — Policies for authorization. Replace the `abort_if` in P2's dashboard controller with a real `PostPolicy`.
