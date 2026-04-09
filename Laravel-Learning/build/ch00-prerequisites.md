# Build Track — Prerequisites

Before you start building anything, get the following tools installed and accounts set up. Doing this once now saves you from interruptions later.

## 1. Local development environment

You should already have these from `phase-1-foundations/ch04-laravel-setup.md`:

- **PHP 8.3 or newer** (`php -v` should show 8.3, 8.4, or 8.5)
- **Composer 2.x** (`composer --version`)
- **Git** (`git --version`)
- **A code editor** — VS Code with the "PHP Intelephense" and "Laravel Blade" extensions is the easy default. PhpStorm if you have a license.

## 2. Database

The whole course uses **PostgreSQL 16**. Install it once now.

### macOS
```bash
brew install postgresql@16
brew services start postgresql@16
```

Verify:
```bash
psql --version          # should show 16.x
psql postgres -c "SELECT version();"
```

### Why PostgreSQL and not MySQL?

Three reasons:
1. P4 (Projectly) uses the Laravel 13 **vector search** feature for AI-powered standups, and that requires the `pgvector` extension — PostgreSQL only.
2. Postgres has stricter SQL semantics than MySQL, which catches sloppy queries earlier (good for learning).
3. Most modern Laravel SaaS shops have moved to Postgres. Knowing it is a small but real hiring edge over CodeIgniter devs who only know MySQL.

You can do P1, P2, and P3 in MySQL if you really want to — but P4 will require Postgres anyway, so it's easier to use it from the start.

### pgvector extension (you'll need this for P4, but install it now)

```bash
brew install pgvector
```

Then in `psql postgres`:
```sql
CREATE EXTENSION IF NOT EXISTS vector;
```

## 3. Node.js (for Tailwind / Vite)

Laravel uses Vite for frontend assets. You need Node 20+:

```bash
brew install node
node -v          # should show v20 or higher
npm -v
```

## 4. GitHub account + SSH key

Every project goes on GitHub. If you don't have an account, create one — and use a username you'd put on a resume (`mayur-cities`, not `xXdragonSlayerXx`).

Set up an SSH key:
```bash
ssh-keygen -t ed25519 -C "your.email@example.com"
cat ~/.ssh/id_ed25519.pub      # copy this output
```

Paste it into GitHub → Settings → SSH and GPG keys → New SSH key. Test:
```bash
ssh -T git@github.com
```

You should see `Hi mayur-cities! You've successfully authenticated...`

## 5. Deployment account (you'll use this in Phase 6, but sign up now)

Pick **one**:

- **Laravel Forge** ($12/mo) + **DigitalOcean** ($6/mo droplet) — the standard, friction-free path. Recommended.
- **Render.com** — free tier exists, slower for PHP, but $0 to start.
- **Plain DigitalOcean droplet + manual setup** — cheapest, most learning, most pain. Skip unless you want sysadmin practice.

You don't need to actually provision the server yet. Just have the account ready.

## 6. AI provider key (only needed for P4)

In P4 you'll use the Laravel 13 AI SDK. Pick one:

- **Anthropic** (Claude) — `console.anthropic.com`. ~$5 free credit at signup.
- **OpenAI** — `platform.openai.com`. Pay-as-you-go from the start.

You don't need this until you reach `build/p4-projectly/ch44b-build.md`. Bookmark the signup pages now so you're not blocked when you get there.

## 7. A scratch directory

Pick a folder where all four projects will live. I'll assume `~/Sites/` from here on:

```bash
mkdir -p ~/Sites
cd ~/Sites
```

Each project gets its own subfolder: `~/Sites/bookmarks`, `~/Sites/blog`, `~/Sites/projectly`. (P3 extends P2, so it lives in `~/Sites/blog` — no separate folder.)

## Verify everything

Run this checklist before moving on:

```bash
php -v                                    # 8.3+
composer --version                        # 2.x
node -v                                   # 20+
psql --version                            # 16+
git --version
ssh -T git@github.com                     # auth success
psql postgres -c "SELECT extname FROM pg_extension;"   # should include 'vector' (after CREATE EXTENSION)
```

If any line fails, fix it now. You're not allowed to start P1 with a broken environment.

## What's next

Finish reading `phase-1-foundations/` (chapters 1–4) — these are pure concepts and don't have build files yet.

When you start `phase-2-core/ch05-request-lifecycle.md`, come back here and open `build/p1-bookmarks/00-spec.md` to see what you'll be building.
