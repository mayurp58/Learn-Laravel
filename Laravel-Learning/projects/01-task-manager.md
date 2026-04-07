# Mini Project 1 — Personal Task Manager

**Build after Phase 2.** Goal: practice routing, controllers, validation, Blade, and Eloquent basics in one cohesive app.

## Features

- Register / login (use Breeze)
- Each user sees only their own tasks
- Create / edit / delete tasks
- Mark task complete / incomplete
- Filter: all / pending / completed
- Validation with Form Requests
- Flash messages on actions

## Data Model

```
users (from Breeze)
tasks
├── id
├── user_id (FK)
├── title (required, max 255)
├── description (nullable, text)
├── due_date (nullable, date)
├── completed_at (nullable, timestamp)
└── timestamps
```

## Steps

1. `composer create-project laravel/laravel task-manager`
2. Install Breeze: `composer require laravel/breeze --dev && php artisan breeze:install blade`
3. `php artisan make:model Task -mcr`
4. Define migration columns. Migrate.
5. Define `Task` model: `$fillable`, `belongsTo(User::class)`, scope `pending()` and `completed()`
6. Add `tasks()` relationship on `User`
7. Routes: `Route::resource('tasks', TaskController::class)->middleware('auth')`
8. Build all 7 controller methods. Use route model binding. Use a `TaskPolicy` so users can only touch their own tasks.
9. Make `StoreTaskRequest` and `UpdateTaskRequest`.
10. Build Blade views: `tasks/index.blade.php`, `create.blade.php`, `edit.blade.php`. Use the layout from Breeze.
11. Add a "Mark complete" button (POST to a `complete` route that updates `completed_at`).
12. Add filter buttons (`?filter=pending`).
13. Push to GitHub. Write a README.

## Bonus

- Add tags (many-to-many)
- Pagination
- Search by title

## What you'll have learned

Routing, controllers, resource controllers, route model binding, validation, form requests, Blade layouts, Eloquent CRUD, scopes, relationships, policies. ~80% of day-to-day Laravel work.
