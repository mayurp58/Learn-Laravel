# P1 · Chapter 11 — Apply: Blade layouts, components, real UI

**Read first:** `phase-2-core/ch11-blade.md`
**Project state:** Working app with stub views.

## What you're building this chapter

Replace the eyesore stub views with a proper Blade layout, a reusable nav component, and basic Tailwind styling. By the end the app should look like something you'd actually screenshot for your portfolio README.

Tailwind is already installed (Breeze brought it in `ch08-build.md`).

## Step 1 — Create a layout

`resources/views/layouts/app.blade.php`:

```blade
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ config('app.name') }} — @yield('title', 'Bookmarks')</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-gray-50 min-h-screen text-gray-900">
    <x-nav />

    <main class="max-w-3xl mx-auto px-4 py-8">
        @if (session('status'))
            <div class="mb-4 rounded bg-green-100 border border-green-300 text-green-800 px-4 py-2">
                {{ session('status') }}
            </div>
        @endif

        @yield('content')
    </main>
</body>
</html>
```

## Step 2 — Create the nav component

```bash
php artisan make:component Nav --view
```

`resources/views/components/nav.blade.php`:

```blade
<nav class="bg-white border-b border-gray-200">
    <div class="max-w-3xl mx-auto px-4 py-3 flex justify-between items-center">
        <a href="{{ route('home') }}" class="font-semibold text-lg">Bookmarks</a>

        <div class="space-x-4 text-sm">
            @auth
                <a href="{{ route('bookmarks.index') }}">My Bookmarks</a>
                <a href="{{ route('bookmarks.create') }}">Add</a>
                <form method="POST" action="{{ route('logout') }}" class="inline">
                    @csrf
                    <button class="text-red-600">Logout</button>
                </form>
            @else
                <a href="{{ route('login') }}">Login</a>
                <a href="{{ route('register') }}">Register</a>
            @endauth
        </div>
    </div>
</nav>
```

## Step 3 — Rewrite the bookmark views to use the layout

`resources/views/bookmarks/index.blade.php`:

```blade
@extends('layouts.app')
@section('title', 'My Bookmarks')

@section('content')
    <div class="flex justify-between items-center mb-6">
        <h1 class="text-2xl font-bold">My Bookmarks</h1>
        <div class="text-sm space-x-3">
            <a href="{{ route('bookmarks.export.json') }}" class="text-blue-600">JSON</a>
            <a href="{{ route('bookmarks.export.csv') }}" class="text-blue-600">CSV</a>
        </div>
    </div>

    <form method="GET" class="mb-6">
        <input type="text" name="search" value="{{ request('search') }}"
               placeholder="Search title or description…"
               class="w-full px-3 py-2 border rounded">
    </form>

    @forelse ($bookmarks as $b)
        <article class="bg-white rounded shadow-sm border p-4 mb-3">
            <h2 class="font-semibold">
                <a href="{{ $b->url }}" target="_blank" class="text-blue-700 hover:underline">{{ $b->title }}</a>
            </h2>
            @if ($b->description)
                <p class="text-sm text-gray-600 mt-1">{{ $b->description }}</p>
            @endif
            @if ($b->tags)
                <p class="text-xs text-gray-500 mt-2">Tags: {{ $b->tags }}</p>
            @endif
            <div class="text-xs mt-3 space-x-3">
                <a href="{{ route('bookmarks.edit', $b) }}" class="text-gray-600">Edit</a>
                <form method="POST" action="{{ route('bookmarks.destroy', $b) }}" class="inline">
                    @csrf @method('DELETE')
                    <button class="text-red-600" onclick="return confirm('Delete this bookmark?')">Delete</button>
                </form>
            </div>
        </article>
    @empty
        <p class="text-gray-500">No bookmarks yet. <a href="{{ route('bookmarks.create') }}" class="text-blue-600">Add one</a>.</p>
    @endforelse
@endsection
```

`resources/views/bookmarks/create.blade.php`:

```blade
@extends('layouts.app')
@section('title', 'Add Bookmark')

@section('content')
    <h1 class="text-2xl font-bold mb-6">Add bookmark</h1>

    @if ($errors->any())
        <ul class="mb-4 rounded bg-red-50 border border-red-200 p-3 text-sm text-red-700 list-disc pl-5">
            @foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach
        </ul>
    @endif

    <form method="POST" action="{{ route('bookmarks.store') }}" class="space-y-4">
        @csrf
        <x-input name="url" label="URL" :value="old('url')" required />
        <x-input name="title" label="Title" :value="old('title')" required />
        <x-textarea name="description" label="Description" :value="old('description')" />
        <x-input name="tags" label="Tags" :value="old('tags')" placeholder="laravel,php" />

        <button class="px-4 py-2 bg-blue-600 text-white rounded">Save</button>
    </form>
@endsection
```

## Step 4 — Build small input components

```bash
php artisan make:component Input --view
php artisan make:component Textarea --view
```

`resources/views/components/input.blade.php`:
```blade
@props(['name', 'label', 'value' => '', 'placeholder' => ''])

<div>
    <label class="block text-sm font-medium mb-1">{{ $label }}</label>
    <input
        type="text"
        name="{{ $name }}"
        value="{{ $value }}"
        placeholder="{{ $placeholder }}"
        {{ $attributes->merge(['class' => 'w-full px-3 py-2 border rounded']) }}
    >
</div>
```

`resources/views/components/textarea.blade.php`:
```blade
@props(['name', 'label', 'value' => ''])

<div>
    <label class="block text-sm font-medium mb-1">{{ $label }}</label>
    <textarea
        name="{{ $name }}"
        rows="4"
        {{ $attributes->merge(['class' => 'w-full px-3 py-2 border rounded']) }}
    >{{ $value }}</textarea>
</div>
```

Update `edit.blade.php` and `show.blade.php` similarly — use `@extends('layouts.app')` and the same Tailwind structure.

## Step 5 — Wire the search

In `BookmarkController@index`:

```php
public function index(Request $request)
{
    $bookmarks = $request->user()
        ->bookmarks()
        ->when($request->search, function ($q, $search) {
            $q->where(function ($q) use ($search) {
                $q->where('title', 'ilike', "%{$search}%")
                  ->orWhere('description', 'ilike', "%{$search}%");
            });
        })
        ->latest()
        ->get();

    return view('bookmarks.index', compact('bookmarks'));
}
```

`ilike` is Postgres's case-insensitive `LIKE`. (MySQL would use `like`.) When you start typing in the search box and submit, results filter live.

## Step 6 — Run Vite and check the look

```bash
npm run dev
```

Visit `/bookmarks`. You should see a clean, styled list. Add a couple bookmarks, search for them, edit one. **Take a screenshot** for the README.

## Verify it works

- ✅ All pages share the same nav and layout
- ✅ Tailwind classes render (not raw class names)
- ✅ Search filters by title and description
- ✅ Empty state shows "No bookmarks yet" with a link

## Commit

```bash
git add .
git commit -m "feat: add Blade layout, components, and Tailwind UI"
```

## Common pitfalls

- **Tailwind classes don't work** → Vite isn't running. `npm run dev` in a separate terminal.
- **`@vite` directive errors** → run `npm install && npm run build` once.
- **Search returns nothing on Postgres** → you used `like` instead of `ilike`. Postgres `like` is case-sensitive.
- **`<x-nav />` shows literal text** → component file is named wrong. Must be `resources/views/components/nav.blade.php` (kebab-case file → kebab-case tag).

## What's next

➡️ `ch12-build.md` — sessions: build the "recently viewed" feature for the home page.
