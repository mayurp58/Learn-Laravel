# P1 — Setup

**Read before:** `00-spec.md`
**You should already have:** PostgreSQL running, PHP 8.3+, Composer, Node 20+, GitHub SSH key (see `build/ch00-prerequisites.md`).

This file scaffolds the bookmarks project. After you finish it, your local app should boot to the Laravel welcome page connected to a real Postgres database, with an empty git history ready for your first feature commit.

## Step 1 — Create the project

```bash
cd ~/Sites
composer create-project laravel/laravel bookmarks
cd bookmarks
```

Composer will pull Laravel 13 and install everything in `vendor/`. This takes a minute or two.

## Step 2 — Create the Postgres database

```bash
psql postgres -c "CREATE DATABASE bookmarks;"
```

Verify:
```bash
psql -l | grep bookmarks
```

You should see `bookmarks` in the list.

## Step 3 — Configure `.env`

Open `.env` in your editor and update these lines:

```env
APP_NAME=Bookmarks
APP_URL=http://localhost:8000

DB_CONNECTION=pgsql
DB_HOST=127.0.0.1
DB_PORT=5432
DB_DATABASE=bookmarks
DB_USERNAME=YOUR_MAC_USERNAME    # whoami in the terminal — Postgres on macOS uses your OS user
DB_PASSWORD=
```

> **Tip:** If you're not sure about your Postgres user, run `psql postgres -c "\du"`. On a fresh Homebrew install you'll see your macOS username with `Superuser` privileges and no password.

## Step 4 — Run the initial migrations

```bash
php artisan migrate
```

You should see output like:

```
INFO  Preparing database.
INFO  Running migrations.
0001_01_01_000000_create_users_table ......... DONE
0001_01_01_000001_create_cache_table ......... DONE
0001_01_01_000002_create_jobs_table .......... DONE
```

If you see a connection error, fix `.env` before continuing. Don't proceed until migrations succeed.

## Step 5 — Install frontend deps and start Vite

```bash
npm install
npm run dev
```

Leave that running. In a **second terminal**:

```bash
php artisan serve
```

Visit http://localhost:8000 — you should see the Laravel 13 welcome page. If yes: scaffolding is done.

## Step 6 — Initialize git and push to GitHub

Stop both servers (`Ctrl+C` in each terminal).

```bash
git init
git add .
git commit -m "chore: scaffold Laravel 13 bookmarks project"
```

Then on GitHub, create a new **empty** public repo named `bookmarks` — no README, no .gitignore, no license (Laravel already has one).

Wire the remote:
```bash
git remote add origin git@github.com:YOUR_USERNAME/bookmarks.git
git branch -M main
git push -u origin main
```

Refresh GitHub. You should see your scaffold pushed.

## Step 7 — Create a working branch

We'll do all P1 work on a single feature branch and merge to `main` only when each feature is verified. This is real-life workflow.

```bash
git checkout -b feature/bookmarks
```

## What you have now

```
~/Sites/bookmarks/        ← Laravel 13 scaffold
├── connected to Postgres database 'bookmarks'
├── pushed to github.com/YOU/bookmarks
└── on branch feature/bookmarks
```

You're ready to start applying Phase 2 chapters. Open `phase-2-core/ch05-request-lifecycle.md` first, read it, then come back to `ch05-build.md` to start adding features.

## Common pitfalls

- **`could not connect to server: Connection refused`** → Postgres isn't running. `brew services start postgresql@16`.
- **`role "your_user" does not exist`** → use `whoami` to find your macOS username, that's your default Postgres role on Homebrew.
- **`npm install` errors about Node version** → upgrade Node to 20+.
- **`php artisan serve` says address already in use** → something else is on port 8000. Use `php artisan serve --port=8001` or kill the other process.
- **You committed `.env` by accident** → it's in `.gitignore` by default, but if you somehow added it, run `git rm --cached .env && git commit -m "chore: untrack .env"`.

## Next

➡️ `ch05-build.md` — explore the request lifecycle in your real project, then add the home page route.
