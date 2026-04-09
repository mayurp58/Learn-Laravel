# P4 — Setup

**Read first:** `00-spec.md`. P4 is a brand-new project. Don't extend P2/P3.

## Step 1 — Create the project

```bash
cd ~/Sites
composer create-project laravel/laravel projectly
cd projectly
```

## Step 2 — Database

```bash
psql postgres -c "CREATE DATABASE projectly;"
psql projectly -c "CREATE EXTENSION IF NOT EXISTS vector;"
```

The `vector` extension is needed for the AI semantic search chapter. We're enabling it now so we don't have to remember later.

## Step 3 — Configure `.env`

```env
APP_NAME=Projectly
APP_URL=http://localhost:8000

DB_CONNECTION=pgsql
DB_HOST=127.0.0.1
DB_PORT=5432
DB_DATABASE=projectly
DB_USERNAME=YOUR_USER
DB_PASSWORD=

# Use Redis for cache + queue + session in dev (matching production)
CACHE_STORE=redis
QUEUE_CONNECTION=redis
SESSION_DRIVER=redis
REDIS_HOST=127.0.0.1
REDIS_PORT=6379

MAIL_MAILER=log         # we'll switch to mailpit in ch33
```

## Step 4 — Install Redis

```bash
brew install redis
brew services start redis
redis-cli ping     # should say PONG
```

## Step 5 — Initial migrations + Breeze

```bash
php artisan migrate
composer require laravel/breeze --dev
php artisan breeze:install blade
npm install
npm run build
php artisan migrate
```

## Step 6 — Install Sanctum (we'll add an API later)

```bash
php artisan install:api
```

## Step 7 — Install Livewire 3 (we'll use it heavily in P4)

```bash
composer require livewire/livewire
```

## Step 8 — Install Pest 4

```bash
composer require pestphp/pest pestphp/pest-plugin-laravel --dev --with-all-dependencies
php artisan pest:install
```

## Step 9 — Git + GitHub

```bash
git init
git add .
git commit -m "chore: scaffold Projectly (Laravel 13 multi-tenant SaaS)"
# create empty 'projectly' repo on GitHub
git remote add origin git@github.com:YOU/projectly.git
git branch -M main
git push -u origin main
git checkout -b feature/foundations
```

## Step 10 — Smoke test

```bash
npm run dev   # terminal 1
php artisan serve   # terminal 2
```

Visit http://localhost:8000 → register an account → land on `/dashboard`. Working baseline confirmed.

## What's next

➡️ `ch26-build.md` — service container: design a `Notifier` interface and bind it.
