# P2 · Chapter 17 — Apply: Eager loading and the N+1 fix

**Read first:** `phase-3-eloquent/ch17-eager-loading.md`

## What you're building this chapter

This is the chapter where you *see* the N+1 problem in your real app, then fix it. Few exercises in Laravel learning are as memorable as this one — do it carefully.

## Step 1 — Watch the queries on `/posts`

Open `app/Providers/AppServiceProvider.php` and add this inside `boot()`:

```php
public function boot(): void
{
    if (app()->environment('local')) {
        \DB::listen(function ($query) {
            \Log::info($query->sql, $query->bindings);
        });
    }
}
```

In one terminal:
```bash
tail -f storage/logs/laravel.log
```

In another:
```bash
php artisan serve
```

Visit http://localhost:8000/posts.

Now look at the log. You should see something horrifying — **dozens** of queries on a single page load:

```
select * from "posts" where ... limit 10 offset 0
select * from "users" where "users"."id" = 1 limit 1
select * from "users" where "users"."id" = 2 limit 1
select * from "users" where "users"."id" = 3 limit 1
... (one per post)
select * from "categories" where ... limit 1
... (one per post)
select * from "comments" where "post_id" = 1
... (one per post)
select * from "tags" inner join "post_tag" ...
... (one per post)
```

That's 1 query for the post list + ~40 follow-up queries (4 relationships × 10 posts). That's the N+1 problem. On a real site with 50 posts/page it would be 200+ queries per page load.

## Step 2 — Fix it with eager loading

In `PostController@index`:

```php
public function index()
{
    $posts = Post::with(['author', 'category', 'tags'])
        ->withCount('comments')
        ->published()
        ->recent()
        ->paginate(10);

    return view('posts.index', compact('posts'));
}
```

`with([...])` does an `IN`-based fetch for related rows in a single follow-up query each. `withCount('comments')` adds a `comments_count` column via a subquery so you don't have to load every comment just to count them.

## Step 3 — Update the view to use `comments_count`

In `resources/views/posts/index.blade.php`, replace:
```blade
{{ $post->comments->count() }} comments
```
with:
```blade
{{ $post->comments_count }} comments
```

This is the difference between "load all comments and count them in PHP" (slow) and "ask the DB to count them once" (fast).

## Step 4 — Also fix the show page

`PostController@show`:

```php
public function show(Post $post)
{
    abort_unless($post->status === 'published', 404);

    $post->load(['author', 'category', 'tags', 'comments']);

    return view('posts.show', compact('post'));
}
```

`->load()` is the equivalent of `with()` after a model is already fetched.

## Step 5 — Refresh and watch the log

Visit `/posts` again. Now look at the log. You should see roughly:

```
select * from "posts" where ... limit 10 offset 0
select * from "users" where "id" in (1, 2, 3, 4, 5)
select * from "categories" where "id" in (1, 2, 3)
select * from "tags" inner join "post_tag" on ... where "post_id" in (1,2,3,4,5,6,7,8,9,10)
```

**Four queries instead of forty.** That's eager loading.

## Step 6 — Same for the category and tag pages

`CategoryController@show`:
```php
$posts = $category->posts()
    ->with(['author', 'tags'])
    ->withCount('comments')
    ->published()
    ->recent()
    ->paginate(10);
```

`TagController@show`:
```php
$posts = $tag->posts()
    ->with(['author', 'category'])
    ->withCount('comments')
    ->published()
    ->recent()
    ->paginate(10);
```

## Step 7 — Add a query counter to the footer (local only)

This is a senior-level habit: visible feedback on query count keeps you honest.

In `resources/views/layouts/app.blade.php`, just before `</body>`:

```blade
@if (app()->environment('local'))
    <div class="fixed bottom-0 right-0 bg-yellow-100 text-xs px-3 py-1 border">
        Queries: {{ count(\DB::getQueryLog()) }}
    </div>
@endif
```

And in `AppServiceProvider::boot()`, also add:
```php
\DB::enableQueryLog();
```

Now every page shows its query count in the corner. Watch it stay low as you navigate. Any time it jumps, you have an N+1 to investigate.

## Verify it works

- ✅ `/posts` runs ≤ 6 queries instead of 40+
- ✅ Page renders identically to before
- ✅ The query counter in the bottom-right shows a small number
- ✅ Comment count is correct (use `withCount`)

## Commit

```bash
git add .
git commit -m "perf: eager-load relationships to fix N+1 on post pages"
```

## Common pitfalls

- **`{{ $post->comments_count }}` shows nothing** → you forgot `withCount('comments')` in the controller, or you typed `comment_count` (singular).
- **Eager loading is still slow** → check if you have a missing index on the FK. `EXPLAIN` the query in `psql`.
- **`with('author.profile')` deep-loading** → works the same way; dot notation chains. Not needed in P2 yet.

## What's next

➡️ `ch18-build.md` — Query Builder for the tag cloud (an aggregate query that doesn't fit Eloquent neatly).
