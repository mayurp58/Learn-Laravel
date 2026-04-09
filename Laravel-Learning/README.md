# Laravel Mastery — From CodeIgniter to Senior Laravel Developer

A complete, text-based, mentor-style learning path designed for an experienced PHP/CodeIgniter developer who wants to master Laravel and switch to a better job.

> **Author note:** This material assumes you already understand HTTP, MVC, basic SQL, sessions, forms, and have written PHP for years. We will NOT re-teach those. We will focus on what is *different* in Laravel and modern PHP, and we will go deep where it matters.

---

## How to Use This Material

This roadmap has **two parallel tracks** that you follow together:

- **Teaching track** (`phase-1-foundations/` → `phase-7-ecosystem/`) — concepts, syntax, isolated examples, hands-on micro-tasks. Read these first.
- **Build track** (`build/`) — apply each chapter to a real, deployable project. Four projects span the whole roadmap. **By Phase 8 you'll have four deployed apps on GitHub.**

For every teaching chapter that has a build counterpart, you'll see a 🔨 **Build it for real** pointer at the bottom — follow it to apply the chapter to your project before moving on.

1. **Read chapters in order.** Each chapter builds on the previous one.
2. **Do every "Hands-on Task"** at the end of each chapter. Reading without coding is wasted time.
3. **Follow the 🔨 Build it for real pointer** at the end of each chapter — this is where the real learning happens.
4. **Keep a personal notes file** as you go. Re-explaining things in your own words is the single highest-ROI study habit.
5. **Don't skip the "Common Mistakes" sections** — they are the things interviewers love to test.
6. **At the end of each Phase**, do the interview-question set in `interview/`.

**Start here:** read `00-how-to-use.md` for the daily rhythm, then `build/README.md` to understand the four projects, then `build/ch00-prerequisites.md` to set up your tools.

---

## Roadmap Overview

| Phase | Chapters | Focus | Build-track project |
|---|---|---|---|
| 1. Foundations | 1–4 | Composer, modern PHP, OOP/DI, Laravel setup | (no project yet) |
| 2. Laravel Core | 5–12 | Routing, controllers, middleware, validation, Blade | **P1 — Bookmarks** (`build/p1-bookmarks/`) |
| 3. Eloquent & DB | 13–20 | Migrations, Eloquent, relationships, API resources | **P2 — Blog** (`build/p2-blog/`) |
| 4. Auth & APIs | 21–25 | Breeze, Sanctum, policies, REST APIs | **P3 — Blog API** (extends P2; `build/p3-blog-api/`) |
| 5. Advanced | 26–36 | Container, providers, queues, events, cache, mail | **P4 — Projectly** starts (`build/p4-projectly/`) |
| 6. Testing & Deploy | 37–41 | Pest, feature tests, deployment | **P4 — Projectly** continues |
| 7. Ecosystem | 42–44b | Livewire, Inertia, Filament, AI SDK (L13) | **P4 — Projectly** finishes |
| 8. Job Prep | 45–49 | Resume, portfolio, interviews, negotiation | (use all 4 projects as portfolio) |

---

## Folder Map

```
Laravel-Learning/
├── README.md                  ← you are here
├── 00-how-to-use.md           ← study habits and rhythm
├── phase-1-foundations/       ← Composer, modern PHP, OOP, setup
├── phase-2-core/              ← Laravel core building blocks
├── phase-3-eloquent/          ← Database mastery
├── phase-4-auth-api/          ← Auth and APIs
├── phase-5-advanced/          ← Container, queues, events, etc.
├── phase-6-testing-deploy/    ← Tests and deployment
├── phase-7-ecosystem/         ← Livewire, Inertia, Filament
├── phase-8-job-prep/          ← Resume + interview prep
├── build/                     ← BUILD TRACK: 4 projects, one build file per chapter
├── projects/                  ← Original 3 mini-project specs (kept for reference)
├── interview/                 ← Question banks per level
└── resources/                 ← Cheat sheets and references
```

---

## Your Setup (already confirmed)

- macOS, MAMP with PHP 8.4 + MySQL ✓
- Goal: full-stack Laravel
- No deadline — depth over speed
- Starting point: zero Laravel, light on modern PHP

We will use **MAMP's PHP** for command-line work and **Composer** to install Laravel. Chapter 4 walks through this in detail.

Start with `00-how-to-use.md`, then go to `phase-1-foundations/ch01-composer-autoloading.md`.

Good luck. Let's make you a Laravel developer.

---

## Target Versions (Laravel 13)

This material targets **Laravel 13** (released March 17, 2026). Key dependency floors:

| Package            | Version |
|--------------------|---------|
| `php`              | 8.3 – 8.5 |
| `laravel/framework`| `^13.0` |
| `laravel/tinker`   | `^3.0`  |
| `pestphp/pest`     | `^4.0`  |
| `phpunit/phpunit`  | `^12.0` |

L13 is a small upgrade from L12 (no skeleton overhaul). The main breaking change to be aware of: the CSRF middleware was renamed from `VerifyCsrfToken` to `PreventRequestForgery` and is now origin-aware via `Sec-Fetch-Site`. See `phase-2-core/ch12-sessions.md` for details, or `resources/laravel-13-upgrade.md` for a complete L12 → L13 upgrade cheat sheet.
