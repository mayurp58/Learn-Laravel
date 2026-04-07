# Chapter 17 — Eager Loading and the N+1 Problem

This chapter is small but critical. Interviewers love to test it.

## The N+1 Problem

```php
$posts = Post::all();           // 1 query
foreach ($posts as $post) {
    echo $post->user->name;     // 1 query PER post
}
```

If you have 100 posts, that's **101 queries**. The "1" is the initial fetch, the "N" is one per post. Disaster at scale.

## The fix: Eager Loading

```php
$posts = Post::with('user')->get();   // 2 queries TOTAL
foreach ($posts as $post) {
    echo $post->user->name;
}
```

Behind the scenes:
1. `SELECT * FROM posts`
2. `SELECT * FROM users WHERE id IN (1, 2, 3, ...)`

## Multiple and nested relationships

```php
Post::with(['user', 'comments', 'tags'])->get();
Post::with('comments.user')->get();    // nested
Post::with(['comments' => fn($q) => $q->latest()->limit(5)])->get();
```

## `withCount`

```php
Post::withCount('comments')->get();
// each post now has $post->comments_count
```

## Detecting N+1 in development

In `app/Providers/AppServiceProvider.php`:

```php
use Illuminate\Database\Eloquent\Model;

public function boot(): void
{
    Model::preventLazyLoading(! app()->isProduction());
}
```

Now any lazy load throws an error in development. Forces you to write `with()`.

## Hands-on Task

1. Enable `preventLazyLoading` in development.
2. Write a controller that fetches all posts and renders titles + author names. Run it without `with()` first — see the error. Then add `with('user')`.
3. Use Laravel Debugbar (`composer require barryvdh/laravel-debugbar --dev`) to see query counts.

## Self-check

1. Explain N+1 in one sentence.
2. What does `Post::with('user')->get()` do under the hood?
3. How would you eager-load `comments.user`?

➡️ Next: `ch18-query-builder.md`
