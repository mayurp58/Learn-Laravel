# Chapter 10 — Responses, Redirects, and JSON

Every controller method returns *something*. That something becomes the HTTP response.

## Returning a string

```php
return 'Hello';   // text/html, 200
```

## Returning a view

```php
return view('posts.index', ['posts' => $posts]);
return view('posts.index', compact('posts'));
return view('posts.index')->with('posts', $posts);
```

## Returning JSON

```php
return response()->json(['ok' => true, 'data' => $posts]);
```

Or just return an array / Eloquent model / collection — Laravel auto-converts to JSON when the route is in `api.php` or the request expects JSON:

```php
return $posts;   // automatically JSON-encoded
```

## Redirects

```php
return redirect('/dashboard');
return redirect()->route('posts.show', $post);
return redirect()->back();
return back();                              // shorthand
return back()->withInput();                 // keep old form data
return redirect()->route('login')->with('error', 'Please log in');
```

The `with()` adds a flash message — available in the next request only via `session('error')`.

## Custom status codes and headers

```php
return response('Forbidden', 403);
return response()->json(['error' => 'Not found'], 404);
return response('Hello')
    ->header('X-Custom', 'value')
    ->cookie('name', 'value', 60);
```

## Aborting

```php
abort(404);
abort(403, 'Forbidden');
abort_if($user->banned, 403);
abort_unless($user->isAdmin(), 403);
```

These throw HTTP exceptions Laravel turns into responses.

## File downloads & streams

```php
return response()->download(storage_path('app/file.pdf'));
return response()->file(storage_path('app/file.pdf'));    // display inline
return response()->streamDownload(function () { echo $bigCsv; }, 'data.csv');
```

## Common Mistakes

1. **Forgetting to `return`** — `redirect()->route(...)` does nothing if not returned.
2. **Returning JSON from a `web.php` route without sessions disabled** — usually fine, but be aware of CSRF on POST.
3. **Using `dd($x)` in production code** — kills the response.

## Hands-on Task

1. Add a `/api/ping` route in `routes/web.php` that returns `response()->json(['status' => 'ok', 'time' => now()])`.
2. Visit it. Inspect the response in the browser network tab — confirm content-type is `application/json`.
3. Add a `/secret` route that calls `abort(403)`. Visit it — Laravel renders a 403 page automatically.

## Self-check

1. How do you flash a message to the next request?
2. What's the difference between `response()->download()` and `response()->file()`?
3. What happens if you forget `return`?

🔨 **Build it for real:** Apply this chapter to project P1 — see [`build/p1-bookmarks/ch10-build.md`](../build/p1-bookmarks/ch10-build.md).

➡️ Next: `ch11-blade.md`
