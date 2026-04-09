# P3 · Chapter 24 — Apply: REST endpoints + versioning

**Read first:** `phase-4-auth-api/ch24-rest-apis.md`

## What you're building this chapter

The full set of write endpoints for posts (POST, PUT, DELETE) at `/api/v1/posts`, plus the comment-create endpoint. Also: a directory restructure to introduce `v1` versioning.

## Step 1 — Restructure into V1

```bash
mkdir -p app/Http/Controllers/Api/V1
git mv app/Http/Controllers/Api/PostController.php app/Http/Controllers/Api/V1/PostController.php
git mv app/Http/Controllers/Api/AuthController.php app/Http/Controllers/Api/V1/AuthController.php
```

Update both files' namespaces:

```php
namespace App\Http\Controllers\Api\V1;
```

## Step 2 — Add a CommentController

```bash
php artisan make:controller Api/V1/CommentController
```

```php
<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Post;
use Illuminate\Http\Request;

class CommentController extends Controller
{
    public function store(Request $request, Post $post)
    {
        abort_unless($post->status === 'published', 404);

        $data = $request->validate([
            'author_name'  => ['required', 'string', 'max:255'],
            'author_email' => ['required', 'email', 'max:255'],
            'body'         => ['required', 'string', 'max:5000'],
        ]);

        $comment = $post->comments()->create($data);

        return response()->json([
            'id'         => $comment->id,
            'author'     => $comment->author_name,
            'body'       => $comment->body,
            'created_at' => $comment->created_at->toIso8601String(),
        ], 201);
    }
}
```

## Step 3 — Add write methods to V1 PostController

`app/Http/Controllers/Api/V1/PostController.php`:

```php
<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\PostResource;
use App\Models\Post;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

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
        $this->authorize('view', $post);
        $post->load(['author', 'category', 'tags'])->loadCount('comments');
        return new PostResource($post);
    }

    public function store(Request $request)
    {
        $this->authorize('create', Post::class);
        $data = $this->validateData($request);

        $post = DB::transaction(function () use ($data, $request) {
            $post = $request->user()->posts()->create([
                'category_id'  => $data['category_id'],
                'title'        => $data['title'],
                'slug'         => Str::slug($data['title']).'-'.Str::random(5),
                'excerpt'      => $data['excerpt'] ?? null,
                'body'         => $data['body'],
                'status'       => $data['status'],
                'published_at' => $data['status'] === 'published' ? now() : null,
            ]);

            $post->tags()->sync($data['tag_ids'] ?? []);
            return $post;
        });

        return (new PostResource($post->load(['author', 'category', 'tags'])))
            ->response()
            ->setStatusCode(201);
    }

    public function update(Request $request, Post $post)
    {
        $this->authorize('update', $post);
        $data = $this->validateData($request);

        DB::transaction(function () use ($post, $data) {
            $post->update([
                'category_id' => $data['category_id'],
                'title'       => $data['title'],
                'excerpt'     => $data['excerpt'] ?? null,
                'body'        => $data['body'],
                'status'      => $data['status'],
                'published_at' => $data['status'] === 'published' && ! $post->published_at
                    ? now() : $post->published_at,
            ]);
            $post->tags()->sync($data['tag_ids'] ?? []);
        });

        return new PostResource($post->fresh()->load(['author', 'category', 'tags']));
    }

    public function destroy(Post $post)
    {
        $this->authorize('delete', $post);
        $post->delete();
        return response()->json(['message' => 'Deleted.']);
    }

    private function validateData(Request $request): array
    {
        return $request->validate([
            'category_id' => ['required', 'exists:categories,id'],
            'title'       => ['required', 'string', 'max:255'],
            'excerpt'     => ['nullable', 'string', 'max:500'],
            'body'        => ['required', 'string'],
            'status'      => ['required', 'in:draft,published'],
            'tag_ids'     => ['array'],
            'tag_ids.*'   => ['integer', 'exists:tags,id'],
        ]);
    }
}
```

## Step 4 — Routes with versioning

Replace `routes/api.php`:

