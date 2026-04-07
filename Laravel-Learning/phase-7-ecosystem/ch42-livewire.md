# Chapter 42 — Livewire 3

Livewire lets you build reactive, dynamic UIs without writing JavaScript. You write PHP components; Livewire handles the JS for you. Massive productivity for full-stack Laravel devs.

## Install

```bash
composer require livewire/livewire
```

In your Blade layout `<head>`:
```blade
@livewireStyles
```

Before `</body>`:
```blade
@livewireScripts
```

## Creating a component

```bash
php artisan make:livewire counter
```

Creates:
- `app/Livewire/Counter.php`
- `resources/views/livewire/counter.blade.php`

`Counter.php`:
```php
namespace App\Livewire;

use Livewire\Component;

class Counter extends Component
{
    public int $count = 0;

    public function increment() { $this->count++; }
    public function decrement() { $this->count--; }

    public function render()
    {
        return view('livewire.counter');
    }
}
```

`counter.blade.php`:
```blade
<div>
    <button wire:click="decrement">-</button>
    <span>{{ $count }}</span>
    <button wire:click="increment">+</button>
</div>
```

Use anywhere:
```blade
<livewire:counter />
```

Click the buttons. The number updates. No JavaScript was written. Behind the scenes Livewire makes AJAX requests, re-renders the component on the server, and patches the DOM.

## Real example: search-as-you-type

```php
class PostSearch extends Component
{
    public string $query = '';

    public function render()
    {
        return view('livewire.post-search', [
            'posts' => Post::where('title', 'like', "%{$this->query}%")->limit(10)->get(),
        ]);
    }
}
```

```blade
<div>
    <input wire:model.live.debounce.300ms="query" placeholder="Search posts...">
    <ul>
        @foreach ($posts as $post)
            <li>{{ $post->title }}</li>
        @endforeach
    </ul>
</div>
```

## Why this matters for jobs

Livewire is HUGE in the Laravel ecosystem right now. Many Laravel-only shops use Livewire instead of Vue/React. Knowing it well can be the difference in interviews.

## Hands-on Task

Build a Livewire `TodoList` component: add, complete, delete. No page reloads.

➡️ Next: `ch43-inertia.md`
