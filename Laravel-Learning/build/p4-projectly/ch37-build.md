# P4 · Chapter 37 — Apply: Pest basics + first tests

**Read first:** `phase-6-testing-deploy/ch37-pest-basics.md`

## What you're building this chapter

Your first real Pest tests for Projectly. Three feature tests that prove the core flows work, plus the test database setup.

## Step 1 — Test database

`.env.testing`:
```env
APP_ENV=testing
APP_KEY=base64:...   # copy from .env, or run php artisan key:generate --env=testing
DB_CONNECTION=pgsql
DB_DATABASE=projectly_test
CACHE_STORE=array
QUEUE_CONNECTION=sync
SESSION_DRIVER=array
MAIL_MAILER=array
```

```bash
psql postgres -c "CREATE DATABASE projectly_test;"
psql projectly_test -c "CREATE EXTENSION IF NOT EXISTS vector;"
```

## Step 2 — Pest setup

`tests/Pest.php`:
```php
<?php

use Illuminate\Foundation\Testing\RefreshDatabase;

uses(Tests\TestCase::class, RefreshDatabase::class)->in('Feature');
uses(Tests\TestCase::class)->in('Unit');
```

`RefreshDatabase` migrates the test DB before each test and rolls back after.

## Step 3 — First feature test: registration creates a team

`tests/Feature/RegistrationTest.php`:

```php
<?php

use App\Models\Team;
use App\Models\User;

it('creates a personal team when a user registers', function () {
    $response = $this->post('/register', [
        'name' => 'Alice',
        'email' => 'alice@example.com',
        'password' => 'secret-password',
        'password_confirmation' => 'secret-password',
    ]);

    $response->assertRedirect('/dashboard');

    $user = User::where('email', 'alice@example.com')->first();
    expect($user)->not->toBeNull();
    expect($user->teams)->toHaveCount(1);
    expect($user->teams->first()->owner_id)->toBe($user->id);
});
```

## Step 4 — Run it

```bash
php artisan test
```

You should see green ticks. If not, the failure message will point at what broke.

## Step 5 — Tenant isolation test (the most impressive kind)

`tests/Feature/TenantIsolationTest.php`:

```php
<?php

use App\Facades\CurrentTeam;
use App\Models\Project;
use App\Models\Team;
use App\Models\User;

it('prevents users from seeing projects in teams they do not belong to', function () {
    // Alice and her team
    $alice = User::factory()->create();
    $aliceTeam = Team::create(['owner_id' => $alice->id, 'name' => 'Alice Team', 'slug' => 'alice']);
    $alice->teams()->attach($aliceTeam->id, ['role' => 'owner']);
    $aliceProject = Project::create(['team_id' => $aliceTeam->id, 'name' => 'Alice Secret Project']);

    // Bob and his team
    $bob = User::factory()->create();
    $bobTeam = Team::create(['owner_id' => $bob->id, 'name' => 'Bob Team', 'slug' => 'bob']);
    $bob->teams()->attach($bobTeam->id, ['role' => 'owner']);

    // Bob logs in and visits Alice's project
    $response = $this
        ->actingAs($bob)
        ->get("/projects/{$aliceProject->id}");

    $response->assertForbidden();   // or 404, depending on how you implement it
});
```

> This is the test interviewers love to see. "I wrote a tenant isolation test" is more credible than "I built multi-tenancy."

## Step 6 — A unit test for the stats service

`tests/Feature/DashboardStatsTest.php`:

```php
<?php

use App\Facades\CurrentTeam;
use App\Models\Project;
use App\Models\Task;
use App\Models\Team;
use App\Models\User;
use App\Services\DashboardStatsService;

it('aggregates stats correctly for the current team', function () {
    $user = User::factory()->create();
    $team = Team::create(['owner_id' => $user->id, 'name' => 'T', 'slug' => 't']);
    $user->teams()->attach($team->id, ['role' => 'owner']);

    $this->actingAs($user);
    CurrentTeam::set($team);

    Project::factory()->count(3)->create(['team_id' => $team->id]);

    $project = Project::first();
    Task::factory()->count(5)->create(['project_id' => $project->id]);
    Task::factory()->count(2)->create(['project_id' => $project->id, 'status' => 'done']);

    $stats = app(DashboardStatsService::class)->forCurrentTeam();

    expect($stats['projects'])->toBe(3);
    expect($stats['tasks_total'])->toBe(7);
    expect($stats['tasks_done'])->toBe(2);
});
```

You'll need factories for Project and Task — generate them if you haven't:
```bash
php artisan make:factory ProjectFactory
php artisan make:factory TaskFactory
```

(Quick definitions: `name => fake()->sentence(3)` for Project; `title => fake()->sentence(4), status => 'todo'` for Task.)

## Step 7 — Run all tests

```bash
php artisan test
```

You should see 3+ green tests.

## Verify it works

- ✅ `php artisan test` runs without errors
- ✅ Test database is separate from dev database
- ✅ All three tests pass
- ✅ Tests are repeatable (run them twice — same result)

## Commit

```bash
git add .
git commit -m "test: first Pest feature tests including tenant isolation"
```

## What's next

➡️ `ch38-build.md` — feature vs unit tests, when to use which.
