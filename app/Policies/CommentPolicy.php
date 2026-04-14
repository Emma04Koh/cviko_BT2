<?php

namespace App\Policies;

use App\Models\Note;
use App\Models\User;
use App\Models\Comment;
use Illuminate\Auth\Access\Response;

class CommentPolicy
{
    public function viewAny(User $user): bool
    {
        return false;
    }

    public function view(User $user, Note $note): bool
    {
        return false;
    }

    public function create(User $user): bool
    {
        return false;
    }

    public function update(User $user, Comment $comment): bool
    {
        return $user->id === $comment->user_id;
    }

    public function delete(User $user, Comment $comment): bool
    {
        return $user->id === $comment->user_id;
    }

    public function restore(User $user, Note $note): bool
    {
        return false;
    }

    public function forceDelete(User $user, Note $note): bool
    {
        return false;
    }
}
