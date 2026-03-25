<?php

namespace App\Http\Controllers;

use App\Models\Note;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;

class NoteController extends Controller
{
    /**
     * Display a listing of the resource.
     */

    public function index()
    {
        $notes = Note::query()
            ->select(['id', 'user_id', 'title', 'body', 'status', 'is_pinned', 'created_at'])
            ->with([
                'user:id,first_name,last_name',
                'categories:id,name,color',
            ])
            ->orderByDesc('is_pinned')
            ->orderByDesc('created_at')
            ->get();

        return response()->json([
            'notes' => $notes,
        ], Response::HTTP_OK);
    }

    /**
     * Store a newly created resource in storage.
     */

    public function store(Request $request)
    {
        $validated = $request->validate([
            "user_id"   => "required|exists:users,id",
            "title"     => "required|string|max:128",
            "body"      => "required|string",
            "status"    => "sometimes|in:draft,published,archived",
            "is_pinned" => "sometimes|boolean",
        ]);

        $note = Note::create([
            'user_id'   => $validated['user_id'],
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

    /**
     * Display the specified resource.
     */

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
    /**
     * Update the specified resource in storage.
     */

    public function update(Request $request, string $id)
    {
        $note = Note::find($id);

        if (!$note) {
            return response()->json(
                ['message' => 'Poznámka nenájdená.'],
                Response::HTTP_NOT_FOUND
            );
        }

        $validated = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'body'  => ['nullable', 'string'],
            'status' => ['sometimes', 'required', 'string', Rule::in(['draft', 'published', 'archived'])],
            'is_pinned' => ['sometimes', 'boolean'],
            'categories' => ['sometimes', 'array'],
            'categories.*' => ['integer', 'distinct', 'exists:categories,id'],
        ]);

        // aktualizujeme iba to, čo prešlo validáciou
        $note->update($validated);

        // spoj. tabulku synchronizujeme iba ak boli poslané idčka
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

    /**
     * Remove the specified resource from storage.
     */

    public function destroy(string $id)
    {
        $note = Note::find($id);

        if (!$note) {
            return response()->json(['message' => 'Poznámka nenájdená.'], Response::HTTP_NOT_FOUND);
        }

        $note->delete(); // soft delete

        return response()->json(['message' => 'Poznámka bola úspešne odstránená.'], Response::HTTP_OK);
    }


    // vlastné metódy - QB
    public function statsByStatus()
    {
        $stats = DB::table('notes')
            ->whereNull('deleted_at')
            ->select('status', DB::raw('COUNT(*) as count'))
            ->groupBy('status')
            ->orderBy('status')
            ->get();

        return response()->json(['stats' => $stats], Response::HTTP_OK);
    }

    public function archiveOldDrafts()
    {
        $affected = DB::table('notes')
            ->whereNull('deleted_at')
            ->where('status', 'draft')
            ->where('updated_at', '<', now()->subDays(30))
            ->update([
                'status' => 'archived',
                'updated_at' => now(),
            ]);

        return response()->json([
            'message' => 'Staré koncepty boli archivované.',
            'affected_rows' => $affected,
        ]);
    }

    public function userNotesWithCategories(string $userId)
    {
        $rows = DB::table('notes')
            ->join('note_category', 'notes.id', '=', 'note_category.note_id')
            ->join('categories', 'note_category.category_id', '=', 'categories.id')
            ->where('notes.user_id', $userId)
            ->whereNull('notes.deleted_at')
            ->orderBy('notes.updated_at', 'desc')
            ->select('notes.id', 'notes.title', 'categories.name as category')
            ->get();

        return response()->json(['notes' => $rows], Response::HTTP_OK);
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

    public function togglePin(string $id)
    {
        $note = Note::find($id);
        if (!$note) {
            return response()->json(['message' => 'Poznámka nenájdená.'], 404);
        }

        $note->togglePin();
        return response()->json(['note' => $note]);
    }

    // archivacia
    public function archive(string $id)
    {
        $note = Note::find($id);
        if (!$note) {
            return response()->json(['message' => 'Poznámka nenájdená.'], 404);
        }

        $note->archive();
        return response()->json(['message' => 'Poznámka archivovaná', 'note' => $note]);
    }

    //pripnute poznamky
    public function pinnedNotes()
    {
        $pinned = Note::where('is_pinned', true)->get();
        return response()->json(['notes' => $pinned]);
    }

    public function addCategory(Request $request, $id)
    {
        $note = Note::find($id);

        if (!$note) {
            return response()->json(['error' => 'Note not found'], 404);
        }

        $validated = $request->validate([
            'category_id' => 'required|exists:categories,id'
        ]);

        $note->categories()->syncWithoutDetaching([$validated['category_id']]);

        return response()->json([
            'message' => 'Category added to note successfully',
            'note' => $note->load('categories')
        ], 200);
    }
    public function removeCategory(Request $request, $id)
    {
        $note = Note::find($id);

        if (!$note) {
            return response()->json(['error' => 'Note not found'], 404);
        }

        $validated = $request->validate([
            'category_id' => 'required|exists:categories,id'
        ]);

        $note->categories()->detach($validated['category_id']);

        return response()->json([
            'message' => 'Category removed from note successfully',
            'note' => $note->load('categories')
        ], 200);
    }
}
