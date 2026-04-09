# Chapter 30 — Queues and Jobs

Queues let slow work happen in the background instead of making the user wait.

## Why

Sending an email takes 2 seconds. Generating a PDF takes 5. Calling an external API can take 30. None of that should happen during an HTTP request.

## Creating a job

```bash
php artisan make:job ProcessVideo
```

```php
class ProcessVideo implements ShouldQueue
{
    use Queueable;

    public function __construct(public Video $video) {}

    public function handle(): void
    {
        // ffmpeg, transcode, store, etc.
        $this->video->update(['status' => 'ready']);
    }
}
```

## Dispatching

```php
ProcessVideo::dispatch($video);
ProcessVideo::dispatch($video)->onQueue('videos');
ProcessVideo::dispatch($video)->delay(now()->addMinutes(10));
```

## Queue drivers

Configured in `.env`:
- `sync` — runs immediately (development)
- `database` — uses a `jobs` table
- `redis` — fast, requires Redis
- `sqs`, `beanstalkd` — production options

For database driver:
```bash
php artisan queue:table
php artisan migrate
```

## Running the worker

```bash
php artisan queue:work
php artisan queue:work --queue=high,default
```

In production, run via Supervisor. In dev, just open a second terminal.

## Failed jobs

```bash
php artisan queue:failed-table
php artisan migrate
php artisan queue:retry all
```

## Job options

```php
public int $tries = 3;
public int $timeout = 120;
public int $backoff = 60;   // seconds between retries
```

### Laravel 13: job attributes

In Laravel 13 you can express the same options as PHP attributes on the class — useful when you want the configuration to read declaratively:

```php
use Illuminate\Queue\Attributes\{Tries, Backoff, Timeout, FailOnTimeout};

#[Tries(3)]
#[Backoff(60)]
#[Timeout(120)]
#[FailOnTimeout]
class ProcessVideo implements ShouldQueue
{
    use Queueable;
    // ...
}
```

### Laravel 13: queue routing by class

Instead of calling `->onQueue('videos')` at every dispatch site, L13 lets you centralize routing rules in a service provider:

```php
use Illuminate\Support\Facades\Queue;

Queue::route(ProcessVideo::class, connection: 'redis', queue: 'videos');
Queue::route(SendInvoice::class,  connection: 'sqs',   queue: 'billing');
```

Now `ProcessVideo::dispatch($video)` automatically lands on the `videos` queue on the `redis` connection. This keeps dispatch sites clean and makes infrastructure routing a single source of truth.

### Laravel 13: `JobAttempted` event change

If you listen to the `JobAttempted` event, note that the `$exceptionOccurred` boolean property has been replaced with `$exception` (an object or `null`). Update any listener code accordingly:

```php
// Old (L12)
if ($event->exceptionOccurred) { ... }

// New (L13)
if ($event->exception !== null) { ... }
```

## Hands-on Task

1. Set `QUEUE_CONNECTION=database` in `.env`.
2. Create `SendWelcomeEmail` job. Dispatch it from your registration flow.
3. Run `php artisan queue:work` and watch it process.

🔨 **Build it for real:** Apply this chapter to project P4 — see [`build/p4-projectly/ch30-build.md`](../build/p4-projectly/ch30-build.md).

➡️ Next: `ch31-scheduler.md`
