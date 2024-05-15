<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Traits\Documentable;
use App\Traits\RussianShortDates;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Blade;

class Comment extends Model
{
    use Documentable;
    use HasFactory;
    use RussianShortDates;

    protected $guarded = ['id'];
    // protected $fillable = ['commentable_id', 'commentable_type', 'comment', 'author_id', 'system', 'count'];

    protected $with = ['files', 'author'];

    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);

        static::deleted(function ($comment) {
            $comment->documents()->delete();
        });
    }

    protected $appends = ['pretty_comment', 'created_at_formatted'];

    const DATE_FORMAT = 'd.m.Y H:i';

    /**
     * This getter parse blade directive if comment
     * have it, otherwise return old comment
     */
    public function getPrettyCommentAttribute(): string
    {
        if (strpos($this->comment, '@') !== false) {
            return Blade::compileString($this->comment);
        }

        return $this->comment;
    }

    /**
     * Getter for created_at formatting
     */
    public function getCreatedAtFormattedAttribute(): ?Carbon
    {
        return $this->created_at->format(self::DATE_FORMAT);
    }

    public function commentable(): MorphTo
    {
        return $this->morphTo();
    }

    public function author(): BelongsTo
    {
        return $this->belongsTo(User::class, 'author_id');
    }
}
