# Chapter 40 — Deployment

## The options

- **Laravel Forge** ($12+/month) — provisions VPS (DigitalOcean, AWS), sets up Nginx, PHP, MySQL, queues, SSL. Easiest path. Industry standard.
- **Laravel Vapor** — serverless on AWS Lambda. Pricier, scales infinitely.
- **Docker + your own VPS** — most flexible, most work.
- **Render / Railway / Fly.io** — simpler PaaS options.

For learning, deploy a project to **Forge + DigitalOcean** at least once. Hiring managers ask "have you deployed Laravel to production?" — the answer should be yes.

## Production checklist

```bash
composer install --optimize-autoloader --no-dev
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan event:cache
php artisan migrate --force
php artisan storage:link
npm ci && npm run build
```

After deploy:
```bash
php artisan optimize:clear     # if you need to clear all caches
```

## Environment

- `APP_ENV=production`
- `APP_DEBUG=false` ← critical, never `true` in prod
- Real `APP_KEY` (`php artisan key:generate`)
- HTTPS only
- Strong DB password
- Mail driver pointing to a real provider (Mailgun, Postmark, SES)

## Queue workers in production

Run via Supervisor:

```ini
[program:laravel-worker]
process_name=%(program_name)s_%(process_num)02d
command=php /var/www/html/artisan queue:work redis --sleep=3 --tries=3 --max-time=3600
autostart=true
autorestart=true
numprocs=2
user=forge
```

## Zero-downtime deploys

Forge's "Quick Deploy" handles this. Manual: deploy to a new folder, atomic-symlink, restart php-fpm.

## Hands-on Task

You don't need to actually pay for a server today. But:
1. Read Forge's documentation top to bottom (forge.laravel.com).
2. Write a `deploy.sh` script for your blog project that runs the production checklist commands.

🔨 **Build it for real:** Apply this chapter to project P4 — see [`build/p4-projectly/ch40-build.md`](../build/p4-projectly/ch40-build.md). This is the chapter where Projectly first goes live in production.

➡️ **End of Phase 6.** Move to Phase 7 — still the same P4 project.
