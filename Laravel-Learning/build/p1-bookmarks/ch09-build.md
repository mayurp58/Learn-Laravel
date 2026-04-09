# P1 · Chapter 9 — Apply: Form Requests for validation

**Read first:** `phase-2-core/ch09-validation.md`
**Project state:** CRUD + auth working. Validation is inline in the controller.

## What you're building this chapter

Move validation out of the controller into dedicated **Form Request** classes. This is the senior Laravel pattern: the controller stays thin, the validation rules live in their own class with their own messages, and you can add authorization there too.

## Step 1 — Generate two form requests

```bash
php artisan make:request StoreBookmarkRequest
php artisan make:request UpdateBookmarkRequest
```

## Step 2 — Implement `StoreBookmarkRequest`

`app/Http/Requests/StoreBookmarkRequest.php`:

```php
<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreBookmarkRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;   // any logged-in user can create their own bookmarks
    }

    public function rules(): array
    {
        return [
            'url'         => ['required', 'url', 'max:2048'],
            'title'       => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:5000'],
            'tags'        => ['nullable', 'string', 'max:255'],
        ];
    }

    public function messages(): array
    {
        return [
            'url.url' => 'That doesn\'t look like a valid URL. Did you forget https://?',
        ];
    }
}
```

## Step 3 — `UpdateBookmarkRequest`

Same rules, but with an authorization check that the bookmark belongs to the user:

```php
<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateBookmarkRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->id === $this->route('bookmark')->user_id;
    }

    public function rules(): array
    {
        return [
            'url'         => ['required', 'url', 'max:2048'],
            'title'       => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:5000'],
            'tags'        => ['nullable', 'string', 'max:255'],
        ];
    }
}
```

The `authorize()` method now does the work that was inline `abort_if(... 403)` in the controller.

## Step 4 — Slim down the controller

Update `BookmarkController.php`:

```php
use App\Http\Requests\StoreBookmarkRequest;
use App\Http\Requests\UpdateBookmarkRequest;

public function store(StoreBookmarkRequest $request)
{
    $bookmark = $request->user()->bookmarks()->create($request->validated());

    return redirect()->route('bookmarks.index')->with('status', 'Bookmark saved.');
}

public function update(UpdateBookmarkRequest $request, Bookmark $bookmark)
{
    $bookmark->update($request->validated());
    return redirect()->route('bookmarks.index')->with('status', 'Bookmark updated.');
}
```

Notice `$request->user()->bookmarks()->create(...)` — that requires a `bookmarks()` relationship on the User model. Add it:

`app/Models/User.php`:
```php
public function bookmarks()
{
    return $this->hasMany(Bookmark::class);
}
```

This relationship auto-fills `user_id` so you don't need `$data['user_id'] = ...` anymore.

## Step 5 — Show validation errors in the form

Update `resources/views/bookmarks/create.blade.php`:

```blade
<h1>Add bookmark</h1>

@if ($errors->any())
    <ul style="color:red">
        @foreach ($errors->all() as $error)
            <li>{{ $error }}</li>
        @endforeach
    </ul>
@endif

<form method="POST" action="{{ route('bookmarks.store') }}">
    @csrf
    <p>URL: <input name="url" value="{{ old('url') }}" required></p>
    <p>Title: <input name="title" value="{{ old('title') }}" required></p>
    <p>Description: <textarea name="description">{{ old('description') }}</textarea></p>
    <p>Tags: <input name="tags" value="{{ old('tags') }}" placeholder="laravel,php"></p>
    <button>Save</button>
</form>
```

`old('field')` repopulates the form with the user's input after a validation failure — a small UX detail Laravel gives you for free.

Do the same `@if ($errors->any())` block in `edit.blade.php`.

## Step 6 — Try invalid input

1. Visit `/bookmarks/create`
2. Submit with empty title → red error appears, form is repopulated
3. Submit with `not-a-url` → "That doesn't look like a valid URL" appears
4. Submit valid data → bookmark saves

## Verify it works

- ✅ Empty submission shows error list
- ✅ Old input is preserved on error
- ✅ Custom message for `url.url` shows
- ✅ User cannot update someone else's bookmark (test by guessing an ID — should 403)

## Commit

```bash
git add .
git commit -m "feat: extract validation into Form Request classes"
```

## Common pitfalls

- **`Class "App\Http\Requests\StoreBookmarkRequest" does not exist`** → typo in `use` statement, or you didn't run the generator.
- **`Property [bookmark] not found`** → you used `$this->bookmark` in `authorize()`. Use `$this->route('bookmark')` to access the route-bound model.
- **`bookmarks()` relationship returns null** → you put it on the wrong model. Goes on `User`, not `Bookmark`.

## What's next

➡️ `ch10-build.md` — work with responses (redirects, JSON, downloads). We'll add a "share as JSON" feature for fun.
