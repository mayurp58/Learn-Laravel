# P4 · Chapter 43 — Apply: Inertia (optional / read-only)

**Read first:** `phase-7-ecosystem/ch43-inertia.md`

## What you're building this chapter

**Honest take:** Projectly is already built with Livewire (ch42). Rewriting the task board in Inertia + Vue/React would mean maintaining two parallel UIs of the same feature, which dilutes the project. **You should skip the rewrite** and just *understand* Inertia well enough to talk about it in interviews.

This chapter therefore has two paths:

### Path A — Skim only (recommended)

Read the teaching chapter `phase-7-ecosystem/ch43-inertia.md`. Then internalize these talking points so you can answer "do you know Inertia?" in an interview without lying:

1. **What it is:** Inertia is a "modern monolith" pattern. Your Laravel controller returns `Inertia::render('Page', $props)` instead of a Blade view. The frontend is a Vue/React/Svelte SPA, but routing and data fetching go through Laravel.
2. **Why use it instead of Livewire:** when your team is already JS-fluent, when you want a real SPA feel (no full-page reloads on navigation), and when you have complex client-state needs that Alpine doesn't cover.
3. **Why use it instead of a separate SPA + API:** no API to maintain, no auth to mint, no CORS headaches. The "API" is the controller, returning props.
4. **Trade-off vs Livewire:** Inertia ships JS to the browser; Livewire ships HTML. Inertia feels snappier; Livewire is closer to "PHP-only stack."

That's enough to handle the question.

### Path B — Build a tiny Inertia page (only if curious)

If you want to actually run Inertia once, do a 30-minute spike on a side branch:

```bash
git checkout -b spike/inertia
composer require inertiajs/inertia-laravel
php artisan inertia:middleware
npm install @inertiajs/vue3 vue@latest @vitejs/plugin-vue
```

Replace the dashboard route with:
```php
Route::middleware('auth')->get('/dashboard-inertia', function () {
    return Inertia::render('Dashboard', [
        'team' => auth()->user()->teams->first(),
    ]);
});
```

Create `resources/js/Pages/Dashboard.vue`:
```vue
<template>
    <h1>{{ team.name }}</h1>
</template>

<script setup>
defineProps({ team: Object });
</script>
```

Spin up `npm run dev`, visit `/dashboard-inertia`. If you see the team name rendered, you've used Inertia.

**Then:**
```bash
git checkout main
git branch -D spike/inertia
```

Don't keep the spike — it's just for the muscle memory.

## Verify it works

- ✅ You can explain Inertia vs Livewire vs SPA+API in 60 seconds
- ✅ (Optional) The spike branch ran and was discarded

## Commit

Nothing to commit. This chapter is conceptual.

## What's next

➡️ `ch44-build.md` — Filament admin panel.
