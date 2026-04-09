# P1 · Chapter 10 — Apply: Responses (redirects, JSON, downloads)

**Read first:** `phase-2-core/ch10-responses.md`
**Project state:** Form Requests in place.

## What you're building this chapter

Three small additions that exercise different response types:

1. A **JSON export** endpoint (`GET /bookmarks/export.json`) that returns all your bookmarks as JSON
2. A **CSV download** (`GET /bookmarks/export.csv`)
3. A polished redirect with a query string after store

## Step 1 — Add the export routes

In `routes/web.php`, inside the `auth` group, **above** the `Route::resource(...)` line:

```php
Route::get('bookmarks/export.json', [BookmarkController::class, 'exportJson'])->name('bookmarks.export.json');
Route::get('bookmarks/export.csv',  [BookmarkController::class, 'exportCsv'])->name('bookmarks.export.csv');
```

> Order matters: these must be **above** `Route::resource('bookmarks', ...)` so Laravel doesn't think `export.json` is a `{bookmark}` parameter.

## Step 2 — Implement the methods

In `BookmarkController.php`:

```php
public function exportJson(Request $request)
{
    return response()->json(
        $request->user()->bookmarks()->latest()->get()
    );
}

public function exportCsv(Request $request)
{
    $bookmarks = $request->user()->bookmarks()->latest()->get();

    $callback = function () use ($bookmarks) {
        $out = fopen('php://output', 'w');
        fputcsv($out, ['title', 'url', 'description', 'tags', 'created_at']);
        foreach ($bookmarks as $b) {
            fputcsv($out, [$b->title, $b->url, $b->description, $b->tags, $b->created_at]);
        }
        fclose($out);
    };

    return response()->streamDownload(
        $callback,
        'bookmarks-'.now()->format('Y-m-d').'.csv',
        ['Content-Type' => 'text/csv']
    );
}
```

`response()->streamDownload()` sends a file to the browser without buffering it in memory — important once your bookmark list grows.

## Step 3 — Add export links to the index view

In `resources/views/bookmarks/index.blade.php`, near the top:

```blade
<p>
    Export:
    <a href="{{ route('bookmarks.export.json') }}">JSON</a> ·
    <a href="{{ route('bookmarks.export.csv') }}">CSV</a>
</p>
```

## Step 4 — Try it

1. Add a few bookmarks
2. Visit `/bookmarks/export.json` → JSON appears in the browser
3. Click the CSV link → file downloads as `bookmarks-2026-04-08.csv`
4. Open the CSV in a spreadsheet to confirm columns

## Verify it works

- ✅ JSON endpoint returns valid JSON of *only your own* bookmarks
- ✅ CSV downloads with the right filename and headers
- ✅ Logging out and visiting `/bookmarks/export.json` redirects to login

## Commit

```bash
git add .
git commit -m "feat: add JSON and CSV export for bookmarks"
```

## Common pitfalls

- **Visiting `/bookmarks/export.json` returns "404 No bookmark found"** → your routes are in the wrong order. Move the export routes *above* `Route::resource(...)`.
- **CSV downloads but is empty** → you used `dd()` somewhere in `exportCsv` and forgot to remove it.
- **JSON shows other people's bookmarks** → you used `Bookmark::all()` instead of `$request->user()->bookmarks()`.

## What's next

➡️ `ch11-build.md` — replace the ugly stub views with a proper Blade layout, components, and basic Tailwind styling.
