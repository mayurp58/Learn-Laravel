# Chapter 26 — The Service Container (Deep Dive)

This is the chapter that turns "Laravel feels like magic" into "Laravel is a thoughtful framework." Take it slowly.

## What is the Service Container?

It's a place where you tell Laravel: *"When someone asks for X, give them Y."*

X is usually an interface or class name. Y is a concrete instance. Laravel uses this container to:

- Inject dependencies into controller constructors
- Resolve typed parameters in route closures
- Hand out shared services (database, cache, mailer)

You used it in Chapter 3 conceptually. This chapter shows the API.

## Resolving things

```php
$service = app(PaymentService::class);
$service = resolve(PaymentService::class);
$service = app()->make(PaymentService::class);
```

All three do the same thing. Laravel news up the class and recursively injects its dependencies.

## Binding

In a service provider's `register()` method:

```php
$this->app->bind(PaymentGateway::class, StripeGateway::class);
```

Now anywhere you type-hint `PaymentGateway`, you get a `StripeGateway`.

## Singleton

```php
$this->app->singleton(PaymentGateway::class, function ($app) {
    return new StripeGateway(config('services.stripe.key'));
});
```

Same instance every resolve call (within the same request).

## Bind interface to implementation

The most common pattern:

```php
$this->app->bind(
    \App\Contracts\PaymentGateway::class,
    \App\Services\StripeGateway::class
);
```

Then in any controller / service:

```php
public function __construct(private PaymentGateway $gateway) {}
```

Laravel resolves the interface to the bound implementation. **This is dependency injection, automated.**

## Contextual binding

"When `OrderController` asks for `PaymentGateway`, give it `StripeGateway`. When `InternationalOrderController` asks, give it `RazorpayGateway`."

```php
$this->app->when(OrderController::class)
    ->needs(PaymentGateway::class)
    ->give(StripeGateway::class);

$this->app->when(InternationalOrderController::class)
    ->needs(PaymentGateway::class)
    ->give(RazorpayGateway::class);
```

## Tagging

```php
$this->app->bind('reports.csv', CsvReport::class);
$this->app->bind('reports.pdf', PdfReport::class);
$this->app->tag(['reports.csv', 'reports.pdf'], 'reports');

// Resolve all tagged
$reports = $this->app->tagged('reports');
```

## Hands-on Task

1. Create an interface `App\Contracts\Notifier` with a `send()` method.
2. Create two implementations: `LogNotifier` and `EmailNotifier`.
3. Bind the interface to `LogNotifier` in `AppServiceProvider`.
4. Inject the interface into a controller. Confirm it works.
5. Switch the binding to `EmailNotifier`. The controller code doesn't change.

➡️ Next: `ch27-service-providers.md`
