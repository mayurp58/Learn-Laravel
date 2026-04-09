# P3 · Chapter 25 — Apply: Rate limiting + Postman collection

**Read first:** `phase-4-auth-api/ch25-rate-limiting.md`

## What you're building this chapter

1. **Rate limits** for auth, write, and comment endpoints — in `bootstrap/app.php`
2. **A Postman collection** committed to the repo, covering every endpoint
3. **An updated README** with API documentation

## Step 1 — Define named rate limiters

Open `bootstrap/app.php`. In the `withRouting(...)` callback or `withProviders(...)` you'll already have some structure. Add a custom configuration step:

```php
->withRouting(
    web: __DIR__.'/../routes/web.php',
    api: __DIR__.'/../routes/api.php',
    commands: __DIR__.'/../routes/console.php',
    health: '/up',
)
```

Then in the same chain, add:

```php
->withMiddleware(function (Middleware $middleware) {
    // existing middleware config...
})
```

For rate limiters, the cleanest place is `app/Providers/AppServiceProvider.php`. Edit `boot()`:

```php
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;

public function boot(): void
{
    RateLimiter::for('auth', function (Request $request) {
        return Limit::perMinute(5)->by($request->ip());
    });

    RateLimiter::for('writes', function (Request $request) {
        return Limit::perMinute(60)->by($request->user()?->id ?: $request->ip());
    });

    RateLimiter::for('comments', function (Request $request) {
        return Limit::perHour(10)->by($request->ip());
    });

    // existing boot code...
}
```

> **Why three named limiters instead of one?** Different endpoints need different limits. Auth needs aggressive limits (brute-force defence). Writes need user-keyed limits (per-user, generous). Comment spam is IP-keyed.

## Step 2 — Apply limiters to routes

Update `routes/api.php` to apply each limiter to its endpoints:

```php
Route::prefix('v1')->name('api.v1.')->group(function () {

    Route::middleware('throttle:auth')->group(function () {
        Route::post('/auth/register', [AuthController::class, 'register']);
        Route::post('/auth/login',    [AuthController::class, 'login']);
    });

    Route::get('/posts',                  [PostController::class, 'index']);
    Route::get('/posts/{post:slug}',      [PostController::class, 'show']);

    Route::middleware('throttle:comments')
        ->post('/posts/{post:slug}/comments', [CommentController::class, 'store']);

    Route::middleware(['auth:sanctum', 'throttle:writes'])->group(function () {
        Route::post('/auth/logout', [AuthController::class, 'logout']);
        Route::get('/auth/me',      [AuthController::class, 'me']);

        Route::post('/posts',                 [PostController::class, 'store']);
        Route::put('/posts/{post:slug}',      [PostController::class, 'update']);
        Route::delete('/posts/{post:slug}',   [PostController::class, 'destroy']);
    });
});
```

## Step 3 — Try to hit the limit

```bash
for i in {1..7}; do
  curl -s -w "%{http_code}\n" -o /dev/null \
    -X POST http://localhost:8000/api/v1/auth/login \
    -H "Content-Type: application/json" \
    -H "Accept: application/json" \
    -d '{"email":"a@b.com","password":"wrong"}'
done
```

You should see five `422`s followed by two `429`s. The 429 means rate limited. Confirmed.

## Step 4 — Verify the Retry-After header

```bash
curl -i -X POST http://localhost:8000/api/v1/auth/login \
  -H "Content-Type: application/json" \
  -H "Accept: application/json" \
  -d '{"email":"a@b.com","password":"wrong"}'
```

When rate limited, the response includes `Retry-After: 60` and `X-RateLimit-Reset` headers. Clients know how long to back off.

## Step 5 — Build the Postman collection

Open Postman → New Collection → name it "Blog API v1". Create these requests:

**Folder: Auth**
- POST Register — `{{base_url}}/auth/register` with JSON body
- POST Login — `{{base_url}}/auth/login`, in the test script: `pm.environment.set("token", pm.response.json().token)`
- POST Logout — `{{base_url}}/auth/logout`
- GET Me — `{{base_url}}/auth/me`

**Folder: Posts**
- GET List — `{{base_url}}/posts`
- GET Single — `{{base_url}}/posts/:slug`
- POST Create — `{{base_url}}/posts` with sample body
- PUT Update — `{{base_url}}/posts/:slug`
- DELETE Delete — `{{base_url}}/posts/:slug`

**Folder: Comments**
- POST Create — `{{base_url}}/posts/:slug/comments`

In the collection settings, set:
- Auth: Bearer Token = `{{token}}`
- Header: `Accept: application/json`

Set up an environment with `base_url = http://localhost:8000/api/v1` and an empty `token` variable.

Export the collection: collection menu → Export → Collection v2.1 → save as `postman/blog-api-v1.postman_collection.json` in your repo.

Also export the environment: `postman/blog-api.postman_environment.json`.

## Step 6 — Update the README

Add a section to `README.md`:

```markdown
## API

The blog exposes a Sanctum-authenticated REST API at `/api/v1`.

### Auth
- `POST /api/v1/auth/register`
- `POST /api/v1/auth/login`
- `POST /api/v1/auth/logout` (requires bearer token)
- `GET  /api/v1/auth/me` (requires bearer token)

### Posts
- `GET    /api/v1/posts` — list published, paginated
- `GET    /api/v1/posts/{slug}` — single post
- `POST   /api/v1/posts` — create (auth required)
- `PUT    /api/v1/posts/{slug}` — update (auth + ownership)
- `DELETE /api/v1/posts/{slug}` — delete (auth + ownership)

### Comments
- `POST /api/v1/posts/{slug}/comments` — create comment (rate-limited 10/hr/IP)

### Rate limits
- Auth endpoints: 5/min per IP
- Write endpoints: 60/min per user
- Comments: 10/hr per IP

### Postman collection
Import `postman/blog-api-v1.postman_collection.json` and `postman/blog-api.postman_environment.json` into Postman to start hitting endpoints in 30 seconds.
```

## Verify it works

- ✅ Rate limits trip after the configured number of requests
- ✅ 429 response includes `Retry-After` header
- ✅ Postman collection imports cleanly into a fresh Postman install
- ✅ README documents every endpoint

## Commit

```bash
git add .
git commit -m "feat(api): rate limiters, Postman collection, README docs"
```

## Common pitfalls

- **Rate limits don't trigger** → wrong middleware name. Should be `throttle:auth`, not `throttle.auth`.
- **`No named rate limiter "auth" exists`** → you put the `RateLimiter::for(...)` calls in the wrong file or method. Must be in a service provider's `boot()`.
- **Postman shows `401` even with token** → your environment didn't save the token. Re-login and verify the test script.

## What's next

➡️ `99-finish.md` — deploy the updated blog (now with API), retire P3, hand off to P4 (Projectly).
