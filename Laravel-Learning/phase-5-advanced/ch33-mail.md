# Chapter 33 — Mail

## Configuration

In `.env`:
```env
MAIL_MAILER=smtp
MAIL_HOST=smtp.mailtrap.io     # great for development
MAIL_PORT=2525
MAIL_USERNAME=...
MAIL_PASSWORD=...
MAIL_FROM_ADDRESS=hello@example.com
MAIL_FROM_NAME="${APP_NAME}"
```

For local testing, use **Mailtrap** (free) or **Mailpit** (runs locally).

## Mailables

```bash
php artisan make:mail WelcomeMail --markdown=emails.welcome
```

```php
class WelcomeMail extends Mailable
{
    public function __construct(public User $user) {}

    public function envelope(): Envelope
    {
        return new Envelope(subject: 'Welcome!');
    }

    public function content(): Content
    {
        return new Content(markdown: 'emails.welcome');
    }
}
```

`resources/views/emails/welcome.blade.php`:
```blade
<x-mail::message>
# Hello {{ $user->name }}

Welcome to our app!

<x-mail::button :url="url('/dashboard')">
View Dashboard
</x-mail::button>

Thanks,
{{ config('app.name') }}
</x-mail::message>
```

## Sending

```php
Mail::to($user->email)->send(new WelcomeMail($user));
Mail::to($user)->cc('cc@x.com')->bcc('bcc@x.com')->send(new WelcomeMail($user));
```

## Queue mail (highly recommended)

```php
Mail::to($user)->queue(new WelcomeMail($user));
```

Or have the mailable implement `ShouldQueue`.

## Hands-on Task

Build a `WelcomeMail` and a `PasswordResetMail`. Use Mailtrap or Mailpit. Verify both render correctly.

➡️ Next: `ch34-storage.md`
