# Laravel Cheat Sheet

## Artisan
```bash
php artisan serve
php artisan tinker
php artisan list
php artisan route:list
php artisan make:model Post -mcrf      # model + migration + controller + resource + factory
php artisan make:controller PostController --resource
php artisan make:request StorePostRequest
php artisan make:middleware LogRequests
php artisan make:policy PostPolicy --model=Post
php artisan make:seeder PostSeeder
php artisan make:factory PostFactory --model=Post
php artisan make:resource PostResource
php artisan make:event UserRegistered
php artisan make:listener SendWelcomeEmail --event=UserRegistered
php artisan make:job ProcessVideo
php artisan make:notification InvoicePaid
php artisan make:mail WelcomeMail --markdown=emails.welcome
php artisan make:provider PaymentServiceProvider
php artisan make:command CleanupOldPosts
php artisan migrate
php artisan migrate:fresh --seed
php artisan migrate:rollback
php artisan db:seed
php artisan queue:work
php artisan schedule:run
php artisan schedule:test
php artisan cache:clear
php artisan config:clear
php artisan view:clear
php artisan route:clear
php artisan optimize
php artisan optimize:clear
php artisan storage:link
```

## Routing
```php
Route::get('/x', [Controller::class, 'method']);
Route::resource('posts', PostController::class);
Route::apiResource('posts', PostController::class);
Route::middleware('auth')->group(fn() => ...);
Route::prefix('admin')->name('admin.')->group(fn() => ...);
```

## Validation rules
required, nullable, sometimes, string, integer, numeric, boolean, array,
date, email, url, min:5, max:255, between:1,10, in:a,b, unique:users,email,
exists:posts,id, confirmed, same:other, regex:/^[A-Z]/, file, image,
mimes:pdf,docx, size:1024

## Eloquent
```php
Post::all();
Post::find(1);
Post::findOrFail(1);
Post::where('x', 1)->first();
Post::create([...]);
$post->update([...]);
$post->delete();
Post::with('user', 'comments.user')->get();
Post::withCount('comments')->get();
Post::has('comments')->get();
Post::whereHas('comments', fn($q) => $q->where(...))->get();
Post::latest()->paginate(15);
Post::chunkById(200, fn($posts) => ...);
```

## Blade
```blade
@extends('layouts.app')
@section('content') ... @endsection
@if @elseif @else @endif
@foreach ($x as $y) ... @endforeach
@forelse ... @empty ... @endforelse
@auth @endauth
@guest @endguest
@can('update', $post) @endcan
{{ $var }}        {{-- escaped --}}
{!! $html !!}     {{-- raw --}}
@csrf
@method('PUT')
@error('field') {{ $message }} @enderror
<x-component-name :prop="$value" />
```

## Helpers
```php
auth()->user()
session('key')
cache('key')
config('app.name')
route('posts.show', $post)
url('/dashboard')
asset('img/logo.png')
old('field', 'default')
now()
today()
abort(403)
abort_if($condition, 403)
dd($var); dump($var);
```
