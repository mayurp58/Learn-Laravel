# Chapter 31 — Task Scheduling

Instead of editing crontab for each task, you write all your scheduled tasks in PHP and have ONE cron entry.

## The setup (one time)

Add to your server's crontab:
```
* * * * * cd /path-to-project && php artisan schedule:run >> /dev/null 2>&1
```

That's it. Laravel takes over.

## Defining tasks (Laravel 11+)

In `routes/console.php`:

```php
use Illuminate\Support\Facades\Schedule;

Schedule::command('inspire')->hourly();
Schedule::command('emails:digest')->daily();
Schedule::call(fn() => Cache::flush())->everySixHours();
Schedule::job(new ProcessAnalytics)->dailyAt('02:00');
```

## Frequencies

```
->everyMinute()
->everyFiveMinutes()
->hourly()
->dailyAt('13:00')
->twiceDaily(1, 13)
->weekly()
->weeklyOn(1, '8:00')   // Monday at 8
->monthlyOn(1, '0:00')
->cron('* * * * *')     // raw cron
```

## Constraints

```php
Schedule::command('emails:digest')
    ->dailyAt('07:00')
    ->weekdays()
    ->timezone('Asia/Kolkata')
    ->withoutOverlapping()
    ->onOneServer();      // when running multi-server
```

## Hands-on Task

1. Create an artisan command `posts:cleanup` that deletes posts older than 1 year (where `deleted_at` is set).
2. Schedule it to run daily at 3 AM.
3. Test by running `php artisan schedule:test`.

➡️ Next: `ch32-notifications.md`
