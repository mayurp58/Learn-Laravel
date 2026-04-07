# Chapter 2 — Modern PHP You Must Know Before Laravel

CodeIgniter 3 was written for PHP 5. A lot of devs with CI backgrounds write PHP that works but doesn't *use* the language as it exists today. Laravel assumes you write modern PHP. This chapter is the bridge.

## 1. Type Declarations

Old PHP:
```php
function add($a, $b) {
    return $a + $b;
}
```

Modern PHP:
```php
function add(int $a, int $b): int
{
    return $a + $b;
}
```

You can type:
- Parameters
- Return types (`: int`, `: string`, `: void`, `: self`, `: static`, `?User` for nullable)
- Properties (PHP 7.4+)

```php
class User
{
    public int $id;
    public string $name;
    public ?string $email = null;   // nullable
}
```

**Why it matters:** Laravel uses type hints everywhere. Without understanding them, controller methods will look mysterious.

## 2. Constructor Property Promotion (PHP 8.0+)

Old way:
```php
class User
{
    private string $name;
    private int $age;

    public function __construct(string $name, int $age)
    {
        $this->name = $name;
        $this->age = $age;
    }
}
```

Modern way:
```php
class User
{
    public function __construct(
        private string $name,
        private int $age,
    ) {}
}
```

Same thing. Less typing. You will see this constantly in Laravel code.

## 3. Readonly Properties (PHP 8.1+)

```php
class Money
{
    public function __construct(
        public readonly int $amount,
        public readonly string $currency,
    ) {}
}

$m = new Money(100, 'USD');
$m->amount = 200; // ERROR — readonly
```

Used heavily in DTOs (Data Transfer Objects) and value objects.

## 4. Enums (PHP 8.1+)

Replaces the old "constants on a class" pattern.

```php
enum OrderStatus: string
{
    case Pending = 'pending';
    case Paid = 'paid';
    case Shipped = 'shipped';
    case Cancelled = 'cancelled';

    public function label(): string
    {
        return match ($this) {
            self::Pending => 'Awaiting payment',
            self::Paid => 'Payment received',
            self::Shipped => 'On the way',
            self::Cancelled => 'Cancelled',
        };
    }
}

$status = OrderStatus::Paid;
echo $status->value;       // 'paid'
echo $status->label();     // 'Payment received'
```

Laravel has first-class enum support in models, validation, and routes.

## 5. The `match` Expression

Better `switch`. Returns a value, strict comparison, no fall-through.

```php
$role = 'admin';

$accessLevel = match ($role) {
    'admin' => 10,
    'editor' => 5,
    'viewer' => 1,
    default => 0,
};
```

## 6. Named Arguments

```php
function createUser(string $name, string $email, bool $admin = false, ?string $phone = null) { }

createUser(
    name: 'Asha',
    email: 'asha@example.com',
    admin: true,
);
```

Lets you skip optional params and makes calls self-documenting.

## 7. Arrow Functions

Short closures that auto-capture outer variables.

```php
$multiplier = 3;
$numbers = [1, 2, 3];

// Old:
$result = array_map(function ($n) use ($multiplier) {
    return $n * $multiplier;
}, $numbers);

// New:
$result = array_map(fn ($n) => $n * $multiplier, $numbers);
```

You'll see `fn ()` everywhere in Eloquent and collections.

## 8. Null Safe Operator `?->`

```php
// Old
$city = null;
if ($user !== null && $user->address !== null) {
    $city = $user->address->city;
}

// New
$city = $user?->address?->city;
```

Returns `null` instead of erroring if any part of the chain is null.

## 9. Spread Operator

```php
$first = [1, 2, 3];
$second = [4, 5, 6];
$combined = [...$first, ...$second]; // [1,2,3,4,5,6]

function sum(int ...$nums): int {
    return array_sum($nums);
}
sum(1, 2, 3, 4); // 10
```

## 10. First-class Callable Syntax (PHP 8.1+)

```php
$callable = strtoupper(...);   // creates a Closure from a function
$callable('hello');            // 'HELLO'

$callable = $user->getName(...);  // closure from a method
```

## Common Mistakes

1. **Mixing old and new style.** Pick the modern style and stick with it. Don't write `function ($x) use ($y)` when `fn ($x) => ...` works.
2. **Not using types.** "PHP is dynamic, I don't need them" — you do. Types catch bugs and make IDEs powerful.
3. **Treating enums as strings.** `$status === 'paid'` is wrong if `$status` is an enum. Use `$status === OrderStatus::Paid` or `$status->value === 'paid'`.

## Hands-on Task

Create `src/Order.php` in your sandbox:

```php
<?php
namespace Sandbox;

enum OrderStatus: string
{
    case Pending = 'pending';
    case Paid = 'paid';
    case Cancelled = 'cancelled';
}

class Order
{
    public function __construct(
        public readonly int $id,
        public readonly float $total,
        public OrderStatus $status = OrderStatus::Pending,
    ) {}

    public function pay(): void
    {
        $this->status = OrderStatus::Paid;
    }

    public function summary(): string
    {
        return match ($this->status) {
            OrderStatus::Pending => "Order #{$this->id} pending — \${$this->total}",
            OrderStatus::Paid => "Order #{$this->id} paid — \${$this->total}",
            OrderStatus::Cancelled => "Order #{$this->id} was cancelled",
        };
    }
}
```

Update `index.php`:

```php
<?php
require __DIR__ . '/vendor/autoload.php';

use Sandbox\Order;

$o = new Order(id: 1, total: 99.50);
echo $o->summary() . PHP_EOL;
$o->pay();
echo $o->summary() . PHP_EOL;
```

Run `php index.php`. You should see two lines, one pending, one paid.

**Bonus:** Try setting `$o->id = 5` after construction. PHP will throw — because `id` is `readonly`. Read the error.

## Self-check

1. What's the difference between `match` and `switch`?
2. What does `?->` do?
3. Why is constructor property promotion useful?
4. What's the difference between an enum and a class with constants?

➡️ Next: `ch03-oop-di.md`
