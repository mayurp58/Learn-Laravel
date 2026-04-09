# P4 · Chapter 26 — Apply: Service container + Notifier interface

**Read first:** `phase-5-advanced/ch26-service-container.md`

## What you're building this chapter

A `Notifier` contract with two implementations (`LogNotifier` for dev, `EmailNotifier` for later). You'll bind it in the container so anywhere you type-hint `Notifier`, you get the right implementation. This sets up the pattern we'll lean on for the rest of P4.

## Step 1 — Define the contract

`app/Contracts/Notifier.php`:

```php
<?php

namespace App\Contracts;

use App\Models\User;

interface Notifier
{
    public function send(User $user, string $subject, string $body): void;
}
```

## Step 2 — Two implementations

`app/Services/Notifiers/LogNotifier.php`:

```php
<?php

namespace App\Services\Notifiers;

use App\Contracts\Notifier;
use App\Models\User;
use Illuminate\Support\Facades\Log;

class LogNotifier implements Notifier
{
    public function send(User $user, string $subject, string $body): void
    {
        Log::info("[NOTIFY] to={$user->email} subject={$subject}", ['body' => $body]);
    }
}
```

`app/Services/Notifiers/EmailNotifier.php`:

```php
<?php

namespace App\Services\Notifiers;

use App\Contracts\Notifier;
use App\Models\User;
use Illuminate\Support\Facades\Mail;

class EmailNotifier implements Notifier
{
    public function send(User $user, string $subject, string $body): void
    {
        // We'll wire a real Mailable in ch33-build.md.
        // For now, the bare facade call demonstrates the pattern.
        Mail::raw($body, function ($m) use ($user, $subject) {
            $m->to($user->email)->subject($subject);
        });
    }
}
```

## Step 3 — Bind in AppServiceProvider

`app/Providers/AppServiceProvider.php`:

```php
public function register(): void
{
    $this->app->bind(\App\Contracts\Notifier::class, function ($app) {
        return $app->environment('local')
            ? new \App\Services\Notifiers\LogNotifier()
            : new \App\Services\Notifiers\EmailNotifier();
    });
}
```

> **Why a closure binding instead of a one-line `$this->app->bind(Notifier::class, LogNotifier::class)`?** Because we want different implementations in different environments. In dev, log-only (no real emails). In production, actual mail. The container call makes the decision.

## Step 4 — Use it from anywhere

In `tinker`:
```bash
php artisan tinker
```
```php
$user = \App\Models\User::first();
app(\App\Contracts\Notifier::class)->send($user, 'Test', 'Hello there.');
```

Then `tail -f storage/logs/laravel.log` — you should see `[NOTIFY] to=... subject=Test`.

## Step 5 — Use it via constructor injection

Create a placeholder service that uses it:

`app/Services/Welcome/SendWelcomeMessage.php`:

```php
<?php

namespace App\Services\Welcome;

use App\Contracts\Notifier;
use App\Models\User;

class SendWelcomeMessage
{
    public function __construct(private Notifier $notifier) {}

    public function __invoke(User $user): void
    {
        $this->notifier->send(
            $user,
            'Welcome to Projectly',
            "Hi {$user->name}, glad to have you."
        );
    }
}
```

In tinker:
```php
app(\App\Services\Welcome\SendWelcomeMessage::class)(\App\Models\User::first());
```

The container resolves `SendWelcomeMessage`, sees it needs a `Notifier`, looks up the binding, news up `LogNotifier`, injects it, and runs the call. **All automatic.**

## Verify it works

- ✅ `app(Notifier::class)` returns a `LogNotifier` in local env
- ✅ Calling `send()` writes a line to the log
- ✅ `SendWelcomeMessage` resolves and runs without you ever calling `new` on `LogNotifier`

## Commit

```bash
git add app/Contracts app/Services app/Providers/AppServiceProvider.php
git commit -m "feat: introduce Notifier contract with Log/Email implementations"
```

## What's next

➡️ `ch27-build.md` — extract the binding into a dedicated `NotificationServiceProvider`.
