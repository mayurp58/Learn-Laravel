# P2 · Chapter 13 — Apply: Design and run the schema

**Read first:** `phase-3-eloquent/ch13-migrations.md`
**Project state:** Fresh blog scaffold + Breeze installed.

## What you're building this chapter

Every migration P2 needs, in one go. We're treating the schema as a design exercise — get it right now and we won't fight it for the rest of P2.

## Step 1 — Generate migrations

```bash
php artisan make:migration create_categories_table
php artisan make:migration create_posts_table
php artisan make:migration create_tags_table
php artisan make:migration create_post_tag_table
php artisan make:migration create_comments_table
```

## Step 2 — Categories

```php
public function up(): void
{
    Schema::create('categories', function (Blueprint $table) {
        $table->id();
        $table->string('name');
        $table->string('slug')->unique();
        $table->text('description')->nullable();
        $table->timestamps();
    });
}
```

## Step 3 — Posts

```php
public function up(): void
{
    Schema::create('posts', function (Blueprint $table) {
        $table->id();
        $table->foreignId('user_id')->constrained()->cascadeOnDelete();
        $table->foreignId('category_id')->constrained()->restrictOnDelete();
        $table->string('title');
        $table->string('slug')->unique();
        $table->text('excerpt')->nullable();
        $table->longText('body');
        $table->enum('status', ['draft', 'published'])->default('draft');
        $table->timestamp('published_at')->nullable();
        $table->timestamps();

        $table->index(['status', 'published_at']);
        $table->index(['user_id', 'status']);
    });
}
```

> **Why those indexes?** The public archive page will run `WHERE status = 'published' ORDER BY published_at DESC` — that's what `(status, published_at)` covers. The author dashboard will run `WHERE user_id = ? AND status = ?` — that's the second index. Senior Laravel devs design indexes from the queries they intend to run, not from gut feel.

## Step 4 — Tags

```php
public function up(): void
{
    Schema::create('tags', function (Blueprint $table) {
        $table->id();
        $table->string('name');
        $table->string('slug')->unique();
        $table->timestamps();
    });
}
```

## Step 5 — post_tag pivot

```php
public function up(): void
{
    Schema::create('post_tag', function (Blueprint $table) {
        $table->foreignId('post_id')->constrained()->cascadeOnDelete();
        $table->foreignId('tag_id')->constrained()->cascadeOnDelete();
        $table->primary(['post_id', 'tag_id']);
    });
}
```

> Pivot table naming: alphabetical, singular, snake_case → `post_tag`. Laravel finds it automatically. No `id` column needed; the composite primary key is the row identity.

## Step 6 — Comments

```php
public function up(): void
{
    Schema::create('comments', function (Blueprint $table) {
        $table->id();
        $table->foreignId('post_id')->constrained()->cascadeOnDelete();
        $table->string('author_name');
        $table->string('author_email');
        $table->text('body');
        $table->timestamps();

        $table->index('post_id');
    });
}
```

## Step 7 — Run them

```bash
php artisan migrate
```

Check the result:

```bash
psql blog -c "\dt"
```

You should see all 5 new tables plus the Laravel defaults.

## Step 8 — Verify foreign keys work

```bash
php artisan tinker
```
```php
DB::table('posts')->insert(['title' => 'X', 'slug' => 'x', 'body' => 'y', 'user_id' => 999, 'category_id' => 999, 'created_at' => now(), 'updated_at' => now()]);
```

You should get a foreign key violation — that means constraints are working. Good.

## Verify it works

- ✅ All 5 migrations show as Ran in `php artisan migrate:status`
- ✅ Inserting an invalid `user_id` fails the FK constraint
- ✅ The `post_tag` table has a composite PK (no `id` column)

## Commit

```bash
git add database/migrations
git commit -m "feat: add blog schema (categories, posts, tags, post_tag, comments)"
```

## Common pitfalls

- **`column "user_id" does not exist`** when trying `constrained()` → you're missing the `users` table. Run `php artisan migrate:fresh` to recreate the entire schema in order.
- **Migration order matters** → Laravel runs them alphabetically by filename (which encodes timestamp). If you generated them in a different order than shown above, FK references may fail. Solution: rename files so the order is right, or accept the order Laravel chose and adjust constraints.

## What's next

➡️ `ch14-build.md` — write factories and seeders to fill the blog with 30+ realistic posts.
