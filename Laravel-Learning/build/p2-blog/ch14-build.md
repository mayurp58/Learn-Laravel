# P2 · Chapter 14 — Apply: Factories and seeders

**Read first:** `phase-3-eloquent/ch14-seeders-factories.md`
**Project state:** All migrations run.

## What you're building this chapter

Realistic seed data: 5 categories, 15 tags, 5 authors, 30 posts (each tagged 1–3 times, some with comments). After this, your `/posts` page will look like a real blog instead of "Lorem Ipsum — title 1".

## Step 1 — Generate models + factories

```bash
php artisan make:model Category -f
php artisan make:model Post -f
php artisan make:model Tag -f
php artisan make:model Comment -f
```

The `-f` flag also generates the factory file.

Set `$fillable` on each model — easiest is `protected $guarded = [];` for now (turns off mass-assignment protection during seeding only). We'll tighten it in `ch15-build.md`.

## Step 2 — CategoryFactory

`database/factories/CategoryFactory.php`:

```php
public function definition(): array
{
    $name = fake()->unique()->words(2, true);
    return [
        'name' => ucfirst($name),
        'slug' => str($name)->slug(),
        'description' => fake()->sentence(),
    ];
}
```

## Step 3 — TagFactory

```php
public function definition(): array
{
    $name = fake()->unique()->word();
    return [
        'name' => $name,
        'slug' => str($name)->slug(),
    ];
}
```

## Step 4 — PostFactory

```php
public function definition(): array
{
    $title = fake()->sentence(6);
    return [
        'user_id'      => User::factory(),
        'category_id'  => Category::factory(),
        'title'        => rtrim($title, '.'),
        'slug'         => str($title)->slug(),
        'excerpt'      => fake()->paragraph(2),
        'body'         => collect(fake()->paragraphs(8))->implode("\n\n"),
        'status'       => 'published',
        'published_at' => fake()->dateTimeBetween('-1 year', 'now'),
    ];
}

public function draft(): static
{
    return $this->state(fn () => ['status' => 'draft', 'published_at' => null]);
}
```

The `draft()` state method lets you do `Post::factory()->draft()->create()`.

## Step 5 — CommentFactory

```php
public function definition(): array
{
    return [
        'post_id'      => Post::factory(),
        'author_name'  => fake()->name(),
        'author_email' => fake()->safeEmail(),
        'body'         => fake()->paragraph(),
    ];
}
```

## Step 6 — DatabaseSeeder

`database/seeders/DatabaseSeeder.php`:

```php
public function run(): void
{
    // 5 authors
    $users = User::factory(5)->create();

    // 5 categories
    $categories = Category::factory(5)->create();

    // 15 tags
    $tags = Tag::factory(15)->create();

    // 30 published posts spread across users + categories
    $posts = Post::factory(30)
        ->recycle($users)
        ->recycle($categories)
        ->create();

    // each post gets 1-3 random tags
    $posts->each(function ($post) use ($tags) {
        $post->tags()->attach($tags->random(rand(1, 3))->pluck('id'));
    });

    // ~half the posts get 1-5 comments
    $posts->random(15)->each(function ($post) {
        Comment::factory(rand(1, 5))->create(['post_id' => $post->id]);
    });

    // a known demo author with a known password
    User::factory()->create([
        'name' => 'Demo Author',
        'email' => 'demo@example.com',
    ]);
}
```

> `recycle($users)` is a Laravel feature that reuses an existing collection instead of creating new users for every post — important for keeping seed data realistic.

You'll need imports at the top of the seeder:
```php
use App\Models\{User, Category, Tag, Post, Comment};
```

## Step 7 — Add the `tags()` relationship to Post (preview of ch16)

`app/Models/Post.php`:

```php
public function tags()
{
    return $this->belongsToMany(Tag::class);
}
```

(We'll add the rest in `ch16-build.md`. The seeder needs this one now.)

## Step 8 — Seed

```bash
php artisan migrate:fresh --seed
```

`migrate:fresh` drops everything and re-runs migrations, then `--seed` runs the seeder. You should see:

```
INFO  Seeding database.
```

No errors.

## Step 9 — Verify in tinker

```bash
php artisan tinker
```
```php
\App\Models\Post::count();             // 30
\App\Models\Tag::count();              // 15
\App\Models\Comment::count();          // ~30-75
\App\Models\Post::first()->tags;       // collection of Tag models
\App\Models\Post::first()->title;      // a real-looking sentence
```

If all 5 lines work, your factories + seeders are right.

## Verify it works

- ✅ `migrate:fresh --seed` runs without errors
- ✅ 30 posts exist
- ✅ Each post has 1–3 tags attached
- ✅ Demo author exists with known credentials

## Commit

```bash
git add database/factories database/seeders app/Models
git commit -m "feat: add factories and seed 30 posts with categories, tags, comments"
```

## Common pitfalls

- **`SQLSTATE[23505] duplicate key value violates unique constraint`** on `slug` → Faker generated the same slug twice. Use `fake()->unique()` like the example does, or call `unique()->forget()` between batches.
- **`Class "User" not found`** in seeder → missing `use App\Models\User;`.
- **`Call to undefined method ... ->tags()`** → you forgot to add the `tags()` relationship in step 7.
- **Posts have `null` for `user_id`** → factory's `user_id => User::factory()` should create one if you don't pass `recycle()` users.

## What's next

➡️ `ch15-build.md` — proper Eloquent models: fillable, casts, accessors, scopes.
