# P2 — Finish: Polish, Deploy, Hand-off (but P3 extends this!)

**Project state:** All Phase 3 chapters applied. Public blog + author dashboard + read-only API.

## Important: P3 extends this same project

Unlike P1 → P2 (where you started fresh), **P3 is not a new repo**. P3 adds API authentication, policies, write endpoints, and rate limiting to *this same blog*. So:

- **Don't create a new project for P3**
- **Don't archive this repo**
- Treat P2's "finish" as a checkpoint, not an endpoint

That said, you should still polish and deploy what you have now — a deployed P2 is the proof that you actually built it before piling more on.

## 1. Polish

### Merge to main

```bash
git checkout main
git merge feature/blog
git push
```

### README

```markdown
# Blog Platform

A multi-author Laravel 13 blog. Built to demonstrate Eloquent relationships, eager loading, query builder aggregates, transactions, and API resources.

![Screenshot](docs/screenshot.png)

## Stack
- Laravel 13 (PHP 8.3)
- PostgreSQL 16
- Tailwind CSS
- Breeze (auth)

## Features
- Public archive of posts with categories and tags
- Author dashboard for post CRUD
- Tag cloud and monthly archive (Query Builder aggregates)
- Comments on posts
- Eager-loaded post lists (no N+1 — query counter visible in dev)
- Read-only JSON API at `/api/posts`

## Demo
Live: https://blog.YOURDOMAIN.com
Demo author: `demo@example.com` / `password`

## Local setup
```bash
git clone git@github.com:YOU/blog.git
cd blog
composer install
npm install
cp .env.example .env
php artisan key:generate
# Configure .env with Postgres credentials
php artisan migrate --seed
npm run build
php artisan serve
```

## What I learned
This was my Phase 3 project from a structured Laravel learning roadmap. It cemented Eloquent (relationships, scopes, accessors), eager loading and N+1 prevention, Query Builder aggregates, transactions, and API Resources.
```

### Screenshots

- Public post list page
- Single post with comments
- Author dashboard
- The query counter at the bottom-right showing a low number on `/posts` (this is unusual to show in a portfolio README and reads as "this dev knows what an N+1 is")

## 2. Deploy

Same options as P1 (`build/p1-bookmarks/99-finish.md`). Reuse your Forge or Render account, just provision a second site.

Don't forget:
- Set `DB_CONNECTION=pgsql`
- Run `php artisan migrate --seed --force` on deploy (the seeded data populates your live demo)
- Set `APP_URL` to the live domain so `route()` calls in API responses are correct

## 3. Hand-off

### What you've achieved

- ✅ Built and deployed a real multi-table Laravel app
- ✅ Used every Phase 3 concept: migrations, factories, Eloquent (basics, relationships, scopes, accessors, eager loading), query builder, transactions, API resources
- ✅ Internalized N+1 by *seeing* it explode and fixing it
- ✅ Two real portfolio projects on GitHub

### What you'll add in P3 (next)

- Sanctum API token auth
- POST/PUT/DELETE endpoints for posts
- Policies for proper authorization
- Rate limiting
- API versioning (`/api/v1/...`)
- A Postman collection in the repo

### Resume bullet you can write today

> **Blog Platform** — Multi-author Laravel 13 blog with categories, tags, comments, author dashboard, and read-only JSON API. Demonstrates Eloquent relationships, eager loading (N+1 prevention), query builder aggregates, transactional writes, and API Resources. github.com/YOU/blog · live: blog.example.com

### Resume bullet you'll write at the end of P3

(Updated version of the same line to add: "Sanctum-authenticated REST API with policies, rate limiting, versioning, and Postman collection.")

## Now: starting P3

**Stay in this project.** Open `phase-4-auth-api/ch21-auth-starters.md` to start Phase 4, then come to `build/p3-blog-api/00-spec.md` when prompted.

You'll create a new branch for P3:
```bash
git checkout main
git pull
git checkout -b feature/api
```

And start building the API alongside the existing web app.
