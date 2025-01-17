<?php

namespace App\Models\Messenger;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model as Eloquent;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Message extends Eloquent
{
    use SoftDeletes;

    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'messages';

    /**
     * The relationships that should be touched on save.
     *
     * @var array
     */
    protected $touches = ['thread'];

    /**
     * The attributes that can be set with Mass Assignment.
     *
     * @var array
     */
    protected $fillable = ['thread_id', 'user_id', 'body', 'has_relation'];

    /**
     * {@inheritDoc}
     */
    public function __construct(array $attributes = [])
    {
        $this->table = Models::table('messages');

        parent::__construct($attributes);
    }

    /**
     * Thread relationship.
     *
     *
     * @codeCoverageIgnore
     */
    public function thread(): BelongsTo
    {
        return $this->belongsTo(Models::classname(Thread::class), 'thread_id', 'id');
    }

    /**
     * User relationship.
     *
     *
     * @codeCoverageIgnore
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(Models::user(), 'user_id');
    }

    /**
     * Participants relationship.
     *
     *
     * @codeCoverageIgnore
     */
    public function participants(): HasMany
    {
        return $this->hasMany(Models::classname(Participant::class), 'thread_id', 'thread_id');
    }

    /**
     * Recipients of this message.
     */
    public function recipients(): HasMany
    {
        return $this->participants()->where('user_id', '!=', $this->user_id);
    }

    /**
     * Files of this message.
     */
    public function files(): HasMany
    {
        return $this->hasMany(MessageFile::class, 'message_id', 'id');
    }

    /**
     * Replies of this message.
     */
    public function related_messages(): HasMany
    {
        return $this->hasMany(MessageForwards::class, 'message_id', 'id');
    }

    /**
     * Returns unread messages given the userId.
     */
    public function scopeUnreadForUser(Builder $query, int $userId): Builder
    {
        return $query->has('thread')
            ->where('user_id', '!=', $userId)
            ->whereHas('participants', function (Builder $query) use ($userId) {
                $query->where('user_id', $userId)
                    ->whereNull('deleted_at')
                    ->where(function (Builder $q) {
                        $q->where('last_read', '<', $this->getConnection()->raw($this->getConnection()->getTablePrefix().$this->getTable().'.created_at'))
                            ->orWhereNull('last_read');
                    });
            });
    }
}
