# The Build Track

The chapters in `phase-1-foundations/` through `phase-7-ecosystem/` teach you Laravel concepts in isolation. The **build track** (this folder) is the parallel track where you actually *build real, deployable apps* by applying each chapter's concepts to a running project.

By the time you finish Phase 8, you'll have **four deployed apps on GitHub** — not exercises, but real things you can show in interviews.

## How to use this folder

For every teaching chapter you read, there's a matching build file here that says "now apply what you just learned to your current project." Read the teaching chapter first, do its hands-on micro-task, then come here and add the feature to the running project.

The flow looks like this:

```
Read phase-2-core/ch07-controllers.md       ← teaching
       ↓
Do its hands-on task (throwaway code)        ← reinforce the concept
       ↓
Open build/p1-bookmarks/ch07-build.md        ← apply to your project
       ↓
git commit                                   ← real progress
```

## The four projects

| # | Project | Spans chapters | What you'll learn by building it |
|---|---|---|---|
| **P1** | **Bookmark Manager** (`p1-bookmarks/`) | 5 – 12 (Phase 2) | Routes, controllers, validation, Blade, sessions, CSRF, middleware. Single-user CRUD app. |
| **P2** | **Blog Platform** (`p2-blog/`) | 13 – 20 (Phase 3) | Migrations, factories, Eloquent, relationships, eager loading, query builder, transactions, API resources. |
| **P3** | **Blog REST API** (`p3-blog-api/`) | 21 – 25 (Phase 4) | **Extends P2.** Adds Breeze (web auth), Sanctum (API tokens), policies, REST endpoints, rate limiting, versioning. |
| **P4** | **Projectly — multi-tenant SaaS** (`p4-projectly/`) | 26 – 44b + 37 – 41 (Phase 5, 6, 7) | The big one. Service container, providers, queues, events, scheduler, cache, mail, notifications, broadcasting, Filament admin, Livewire UI, AI SDK, full Pest test suite, deployment. **The portfolio centerpiece.** |

After P4 you move to Phase 8 (job prep), which uses all four projects as resume + portfolio material.

## Stack (locked in)

To keep build files concrete, the entire course uses the same stack:

- **Framework:** Laravel 13 (PHP 8.3+)
- **Database:** PostgreSQL 16 (chosen so P4 can use the L13 vector search chapter)
- **CSS:** Tailwind (Laravel default)
- **Frontend:** Blade for P1/P2/P3, Livewire for P4's interactive parts (task board, dashboards)
- **Auth:** Breeze for the web, Sanctum for the API
- **Testing:** Pest 4
- **Deploy:** Laravel Forge on a $6 DigitalOcean droplet (P4 also gets a Docker Compose file)

If you want to swap any of these out (e.g., MySQL instead of PostgreSQL), you can — but you'll need to translate a few snippets yourself, and the AI / vector search chapters will only work on PostgreSQL.

## Project boundaries

- **P1, P2, P4 are brand-new repos.** Each one starts with `composer create-project laravel/laravel <name>`.
- **P3 extends P2.** It's the same codebase — you add API features to your existing blog. This mirrors real life: most apps grow web UIs and APIs side by side, sharing models and business logic.

When a project ends, the corresponding folder has a `99-finish.md` file with deployment instructions and a "you're done with this project — here's what's next" handoff.

## Build file format

Every `chXX-build.md` follows the same shape:

1. **What you're building this chapter** — one paragraph
2. **Prerequisites** — what state your project should be in
3. **Steps** — full code, exact files, in order
4. **Verify it works** — what to click / run to confirm
5. **Commit** — a suggested git commit message
6. **Common pitfalls** — what trips people up
7. **What's next** — pointer to the next build file

You can read it top to bottom and follow along. There are no missing steps.

## Start here

1. Read `ch00-prerequisites.md` once. Get your tools installed.
2. Finish `phase-1-foundations/` (chapters 1–4). There's no build project yet — these are pure concepts.
3. When you start `phase-2-core/ch05-request-lifecycle.md`, also open `build/p1-bookmarks/00-spec.md` and read what you'll be building.
4. Then `build/p1-bookmarks/01-setup.md` to scaffold P1.
5. From there, every teaching chapter has a matching build file. Follow them in order.

Good luck. Build real things.
