# Chapter 6 — Routing

Routing in Laravel is *explicit*. You declare every URL and what handles it. (No auto-routing like CI's `controller/method/param` magic. Explicit is better — interviewers love hearing this.)

## The basics

```php
use Illuminate\Support\Facades\Route;

Route::get('/about', function () {
    return 'About page';
});

Route::post('/users', function () {
    // create user
});

Route::put('/users/{id}', ...);
Route::patch('/users/{id}', ...);
Route::delete('/users/{id}', ...);
```

Or matching multiple verbs:
```php
Route::match(['get', 'post'], '/contact', ...);
Route::any('/anything', ...);
```

## Pointing to a controller

```php
use App\Http\Controllers\PostController;

Route::get('/posts', [PostController::class, 'index']);
Route::get('/posts/{id}', [PostController::class, 'show']);
```

## Route Parameters

```php
Route::get('/users/{id}', function (string $id) {
    return "User $id";
});
```

Optional parameter (with default):
```php
Route::get('/users/{name?}', function ($name = 'Guest') {
    return "Hello $name";
});
```

Constraints:
```php
Route::get('/users/{id}', ...)->where('id', '[0-9]+');
Route::get('/posts/{slug}', ...)->whereAlpha('slug');
```

Or globally in a service provider with `Route::pattern('id', '[0-9]+')`.

## Named routes

```php
Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
```

Then anywhere in your code:
```php
route('dashboard');                          // /dashboard
route('user.show', ['id' => 42]);            // /users/42
return redirect()->route('dashboard');
```

**Why named routes matter:** if you change the URL from `/dashboard` to `/home`, you only fix it in one place. Every link uses the name.

### CI comparison
CI used `site_url('controller/method')` — string-based and breaks the moment you rename. Laravel's named routes are checked at runtime and survive refactors.

## Route Groups

When many routes share the same middleware, prefix, or namespace, group them.

```php
Route::middleware(['auth'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/dashboard', [AdminController::class, 'dashboard'])->name('dashboard');
    // URL: /admin/dashboard, name: admin.dashboard
    Route::get('/users', [AdminController::class, 'users'])->name('users');
    // URL: /admin/users, name: admin.users
});
```

## Resource Routes (RESTful)

```php
Route::resource('posts', PostController::class);
```

That single line creates 7 routes (index, create, store, show, edit, update, destroy). Run `php artisan route:list` to see them.

For APIs (no `create`/`edit` form routes):
```php
Route::apiResource('posts', PostController::class);
```

## Route Model Binding (you will love this)

Old way:
```php
Route::get('/posts/{id}', function ($id) {
    $post = Post::findOrFail($id);
    return view('posts.show', compact('post'));
});
```

Laravel way:
```php
Route::get('/posts/{post}', function (Post $post) {
    return view('posts.show', compact('post'));
});
```

When the parameter name (`{post}`) matches a type-hinted Eloquent model (`Post $post`), Laravel automatically does `Post::findOrFail($id)` for you.

You can even bind by a different column:
```php
Route::get('/posts/{post:slug}', function (Post $post) { ... });
```

### CI comparison
CI made you fetch the model in every controller method. Laravel does it for you, and 404s automatically if not found.

## Common Mistakes

1. **Forgetting `use App\Http\Controllers\X;`** — you'll get "Class not found."
2. **Defining routes in the wrong file.** `web.php` has session/CSRF; `api.php` is stateless. APIs go in `api.php`.
3. **Mixing route order with conflicting patterns.** Laravel matches top-down — put more specific routes first.
4. **Reusing URL strings everywhere instead of names.** Refactor pain.

## Hands-on Task

In `routes/web.php`:

```php
use App\Http\Controllers\PostController;

Route::get('/welcome/{name?}', function ($name = 'Guest') {
    return "Welcome, {$name}!";
})->name('welcome');

Route::prefix('blog')->name('blog.')->group(function () {
    Route::get('/', fn() => 'All posts')->name('index');
    Route::get('/{slug}', fn($slug) => "Post: $slug")->name('show');
});
```

Then:
1. Visit `/welcome` and `/welcome/Asha` and confirm.
2. Visit `/blog` and `/blog/my-first-post`.
3. Run `php artisan route:list` — find your routes by name.
4. In tinker, run: `route('blog.show', ['slug' => 'hello'])` — it should print the URL.

## Self-check

1. What does `Route::resource` create?
2. Why are named routes better than hard-coded URL strings?
3. What is route model binding?
4. What's the difference between `web.php` and `api.php`?

➡️ Next: `ch07-controllers.md`