```php
<?php

use App\Http\Controllers\Api\V1\AuthController;
use App\Http\Controllers\Api\V1\CommentController;
use App\Http\Controllers\Api\V1\PostController;
use Illuminate\Support\Facades\Route;

Route::prefix('v1')->name('api.v1.')->group(function () {

    // Public auth
    Route::post('/auth/register', [AuthController::class, 'register'])->name('auth.register');
    Route::post('/auth/login',    [AuthController::class, 'login'])->name('auth.login');

    // Public reads
    Route::get('/posts',                  [PostController::class, 'index'])->name('posts.index');
    Route::get('/posts/{post:slug}',      [PostController::class, 'show'])->name('posts.show');

    // Public comment creation
    Route::post('/posts/{post:slug}/comments', [CommentController::class, 'store'])->name('comments.store');

    // Authenticated writes
    Route::middleware('auth:sanctum')->group(function () {
        Route::post('/auth/logout', [AuthController::class, 'logout'])->name('auth.logout');
        Route::get('/auth/me',      [AuthController::class, 'me'])->name('auth.me');

        Route::post('/posts',                 [PostController::class, 'store'])->name('posts.store');
        Route::put('/posts/{post:slug}',      [PostController::class, 'update'])->name('posts.update');
        Route::delete('/posts/{post:slug}',   [PostController::class, 'destroy'])->name('posts.destroy');
    });
});
```

> **All endpoints now live under `/api/v1/`.** When a future breaking change ships (e.g. a new field shape), you create `Api/V2/PostController.php` and add a `Route::prefix('v2')` block. Old clients keep working on v1 until you deprecate it.

## Step 5 — Update the PostResource self link

`PostResource.php` references `route('api.posts.show', ...)` from P2. Update it to use the v1 name:

```php
'links' => [
    'self' => route('api.v1.posts.show', $this->slug),
    'web'  => route('posts.show', $this->slug),
],
```

Same for `routeIs('api.posts.show')` → `routeIs('api.v1.posts.show')`.

## Step 6 — Try a full create flow

Get a token by logging in, then:

```bash
TOKEN="paste-token-here"

curl -X POST http://localhost:8000/api/v1/posts \
  -H "Authorization: Bearer $TOKEN" \
  -H "Accept: application/json" \
  -H "Content-Type: application/json" \
  -d '{
    "category_id": 1,
    "title": "Created via API",
    "body": "This post was created via curl.",
    "status": "published",
    "tag_ids": [1, 2]
  }'
```

You should get back a 201 with the post resource. Now visit `/posts` in the browser — it should be at the top.

Try updating it:
```bash
curl -X PUT http://localhost:8000/api/v1/posts/created-via-api-XXXXX \
  -H "Authorization: Bearer $TOKEN" \
  -H "Accept: application/json" \
  -H "Content-Type: application/json" \
  -d '{"category_id":1,"title":"Edited","body":"Edited body","status":"published","tag_ids":[1]}'
```

Try with the wrong user's token → 403. Try without a token → 401.

## Verify it works

- ✅ All endpoints accessible at `/api/v1/...`
- ✅ POST create returns 201
- ✅ PUT update returns 200
- ✅ DELETE returns 200 + message
- ✅ Editing someone else's post returns 403
- ✅ Validation errors return 422 with JSON
- ✅ Old `/api/posts` URLs are gone (or redirect — your call; we did a clean break)

## Commit

```bash
git add .
git commit -m "feat(api): v1 namespace with full post CRUD and comment endpoint"
```

## Common pitfalls

- **`Route [api.posts.show] not defined`** → you renamed routes but didn't update PostResource references.
- **Slug-binding 404 on update** → URL slug includes special chars. Use the slug Laravel generated, not what you'd expect.
- **`Authorization: Bearer ...` not working** → Sanctum's middleware needs the token to match a row in `personal_access_tokens`. Verify in psql. Tokens you "saved earlier" might be deleted by logout.
- **Old controllers in `Api/` not in `Api/V1/`** → you forgot to update the namespace at the top of the file after the move.

## What's next

➡️ `ch25-build.md` — rate limiting + the Postman collection.
