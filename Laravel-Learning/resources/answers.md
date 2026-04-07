# Self-check Answers (Chapter 1)

1. **What does Composer actually do?** PHP's dependency manager. Downloads packages into `vendor/`, generates an autoloader.
2. **What does PSR-4 mean in plain English?** A standard that says "the namespace of a class maps directly to the folder/file where it lives."
3. **Why doesn't Laravel need `require_once` everywhere?** Composer's autoloader uses PSR-4 to find classes automatically when they're first used.
4. **`app/Services/Billing/Invoice.php`** → `namespace App\Services\Billing;` and `class Invoice`.
5. **`composer install` vs `composer update`?** `install` respects `composer.lock` (deterministic, used in production). `update` ignores the lock and pulls the latest versions allowed by `composer.json` (used during development to bump packages).

(For other chapters, the answers are in the chapter text itself — re-read the section.)
