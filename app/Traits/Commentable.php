<?php

namespace App\Traits;

use App\Models\Comment;
trait Commentable
{
    public function initializeCommentable()
    {
        $this->appends = array_merge([
            'class_name',
        ], $this->appends);
    }

    public function comments()
    {
        return $this->morphMany(Comment::class, 'commentable')->latest();
    }

    public function getClassNameAttribute()
    {
        return $this->getMorphClass();
    }
}
