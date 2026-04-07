# Chapter 24 — Building RESTful APIs

This chapter is more philosophy + checklist than syntax — you already know the syntax from prior chapters.

## REST conventions

| Verb | URL | Action | Method |
|---|---|---|---|
| GET | /posts | list | index |
| POST | /posts | create | store |
| GET | /posts/{id} | show one | show |
| PUT/PATCH | /posts/{id} | update | update |
| DELETE | /posts/{id} | delete | destroy |

`Route::apiResource('posts', PostController::class)` sets all this up.

## URL versioning

```php
Route::prefix('v1')->group(function () {
    Route::apiResource('posts', \App\Http\Controllers\Api\V1\PostController::class);
});
```

When you make breaking changes, ship `v2` and keep `v1` running.

## Pagination

```php
return PostResource::collection(Post::paginate(15));
```

Response includes `data`, `links`, `meta` blocks automatically.

## Standard error format

Stick to a consistent shape:
```json
{
    "message": "Validation failed",
    "errors": {
        "email": ["The email field is required."]
    }
}
```

Laravel's `validate()` already returns this format for `Accept: application/json` requests.

For other errors:
```php
return response()->json(['message' => 'Not found'], 404);
```

## Rate limiting

In `bootstrap/app.php` or via middleware:

```php
Route::middleware('throttle:60,1')->group(function () { ... });
```

(60 requests per minute.)

For per-user throttling:
```php
RateLimiter::for('api', function (Request $request) {
    return Limit::perMinute(60)->by($request->user()?->id ?: $request->ip());
});
```

## CORS

Configured in `config/cors.php`. Add your frontend domain to `allowed_origins`.

## Common Mistakes

1. **Returning HTTP 200 with `success: false`** — use proper status codes (4xx for client errors, 5xx for server).
2. **Inconsistent envelope.** Pick one shape and stick to it.
3. **No pagination on list endpoints.** Returns 50,000 records and crashes the client.
4. **Leaking internal fields.** Use API Resources.

## Hands-on Task

Build a complete CRUD API for `posts`:
- `GET /api/v1/posts` (paginated)
- `POST /api/v1/posts` (auth required)
- `GET /api/v1/posts/{post}`
- `PUT /api/v1/posts/{post}` (only owner)
- `DELETE /api/v1/posts/{post}` (only owner)

Test every endpoint in Postman.

➡️ Next: `ch25-api-tokens.md`
