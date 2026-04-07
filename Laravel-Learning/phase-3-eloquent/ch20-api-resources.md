# Chapter 20 — API Resources

When building APIs, you almost never want to return your raw Eloquent model. You'll leak fields you didn't mean to (`password_hash`, `internal_notes`), and you'll lock your DB schema to your API contract. **API Resources** solve both.

## Creating a resource

```bash
php artisan make:resource PostResource
php artisan make:resource PostCollection
```

```php
class PostResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id' => $this->id,
            'title' => $this->title,
            'excerpt' => str($this->body)->limit(120),
            'author' => $this->whenLoaded('user', fn() => [
                'id' => $this->user->id,
                'name' => $this->user->name,
            ]),
            'published_at' => $this->published_at?->toIso8601String(),
        ];
    }
}
```

## Using

```php
public function show(Post $post)
{
    return new PostResource($post->load('user'));
}

public function index()
{
    return PostResource::collection(Post::with('user')->paginate(15));
}
```

The pagination wrapper auto-includes `meta` and `links`.

## Conditional fields

```php
'admin_notes' => $this->when($request->user()?->isAdmin(), $this->admin_notes),
'comments_count' => $this->whenCounted('comments'),
```

## Common Mistakes

1. **Returning raw models from APIs.** Use Resources.
2. **N+1 in resources.** If you reference `$this->user`, eager-load `with('user')` in the controller.

## Hands-on Task

Create a `PostResource` and a `UserResource` that nests posts. Test with Postman.

➡️ **End of Phase 3.** Build **Mini Project 2: Blog Platform** (`projects/02-blog.md`). Then Phase 4.
