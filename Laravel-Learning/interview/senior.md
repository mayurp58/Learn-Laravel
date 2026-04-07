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

These are open-ended on purpose. The goal is to *talk* through trade-offs, not give a single right answer. Practice with a friend or rubber duck.
