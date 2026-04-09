# Chapter 44b — The Laravel AI SDK (Laravel 13)

Laravel 13 introduces a **first-party AI SDK** — a unified, provider-agnostic API for text generation, image generation, audio synthesis, and embeddings. Before L13, every team rolled their own wrapper around `openai-php/client` or similar; now there's a blessed way that ships with the framework.

This chapter is short but important: AI features are increasingly an expected line item on Laravel resumes, and L13 makes them genuinely easy.

## Why first-party matters

- **Provider-agnostic**: swap OpenAI ↔ Anthropic ↔ Gemini ↔ local models by changing config, not code.
- **Integrated with the framework**: respects queues, events, retries, logging.
- **Type-safe**: real PHP objects, not raw arrays.
- **Tested patterns**: streaming, tool use, structured output, and agents are first-class concepts.

## Configuration

After upgrading to L13, install and configure providers in `config/ai.php`:

```php
return [
    'default' => env('AI_PROVIDER', 'openai'),

    'providers' => [
        'openai' => [
            'driver' => 'openai',
            'api_key' => env('OPENAI_API_KEY'),
            'model'   => env('OPENAI_MODEL', 'gpt-4o-mini'),
        ],
        'anthropic' => [
            'driver' => 'anthropic',
            'api_key' => env('ANTHROPIC_API_KEY'),
            'model'   => env('ANTHROPIC_MODEL', 'claude-sonnet-4-6'),
        ],
    ],
];
```

## Text generation

The simplest case:

```php
use Illuminate\Support\Facades\AI;

$response = AI::prompt('Summarize this article in 3 bullet points: ...')
    ->generate();

echo $response->text;
```

## Agents (named, reusable prompts)

Agents are PHP classes that encapsulate a system prompt, tools, and behavior:

```php
class SalesCoach extends Agent
{
    protected string $instructions = <<<PROMPT
        You are a sales coach. When given a transcript, identify three
        coaching opportunities and suggest concrete language the rep could
        use next time. Be direct and specific.
    PROMPT;
}

// Anywhere in your app
$response = SalesCoach::make()->prompt('Analyze this sales transcript: ...');
```

Agents are discoverable, testable, and queueable — you can dispatch them to a job and run hundreds in parallel.

## Image and audio

```php
use Illuminate\Support\Facades\Image;
use Illuminate\Support\Facades\Audio;

$image = Image::of('A donut sitting on the kitchen counter, soft natural light')
    ->size('1024x1024')
    ->generate();

$image->store('public/generated');

$audio = Audio::of('I love coding with Laravel.')
    ->voice('alloy')
    ->generate();

$audio->store('public/voiceovers');
```

Both return objects backed by Laravel's filesystem — you store them just like an uploaded file.

## Embeddings (for semantic search)

```php
use Illuminate\Support\Str;

$vector = Str::of('Napa Valley has great wine.')->toEmbeddings();

DB::table('documents')->insert([
    'content'   => 'Napa Valley has great wine.',
    'embedding' => $vector,
]);
```

Combine this with the new pgvector query support (see `phase-3-eloquent/ch18-query-builder.md`) and you have a complete RAG / semantic-search pipeline in roughly 20 lines of Laravel.

## Putting it together: a mini RAG endpoint

```php
Route::post('/search', function (Request $request) {
    $documents = DB::table('documents')
        ->whereVectorSimilarTo('embedding', $request->input('q'))
        ->limit(5)
        ->get();

    $context = $documents->pluck('content')->implode("\n---\n");

    return AI::prompt("Answer the question using this context:\n{$context}\n\nQuestion: {$request->input('q')}")
        ->generate()
        ->text;
});
```

That's a fully functioning "ask questions about your data" endpoint. In CodeIgniter, this would be a multi-day spike. In Laravel 13, it's an afternoon.

## Testing

The AI facade supports faking, just like `Http::fake()`:

```php
AI::fake([
    'Summarize:*' => 'A short fake summary.',
]);

// In your test
$result = AI::prompt('Summarize: This long article...')->generate();
expect($result->text)->toBe('A short fake summary.');
```

This means your test suite never actually calls a paid API.

## Common Mistakes

1. **Calling AI synchronously in a web request.** A 5-second model call ruins UX. Dispatch to a queue (Chapter 30) and notify the user when done.
2. **Not budgeting tokens.** Set spending alerts at the provider level. Log token counts via the SDK's events.
3. **Putting prompts in controllers.** Use Agent classes — they're testable and reusable.
4. **Trusting model output blindly.** For anything user-facing, validate / sanitize the response. Models can and do hallucinate.

## CodeIgniter comparison

There is no CI equivalent. This is one of the cleanest examples of why Laravel keeps pulling ahead — features that would be a multi-week integration in legacy frameworks ship as a one-line facade call.

## Hands-on Task

1. Sign up for OpenAI or Anthropic, get an API key, set it in `.env`.
2. Build a `/summarize` endpoint that takes a `text` field and returns a 3-bullet summary using `AI::prompt(...)`.
3. Wrap the call in a queue job so the request returns immediately. Use a database listener / polling endpoint to fetch the result.
4. Write a Pest test using `AI::fake([...])` so it runs without hitting the network.

## Self-check

1. Why is the AI SDK provider-agnostic? What does that buy you?
2. When should AI work go on a queue vs. run inline?
3. How do you test code that uses `AI::prompt()` without spending money?
4. What's the relationship between embeddings and `whereVectorSimilarTo()`?

🔨 **Build it for real:** Apply this chapter to project P4 — see [`build/p4-projectly/ch44b-build.md`](../build/p4-projectly/ch44b-build.md). This is the last build file in the course. After this, wrap up P4 with [`build/p4-projectly/99-finish.md`](../build/p4-projectly/99-finish.md).

➡️ Next: `phase-8-job-prep/ch45-resume.md`
