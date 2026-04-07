# Chapter 36 — Localization & Broadcasting (Brief)

## Localization

Translation files live in `lang/{locale}/`.

`lang/en/messages.php`:
```php
return [
    'welcome' => 'Welcome, :name!',
];
```

`lang/hi/messages.php`:
```php
return [
    'welcome' => 'स्वागत है, :name!',
];
```

Use:
```php
__('messages.welcome', ['name' => 'Asha']);
trans('messages.welcome', ['name' => 'Asha']);
```

In Blade:
```blade
{{ __('messages.welcome', ['name' => $user->name]) }}
```

Switch locale:
```php
app()->setLocale('hi');
```

Set per-request via middleware that reads `Accept-Language` or a user preference.

## Broadcasting (overview)

Broadcasting pushes events to the browser via WebSockets. Use cases: notifications, chat, live updates.

Stack: Laravel events + a broadcasting driver (Pusher, Reverb (official), Ably) + Laravel Echo (JS client).

This is a deep topic — when you need it, read the dedicated docs page. For now, just know it exists.

## Hands-on Task

Make your task manager bilingual (English + Hindi). Add a language switcher.

➡️ **End of Phase 5.** Move to Phase 6.
