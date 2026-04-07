# Chapter 47 — Interview Strategy

Interview questions are in `interview/` (separate files for basic, intermediate, senior). This chapter is about *strategy*.

## Types of Laravel interviews

1. **Verbal/conceptual** — "Explain the request lifecycle. What's a service container?"
2. **Live coding** — "Build a CRUD endpoint with auth. 45 minutes. Share screen."
3. **Take-home** — "Build a small app, push to GitHub, walk us through."
4. **System design** — "Design Twitter. How would you scale Eloquent queries?"
5. **Code review** — "Here's some code. What's wrong with it?"

## How to prepare

### Daily for 2 weeks before applying:
- 5 interview questions from the bank
- 1 small coding warm-up (Eloquent query, route, validation)
- 30 min of reading the official Laravel docs

### Weekly:
- Mock interview with a friend (or me — paste a question, I'll grill you)
- Build one small thing from scratch *without copy-paste*

## Live coding tips

1. **Talk while you code.** Silent candidates lose points.
2. **Start with the route.** `Route::post('/x', ...)`. Then the controller skeleton. Then the validation. Then the DB. Then the response. **Build incrementally.**
3. **Use `dd()` and `tinker` openly.** Showing your debugging process is a strength.
4. **Don't pretend.** "I don't remember the exact syntax but conceptually..." is a great answer.
5. **Ask clarifying questions.** "Should this paginate?" "Should anonymous users see this?"

## Take-home tips

1. **Spend half the time on tests, README, and polish.** Most candidates don't.
2. **One feature done well > five features half-done.**
3. **Use the technologies you're comfortable with.** Don't try Inertia for the first time on a take-home.
4. **Commit history matters.** Don't squash to one commit.

## Salary negotiation (one paragraph)

Always counter. Even a 5–10% bump on the first offer is normal. When asked your expectations early, deflect: "I'd like to learn more about the role first. What's the band for this position?" If you must give a number, give a range based on local market data — Glassdoor, Levels.fyi, AmbitionBox (India).

## Hands-on Task

Open `interview/basic.md`. Answer every question out loud, in your own words. Do not look up answers first. Then check.

➡️ Next: `ch48-system-design.md`
