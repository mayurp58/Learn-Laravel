# P3 — Finish: Deploy and Hand-off

**Project state:** Blog now has authenticated REST API, policies, rate limits, Postman collection.

## 1. Polish

### Merge to main and push

```bash
git checkout main
git merge feature/api
git push
```

### Tag a release

```bash
git tag v1.0-api
git push --tags
```

This is small but it reads as "this dev tags releases" — a real signal.

### Update the README badge

If you want to flex slightly, add a `![Postman](https://img.shields.io/badge/Postman-collection-orange)` badge near the top of the README, linking to the postman/ folder.

## 2. Deploy

Re-deploy your existing P2 site (the same Forge or Render service). The same database is fine — no migrations to run; Sanctum's `personal_access_tokens` table was created back in P2 ch20 via `install:api`.

After deploy:

```bash
curl -s https://your-blog.com/api/v1/posts | jq .
```

Should return JSON. If yes, you're live.

Try registering through curl against your live site:
```bash
curl -X POST https://your-blog.com/api/v1/auth/register \
  -H "Accept: application/json" \
  -d '{"name":"Test","email":"newuser@example.com","password":"secret123","password_confirmation":"secret123"}'
```

You should get a token back. Save it as proof and move on.

## 3. Hand-off

### What you've achieved

- ✅ A real Sanctum-authenticated REST API on top of an existing Laravel app
- ✅ Policies replacing inline authorization
- ✅ URL versioning (`/api/v1/`) — you understand the upgrade path
- ✅ Rate limiting per user / per IP
- ✅ A Postman collection committed to the repo
- ✅ Full API documentation in README

### Updated resume bullet

> **Blog Platform** — Multi-author Laravel 13 blog with Sanctum-authenticated REST API. Eloquent relationships, eager loading, query builder aggregates, transactions, API Resources, Policies, rate limiting, URL versioning, and a Postman collection. github.com/YOU/blog · live: blog.example.com

### What you'll add in P4

Almost everything you haven't seen yet:

- Service container deep-dive (Notifier interface)
- Service providers
- Events & listeners
- Queues + Horizon-style job handling
- Scheduled jobs (`routes/console.php`)
- Mail + notifications
- File storage / uploads
- Cache strategies (incl. L13's `Cache::touch`)
- Localization
- Pest tests (Phase 6, interleaved with P4)
- Filament admin panel
- Livewire interactive UI
- Laravel AI SDK (L13 first-party)
- Vector / semantic search (pgvector)
- Multi-tenancy
- Real deployment with CI

It's the biggest project. Take your time.

## Now: starting P4

Open `phase-5-advanced/ch26-service-container.md` to start Phase 5, then come to `build/p4-projectly/00-spec.md` when prompted.

P4 is **a brand-new project**. Don't extend P2 — Projectly is a different domain (multi-tenant SaaS) and deserves its own repo from day one.

```bash
cd ~/Sites
# don't make the folder yet — 01-setup.md will run composer create-project
```

Close this folder mentally. The blog is done. Onward.
