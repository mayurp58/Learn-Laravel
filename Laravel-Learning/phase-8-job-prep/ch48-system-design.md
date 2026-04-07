# Chapter 48 — System Design Basics for Laravel Devs

Senior interviews almost always include a system design round. You don't need to be a distributed-systems PhD — you need to think out loud about scaling, caching, queues, and database design.

## Common questions

1. "Design a URL shortener (like bit.ly)."
2. "Design a notifications system for a social network."
3. "How would you build a Twitter feed with Laravel?"
4. "Your app is slow. How do you find the bottleneck?"
5. "How would you handle 10x traffic?"

## A framework for answering

1. **Clarify requirements.** Read traffic? Write traffic? Realtime? How many users?
2. **Sketch the data model.** Tables, relationships, indexes.
3. **Sketch the request flow.** Routes → controllers → DB.
4. **Identify bottlenecks.** Slow queries? External APIs? Mail?
5. **Add caching where helpful.** Redis for hot data.
6. **Move slow work to queues.** Mail, PDF generation, third-party calls.
7. **Talk about scaling.** Read replicas, horizontal scaling, CDN, queue workers.
8. **Talk about monitoring.** Logs, Sentry, Horizon.

## Key Laravel concepts they probe

- **Eager loading & N+1** — almost always
- **Queues** — when, why, drivers
- **Cache strategies** — cache-aside, invalidation
- **Indexes** — composite, covering, when not to
- **Transactions and locking** — race conditions
- **Service container** — how DI works
- **Testing strategy** — what to feature-test vs unit-test

## Sample answer outline: "Twitter feed"

> "I'd start with three tables: `users`, `tweets`, `follows`. The feed query is 'show me tweets from people I follow, latest first.' That's `Tweet::whereIn('user_id', $followingIds)->latest()->paginate()`. With millions of tweets, I'd add an index on `(user_id, created_at)`. For a hot feed, I'd cache the first page in Redis, invalidated when the user follows someone new or when one of their followees posts. For very-popular celebrity accounts (the 'fan-out' problem), I'd use a hybrid: fan-out on write for normal users, fan-in on read for celebrities. Posting a tweet would dispatch a `FanOutTweet` job to a queue for async fan-out."

That answer touches: schema, queries, indexes, caching, queues, edge cases. Hire-worthy.

## Hands-on Task

Pick one of the questions above. Write a 1-page design doc in your notes. Bring it next session and we'll review.

➡️ Next: `ch49-final.md`
