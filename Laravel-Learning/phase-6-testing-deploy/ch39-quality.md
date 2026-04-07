# Chapter 39 — Code Quality: Pint, Larastan, IDE Helpers

## Laravel Pint (formatting)

```bash
composer require laravel/pint --dev
./vendor/bin/pint
./vendor/bin/pint --test    # check without modifying
```

Configurable in `pint.json`. Run it before every commit. Most teams have a CI job that fails the build if formatting is off.

## Larastan / PHPStan (static analysis)

Catches type errors, undefined methods, etc., without running the code.

```bash
composer require --dev larastan/larastan
```

`phpstan.neon`:
```yaml
includes:
    - vendor/larastan/larastan/extension.neon

parameters:
    paths:
        - app/
    level: 6
```

```bash
./vendor/bin/phpstan analyse
```

Start at level 5, work up to 9 over time.

## IDE Helper

```bash
composer require --dev barryvdh/laravel-ide-helper
php artisan ide-helper:generate
php artisan ide-helper:models -W
```

Gives PhpStorm/VSCode autocompletion for facades and Eloquent model properties. Hugely productive.

## Pre-commit hooks

Use `husky` (via npm) or a simple `.git/hooks/pre-commit`:

```bash
#!/bin/sh
./vendor/bin/pint --test && ./vendor/bin/phpstan analyse && php artisan test
```

➡️ Next: `ch40-deployment.md`
