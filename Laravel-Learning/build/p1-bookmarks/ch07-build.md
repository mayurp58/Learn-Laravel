# P1 · Chapter 7 — Apply: Implement the BookmarkController

**Read first:** `phase-2-core/ch07-controllers.md`
**Project state:** Routes wired, controller and model are empty stubs.

## What you're building this chapter

Now we put real code in `BookmarkController` and create the `bookmarks` table so the data has somewhere to live. By the end you'll be able to add bookmarks via the form, see them on the index page, and delete them. No styling yet — that comes in `ch11-build.md`.

## Step 1 — Migration for the bookmarks table

```bash
php artisan make:migration create_bookmarks_table
```

Open the new file in `database/migrations/` and replace `up()`:

```php
public function up(): void
{
    Schema::create('bookmarks', function (Blueprint $table) {
        $table->id();
        $table->foreignId('user_id')->constrained()->cascadeOnDelete();
        $table->text('url');
        $table->string('title');
        $table->text('description')->nullable();
        $table->string('tags')->nullable();
        $table->timestamps();
    });
}
```

Run it:
```bash
php artisan migrate
```

## Step 2 — Configure the model

Open `app/Models/Bookmark.php`:

```php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Bookmark extends Model
{
    use HasFactory;

    protected $fillable = ['url', 'title', 'description', 'tags', 'user_id'];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
```

`$fillable` is the allowlist of columns that mass-assignment (`Bookmark::create([...])`) is allowed to write. Skipping it = `MassAssignmentException`.

## Step 3 — Implement the controller

Open `app/Http/Controllers/BookmarkController.php` and replace it:

```php
<?php

namespace App\Http\Controllers;

use App\Models\Bookmark;
use Illuminate\Http\Request;

class BookmarkController extends Controller
{
    public function index(Request $request)
    {
        $bookmarks = Bookmark::where('user_id', $request->user()->id)
            ->latest()
            ->get();

        return view('bookmarks.index', compact('bookmarks'));
    }

    public function create()
    {
        return view('bookmarks.create');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'url'         => ['required', 'url', 'max:2048'],
            'title'       => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'tags'        => ['nullable', 'string', 'max:255'],
        ]);

        $data['user_id'] = $request->user()->id;
        Bookmark::create($data);

        return redirect()->route('bookmarks.index')->with('status', 'Bookmark saved.');
    }

    public function show(Bookmark $bookmark)
    {
        abort_if($bookmark->user_id !== auth()->id(), 403);
        return view('bookmarks.show', compact('bookmark'));
    }

    public function edit(Bookmark $bookmark)
    {
        abort_if($bookmark->user_id !== auth()->id(), 403);
        return view('bookmarks.edit', compact('bookmark'));
    }

    public function update(Request $request, Bookmark $bookmark)
    {
        abort_if($bookmark->user_id !== auth()->id(), 403);

        $data = $request->validate([
            'url'         => ['required', 'url', 'max:2048'],
            'title'       => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'tags'        => ['nullable', 'string', 'max:255'],
        ]);

        $bookmark->update($data);

        return redirect()->route('bookmarks.index')->with('status', 'Bookmark updated.');
    }

    public function destroy(Bookmark $bookmark)
    {
        abort_if($bookmark->user_id !== auth()->id(), 403);
        $bookmark->delete();

        return redirect()->route('bookmarks.index')->with('status', 'Bookmark deleted.');
    }
}
```

> **Why `abort_if`?** Without it, user A could visit `/bookmarks/5/edit` and see user B's bookmark. We're inlining the check now; in `ch08-build.md` we'll move it into middleware, and in P2/P4 we'll move it again into Policies (the proper home).

## Step 4 — Stub the views

We'll write proper views in `ch11-build.md`. For now, create the bare minimum so the controller doesn't crash:

```bash
mkdir -p resources/views/bookmarks
```

`resources/views/bookmarks/index.blade.php`:
```blade
<h1>Bookmarks</h1>
@if (session('status'))
    <p>{{ session('status') }}</p>
@endif
<a href="{{ route('bookmarks.create') }}">Add</a>
<ul>
    @foreach ($bookmarks as $b)
        <li>
            <a href="{{ $b->url }}" target="_blank">{{ $b->title }}</a>
            — <a href="{{ route('bookmarks.edit', $b) }}">edit</a>
            <form method="POST" action="{{ route('bookmarks.destroy', $b) }}" style="display:inline">
                @csrf @method('DELETE')
                <button>delete</button>
            </form>
        </li>
    @endforeach
</ul>
```

`resources/views/bookmarks/create.blade.php`:
```blade
<h1>Add bookmark</h1>
<form method="POST" action="{{ route('bookmarks.store') }}">
    @csrf
    <p>URL: <input name="url" required></p>
    <p>Title: <input name="title" required></p>
    <p>Description: <textarea name="description"></textarea></p>
    <p>Tags: <input name="tags" placeholder="laravel,php"></p>
    <button>Save</button>
</form>
```

`resources/views/bookmarks/edit.blade.php`:
```blade
<h1>Edit bookmark</h1>
<form method="POST" action="{{ route('bookmarks.update', $bookmark) }}">
    @csrf @method('PUT')
    <p>URL: <input name="url" value="{{ $bookmark->url }}" required></p>
    <p>Title: <input name="title" value="{{ $bookmark->title }}" required></p>
    <p>Description: <textarea name="description">{{ $bookmark->description }}</textarea></p>
    <p>Tags: <input name="tags" value="{{ $bookmark->tags }}"></p>
    <button>Update</button>
</form>
```

`resources/views/bookmarks/show.blade.php`:
```blade
<h1>{{ $bookmark->title }}</h1>
<p><a href="{{ $bookmark->url }}">{{ $bookmark->url }}</a></p>
<p>{{ $bookmark->description }}</p>
<p>Tags: {{ $bookmark->tags }}</p>
<a href="{{ route('bookmarks.index') }}">Back</a>
```

## Step 5 — Test it without auth (yet)

Auth comes in `ch08-build.md`. For now, hardcode a user. In `tinker`:

```bash
php artisan tinker
```
```php
\App\Models\User::factory()->create(['email' => 'test@example.com', 'password' => bcrypt('password')]);
```

Then temporarily patch the controller's `index` and `store` to use `User::first()` instead of `$request->user()` so you can test the form flow without logging in. **Revert this in `ch08-build.md`.**

Visit http://localhost:8000/bookmarks/create, add one, visit http://localhost:8000/bookmarks. You should see it.

## Verify it works

- ✅ Migration ran (`php artisan migrate:status` shows `create_bookmarks_table` as Ran)
- ✅ Adding a bookmark via the form persists it
- ✅ The index page shows the bookmark
- ✅ Delete removes it

## Commit

```bash
git add .
git commit -m "feat: implement BookmarkController CRUD with stub views"
```

## Common pitfalls

- **`MassAssignmentException`** → you forgot `$fillable` on the model.
- **`SQLSTATE[42P01]` (Postgres)** → you forgot to run `php artisan migrate`.
- **`The url field must be a valid URL`** → Laravel's `url` validator requires a scheme. Use `https://example.com`, not `example.com`.
- **`Call to a member function id() on null`** → `$request->user()` is null because there's no auth yet. Use the temporary `User::first()` workaround until `ch08-build.md`.

## What's next

➡️ `ch08-build.md` — install Breeze, gate everything behind real auth, remove the temporary workarounds.
