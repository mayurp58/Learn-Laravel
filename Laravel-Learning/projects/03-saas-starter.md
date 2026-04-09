# Mini Project 3 — Multi-Tenant SaaS Starter (Portfolio Centerpiece)

**Build after Phase 7.** This is the project that gets you hired. Take your time. Polish it.

## Concept

A simple "Project Management" SaaS where teams sign up, invite members, and manage projects + tasks. The idea is mundane on purpose — the *quality* of execution is what matters.

## Features

- Team-based multi-tenancy (Jetstream-style or build manually)
- User registration → create team
- Invite team members via email
- Roles: owner, admin, member
- Each team has its own projects
- Each project has tasks (CRUD, statuses: todo / in progress / done)
- Email notifications when assigned to a task
- Activity log
- Filament admin panel (for site admin, not team admin)
- REST API (Sanctum) for hypothetical mobile app
- Queue jobs for emails and notifications
- Scheduled task: daily digest email
- Tests with Pest
- Deployed live with HTTPS

## Tech Stack

- Laravel 13 / PHP 8.3+
- MySQL
- Redis (cache + queues)
- Sanctum (API)
- Filament (admin)
- Livewire (interactive UI parts)
- Tailwind
- Pest (tests)
- GitHub Actions (CI: pint, phpstan, tests)
- Forge or Docker for deployment

## Data Model (sketch)

```
users
teams: id, owner_id, name
team_user (pivot): team_id, user_id, role
projects: id, team_id, name, description
tasks: id, project_id, assigned_to, title, status, due_at
invitations: id, team_id, email, token, expires_at
activities: id, team_id, user_id, description, subject_id, subject_type
```

## Multi-tenancy approach (single DB, scoped)

Add `team_id` to `projects`, etc. In the boot method of each tenant model:

```php
protected static function booted(): void
{
    static::addGlobalScope('team', function ($query) {
        if ($team = currentTeam()) {
            $query->where('team_id', $team->id);
        }
    });

    static::creating(function ($model) {
        $model->team_id = currentTeam()->id;
    });
}
```

`currentTeam()` is a helper that reads from session/auth.

## Tests to include

- Auth flows
- Team creation and invitation acceptance
- Tenant isolation: user A from team 1 cannot read team 2's projects (huge for credibility)
- Task CRUD
- Email is sent when assigned
- Job is dispatched when X happens
- API endpoints (auth, validation, pagination)

## Polish (this is what gets you hired)

- ✅ A clean README with screenshots and a live demo URL
- ✅ Docker Compose file for local setup
- ✅ GitHub Actions CI (run tests + pint on every PR)
- ✅ At least 70% test coverage
- ✅ Sentry or Bugsnag wired up
- ✅ A "system design" section in the README explaining your architecture choices
- ✅ Conventional commits

## Optional differentiator: an AI feature (Laravel 13)

Since you're targeting Laravel 13, add **one** small AI-powered feature using the first-party AI SDK. It takes a weekend and dramatically separates your portfolio from the standard "CRUD + auth" SaaS clones. Pick one:

- **Smart task summaries** — when a project has 20+ tasks, an "AI summary" button that calls `AI::prompt(...)` on a queue and stores the result. Show it on the project dashboard.
- **Semantic task search** — generate embeddings on task creation (queued), store in pgvector, expose `/search?q=...` using `whereVectorSimilarTo()`.
- **Auto-generated stand-up reports** — daily scheduled job that calls the AI SDK with each user's completed tasks and emails them a Slack-style stand-up.

Whichever you pick: dispatch the AI call to a queue (never inline in a request), use `AI::fake()` in tests, and write 1–2 paragraphs in the README explaining your prompt design and cost controls. That README section is the part interviewers will quote back to you.

## What you'll be able to say in interviews

> "I built a multi-tenant SaaS in Laravel with Sanctum APIs, Filament admin, queue-driven email and notifications, scheduled jobs, full Pest test coverage including tenant-isolation tests, and a CI pipeline. It's deployed at saas-demo.example.com — happy to walk you through it."

That sentence opens doors.
