# Chapter 37 — Testing Basics with Pest

Laravel ships with PHPUnit. Pest is a more elegant wrapper on top — modern Laravel uses Pest by default in new installs. Both work; we'll use Pest.

> **Laravel 13 note:** New L13 installs ship with **Pest 4** and **PHPUnit 12**. Pest 4 introduces architectural testing improvements and faster parallel runs; the test syntax shown below is unchanged.

## Why test

- Catch regressions
- Refactor without fear
- Document behavior
- **Senior Laravel jobs require it.** Non-negotiable.

## Running tests

```bash
php artisan test
php artisan test --filter=PostTest
php artisan test --parallel
```

## Anatomy of a Pest test

`tests/Feature/PostTest.php`:

```php
<?php

use App\Models\User;
use App\Models\Post;

it('lists posts on the index page', function () {
    Post::factory()->count(3)->create();

    $response = $this->get('/posts');

    $response->assertStatus(200);
    $response->assertSee('All Posts');
});

it('requires authentication to create a post', function () {
    $response = $this->get('/posts/create');
    $response->assertRedirect('/login');
});

it('lets logged in users create posts', function () {
    $user = User::factory()->create();

    $response = $this->actingAs($user)->post('/posts', [
        'title' => 'Hello',
        'body' => 'World',
    ]);

    $response->assertRedirect();
    $this->assertDatabaseHas('posts', [
        'title' => 'Hello',
        'user_id' => $user->id,
    ]);
});
```

## Database refresh between tests

In `tests/TestCase.php` or each test file:
```php
uses(\Illuminate\Foundation\Testing\RefreshDatabase::class);
```

This wipes the test database before each test.

Use `.env.testing` with `DB_CONNECTION=sqlite` and `DB_DATABASE=:memory:` for fast tests.

## Useful assertions

```php
$response->assertStatus(200);
$response->assertOk();
$response->assertNotFound();
$response->assertForbidden();
$response->assertRedirect('/dashboard');
$response->assertSee('text');
$response->assertJson(['key' => 'value']);
$response->assertJsonStructure(['data' => ['id', 'title']]);

$this->assertDatabaseHas('posts', [...]);
$this->assertDatabaseMissing('posts', [...]);
$this->assertDatabaseCount('posts', 5);
```

## Hands-on Task

Write three feature tests for your blog: list, create (authed), delete (authed + own post only).

🔨 **Build it for real:** Apply this chapter to project P4 — see [`build/p4-projectly/ch37-build.md`](../build/p4-projectly/ch37-build.md).

➡️ Next: `ch38-feature-vs-unit.md`
