# Chapter 32 — Notifications

A unified API for sending messages over many channels: mail, database, Slack, SMS, broadcast.

## Creating

```bash
php artisan make:notification InvoicePaid
```

```php
class InvoicePaid extends Notification
{
    public function __construct(public Invoice $invoice) {}

    public function via(object $notifiable): array
    {
        return ['mail', 'database'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Invoice paid')
            ->greeting("Hi {$notifiable->name}!")
            ->line("Your invoice #{$this->invoice->id} has been paid.")
            ->action('View Invoice', route('invoices.show', $this->invoice))
            ->line('Thanks!');
    }

    public function toArray(object $notifiable): array
    {
        return [
            'invoice_id' => $this->invoice->id,
            'amount' => $this->invoice->total,
        ];
    }
}
```

## Sending

```php
$user->notify(new InvoicePaid($invoice));
Notification::send($users, new InvoicePaid($invoice));
```

## Database notifications

Add `Notifiable` trait (already on `User` by default). Run:

```bash
php artisan notifications:table
php artisan migrate
```

Read them:
```php
$user->notifications;
$user->unreadNotifications;
$notification->markAsRead();
```

## Hands-on Task

Build a `PostCommented` notification that emails the post author when someone comments on their post.

🔨 **Build it for real:** Apply this chapter to project P4 — see [`build/p4-projectly/ch32-build.md`](../build/p4-projectly/ch32-build.md).

➡️ Next: `ch33-mail.md`
