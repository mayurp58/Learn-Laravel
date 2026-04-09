# P4 · Chapter 40 — Apply: Deployment + CI

**Read first:** `phase-6-testing-deploy/ch40-deployment.md`

## What you're building this chapter

A real production deployment of Projectly + a GitHub Actions workflow that runs Pint, PHPStan, and Pest on every push.

## Step 1 — GitHub Actions CI

`.github/workflows/ci.yml`:

```yaml
name: CI

on:
  push:
    branches: [main]
  pull_request:
    branches: [main]

jobs:
  qa:
    runs-on: ubuntu-latest

    services:
      postgres:
        image: postgres:16
        env:
          POSTGRES_USER: postgres
          POSTGRES_PASSWORD: postgres
          POSTGRES_DB: projectly_test
        ports:
          - 5432:5432
        options: >-
          --health-cmd pg_isready
          --health-interval 10s
          --health-timeout 5s
          --health-retries 5

      redis:
        image: redis:7
        ports:
          - 6379:6379

    steps:
      - uses: actions/checkout@v4

      - name: Setup PHP
        uses: shivammathur/setup-php@v2
        with:
          php-version: 8.3
          extensions: pdo_pgsql, redis
          coverage: pcov

      - name: Install dependencies
        run: composer install --no-interaction --prefer-dist

      - name: Copy env
        run: cp .env.example .env.testing && php artisan key:generate --env=testing

      - name: Run Pint (check only)
        run: ./vendor/bin/pint --test

      - name: Run PHPStan
        run: ./vendor/bin/phpstan analyse

      - name: Run tests with coverage
        env:
          DB_HOST: 127.0.0.1
          REDIS_HOST: 127.0.0.1
        run: ./vendor/bin/pest --coverage --min=70
```

Push to `main`, open the Actions tab on GitHub. You should see your workflow running. Fix anything it surfaces.

## Step 2 — Forge deployment

Assuming you have a Forge + DigitalOcean account from `build/ch00-prerequisites.md`:

1. **Provision a server** in Forge → DigitalOcean → 1 GB droplet ($6/mo) → PHP 8.3 + PostgreSQL 16 + Redis
2. **Add a site** → primary domain `projectly.YOURDOMAIN.com` → project type Laravel → web directory `/public`
3. **Connect to GitHub** → repository `YOU/projectly` → branch `main`
4. **Install repository**
5. **Environment** tab → set `APP_ENV=production`, `APP_DEBUG=false`, `APP_KEY` (Forge can auto-generate), `APP_URL=https://projectly.YOURDOMAIN.com`, plus DB / Redis / Mail vars
6. **Database** tab → create DB `projectly`, then in `psql` enable pgvector: `CREATE EXTENSION vector;`
7. **SSL** → enable Let's Encrypt
8. **Daemons** → add a queue worker: `php /home/forge/projectly.YOURDOMAIN.com/artisan queue:work redis --queue=reports,default --tries=3`
9. **Scheduler** → enable (Forge installs the cron entry)
10. **Deployment script**:

```bash
cd /home/forge/projectly.YOURDOMAIN.com
git pull origin main
composer install --no-interaction --prefer-dist --optimize-autoloader --no-dev
( flock -w 10 9 || exit 1
    echo 'Restarting FPM...'; sudo -S service php8.3-fpm reload ) 9>/tmp/fpmlock

php artisan migrate --force
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan queue:restart
npm ci
npm run build
```

11. Click **Deploy now**

After ~30 seconds, visit https://projectly.YOURDOMAIN.com → you should see the landing page.

## Step 3 — Smoke test production

```bash
curl -I https://projectly.YOURDOMAIN.com
```

Expect `200 OK` and HTTPS headers.

Register an account through the live site, create a team, create a project, create a task, assign it. Watch Forge's "Application logs" tab.

## Step 4 — Connect Sentry (optional but recommended)

```bash
composer require sentry/sentry-laravel
php artisan sentry:publish --dsn=YOUR_DSN
```

Set `SENTRY_LARAVEL_DSN` in Forge env. Now any production error gets reported.

## Verify it works

- ✅ GitHub Actions CI runs and passes
- ✅ Forge deploy completes without errors
- ✅ Live site responds with 200
- ✅ You can register and use the app live
- ✅ Queue worker is running (check Forge daemons tab)
- ✅ Scheduler is running (check Forge scheduler tab)

## Commit

```bash
git add .github
git commit -m "ci: GitHub Actions for lint, stan, tests"
```

## What's next

➡️ `ch41-build.md` — monitoring: log channels, Sentry, Telescope, simple uptime.
