# P1 · Chapter 6 — Apply: Define the bookmark routes

**Read first:** `phase-2-core/ch06-routing.md`
**Project state going in:** Home route + lifecycle logging from `ch05-build.md`.

## What you're building this chapter

Routing is the spine of any web app. In Chapter 6 you learned the routing API — `Route::get`, route parameters, named routes, route groups, resource routes. Now we apply all of that to define every URL the bookmarks app will ever have.

We're not building controllers yet (that's `ch07-build.md`). We're declaring routes that point at controller methods that don't exist yet — and verifying the route table looks correct. This is the right order: design the URL structure first, then fill in the implementation.

## Step 1 — Plan the URL structure

From `00-spec.md`, here's what we need:

```
GET    /                           → home (already done)
GET    /bookmarks                  → list mine, with optional ?search=
GET    /bookmarks/create           → form
POST   /bookmarks                  → store
GET    /bookmarks/{bookmark}       → show + push to recently viewed
GET    /bookmarks/{bookmark}/edit  → edit form
PUT    /bookmarks/{bookmark}       → update
DELETE /bookmarks/{bookmark}       → destroy
```

That's exactly the standard "resourceful" pattern Laravel ships with — `Route::resource()` will give us all of it in one line.

## Step 2 — Generate the controller (empty for now)

```bash
php artisan make:controller BookmarkController --resource --model=Bookmark
```

This creates `app/Http/Controllers/BookmarkController.php` with empty `index()`, `create()`, `store()`, `show()`, `edit()`, `update()`, `destroy()` methods. It also asks if you want to create the missing `Bookmark` model — **say yes** by running:

```bash
php artisan make:model Bookmark
```

Don't add anything to either file yet. We're only wiring routes this chapter.

## Step 3 — Wire the routes

Open `routes/web.php` and replace its contents with:

```php
<?php

use App\Http\Controllers\BookmarkController;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    Log::info('Home page hit', ['ip' => request()->ip()]);
    return view('welcome');
})->name('home');

Route::resource('bookmarks', BookmarkController::class);
```

Two things happened:
1. We named the home route `home` so we can refer to it as `route('home')` in views.
2. `Route::resource()` registered all 7 RESTful routes in one line.

## Step 4 — Verify the route table

```bash
php artisan route:list
```

You should see something like:

```
GET|HEAD   /                              home
GET|HEAD   /bookmarks                     bookmarks.index   › BookmarkController@index
POST       /bookmarks                     bookmarks.store   › BookmarkController@store
GET|HEAD   /bookmarks/create              bookmarks.create  › BookmarkController@create
GET|HEAD   /bookmarks/{bookmark}          bookmarks.show    › BookmarkController@show
PUT|PATCH  /bookmarks/{bookmark}          bookmarks.update  › BookmarkController@update
DELETE     /bookmarks/{bookmark}          bookmarks.destroy › BookmarkController@destroy
GET|HEAD   /bookmarks/{bookmark}/edit     bookmarks.edit    › BookmarkController@edit
```

If yes — you've just declared the entire URL surface of P1 in 2 lines of code. **This is the moment Laravel pays off compared to CodeIgniter,** where you would have hand-written each route in `application/config/routes.php`.

## Step 5 — Test that one of the routes loads (even if empty)

Visit http://localhost:8000/bookmarks in your browser. You'll see a blank white page — that's correct. The route exists, the controller method exists, but it returns nothing yet. Look at the server terminal — you'll see a `200` status. The route worked.

Now visit http://localhost:8000/bookmarks/create. Same thing — blank but 200. The route table is wired correctly.

## Step 6 — Use named routes in your home view

Open `resources/views/welcome.blade.php`. Find the `<body>` tag and the existing welcome content. We're going to do a *minimal* change: at the very top of `<body>`, add:

```blade
<nav style="padding: 1rem; background: #f3f4f6;">
    <a href="{{ route('home') }}">Home</a> ·
    <a href="{{ route('bookmarks.index') }}">My Bookmarks</a> ·
    <a href="{{ route('bookmarks.create') }}">Add Bookmark</a>
</nav>
```

Refresh http://localhost:8000. You should see a simple gray nav bar at the top with three working links. Click each one — the URLs in the address bar should match the route table.

We'll throw out this inline-style nav in `ch11-build.md` when we build a real Blade layout. For now it just proves named routes work.

## Verify it works

- ✅ `php artisan route:list` shows 8 routes (home + 7 bookmark routes)
- ✅ Each named bookmark route resolves to a `BookmarkController` method
- ✅ The nav bar on `/` shows three working links
- ✅ Clicking "My Bookmarks" goes to `/bookmarks` and returns blank/200

## Commit

```bash
git add app/Http/Controllers/BookmarkController.php \
        app/Models/Bookmark.php \
        routes/web.php \
        resources/views/welcome.blade.php
git commit -m "feat: scaffold bookmark resource routes and nav"
```

## Common pitfalls

- **`Class "App\Http\Controllers\BookmarkController" does not exist`** → typo in the `use` statement, or you skipped `make:controller`. Re-run it.
- **`Route [bookmarks.index] not defined`** → you put `Route::resource()` *after* a `view('welcome')` that references the route. Order matters in some edge cases — but for `route()` helper inside a Blade view that's resolved per-request, order in `web.php` doesn't matter. If you see this error, you probably typed `bookmark.index` (singular) instead of `bookmarks.index`.
- **The `{bookmark}` placeholder confuses you** → it's a route-model binding parameter. Laravel will eventually use it to inject a `Bookmark` model instance into your controller methods. We'll cover this in `ch07-build.md`.
- **You see `Class "App\Models\Bookmark" not found`** → you didn't run `make:model Bookmark`. Run it.

## What's next

➡️ `ch07-build.md` — implement the controller methods. Start storing real bookmarks in the database.
