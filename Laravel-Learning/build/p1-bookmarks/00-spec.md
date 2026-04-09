# P1 — Bookmark Manager (Spec)

**Spans:** Phase 2 (chapters 5–12)
**Final state:** A deployed personal bookmark manager you actually use. Single-user. Clean Blade UI. Login + CRUD + search. Hosted on Forge or Render.

## The pitch

Every developer has a Notes app or a Slack channel full of "I should bookmark this" links. We're going to build a tiny app that does it properly: log in, paste a URL, add a title and tags, search later. Boring on purpose — the goal is to *cement Phase 2 concepts* in real code, not invent features.

By the end of P1 you'll have used:

- Routing (`routes/web.php`)
- Controllers (resourceful + custom actions)
- Form Requests (validation)
- Blade layouts, components, partials, conditionals, loops
- Sessions (recently viewed list, flash messages)
- Middleware (auth gate)
- CSRF protection
- Eloquent (just enough — full depth comes in P2)
- Migrations (one table)

You will NOT use:
- Relationships (Phase 3)
- Queues, events, mail (Phase 5)
- Tests yet (Phase 6 — we revisit P1 briefly there)
- Livewire / Filament (Phase 7)

## Features (the whole list)

1. **Auth** — register, login, logout (we'll use Breeze in `ch08-build.md`)
2. **Bookmark CRUD** — create, list, edit, delete bookmarks
3. **Bookmark fields** — `url`, `title`, `description`, `tags` (comma-separated string for now — we'll properly normalize tags in P2)
4. **Validation** — URL must be valid, title required, description optional
5. **Search** — a search box on the index page that filters by title and description
6. **"Recently viewed"** — clicking a bookmark records it in the session, and the home page shows the last 5
7. **Flash messages** — "Bookmark saved", "Bookmark deleted" using session flash
8. **Middleware** — only logged-in users can see their own bookmarks; nobody can see anyone else's

## Data model

One table. That's the whole point of P1.

```
bookmarks
├── id              bigint pk
├── user_id         bigint fk → users
├── url             text
├── title           varchar(255)
├── description     text nullable
├── tags            varchar(255) nullable     -- e.g. "laravel,php,tutorial"
├── created_at      timestamp
└── updated_at      timestamp
```

## Routes you'll have by the end

```
GET    /                           home — recently viewed + welcome
GET    /bookmarks                  index — list all of mine, with search
GET    /bookmarks/create           form
POST   /bookmarks                  store
GET    /bookmarks/{bookmark}       show — also pushes to "recently viewed"
GET    /bookmarks/{bookmark}/edit  edit form
PUT    /bookmarks/{bookmark}       update
DELETE /bookmarks/{bookmark}       destroy

+ Breeze auth routes (login, register, logout, forgot password)
```

## Screen list

- **Home** (`/`) — welcome + recently viewed
- **Index** (`/bookmarks`) — table of bookmarks + search box
- **Create** (`/bookmarks/create`) — form
- **Show** (`/bookmarks/{id}`) — details
- **Edit** (`/bookmarks/{id}/edit`) — form
- **Login / Register** (Breeze defaults, lightly styled)

## What "done" looks like

When P1 is finished:

- Code pushed to a public GitHub repo named `bookmarks` (or `<yourname>-bookmarks`)
- README with screenshots and a one-paragraph "what this is"
- Deployed live at a URL you control (Forge subdomain or Render free tier — both fine)
- You can register a fresh account, add a bookmark, see it in the list, search for it, edit it, delete it, and the recently-viewed panel works
- All routes locked behind auth except `/` (home) and the Breeze login/register pages
- A demo account in the README so reviewers don't have to register

After that you'll do `99-finish.md` to deploy and retire P1, then move to P2 (Blog).

## Why bookmarks instead of "Todo"?

Three reasons:
1. **You'll actually use it.** A real personal tool is more motivating than a fake todo. After the course you keep using it — and "I built and use this myself" is a strong line in interviews.
2. **It exercises validation in a way todo doesn't** — URL validation is a real requirement, not contrived.
3. **The search feature gives Phase 2 a non-trivial controller method** without dragging Eloquent relationships in early.

## What you'll be able to say in an interview about P1

> "When I started learning Laravel I built a personal bookmark manager — single user, CRUD, search, deployed on Forge. It's small on purpose; the point was to internalize routing, controllers, form requests, and Blade. Repo is at github.com/me/bookmarks. I still use it every day."

That's a confident, concrete first project. It opens the door to talking about Phase 3 onwards.

## Next

Read `phase-2-core/ch05-request-lifecycle.md`, then come back to `01-setup.md` to scaffold the project.
