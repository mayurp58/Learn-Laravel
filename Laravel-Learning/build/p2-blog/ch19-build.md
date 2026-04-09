# P2 · Chapter 19 — Apply: Transactions

**Read first:** `phase-3-eloquent/ch19-transactions.md`

## What you're building this chapter

The author dashboard. Logged-in users can create, edit, publish, and delete their own posts. The "publish with tags" flow is wrapped in a database transaction so partial saves are impossible.

## Step 1 — Author DashboardPostController

```bash
php artisan make:controller Dashboard/PostController --resource --model=Post
```

We're putting it in a `Dashboard/` subdirectory to keep author CRUD separate from public read controllers.

`app/Http/Controllers/Dashboard/PostController.php`:

```php
<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Post;
use App\Models\Tag;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class PostController extends Controller
{
    public function index(Request $request)
    {
        $posts = $request->user()->posts()
            ->with('category')
            ->latest()
            ->paginate(15);

        return view('dashboard.posts.index', compact('posts'));
    }

    public function create()
    {
        return view('dashboard.posts.create', [
            'categories' => Category::orderBy('name')->get(),
            'tags'       => Tag::orderBy('name')->get(),
        ]);
    }

    public function store(Request $request)
    {
        $data = $this->validateData($request);

        $post = DB::transaction(function () use ($data, $request) {
            $post = $request->user()->posts()->create([
                'category_id'  => $data['category_id'],
                'title'        => $data['title'],
                'slug'         => Str::slug($data['title']).'-'.Str::random(5),
                'excerpt'      => $data['excerpt'],
                'body'         => $data['body'],
                'status'       => $data['status'],
                'published_at' => $data['status'] === 'published' ? now() : null,
            ]);

            $post->tags()->sync($data['tag_ids'] ?? []);

            return $post;
        });

        return redirect()->route('dashboard.posts.index')->with('status', 'Post saved.');
    }

    public function edit(Post $post)
    {
        $this->authorize($post);

        return view('dashboard.posts.edit', [
            'post'       => $post->load('tags'),
            'categories' => Category::orderBy('name')->get(),
            'tags'       => Tag::orderBy('name')->get(),
        ]);
    }

    public function update(Request $request, Post $post)
    {
        $this->authorize($post);
        $data = $this->validateData($request);

        DB::transaction(function () use ($post, $data) {
            $post->update([
                'category_id'  => $data['category_id'],
                'title'        => $data['title'],
                'excerpt'      => $data['excerpt'],
                'body'         => $data['body'],
                'status'       => $data['status'],
                'published_at' => $data['status'] === 'published' && ! $post->published_at
                    ? now()
                    : $post->published_at,
            ]);

            $post->tags()->sync($data['tag_ids'] ?? []);
        });

        return redirect()->route('dashboard.posts.index')->with('status', 'Post updated.');
    }

    public function destroy(Post $post)
    {
        $this->authorize($post);
        $post->delete();
        return back()->with('status', 'Post deleted.');
    }

    private function validateData(Request $request): array
    {
        return $request->validate([
            'category_id' => ['required', 'exists:categories,id'],
            'title'       => ['required', 'string', 'max:255'],
            'excerpt'     => ['nullable', 'string', 'max:500'],
            'body'        => ['required', 'string'],
            'status'      => ['required', 'in:draft,published'],
            'tag_ids'     => ['array'],
            'tag_ids.*'   => ['exists:tags,id'],
        ]);
    }

    private function authorize(Post $post): void
    {
        abort_if($post->user_id !== request()->user()->id, 403);
    }
}
```

> **Why a transaction?** `posts()->create()` and `tags()->sync()` are two separate DB operations. If the first succeeds and the second fails (e.g. an FK violation, deadlock, network blip), you'd have a post with no tags — corrupted state. `DB::transaction()` rolls back both if either fails. **Always wrap multi-write operations in a transaction.** This is the kind of detail interviewers love.
>
> Note: I'm using `$this->authorize()` as a private method here, not Laravel's Policy-based `authorize()`. Policies are P3.

## Step 2 — Routes

`routes/web.php`:

```php
use App\Http\Controllers\Dashboard\PostController as DashboardPostController;

Route::middleware('auth')->prefix('dashboard')->name('dashboard.')->group(function () {
    Route::resource('posts', DashboardPostController::class)->except(['show']);
});
```

## Step 3 — Views

`resources/views/dashboard/posts/index.blade.php`:

