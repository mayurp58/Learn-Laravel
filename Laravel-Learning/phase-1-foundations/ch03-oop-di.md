# Chapter 3 — OOP and Dependency Injection (the concept)

This chapter is the single most important one in Phase 1. If you understand dependency injection, Laravel's "Service Container" will make sense in Phase 5. If you don't, it will feel like magic forever.

## 1. Interfaces

An interface is a *contract*. It says "any class implementing me must have these methods" — but it doesn't provide the code.

```php
interface PaymentGateway
{
    public function charge(int $amount, string $currency): bool;
}

class StripeGateway implements PaymentGateway
{
    public function charge(int $amount, string $currency): bool
    {
        // call Stripe API
        return true;
    }
}

class RazorpayGateway implements PaymentGateway
{
    public function charge(int $amount, string $currency): bool
    {
        // call Razorpay API
        return true;
    }
}
```

Why? Because now any code that needs "a payment gateway" can accept the interface, not a specific class. Tomorrow you swap Stripe for Razorpay — your business code doesn't change.

## 2. Abstract Classes

Like an interface, but can have partial implementation.

```php
abstract class Notification
{
    abstract public function send(string $to, string $message): void;

    protected function log(string $msg): void
    {
        file_put_contents('/tmp/log.txt', $msg . PHP_EOL, FILE_APPEND);
    }
}

class EmailNotification extends Notification
{
    public function send(string $to, string $message): void
    {
        // send email
        $this->log("Email sent to {$to}");
    }
}
```

Use abstract classes when you have shared behavior. Use interfaces when you only have a contract.

## 3. Traits

Reusable chunks of methods you can paste into multiple classes.

```php
trait HasTimestamps
{
    public ?string $createdAt = null;
    public ?string $updatedAt = null;

    public function touch(): void
    {
        $this->updatedAt = date('Y-m-d H:i:s');
    }
}

class Post
{
    use HasTimestamps;
}

$p = new Post();
$p->touch();
```

Eloquent uses traits heavily (`SoftDeletes`, `HasFactory`, etc.).

## 4. Dependency Injection — the core idea

**Bad** code (tight coupling):

```php
class OrderService
{
    public function checkout(int $amount): void
    {
        $gateway = new StripeGateway();   // ← hard-coded dependency
        $gateway->charge($amount, 'USD');
    }
}
```

Problems:
- You can't test `OrderService` without actually hitting Stripe.
- You can't switch gateways without editing this file.
- You're violating the **Dependency Inversion Principle**.

**Good** code (dependency injection):

```php
class OrderService
{
    public function __construct(
        private PaymentGateway $gateway,   // injected
    ) {}

    public function checkout(int $amount): void
    {
        $this->gateway->charge($amount, 'USD');
    }
}

// Wiring (this is what the Service Container does for you in Laravel)
$service = new OrderService(new StripeGateway());
$service->checkout(100);
```

Now:
- You can pass a fake gateway in tests.
- You can swap implementations without touching `OrderService`.
- The class is reusable.

**This is what "Laravel resolves dependencies automatically" means.** When you type-hint `PaymentGateway` in a controller constructor, Laravel's Service Container looks at its bindings, finds that `PaymentGateway` is bound to `StripeGateway`, news up a `StripeGateway`, and passes it to your controller. You never write `new` for services.

### CodeIgniter comparison

In CI3 you wrote `$this->load->library('payment')` and you got a singleton. There was no concept of "I want any payment gateway, you decide which." Laravel's container is built around exactly that idea.

## 5. SOLID — the short version

You don't need to memorize SOLID, but at least know the names. Interviewers ask.

- **S** — Single Responsibility: a class should have one reason to change.
- **O** — Open/Closed: open for extension, closed for modification.
- **L** — Liskov Substitution: subclasses must be usable wherever parents are used.
- **I** — Interface Segregation: many small interfaces > one fat one.
- **D** — Dependency Inversion: depend on abstractions, not concretions. ← The one DI is built on.

## Common Mistakes

1. **Newing up dependencies inside methods.** Anytime you write `new SomeService()` inside a class, ask: "should this be injected?"
2. **Using static facades for everything in Laravel** without understanding what they hide. (More in Chapter 28.)
3. **Confusing "interface" with "abstract class."** Abstract classes can have state and code; interfaces cannot (well, they can have constants, but that's it).

## Hands-on Task

In your sandbox, create:

`src/Notifier.php`
```php
<?php
namespace Sandbox;

interface Notifier
{
    public function send(string $to, string $message): void;
}
```

`src/EmailNotifier.php`
```php
<?php
namespace Sandbox;

class EmailNotifier implements Notifier
{
    public function send(string $to, string $message): void
    {
        echo "[EMAIL → {$to}] {$message}" . PHP_EOL;
    }
}
```

`src/SmsNotifier.php`
```php
<?php
namespace Sandbox;

class SmsNotifier implements Notifier
{
    public function send(string $to, string $message): void
    {
        echo "[SMS → {$to}] {$message}" . PHP_EOL;
    }
}
```

`src/UserRegistration.php`
```php
<?php
namespace Sandbox;

class UserRegistration
{
    public function __construct(private Notifier $notifier) {}

    public function register(string $name, string $contact): void
    {
        // ... save user logic
        $this->notifier->send($contact, "Welcome, {$name}!");
    }
}
```

`index.php`
```php
<?php
require __DIR__ . '/vendor/autoload.php';

use Sandbox\{UserRegistration, EmailNotifier, SmsNotifier};

$reg = new UserRegistration(new EmailNotifier());
$reg->register('Asha', 'asha@example.com');

$reg2 = new UserRegistration(new SmsNotifier());
$reg2->register('Ravi', '+911234567890');
```

Run it. Notice that `UserRegistration` has zero knowledge of how the message is sent. That's the whole point.

**Bonus task:** Add a `LogNotifier` that writes to a file. Pass it to `UserRegistration`. You should not need to change `UserRegistration` at all.

## Self-check

1. In your own words, what is dependency injection and why does it exist?
2. What's the difference between an interface and an abstract class?
3. Why is `new StripeGateway()` inside a service class a bad smell?
4. What does the "D" in SOLID stand for, and how does DI relate to it?

➡️ Next: `ch04-laravel-setup.md` — finally, install Laravel.
