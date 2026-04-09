# Chapter 19 — Transactions, Locking, and Big Datasets

## Transactions

When you do multiple writes that must succeed or fail together, wrap them in a transaction.

```php
use Illuminate\Support\Facades\DB;

DB::transaction(function () {
    $order = Order::create([...]);
    $order->items()->createMany([...]);
    $user->decrement('balance', $order->total);
});
```

If any line throws, everything rolls back. Clean and atomic.

Manual control:
```php
DB::beginTransaction();
try {
    // ...
    DB::commit();
} catch (\Throwable $e) {
    DB::rollBack();
    throw $e;
}
```

## Pessimistic locking

Prevent race conditions when multiple processes might update the same row.

```php
DB::transaction(function () {
    $account = Account::where('id', 1)->lockForUpdate()->first();
    $account->balance -= 100;
    $account->save();
});
```

`lockForUpdate()` issues `SELECT ... FOR UPDATE`.

## Chunking large datasets

```php
User::chunk(200, function ($users) {
    foreach ($users as $user) {
        // process
    }
});
```

Use this instead of `User::all()` when working with > a few thousand rows.

Even better — `chunkById`:
```php
User::chunkById(200, function ($users) { ... });
```

Or use a memory-cheap cursor:
```php
foreach (User::cursor() as $user) { ... }
```

## Common Mistakes

1. **Not wrapping multi-write business logic in transactions.** Half-completed orders are a real production bug.
2. **Using `chunk()` while updating the same column you're filtering on** — use `chunkById`.

## Hands-on Task

Write a tinker snippet that creates a `User` and 3 `Posts` for them inside a transaction. Force an exception on the third post and confirm nothing was committed.

🔨 **Build it for real:** Apply this chapter to project P2 — see [`build/p2-blog/ch19-build.md`](../build/p2-blog/ch19-build.md).

➡️ Next: `ch20-api-resources.md`
