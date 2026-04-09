# P4 · Chapter 44 — Apply: Filament admin panel

**Read first:** `phase-7-ecosystem/ch44-filament.md`

## What you're building this chapter

A site-admin Filament panel at `/admin` for managing all teams, users, and projects across the platform. Not for tenant admins (that's Livewire), but for *you* — the SaaS operator.

## Step 1 — Install Filament

```bash
composer require filament/filament:"^4.0" -W
php artisan filament:install --panels
```

When prompted, accept defaults. This creates `app/Providers/Filament/AdminPanelProvider.php`.

## Step 2 — Create an admin user

```bash
php artisan make:filament-user
```

Pick a name, email, password. This is your platform-admin login.

> In a real product you'd add an `is_admin` column to users + an authorization gate. For P4 we'll keep it simple: anyone with a Filament user record can access. Restrict in production.

## Step 3 — Resources for your models

```bash
php artisan make:filament-resource Team
php artisan make:filament-resource User
php artisan make:filament-resource Project
```

Each generates a CRUD interface for that model. Filament auto-generates form fields and table columns from your migrations.

Open `app/Filament/Resources/TeamResource.php`. The `form()` method defines the create/edit form, and `table()` defines the index list. Filament guesses well, but you'll customize:

```php
public static function form(Schema $schema): Schema
{
    return $schema->components([
        Forms\Components\TextInput::make('name')->required(),
        Forms\Components\TextInput::make('slug')->required()->unique(ignoreRecord: true),
        Forms\Components\Select::make('owner_id')
            ->relationship('owner', 'name')
            ->searchable()
            ->required(),
    ]);
}

public static function table(Table $table): Table
{
    return $table
        ->columns([
            Tables\Columns\TextColumn::make('name')->searchable(),
            Tables\Columns\TextColumn::make('owner.name')->label('Owner'),
            Tables\Columns\TextColumn::make('members_count')->counts('members')->label('Members'),
            Tables\Columns\TextColumn::make('created_at')->dateTime()->sortable(),
        ])
        ->actions([
            Tables\Actions\EditAction::make(),
            Tables\Actions\DeleteAction::make(),
        ]);
}
```

> **`->counts('members')`** is the Filament shortcut for `withCount`. Avoids N+1 in the index automatically.

Do similar customization for `UserResource` and `ProjectResource`. The point isn't to perfect every form — it's to feel how fast Filament gets you from "models exist" to "fully usable admin UI."

## Step 4 — Try it

Visit http://localhost:8000/admin → log in with the user you created → click around. You'll see your three resources in the sidebar. Click Teams → see all teams with member count. Edit one. Delete one. Create one.

**This took 10 minutes.** Equivalent CodeIgniter would have been 1–2 days of building admin templates from scratch.

## Step 5 — A custom widget on the dashboard

Filament's default dashboard is empty. Let's add a stats widget:

```bash
php artisan make:filament-widget StatsOverview --stats-overview
```

`app/Filament/Widgets/StatsOverview.php`:

```php
protected function getStats(): array
{
    return [
        Stat::make('Teams',    \App\Models\Team::count()),
        Stat::make('Users',    \App\Models\User::count()),
        Stat::make('Projects', \App\Models\Project::count()),
        Stat::make('Tasks',    \App\Models\Task::count())
            ->description('Across all teams')
            ->color('success'),
    ];
}
```

Refresh `/admin` — the stats appear at the top of the dashboard.

## Verify it works

- ✅ `/admin` loads with the Filament UI
- ✅ All three resources are listed in the sidebar
- ✅ CRUD works for each
- ✅ The stats widget shows correct counts
- ✅ Filament resources don't N+1 (verify with the query counter from earlier)

## Commit

```bash
git add .
git commit -m "feat: Filament admin panel with Team/User/Project resources + stats"
```

## What's next

➡️ `ch44b-build.md` — Laravel AI SDK: AI-summarized standups (the L13 headline feature).
