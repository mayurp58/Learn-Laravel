# P4 · Chapter 27 — Apply: Service providers

**Read first:** `phase-5-advanced/ch27-service-providers.md`

## What you're building this chapter

Move the `Notifier` binding out of `AppServiceProvider` into its own dedicated provider. Small change, big lesson: organized providers scale to dozens of bindings without becoming a 500-line `AppServiceProvider`.

## Step 1 — Generate the provider

```bash
php artisan make:provider NotificationServiceProvider
```

`app/Providers/NotificationServiceProvider.php`:

```php
<?php

namespace App\Providers;

use App\Contracts\Notifier;
use App\Services\Notifiers\EmailNotifier;
use App\Services\Notifiers\LogNotifier;
use Illuminate\Support\ServiceProvider;

class NotificationServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(Notifier::class, function ($app) {
            return $app->environment('local')
                ? new LogNotifier()
                : new EmailNotifier();
        });
    }

    public function provides(): array
    {
        return [Notifier::class];
    }
}
```

> The `provides()` method declares what this provider exposes. Combined with `protected $defer = true` (set on the class) you get a *deferred* provider that doesn't load until something resolves `Notifier::class`. For a notifier that's wanted on most requests it's not worth deferring, but knowing the option exists matters in interviews.

## Step 2 — Register it

In Laravel 11+, providers go in `bootstrap/providers.php`:

```php
<?php

return [
    App\Providers\AppServiceProvider::class,
    App\Providers\NotificationServiceProvider::class,
];
```

## Step 3 — Remove the binding from AppServiceProvider

Delete the `$this->app->bind(Notifier::class, ...)` block from `AppServiceProvider::register()`. The new dedicated provider handles it.

## Step 4 — Verify

```bash
php artisan tinker
```
```php
app(\App\Contracts\Notifier::class);    // still returns LogNotifier
```

Same behavior, cleaner organization.

## Step 5 — Optional: list registered providers

```bash
php artisan about
```

Look for your `NotificationServiceProvider` in the "Bootstrappers" section.

## Verify it works

- ✅ `app(Notifier::class)` still returns `LogNotifier`
- ✅ `AppServiceProvider` no longer mentions Notifier
- ✅ `bootstrap/providers.php` lists the new provider
- ✅ `php artisan about` confirms it loaded

## Commit

```bash
git add .
git commit -m "refactor: extract Notifier binding to NotificationServiceProvider"
```

## What's next

➡️ `ch28-build.md` — facades: build a `CurrentTeam` facade for tenant context.
