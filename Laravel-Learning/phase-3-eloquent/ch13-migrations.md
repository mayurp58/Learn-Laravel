# Chapter 13 — Migrations and Schema Builder

Migrations are version control for your database schema. Instead of "Hey team, run this SQL on staging," you commit a migration file. Anyone who clones the repo runs `php artisan migrate` and gets the same DB structure.

## Creating a migration

```bash
php artisan make:migration create_posts_table
php artisan make:migration add_status_to_posts_table --table=posts
```

Files land in `database/migrations/` with a timestamp prefix so they run in order.

## Anatomy

```php
public function up(): void
{
    Schema::create('posts', function (Blueprint $table) {
        $table->id();                              // bigint primary
        $table->string('title');                   // varchar 255
        $table->text('body');
        $table->string('slug')->unique();
        $table->foreignId('user_id')->constrained()->cascadeOnDelete();
        $table->boolean('published')->default(false);
        $table->timestamp('published_at')->nullable();
        $table->timestamps();                      // created_at + updated_at
        $table->softDeletes();                     // deleted_at
    });
}

public function down(): void
{
    Schema::dropIfExists('posts');
}
```

## Common column types

```
$table->id();
$table->bigIncrements('id');
$table->uuid('id')->primary();
$table->string('name', 100);
$table->text('body');
$table->longText('content');
$table->integer('age');
$table->unsignedBigInteger('user_id');
$table->decimal('price', 8, 2);
$table->boolean('active');
$table->date('birthday');
$table->dateTime('starts_at');
$table->timestamp('published_at')->nullable();
$table->json('meta');
$table->enum('status', ['draft', 'published']);
$table->foreignId('user_id')->constrained();
```

## Modifiers

```
->nullable()
->default('value')
->unique()
->index()
->after('column_name')
->comment('...')
```

## Foreign keys (the modern way)

```php
$table->foreignId('user_id')->constrained()->cascadeOnDelete();
```

That single line:
- Creates `user_id` as `unsignedBigInteger`
- Adds an FK referencing `users.id`
- Sets `ON DELETE CASCADE`

## Modifying tables

```bash
php artisan make:migration add_views_to_posts_table --table=posts
```

```php
public function up(): void
{
    Schema::table('posts', function (Blueprint $table) {
        $table->unsignedInteger('views')->default(0)->after('body');
    });
}

public function down(): void
{
    Schema::table('posts', function (Blueprint $table) {
        $table->dropColumn('views');
    });
}
```

## Migration commands

```bash
php artisan migrate                # run pending migrations
php artisan migrate:rollback       # rollback last batch
php artisan migrate:reset          # rollback all
php artisan migrate:refresh        # rollback all + run all
php artisan migrate:fresh          # drop all tables + run all (faster)
php artisan migrate:fresh --seed   # also seed
php artisan migrate:status         # what's run, what isn't
```

**`migrate:fresh` is your friend in development.** Wipe and rebuild constantly.

## CI comparison

CI3 had migrations but they were rarely used in real projects. Laravel migrations are central to every workflow. You'll use them daily.

## Common Mistakes

1. **Editing an old migration after it's been run.** Don't. Create a new one. Old migrations are immutable history.
2. **Forgetting `down()`** — needed for rollback.
3. **Renaming columns without `doctrine/dbal`** in older Laravel — Laravel 10+ handles this natively.

## Hands-on Task

1. Create a migration for a `categories` table (`id`, `name`, `slug`, timestamps).
2. Create a migration for `posts` with `title`, `body`, `slug`, `category_id` (foreign key), `published_at` nullable, timestamps.
3. Run `php artisan migrate:fresh` and verify in phpMyAdmin.

🔨 **Build it for real:** Apply this chapter to project P2 (Blog Platform) — see [`build/p2-blog/ch13-build.md`](../build/p2-blog/ch13-build.md). If you haven't started P2 yet, read [`build/p2-blog/00-spec.md`](../build/p2-blog/00-spec.md) and [`01-setup.md`](../build/p2-blog/01-setup.md) first.

➡️ Next: `ch14-seeders-factories.md`
