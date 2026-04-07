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

## Hands-on Task

1. Set `QUEUE_CONNECTION=database` in `.env`.
2. Create `SendWelcomeEmail` job. Dispatch it from your registration flow.
3. Run `php artisan queue:work` and watch it process.

➡️ Next: `ch31-scheduler.md`
