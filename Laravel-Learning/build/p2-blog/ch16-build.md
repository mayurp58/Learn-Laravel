# P2 · Chapter 16 — Apply: Relationships

**Read first:** `phase-3-eloquent/ch16-relationships.md`

## What you're building this chapter

Wire up every relationship in the schema, then use them in views: show author name, category, tag list, and comment count on each post.

## Step 1 — All relationships

`app/Models/User.php` (add inside the class):
```php
public function posts()
{
    return $this->hasMany(Post::class);
}
```

`app/Models/Category.php`:
```php
public function posts()
{
    return $this->hasMany(Post::class);
}
```

`app/Models/Post.php` (add):
```php
public function author()
{
    return $this->belongsTo(User::class, 'user_id');
}

public function category()
{
    return $this->belongsTo(Category::class);
}

public function comments()
{
    return $this->hasMany(Comment::class)->latest();
}
```

(Note: `tags()` is already there from ch14.)

`app/Models/Tag.php`:
```php
public function posts()
{
    return $this->belongsToMany(Post::class);
}
```

`app/Models/Comment.php`:
```php
public function post()
{
    return $this->belongsTo(Post::class);
}
```

## Step 2 — Use them in the post list

Update `resources/views/posts/index.blade.php` — add author, category, tags, comment count to each card:

```blade
@foreach ($posts as $post)
    <article class="bg-white rounded shadow-sm border p-5 mb-4">
        <h2 class="text-xl font-semibold">
            <a href="{{ route('posts.show', $post) }}" class="text-blue-700 hover:underline">{{ $post->title }}</a>
        </h2>
        <p class="text-sm text-gray-500 mt-1">
            By {{ $post->author->name }} ·
            in <a href="{{ route('categories.show', $post->category) }}" class="text-blue-600">{{ $post->category->name }}</a> ·
            {{ $post->published_at->format('M j, Y') }} ·
            {{ $post->comments->count() }} comments
        </p>
        <p class="mt-3 text-gray-700">{{ $post->excerpt }}</p>
        <div class="mt-3 space-x-2 text-xs">
            @foreach ($post->tags as $tag)
                <a href="{{ route('tags.show', $tag) }}" class="bg-gray-100 px-2 py-1 rounded">#{{ $tag->name }}</a>
            @endforeach
        </div>
    </article>
@endforeach
```

> **Notice:** This is the textbook N+1 trap. Every iteration triggers fresh queries for `$post->author`, `$post->category`, `$post->comments`, `$post->tags`. We'll watch this happen in `ch17-build.md` and fix it. **Leave it broken for now** — the lesson lands harder when you see the queries explode.

## Step 3 — Show comments on the post page

Update `resources/views/posts/show.blade.php`:

```blade
@extends('layouts.app')
@section('title', $post->title)

@section('content')
    <article class="bg-white rounded shadow-sm border p-6">
        <h1 class="text-3xl font-bold">{{ $post->title }}</h1>
        <p class="text-sm text-gray-500 mt-2">
            By {{ $post->author->name }} in
            <a href="{{ route('categories.show', $post->category) }}" class="text-blue-600">{{ $post->category->name }}</a>
            · {{ $post->published_at->format('M j, Y') }} · {{ $post->reading_time }} min read
        </p>
        <div class="prose mt-6">
            {!! nl2br(e($post->body)) !!}
        </div>

        <div class="mt-6 space-x-2 text-xs">
            @foreach ($post->tags as $tag)
                <a href="{{ route('tags.show', $tag) }}" class="bg-gray-100 px-2 py-1 rounded">#{{ $tag->name }}</a>
            @endforeach
        </div>
    </article>

    <section class="mt-10">
        <h2 class="text-xl font-semibold mb-4">{{ $post->comments->count() }} comments</h2>
        @forelse ($post->comments as $comment)
            <div class="bg-white border rounded p-4 mb-3">
                <p class="text-sm font-semibold">{{ $comment->author_name }}</p>
                <p class="text-xs text-gray-500">{{ $comment->created_at->diffForHumans() }}</p>
                <p class="mt-2 text-gray-700">{{ $comment->body }}</p>
            </div>
        @empty
            <p class="text-gray-500">No comments yet.</p>
        @endforelse
    </section>
@endsection
```

## Step 4 — Category and Tag pages

```bash
php artisan make:controller CategoryController
php artisan make:controller TagController
```

```php
// CategoryController.php
class CategoryController extends Controller
{
    public function show(\App\Models\Category $category)
    {
        $posts = $category->posts()->published()->recent()->paginate(10);
        return view('categories.show', compact('category', 'posts'));
    }
}

// TagController.php
class TagController extends Controller
{
    public function show(\App\Models\Tag $tag)
    {
        $posts = $tag->posts()->published()->recent()->paginate(10);
        return view('tags.show', compact('tag', 'posts'));
    }
}
```

Routes:
```php
Route::get('/categories/{category:slug}', [CategoryController::class, 'show'])->name('categories.show');
Route::get('/tags/{tag:slug}', [TagController::class, 'show'])->name('tags.show');
```

Quick views (`resources/views/categories/show.blade.php` and `tags/show.blade.php`) — almost identical to `posts/index.blade.php` but with a header that says "Posts in [Category]" or "Tagged #[tag]". Reuse the same article markup.

## Step 5 — Try it

Visit `/posts`. You'll see author, category, tags, comments count on each card. Click a tag → goes to `/tags/laravel`. Click an author's category → goes to `/categories/tutorials`. Everything navigates.

## Verify it works

- ✅ Author name shows on each post card
- ✅ Category and tag links navigate
- ✅ Comment count is correct
- ✅ Empty state on a fresh tag still renders without errors

## Commit

```bash
git add .
git commit -m "feat: wire all blog relationships and use them in views"
```

## Common pitfalls

- **`Trying to access property "name" of null`** on `$post->author->name` → some posts have a `user_id` that doesn't exist (orphans from messed-up seeds). Re-run `migrate:fresh --seed`.
- **Pagination links break on filtered pages** → make sure the controller method passes `$category` or `$tag` along with `$posts`.

## What's next

➡️ `ch17-build.md` — turn on the SQL log, watch the N+1 explode, fix it with eager loading.
