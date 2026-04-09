# P1 — Finish: Polish, Deploy, Retire

**Project state:** All chapters 5–12 applied. App works locally end-to-end.

## What you're doing this chapter

Three things, in order:

1. **Polish** — README, demo account, screenshots
2. **Deploy** — Forge or Render so you have a public URL
3. **Hand-off** — close the chapter mentally and prepare to start P2

## 1. Polish

### Merge to main

```bash
git checkout main
git merge feature/bookmarks
git push
```

### Write a real README

Replace the default `README.md` (Laravel ships its own) with this:

```markdown
# Bookmarks

A small personal bookmark manager I built while learning Laravel 13. Single-user CRUD, search, recently-viewed history, JSON/CSV export.

![Screenshot](docs/screenshot.png)

## Stack
- Laravel 13 (PHP 8.3)
- PostgreSQL 16
- Tailwind CSS
- Breeze (auth)

## Features
- Register / login (Breeze)
- Add, edit, delete bookmarks
- Search by title and description
- Tag bookmarks (free-form)
- Recently viewed history (session-backed)
- JSON and CSV export

## Demo
Live at: https://bookmarks.YOURDOMAIN.com
Demo account: `demo@example.com` / `password`

## Local setup
```bash
git clone git@github.com:YOU/bookmarks.git
cd bookmarks
composer install
npm install
cp .env.example .env
php artisan key:generate
# Edit .env with your Postgres credentials
php artisan migrate
npm run build
php artisan serve
```

## What I learned
This was my first Laravel project after years of CodeIgniter. It cemented routing, controllers, Form Requests, Blade components, sessions, and middleware — the entire Phase 2 of my learning roadmap.
```

### Take screenshots

Run the app, log in, add some real bookmarks, take 2–3 screenshots:
- The index page with a few bookmarks
- The "Add bookmark" form
- The home page with "Recently viewed" populated

Save them in `docs/` (create the folder). Reference them in the README.

### Create a demo account seeder

`database/seeders/DemoSeeder.php`:

```php
<?php

namespace Database\Seeders;

use App\Models\Bookmark;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DemoSeeder extends Seeder
{
    public function run(): void
    {
        $user = User::firstOrCreate(
            ['email' => 'demo@example.com'],
            ['name' => 'Demo User', 'password' => Hash::make('password')]
        );

        $bookmarks = [
            ['url' => 'https://laravel.com/docs', 'title' => 'Laravel Docs', 'tags' => 'laravel,docs'],
            ['url' => 'https://laravel-news.com', 'title' => 'Laravel News', 'tags' => 'laravel,news'],
            ['url' => 'https://laracasts.com', 'title' => 'Laracasts', 'tags' => 'laravel,video'],
        ];

        foreach ($bookmarks as $b) {
            $user->bookmarks()->firstOrCreate(['url' => $b['url']], $b);
        }
    }
}
```

Register it in `DatabaseSeeder.php`:
```php
$this->call(DemoSeeder::class);
```

Run locally to verify:
```bash
php artisan migrate:fresh --seed
```

## 2. Deploy

### Option A — Laravel Forge + DigitalOcean (recommended)

1. Provision a $6 DigitalOcean droplet from Forge (Forge does this for you)
2. Create a new site, point it at the `bookmarks` GitHub repo, branch `main`
3. Forge auto-installs PHP 8.3, Nginx, PostgreSQL
4. In the Forge UI, set environment variables: `APP_NAME`, `APP_ENV=production`, `APP_DEBUG=false`, `APP_URL=https://your-subdomain.com`, DB credentials
5. Add a deploy script: `php artisan migrate --force && php artisan db:seed --class=DemoSeeder --force`
6. Click Deploy
7. Forge gives you a free SSL via Let's Encrypt — enable it

### Option B — Render.com (free, slower)

1. Sign up at render.com
2. New → Web Service → connect GitHub → select `bookmarks`
3. Build command: `composer install --no-dev && npm ci && npm run build && php artisan migrate --force && php artisan db:seed --class=DemoSeeder --force`
4. Start command: `php artisan serve --host 0.0.0.0 --port $PORT`
5. Add a free Postgres add-on, copy its URL into env vars
6. Deploy

### Option C — Skip deployment for now

Honestly fine if you're impatient to get to P2. You can come back to this in Phase 6 (`ch41-deployment.md`) when there's a proper deployment chapter. **But:** add a `TODO: deploy` line to your README so you don't forget.

## 3. Hand-off

### What you've achieved

- ✅ Built and deployed a real Laravel app from scratch
- ✅ Used routing, controllers, validation, middleware, sessions, Blade
- ✅ Wrote authorization checks (will be replaced by Policies in P2)
- ✅ Pushed clean commit history to GitHub
- ✅ Have a portfolio piece you can demo

### What you did NOT learn yet (and will in P2)

- Eloquent relationships (one-to-many, many-to-many)
- Migrations beyond a single table
- Eager loading and N+1 prevention
- Query Builder for aggregates
- Transactions
- API resources
- Factories and seeders at scale

These are the meat of Phase 3 — the next 8 chapters.

### Resume bullet you can write today

> **Bookmarks** — Personal bookmark manager built in Laravel 13. Auth via Breeze, validation via Form Requests, search, session-backed history, JSON/CSV export. Deployed on Forge. github.com/YOU/bookmarks · live: bookmarks.example.com

### Keep using it

Seriously — log in once a week and add real bookmarks. The fastest way to find UX bugs in your own work is to use it daily.

## Now: starting P2

Close this folder. Open `phase-3-eloquent/ch13-migrations.md` to start Phase 3, then come to `build/p2-blog/00-spec.md` when prompted.

P2 is **a brand-new project** — fresh `composer create-project`, fresh git repo. The bookmark manager is done.
