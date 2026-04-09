# Chapter 9 — Requests, Validation, and Form Requests

Validation in Laravel is *vastly* better than CI's `form_validation` library. You'll be productive immediately.

## Quick inline validation

```php
public function store(Request $request)
{
    $validated = $request->validate([
        'title' => 'required|string|max:255',
        'body'  => 'required|string',
        'tags'  => 'array',
        'tags.*' => 'string|max:50',
        'email' => 'required|email|unique:users,email',
    ]);

    Post::create($validated);
}
```

If validation fails:
- For HTML forms → automatic redirect back with errors and old input.
- For JSON requests → automatic `422 Unprocessable Entity` response with structured errors.

You don't write the redirect or the response. Laravel does it.

## Showing errors in Blade

```blade
<input name="title" value="{{ old('title') }}">
@error('title')
    <div class="text-red-500">{{ $message }}</div>
@enderror
```

## Common rules (the must-knows)

```
required        sometimes        nullable
string          integer          numeric          boolean
array           json             date             url   email
min:5           max:255          between:1,100    size:10
in:a,b,c        not_in:x,y       regex:/^[A-Z]/
unique:users,email
exists:posts,id
confirmed       (matches password_confirmation)
same:other      different:other
file            image            mimes:pdf,docx   max:2048
```

The full list is in the docs — bookmark it.

## Custom messages

```php
$request->validate(
    ['title' => 'required'],
    ['title.required' => 'Please enter a title.'],
);
```

## Form Requests — the right way

For anything serious, move validation out of the controller into a Form Request class.

```bash
php artisan make:request StorePostRequest
```

`app/Http/Requests/StorePostRequest.php`:

```php
<?php
namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StorePostRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;   // any logged-in user can post
    }

    public function rules(): array
    {
        return [
            'title' => 'required|string|max:255',
            'body'  => 'required|string|min:10',
        ];
    }

    public function messages(): array
    {
        return [
            'title.required' => 'You forgot the title!',
        ];
    }
}
```

Then in the controller:

```php
public function store(StorePostRequest $request)
{
    Post::create($request->validated());
    return redirect()->route('posts.index');
}
```

That's it. The framework runs validation *before* your controller method even executes. If it fails, controller never runs.

## Custom rules

```bash
php artisan make:rule Uppercase
```

```php
public function validate(string $attribute, mixed $value, Closure $fail): void
{
    if (strtoupper($value) !== $value) {
        $fail("The {$attribute} must be uppercase.");
    }
}
```

Use:
```php
'code' => ['required', new Uppercase],
```

## CI comparison

CI's form_validation library required loading, manual config, and was clunky. Laravel's validation is concise, powerful, and integrates with redirects and JSON responses out of the box.

## Common Mistakes

1. **Validating in the controller for complex forms.** Move to Form Requests early.
2. **Forgetting `authorize(): bool` returns false by default in some templates** — you'll get 403s.
3. **Using `$request->all()` instead of `$request->validated()`** — opens you to mass assignment of fields you didn't validate.
4. **Forgetting `unique` rules ignore the current record on update:** use `unique:users,email,'.$user->id`.

## Hands-on Task

1. Create `StorePostRequest` and `UpdatePostRequest`.
2. Move validation out of `PostController@store` and `update`.
3. Try submitting an invalid form and confirm you see errors.

## Self-check

1. What's the difference between `$request->all()` and `$request->validated()`?
2. What does `authorize()` do in a Form Request?
3. How do you ignore the current record in a `unique` rule?

🔨 **Build it for real:** Apply this chapter to project P1 — see [`build/p1-bookmarks/ch09-build.md`](../build/p1-bookmarks/ch09-build.md).

➡️ Next: `ch10-responses.md`
