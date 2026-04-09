# Chapter 46 — Portfolio and GitHub Polish

Your GitHub is your real resume. Hiring managers will spend 90 seconds there. Make those 90 seconds count.

## What hiring managers look for

1. **A pinned repo or two** that look polished
2. **README files** that explain what the project does, with screenshots
3. **Recent activity** — commits in the last few months
4. **Clean commit history** — meaningful messages, not "wip" or "fix"
5. **Tests in the repo** — even a few feature tests
6. **A live demo URL** — DigitalOcean, Forge, Vercel, anything

## The 3 portfolio projects

By the time you finish this course, you'll have built three projects. All three should be on GitHub:

### 1. Task Manager (from Phase 2)
- Auth, CRUD, validation, Blade UI, simple but clean
- README explains "Built to learn Laravel routing, controllers, validation, Blade"

### 2. Blog Platform (from Phase 3)
- Categories, tags, comments, likes, eager loading, API resources
- Livewire search-as-you-type
- Pest tests
- README highlights: "Demonstrates Eloquent relationships, eager loading, API resources, Livewire"

### 3. SaaS Starter (from Phase 7) — your centerpiece
- Multi-tenant
- Sanctum API
- Filament admin
- Queue + scheduled jobs
- Mail notifications
- Pest tests with > 70% coverage
- Dockerfile
- GitHub Actions CI running tests
- Live demo

This is the project that gets you hired.

## README template

```markdown
# Project Name

One-line description.

![Screenshot](docs/screenshot.png)

## Features
- Feature 1
- Feature 2

## Tech Stack
- Laravel 13 (PHP 8.3+)
- MySQL 8
- Redis (queues + cache)
- Filament (admin)
- Pest (tests)

## Local Setup
```bash
git clone ...
composer install
cp .env.example .env
php artisan key:generate
php artisan migrate --seed
php artisan serve
```

## Tests
```bash
php artisan test
```

## Demo
https://demo.example.com — login: demo@example.com / password
```

## Commit message style

```
feat: add post comments with policies
fix: prevent N+1 in dashboard query
test: cover post update endpoint
refactor: extract pricing to service class
docs: update setup instructions
```

## Hands-on Task

Pick your blog project. Write a real README with screenshots, setup instructions, and a feature list. Push it.

➡️ Next: `ch47-interview-questions.md`
