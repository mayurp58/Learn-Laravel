# How to Use This Course

## Mindset

You are NOT a beginner programmer. You are a beginner *Laravel* developer. Those are very different things. Your existing PHP, SQL, and MVC knowledge is an asset — but it can also be a trap, because Laravel's "magic" will sometimes feel uncomfortable until you understand *why* it exists.

Three rules:

1. **Never copy-paste without understanding.** If a code block has a `Route::get(...)` and you don't know what `Route` is, stop and find out.
2. **Always run the code.** Reading code is not learning code.
3. **When something feels magical, dig.** Use `php artisan route:list`, `dd()`, `var_dump()`, IDE "Go to definition", and read Laravel's source code on GitHub. Laravel is just PHP — there is no magic, only abstractions.

## Recommended Weekly Rhythm

Since you have no deadline, treat this as a marathon. A sustainable rhythm:

- **2 chapters/week** at minimum
- **1 hands-on task per chapter** — do it the same day
- **Saturday review:** re-read your notes from the week
- **End of phase:** do the interview questions and the mini-project

If you stick to that, you'll finish in ~6 months with deep, real knowledge. That's faster than most bootcamps and infinitely deeper.

## What to Do When You Get Stuck

In order:

1. Re-read the relevant section of the chapter.
2. Read the official Laravel docs page on the topic — they are excellent: https://laravel.com/docs
3. Run `php artisan` commands to inspect state (`route:list`, `tinker`, `config:show`).
4. Use `dd($var)` liberally. It's Laravel's "die and dump."
5. Search the error message (Laravel error pages are extremely helpful).
6. Ask your mentor (me) — paste the code and the error.

## Tools You Should Install

- **Laravel Herd** (free, optional but easier than MAMP for Laravel) — https://herd.laravel.com  
  *You can stay on MAMP if you prefer. Chapter 4 covers both.*
- **VS Code** or **PhpStorm** (PhpStorm is the gold standard for Laravel devs; free 30-day trial)
- **TablePlus** or **Sequel Ace** for browsing MySQL
- **Postman** or **Insomnia** for testing APIs (Phase 4)
- **Git** + a **GitHub** account — every project goes here for your portfolio

## A Note on AI Tools

You will be tempted to ask ChatGPT/Claude to write code for you. Don't — at least not in the first 5 phases. Type every line yourself. Once you understand the patterns, AI becomes a multiplier. Before then, it becomes a crutch that prevents real learning.

When you do start using AI, use it to:
- Explain code you're reading
- Code review your own code
- Generate boilerplate you already understand

Never use it to write code you don't understand.

Now go to `phase-1-foundations/ch01-composer-autoloading.md` and begin.
