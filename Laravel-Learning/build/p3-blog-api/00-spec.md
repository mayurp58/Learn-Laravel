# P3 — Blog REST API (Spec)

**Spans:** Phase 4 (chapters 21–25)
**Final state:** P2 (the blog) extended with a Sanctum-authenticated REST API. Same codebase, same database, new endpoints.

## The pitch

Real apps don't have a "web app" and an "API" as separate codebases — they're the same Laravel app exposing two surfaces over the same models. P3 makes that real: take your existing blog and expose authenticated CRUD endpoints suitable for a future mobile client.

By the end you'll have:

- Sanctum token auth (the same Sanctum a SPA or mobile app would use)
- `/api/v1/posts` write endpoints (POST, PUT, DELETE) with proper authorization
- Laravel Policies enforcing "you can only edit your own posts" — replacing the inline `abort_if` we used in P2
- Rate limiting on auth + write endpoints
- API versioning (`/api/v1/...`) so future breaking changes can ship as `/api/v2/`
- A Postman collection committed to the repo
- An updated README documenting the API

## What we already have from P2

- `/api/posts` and `/api/posts/{slug}` (read-only, public)
- `PostResource`, `AuthorResource`, `TagResource`
- The full blog domain: posts, categories, tags, comments

## What's new in P3

| Endpoint | Auth | Purpose |
|---|---|---|
| POST `/api/v1/auth/register` | none | Create account, get token |
| POST `/api/v1/auth/login` | none | Log in, get token |
| POST `/api/v1/auth/logout` | bearer | Revoke current token |
| GET `/api/v1/auth/me` | bearer | Current user info |
| GET `/api/v1/posts` | none | List published (inherited from P2, namespaced) |
| GET `/api/v1/posts/{slug}` | none | Single post (inherited) |
| POST `/api/v1/posts` | bearer | Create a post |
| PUT `/api/v1/posts/{slug}` | bearer + owner | Update |
| DELETE `/api/v1/posts/{slug}` | bearer + owner | Delete |
| POST `/api/v1/posts/{slug}/comments` | none | Public comment (rate-limited) |

## Authorization

Now that we're shipping write endpoints, we need real authorization. P2 used inline `abort_if($post->user_id !== ...)`. P3 replaces this with a `PostPolicy`:

- `update(User $user, Post $post)` → owner only
- `delete(User $user, Post $post)` → owner only
- `view(?User $user, Post $post)` → published OR owner
- `create(User $user)` → any authenticated user

The Policy is registered automatically in L11+ via convention, then used in both the API controller (`$this->authorize('update', $post)`) and the web dashboard controller (which also gets cleaned up).

## Rate limits

| Endpoint | Limit |
|---|---|
| `/api/v1/auth/login`, `/register` | 5 / minute per IP |
| `/api/v1/posts` POST/PUT/DELETE | 60 / minute per user |
| `/api/v1/posts/{slug}/comments` | 10 / hour per IP |
| Everything else | 60 / minute per IP |

Defined in `bootstrap/app.php` via `RateLimiter::for(...)`.

## Versioning

We're using **URL versioning**: `/api/v1/...`. When a breaking change ships, we add `/api/v2/...` and keep v1 alive for a deprecation period. The directory structure mirrors this:

```
app/Http/Controllers/Api/V1/
├── AuthController.php
├── PostController.php
└── CommentController.php

app/Http/Resources/V1/
└── PostResource.php  (we'll move the existing one)
```

## Postman collection

A `.postman_collection.json` file in the repo root, exported from Postman, covering every endpoint with example requests and an environment file template. Reviewers (and your future self) can import and click around in 30 seconds.

## What's intentionally NOT in P3

- WebSockets / broadcasting — that's P4
- File uploads — P4
- Tests — Phase 6 (we revisit then)
- OAuth2 (Passport) — out of scope; Sanctum is enough for 95% of jobs
- API documentation generators (Scribe, OpenAPI) — mention in interview, skip the implementation

## Next

Read `phase-4-auth-api/ch21-auth-starters.md`, then open `01-setup.md`.
