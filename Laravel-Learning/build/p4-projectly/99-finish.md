# P4 — Finish: Polish, Deploy, Course Wrap

**Project state:** Every Phase 5/6/7 chapter applied. Projectly works locally + on Forge.

This is the last build file in the course. After this you move to Phase 8 (job prep) using all four projects as portfolio material.

## 1. Polish

### Merge to main

```bash
git checkout main
git merge feature/foundations    # or whatever branches you have open
git push
```

### README — the centerpiece

Projectly's README is the single most important document in your portfolio. Spend an hour on it. Include:

```markdown
# Projectly

A multi-tenant project management SaaS built in Laravel 13. Production-quality code, AI-powered features, full test suite, deployed live.

![Screenshot](docs/screenshot.png)

[Live demo](https://projectly.YOURDOMAIN.com) · [Video walkthrough](https://...)

## Features

### Product
- Multi-tenant teams with role-based membership (owner / admin / member)
- Tenant isolation: enforced at the model level + tested
- Project + task CRUD via Livewire kanban (drag-and-drop)
- Email invitations (signed token, 7-day expiry)
- Activity log via Eloquent model events
- File attachments per task with mime/size validation
- AI-powered daily standups via the Laravel 13 AI SDK
- Semantic task search using pgvector + `whereVectorSimilarTo`

### Engineering
- Service container patterns: `Notifier` interface bound conditionally
- Dedicated service providers per concern
- Custom `CurrentTeam` facade
- Centralized queue routing (`Queue::route` — L13)
- Job attributes (`#[Tries]`, `#[Backoff]`)
- `Cache::touch` for sliding-window dashboard cache
- Localization (English + Spanish)
- 70%+ Pest test coverage including tenant-isolation tests
- Pint + Larastan + GitHub Actions CI on every push
- Filament 4 admin panel
- Sentry-monitored production deploy
- Telescope for local debugging (gated)
- `/up` and `/health` endpoints for uptime monitoring

## Stack
- Laravel 13 (PHP 8.3)
- PostgreSQL 16 + pgvector
- Redis 7 (cache + queues + sessions)
- Tailwind + Livewire 3
- Filament 4
- Pest 4
- Anthropic / OpenAI (configurable)

## Architecture notes

[Write 2-3 paragraphs explaining: how multi-tenancy works, why you chose Livewire over Inertia, how the AI standup pipeline runs end-to-end.]

## Local setup
[Standard clone instructions.]

## Demo accounts
- Owner of "Acme Inc": `acme@example.com` / `password`
- Owner of "Globex Corp": `globex@example.com` / `password`

(These two accounts demonstrate tenant isolation — log in as either and you only see your own team's data.)

## What I learned
[2-3 paragraphs reflecting on the project. Be honest about what you found hard — hiring managers love that.]
```

### Screenshots and a video

- 4–6 screenshots covering: landing, register flow, dashboard, kanban board, Filament admin, AI standup email
- A 60-second Loom (free) video walking through the live site
- Both linked from the README

### Polish git history

If your commits are messy, that's fine — most reviewers don't dig into history. But if you want to flex:

```bash
git log --oneline | head -20
```

Make sure the recent ones use conventional-commit style (`feat:`, `fix:`, `chore:`, etc.).

## 2. Deploy + smoke test

You should already have a Forge deployment from `ch40-build.md`. Run the deploy script one more time to pick up all the recent changes:

```bash
# In Forge UI: click Deploy now
```

Then smoke-test on the live site:
1. Register a new account
2. Create a project
3. Add 3 tasks via Livewire
4. Drag a task between columns
5. Visit `/admin` (with admin user) and confirm it loads
6. Visit `/up` and `/health` — both green
7. Trigger the standup job manually: `php artisan tinker → GenerateStandupForUser::dispatchSync(User::first())` and confirm the email arrives

If all 7 work, you're done.

## 3. Course wrap-up

### What you've built

Across all four projects:

| Project | Lines of code (rough) | What it proves |
|---|---|---|
| **P1 — Bookmarks** | ~1.5k | Routing, controllers, validation, Blade, sessions |
| **P2 — Blog** | ~3k | Eloquent depth, eager loading, Query Builder, transactions, API resources |
| **P3 — Blog API** | +1k on top of P2 | Sanctum, policies, REST, rate limits, versioning |
| **P4 — Projectly** | ~6–8k | Everything else: container, queues, scheduling, mail, notifications, storage, cache, i18n, tests, deployment, Livewire, Filament, AI SDK |

**Four deployed apps. Four GitHub repos. One coherent story.**

### Updated resume bullets

```
SENIOR PHP / LARAVEL DEVELOPER

PROJECTS
• Projectly — Multi-tenant project management SaaS in Laravel 13.
  Multi-tenant teams, Sanctum API, Filament admin, Livewire kanban,
  queued mail/notifications, scheduled jobs, AI-generated standups
  via Laravel 13 AI SDK, semantic task search via pgvector.
  70%+ Pest coverage including tenant-isolation tests. CI/CD via
  GitHub Actions. Deployed on Forge.
  github.com/me/projectly · projectly.example.com

• Blog Platform — Multi-author Laravel 13 blog with Sanctum-authenticated
  REST API. Eloquent relationships, eager loading, query builder
  aggregates, Policies, rate limiting, versioned API, Postman collection.
  github.com/me/blog · blog.example.com

• Bookmark Manager — Personal bookmark manager. Auth, validation,
  search, JSON/CSV export. My first Laravel project.
  github.com/me/bookmarks · bookmarks.example.com
```

### What to do this week

1. **Stop building.** You have enough portfolio for senior interviews.
2. Move to Phase 8: `phase-8-job-prep/ch45-resume.md` and apply your portfolio to your actual resume.
3. Start applying to jobs. Aim for 5/week. Every application includes the GitHub + live demo URLs.
4. While applying, **use Projectly daily** as your real personal project tracker. Real usage finds real bugs, and you'll have answers for "tell me about a bug you fixed" in interviews.

### What you've earned

You started this course as a senior CodeIgniter dev who'd never written Laravel. You finish it with:

- Four deployed, tested, documented Laravel apps
- Real understanding of Laravel 13's features (not just L11/12)
- Tests, CI, deployment, monitoring — the full SDLC
- Talking points for every senior interview question in `interview/senior.md`
- A specific, demonstrable portfolio that's better than 95% of applicants

The job switch is now a function of applying, not learning. Go do it.

## You did good. Close the laptop tonight.

Tomorrow, open `phase-8-job-prep/ch45-resume.md` and start the job hunt phase.

End of build track.
