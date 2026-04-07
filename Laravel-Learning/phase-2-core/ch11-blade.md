# Chapter 11 — Blade Templating

Blade is Laravel's templating engine. Think: PHP with cleaner syntax, layouts, components, and automatic XSS escaping.

## Basics

```blade
{{-- this is a Blade comment, not rendered --}}

<h1>{{ $title }}</h1>            {{-- escaped output --}}
<div>{!! $htmlContent !!}</div>  {{-- raw output, dangerous --}}
```

`{{ }}` automatically escapes HTML to prevent XSS. Use `{!! !!}` only for trusted HTML.

## Control structures

```blade
@if ($user->isAdmin())
    Admin
@elseif ($user->isEditor())
    Editor
@else
    Viewer
@endif

@unless ($user->verified) ... @endunless

@isset($name) ... @endisset
@empty($posts) ... @endempty

@foreach ($posts as $post)
    <li>{{ $post->title }}</li>
@endforeach

@forelse ($posts as $post)
    <li>{{ $post->title }}</li>
@empty
    <li>No posts yet</li>
@endforelse

@for ($i = 0; $i < 5; $i++)
    {{ $i }}
@endfor
```

## Layouts (the modern way: components)

`resources/views/layouts/app.blade.php`:
```blade
<!DOCTYPE html>
<html>
<head>
    <title>{{ $title ?? 'My App' }}</title>
</head>
<body>
    <nav>...</nav>
    <main>
        {{ $slot }}
    </main>
</body>
</html>
```

Then in any view:
```blade
<x-layouts.app title="Posts">
    <h1>All Posts</h1>
    @foreach ($posts as $post)
        <article>{{ $post->title }}</article>
    @endforeach
</x-layouts.app>
```

`<x-layouts.app>` resolves to `resources/views/layouts/app.blade.php`. The content between the tags becomes `$slot`.

## Components

```bash
php artisan make:component Alert
```

Generates:
- `app/View/Components/Alert.php`
- `resources/views/components/alert.blade.php`

```php
class Alert extends Component
{
    public function __construct(public string $type = 'info', public string $message) {}

    public function render() { return view('components.alert'); }
}
```

`alert.blade.php`:
```blade
<div class="alert alert-{{ $type }}">{{ $message }}</div>
```

Use:
```blade
<x-alert type="success" message="Saved!" />
```

### Anonymous components (no PHP class)

Just create `resources/views/components/card.blade.php`:
```blade
@props(['title'])
<div class="card">
    <h2>{{ $title }}</h2>
    {{ $slot }}
</div>
```

Use:
```blade
<x-card title="Hello">
    Some content
</x-card>
```

## Including partials

```blade
@include('partials.nav')
@include('partials.user-card', ['user' => $user])
```

But prefer components for new code.

## Useful directives

```blade
@auth ... @endauth                     {{-- only if logged in --}}
@guest ... @endguest                   {{-- only if not logged in --}}
@can('update', $post) ... @endcan      {{-- authorization --}}

@csrf                                  {{-- CSRF token in forms --}}
@method('PUT')                         {{-- spoof PUT/PATCH/DELETE --}}

@error('field')
    <span class="text-red-500">{{ $message }}</span>
@enderror

@class(['btn', 'btn-primary' => $isPrimary])  {{-- conditional CSS --}}
```

## A complete form example

```blade
<x-layouts.app title="New Post">
    <form method="POST" action="{{ route('posts.store') }}">
        @csrf

        <input name="title" value="{{ old('title') }}">
        @error('title') <p>{{ $message }}</p> @enderror

        <textarea name="body">{{ old('body') }}</textarea>
        @error('body') <p>{{ $message }}</p> @enderror

        <button type="submit">Save</button>
    </form>
</x-layouts.app>
```

## CI comparison

CI views were just `<?php echo $title; ?>` — no escaping by default, no layouts, no components. Blade is a real templating engine.

## Common Mistakes

1. **Using `{!! !!}` on user input** — XSS vulnerability. Use `{{ }}` unless you're certain.
2. **Forgetting `@csrf`** in forms — you'll get a 419 error.
3. **Forgetting `@method('PUT')`** when submitting an update form via POST.

## Hands-on Task

1. Create `resources/views/layouts/app.blade.php` as shown above.
2. Create `resources/views/posts/index.blade.php` that loops over `$posts`.
3. Create `resources/views/posts/create.blade.php` with a form to create a post.
4. Wire `PostController@index` to return `view('posts.index', compact('posts'))`.
5. Make `Post::create(...)` work via the form. (You might need to use `Post::factory()` in tinker to seed some posts first.)

## Self-check

1. Why use `{{ }}` instead of `{!! !!}`?
2. What's the difference between `@include` and `<x-...>` components?
3. What does `@csrf` output?

➡️ Next: `ch12-sessions.md`
