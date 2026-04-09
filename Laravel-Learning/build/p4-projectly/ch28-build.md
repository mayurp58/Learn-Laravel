# P4 · Chapter 28 — Apply: Custom CurrentTeam facade + tenant scaffolding

**Read first:** `phase-5-advanced/ch28-facades.md`

## What you're building this chapter

The first piece of the multi-tenancy story: a `Team` model, a `CurrentTeam` facade that resolves to "the team the logged-in user is currently working in," and a global Eloquent scope that automatically filters queries by current team.

## Step 1 — Migrations + models

```bash
php artisan make:model Team -m
```

Migration:
```php
public function up(): void
{
    Schema::create('teams', function (Blueprint $table) {
        $table->id();
        $table->foreignId('owner_id')->constrained('users')->cascadeOnDelete();
        $table->string('name');
        $table->string('slug')->unique();
        $table->timestamps();
    });

    Schema::create('team_user', function (Blueprint $table) {
        $table->foreignId('team_id')->constrained()->cascadeOnDelete();
        $table->foreignId('user_id')->constrained()->cascadeOnDelete();
        $table->string('role')->default('member'); // owner, admin, member
        $table->primary(['team_id', 'user_id']);
    });
}
```

```bash
php artisan migrate
```

`app/Models/Team.php`:
```php
protected $fillable = ['owner_id', 'name', 'slug'];

public function members()
{
    return $this->belongsToMany(User::class)->withPivot('role')->withTimestamps();
}

public function owner()
{
    return $this->belongsTo(User::class, 'owner_id');
}
```

Add to `User`:
```php
public function teams()
{
    return $this->belongsToMany(Team::class)->withPivot('role')->withTimestamps();
}

public function ownedTeams()
{
    return $this->hasMany(Team::class, 'owner_id');
}
```

## Step 2 — TeamManager service

`app/Services/TeamManager.php`:

```php
<?php

namespace App\Services;

use App\Models\Team;

class TeamManager
{
    private ?Team $current = null;

    public function set(Team $team): void
    {
        $this->current = $team;
        session(['current_team_id' => $team->id]);
    }

    public function get(): ?Team
    {
        if ($this->current) {
            return $this->current;
        }

        if ($id = session('current_team_id')) {
            return $this->current = Team::find($id);
        }

        if ($user = auth()->user()) {
            return $this->current = $user->teams()->first();
        }

        return null;
    }

    public function clear(): void
    {
        $this->current = null;
        session()->forget('current_team_id');
    }
}
```

## Step 3 — Bind as singleton

In `AppServiceProvider::register()`:
```php
$this->app->singleton(\App\Services\TeamManager::class);
```

Singleton because we want the same instance for an entire request.

## Step 4 — Build the facade

`app/Facades/CurrentTeam.php`:

```php
<?php

namespace App\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @method static void set(\App\Models\Team $team)
 * @method static \App\Models\Team|null get()
 * @method static void clear()
 */
class CurrentTeam extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return \App\Services\TeamManager::class;
    }
}
```

> The `@method` PHPDoc isn't required but it gives IDE autocomplete on the facade calls.

## Step 5 — Use it

In tinker:
```php
$user = \App\Models\User::first();
auth()->login($user);
$team = \App\Models\Team::create(['owner_id' => $user->id, 'name' => 'My Team', 'slug' => 'my-team']);
$user->teams()->attach($team->id, ['role' => 'owner']);

\App\Facades\CurrentTeam::set($team);
\App\Facades\CurrentTeam::get();   // returns the Team
```

## Step 6 — Auto-create personal team on registration

Edit `app/Http/Controllers/Auth/RegisteredUserController.php` (Breeze created it). Add to the `store()` method, after `event(new Registered($user))`:

```php
$team = \App\Models\Team::create([
    'owner_id' => $user->id,
    'name'     => "{$user->name}'s team",
    'slug'     => \Str::slug($user->name).'-'.\Str::random(5),
]);

$user->teams()->attach($team->id, ['role' => 'owner']);
\App\Facades\CurrentTeam::set($team);
```

Now every new user automatically has a personal team and `CurrentTeam::get()` returns it.

## Verify it works

- ✅ Registering a fresh user creates a team and a `team_user` row
- ✅ `CurrentTeam::get()` returns the right team after login
- ✅ Setting a different team persists across requests (session-backed)

## Commit

```bash
git add .
git commit -m "feat: Team model + CurrentTeam facade + auto-create on register"
```

## What's next

➡️ `ch29-build.md` — events and listeners: activity log via the `creating`/`updating` model events.
