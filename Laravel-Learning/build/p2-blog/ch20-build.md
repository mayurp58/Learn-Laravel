# P2 · Chapter 20 — Apply: API Resources

**Read first:** `phase-3-eloquent/ch20-api-resources.md`

## What you're building this chapter

A read-only public JSON API for posts at `/api/posts` and `/api/posts/{slug}`. Uses API Resource classes so the JSON shape is decoupled from the database schema. This is a preview of P3 — full auth, write endpoints, and Sanctum come there.

## Step 1 — Generate resources

```bash
php artisan make:resource PostResource
php artisan make:resource PostCollection
php artisan make:resource AuthorResource
php artisan make:resource TagResource
```

## Step 2 — PostResource

`app/Http/Resources/PostResource.php`:

```php
<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class PostResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id'             => $this->id,
            'title'          => $this->title,
            'slug'           => $this->slug,
            'excerpt'        => $this->excerpt,
            'body'           => $this->when($request->routeIs('api.posts.show'), $this->body),
            'reading_time'   => $this->reading_time,
            'published_at'   => $this->published_at?->toIso8601String(),
            'author'         => new AuthorResource($this->whenLoaded('author')),
            'category'       => $this->whenLoaded('category', fn () => [
                'name' => $this->category->name,
                'slug' => $this->category->slug,
            ]),
            'tags'           => TagResource::collection($this->whenLoaded('tags')),
            'comments_count' => $this->when(isset($this->comments_count), $this->comments_count),
            'links' => [
                'self' => route('api.posts.show', $this->slug),
                'web'  => route('posts.show', $this->slug),
            ],
        ];
    }
}
```

> **Two patterns to notice:**
> 1. `$this->when(...)` only includes a field conditionally — here, `body` is only sent on the single-post endpoint, not the list. That's a lightweight way to keep list payloads small.
> 2. `$this->whenLoaded(...)` only emits a relationship if the controller eager-loaded it. This forces the controller to be explicit about what it ships.

## Step 3 — AuthorResource and TagResource

`AuthorResource.php`:
```php
public function toArray($request): array
{
    return [
        'id'   => $this->id,
        'name' => $this->name,
    ];
}
```

`TagResource.php`:
```php
public function toArray($request): array
{
    return [
        'name' => $this->name,
        'slug' => $this->slug,
    ];
}
```

## Step 4 — API controller

```bash
php artisan make:controller Api/PostController
```

`app/Http/Controllers/Api/PostController.php`:

```php
<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\PostResource;
use App\Models\Post;

class PostController extends Controller
{
    public function index()
    {
        $posts = Post::with(['author', 'category', 'tags'])
            ->withCount('comments')
            ->published()
            ->recent()
            ->paginate(10);

        return PostResource::collection($posts);
    }

    public function show(Post $post)
    {
        abort_unless($post->status === 'published', 404);

        $post->load(['author', 'category', 'tags']);
        $post->loadCount('comments');

        return new PostResource($post);
    }
}
```

## Step 5 — Enable API routes

Laravel 11+ doesn't ship `routes/api.php` enabled by default. Run:

```bash
php artisan install:api
```

This creates `routes/api.php` and wires it into `bootstrap/app.php`. (It also installs Sanctum — we'll use that in P3, ignore for now.)

## Step 6 — Define the API routes

`routes/api.php`:

```php
<?php

use App\Http\Controllers\Api\PostController;
use Illuminate\Support\Facades\Route;

Route::get('/posts',          [PostController::class, 'index'])->name('api.posts.index');
Route::get('/posts/{post:slug}', [PostController::class, 'show'])->name('api.posts.show');
```

API routes are auto-prefixed with `/api`, so the URLs end up at `/api/posts` and `/api/posts/laravel-13-rocks`.

## Step 7 — Try it

```bash
curl -s http://localhost:8000/api/posts | jq .
```

You should see paginated JSON like:

```json
{
  "data": [
    {
      "id": 1,
      "title": "...",
      "slug": "...",
      "excerpt": "...",
      "reading_time": 3,
      "published_at": "2026-03-15T...",
      "author": {"id": 1, "name": "..."},
      "category": {"name": "...", "slug": "..."},
      "tags": [{"name": "laravel", "slug": "laravel"}],
      "comments_count": 4,
      "links": {"self": "...", "web": "..."}
    },
    ...
  ],
  "links": {"first": "...", "last": "...", "prev": null, "next": "..."},
  "meta": {"current_page": 1, "from": 1, "last_page": 3, ...}
}
```

The `data`, `links`, and `meta` envelopes come for free from `Resource::collection()` + paginator.

Now try a single post:

```bash
curl -s http://localhost:8000/api/posts/your-post-slug | jq .
```

Notice that **`body` is included here** but absent from the index response — that's the `$this->when($request->routeIs('api.posts.show'))` trick from step 2.

## Step 8 — Inspect what's different from raw model JSON

Compare with what `return Post::first();` would have returned: every column including timestamps, no relationships unless loaded, FK columns exposed (`user_id`, `category_id`). API Resources are how you stop leaking schema.

## Verify it works

- ✅ `/api/posts` returns paginated JSON wrapped in `data` / `links` / `meta`
- ✅ `/api/posts/{slug}` returns a single post with `body`
- ✅ List endpoint omits `body` (small payload)
- ✅ `tags` array uses `name`/`slug`, not `id`/`pivot` noise
- ✅ `comments_count` is present
- ✅ Drafts return 404

## Commit

```bash
git add .
git commit -m "feat: read-only API for posts via API Resources"
```

## Common pitfalls

- **`api/posts` returns 404** → you didn't run `php artisan install:api`. Or you forgot to register the route in `routes/api.php`.
- **`tags` array is empty even though the post has tags** → you forgot `'tags'` in the controller's `with([...])`. `whenLoaded` is doing exactly its job.
- **`pivot` noise in tag JSON** → that's why we use a Resource class instead of returning the relationship directly. `TagResource` only emits `name` and `slug`.
- **Timestamps look weird** → `?->toIso8601String()` formats them properly. Without the `?`, a null `published_at` would crash.

## What's next

➡️ `99-finish.md` — deploy P2, retire it, hand off to P3 (Blog API).
