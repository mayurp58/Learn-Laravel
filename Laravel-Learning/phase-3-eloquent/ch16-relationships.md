# Chapter 16 — Eloquent Relationships

This is Eloquent's killer feature. Relationships let you traverse data like objects.

## One-to-Many (most common)

```php
// Post.php
public function user()
{
    return $this->belongsTo(User::class);
}

// User.php
public function posts()
{
    return $this->hasMany(Post::class);
}
```

Use:
```php
$post->user->name;
$user->posts;             // collection
$user->posts()->where('published', true)->get();
```

## One-to-One

```php
// User.php
public function profile()
{
    return $this->hasOne(Profile::class);
}
```

## Many-to-Many

Requires a pivot table (e.g. `post_tag` with `post_id`, `tag_id`).

```php
// Post.php
public function tags()
{
    return $this->belongsToMany(Tag::class);
}

// Tag.php
public function posts()
{
    return $this->belongsToMany(Post::class);
}
```

Attach / detach:
```php
$post->tags()->attach([1, 2, 3]);
$post->tags()->detach(2);
$post->tags()->sync([1, 3, 5]);   // replaces all
```

## Has-Many-Through

`Country` has many `Posts` through `Users`:

```php
public function posts()
{
    return $this->hasManyThrough(Post::class, User::class);
}
```

## Polymorphic

A `Comment` belongs to either a `Post` or a `Video`:

```php
// Comment.php
public function commentable()
{
    return $this->morphTo();
}

// Post.php
public function comments()
{
    return $this->morphMany(Comment::class, 'commentable');
}

// Video.php (same)
```

Migration adds `commentable_id` and `commentable_type`.

## Querying through relationships

```php
User::with('posts')->get();              // eager load
User::has('posts')->get();               // users who have at least 1 post
User::has('posts', '>=', 3)->get();
User::whereHas('posts', fn($q) => $q->where('published', true))->get();
User::withCount('posts')->get();         // users with posts_count
```

## CI comparison

In CI you wrote joins manually for everything. Eloquent does it cleanly with `with()`, `whereHas()`, etc.

## Hands-on Task

1. Create `User`, `Post`, `Comment`, `Tag` models with the relationships above.
2. In tinker, create a user, give them 5 posts, give each post 3 comments and 2 tags.
3. Try: `$user->posts->first()->comments`, `$user->posts->first()->tags->pluck('name')`.

🔨 **Build it for real:** Apply this chapter to project P2 — see [`build/p2-blog/ch16-build.md`](../build/p2-blog/ch16-build.md).

➡️ Next: `ch17-eager-loading.md`
