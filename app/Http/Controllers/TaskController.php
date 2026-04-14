<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class TaskController extends Controller
{
    public function index(Note $note)
    {
        $this->authorize('view', [Task::class, $note]);

        $tasks = $note->tasks()->orderBy('created_at')->get();

        return response()->json(['tasks' => $tasks], Response::HTTP_OK);
    }

    public function update(Request $request, Note $note, Task $task)
    {
        $this->authorize('update', $task);

        $validated = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'is_done' => ['sometimes', 'boolean'],
            'due_at' => ['nullable', 'date'],
        ]);

        $task->update($validated);

        return response()->json([
            'message' => 'Úloha bola úspešne aktualizovaná.',
            'task' => $task,
        ], Response::HTTP_OK);
    }
}
