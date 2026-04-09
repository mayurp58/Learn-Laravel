# P4 · Chapter 31 — Apply: Scheduler (daily digest)

**Read first:** `phase-5-advanced/ch31-scheduler.md`

## What you're building this chapter

A daily 8 AM scheduled job that emails each user a digest of yesterday's activity in their teams. Defined in `routes/console.php` (the Laravel 11+ way).

## Step 1 — Generate the command

```bash
php artisan make:command SendDailyDigests
```

`app/Console/Commands/SendDailyDigests.php`:

```php
<?php

namespace App\Console\Commands;

use App\Contracts\Notifier;
use App\Models\Activity;
use App\Models\User;
use Illuminate\Console\Command;

class SendDailyDigests extends Command
{
    protected $signature = 'projectly:send-digests';
    protected $description = 'Email each user a digest of yesterday\'s team activity';

    public function handle(Notifier $notifier): int
    {
        $yesterday = now()->subDay()->startOfDay();
        $today     = now()->startOfDay();

        User::with('teams')->chunk(100, function ($users) use ($notifier, $yesterday, $today) {
            foreach ($users as $user) {
                $teamIds = $user->teams->pluck('id');
                $count = Activity::whereIn('team_id', $teamIds)
                    ->whereBetween('created_at', [$yesterday, $today])
                    ->count();

                if ($count === 0) continue;

                $notifier->send(
                    $user,
                    'Your daily Projectly digest',
                    "{$count} things happened in your teams yesterday."
                );
            }
        });

        $this->info('Digests sent.');
        return self::SUCCESS;
    }
}
```

> Note `chunk(100, ...)` — we never load all users into memory at once. Important for any "for each user" command in production.

## Step 2 — Schedule it

`routes/console.php`:

```php
<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Schedule::command('projectly:send-digests')
    ->dailyAt('08:00')
    ->timezone('America/New_York')
    ->withoutOverlapping()
    ->onOneServer();
```

> `withoutOverlapping()` prevents two copies running if the previous one is still going. `onOneServer()` matters when you scale to multiple servers — only one runs the schedule.

## Step 3 — Run it manually first

```bash
php artisan projectly:send-digests
```

Should print "Digests sent." and (assuming you have activity from yesterday — you may need to seed some) write notifier log lines.

## Step 4 — See it in the schedule list

```bash
php artisan schedule:list
```

You should see your command with the "next due at" timestamp.

## Step 5 — Run the scheduler in dev

```bash
php artisan schedule:work
```

That runs the scheduler in foreground (every minute it checks what's due). In production you set a single cron entry: `* * * * * cd /path && php artisan schedule:run >> /dev/null 2>&1`.

## Verify it works

- ✅ The command runs without errors
- ✅ `schedule:list` shows it
- ✅ With `schedule:work` running, it would fire at 8:00 in the configured timezone
- ✅ `withoutOverlapping()` is set

## Commit

```bash
git add .
git commit -m "feat: daily digest scheduled command"
```

## What's next

➡️ `ch32-build.md` — Notifications: assigned-to-task notifications via Laravel's notification system.
