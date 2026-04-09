# P4 · Chapter 36 — Apply: Localization

**Read first:** `phase-5-advanced/ch36-localization.md`

## What you're building this chapter

Localize the Projectly UI into English + Spanish (or another language you actually read). This is a senior-level chore that earns you the right to put "i18n / l10n" on your resume.

## Step 1 — Set up locale config

`config/app.php`:
```php
'locale'          => 'en',
'fallback_locale' => 'en',
```

These are usually already set.

## Step 2 — Create translation files

```bash
mkdir -p lang/en lang/es
```

`lang/en/projectly.php`:
```php
<?php

return [
    'dashboard'        => 'Dashboard',
    'projects'         => 'Projects',
    'tasks'            => 'Tasks',
    'tasks_done'       => 'Tasks done',
    'members'          => 'Members',
    'recent_activity'  => 'Recent activity',
    'new_project'      => 'New project',
    'new_task'         => 'New task',
    'task_assigned'    => 'You\'ve been assigned to :task',
    'team_invitation_subject' => 'You\'re invited to join :team',
];
```

`lang/es/projectly.php`:
```php
<?php

return [
    'dashboard'        => 'Panel',
    'projects'         => 'Proyectos',
    'tasks'            => 'Tareas',
    'tasks_done'       => 'Tareas completadas',
    'members'          => 'Miembros',
    'recent_activity'  => 'Actividad reciente',
    'new_project'      => 'Nuevo proyecto',
    'new_task'         => 'Nueva tarea',
    'task_assigned'    => 'Se te ha asignado :task',
    'team_invitation_subject' => 'Te han invitado a unirte a :team',
];
```

## Step 3 — Use them in views

In `resources/views/dashboard.blade.php`, replace hard-coded labels:

```blade
<h1 class="text-2xl font-bold mb-6">{{ __('projectly.dashboard') }}</h1>

<p class="text-xs text-gray-500">{{ __('projectly.projects') }}</p>
```

`__('key')` is the localization helper. Sprintf-style placeholders use `:name`:

```blade
{{ __('projectly.task_assigned', ['task' => $task->title]) }}
```

## Step 4 — Locale switcher

Add to your nav component:

```blade
<form method="POST" action="{{ route('locale.set') }}" class="inline">
    @csrf
    <select name="locale" onchange="this.form.submit()">
        <option value="en" @selected(app()->getLocale() === 'en')>English</option>
        <option value="es" @selected(app()->getLocale() === 'es')>Español</option>
    </select>
</form>
```

Route + controller:

```php
Route::post('/locale', function (Request $request) {
    $locale = $request->validate(['locale' => 'in:en,es'])['locale'];
    session(['locale' => $locale]);
    return back();
})->name('locale.set');
```

## Step 5 — Apply locale per request via middleware

```bash
php artisan make:middleware SetLocale
```

```php
public function handle(Request $request, Closure $next)
{
    if ($locale = $request->session()->get('locale')) {
        app()->setLocale($locale);
    }
    return $next($request);
}
```

Register in `bootstrap/app.php`:
```php
->withMiddleware(function (Middleware $middleware) {
    $middleware->web(append: [
        \App\Http\Middleware\SetLocale::class,
    ]);
})
```

## Step 6 — Try it

Visit `/dashboard`. Switch the locale dropdown to Spanish. The labels should change.

## Step 7 — Localize the invitation mail subject

Edit `TeamInvitationMail::envelope()`:
```php
return new Envelope(
    subject: __('projectly.team_invitation_subject', ['team' => $this->invitation->team->name]),
);
```

## Verify it works

- ✅ Locale switcher persists across requests (session)
- ✅ All `__('projectly....')` labels translate
- ✅ Mail subject reflects current locale
- ✅ Falling back to English if a key is missing in `es`

## Commit

```bash
git add .
git commit -m "feat: i18n support (English + Spanish)"
```

## What's next

➡️ `ch37-build.md` — start writing Pest tests for everything we've built.
