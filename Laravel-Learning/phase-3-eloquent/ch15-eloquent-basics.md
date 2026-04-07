# Chapter 15 — Eloquent Basics

Eloquent is Laravel's ORM (Object-Relational Mapper). It maps database rows to PHP objects. Coming from CI's Active Record / Query Builder, this is a major upgrade.

## Creating a model

```bash
php artisan make:model Post -m   # also creates a migration
```

```php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Post extends Model
{
    use HasFactory;

    protected $fillable = ['title', 'body', 'user_id'];
}
```

By convention:
- Model `Post` ↔ table `posts` (pluralized)
- Primary key `id`
- Timestamps `created_at`, `updated_at`

Override if needed:
```php
protected $table = 'blog_posts';
protected $primaryKey = 'post_id';
public $timestamps = false;
```

## CRUD

```php
// Create
$post = Post::create(['title' => 'Hello', 'body' => 'World']);

// Read
$all = Post::all();
$post = Post::find(1);
$post = Post::findOrFail(1);
$first = Post::where('published', true)->first();
$count = Post::count();

// Update
$post->title = 'New title';
$post->save();

// Or:
$post->update(['title' => 'New title']);

// Delete
$post->delete();
Post::destroy(1);
Post::where('views', 0)->delete();
```

## Mass assignment

`fillable` (whitelist) prevents mass assignment vulnerabilities. Without it, you can't `Post::create([...])`.

```php
protected $fillable = ['title', 'body'];
// or
protected $guarded = ['id'];   // blacklist
```

## Casts

Convert columns automatically:

```php
protected $casts = [
    'published_at' => 'datetime',
    'meta' => 'array',          // JSON ↔ array
    'is_admin' => 'boolean',
    'price' => 'decimal:2',
    'status' => OrderStatus::class,   // enum cast
];
```

## Accessors and Mutators

```php
use Illuminate\Database\Eloquent\Casts\Attribute;

protected function title(): Attribute
{
    return Attribute::make(
        get: fn ($value) => ucfirst($value),
        set: fn ($value) => strtolower($value),
    );
}
```

## Scopes

Reusable query fragments:

```php
public function scopePublished($query)
{
    return $query->whereNotNull('published_at');
}
```

Use:
```php
Post::published()->latest()->get();
```

## CI comparison

CI's Active Record / Query Builder gave you arrays. Eloquent gives you full objects with relationships, casts, accessors, scopes, events. It's a different category of tool.

## Common Mistakes

1. **Forgetting `$fillable`** → can't create or update.
2. **Calling `->all()` on huge tables** → memory blowup. Use `chunk()` or `cursor()`.
3. **N+1 queries** — see Chapter 17.

## Hands-on Task

In tinker:
```php
Post::factory()->count(20)->create();
Post::count();
Post::latest()->take(5)->get()->pluck('title');
$post = Post::first();
$post->update(['title' => 'Updated!']);
$post->fresh();
```

➡️ Next: `ch16-relationships.md`
