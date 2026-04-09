# Chapter 29 — Events and Listeners

Events let you decouple "something happened" from "what to do about it."

## Example

When a user registers, you might want to:
- Send a welcome email
- Create a Stripe customer
- Log it to analytics
- Notify Slack

You could do all of this in `RegisterController@store`. But that controller now has 4 reasons to change. Instead:

```php
event(new UserRegistered($user));
```

And four separate listeners each handle one concern.

## Creating events and listeners

```bash
php artisan make:event UserRegistered
php artisan make:listener SendWelcomeEmail --event=UserRegistered
```

`UserRegistered.php`:
```php
class UserRegistered
{
    public function __construct(public User $user) {}
}
```

`SendWelcomeEmail.php`:
```php
class SendWelcomeEmail
{
    public function handle(UserRegistered $event): void
    {
        Mail::to($event->user)->send(new WelcomeMail($event->user));
    }
}
```

## Wiring (Laravel 11+ / 13)

Auto-discovery happens when listener methods type-hint the event. You can also register manually in `EventServiceProvider`:

```php
protected $listen = [
    UserRegistered::class => [
        SendWelcomeEmail::class,
        CreateStripeCustomer::class,
    ],
];
```

## Async listeners (queued)

Implement `ShouldQueue`:

```php
class SendWelcomeEmail implements ShouldQueue
{
    public function handle(UserRegistered $event): void { ... }
}
```

Now this listener runs in a background worker instead of blocking the request.

## Hands-on Task

Create a `PostPublished` event. Add two listeners: one that logs to a file, one that pretends to send a notification. Fire the event in `PostController@update` when `published_at` changes from null to a date.

🔨 **Build it for real:** Apply this chapter to project P4 — see [`build/p4-projectly/ch29-build.md`](../build/p4-projectly/ch29-build.md).

➡️ Next: `ch30-queues.md`
