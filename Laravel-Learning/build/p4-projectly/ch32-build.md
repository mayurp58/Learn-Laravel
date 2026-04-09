# P4 · Chapter 32 — Apply: Notifications

**Read first:** `phase-5-advanced/ch32-notifications.md`

## What you're building this chapter

When user A assigns a task to user B, B gets two notifications: a row in the `notifications` table (in-app bell) and a queued email. Both via Laravel's `Notification` system, multi-channel.

## Step 1 — The notifications table (Laravel default)

```bash
php artisan notifications:table
php artisan migrate
```

This creates a `notifications` table that the `Notifiable` trait uses.

`app/Models/User.php` already uses `Notifiable` from Breeze — confirm.

## Step 2 — Create the notification class

```bash
php artisan make:notification TaskAssigned
```

`app/Notifications/TaskAssigned.php`:

```php
<?php

namespace App\Notifications;

use App\Models\Task;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class TaskAssigned extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(public Task $task) {}

    public function via(object $notifiable): array
    {
        return ['mail', 'database'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject("New task assigned: {$this->task->title}")
            ->line("You've been assigned to a new task in {$this->task->project->name}.")
            ->line("Title: {$this->task->title}")
            ->action('View task', url("/projects/{$this->task->project_id}/tasks/{$this->task->id}"))
            ->line('Get to it.');
    }

    public function toArray(object $notifiable): array
    {
        return [
            'task_id'      => $this->task->id,
            'task_title'   => $this->task->title,
            'project_name' => $this->task->project->name,
        ];
    }
}
```

> `implements ShouldQueue` makes the notification dispatch to a queue automatically — the user's request returns instantly even though sending mail is slow.

## Step 3 — Fire the notification on task assignment

In `Task.php`, add a model event:

```php
protected static function booted(): void
{
    static::saved(function (Task $task) {
        if ($task->wasChanged('assignee_id') && $task->assignee_id) {
            $task->assignee->notify(new \App\Notifications\TaskAssigned($task));
        }
    });
}
```

`wasChanged('assignee_id')` only fires if the assignee actually changed (so re-saving without changing it doesn't spam notifications).

## Step 4 — Try it

```bash
php artisan tinker
```
```php
$task = \App\Models\Task::first();
$user = \App\Models\User::first();
$task->update(['assignee_id' => $user->id]);
```

Then:
```bash
php artisan queue:work
```

Watch it process the notification job. Check the database:
```bash
psql projectly -c "SELECT * FROM notifications;"
```

A row should exist with `type = App\Notifications\TaskAssigned` and `data` containing the task info.

Check `storage/logs/laravel.log` — the email body (because `MAIL_MAILER=log` in dev).

## Step 5 — Display unread count

In a Blade view (we'll wire it properly in `ch42-build.md` for Livewire; for now just a tinker check):

```php
$user->unreadNotifications->count();   // 1
$user->unreadNotifications()->markAsRead();
$user->unreadNotifications->count();   // 0
```

## Verify it works

- ✅ Reassigning a task creates a notifications row + mail log entry
- ✅ The notification is queued (not sent inline)
- ✅ Re-saving a task without changing assignee doesn't trigger a new notification
- ✅ `unreadNotifications` and `markAsRead` work

## Commit

```bash
git add .
git commit -m "feat: TaskAssigned multi-channel notification"
```

## What's next

➡️ `ch33-build.md` — Mail: a real Mailable for team invitations.
