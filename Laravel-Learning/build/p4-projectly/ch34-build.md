# P4 · Chapter 34 — Apply: Storage (task attachments)

**Read first:** `phase-5-advanced/ch34-storage.md`

## What you're building this chapter

Users can upload files to a task. Files stored on the `public` disk, max 5 MB, validated mime types. Download via a controller route (not direct URL) so we can authorize.

## Step 1 — Migration + model

```bash
php artisan make:model TaskAttachment -m
```

```php
Schema::create('task_attachments', function (Blueprint $table) {
    $table->id();
    $table->foreignId('task_id')->constrained()->cascadeOnDelete();
    $table->foreignId('uploaded_by')->constrained('users')->cascadeOnDelete();
    $table->string('path');
    $table->string('original_name');
    $table->unsignedInteger('size_bytes');
    $table->string('mime_type');
    $table->timestamps();
});
```

```php
// TaskAttachment.php
protected $fillable = ['task_id', 'uploaded_by', 'path', 'original_name', 'size_bytes', 'mime_type'];

public function task() { return $this->belongsTo(Task::class); }
public function uploader() { return $this->belongsTo(User::class, 'uploaded_by'); }
```

Add to `Task.php`:
```php
public function attachments() { return $this->hasMany(TaskAttachment::class); }
```

```bash
php artisan migrate
```

## Step 2 — Symlink the public disk

```bash
php artisan storage:link
```

Creates `public/storage` → `storage/app/public`.

## Step 3 — Upload controller

```bash
php artisan make:controller TaskAttachmentController
```

```php
<?php

namespace App\Http\Controllers;

use App\Models\Task;
use App\Models\TaskAttachment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class TaskAttachmentController extends Controller
{
    public function store(Request $request, Task $task)
    {
        $request->validate([
            'file' => ['required', 'file', 'max:5120',  // 5 MB
                       'mimes:jpg,jpeg,png,pdf,txt,md,docx'],
        ]);

        $file = $request->file('file');
        $path = $file->store("attachments/{$task->id}", 'public');

        $task->attachments()->create([
            'uploaded_by'   => $request->user()->id,
            'path'          => $path,
            'original_name' => $file->getClientOriginalName(),
            'size_bytes'    => $file->getSize(),
            'mime_type'     => $file->getMimeType(),
        ]);

        return back()->with('status', 'Attachment uploaded.');
    }

    public function download(TaskAttachment $attachment)
    {
        // TODO in ch37+: real authorization via TaskPolicy
        abort_unless(
            $attachment->task->project->team->members->contains(auth()->id()),
            403
        );

        return Storage::disk('public')->download(
            $attachment->path,
            $attachment->original_name
        );
    }

    public function destroy(TaskAttachment $attachment, Request $request)
    {
        abort_unless($attachment->uploaded_by === $request->user()->id, 403);

        Storage::disk('public')->delete($attachment->path);
        $attachment->delete();

        return back()->with('status', 'Attachment deleted.');
    }
}
```

## Step 4 — Routes

```php
Route::middleware('auth')->group(function () {
    Route::post('/tasks/{task}/attachments', [TaskAttachmentController::class, 'store'])->name('attachments.store');
    Route::get('/attachments/{attachment}/download', [TaskAttachmentController::class, 'download'])->name('attachments.download');
    Route::delete('/attachments/{attachment}', [TaskAttachmentController::class, 'destroy'])->name('attachments.destroy');
});
```

## Step 5 — Try it

```bash
curl -X POST http://localhost:8000/tasks/1/attachments \
  -b cookies.txt -c cookies.txt \
  -F "file=@./README.md"
```

(Or just use the form — see the Livewire chapter ch42 for the UI integration.)

Check the disk:
```bash
ls -la storage/app/public/attachments/1/
```

## Verify it works

- ✅ Upload writes a file under `storage/app/public/attachments/{task_id}/`
- ✅ Database row created with correct metadata
- ✅ Files larger than 5 MB are rejected
- ✅ Wrong mime types are rejected
- ✅ Download returns the file with the original filename
- ✅ Non-team-members get 403 on download

## Commit

```bash
git add .
git commit -m "feat: task attachments with upload, download, delete"
```

## What's next

➡️ `ch35-build.md` — cache: dashboard stats with `Cache::touch` (L13 feature).
