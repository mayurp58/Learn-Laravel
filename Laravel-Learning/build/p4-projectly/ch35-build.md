# P4 · Chapter 35 — Apply: Cache (dashboard stats + L13 Cache::touch)

**Read first:** `phase-5-advanced/ch35-cache.md`

## What you're building this chapter

A team dashboard that aggregates expensive stats (total tasks, completed tasks, active members, recent activity count). Cache the result for 10 minutes, with `Cache::touch()` to extend the TTL on every dashboard hit.

## Step 1 — DashboardStatsService

`app/Services/DashboardStatsService.php`:

```php
<?php

namespace App\Services;

use App\Facades\CurrentTeam;
use App\Models\Activity;
use App\Models\Project;
use App\Models\Task;
use Illuminate\Support\Facades\Cache;

class DashboardStatsService
{
    public function forCurrentTeam(): array
    {
        $team = CurrentTeam::get();
        $key  = "team:{$team->id}:dashboard-stats";

        // Try to extend TTL if cached, else compute
        if (Cache::has($key)) {
            Cache::touch($key, now()->addMinutes(10));
            return Cache::get($key);
        }

        $stats = [
            'projects'        => Project::where('team_id', $team->id)->count(),
            'projects_active' => Project::where('team_id', $team->id)->where('status', 'active')->count(),
            'tasks_total'     => Task::whereHas('project', fn($q) => $q->where('team_id', $team->id))->count(),
            'tasks_done'      => Task::whereHas('project', fn($q) => $q->where('team_id', $team->id))
                                     ->where('status', 'done')
                                     ->count(),
            'members'         => $team->members()->count(),
            'recent_activity' => Activity::where('team_id', $team->id)
                                         ->where('created_at', '>=', now()->subWeek())
                                         ->count(),
        ];

        Cache::put($key, $stats, now()->addMinutes(10));

        return $stats;
    }

    public function invalidate(int $teamId): void
    {
        Cache::forget("team:{$teamId}:dashboard-stats");
    }
}
```

> **What `Cache::touch()` buys you:** without it, the cache expires every 10 minutes regardless of activity. With it, an active team's dashboard cache stays warm forever (sliding window) — a team that hasn't touched the dashboard in 10 minutes gets a fresh recompute. This is exactly the pattern you'd use for session-like caches.

## Step 2 — Invalidate on relevant model events

In `Project.php` and `Task.php`, hook the `saved` and `deleted` events to bust the cache:

```php
protected static function booted(): void
{
    static::saved(function ($model) {
        $teamId = $model instanceof Project
            ? $model->team_id
            : $model->project?->team_id;

        if ($teamId) {
            app(\App\Services\DashboardStatsService::class)->invalidate($teamId);
        }
    });

    static::deleted(function ($model) {
        $teamId = $model instanceof Project
            ? $model->team_id
            : $model->project?->team_id;

        if ($teamId) {
            app(\App\Services\DashboardStatsService::class)->invalidate($teamId);
        }
    });
}
```

> **Trade-off:** invalidate-on-write is the simplest cache strategy. It's correct but can thrash if you have rapid updates. For dashboards that are read-heavy and write-light it's perfect.

## Step 3 — Use it in a controller

```php
public function dashboard(\App\Services\DashboardStatsService $stats)
{
    return view('dashboard', [
        'stats' => $stats->forCurrentTeam(),
    ]);
}
```

## Step 4 — A tiny dashboard view

`resources/views/dashboard.blade.php` (replace Breeze's default):

```blade
@extends('layouts.app')
@section('content')
    <h1 class="text-2xl font-bold mb-6">{{ \App\Facades\CurrentTeam::get()->name }} dashboard</h1>

    <div class="grid grid-cols-2 md:grid-cols-3 gap-4">
        @foreach (['Projects' => $stats['projects'], 'Active projects' => $stats['projects_active'], 'Tasks' => $stats['tasks_total'], 'Tasks done' => $stats['tasks_done'], 'Members' => $stats['members'], 'Recent activity' => $stats['recent_activity']] as $label => $value)
            <div class="bg-white border rounded p-4">
                <p class="text-xs text-gray-500">{{ $label }}</p>
                <p class="text-3xl font-semibold">{{ $value }}</p>
            </div>
        @endforeach
    </div>
@endsection
```

## Step 5 — Verify cache behaviour

In tinker:
```php
Cache::has("team:1:dashboard-stats");                     // false
app(\App\Services\DashboardStatsService::class)->forCurrentTeam();
Cache::has("team:1:dashboard-stats");                     // true
\App\Models\Project::create(['team_id' => 1, 'name' => 'Cache test']);
Cache::has("team:1:dashboard-stats");                     // false (invalidated)
```

## Verify it works

- ✅ First dashboard load runs the queries
- ✅ Subsequent loads (within 10 min) skip the queries
- ✅ `Cache::touch` extends the TTL on each hit (check `redis-cli TTL "..."`)
- ✅ Creating a project busts the cache

## Commit

```bash
git add .
git commit -m "feat: dashboard stats service with Cache::touch (L13)"
```

## What's next

➡️ `ch36-build.md` — Localization: ship Projectly in English + one more language.
