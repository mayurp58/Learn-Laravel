# Chapter 34 — File Storage and Uploads

Laravel's filesystem abstraction lets you treat local disk, S3, and others identically.

## Disks

Defined in `config/filesystems.php`. Defaults:
- `local` — `storage/app`
- `public` — `storage/app/public`, served via a symlink
- `s3` — Amazon S3

Create the public symlink once:
```bash
php artisan storage:link
```

## Uploading files

```php
public function store(Request $request)
{
    $request->validate([
        'avatar' => 'required|image|max:2048',
    ]);

    $path = $request->file('avatar')->store('avatars', 'public');
    // $path = 'avatars/randomname.jpg'

    auth()->user()->update(['avatar' => $path]);
}
```

Custom filename:
```php
$path = $request->file('avatar')->storeAs('avatars', $userId.'.jpg', 'public');
```

## Storage operations

```php
use Illuminate\Support\Facades\Storage;

Storage::disk('public')->put('file.txt', 'contents');
Storage::disk('public')->get('file.txt');
Storage::disk('public')->exists('file.txt');
Storage::disk('public')->delete('file.txt');
Storage::disk('public')->url('avatars/x.jpg');   // public URL
```

## S3

```bash
composer require league/flysystem-aws-s3-v3 "^3.0"
```

```env
AWS_ACCESS_KEY_ID=...
AWS_SECRET_ACCESS_KEY=...
AWS_DEFAULT_REGION=us-east-1
AWS_BUCKET=my-bucket
```

Then `Storage::disk('s3')->put(...)` and everything just works.

## Hands-on Task

Build an `/avatar` upload form. Show the avatar after upload using `Storage::url()`.

➡️ Next: `ch35-cache.md`
