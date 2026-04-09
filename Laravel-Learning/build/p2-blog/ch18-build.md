# P2 · Chapter 18 — Apply: Query Builder (tag cloud + archive aggregates)

**Read first:** `phase-3-eloquent/ch18-query-builder.md`

## What you're building this chapter

Two features that lean on raw Query Builder because Eloquent would be awkward:

1. **Tag cloud** — homepage list of tags weighted by post count
2. **Monthly archive** — sidebar list of "April 2026 (4)", "March 2026 (7)", …

Both are aggregate queries. Eloquent can do them but the Query Builder version is more direct.

## Step 1 — HomeController

```bash
php artisan make:controller HomeController
```

```php
<?php

namespace App\Http\Controllers;

use App\Models\Post;
use Illuminate\Support\Facades\DB;

class HomeController extends Controller
{
    public function index()
    {
        $latest = Post::with(['author', 'category'])
            ->withCount('comments')
            ->published()
            ->recent()
            ->limit(5)
            ->get();

        $tagCloud = DB::table('tags')
            ->leftJoin('post_tag', 'tags.id', '=', 'post_tag.tag_id')
            ->leftJoin('posts', function ($join) {
                $join->on('post_tag.post_id', '=', 'posts.id')
                     ->where('posts.status', 'published');
            })
            ->select('tags.name', 'tags.slug', DB::raw('COUNT(posts.id) as post_count'))
            ->groupBy('tags.id', 'tags.name', 'tags.slug')
            ->having(DB::raw('COUNT(posts.id)'), '>', 0)
            ->orderByDesc('post_count')
            ->limit(20)
            ->get();

        $archive = DB::table('posts')
            ->select(
                DB::raw("TO_CHAR(published_at, 'YYYY-MM') as month"),
                DB::raw('COUNT(*) as count')
            )
            ->where('status', 'published')
            ->whereNotNull('published_at')
            ->groupBy('month')
            ->orderByDesc('month')
            ->get();

        return view('home', compact('latest', 'tagCloud', 'archive'));
    }
}
```

> **Why Query Builder, not Eloquent?** The tag cloud is a join + group by + count — Eloquent can do `Tag::withCount(['posts' => fn($q) => $q->published()])`, which is cleaner. Honestly, use whichever you find readable. The point of this chapter is: when Eloquent gets contorted, drop to Query Builder. Senior devs are fluent in both.
>
> The archive query uses `TO_CHAR` (Postgres-specific). MySQL would use `DATE_FORMAT`. We picked Postgres in `ch00-prerequisites.md` partly so you'd see this.

## Step 2 — Route

`routes/web.php`:
```php
use App\Http\Controllers\HomeController;

Route::get('/', [HomeController::class, 'index'])->name('home');
```

## Step 3 — Home view

`resources/views/home.blade.php`:

```blade
@extends('layouts.app')
@section('title', 'Home')

@section('content')
    <h1 class="text-3xl font-bold mb-2">{{ config('app.name') }}</h1>
    <p class="text-gray-600 mb-8">Latest from the blog.</p>

    <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
        <div class="md:col-span-2">
            <h2 class="text-xl font-semibold mb-3">Recent posts</h2>
            @foreach ($latest as $post)
                <article class="bg-white border rounded p-4 mb-3">
                    <h3 class="font-semibold">
                        <a href="{{ route('posts.show', $post) }}" class="text-blue-700 hover:underline">{{ $post->title }}</a>
                    </h3>
                    <p class="text-xs text-gray-500">
                        {{ $post->author->name }} · {{ $post->published_at->format('M j, Y') }} · {{ $post->comments_count }} comments
                    </p>
                </article>
            @endforeach
            <a href="{{ route('posts.index') }}" class="text-blue-600">Browse all posts →</a>
        </div>

        <aside>
            <h2 class="text-xl font-semibold mb-3">Tag cloud</h2>
            <div class="space-x-2 space-y-2 mb-8">
                @foreach ($tagCloud as $tag)
                    @php
                        // Larger font for higher post count, capped
                        $size = min(1.5, 0.8 + ($tag->post_count * 0.1));
                    @endphp
                    <a href="{{ route('tags.show', $tag->slug) }}"
                       style="font-size: {{ $size }}rem"
                       class="inline-block text-blue-600 hover:underline">
                        #{{ $tag->name }}
                    </a>
                @endforeach
            </div>

            <h2 class="text-xl font-semibold mb-3">Archive</h2>
            <ul class="text-sm space-y-1">
                @foreach ($archive as $month)
                    <li>
                        {{ \Carbon\Carbon::createFromFormat('Y-m', $month->month)->format('F Y') }}
                        <span class="text-gray-500">({{ $month->count }})</span>
                    </li>
                @endforeach
            </ul>
        </aside>
    </div>
@endsection
```

## Step 4 — Try it

Visit http://localhost:8000. You should see:
- Top: 5 most recent posts
- Right sidebar: tag cloud with sized links
- Below tag cloud: archive months with counts

Click a tag — goes to `/tags/{slug}`.

## Step 5 — Inspect the SQL

Check `storage/logs/laravel.log`. You should see the three aggregate queries:
- One for `posts` join `users` join `categories`
- One for the tag cloud join+group
- One for the archive group

That's it. Three queries for the entire homepage.

## Verify it works

- ✅ Homepage renders 5 posts, tag cloud, archive
- ✅ Tag links navigate
- ✅ Tag font size varies with post count (most-used tag is biggest)
- ✅ Total queries on homepage stays low (check the corner counter)
- ✅ Archive shows month names like "April 2026", not raw `2026-04`

## Commit

```bash
git add .
git commit -m "feat: home page with tag cloud and archive (Query Builder aggregates)"
```

## Common pitfalls

- **`function date_format does not exist`** → you copied a MySQL example into a Postgres app. Use `TO_CHAR`.
- **Tag cloud is empty** → your `having()` excludes tags with zero posts but you have unattached tags. That's correct — verify by removing the `having` and confirming tag rows appear with `post_count = 0`.
- **`Trying to get property on string`** in the tag loop → Query Builder returns `stdClass`, not Eloquent models. Use `->name`, not `[name]`. This is fine but a common confusion when mixing the two builders.

## What's next

➡️ `ch19-build.md` — transactions: atomically publish a post with its tags.
