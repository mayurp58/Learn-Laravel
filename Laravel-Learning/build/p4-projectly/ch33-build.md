# P4 · Chapter 33 — Apply: Mailables for team invitations

**Read first:** `phase-5-advanced/ch33-mail.md`

## What you're building this chapter

A team owner can invite someone by email. The system creates an invitation row, fires a `TeamInvitation` Mailable (queued), and the recipient clicks a signed URL to accept.

## Step 1 — Mailpit for local mail

```bash
brew install mailpit
brew services start mailpit
```

In `.env`:
```env
MAIL_MAILER=smtp
MAIL_HOST=127.0.0.1
MAIL_PORT=1025
MAIL_FROM_ADDRESS=hello@projectly.local
MAIL_FROM_NAME="${APP_NAME}"
```

Mailpit's web UI is at http://localhost:8025. All mail in dev shows up there — you can click into messages and see the rendered HTML.

## Step 2 — TeamInvitation migration + model

```bash
php artisan make:model TeamInvitation -m
```

```php
Schema::create('team_invitations', function (Blueprint $table) {
    $table->id();
    $table->foreignId('team_id')->constrained()->cascadeOnDelete();
    $table->string('email');
    $table->string('token', 64)->unique();
    $table->timestamp('expires_at');
    $table->timestamp('accepted_at')->nullable();
    $table->timestamps();
});
```

```php
// TeamInvitation.php
protected $fillable = ['team_id', 'email', 'token', 'expires_at'];
protected $casts = ['expires_at' => 'datetime', 'accepted_at' => 'datetime'];

public function team() { return $this->belongsTo(Team::class); }

public function isExpired(): bool
{
    return $this->expires_at->isPast();
}
```

```bash
php artisan migrate
```

## Step 3 — Generate the Mailable

```bash
php artisan make:mail TeamInvitationMail --markdown=mail.team-invitation
```

`app/Mail/TeamInvitationMail.php`:

```php
<?php

namespace App\Mail;

use App\Models\TeamInvitation;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class TeamInvitationMail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public function __construct(public TeamInvitation $invitation) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: "You're invited to join {$this->invitation->team->name}",
        );
    }

    public function content(): Content
    {
        return new Content(
            markdown: 'mail.team-invitation',
            with: [
                'team' => $this->invitation->team,
                'url'  => url("/invitations/{$this->invitation->token}"),
            ],
        );
    }
}
```

`resources/views/mail/team-invitation.blade.php`:

```blade
<x-mail::message>
# You're invited

You've been invited to join the **{{ $team->name }}** team on {{ config('app.name') }}.

<x-mail::button :url="$url">
Accept invitation
</x-mail::button>

This link expires in 7 days.

Thanks,<br>
{{ config('app.name') }}
</x-mail::message>
```

## Step 4 — Invitation controller

```bash
php artisan make:controller TeamInvitationController
```

```php
<?php

namespace App\Http\Controllers;

use App\Facades\CurrentTeam;
use App\Mail\TeamInvitationMail;
use App\Models\TeamInvitation;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;

class TeamInvitationController extends Controller
{
    public function store(Request $request)
    {
        $data = $request->validate(['email' => ['required', 'email']]);

        $invitation = TeamInvitation::create([
            'team_id'    => CurrentTeam::get()->id,
            'email'      => $data['email'],
            'token'      => Str::random(64),
            'expires_at' => now()->addDays(7),
        ]);

        Mail::to($data['email'])->send(new TeamInvitationMail($invitation));

        return back()->with('status', "Invitation sent to {$data['email']}.");
    }

    public function accept(string $token, Request $request)
    {
        $invitation = TeamInvitation::where('token', $token)->firstOrFail();

        abort_if($invitation->isExpired(), 410, 'Invitation expired.');
        abort_if($invitation->accepted_at, 410, 'Already accepted.');

        // Assume the recipient is logged in (in production: redirect to register if not)
        abort_unless($request->user(), 401);

        $invitation->team->members()->syncWithoutDetaching([
            $request->user()->id => ['role' => 'member'],
        ]);

        $invitation->update(['accepted_at' => now()]);

        return redirect('/dashboard')->with('status', "Joined {$invitation->team->name}.");
    }
}
```

## Step 5 — Routes

```php
Route::middleware('auth')->post('/invitations', [TeamInvitationController::class, 'store'])->name('invitations.store');
Route::middleware('auth')->get('/invitations/{token}', [TeamInvitationController::class, 'accept'])->name('invitations.accept');
```

## Step 6 — Try it

In tinker:
```php
auth()->login(\App\Models\User::first());
\App\Facades\CurrentTeam::set(auth()->user()->teams->first());

(new \App\Http\Controllers\TeamInvitationController)->store(
    new \Illuminate\Http\Request(['email' => 'newperson@example.com'])
);
```

Open Mailpit at http://localhost:8025 — you should see the invitation email rendered. Click "Accept invitation" — should land on `/invitations/{token}` and (if you're still logged in) join the team.

## Verify it works

- ✅ Invitation row created
- ✅ Mail visible in Mailpit
- ✅ Markdown email renders properly with the button
- ✅ Accepting adds the user to `team_user` with role=member
- ✅ Accepting twice returns 410

## Commit

```bash
git add .
git commit -m "feat: team invitation mail with markdown template"
```

## What's next

➡️ `ch34-build.md` — Storage: task attachments uploaded to disk.
