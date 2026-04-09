# P4 — Projectly (Multi-Tenant SaaS) — Spec

**Spans:** Phase 5 (ch26–36), Phase 6 (ch37–41), Phase 7 (ch42–44b)
**Final state:** A deployed multi-tenant project management SaaS — your portfolio centerpiece. The biggest project; takes weeks. Don't rush.

## The pitch

Teams sign up. Teams have members with roles. Teams have projects. Projects have tasks. Tasks have assignees, statuses, attachments, comments. Email notifications. Activity logs. AI-summarized standups. Real-time updates. Filament admin panel. Livewire task board. Full Pest test suite. CI on every PR. Deployed live.

The domain is intentionally generic — like Asana or Linear's first version. The point is not innovation, it's *executing every advanced Laravel concept correctly* in one cohesive codebase.

## Features

### Core
- Email + password auth (Breeze)
- User creates account → automatically gets a personal team
- User can create more teams, switch between them
- Invite team members by email (queued mailable)
- Roles: owner, admin, member
- Tenant isolation: a user from team A can NEVER see team B's projects/tasks (enforced by global scope + tested)

### Projects
- Each team has many projects
- Project CRUD via Livewire
- Project status: active / archived
- Project description (markdown)

### Tasks
- Each project has many tasks
- Tasks have: title, description, status (todo/in_progress/done), assignee, due date
- Drag-and-drop kanban board (Livewire)
- File attachments stored on disk (`storage/app/public/attachments`)
- Comments on tasks
- Activity log: "Alice marked Task #5 as done"

### Notifications
- Email when assigned to a task
- Email when mentioned in a comment (`@alice`)
- In-app notification bell (Livewire dropdown reading `notifications` table)
- Daily digest email (scheduled job)

### AI features (L13 first-party)
- "Summarize this project" button — AI SDK call, queued
- "Generate standup" — daily scheduled job that builds an AI standup from each user's completed tasks
- (Optional, advanced) Semantic task search using `whereVectorSimilarTo`

### Admin
- Filament admin panel at `/admin` for site-wide management
- Site admin can list teams, users, suspend accounts

### Deploy + Ops
- Docker Compose file for local clone-and-run
- GitHub Actions CI: pint, phpstan, Pest
- Forge deploy from main
- Sentry / Bugsnag wired up
- 70%+ test coverage including tenant-isolation tests

## Data model (sketch)

```
users                                 (Breeze)
teams (id, owner_id, name, slug)
team_user (team_id, user_id, role)    pivot
team_invitations (id, team_id, email, token, expires_at)

projects (id, team_id, name, description, status)
tasks (id, project_id, assignee_id nullable, title, description, status, due_at)
task_attachments (id, task_id, path, original_name, size)
task_comments (id, task_id, user_id, body)

activities (id, team_id, user_id, description, subject_id, subject_type)   ← polymorphic
notifications                         (Laravel default)
```

Plus Sanctum's `personal_access_tokens` (we'll add an API too).

## Stack

- Laravel 13, PHP 8.3
- PostgreSQL 16 + pgvector (for AI semantic search)
- Redis (cache + queues + sessions)
- Tailwind, Livewire 3 for interactive UI
- Filament 4 for admin
- Pest 4 for tests
- Mailpit locally; Mailgun/SES in production

## Phases of P4 (rough roadmap)

1. **Foundations (ch26-28)**: container, providers, facades. Wire a `Notifier` interface.
2. **Events + Activity log (ch29)**.
3. **Queues + jobs (ch30)** for slow work.
4. **Scheduler (ch31)** for digest emails.
5. **Notifications (ch32) + Mail (ch33)** for invites.
6. **Storage (ch34)** for task attachments.
7. **Cache (ch35)** for dashboard stats.
8. **Localization (ch36)** — ship in English + one more language.
9. **Tests (ch37-39)** — interleaved with everything above.
10. **Deploy + monitoring (ch40-41)**.
11. **Livewire UI (ch42)** — task board.
12. **Inertia alternative (ch43)** — optional or skip.
13. **Filament admin (ch44)**.
14. **AI SDK (ch44b)** — standups + semantic search.
15. **Finish (99)** — polish, demo URL, retrospective.

## What "done" looks like

- Live at projectly.YOURDOMAIN.com
- Public landing page that explains what it is
- Two demo accounts with two teams that demonstrate isolation
- README with screenshots, system design notes, and a 60-second video walkthrough link
- 70%+ Pest coverage with passing CI
- Code on GitHub with conventional commits

## What you'll be able to say in interviews

> "Projectly is a multi-tenant project management SaaS I built in Laravel 13. Sanctum-authenticated REST API plus a Livewire task board, Filament admin panel, queue-driven email and notifications, scheduled jobs for AI-generated standups using the Laravel AI SDK, full tenant-isolation tests in Pest, and a CI/CD pipeline. Live at projectly.example.com — happy to walk you through it."

That's a sentence that opens senior interview doors.

## Next

Read `phase-5-advanced/ch26-service-container.md`, then open `01-setup.md`.
