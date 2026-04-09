# Chapter 38 — Feature vs Unit Tests, HTTP Tests, Mocking

## Feature vs Unit

- **Unit test**: tests one small piece in isolation. No DB, no HTTP. Pure logic.
- **Feature test**: tests a whole slice — HTTP request → controller → DB → response. This is where Laravel shines.

In practice, **feature tests are 80% of what you write in Laravel**. Unit tests are reserved for value objects, services with pure logic, calculations.

## Unit test example

`tests/Unit/PriceCalculatorTest.php`:

```php
use App\Services\PriceCalculator;

it('applies discount correctly', function () {
    $calc = new PriceCalculator();
    expect($calc->withDiscount(100, 10))->toBe(90.0);
});
```

## HTTP feature test (continued)

```php
it('returns paginated json from the api', function () {
    Post::factory()->count(20)->create();

    $response = $this->getJson('/api/posts');

    $response->assertOk()
        ->assertJsonStructure([
            'data' => [['id', 'title']],
            'meta' => ['total', 'per_page'],
        ]);
});
```

## Acting as a user

```php
$user = User::factory()->create();
$this->actingAs($user)->get('/dashboard')->assertOk();
$this->actingAs($user, 'sanctum')->getJson('/api/user');
```

## Mocking external services

```php
use Illuminate\Support\Facades\Http;

Http::fake([
    'api.example.com/*' => Http::response(['ok' => true], 200),
]);

// Code that calls Http::get('https://api.example.com/...')

Http::assertSent(fn ($request) => $request->url() === 'https://api.example.com/x');
```

For mailing:
```php
Mail::fake();
// ... action that should send mail
Mail::assertSent(WelcomeMail::class);
```

For queues:
```php
Queue::fake();
ProcessVideo::dispatch($video);
Queue::assertPushed(ProcessVideo::class);
```

For events:
```php
Event::fake();
event(new UserRegistered($user));
Event::assertDispatched(UserRegistered::class);
```

These fakes are the most useful testing feature in Laravel.

## Hands-on Task

Write a test that:
1. Fakes the queue
2. Hits `POST /api/posts`
3. Asserts the post was created and a `NotifyFollowers` job was dispatched

🔨 **Build it for real:** Apply this chapter to project P4 — see [`build/p4-projectly/ch38-build.md`](../build/p4-projectly/ch38-build.md).

➡️ Next: `ch39-quality.md`
