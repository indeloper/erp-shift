<?php

namespace App\Observers;

use App\Models\Comment;
use App\Models\TechAcc\Defects\Defects;
use App\Traits\NotificationGenerator;

class CommentObserver
{
    use NotificationGenerator;

    /**
     * Handle the defects "saved" event.
     *
     * @param  Comment  $comment
     * @return void
     */
    public function saved(Comment $comment)
    {
        if ($comment->commentable_type == Defects::class and strpos($comment->comment, '@user(') === false) return $this->generateDefectNewCommentNotifications($comment);
    }
}
