# Interview Questions — Senior / System Design (After Phases 6–8)

1. **Walk me through how Laravel's request lifecycle works in detail. What happens between `index.php` and your controller?**
2. **How does Laravel's Service Container actually resolve dependencies? Show contextual binding.**
3. **Explain the difference between deferred service providers and regular providers.**
4. **How would you optimize an Eloquent query that's hitting the DB 50 times per request?**
5. **You have a `users` table with 10 million rows. How do you iterate over all of them safely? What about updating a column on each?**
6. **Design indexes for a `posts` table that's queried by `(user_id, created_at)` and `(category_id, published_at)`. What about a covering index?**
7. **How does Laravel handle race conditions on, say, decrementing a stock counter? Show the code.**
8. **Design a notifications system that supports email, SMS, and in-app, queued, with retries. Walk through the classes.**
9. **You're seeing a slow page. Walk me through how you'd diagnose it.**
10. **How do you handle background jobs that take 10+ minutes?**
11. **How do you prevent a queue worker from getting stuck on a single bad job?**
12. **Explain how you'd structure a multi-tenant SaaS in Laravel. Single DB vs multi-DB. Trade-offs.**
13. **How do you test a job that calls an external API?**
14. **What does `Mail::fake()` do under the hood?**
15. **Explain Laravel's broadcasting. How do WebSockets fit in?**
16. **You need to add a new column to a 50M-row production table. How?**
17. **A teammate writes `Post::all()->filter(fn($p) => $p->published)`. What's wrong, and how would you fix it?**
18. **Difference between `pluck` and `select` in terms of queries and memory?**
19. **How does Laravel's session driver affect horizontal scaling?**
20. **You see a `Whoops, looks like something went wrong` page in production. What does that tell you, and how do you fix it immediately?**
21. **Explain how Sanctum's SPA mode works versus token mode.**
22. **How do you handle file uploads larger than 100 MB?**
23. **Walk me through deploying a Laravel app with zero downtime.**
24. **How do you secure a Laravel API against abuse?**
25. **Design a Twitter-like timeline. Start with the schema and end with the cache strategy.**

### Laravel 13–specific (added 2026)

26. **What changed in the CSRF middleware in Laravel 13, and why is the new origin verification useful?** (Answer should mention the rename to `PreventRequestForgery` and the `Sec-Fetch-Site` check as defence-in-depth on top of token validation.)
27. **Walk through the Laravel AI SDK. How would you build a "summarize this document" feature end-to-end — controller, queue, storage, testing?**
28. **What problem does `Queue::route()` solve compared to calling `->onQueue()` at every dispatch site?** (Centralized routing, single source of truth for infra topology, easier to change.)
29. **When would you use first-party JSON:API Resources vs. plain `JsonResource`?** (JSON:API for spec-compliant clients / public APIs; plain for internal/mobile.)
30. **Explain how `whereVectorSimilarTo()` works under the hood. What infrastructure do you need, and what's the difference between cosine and L2 similarity?**
31. **You're upgrading a production app from L12 to L13. Walk through the breaking-change checklist.** (CSRF rename, `serializable_classes` cache config, `JobAttempted` event, `Container::call()` nullable defaults, cache prefix / session cookie naming, Pest 4 / PHPUnit 12 deps, PHP 8.3 floor.)
32. **What's the trade-off of putting middleware in PHP attributes vs. in route definitions?** (Locality vs. discoverability — attributes keep rules next to actions but hide them from `route:list` readers.)

These are open-ended on purpose. The goal is to *talk* through trade-offs, not give a single right answer. Practice with a friend or rubber duck.
