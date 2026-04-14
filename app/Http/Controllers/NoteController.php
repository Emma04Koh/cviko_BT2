<?php

namespace App\Http\Controllers;

use App\Models\Note;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class NoteController extends Controller
{
    public function index()
    {
        $this->authorize('viewAny', Note::class);

        $notes = Note::query()
            ->select(['id', 'user_id', 'title', 'body', 'status', 'is_pinned', 'created_at'])
            ->with([
                'user:id,first_name,last_name',
                'categories:id,name,color',
            ])
            ->whereIn('status', ['published', 'archived'])
            ->orderByDesc('is_pinned')
            ->orderByDesc('created_at')
            ->paginate(5);

        return response()->json([
            'notes' => $notes,
        ], Response::HTTP_OK);
    }

    public function myNotes(Request $request)
    {
        $this->authorize('viewAny', Note::class);

        $notes = $request->user()
            ->notes()
            ->select(['id', 'user_id', 'title', 'body', 'status', 'is_pinned', 'created_at'])
            ->with([
                'categories:id,name,color',
            ])
            ->orderByDesc('is_pinned')
            ->orderByDesc('created_at')
            ->paginate(5);

        return response()->json([
            'notes' => $notes,
        ], Response::HTTP_OK);
    }

    public function store(Request $request)
    {
        $this->authorize('create', Note::class);

        $validated = $request->validate([
            'title' => ['required', 'string', 'min:3', 'max:255'],
            'body'  => ['nullable', 'string'],
            'status' => ['sometimes', 'required', 'string', Rule::in(['draft', 'published', 'archived'])],
            'is_pinned' => ['sometimes', 'boolean'],

            'categories' => ['sometimes', 'array', 'max:3'],
            'categories.*' => ['integer', 'distinct', 'exists:categories,id'],
        ]);

//        $note = Note::create([
//            'user_id' => $request->user()->id,
//            'title'     => $validated['title'],
//            'body'      => $validated['body'] ?? null,
//            'status'    => $validated['status'] ?? 'draft',
//            'is_pinned' => $validated['is_pinned'] ?? false,
//        ]);

        // alebo lepšie riešenie, len potom odstráňte z fillable user_id...
        $note = $request->user()->notes()->create([
            'title'     => $validated['title'],
            'body'      => $validated['body'] ?? null,
            'status'    => $validated['status'] ?? 'draft',
            'is_pinned' => $validated['is_pinned'] ?? false,
        ]);

        if (!empty($validated['categories'])) {
            $note->categories()->sync($validated['categories']);
        }

        return response()->json([
            'message' => 'Poznámka bola úspešne vytvorená.',
            'note' => $note->load([
                'user:id,first_name,last_name',
                'categories:id,name,color',
            ]),
        ], Response::HTTP_CREATED);
    }

    public function show($item)
    {
        $note = \App\Models\Note::with([
            'user',
            'categories',
            'tasks.comments',
            'comments'
        ])->find($item);

        if (!$note) {
            return response()->json([
                'error' => 'Note not found'
            ], 404);
        }
        return response()->json($note);
    }

    public function update(Request $request, string $id)
    {
        $note = Note::find($id);

        if (!$note) {
            return response()->json(
                ['message' => 'Poznámka nenájdená.'],
                Response::HTTP_NOT_FOUND
            );
        }

        $this->authorize('update', [Note::class, $note]);

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
            'note' => $note->load([
                'user:id,first_name,last_name',
                'categories:id,name,color',
            ]),
        ], Response::HTTP_OK);
    }
    public function destroy(string $id)
    {
        $note = Note::find($id);

        if (!$note) {
            return response()->json(['message' => 'Poznámka nenájdená.'], Response::HTTP_NOT_FOUND);
        }

        $note->delete(); // soft delete

        return response()->json(['message' => 'Poznámka bola úspešne odstránená.'], Response::HTTP_OK);
    }

    public function publish(string $id) {
        $note = Note::find($id);

        if (!$note) {
            return response()->json(["message" => "Poznámka nenájdená."], Response::HTTP_NOT_FOUND);
        }

        $note->publish();

        return response()->json(["note" => $note], Response::HTTP_OK);
    }

    public function search(Request $request) {
        $q = trim((string) $request->query("q", ""));

        $notes = Note::searchPublished($q);

        return response()->json([
            "query" => $q,
            "notes" => $notes
        ], Response::HTTP_OK);
    }

    public function archive(string $id)
    {
        $note = Note::find($id);
        if (!$note) {
            return response()->json(['message' => 'Poznámka nenájdená.'], 404);
        }

        $note->archive();
        return response()->json(['message' => 'Poznámka archivovaná', 'note' => $note]);
    }
}
