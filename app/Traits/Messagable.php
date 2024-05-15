<?php

namespace App\Traits;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;
use App\Models\Messenger\Message;
use App\Models\Messenger\Models;
use App\Models\Messenger\Participant;
use App\Models\Messenger\Thread;
use Illuminate\Database\Eloquent\Builder;

trait Messagable
{
    /**
     * Message relationship.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     *
     * @codeCoverageIgnore
     */
    public function messages(): HasMany
    {
        return $this->hasMany(Models::classname(Message::class));
    }

    /**
     * Participants relationship.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     *
     * @codeCoverageIgnore
     */
    public function participants(): HasMany
    {
        return $this->hasMany(Models::classname(Participant::class));
    }

    /**
     * Thread relationship.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     *
     * @codeCoverageIgnore
     */
    public function threads(): BelongsToMany
    {
        return $this->belongsToMany(
            (Thread::class),
            (Participant::class),
            'user_id',
            'thread_id'
        );
    }

    /**
     * Returns the new messages count for user.
     *
     * @return int
     */
    public function newThreadsCount(): int
    {
        return $this->threadsWithNewMessages()->count();
    }

    /**
     * Returns the new messages for user.
     *
     * @return int
     */
    public function unreadMessages(): int
    {
        return \App\Models\Messenger\Message::unreadForUser($this->getKey())->get();
    }

    /**
     * Returns the new messages count for user.
     *
     * @return int
     */
    public function unreadMessagesCount(): int
    {
        return count($this->unreadMessages());
    }

    /**
     * Returns all threads with new messages.
     *
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function threadsWithNewMessages(): Collection
    {
        return $this->threads()
            ->where(function (Builder $q) {
                $q->whereIn(Models::table('threads').'.id', $this->unreadMessages()->pluck('thread_id'));
            })
            ->get();
    }

    /**
     * Returns the user's starred threads.
     *
     * @return int
     */
    public function starred(): HasManyThrough
    {
        return $this->hasManyThrough(
            Models::table('threads'),
            Models::table('participants'),
            'thread_id',
            'user_id',
            'id',
            'id'
        );
    }

    /**
     * Returns the starred threads. An alias of starred
     *
     * @return int
     */
    public function favourites(): int
    {
        return $this->starred();
    }

    /**
     * Get name to use. Should be overridden in model to reflect your project
     *
     * @return string $name
     */
    public function getNameAttribute(): string
    {
        $this->attributes['full_name'] = $this->full_name;
        if ($this->attributes['first_name']) {
            return $this->attributes['first_name'];
        }

        if ($this->username) {
            return $this->username;
        }

        if ($this->first_name) {
            return $this->first_name;
        }

        // if none is found, just return the email
        return $this->email;
    }
}
