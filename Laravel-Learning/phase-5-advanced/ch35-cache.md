# Chapter 35 — Caching

## Drivers

Configured in `config/cache.php`:
- `file` (default)
- `database`
- `redis` (recommended for production)
- `memcached`
- `array` (testing)

## Basic API

```php
use Illuminate\Support\Facades\Cache;

Cache::put('key', 'value', now()->addMinutes(10));
Cache::get('key');
Cache::get('key', 'default');
Cache::has('key');
Cache::forget('key');
Cache::flush();
Cache::increment('hits');
Cache::forever('key', 'value');
```

## Remember (the most useful one)

```php
$users = Cache::remember('users.all', 300, function () {
    return User::all();
});
```

If `users.all` is in cache, return it. Otherwise run the closure, cache the result for 300 seconds, return it.

## Cache tags (Redis/Memcached only)

```php
Cache::tags(['users', 'admins'])->put('key', 'value', 60);
Cache::tags('users')->flush();    // wipe everything tagged 'users'
```

## Cache strategies

- **Cache-aside**: Check cache → miss → load from DB → store in cache. (`remember()`)
- **Write-through**: Update DB and cache together.
- **Cache invalidation**: When data changes, forget the cached key. Use model events (Chapter 29).

## Common Mistakes

1. **Caching everything.** Cache only expensive queries that are read often and change rarely.
2. **Forgetting to invalidate.** Stale data is worse than no data sometimes.
3. **Caching paginated data with the page in the key.** It works but explodes cache size.

## Hands-on Task

Cache a "trending posts" query for 10 minutes. Then write a model observer on `Post` that flushes that cache key when any post is updated.

➡️ Next: `ch36-localization.md`
