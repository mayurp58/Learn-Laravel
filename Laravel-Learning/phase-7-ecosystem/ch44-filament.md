# Chapter 44 — Filament (Admin Panels)

Filament is a TALL-stack (Tailwind, Alpine, Livewire, Laravel) admin panel framework. It generates beautiful, fully-featured admin interfaces from your Eloquent models in minutes. Currently exploding in popularity. **Knowing Filament is a real job differentiator.**

## Install

```bash
composer require filament/filament:"^3.2" -W
php artisan filament:install --panels
```

Create an admin user:
```bash
php artisan make:filament-user
```

Visit `/admin`. Done — you have an admin panel.

## Generating a resource

```bash
php artisan make:filament-resource Post --generate
```

This inspects your `Post` model and creates:
- A list view with sorting, filtering, search
- A create form
- An edit form
- A view page

You can customize:

```php
public static function form(Form $form): Form
{
    return $form->schema([
        TextInput::make('title')->required()->maxLength(255),
        Textarea::make('body')->required(),
        Select::make('user_id')->relationship('user', 'name'),
        Toggle::make('published'),
        FileUpload::make('cover_image'),
    ]);
}

public static function table(Table $table): Table
{
    return $table
        ->columns([
            TextColumn::make('title')->searchable()->sortable(),
            TextColumn::make('user.name')->label('Author'),
            IconColumn::make('published')->boolean(),
            TextColumn::make('created_at')->dateTime(),
        ])
        ->filters([
            Filter::make('published')->query(fn($q) => $q->whereNotNull('published_at')),
        ])
        ->actions([
            EditAction::make(),
            DeleteAction::make(),
        ]);
}
```

## What you get for free

- Auth, roles, multi-tenancy
- File uploads
- Rich-text editing
- Search, filters, sorting
- Bulk actions
- Notifications
- Charts and dashboards

## Why it matters

Many freelancers and agencies build entire client projects with Filament because it cuts admin development from weeks to days. Hiring managers know this.

## Hands-on Task

Install Filament on your blog project. Generate a `PostResource` and a `CategoryResource`. Try filtering, sorting, bulk delete.

🔨 **Build it for real:** Apply this chapter to project P4 — see [`build/p4-projectly/ch44-build.md`](../build/p4-projectly/ch44-build.md).

➡️ **End of Phase 7 (almost).** One more chapter: `ch44b-ai-sdk.md` — the Laravel 13 AI SDK headline feature. Don't skip it.
