# Chapter 1 — Composer, Autoloading, and PSR-4

## Why this is Chapter 1

In CodeIgniter, when you needed a class, you wrote `$this->load->library('cart')` or `$this->load->model('user_model')`. CI had its own custom loader, and you mostly didn't think about *how* PHP found those files.

In Laravel — and in **every modern PHP framework** — there is no custom loader. There is only **Composer** and an industry standard called **PSR-4 autoloading**. Once you understand this, half of "Laravel feels weird" disappears.

## What is Composer?

Composer is PHP's package manager. Think `npm` for Node, `pip` for Python, `gem` for Ruby. It does two things:

1. **Installs third-party libraries** (and their dependencies) into a `vendor/` folder.
2. **Generates an autoloader** so PHP can find any class without you writing `require` or `include`.

Every Laravel project has a `composer.json` file (the project manifest) and a `composer.lock` file (the exact versions installed). Both go into git.

### Key commands

```bash
composer --version                    # check installation
composer create-project laravel/laravel myapp   # create a Laravel project
composer install                      # install everything in composer.lock
composer update                       # update to latest allowed versions
composer require vendor/package       # install a new package
composer dump-autoload                # rebuild the autoloader (rarely needed)
```

### CodeIgniter comparison

| CodeIgniter 3 | Modern PHP / Laravel |
|---|---|
| `$this->load->library('email')` | `use Illuminate\Support\Facades\Mail;` then `Mail::send(...)` |
| Manually copy library to `application/libraries/` | `composer require some/package` |
| No `vendor/` folder | Everything lives in `vendor/` |

## What is Autoloading?

Without autoloading, you would have to write:

```php
require_once 'app/Models/User.php';
require_once 'app/Services/PaymentService.php';
require_once 'app/Http/Controllers/UserController.php';
// ... 500 more lines
```

That's miserable. Autoloading lets PHP say: *"I need the class `App\Models\User`. Where is it? Composer, find it."*

## What is PSR-4?

PSR-4 is a community standard that says:

> "The fully-qualified class name maps directly to a file path."

Example:

- Class: `App\Models\User`
- File: `app/Models/User.php`

That's it. The namespace is the folder structure. The class name is the file name.

In `composer.json` you'll see:

```json
"autoload": {
    "psr-4": {
        "App\\": "app/",
        "Database\\Factories\\": "database/factories/",
        "Database\\Seeders\\": "database/seeders/"
    }
}
```

This says: "any class starting with `App\` lives under the `app/` folder." Composer does the rest.

## What is a Namespace?

A namespace is a "last name" for your class. It prevents collisions. If two libraries both have a class called `User`, namespaces let them coexist:

```php
namespace App\Models;

class User { }
```

```php
namespace Acme\Billing;

class User { }   // totally different class, no conflict
```

To use a namespaced class, you `use` it:

```php
use App\Models\User;

$user = new User();
```

Or you reference it fully-qualified:

```php
$user = new \App\Models\User();
```

### Common Mistakes

1. **Wrong namespace at the top of a file.** If you create `app/Services/PaymentService.php`, the file MUST start with `namespace App\Services;`. Wrong namespace = "Class not found" errors.
2. **Filename doesn't match class name.** `class PaymentService` must live in `PaymentService.php`. Case-sensitive on Linux servers, case-insensitive on macOS — this is a classic "works on my Mac, breaks in production" bug.
3. **Forgetting to run `composer dump-autoload`** after manually creating new namespaces in `composer.json`. (You don't need this when you create normal classes — Composer handles it via PSR-4.)

## Hands-on Task

You won't install Laravel until Chapter 4. For now:

1. Open Terminal. Run `composer --version`. If it's missing, install Composer: https://getcomposer.org/download/
2. Create a sandbox folder anywhere: `mkdir ~/php-sandbox && cd ~/php-sandbox`
3. Run `composer init` and accept defaults. Look at the `composer.json` it created.
4. Manually add this to `composer.json`:

   ```json
   "autoload": {
       "psr-4": {
           "Sandbox\\": "src/"
       }
   }
   ```

5. Run `composer dump-autoload`.
6. Create `src/Greeter.php`:

   ```php
   <?php
   namespace Sandbox;

   class Greeter
   {
       public function hello(string $name): string
       {
           return "Hello, {$name}!";
       }
   }
   ```

7. Create `index.php` at the root:

   ```php
   <?php
   require __DIR__ . '/vendor/autoload.php';

   use Sandbox\Greeter;

   $g = new Greeter();
   echo $g->hello('Laravel');
   ```

8. Run `php index.php`. You should see `Hello, Laravel!`.

If that worked, you have just done — by hand — what Laravel does for you automatically. Every Laravel class works exactly the same way.

## Self-check Questions

1. What does Composer actually do?
2. What does PSR-4 mean in plain English?
3. Why doesn't Laravel need `require_once` everywhere?
4. If a file is at `app/Services/Billing/Invoice.php`, what should its namespace and class name be?
5. What's the difference between `composer install` and `composer update`?

(Answers in `resources/answers.md`.)

➡️ Next: `ch02-modern-php.md`
