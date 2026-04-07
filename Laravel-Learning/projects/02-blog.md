# Mini Project 2 — Blog Platform

**Build after Phase 3.** Goal: master Eloquent relationships, eager loading, API resources.

## Features

- Posts with categories and tags
- Comments on posts (one user, many comments)
- Likes (polymorphic — could be on posts or comments)
- Author profile pages
- Public list with pagination + filter by category/tag
- API endpoints (JSON) using API Resources
- Search posts by title (simple `LIKE`)

## Data Model

```
users
posts: id, user_id, category_id, title, slug, body, published_at, timestamps
categories: id, name, slug, timestamps
tags: id, name, slug, timestamps
post_tag (pivot): post_id, tag_id
comments: id, user_id, post_id, body, timestamps
likes: id, user_id, likeable_id, likeable_type, timestamps  (polymorphic)
```

## Relationships to define

- Post belongsTo User, belongsTo Category, belongsToMany Tag, hasMany Comment, morphMany Like
- User hasMany Post, hasMany Comment
- Category hasMany Post
- Tag belongsToMany Post
- Comment belongsTo User, belongsTo Post, morphMany Like

## Endpoints

Web:
- GET / — homepage with paginated posts
- GET /posts/{slug} — single post page
- GET /categories/{slug} — posts in category
- GET /tags/{slug} — posts with tag

API:
- GET /api/posts — list (paginated, eager-loaded)
- GET /api/posts/{slug} — show with author, category, tags, comments
- POST /api/posts/{post}/comments — auth required
- POST /api/posts/{post}/like — auth required

## Critical practice

- Use `with()` on every query that touches relations. Enable `Model::preventLazyLoading()` in dev.
- Use API Resources, never raw model JSON.
- Use a policy for comments and likes.
- Write at least 5 feature tests.

## Bonus

- Markdown rendering for post bodies (`league/commonmark`)
- Livewire search-as-you-type
- View counter incremented in middleware

## What you'll have learned

Migrations, factories, seeders, all relationship types, eager loading, polymorphism, API resources, pagination, scopes, simple full-text search, polices.
