# Chapter 23 — Authorization: Gates and Policies

Authentication = "Who are you?"
Authorization = "What are you allowed to do?"

Laravel has two tools: **Gates** (closures) and **Policies** (classes per model).

## Gates (simple)

In `AppServiceProvider::boot()`:

```php
use Illuminate\Support\Facades\Gate;

Gate::define('access-admin', fn(User $user) => $user->is_admin);
```

Use:
```php
if (Gate::allows('access-admin')) { ... }
$this->authorize('access-admin');     // throws 403 if not
```

In Blade:
```blade
@can('access-admin') ... @endcan
```

## Policies (the right way for models)

```bash
php artisan make:policy PostPolicy --model=Post
```

```php
class PostPolicy
{
    public function viewAny(User $user): bool { return true; }
    public function view(User $user, Post $post): bool { return true; }
    public function create(User $user): bool { return true; }
    public function update(User $user, Post $post): bool
    {
        return $user->id === $post->user_id;
    }
    public function delete(User $user, Post $post): bool
    {
        return $user->id === $post->user_id || $user->is_admin;
    }
}
```

Laravel auto-discovers policies in `app/Policies/` matching model names.

## Using policies

In a controller:
```php
public function update(UpdatePostRequest $request, Post $post)
{
    $this->authorize('update', $post);
    $post->update($request->validated());
}
```

Or as middleware on a route:
```php
Route::put('/posts/{post}', [PostController::class, 'update'])
    ->middleware('can:update,post');
```

In Blade:
```blade
@can('update', $post)
    <a href="{{ route('posts.edit', $post) }}">Edit</a>
@endcan
```

## The `before` method

Lets admins bypass all checks:
```php
public function before(User $user, string $ability): ?bool
{
    return $user->is_admin ? true : null;
}
```

Returning `null` means "let normal checks run." Returning `true` short-circuits.

## Common Mistakes

1. **Not using policies and writing `if ($user->id !== $post->user_id) abort(403)` in every controller.** Policies are reusable.
2. **Forgetting to register policies** — Laravel auto-discovers, but in some configs you need to map them in `AuthServiceProvider`.

## Hands-on Task

Create a `PostPolicy`. Only the post's author can update or delete it. Wire it in `PostController`.

🔨 **Build it for real:** Apply this chapter to project P3 — see [`build/p3-blog-api/ch23-build.md`](../build/p3-blog-api/ch23-build.md).

➡️ Next: `ch24-rest-api.md`
