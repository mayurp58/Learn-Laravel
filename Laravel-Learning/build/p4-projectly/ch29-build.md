# P4 · Chapter 29 — Apply: Events + activity log

**Read first:** `phase-5-advanced/ch29-events.md`

## What you're building this chapter

An `activities` table + an event-driven activity log. Whenever a project or task is created/updated/deleted, an `Activity` row is recorded — who did it, when, what happened, what model.

## Step 1 — Migrations + models for projects + tasks + activities

```bash
php artisan make:model Project -m
php artisan make:model Task -m
php artisan make:model Activity -m
```

Projects migration:
```php
Schema::create('projects', function (Blueprint $table) {
    $table->id();
    $table->foreignId('team_id')->constrained()->cascadeOnDelete();
    $table->string('name');
    $table->text('description')->nullable();
    $table->string('status')->default('active');
    $table->timestamps();
    $table->index(['team_id', 'status']);
});
```

Tasks migration:
```php
Schema::create('tasks', function (Blueprint $table) {
    $table->id();
    $table->foreignId('project_id')->constrained()->cascadeOnDelete();
    $table->foreignId('assignee_id')->nullable()->constrained('users')->nullOnDelete();
    $table->string('title');
    $table->text('description')->nullable();
    $table->string('status')->default('todo');
    $table->timestamp('due_at')->nullable();
    $table->timestamps();
    $table->index(['project_id', 'status']);
});
```

Activities migration (polymorphic):
```php
Schema::create('activities', function (Blueprint $table) {
    $table->id();
    $table->foreignId('team_id')->constrained()->cascadeOnDelete();
    $table->foreignId('user_id')->constrained()->cascadeOnDelete();
    $table->string('description');
    $table->morphs('subject');   // subject_id + subject_type
    $table->json('properties')->nullable();
    $table->timestamps();
    $table->index(['team_id', 'created_at']);
});
```

```bash
php artisan migrate
```

## Step 2 — Models

`Project.php`:
```php
protected $fillable = ['team_id', 'name', 'description', 'status'];

public function team() { return $this->belongsTo(Team::class); }
public function tasks() { return $this->hasMany(Task::class); }
```

`Task.php`:
```php
protected $fillable = ['project_id', 'assignee_id', 'title', 'description', 'status', 'due_at'];
protected $casts = ['due_at' => 'datetime'];

public function project() { return $this->belongsTo(Project::class); }
public function assignee() { return $this->belongsTo(User::class, 'assignee_id'); }
```

`Activity.php`:
```php
protected $fillable = ['team_id', 'user_id', 'description', 'subject_id', 'subject_type', 'properties'];
protected $casts = ['properties' => 'array'];

public function subject() { return $this->morphTo(); }
public function user() { return $this->belongsTo(User::class); }
```

## Step 3 — A trait that auto-records activities

`app/Models/Concerns/RecordsActivity.php`:

```php
<?php

namespace App\Models\Concerns;

use App\Facades\CurrentTeam;
use App\Models\Activity;

trait RecordsActivity
{
    public static function bootRecordsActivity(): void
    {
        foreach (['created', 'updated', 'deleted'] as $event) {
            static::$event(function ($model) use ($event) {
                if (! auth()->check() || ! CurrentTeam::get()) {
                    return;
                }

                Activity::create([
                    'team_id'      => CurrentTeam::get()->id,
                    'user_id'      => auth()->id(),
                    'description'  => $event,
                    'subject_id'   => $model->getKey(),
                    'subject_type' => get_class($model),
                    'properties'   => $event === 'updated' ? $model->getDirty() : null,
                ]);
            });
        }
    }
}
```

> **What's happening here:** Eloquent fires `created`, `updated`, `deleted` events on every model save. We hook each one and write an activity row. This *is* the events system — Laravel calls these "model events," and they're built on top of the same dispatcher that powers user-defined events.

## Step 4 — Apply the trait

`Project.php` and `Task.php` — add at the top:
```php
use App\Models\Concerns\RecordsActivity;

class Project extends Model
{
    use HasFactory, RecordsActivity;
    // ...
}
```

## Step 5 — Try it

```bash
php artisan tinker
```
```php
$user = \App\Models\User::first();
auth()->login($user);
\App\Facades\CurrentTeam::set($user->teams()->first());

$project = \App\Models\Project::create([
    'team_id' => \App\Facades\CurrentTeam::get()->id,
    'name' => 'Launch website',
]);

$task = \App\Models\Task::create([
    'project_id' => $project->id,
    'title' => 'Write copy',
]);

$task->update(['status' => 'in_progress']);

\App\Models\Activity::all();
```

You should see 3 activity rows: project created, task created, task updated (with `properties => {"status": "in_progress"}` on the last one).

## Step 6 — A user-defined event (the alternative pattern)

Model events are convenient but coupled to the model. For "real" events (e.g. "team invitation accepted"), use a custom event class. We'll use one in `ch33-build.md` when we wire mail.

## Verify it works

- ✅ Creating a project writes an activity
- ✅ Updating a task writes an activity with `properties` as the diff
- ✅ Deleting writes an activity (verify with `$task->delete()`)
- ✅ Activity rows are scoped to the current team

## Commit

```bash
git add .
git commit -m "feat: activity log via Eloquent model events"
```

## What's next

➡️ `ch30-build.md` — queues: dispatch heavy work to background jobs.
