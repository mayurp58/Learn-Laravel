# Chapter 41 — Monitoring and Logging in Production

A short bonus chapter to round out Phase 6.

## Logs

Configured in `config/logging.php`. Default channel is `stack` → `single` → `storage/logs/laravel.log`.

```php
use Illuminate\Support\Facades\Log;

Log::info('Order placed', ['order_id' => $order->id]);
Log::warning('Low stock', ['sku' => $sku]);
Log::error('Payment failed', ['exception' => $e]);
```

For production, use the `daily` channel (rotates files) and pipe to a service like Papertrail or Logtail.

## Sentry / Bugsnag

Catch and report exceptions automatically.

```bash
composer require sentry/sentry-laravel
php artisan sentry:publish --dsn=...
```

Hire-yourself tip: every Laravel project you deploy should have error reporting wired up. Mention it on your resume.

## Telescope (development)

```bash
composer require laravel/telescope --dev
php artisan telescope:install
php artisan migrate
```

Visit `/telescope` — see every request, query, job, mail, log, etc. Magic for debugging.

**Don't run Telescope in production** unless behind auth + permissions.

## Horizon (queue monitoring)

For Redis-backed queues:
```bash
composer require laravel/horizon
php artisan horizon:install
```

Beautiful dashboard at `/horizon`.

## Hands-on Task

1. Install Telescope on your blog project.
2. Browse the dashboard and inspect a few requests.
