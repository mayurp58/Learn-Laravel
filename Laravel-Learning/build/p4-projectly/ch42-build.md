# P4 · Chapter 42 — Apply: Livewire task board

**Read first:** `phase-7-ecosystem/ch42-livewire.md`

## What you're building this chapter

A real Livewire 3 component: a kanban board for a project's tasks. Three columns (Todo / In Progress / Done), drag-and-drop, instant save. No page reloads.

This is the chapter that makes Projectly feel like a real product.

## Step 1 — Generate the component

```bash
php artisan make:livewire TaskBoard
```

Two files appear:
- `app/Livewire/TaskBoard.php`
- `resources/views/livewire/task-board.blade.php`

## Step 2 — Component class

`app/Livewire/TaskBoard.php`:

```php
<?php

namespace App\Livewire;

use App\Models\Project;
use App\Models\Task;
use Livewire\Component;

class TaskBoard extends Component
{
    public Project $project;

    public string $newTaskTitle = '';
    public string $newTaskColumn = 'todo';

    public function mount(Project $project): void
    {
        $this->project = $project;
    }

    public function addTask(): void
    {
        $this->validate(['newTaskTitle' => 'required|string|max:255']);

        $this->project->tasks()->create([
            'title'  => $this->newTaskTitle,
            'status' => $this->newTaskColumn,
        ]);

        $this->newTaskTitle = '';
    }

    public function moveTask(int $taskId, string $newStatus): void
    {
        $task = Task::findOrFail($taskId);
        abort_if($task->project_id !== $this->project->id, 403);

        $task->update(['status' => $newStatus]);
    }

    public function deleteTask(int $taskId): void
    {
        $task = Task::findOrFail($taskId);
        abort_if($task->project_id !== $this->project->id, 403);
        $task->delete();
    }

    public function render()
    {
        return view('livewire.task-board', [
            'columns' => [
                'todo'        => $this->project->tasks()->where('status', 'todo')->latest()->get(),
                'in_progress' => $this->project->tasks()->where('status', 'in_progress')->latest()->get(),
                'done'        => $this->project->tasks()->where('status', 'done')->latest()->get(),
            ],
        ]);
    }
}
```

## Step 3 — The view

`resources/views/livewire/task-board.blade.php`:

```blade
<div>
    <div class="flex gap-4 mb-6">
        <input type="text" wire:model="newTaskTitle" placeholder="New task title…"
               wire:keydown.enter="addTask"
               class="flex-1 px-3 py-2 border rounded">
        <select wire:model="newTaskColumn" class="px-3 py-2 border rounded">
            <option value="todo">To do</option>
            <option value="in_progress">In progress</option>
            <option value="done">Done</option>
        </select>
        <button wire:click="addTask" class="px-4 py-2 bg-blue-600 text-white rounded">Add</button>
    </div>

    <div class="grid grid-cols-3 gap-4">
        @foreach ($columns as $status => $tasks)
            <div class="bg-gray-100 rounded p-3 min-h-[300px]"
                 x-data
                 @drop.prevent="$wire.moveTask($event.dataTransfer.getData('id'), '{{ $status }}')"
                 @dragover.prevent>
                <h3 class="font-semibold mb-3 capitalize">{{ str_replace('_', ' ', $status) }} ({{ $tasks->count() }})</h3>

                @foreach ($tasks as $task)
                    <div class="bg-white border rounded p-3 mb-2 cursor-move shadow-sm"
                         draggable="true"
                         x-on:dragstart="$event.dataTransfer.setData('id', '{{ $task->id }}')">
                        <p class="font-medium">{{ $task->title }}</p>
                        <button wire:click="deleteTask({{ $task->id }})"
                                onclick="return confirm('Delete?')"
                                class="text-xs text-red-500 mt-1">Delete</button>
                    </div>
                @endforeach
            </div>
        @endforeach
    </div>
</div>
```

> The Alpine.js attributes (`x-data`, `@drop`, `@dragover`) handle the drag-and-drop client-side, then call `$wire.moveTask(...)` which round-trips to the server. Livewire 3 automatically re-renders the component.

## Step 4 — Wire it into a route

`routes/web.php`:
```php
Route::middleware('auth')->get('/projects/{project}', function (\App\Models\Project $project) {
    abort_unless($project->team->members->contains(auth()->id()), 403);
    return view('projects.show', compact('project'));
})->name('projects.show');
```

`resources/views/projects/show.blade.php`:
```blade
@extends('layouts.app')
@section('content')
    <div class="flex justify-between mb-6">
        <h1 class="text-2xl font-bold">{{ $project->name }}</h1>
        <a href="{{ route('dashboard') }}" class="text-blue-600">← Dashboard</a>
    </div>

    <livewire:task-board :project="$project" />
@endsection
```

## Step 5 — Try it

Visit `/projects/1`. You should see three columns. Type a task title, hit Enter or Add. It appears in the column. Drag it to another column. It snaps. No page reloads, no JS framework.

## Verify it works

- ✅ Adding tasks works (form clears after add)
- ✅ Dragging tasks between columns persists
- ✅ Deleting tasks works
- ✅ The component is restricted to team members (try with another user → 403)
- ✅ Counts in the column headers update

## Commit

```bash
git add .
git commit -m "feat: Livewire kanban task board with drag-and-drop"
```

## What's next

➡️ `ch43-build.md` — Inertia (optional alternative). Or skip to ch44 for Filament.
