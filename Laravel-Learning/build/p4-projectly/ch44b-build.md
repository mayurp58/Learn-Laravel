# P4 · Chapter 44b — Apply: Laravel AI SDK (standups + semantic search)

**Read first:** `phase-7-ecosystem/ch44b-ai-sdk.md`

## What you're building this chapter

The headline L13 feature: AI-generated standups. Every morning at 9 AM, a scheduled job builds a "what I did yesterday" summary for each user using the Laravel AI SDK and emails it to them.

Optional bonus: semantic task search using `whereVectorSimilarTo` (pgvector).

## Step 1 — Install + configure

```bash
composer require anthropic-php/sdk     # or openai-php/laravel
```

Set the API key in `.env`:
```env
ANTHROPIC_API_KEY=sk-ant-...
AI_PROVIDER=anthropic
AI_MODEL=claude-sonnet-4-6
```

Configure `config/ai.php` (the L13 SDK reads this):

```php
return [
    'default' => env('AI_PROVIDER', 'anthropic'),
    'providers' => [
        'anthropic' => [
            'driver'  => 'anthropic',
            'api_key' => env('ANTHROPIC_API_KEY'),
            'model'   => env('AI_MODEL', 'claude-sonnet-4-6'),
        ],
    ],
];
```

## Step 2 — A StandupAgent

`app/AI/StandupAgent.php`:

```php
<?php

namespace App\AI;

use Illuminate\Support\Facades\AI;

class StandupAgent
{
    public function generate(string $userName, array $completedTasks, array $inProgressTasks): string
    {
        $taskList = collect($completedTasks)->map(fn($t) => "- DONE: {$t}")->concat(
            collect($inProgressTasks)->map(fn($t) => "- WIP: {$t}")
        )->implode("\n");

        $prompt = <<<PROMPT
        You are writing a daily standup update for {$userName}.

        Yesterday they worked on these tasks:
        {$taskList}

        Write a 3-sentence standup in first person that:
        1. Summarizes what was done
        2. Mentions what's in progress
        3. Notes any blockers (or "no blockers" if none implied)

        Keep it terse and human. No bullet points.
        PROMPT;

        return AI::prompt($prompt)->generate()->text;
    }
}
```

## Step 3 — Queue job that runs the agent and sends mail

```bash
php artisan make:job GenerateStandupForUser
```

```php
<?php

namespace App\Jobs;

use App\AI\StandupAgent;
use App\Contracts\Notifier;
use App\Models\Task;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\Attributes\{Tries, Backoff};
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

#[Tries(2)]
#[Backoff(120)]
class GenerateStandupForUser implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(public User $user) {}

    public function handle(StandupAgent $agent, Notifier $notifier): void
    {
        $teamIds = $this->user->teams->pluck('id');

        $completed = Task::whereIn('project_id', function ($q) use ($teamIds) {
                $q->select('id')->from('projects')->whereIn('team_id', $teamIds);
            })
            ->where('assignee_id', $this->user->id)
            ->where('status', 'done')
            ->where('updated_at', '>=', now()->subDay())
            ->pluck('title')
            ->toArray();

        $inProgress = Task::whereIn('project_id', function ($q) use ($teamIds) {
                $q->select('id')->from('projects')->whereIn('team_id', $teamIds);
            })
            ->where('assignee_id', $this->user->id)
            ->where('status', 'in_progress')
            ->pluck('title')
            ->toArray();

        if (empty($completed) && empty($inProgress)) {
            return;
        }

        $standup = $agent->generate($this->user->name, $completed, $inProgress);

        $notifier->send(
            $this->user,
            "Your standup for " . now()->format('M j'),
            $standup
        );
    }
}
```

## Step 4 — Schedule it

`routes/console.php`:

```php
Schedule::call(function () {
    \App\Models\User::chunk(50, function ($users) {
        foreach ($users as $user) {
            \App\Jobs\GenerateStandupForUser::dispatch($user);
        }
    });
})->dailyAt('09:00')->name('generate-standups');
```

Each user gets their own job → parallelism via the queue worker.

## Step 5 — Test with `AI::fake()`

`tests/Feature/StandupGenerationTest.php`:

```php
<?php

use App\Jobs\GenerateStandupForUser;
use App\Models\Project;
use App\Models\Task;
use App\Models\Team;
use App\Models\User;
use Illuminate\Support\Facades\AI;

it('generates and sends a standup', function () {
    AI::fake([
        '*' => 'Yesterday I completed onboarding tweaks. Today I am wrapping up the dashboard. No blockers.',
    ]);

    $user = User::factory()->create();
    $team = Team::create(['owner_id' => $user->id, 'name' => 'T', 'slug' => 't']);
    $user->teams()->attach($team->id, ['role' => 'owner']);
    $project = Project::factory()->create(['team_id' => $team->id]);

    Task::factory()->create([
        'project_id' => $project->id,
        'assignee_id' => $user->id,
        'status' => 'done',
    ]);

    GenerateStandupForUser::dispatchSync($user);

    AI::assertSent(fn ($prompt) => str_contains($prompt, $user->name));
});
```

> `AI::fake()` is the L13 equivalent of `Http::fake()` and `Mail::fake()`. **Never let real AI calls into your test suite** — they're slow and cost money.

## Step 6 — Bonus: semantic task search

Migration:
```bash
php artisan make:migration add_embedding_to_tasks
```

```php
public function up(): void
{
    DB::statement('ALTER TABLE tasks ADD COLUMN embedding vector(1536)');
}
```

Job that runs on task creation:

```php
class GenerateTaskEmbedding implements ShouldQueue
{
    use Dispatchable, Queueable, SerializesModels;

    public function __construct(public Task $task) {}

    public function handle(): void
    {
        $vector = \Illuminate\Support\Str::of(
            $this->task->title . "\n" . $this->task->description
        )->toEmbeddings();

        DB::table('tasks')->where('id', $this->task->id)
            ->update(['embedding' => $vector]);
    }
}
```

Hook the model:
```php
// Task.php booted()
static::created(fn($task) => GenerateTaskEmbedding::dispatch($task));
```

Search endpoint:
```php
Route::middleware('auth')->get('/tasks/search', function (Request $request) {
    $teamIds = $request->user()->teams->pluck('id');

    $tasks = DB::table('tasks')
        ->join('projects', 'tasks.project_id', '=', 'projects.id')
        ->whereIn('projects.team_id', $teamIds)
        ->whereVectorSimilarTo('tasks.embedding', $request->input('q'))
        ->limit(10)
        ->get();

    return response()->json($tasks);
});
```

Now `/tasks/search?q=onboarding%20bug` finds tasks even if they don't contain the words "onboarding" or "bug" — they just need to be *semantically similar*.

## Verify it works

- ✅ Manually dispatching the job generates a real standup (visible in mail log)
- ✅ Test using `AI::fake()` passes
- ✅ Tasks have embeddings populated after creation
- ✅ Search endpoint returns semantically related tasks

## Commit

```bash
git add .
git commit -m "feat: AI standups + semantic task search via Laravel 13 AI SDK"
```

## What's next

➡️ `99-finish.md` — final polish, deploy, retire P4, finish the entire course.
