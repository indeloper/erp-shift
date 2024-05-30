<?php

namespace App\Traits;

use App\Models\Comment;
use Illuminate\Database\Eloquent\Relations\MorphMany;

trait Commentable
{
    public function initializeCommentable()
    {
        $this->appends = array_merge([
            'class_name',
        ], $this->appends);
    }

    public function comments(): MorphMany
    {
        return $this->morphMany(Comment::class, 'commentable')->latest();
    }

    public function getClassNameAttribute()
    {
        return $this->getMorphClass();
    }

    public function copyCommentsTo($another_commentable)
    {
        if (method_exists($another_commentable, 'comments')) {
            foreach ($this->comments()->get() as $old_comment) {
                $new_comment = $old_comment->replicate();
                $new_comment->commentable_id = $another_commentable->id;
                $new_comment->save();
            }
            $another_commentable->load('comments');
        }

        return $another_commentable;
    }
}
