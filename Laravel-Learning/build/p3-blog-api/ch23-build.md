# P3 · Chapter 23 — Apply: Policies

**Read first:** `phase-4-auth-api/ch23-policies.md`

## What you're building this chapter

A `PostPolicy` that defines who can do what to a post. Then use it everywhere (web dashboard + API) to replace inline `abort_if` checks. This is the senior pattern.

## Step 1 — Generate the policy

```bash
php artisan make:policy PostPolicy --model=Post
```

The `--model=Post` flag pre-fills the standard methods (`viewAny`, `view`, `create`, `update`, `delete`, `restore`, `forceDelete`).

## Step 2 — Implement PostPolicy

`app/Policies/PostPolicy.php`:

```php
<?php

namespace App\Policies;

use App\Models\Post;
use App\Models\User;

class PostPolicy
{
    public function viewAny(?User $user): bool
    {
        return true;   // anyone can browse the post list
    }

    public function view(?User $user, Post $post): bool
    {
        // Published posts visible to everyone, drafts only to the author
        if ($post->status === 'published') {
            return true;
        }

        return $user && $user->id === $post->user_id;
    }

    public function create(User $user): bool
    {
        return true;   // any authenticated user can create posts
    }

    public function update(User $user, Post $post): bool
    {
        return $user->id === $post->user_id;
    }

    public function delete(User $user, Post $post): bool
    {
        return $user->id === $post->user_id;
    }
}
```

> Note `?User $user` (nullable) on `view` and `viewAny` — that's how policies handle the "guest user" case. `update`/`delete` require an authenticated user, so the type is non-nullable.

## Step 3 — Auto-discovery (Laravel 11+)

In Laravel 11+ (and 13), policies are auto-discovered if they live in `App\Policies\` and follow the naming convention `XPolicy` for model `X`. You don't need to register them in `AuthServiceProvider` anymore. Verify:

```bash
php artisan tinker
```
```php
$user = \App\Models\User::first();
$post = \App\Models\Post::first();
$user->can('update', $post);   // true if same user, false otherwise
```

## Step 4 — Use the policy in the dashboard controller

Open `app/Http/Controllers/Dashboard/PostController.php`. Remove the private `authorize()` method and replace each `$this->authorize($post);` line with the framework version:

```php
public function edit(Post $post)
{
    $this->authorize('update', $post);
    // ...
}

public function update(Request $request, Post $post)
{
    $this->authorize('update', $post);
    // ...
}

public function destroy(Post $post)
{
    $this->authorize('delete', $post);
    // ...
}
```

`Controller::authorize($ability, $model)` calls the policy and throws a 403 if it returns false. **Same behavior as our hand-rolled `abort_if`, but the rules live in one place.**

You can also delete the `private function authorize(...)` method from the dashboard controller — it's no longer used.

## Step 5 — Try it

1. Log in as user A, create a post
2. Note the post ID
3. Log out, log in as user B
4. Visit `/dashboard/posts/{A's post id}/edit` → 403

Same as before, but the rule is now in `PostPolicy::update` and reusable.

## Step 6 — Test policies in tinker more

```php
$alice = User::factory()->create();
$bob = User::factory()->create();
$post = Post::factory()->create(['user_id' => $alice->id]);

$alice->can('update', $post);   // true
$bob->can('update', $post);     // false
$alice->can('delete', $post);   // true
auth()->guest() ? null : auth()->user()->can('view', $post);   // true if published
```

## Verify it works

- ✅ Editing your own post still works
- ✅ Editing someone else's post returns 403
- ✅ Deleting someone else's post returns 403
- ✅ The dashboard controller no longer has its private `authorize` method
- ✅ Tinker confirms `$user->can(...)` matches expectations

## Commit

```bash
git add .
git commit -m "refactor: extract authorization into PostPolicy"
```

## Common pitfalls

- **`This action is unauthorized`** for your own post → you typed `$user->id == $post->id` instead of `$post->user_id`. Common typo.
- **Policy not firing at all** → name mismatch. `PostPolicy` for `Post` model. The `--model=Post` flag prevents this.
- **`Method authorize does not exist`** → the controller doesn't extend `App\Http\Controllers\Controller`. The `AuthorizesRequests` trait on the base controller is what gives you `$this->authorize(...)`.

## What's next

➡️ `ch24-build.md` — REST endpoints (POST/PUT/DELETE for posts) + API versioning.
