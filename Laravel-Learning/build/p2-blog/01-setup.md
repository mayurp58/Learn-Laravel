# P2 — Setup

**Read first:** `00-spec.md`
**Project state:** None — we're starting fresh.

This is a brand-new project. P1 (bookmarks) is done and retired. Don't try to extend it.

## Step 1 — Create the project

```bash
cd ~/Sites
composer create-project laravel/laravel blog
cd blog
```

## Step 2 — Create the database

```bash
psql postgres -c "CREATE DATABASE blog;"
```

## Step 3 — Configure `.env`

```env
APP_NAME=Blog
APP_URL=http://localhost:8000

DB_CONNECTION=pgsql
DB_HOST=127.0.0.1
DB_PORT=5432
DB_DATABASE=blog
DB_USERNAME=YOUR_USER
DB_PASSWORD=
```

## Step 4 — Initial migrations

```bash
php artisan migrate
```

## Step 5 — Install Breeze (we'll need auth for the author dashboard)

```bash
composer require laravel/breeze --dev
php artisan breeze:install blade
npm install
npm run build
php artisan migrate
```

## Step 6 — Git + GitHub

```bash
git init
git add .
git commit -m "chore: scaffold Laravel 13 blog project"
```

Create a public GitHub repo named `blog`, then:

```bash
git remote add origin git@github.com:YOU/blog.git
git branch -M main
git push -u origin main
git checkout -b feature/blog
```

## Step 7 — Smoke test

```bash
npm run dev    # leave running in one terminal
php artisan serve   # in another
```

Visit http://localhost:8000 → Laravel welcome. Click "Register" (Breeze added it) → create an account → land on `/dashboard`. Working baseline confirmed.

## What's next

➡️ `ch13-build.md` — design and run all the migrations for the blog schema.
