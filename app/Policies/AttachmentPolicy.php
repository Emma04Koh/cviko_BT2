<?php

namespace App\Policies;

use App\Models\Attachment;
use App\Models\Note;
use App\Models\User;

class AttachmentPolicy
{
    public function view(User $user, Attachment $attachment): bool
    {
        $note = $attachment->attachable;
        if ($note instanceof Note) {
            return (new NotePolicy())->view($user, $note);
        }
        return false;
    }

    public function create(User $user, Note $note): bool
    {
        return (new NotePolicy())->update($user, $note);
    }

    public function delete(User $user, Attachment $attachment): bool
    {
        $note = $attachment->attachable;
        if ($note instanceof Note) {
            return (new NotePolicy())->update($user, $note);
        }
        return false;
    }
}
