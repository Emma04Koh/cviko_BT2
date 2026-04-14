<?php

namespace App\Http\Controllers;

use App\Models\Comment;
use App\Models\Note;
use App\Models\Task;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class CommentController extends Controller
{
    public function indexForNote(Note $note)
    {
        $this->authorize('view', $note);

        $comments = $note->comments()->with('user')->latest()->get();

        return response()->json($comments, Response::HTTP_OK);
    }

    public function indexForTask(Task $task)
    {
        $this->authorize('view', [Task::class, $task->note]);

        $comments = $task->comments()->with('user')->latest()->get();

        return response()->json($comments, Response::HTTP_OK);
    }

    public function storeForNote(Request $request, Note $note)
    {
        $this->authorize('view', $note);

        $validated = $request->validate([
            'body' => 'required|string',
            'attachment' => 'nullable|file|max:10240',
        ]);

        $comment = $note->comments()->create([
            'body' => $validated['body'],
            'user_id' => $request->user()->id,
        ]);

        if ($request->hasFile('attachment')) {
            $path = $request->file('attachment')->store('attachments/comments', 'public');
        }

        return response()->json($comment->load('user'), Response::HTTP_CREATED);
    }

    public function storeForTask(Request $request, Task $task)
    {
        $this->authorize('view', [Task::class, $task->note]);

        $validated = $request->validate([
            'body' => 'required|string',
        ]);

        $comment = $task->comments()->create([
            'body' => $validated['body'],
            'user_id' => $request->user()->id,
        ]);

        return response()->json($comment->load('user'), Response::HTTP_CREATED);
    }

    public function update(Request $request, Comment $comment)
    {
        $this->authorize('update', $comment);

        $validated = $request->validate(['body' => 'required|string']);
        $comment->update($validated);

        return response()->json($comment->load('user'), Response::HTTP_OK);
    }

    public function destroy(Comment $comment)
    {
        $this->authorize('delete', $comment);

        $comment->delete();

        return response()->json(['message' => 'Komentár bol zmazaný.'], Response::HTTP_OK);
    }
}
