# Chapter 27 — Service Providers

Service providers are the central place where Laravel boots itself. Every package, every framework feature, registers itself in a provider.

## The two methods

```php
class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        // BIND things into the container.
        // Do NOT use other services here — they may not be loaded yet.
    }

    public function boot(): void
    {
        // Run code AFTER all providers have been registered.
        // Safe to use any service.
    }
}
```

## What goes in `register()`

- Container bindings
- Singletons
- Aliases

## What goes in `boot()`

- View composers
- Macros
- Event listeners
- Observers
- Validation rules
- Blade directives
- Anything that needs the framework to be ready

## Creating a custom provider

```bash
php artisan make:provider PaymentServiceProvider
```

Register it in `bootstrap/providers.php`:

```php
return [
    App\Providers\AppServiceProvider::class,
    App\Providers\PaymentServiceProvider::class,
];
```

## Real example

```php
class PaymentServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(PaymentGateway::class, function () {
            return new StripeGateway(
                config('services.stripe.key'),
                config('services.stripe.secret'),
            );
        });
    }

    public function boot(): void
    {
        // nothing for now
    }
}
```

## Common Mistakes

1. **Resolving services in `register()`** — those services may not be bound yet.
2. **Putting too much logic in `AppServiceProvider`** — split into focused providers.

🔨 **Build it for real:** Apply this chapter to project P4 — see [`build/p4-projectly/ch27-build.md`](../build/p4-projectly/ch27-build.md).

➡️ Next: `ch28-facades.md`
