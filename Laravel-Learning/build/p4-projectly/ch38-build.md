# P4 · Chapter 38 — Apply: Feature vs unit tests

**Read first:** `phase-6-testing-deploy/ch38-feature-vs-unit.md`

## What you're building this chapter

Pure unit tests for things that don't need the database — service classes, helpers, value objects. We'll convert the `DashboardStatsService` test from ch37 (which is a feature test) into a *true* unit test using a stub.

Then you'll write unit tests for any pure logic you can identify in the codebase.

## Step 1 — Identify pure logic to unit test

Pure = no DB, no HTTP, no filesystem, no facades. Just inputs → outputs.

In Projectly so far, candidates are:
- `TeamInvitation::isExpired()` (logic on a single property)
- A future helper like `TaskFormatter::dueLabel($task)` ("Due tomorrow", "Overdue by 2 days")

Let's add the helper and test it.

## Step 2 — DueLabel helper

`app/Support/DueLabel.php`:

```php
<?php

namespace App\Support;

use Carbon\CarbonInterface;

class DueLabel
{
    public static function for(?CarbonInterface $dueAt, ?CarbonInterface $now = null): string
    {
        $now = $now ?? now();

        if (! $dueAt) return 'No due date';

        if ($dueAt->isToday()) return 'Due today';
        if ($dueAt->isYesterday()) return 'Overdue (yesterday)';
        if ($dueAt->isPast()) {
            $days = (int) $now->startOfDay()->diffInDays($dueAt->startOfDay());
            return "Overdue by {$days} days";
        }
        if ($dueAt->isTomorrow()) return 'Due tomorrow';

        $days = (int) $now->startOfDay()->diffInDays($dueAt->startOfDay());
        return "Due in {$days} days";
    }
}
```

> Note `?CarbonInterface $now = null` — that injectable "now" parameter is what makes this *testable*. Production passes nothing (default to actual now); tests pass a fixed reference time.

## Step 3 — Pure unit test

`tests/Unit/DueLabelTest.php`:

```php
<?php

use App\Support\DueLabel;
use Carbon\Carbon;

beforeEach(function () {
    $this->now = Carbon::create(2026, 4, 8, 12, 0, 0);
});

it('returns "no due date" when null', function () {
    expect(DueLabel::for(null, $this->now))->toBe('No due date');
});

it('says due today', function () {
    expect(DueLabel::for($this->now->copy()->setHour(18), $this->now))->toBe('Due today');
});

it('says due tomorrow', function () {
    expect(DueLabel::for($this->now->copy()->addDay(), $this->now))->toBe('Due tomorrow');
});

it('says overdue yesterday', function () {
    expect(DueLabel::for($this->now->copy()->subDay(), $this->now))->toBe('Overdue (yesterday)');
});

it('counts overdue days', function () {
    expect(DueLabel::for($this->now->copy()->subDays(5), $this->now))->toBe('Overdue by 5 days');
});

it('counts future days', function () {
    expect(DueLabel::for($this->now->copy()->addDays(7), $this->now))->toBe('Due in 7 days');
});
```

These tests don't touch the database or framework. They run in milliseconds. **This is what unit tests should look like.**

Make sure they're in `tests/Unit/`, not `tests/Feature/` — the `Pest.php` config we set up earlier doesn't apply `RefreshDatabase` to Unit tests, which is faster.

## Step 4 — Run only unit tests

```bash
./vendor/bin/pest tests/Unit
```

Should be near-instant. Compare with:

```bash
./vendor/bin/pest tests/Feature
```

The feature suite is slower because it migrates the database for each test.

## Step 5 — When to write which

| Use feature test for | Use unit test for |
|---|---|
| HTTP endpoints | Pure functions |
| Database queries | Value object methods |
| Multi-step business flows | Formatters / serializers |
| Authorization | Algorithms |
| Mail/notifications fired | State machines |

If you're not sure: start with a feature test (it's harder to fool yourself with a feature test) and extract pure logic into a class that gets unit tests as it stabilizes.

## Verify it works

- ✅ `pest tests/Unit` runs in < 1 second
- ✅ All 6 DueLabel tests pass
- ✅ The helper handles every case (no due date, overdue, today, tomorrow, past, future)

## Commit

```bash
git add .
git commit -m "test: DueLabel helper with pure unit tests"
```

## What's next

➡️ `ch39-build.md` — quality tools (Pint, PHPStan, code coverage).
