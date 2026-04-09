# P2 · Chapter 15 — Apply: Eloquent model basics

**Read first:** `phase-3-eloquent/ch15-eloquent-basics.md`

## What you're building this chapter

Properly configure all five models: `$fillable`, casts, accessors, scopes. Then build the public posts list page using the new query scopes.

## Step 1 — Configure each model

`app/Models/Post.php`:

```php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Post extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id', 'category_id', 'title', 'slug',
        'excerpt', 'body', 'status', 'published_at',
    ];

    protected $casts = [
        'published_at' => 'datetime',
    ];

    public function getRouteKeyName(): string
    {
        return 'slug';   // /posts/{slug} instead of /posts/{id}
    }

    // ----- Scopes -----

    public function scopePublished(Builder $q): void
    {
        $q->where('status', 'published')->whereNotNull('published_at');
    }

    public function scopeRecent(Builder $q): void
    {
        $q->orderByDesc('published_at');
    }

    // ----- Accessors -----

    public function getReadingTimeAttribute(): int
    {
        return max(1, (int) round(str_word_count(strip_tags($this->body)) / 200));
    }

    // ----- Relationships (preview, full set in ch16) -----

    public function tags()
    {
        return $this->belongsToMany(Tag::class);
    }
}
```

> `getRouteKeyName()` lets you do `Route::get('/posts/{post}', ...)` and have Laravel look up by `slug`. Cleaner URLs, no extra controller code.
>
> The `scopePublished` scope is reusable: `Post::published()->get()` does the right WHERE clause everywhere instead of you remembering to type it.
>
> The `getReadingTimeAttribute` accessor lets you call `$post->reading_time` in a view and get an integer minute count.

Apply analogous `$fillable` to `Category`, `Tag`, `Comment`. Skip `$guarded = []` we set in ch14 — that was temporary for seeding.

`app/Models/Category.php`:
```php
protected $fillable = ['name', 'slug', 'description'];
public function getRouteKeyName(): string { return 'slug'; }
```

`app/Models/Tag.php`:
```php
protected $fillable = ['name', 'slug'];
public function getRouteKeyName(): string { return 'slug'; }
```

`app/Models/Comment.php`:
```php
protected $fillable = ['post_id', 'author_name', 'author_email', 'body'];
```

## Step 2 — Public posts controller

```bash
php artisan make:controller PostController
```

`app/Http/Controllers/PostController.php`:

```php
<?php

namespace App\Http\Controllers;

use App\Models\Post;

class PostController extends Controller
{
    public function index()
    {
        $posts = Post::published()->recent()->paginate(10);
        return view('posts.index', compact('posts'));
    }

    public function show(Post $post)
    {
        abort_unless($post->status === 'published', 404);
        return view('posts.show', compact('post'));
    }
}
```

## Step 3 — Routes

`routes/web.php`:

```php
use App\Http\Controllers\PostController;

Route::get('/posts', [PostController::class, 'index'])->name('posts.index');
Route::get('/posts/{post:slug}', [PostController::class, 'show'])->name('posts.show');
```

> Note `{post:slug}` — explicit binding column. Combined with `getRouteKeyName()` either form works, but explicit is clearer.

## Step 4 — Minimal views

`resources/views/posts/index.blade.php`:

```blade
@extends('layouts.app')
@section('title', 'All Posts')

@section('content')
    <h1 class="text-3xl font-bold mb-6">Latest Posts</h1>

    @foreach ($posts as $post)
        <article class="bg-white rounded shadow-sm border p-5 mb-4">
            <h2 class="text-xl font-semibold">
                <a href="{{ route('posts.show', $post) }}" class="text-blue-700 hover:underline">{{ $post->title }}</a>
            </h2>
            <p class="text-sm text-gray-500 mt-1">
                {{ $post->published_at->format('M j, Y') }} · {{ $post->reading_time }} min read
            </p>
            <p class="mt-3 text-gray-700">{{ $post->excerpt }}</p>
        </article>
    @endforeach

    {{ $posts->links() }}
@endsection
```

`resources/views/posts/show.blade.php`:

```blade
@extends('layouts.app')
@section('title', $post->title)

@section('content')
    <article class="bg-white rounded shadow-sm border p-6">
        <h1 class="text-3xl font-bold">{{ $post->title }}</h1>
        <p class="text-sm text-gray-500 mt-2">
            {{ $post->published_at->format('M j, Y') }} · {{ $post->reading_time }} min read
        </p>
        <div class="prose mt-6">
            {!! nl2br(e($post->body)) !!}
        </div>
    </article>

    <a href="{{ route('posts.index') }}" class="inline-block mt-6 text-blue-600">← All posts</a>
@endsection
```

## Step 5 — `layouts/app.blade.php`

If you don't already have it (P2 starts fresh), create it as we did in P1 ch11:

```blade
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>{{ config('app.name') }} — @yield('title')</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-gray-50 text-gray-900">
    <nav class="bg-white border-b">
        <div class="max-w-3xl mx-auto px-4 py-3 flex justify-between">
            <a href="/" class="font-semibold">{{ config('app.name') }}</a>
            <div class="space-x-4 text-sm">
                <a href="{{ route('posts.index') }}">All posts</a>
                @auth
                    <a href="{{ url('/dashboard') }}">Dashboard</a>
                @else
                    <a href="{{ route('login') }}">Login</a>
                @endauth
            </div>
        </div>
    </nav>
    <main class="max-w-3xl mx-auto px-4 py-8">
        @yield('content')
    </main>
</body>
</html>
```

## Step 6 — Run it

```bash
npm run dev
php artisan serve
```

Visit `/posts`. You should see 10 posts paginated, each with reading time, formatted date, excerpt. Click any title → reads the post.

## Verify it works

- ✅ `/posts` shows 10 posts per page
- ✅ Pagination links work (page 2, 3, …)
- ✅ Reading time is a positive integer
- ✅ Dates are formatted (not raw ISO timestamps)
- ✅ `/posts/{slug}` works
- ✅ `Post::published()` excludes drafts (test: `Post::factory()->draft()->create(); Post::published()->count()` should be 30, not 31)

## Commit

```bash
git add .
git commit -m "feat: configure Post model with scopes/accessors; public post pages"
```

## Common pitfalls

- **`Call to undefined method published()`** → you put `scopePublished` outside the class or with the wrong signature.
- **`Trying to format property of non-object`** on `$post->published_at->format(...)` → you forgot the `'datetime'` cast.
- **`/posts/{slug}` returns 404** → either you skipped `getRouteKeyName()` or the slug has special characters. Check the slug column for spaces.
- **Reading time always 1** → the formula divides by 200 words/min and rounds. Short posts genuinely have a 1-minute reading time. Try a longer body to verify.

## What's next

➡️ `ch16-build.md` — add all the relationships (User↔Post, Post↔Category, Post↔Tag, Post↔Comment).
