# P2 — Blog Platform (Spec)

**Spans:** Phase 3 (chapters 13–20)
**Final state:** A deployed multi-author blog with categories, tags, comments, and a public archive — using every Phase 3 concept naturally.

## The pitch

P1 was deliberately small (one table, no relationships). P2 is the opposite: a content site with real schema complexity that *forces* you to use migrations, factories, Eloquent relationships, eager loading, query builder aggregates, transactions, and API resources.

Boring content, deliberately. The point is the schema and the queries, not the topic.

## Features

1. **Multi-author** — multiple users, each can write posts
2. **Posts** — title, slug, body (markdown), excerpt, published_at, draft/published states
3. **Categories** — each post belongs to one category
4. **Tags** — each post can have many tags (many-to-many)
5. **Comments** — anyone can comment on a published post (no auth required for commenting yet)
6. **Public archive** — `/posts`, `/posts/{slug}`, `/categories/{slug}`, `/tags/{slug}`
7. **Author dashboard** — logged-in authors see their own draft + published posts
8. **N+1 prevention** — the post index page eager-loads everything it needs
9. **Tag cloud** — homepage shows tags weighted by post count (Query Builder aggregate)
10. **API preview** — JSON endpoint for posts using API Resources (full API track is P3)

## Data model

```
users                            (already exists from Breeze)
  id, name, email, password, …

categories
  id, name, slug, description nullable

posts
  id, user_id fk, category_id fk, title, slug, excerpt, body,
  published_at nullable, status enum('draft','published'),
  timestamps

tags
  id, name, slug

post_tag                         (pivot)
  post_id, tag_id, primary (post_id, tag_id)

comments
  id, post_id fk, author_name, author_email, body, timestamps
```

Relationships:
- `User hasMany Post`
- `Post belongsTo User` (alias: `author`)
- `Post belongsTo Category`
- `Post belongsToMany Tag`
- `Post hasMany Comment`
- `Category hasMany Post`

## Routes

```
GET  /                            home — recent posts + tag cloud
GET  /posts                       all published, paginated
GET  /posts/{slug}                show one + comments
POST /posts/{slug}/comments       create comment
GET  /categories/{slug}           filter by category
GET  /tags/{slug}                 filter by tag

GET  /api/posts                   JSON list (preview of P3)
GET  /api/posts/{slug}            JSON single

# Author area (auth required)
GET    /dashboard/posts           my posts
GET    /dashboard/posts/create    new post form
POST   /dashboard/posts           store
GET    /dashboard/posts/{post}/edit
PUT    /dashboard/posts/{post}    update
DELETE /dashboard/posts/{post}    delete

+ Breeze auth routes
```

## What "done" looks like

- Public users can browse posts, click into them, read comments, leave a comment
- Logged-in authors can write/edit/publish their own posts via a dashboard
- Categories and tags filter properly
- Post index page does NOT trigger N+1 (we'll verify this with `DB::listen` or Telescope)
- Tag cloud on the homepage shows tag names sized by post count
- `/api/posts` returns clean JSON via API Resources
- 30+ posts, 5 categories, 15 tags seeded via factories
- Deployed somewhere public

## What's intentionally NOT in P2

- API authentication (Sanctum) — that's P3
- Policies for authorization — we'll do simple `abort_if` checks; Policies come in P3
- Background jobs / queues — Phase 5 (P4)
- Tests — Phase 6 (we revisit P2/P4 to add them)

## Why a blog and not something more original?

A blog naturally has every Phase 3 concept:
- One-to-many (User → Posts, Category → Posts)
- Many-to-many with pivot (Post ↔ Tag)
- One-to-many leading the other way (Post → Comments)
- Aggregates (count posts per tag, count comments per post)
- N+1 traps (post index showing author name = classic N+1)
- Public-facing pagination
- Content that benefits from `slug` URLs
- Easy to seed with realistic-looking data via Faker

You'd have to invent half of these in a custom domain. Don't fight the canon.

## Resume bullet you'll write at the end

> **Blog Platform** — Multi-author blog built in Laravel 13 with categories, tags, comments. Demonstrates Eloquent relationships (one-to-many, many-to-many, polymorphic precursor), eager loading, query builder aggregates, transactions, and API Resources. Seeded with 30+ posts via factories. github.com/YOU/blog · live: blog.example.com

## Next

Read `phase-3-eloquent/ch13-migrations.md`, then open `01-setup.md`.
