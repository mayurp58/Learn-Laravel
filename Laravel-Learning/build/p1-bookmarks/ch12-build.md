# P1 · Chapter 12 — Apply: Sessions ("recently viewed")

**Read first:** `phase-2-core/ch12-sessions.md`
**Project state:** Polished UI with auth + CRUD + search.

## What you're building this chapter

A "recently viewed" panel on the home page that shows the last 5 bookmarks the current user has visited. We'll store a list of bookmark IDs in the session — exercising session put/get and a tiny bit of array logic.

## Step 1 — Push to session on `show`

Update `BookmarkController@show`:

```php
public function show(Bookmark $bookmark, Request $request)
{
    abort_if($bookmark->user_id !== $request->user()->id, 403);

    // Track recently viewed in session (max 5, no duplicates)
    $recent = collect($request->session()->get('recent_bookmarks', []))
        ->reject(fn ($id) => $id === $bookmark->id)
        ->prepend($bookmark->id)
        ->take(5)
        ->values()
        ->all();

    $request->session()->put('recent_bookmarks', $recent);

    return view('bookmarks.show', compact('bookmark'));
}
```

## Step 2 — Read from session on the home page

Open `routes/web.php` and replace the home closure:

```php
Route::get('/', function (Request $request) {
    $recent = [];
    if ($request->user()) {
        $ids = $request->session()->get('recent_bookmarks', []);
        $recent = $request->user()->bookmarks()
            ->whereIn('id', $ids)
            ->get()
            ->sortBy(fn ($b) => array_search($b->id, $ids))
            ->values();
    }

    return view('home', compact('recent'));
})->name('home');
```

The `sortBy(array_search...)` keeps the bookmarks in the order the IDs were stored (most recent first), not the order Eloquent returns them.

Don't forget to import `Request`:
```php
use Illuminate\Http\Request;
```

## Step 3 — Build the home view

`resources/views/home.blade.php`:

```blade
@extends('layouts.app')
@section('title', 'Home')

@section('content')
    <h1 class="text-3xl font-bold mb-2">Bookmarks</h1>
    <p class="text-gray-600 mb-8">Your personal link collection.</p>

    @auth
        @if ($recent->isNotEmpty())
            <h2 class="text-xl font-semibold mb-3">Recently viewed</h2>
            <ul class="space-y-2 mb-8">
                @foreach ($recent as $b)
                    <li>
                        <a href="{{ route('bookmarks.show', $b) }}" class="text-blue-700 hover:underline">{{ $b->title }}</a>
                    </li>
                @endforeach
            </ul>
        @endif

        <a href="{{ route('bookmarks.index') }}" class="px-4 py-2 bg-blue-600 text-white rounded">View all bookmarks →</a>
    @else
        <a href="{{ route('login') }}" class="px-4 py-2 bg-blue-600 text-white rounded">Log in to get started</a>
    @endauth
@endsection
```

(Delete the original `resources/views/welcome.blade.php` or leave it — `home.blade.php` is what we're using now.)

## Step 4 — Add a "clear recently viewed" button

In the home view, below the recently-viewed list:

```blade
<form method="POST" action="{{ route('recent.clear') }}" class="mt-2">
    @csrf
    <button class="text-xs text-gray-500">Clear history</button>
</form>
```

In `routes/web.php`:

```php
Route::post('/recent/clear', function (Request $request) {
    $request->session()->forget('recent_bookmarks');
    return redirect()->route('home')->with('status', 'History cleared.');
})->middleware('auth')->name('recent.clear');
```

## Step 5 — Try it

1. Log in
2. Visit `/bookmarks`, click into 3 different bookmarks
3. Visit `/` → all 3 should appear under "Recently viewed", in order
4. Click another → the new one moves to the top
5. Visit the same one twice → it doesn't duplicate
6. Click "Clear history" → list disappears

## Verify it works

- ✅ Recently viewed shows 5 max
- ✅ No duplicates
- ✅ Most recent is at the top
- ✅ Logging out and back in clears the list (different session)
- ✅ "Clear history" works

## Commit

```bash
git add .
git commit -m "feat: add 'recently viewed' panel using session"
```

## Common pitfalls

- **Recently viewed shows IDs that don't belong to me** → you didn't filter by `$request->user()->bookmarks()`. Always scope.
- **List doesn't update** → session driver. Default is `database` in L13 — make sure you ran the initial migrations (`sessions` table should exist).
- **Order keeps changing** → you used `Collection::sortBy` without preserving the array_search index. Use the exact code above.

## What's next

➡️ `99-finish.md` — deploy P1, push to GitHub, retire the project, hand off to P2 (Blog).
