# Chapter 4 — Installing Laravel and Touring the Project

You have MAMP with PHP 8.4 and MySQL. Perfect — Laravel 13 requires **PHP 8.3+** (supports 8.3, 8.4, and 8.5), so you're set.

## Step 1: Make MAMP's PHP available in your terminal

By default, your terminal uses macOS's built-in PHP. We want MAMP's. Add this to `~/.zshrc`:

```bash
export PATH="/Applications/MAMP/bin/php/php8.4.0/bin:$PATH"
```

(Adjust `php8.4.0` to whatever exact version MAMP installed — check `/Applications/MAMP/bin/php/`.)

Then:
```bash
source ~/.zshrc
php -v          # should show PHP 8.4
which php       # should show MAMP path
```

## Step 2: Install Composer (if not already)

```bash
php -r "copy('https://getcomposer.org/installer', 'composer-setup.php');"
php composer-setup.php
sudo mv composer.phar /usr/local/bin/composer
composer --version
```

## Step 3: Create your first Laravel project

```bash
cd ~/Sites    # or wherever you keep code
composer create-project laravel/laravel hello-laravel
cd hello-laravel
```

This downloads Laravel and installs all dependencies (~80 MB in `vendor/`).

## Step 4: Run it

```bash
php artisan serve
```

Open http://127.0.0.1:8000 — you should see the Laravel welcome page. Congratulations.

`php artisan serve` starts PHP's built-in dev server. You don't need MAMP's Apache for development — only for MySQL.

## Step 5: Connect to MAMP's MySQL

Edit `.env` in the project root:

```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=8889                # MAMP's default MySQL port
DB_DATABASE=hello_laravel
DB_USERNAME=root
DB_PASSWORD=root
DB_SOCKET=/Applications/MAMP/tmp/mysql/mysql.sock
```

In MAMP, open phpMyAdmin and create a database called `hello_laravel`.

Then run:
```bash
php artisan migrate
```

You should see "Migration table created successfully" and a few default tables created. If yes — your Laravel + MySQL is wired up.

## Project Structure Tour

Open the project in your editor. Here's what each folder is:

```
hello-laravel/
├── app/                  ← YOUR application code lives here
│   ├── Http/
│   │   ├── Controllers/  ← Controllers
│   │   ├── Middleware/   ← Middleware
│   │   └── Requests/     ← Form Request validation classes
│   ├── Models/           ← Eloquent models
│   ├── Providers/        ← Service providers
│   └── ...
├── bootstrap/            ← Framework bootstrap (rarely touched)
│   └── app.php           ← App config (Laravel 11+; still where middleware/exceptions live in L13)
├── config/               ← Config files (database, mail, cache, etc.)
├── database/
│   ├── migrations/       ← DB schema migrations
│   ├── seeders/          ← Seed data
│   └── factories/        ← Model factories for tests
├── public/               ← Web root (index.php lives here)
├── resources/
│   ├── views/            ← Blade templates
│   ├── css/  js/         ← Frontend assets
│   └── lang/             ← Translations
├── routes/
│   ├── web.php           ← Web routes (with sessions, CSRF)
│   ├── api.php           ← API routes (stateless) — may need install
│   └── console.php       ← Artisan commands
├── storage/              ← Logs, cache, file uploads, compiled views
├── tests/                ← PHPUnit/Pest tests
├── vendor/               ← Composer packages (gitignored)
├── .env                  ← Environment variables (gitignored!)
├── artisan               ← The CLI tool
├── composer.json
└── package.json          ← Frontend deps (Vite, Tailwind)
```

### CodeIgniter comparison

| CodeIgniter 3 | Laravel |
|---|---|
| `application/controllers/` | `app/Http/Controllers/` |
| `application/models/` | `app/Models/` |
| `application/views/` | `resources/views/` |
| `application/config/` | `config/` |
| `application/libraries/` | Composer packages or `app/Services/` |
| `application/helpers/` | `app/Helpers/` (you create it) or service classes |
| `system/` | `vendor/laravel/framework/` |
| Auto-routing | Explicit in `routes/web.php` |

## Artisan — your new best friend

Artisan is Laravel's CLI tool. Try:

```bash
php artisan list                  # show all commands
php artisan make:controller PostController
php artisan make:model Post -m    # model + migration
php artisan route:list            # show all registered routes
php artisan tinker                # interactive REPL
php artisan migrate               # run migrations
php artisan migrate:fresh --seed  # wipe and reseed
php artisan cache:clear
php artisan config:clear
```

Spend 10 minutes exploring `php artisan list`. You don't need to memorize anything — just get a feel.

## Tinker — the interactive shell

```bash
php artisan tinker
```

```php
>>> 1 + 1
=> 2
>>> $user = new \App\Models\User(['name' => 'Test']);
>>> $user->name
=> "Test"
>>> exit
```

This is invaluable for testing Eloquent queries without writing a controller. We'll use it constantly in Phase 3.

## Common Mistakes

1. **Editing `vendor/` files.** Never. They get overwritten on `composer install`.
2. **Committing `.env`.** It contains secrets. It's in `.gitignore` for a reason. Use `.env.example` for sharing structure.
3. **Forgetting `php artisan config:clear`** after editing `.env` if config caching is on.
4. **Running `composer update` randomly in production.** Use `composer install` (which respects `composer.lock`).

## Hands-on Task

1. Get `php artisan serve` running and visit the welcome page.
2. Connect to MAMP's MySQL via `.env`. Run `php artisan migrate`. Verify the `users`, `cache`, and `jobs` tables exist in phpMyAdmin.
3. Run `php artisan tinker` and try:
   ```php
   \App\Models\User::factory()->create(['name' => 'Test User', 'email' => 'test@example.com']);
   \App\Models\User::all();
   ```
   You just created and queried a user without writing a single SQL line. (We'll explain this in Phase 3.)
4. Run `php artisan route:list` and look at the output. You should see one route — the welcome page.

## Self-check

1. What does `php artisan serve` actually do?
2. Where does Laravel store secrets?
3. What's the difference between `routes/web.php` and `routes/api.php`?
4. What's `tinker` for?
5. Where do controllers go? Where do models go?

➡️ **End of Phase 1.** Take a break. Re-read your notes. Then go to `phase-2-core/ch05-request-lifecycle.md`.
