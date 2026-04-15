<?php

namespace App\Http\Controllers;

use App\Models\Note;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class NoteController extends Controller
{
    public function index()
    {
        $this->authorize('viewAny', Note::class);

        $notes = Note::query()
            ->select(['id', 'user_id', 'title', 'body', 'status', 'is_pinned', 'created_at'])
            ->with(['user:id,first_name,last_name', 'categories:id,name,color'])
            ->whereIn('status', ['published', 'archived'])
            ->orderByDesc('is_pinned')
            ->orderByDesc('created_at')
            ->paginate(5);

        return response()->json(['notes' => $notes], Response::HTTP_OK);
    }

    public function myNotes(Request $request)
    {
        $this->authorize('viewAny', Note::class);

        $notes = $request->user()
            ->notes()
            ->select(['id', 'user_id', 'title', 'body', 'status', 'is_pinned', 'created_at'])
            ->with(['categories:id,name,color'])
            ->orderByDesc('is_pinned')
            ->orderByDesc('created_at')
            ->paginate(5);

        return response()->json(['notes' => $notes], Response::HTTP_OK);
    }

    public function store(Request $request)
    {
        $this->authorize('create', Note::class);

        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'body' => 'required|string',
            'status' => 'required|in:draft,published,archived',
            'categories' => 'nullable|array',
            'attachment' => 'nullable|file|max:10240',
        ]);

        return DB::transaction(function () use ($validated, $request) {
            $note = $request->user()->notes()->create($validated);

            if (!empty($validated['categories'])) {
                $note->categories()->attach($validated['categories']);
            }

            if ($request->hasFile('attachment')) {
                $file = $request->file('attachment');
                $path = $file->store('notes/' . $note->id, 'public');

                $note->attachments()->create([
                    'public_id'     => (string) Str::ulid(),
                    'collection'    => 'attachment',
                    'visibility'    => 'private',
                    'disk'          => 'public',
                    'path'          => $path,
                    'original_name' => $file->getClientOriginalName(),
                    'stored_name'   => basename($path),
                    'mime_type'     => $file->getMimeType(),
                    'size'          => $file->getSize(),
                ]);
            }

            return response()->json($note->load('categories', 'attachments'), Response::HTTP_CREATED);
        });
    }

    public function show(Note $note)
    {
        $this->authorize('view', $note);

        return response()->json($note->load([
            'user:id,first_name,last_name',
            'categories:id,name,color',
        ]), Response::HTTP_OK);
    }

    public function update(Request $request, Note $note)
    {
        $this->authorize('update', $note);

        $validated = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'body'  => ['nullable', 'string'],
            'status' => ['sometimes', 'required', 'string', Rule::in(['draft', 'published', 'archived'])],
            'is_pinned' => ['sometimes', 'boolean'],
            'categories' => ['sometimes', 'array'],
            'categories.*' => ['integer', 'distinct', 'exists:categories,id'],
        ]);

        $note->update($validated);

        if (array_key_exists('categories', $validated)) {
            $note->categories()->sync($validated['categories']);
        }

        return response()->json([
            'message' => 'Poznámka bola aktualizovaná.',
            'note' => $note->load(['user:id,first_name,last_name', 'categories:id,name,color']),
        ], Response::HTTP_OK);
    }

    public function destroy(Note $note)
    {
        $this->authorize('delete', $note);

        $note->delete();

        return response()->json(['message' => 'Poznámka bola úspešne odstránená.'], Response::HTTP_OK);
    }

    public function publish(Note $note)
    {
        $this->authorize('update', $note);
        $note->publish();
        return response()->json(["note" => $note], Response::HTTP_OK);
    }

    public function archive(Note $note)
    {
        $this->authorize('update', $note);
        $note->archive();
        return response()->json(['message' => 'Poznámka archivovaná', 'note' => $note]);
    }

    public function search(Request $request)
    {
        $this->authorize('viewAny', Note::class);
        $q = trim((string) $request->query("q", ""));

        $notes = Note::searchPublished($q);

        return response()->json(["query" => $q, "notes" => $notes], Response::HTTP_OK);
    }
}
