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

## Laravel 13: extending TTL with `Cache::touch()`

Before L13, extending a cached value's lifetime meant re-fetching, re-storing, and absorbing the cost of serialization. L13 adds a dedicated method:

```php
Cache::touch('users.all', 600);   // extend TTL by 600 more seconds
```

Use this for "sliding window" caches — e.g., session-like data that should stay alive as long as it's being read, without rewriting it on every hit.

## Laravel 13: `serializable_classes` security default

L13 hardens the cache against object-injection attacks. By default, the cache will refuse to unserialize arbitrary PHP objects. If you store objects (not arrays/scalars) in cache, you must allowlist them in `config/cache.php`:

```php
'serializable_classes' => [
    App\Data\CachedDashboardStats::class,
    App\Support\CachedPricingSnapshot::class,
],
```

If you don't store objects in cache, you don't need to do anything. The safest pattern (and one I'd recommend) is to cache plain arrays or DTOs you fully control, not Eloquent models.

## Laravel 13: cache prefix & session cookie naming changed

L13 changed the default naming scheme:

- Cache prefix: now hyphenated, e.g. `myapp-cache-` instead of `myapp_cache_`
- Session cookie: now uses `Str::snake()`, e.g. `my_app_session`

If you're upgrading an existing app and don't want users logged out / cache invalidated on deploy, **explicitly pin** these in `.env`:

```env
CACHE_PREFIX=myapp_cache_
REDIS_PREFIX=myapp_database_
SESSION_COOKIE=myapp_session
```

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

🔨 **Build it for real:** Apply this chapter to project P4 — see [`build/p4-projectly/ch35-build.md`](../build/p4-projectly/ch35-build.md).

➡️ Next: `ch36-localization.md`
