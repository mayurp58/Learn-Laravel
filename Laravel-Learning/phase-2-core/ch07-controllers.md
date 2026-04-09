# Chapter 7 — Controllers

Controllers in Laravel are PHP classes that group related request-handling logic. Conceptually identical to CI controllers — but with much more power because of dependency injection and form requests.

## Creating a controller

```bash
php artisan make:controller PostController
```

Creates `app/Http/Controllers/PostController.php`:

```php
<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class PostController extends Controller
{
    //
}
```

## Resource controller (RESTful skeleton)

```bash
php artisan make:controller PostController --resource
```

Generates 7 method stubs: `index`, `create`, `store`, `show`, `edit`, `update`, `destroy`. These match `Route::resource('posts', PostController::class)` 1:1.

For APIs:
```bash
php artisan make:controller Api/PostController --api
```

(no `create`/`edit` since APIs don't render forms)

## A complete controller example

```php
<?php

namespace App\Http\Controllers;

use App\Models\Post;
use Illuminate\Http\Request;

class PostController extends Controller
{
    public function index()
    {
        $posts = Post::latest()->paginate(10);
        return view('posts.index', compact('posts'));
    }

    public function create()
    {
        return view('posts.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|min:3|max:255',
            'body'  => 'required',
        ]);

        Post::create($validated);

        return redirect()->route('posts.index')->with('success', 'Post created!');
    }

    public function show(Post $post)            // route model binding
    {
        return view('posts.show', compact('post'));
    }

    public function edit(Post $post)
    {
        return view('posts.edit', compact('post'));
    }

    public function update(Request $request, Post $post)
    {
        $validated = $request->validate([
            'title' => 'required|min:3|max:255',
            'body'  => 'required',
        ]);

        $post->update($validated);

        return redirect()->route('posts.show', $post)->with('success', 'Updated!');
    }

    public function destroy(Post $post)
    {
        $post->delete();
        return redirect()->route('posts.index')->with('success', 'Deleted!');
    }
}
```

## Single-action (Invokable) Controllers

When a controller does ONE thing, use `__invoke`.

```bash
php artisan make:controller PublishPostController --invokable
```

```php
class PublishPostController extends Controller
{
    public function __invoke(Post $post)
    {
        $post->update(['published_at' => now()]);
        return back()->with('success', 'Published!');
    }
}
```

```php
Route::post('/posts/{post}/publish', PublishPostController::class);
```

Pass the class itself, not an array. Cleaner for one-off endpoints.

## Dependency Injection in Controllers

You can type-hint services in the constructor *or* method:

```php
class PostController extends Controller
{
    public function __construct(private PostService $posts) {}

    public function index()
    {
        return $this->posts->latest();
    }
}
```

Or per-method:
```php
public function store(StorePostRequest $request, PostService $posts)
{
    $posts->create($request->validated());
    ...
}
```

Laravel's container auto-injects whatever you type-hint. **This is the payoff from Chapter 3.** You don't write `new PostService()`. Ever.

## Common Mistakes

1. **Fat controllers.** Anything more than ~30 lines per method is a smell. Move logic to a service class or action.
2. **Returning data without explicit responses.** Be deliberate — return view, redirect, or JSON, not a string.
3. **Forgetting `use App\Models\Post;`** at the top.
4. **Validating in the controller for big forms** — use Form Requests (Chapter 9).

## Hands-on Task

1. Create a `Post` model and migration in one shot:
   ```bash
   php artisan make:model Post -mcr
   ```
   That's `model + migration + controller + resource methods`.
2. Open the migration in `database/migrations/` and add:
   ```php
   $table->string('title');
   $table->text('body');
   ```
3. Add to `app/Models/Post.php`:
   ```php
   protected $fillable = ['title', 'body'];
   ```
4. Run `php artisan migrate`.
5. Add `Route::resource('posts', PostController::class);` in `routes/web.php`.
6. Run `php artisan route:list` — you should see 7 post routes.

You can't visit them yet (no views) — that's Chapter 11. But the wiring is done.

## Self-check

1. What's an invokable controller? When should you use it?
2. What does `--api` do differently from `--resource`?
3. How does Laravel know to inject `PostService` into your constructor?

🔨 **Build it for real:** Apply this chapter to project P1 — see [`build/p1-bookmarks/ch07-build.md`](../build/p1-bookmarks/ch07-build.md).

➡️ Next: `ch08-middleware.md`
