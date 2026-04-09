# P4 · Chapter 39 — Apply: Quality (Pint, PHPStan, coverage)

**Read first:** `phase-6-testing-deploy/ch39-quality.md`

## What you're building this chapter

Three small tooling installs that signal "this dev cares about code quality." Goes a long way in resume reads.

## Step 1 — Pint (formatter)

Pint is Laravel's official PHP-CS-Fixer wrapper. Zero config needed.

```bash
composer require laravel/pint --dev
./vendor/bin/pint
```

Run it once — it'll fix any whitespace / import-order issues across your codebase. Commit the result:

```bash
git add .
git commit -m "style: pint format pass"
```

Optional `pint.json` config in the repo root if you want stricter rules:
```json
{
  "preset": "laravel",
  "rules": {
    "no_unused_imports": true,
    "ordered_imports": { "sort_algorithm": "alpha" }
  }
}
```

## Step 2 — Larastan (static analysis)

```bash
composer require larastan/larastan --dev
```

`phpstan.neon` in repo root:
```yaml
includes:
    - vendor/larastan/larastan/extension.neon

parameters:
    paths:
        - app/
    level: 5
    ignoreErrors: []
```

Run it:
```bash
./vendor/bin/phpstan analyse
```

Fix the errors it reports — usually missing return types, unused variables, possibly-undefined variables. Level 5 is a sane starting point. Senior projects run level 8+.

## Step 3 — Code coverage

Pest can collect coverage via Xdebug or PCOV. Install PCOV (faster than Xdebug):

```bash
pecl install pcov
```

(If pecl isn't around: `brew install php-pcov` or follow Pest docs for your setup.)

Run with coverage:

```bash
./vendor/bin/pest --coverage --min=70
```

`--min=70` enforces 70% coverage. Anything less = failing exit code, perfect for CI.

You'll likely fail at first. Add tests until you pass. **Don't game the metric** — cover the things that matter (controllers, services, business logic), skip noise (config files, scaffolding).

## Step 4 — Composer scripts

`composer.json`:
```json
"scripts": {
    "test": "@php artisan test",
    "test:coverage": "./vendor/bin/pest --coverage --min=70",
    "lint": "./vendor/bin/pint",
    "lint:check": "./vendor/bin/pint --test",
    "stan": "./vendor/bin/phpstan analyse",
    "qa": ["@lint:check", "@stan", "@test:coverage"]
}
```

Now you can run the entire QA suite with:
```bash
composer qa
```

## Verify it works

- ✅ `composer lint` rewrites files to canonical format
- ✅ `composer stan` passes (or surfaces fixable issues)
- ✅ `composer test:coverage` runs and reports a percentage
- ✅ `composer qa` runs all three in sequence

## Commit

```bash
git add .
git commit -m "chore: add Pint, PHPStan/Larastan, and Pest coverage tooling"
```

## What's next

➡️ `ch40-build.md` — deployment to Forge with zero-downtime + GitHub Actions CI.
