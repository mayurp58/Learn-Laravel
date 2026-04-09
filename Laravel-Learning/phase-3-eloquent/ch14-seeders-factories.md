# Chapter 14 — Seeders and Factories

Seeders fill your database with data. Factories generate fake data using Faker. Together they're how you bootstrap a development database in seconds.

## Factories

```bash
php artisan make:factory PostFactory --model=Post
```

`database/factories/PostFactory.php`:

```php
class PostFactory extends Factory
{
    public function definition(): array
    {
        return [
            'title' => fake()->sentence(),
            'body'  => fake()->paragraphs(3, true),
            'user_id' => User::factory(),
        ];
    }

    // States
    public function published(): static
    {
        return $this->state(['published_at' => now()]);
    }
}
```

## Using factories

In tinker or seeders:

```php
Post::factory()->create();                       // 1 post
Post::factory()->count(50)->create();            // 50 posts
Post::factory()->published()->count(10)->create();
Post::factory()->for(User::factory())->count(5)->create();
Post::factory()->has(Comment::factory()->count(3))->create();
```

## Seeders

```bash
php artisan make:seeder PostSeeder
```

```php
class PostSeeder extends Seeder
{
    public function run(): void
    {
        Post::factory()->count(50)->create();
    }
}
```

`database/seeders/DatabaseSeeder.php` is the entry point — register others here:

```php
public function run(): void
{
    User::factory()->create([
        'name' => 'Admin',
        'email' => 'admin@example.com',
    ]);

    $this->call([
        CategorySeeder::class,
        PostSeeder::class,
    ]);
}
```

Run:
```bash
php artisan db:seed
php artisan migrate:fresh --seed       # rebuild + seed
```

## Hands-on Task

1. Create a `PostFactory` that generates a title, body, and links to a `User`.
2. Create a `CategoryFactory` that generates a name and slug.
3. In `DatabaseSeeder`, create 5 categories, 10 users, and 50 posts.
4. Run `migrate:fresh --seed` and confirm in phpMyAdmin.

🔨 **Build it for real:** Apply this chapter to project P2 — see [`build/p2-blog/ch14-build.md`](../build/p2-blog/ch14-build.md).

➡️ Next: `ch15-eloquent-basics.md`
