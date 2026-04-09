# P4 · Chapter 30 — Apply: Queues + jobs

**Read first:** `phase-5-advanced/ch30-queues.md`

## What you're building this chapter

A `GenerateProjectReport` job that pretends to do slow work (counting tasks, summarizing). Dispatched from a button click. You'll watch it run in `queue:work` instead of blocking the request.

## Step 1 — Generate the job

```bash
php artisan make:job GenerateProjectReport
```

`app/Jobs/GenerateProjectReport.php`:

```php
<?php

namespace App\Jobs;

use App\Models\Project;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\Attributes\{Backoff, Tries, Timeout};
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Storage;

#[Tries(3)]
#[Backoff(60)]
#[Timeout(120)]
class GenerateProjectReport implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(public Project $project) {}

    public function handle(): void
    {
        // Pretend this is expensive
        sleep(3);

        $report = [
            'project'        => $this->project->name,
            'total_tasks'    => $this->project->tasks()->count(),
            'tasks_done'     => $this->project->tasks()->where('status', 'done')->count(),
            'generated_at'   => now()->toIso8601String(),
        ];

        Storage::disk('local')->put(
            "reports/project-{$this->project->id}.json",
            json_encode($report, JSON_PRETTY_PRINT)
        );
    }
}
```

> The `#[Tries]`, `#[Backoff]`, `#[Timeout]` attributes are L13's declarative way of configuring job retry behaviour. Same effect as the `public int $tries = 3;` properties, but reads as configuration not state.

## Step 2 — Centralized queue routing (L13)

In `AppServiceProvider::boot()`:

```php
use Illuminate\Support\Facades\Queue;
use App\Jobs\GenerateProjectReport;

public function boot(): void
{
    Queue::route(GenerateProjectReport::class, connection: 'redis', queue: 'reports');
}
```

Now `GenerateProjectReport::dispatch($project)` automatically lands on the `reports` queue without you typing `->onQueue('reports')` at every dispatch site. **L13 win.**

## Step 3 — Dispatch from a controller

```bash
php artisan make:controller ProjectReportController
```

```php
<?php

namespace App\Http\Controllers;

use App\Jobs\GenerateProjectReport;
use App\Models\Project;

class ProjectReportController extends Controller
{
    public function store(Project $project)
    {
        GenerateProjectReport::dispatch($project);

        return back()->with('status', 'Report queued. It will be ready in a moment.');
    }
}
```

Route:
```php
Route::middleware('auth')->post('/projects/{project}/report', [ProjectReportController::class, 'store'])->name('projects.report');
```

## Step 4 — Set up the queue worker

In a new terminal:
```bash
php artisan queue:work --queue=reports,default
```

Leave it running. It listens for new jobs.

## Step 5 — Try it

In another terminal:
```bash
php artisan tinker
```
```php
$project = \App\Models\Project::first();
\App\Jobs\GenerateProjectReport::dispatch($project);
```

Watch the worker terminal — within 3 seconds you should see:

```
[2026-04-08 ...] App\Jobs\GenerateProjectReport ............... DONE
```

Then:
```bash
cat storage/app/reports/project-1.json
```

The report file exists.

## Step 6 — Force a failure

Edit the job's `handle()` to throw an exception:
```php
throw new \Exception('Boom');
```

Dispatch again. Watch the worker — it'll retry 3 times (with 60s backoff between each), then mark the job failed. Check failed jobs:

```bash
php artisan queue:failed
```

Restore the original `handle()`. Retry the failed job:
```bash
php artisan queue:retry all
```

## Verify it works

- ✅ Dispatching the job returns the controller redirect immediately (the request doesn't wait 3 seconds)
- ✅ The worker picks it up and writes the report file
- ✅ Failed jobs go to `failed_jobs` table after 3 attempts
- ✅ `queue:retry` re-runs them

## Commit

```bash
git add .
git commit -m "feat: GenerateProjectReport job with queue routing"
```

## What's next

➡️ `ch31-build.md` — scheduler: a daily digest that runs without a cron-by-hand.
