# Chapter 28 — Facades (and Why They Aren't Static)

`Cache::get('key')`, `DB::table(...)`, `Mail::to(...)->send(...)` — these look static. They aren't. They're **facades**, and understanding them is a Laravel-developer rite of passage.

## What a facade actually does

`Cache::get('key')` is shorthand for:

```php
app('cache')->get('key');
```

The `Cache` class extends `Illuminate\Support\Facades\Facade`. That base class implements `__callStatic()`, which:
1. Resolves the underlying instance from the container (`app('cache')`)
2. Calls the method on it
3. Returns the result

So `Cache::get('key')` and `app('cache')->get('key')` are *the same thing*. Facades are just a syntactic shortcut.

## Why this matters

- **Facades are testable.** You can swap the underlying instance in tests with `Cache::shouldReceive(...)`.
- **They're not "global state" in the bad sense.** The container can return different instances per environment.
- **You can replace any facade with constructor injection** if you prefer testable, explicit code:
  ```php
  public function __construct(private \Illuminate\Contracts\Cache\Repository $cache) {}
  ```

## Common facades you'll use

```
Auth::user()
Cache::remember('key', 60, fn() => User::all())
Config::get('app.name')
DB::table('users')->get()
Hash::make('password')
Http::get('https://api.example.com')
Log::info('Something happened')
Mail::to($user)->send(new WelcomeMail())
Queue::push(new ProcessJob())
Redis::get('key')
Route::get('/x', ...)
Schema::create(...)
Session::get('key')
Storage::disk('s3')->put('file.txt', $contents)
URL::to('/')
Validator::make($data, $rules)
View::share('name', 'value')
```

## Helper functions vs facades

Many facades have shortcut helpers:
- `Auth::user()` ↔ `auth()->user()`
- `Session::get(...)` ↔ `session(...)`
- `Cache::get(...)` ↔ `cache(...)`
- `Config::get(...)` ↔ `config(...)`

Use whichever your team prefers. They're identical.

## When NOT to use facades

In *services / domain classes* that you want to be portable and unit-testable, prefer constructor injection. Save facades for controllers, providers, and quick scripts.

## Hands-on Task

1. Use `Cache::remember(...)` to cache a "popular posts" query for 5 minutes.
2. In another controller, achieve the same thing by injecting `\Illuminate\Contracts\Cache\Repository` in the constructor. Compare both styles.

🔨 **Build it for real:** Apply this chapter to project P4 — see [`build/p4-projectly/ch28-build.md`](../build/p4-projectly/ch28-build.md).

➡️ Next: `ch29-events.md`
