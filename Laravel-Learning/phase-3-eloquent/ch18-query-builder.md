# Chapter 18 — Query Builder Deep Dive

Sometimes Eloquent isn't the right tool — bulk operations, complex reports, raw aggregates. Query Builder is the layer beneath Eloquent. (In fact, Eloquent uses it internally.)

## Basic queries

```php
use Illuminate\Support\Facades\DB;

DB::table('posts')->get();
DB::table('posts')->where('published', true)->get();
DB::table('posts')->where('views', '>', 100)->orderBy('created_at', 'desc')->get();
DB::table('posts')->find(1);
DB::table('posts')->first();
DB::table('posts')->count();
DB::table('posts')->sum('views');
DB::table('posts')->avg('views');
```

## Where variants

```php
->where('title', 'like', '%Laravel%')
->whereIn('id', [1, 2, 3])
->whereNotIn(...)
->whereBetween('views', [10, 100])
->whereNull('deleted_at')
->whereDate('created_at', today())
->whereMonth('created_at', 6)
->orWhere('status', 'draft')
```

Grouped wheres:
```php
->where(function ($q) {
    $q->where('a', 1)->orWhere('b', 2);
})
```

## Joins

```php
DB::table('posts')
    ->join('users', 'posts.user_id', '=', 'users.id')
    ->select('posts.*', 'users.name as author')
    ->get();

->leftJoin(...)
->rightJoin(...)
```

## Aggregates and grouping

```php
DB::table('posts')
    ->select('user_id', DB::raw('COUNT(*) as total'))
    ->groupBy('user_id')
    ->having('total', '>', 5)
    ->get();
```

## Subqueries

```php
$users = User::addSelect(['last_post' => Post::select('title')
    ->whereColumn('user_id', 'users.id')
    ->latest()
    ->limit(1),
])->get();
```

## Insert / Update / Delete

```php
DB::table('posts')->insert(['title' => 'X', 'body' => 'Y']);
DB::table('posts')->where('id', 1)->update(['title' => 'New']);
DB::table('posts')->where('views', 0)->delete();

DB::table('counters')->where('id', 1)->increment('views');
DB::table('counters')->where('id', 1)->decrement('stock', 5);
```

## Raw expressions (use carefully)

```php
DB::table('orders')
    ->select(DB::raw('DATE(created_at) as day, SUM(total) as revenue'))
    ->groupBy('day')
    ->get();
```

Bind parameters when raw:
```php
->whereRaw('total > ?', [100])
```

## CI comparison

CI's Query Builder is the closest analogue. Laravel's is more fluent and slightly more powerful, but the concepts transfer directly.

## Hands-on Task

Write a Query Builder query that returns the top 5 users by post count, including their name and post count.

➡️ Next: `ch19-transactions.md`