```blade
@extends('layouts.app')
@section('content')
    <div class="flex justify-between items-center mb-6">
        <h1 class="text-2xl font-bold">My posts</h1>
        <a href="{{ route('dashboard.posts.create') }}" class="px-4 py-2 bg-blue-600 text-white rounded">New post</a>
    </div>

    @foreach ($posts as $post)
        <div class="bg-white border rounded p-4 mb-3">
            <h3 class="font-semibold">{{ $post->title }}</h3>
            <p class="text-xs text-gray-500">
                {{ ucfirst($post->status) }} · {{ $post->category->name }}
                @if ($post->published_at) · {{ $post->published_at->format('M j, Y') }} @endif
            </p>
            <div class="text-xs mt-2 space-x-3">
                <a href="{{ route('dashboard.posts.edit', $post) }}" class="text-blue-600">Edit</a>
                <form method="POST" action="{{ route('dashboard.posts.destroy', $post) }}" class="inline">
                    @csrf @method('DELETE')
                    <button class="text-red-600" onclick="return confirm('Delete?')">Delete</button>
                </form>
            </div>
        </div>
    @endforeach

    {{ $posts->links() }}
@endsection
```

`resources/views/dashboard/posts/create.blade.php` and `edit.blade.php` — large form. To keep this file shorter, here's create; clone for edit with `value="{{ old('title', $post->title) }}"`:

```blade
@extends('layouts.app')
@section('content')
    <h1 class="text-2xl font-bold mb-6">New post</h1>

    @if ($errors->any())
        <ul class="bg-red-50 border border-red-200 rounded p-3 mb-4 text-sm text-red-700 list-disc pl-5">
            @foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach
        </ul>
    @endif

    <form method="POST" action="{{ route('dashboard.posts.store') }}" class="space-y-4">
        @csrf

        <div>
            <label class="block text-sm font-medium mb-1">Title</label>
            <input name="title" value="{{ old('title') }}" class="w-full px-3 py-2 border rounded" required>
        </div>

        <div>
            <label class="block text-sm font-medium mb-1">Category</label>
            <select name="category_id" class="w-full px-3 py-2 border rounded">
                @foreach ($categories as $cat)
                    <option value="{{ $cat->id }}" @selected(old('category_id') == $cat->id)>{{ $cat->name }}</option>
                @endforeach
            </select>
        </div>

        <div>
            <label class="block text-sm font-medium mb-1">Excerpt</label>
            <textarea name="excerpt" rows="2" class="w-full px-3 py-2 border rounded">{{ old('excerpt') }}</textarea>
        </div>

        <div>
            <label class="block text-sm font-medium mb-1">Body</label>
            <textarea name="body" rows="12" class="w-full px-3 py-2 border rounded" required>{{ old('body') }}</textarea>
        </div>

        <div>
            <label class="block text-sm font-medium mb-1">Tags</label>
            <select name="tag_ids[]" multiple size="6" class="w-full px-3 py-2 border rounded">
                @foreach ($tags as $tag)
                    <option value="{{ $tag->id }}">{{ $tag->name }}</option>
                @endforeach
            </select>
            <p class="text-xs text-gray-500 mt-1">Hold Cmd/Ctrl to select multiple.</p>
        </div>

        <div>
            <label class="block text-sm font-medium mb-1">Status</label>
            <select name="status" class="w-full px-3 py-2 border rounded">
                <option value="draft">Draft</option>
                <option value="published">Published</option>
            </select>
        </div>

        <button class="px-4 py-2 bg-blue-600 text-white rounded">Save</button>
    </form>
@endsection
```

## Step 4 — Test it

Log in as the demo author. Go to `/dashboard/posts`. Create a new post with 3 tags, status published. Save. Visit `/posts` — your new post is at the top with the right tags. Edit it, change a tag, save again. Verify in tinker:

```php
\App\Models\Post::latest()->first()->tags;
```

## Step 5 — Force a transaction failure (educational)

Temporarily break the `tags()->sync()` line by passing a non-existent tag id (e.g. `99999`). Try to create a post. You should get an FK violation — and the post itself should NOT be created. Check `Post::count()` before and after.

That's the transaction working. Restore the line and move on.

## Verify it works

- ✅ Author can create / edit / delete only their own posts
- ✅ Visiting another author's edit URL returns 403
- ✅ Published posts appear on `/posts`
- ✅ Drafts do NOT appear on `/posts`
- ✅ Tags are correctly synced on update (unselecting a tag removes the relation)
- ✅ Force-failing the tag sync also rolls back the post insert

## Commit

```bash
git add .
git commit -m "feat: author dashboard for post CRUD with transactional tag sync"
```

## Common pitfalls

- **`Call to undefined method ... ->authorize()`** → I named our private method `authorize` which collides with Laravel's `Controller::authorize()`. If conflicts, rename to `authorizePost()`.
- **`exists:tags,id` validation fails** → make sure tags are seeded.
- **Slug collisions** → I append `Str::random(5)` to avoid them. Not pretty URLs, but no duplicates. Better solutions exist (e.g. unique-checking generator) — out of scope for P2.

## What's next

➡️ `ch20-build.md` — API resources: a `/api/posts` JSON endpoint that's a preview of P3.
