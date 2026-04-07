# Interview Questions — Basic (After Phases 1–2)

Try answering each in your own words *before* checking the hint. Speak out loud — practice the verbalization.

---

1. **What is Laravel? Who maintains it?**
2. **What's the difference between `composer install` and `composer update`?**
3. **What is PSR-4 autoloading?**
4. **Explain the request lifecycle in Laravel.**
5. **What's the difference between `routes/web.php` and `routes/api.php`?**
6. **How do you create a controller? What's a resource controller?**
7. **What's an invokable controller?**
8. **What is route model binding? Show an example.**
9. **What is middleware? Give 3 examples of what it's used for.**
10. **What's the difference between `Route::get` and `Route::post`?**
11. **What is CSRF? How does Laravel protect against it?**
12. **How do you validate a form in Laravel?**
13. **What's a Form Request?**
14. **What's the difference between `$request->all()` and `$request->validated()`?**
15. **How do you redirect with a flash message?**
16. **What is Blade?**
17. **Difference between `{{ $var }}` and `{!! $var !!}`?**
18. **What does `@csrf` do?**
19. **What does `@method('PUT')` do?**
20. **What is `.env` for? Should you commit it?**
21. **What does `php artisan tinker` do?**
22. **How do you run Laravel locally without Apache/Nginx?**
23. **Where do session files live by default?**
24. **What's the difference between `session()->put()` and `session()->flash()`?**
25. **What is Artisan?**

---

### Hints (don't read until you've tried)

1. PHP MVC framework, by Taylor Otwell.
2. `install` respects `composer.lock`; `update` ignores it and pulls latest allowed versions.
3. Standard mapping fully-qualified class names to file paths (`App\Models\User` → `app/Models/User.php`).
4. See Chapter 5.
5. `web` has session/cookies/CSRF; `api` is stateless.
6. `php artisan make:controller X --resource`. Resource = 7 RESTful methods.
7. A controller with only an `__invoke` method, called as `Controller::class` directly.
8. `Route::get('/posts/{post}', ...)` with `Post $post` type-hint auto-fetches by id.
9. Auth checks, logging, CORS, throttling, headers.
10. HTTP verb. Get reads, post creates.
11. Cross-Site Request Forgery. Token in form (`@csrf`) validated on submit.
12. `$request->validate([...])` or Form Request.
13. Class extending `FormRequest`, holds rules and authorization for a single endpoint.
14. `all()` returns everything (unsafe for mass assignment); `validated()` returns only validated fields.
15. `redirect()->route('x')->with('success', 'Saved!')`.
16. Laravel's templating engine.
17. `{{ }}` escapes HTML; `{!! !!}` doesn't (XSS risk).
18. Outputs a hidden CSRF token field.
19. Spoofs HTTP verb (browsers can't send PUT/PATCH/DELETE in HTML forms).
20. Environment-specific config and secrets. Never commit it.
21. Interactive REPL — eval Laravel code from terminal.
22. `php artisan serve`.
23. `storage/framework/sessions` (with file driver).
24. `put` persists; `flash` lasts only one request.
25. Laravel's CLI tool for code generation and management.
