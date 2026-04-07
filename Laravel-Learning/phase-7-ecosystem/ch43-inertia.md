# Chapter 43 — Inertia.js (Laravel + Vue/React)

Inertia is the "modern monolith" approach: a real Laravel backend serves a real Vue or React frontend, but they're glued together so you don't write a separate API.

## How it works

- Routes still live in `routes/web.php`.
- Controllers return `Inertia::render('PostIndex', ['posts' => $posts])` instead of a Blade view.
- Inertia sends a JSON payload to a Vue/React component named `PostIndex.vue` (or `.jsx`).
- The frontend renders. URL changes feel like a SPA but server controls the data.

## Why use it

- Real SPA feel without building a real API + auth tokens
- Use Vue/React skills if you have them
- Type-safe with TypeScript
- Default for Laravel Breeze's Vue/React variants

## Setup (via Breeze)

```bash
composer require laravel/breeze --dev
php artisan breeze:install vue       # or react
npm install
npm run dev
```

You now have a complete auth scaffold using Inertia + Vue.

## Controller example

```php
use Inertia\Inertia;

public function index()
{
    return Inertia::render('Posts/Index', [
        'posts' => Post::with('user')->latest()->paginate(10),
    ]);
}
```

## Vue page

`resources/js/Pages/Posts/Index.vue`:
```vue
<script setup>
defineProps(['posts'])
</script>

<template>
    <div>
        <h1>Posts</h1>
        <article v-for="post in posts.data" :key="post.id">
            {{ post.title }}
        </article>
    </div>
</template>
```

## Inertia vs Livewire

- **Livewire** = backend-only, PHP-only, simpler.
- **Inertia** = real Vue/React frontend, more powerful, requires JS knowledge.

Both are valid. Livewire for backend-heavy devs, Inertia for those comfortable with frontend frameworks.

## Hands-on Task

Install Breeze (Vue or React variant). Build a posts index page using Inertia.

➡️ Next: `ch44-filament.md`
