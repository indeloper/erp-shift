<?php

namespace App\Models\Messenger;

use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model as Eloquent;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Lexx\ChatMessenger\Models\Message;

class Thread extends Eloquent
{
    use SoftDeletes;

    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'threads';

    /**
     * The attributes that can be set with Mass Assignment.
     *
     * @var array
     */
    protected $fillable = ['subject', 'creator_id', 'start_date', 'end_date', 'max_participants', 'avatar'];

    /**
     * Internal cache for creator.
     *
     * @var null|Models::user()
     */
    protected $creatorCache = null;

    /**
     * {@inheritDoc}
     */
    public function __construct(array $attributes = [])
    {
        $this->table = Models::table('threads');

        parent::__construct($attributes);
    }

    /**
     * Messages relationship.
     *
     *
     * @codeCoverageIgnore
     */
    public function messages(): HasMany
    {
        return $this->hasMany(Models::classname(Message::class), 'thread_id', 'id');
    }

    /**
     * Returns the latest message from a thread.
     */
    public function getLatestMessageAttribute(): ?Message
    {
        return $this->messages()->latest()->first();
    }

    /**
     * Participants relationship.
     *
     *
     * @codeCoverageIgnore
     */
    public function participants(): HasMany
    {
        return $this->hasMany(Models::classname(Participant::class), 'thread_id', 'id');
    }

    /**
     * Participants relationship with trashed.
     *
     *
     * @codeCoverageIgnore
     */
    public function participantsWithTrashed(): HasMany
    {
        return $this->hasMany(Participant::class, 'thread_id', 'id')
            ->withTrashed();
    }

    /**
     * User's relationship without thrashed.
     *
     *
     * @codeCoverageIgnore
     */
    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class, Models::table('participants'), 'thread_id', 'user_id')
            ->where('deleted_at', null);
    }

    /**
     * User's relationship without thrashed.
     *
     *
     * @codeCoverageIgnore
     */
    public function usersWithThrashed(): BelongsToMany
    {
        return $this->belongsToMany(User::class, Models::table('participants'), 'thread_id', 'user_id');
    }

    /**
     * Returns the user object that created the thread.
     *
     * @return Models::user()
     */
    public function creator(): self
    {
        if (! is_null($this->creator_id)) {
            return User::find($this->creator_id);
        } elseif (is_null($this->creatorCache)) {
            $firstMessage = $this->messages()->withTrashed()->oldest()->first();
            $this->creatorCache = $firstMessage ? $firstMessage->user : Models::user();
        }

        return $this->creatorCache;
    }

    /**
     * Returns all of the latest threads by updated_at date.
     *
     * @return \Illuminate\Database\Query\Builder|static
     */
    public static function getAllLatest()
    {
        return static::latest('updated_at');
    }

    /**
     * Returns all threads by subject.
     *
     * @return \Illuminate\Database\Query\Builder|static
     */
    public static function getBySubject(string $subject)
    {
        return static::where('subject', 'like', $subject)->get();
    }

    /**
     * Returns an array of user ids that are associated with the thread.
     * Deleted participants from a thread will not be returned
     *
     * @param  null  $userId
     */
    public function participantsUserIds($userId = null): array
    {
        $users = $this->participants()->select('user_id')->get()->map(function ($participant) {
            return $participant->user_id;
        });

        if ($userId) {
            $users->push($userId);
        }

        return $users->toArray();
    }

    /**
     * Returns an array of user ids that are associated with the thread (including removed participants from a thread).
     *
     * @param  null  $userId
     */
    public function participantsUserIdsWithTrashed($userId = null): array
    {
        $users = $this->participants()->withTrashed()->select('user_id')->get()->map(function ($participant) {
            return $participant->user_id;
        });

        if ($userId) {
            $users->push($userId);
        }

        return $users->toArray();
    }

    /**
     * Returns threads that the user is associated with.
     */
    public function scopeForUser(Builder $query, int $userId): Builder
    {
        $participantsTable = Models::table('participants');
        $threadsTable = Models::table('threads');

        return $query->join($participantsTable, $this->getQualifiedKeyName(), '=', $participantsTable.'.thread_id')
            ->where($participantsTable.'.user_id', $userId)
            ->where($participantsTable.'.deleted_at', null)
            ->select($threadsTable.'.*');
    }

    /**
     * Returns threads that the user is associated with, now with leaved threads.
     */
    public function scopeForUserWithTrashed(Builder $query, int $userId): Builder
    {
        $participantsTable = Models::table('participants');
        $threadsTable = Models::table('threads');

        return $query->join($participantsTable, $this->getQualifiedKeyName(), '=', $participantsTable.'.thread_id')
            ->where($participantsTable.'.user_id', $userId)
            ->select($threadsTable.'.*');
    }

    /**
     * Returns threads that the user is associated with, only leaved threads.
     */
    public function scopeForUserOnlyTrashed(Builder $query, int $userId): Builder
    {
        $participantsTable = Models::table('participants');
        $threadsTable = Models::table('threads');

        return $query->join($participantsTable, $this->getQualifiedKeyName(), '=', $participantsTable.'.thread_id')
            ->where($participantsTable.'.user_id', $userId)
            ->where($participantsTable.'.deleted_at', '!=', null)
            ->select($threadsTable.'.*');
    }

    /**
     * Returns threads with new messages that the user is associated with.
     */
    public function scopeForUserWithNewMessages(Builder $query, int $userId): Builder
    {
        $participantTable = Models::table('participants');
        $threadsTable = Models::table('threads');

        return $query->join($participantTable, $this->getQualifiedKeyName(), '=', $participantTable.'.thread_id')
            ->where($participantTable.'.user_id', $userId)
            ->whereNull($participantTable.'.deleted_at')
            ->where(function (Builder $query) use ($participantTable, $threadsTable) {
                $query->where($threadsTable.'.updated_at', '>', $this->getConnection()->raw($this->getConnection()->getTablePrefix().$participantTable.'.last_read'))
                    ->orWhereNull($participantTable.'.last_read');
            })
            ->select($threadsTable.'.*');
    }

    /**
     * Returns threads between given user ids.
     */
    public function scopeBetween(Builder $query, array $participants): Builder
    {
        return $query->whereHas('participants', function (Builder $q) use ($participants) {
            $q->whereIn('user_id', $participants)
                ->select($this->getConnection()->raw('DISTINCT(thread_id)'))
                ->groupBy('thread_id')
                ->havingRaw('COUNT(thread_id)='.count($participants));
        });
    }

    /**
     * Add users to thread as participants.
     *
     * @param  array|mixed  $userId
     */
    public function addParticipant($userId): bool
    {
        $userIds = is_array($userId) ? $userId : (array) func_get_args();

        return collect($userIds)->each(function ($userId) {
            $participant = Participant::withTrashed()->firstOrCreate([
                'user_id' => $userId,
                'thread_id' => $this->id,
            ]);
            if ($participant->trashed()) {
                $participant->restore();
            }
        });
    }

    /**
     * Remove participants from thread.
     *
     * @param  array|mixed  $userId
     */
    public function removeParticipant($userId): bool
    {
        $userIds = is_array($userId) ? $userId : (array) func_get_args();

        return Models::participant()->where('thread_id', $this->id)->whereIn('user_id', $userIds)->delete();
    }

    /**
     * Mark a thread as read for a user.
     */
    public function markAsRead(int $userId): void
    {
        try {
            $participant = $this->getParticipantFromUser($userId);
            $participant->last_read = new Carbon();
            $participant->save();
        } catch (ModelNotFoundException $e) { // @codeCoverageIgnore
            // do nothing
        }
    }

    /**
     * See if the current thread is unread by the user.
     */
    public function isUnread(int $userId): bool
    {
        try {
            $participant = $this->getParticipantFromUser($userId);

            if ($participant->last_read === null || $this->updated_at->gt($participant->last_read)) {
                return true;
            }
        } catch (ModelNotFoundException $e) { // @codeCoverageIgnore
            // do nothing
        }

        return false;
    }

    /**
     * Finds the participant record from a user id.
     *
     *
     * @return mixed
     *
     * @throws \Illuminate\Database\Eloquent\ModelNotFoundException
     */
    public function getParticipantFromUser($userId)
    {
        return $this->participants()->where('user_id', $userId)->firstOrFail();
    }

    /**
     * Restores all participants within a thread that has a new message.
     */
    public function activateAllParticipants(): void
    {
        $participants = $this->participants()->withTrashed()->get();
        foreach ($participants as $participant) {
            $participant->restore();
        }
    }

    /**
     * Generates a string of participant information.
     */
    public function participantsString(?int $userId = null, array $columns = []): string
    {
        $participantsTable = Models::table('participants');
        $usersTable = Models::table('users');
        $userPrimaryKey = Models::user()->getKeyName();

        if (empty($columns)) {
            $columns = config('chatmessenger.defaults.participant_aka');
        }

        $selectString = $this->createSelectString($columns);

        $participantNames = $this->getConnection()->table($usersTable)
            ->join($participantsTable, $usersTable.'.'.$userPrimaryKey, '=', $participantsTable.'.user_id')
            ->where($participantsTable.'.thread_id', $this->id)
            ->where($participantsTable.'.deleted_at', null)
            ->select($this->getConnection()->raw($selectString));

        if ($userId !== null) {
            $participantNames->where($usersTable.'.'.$userPrimaryKey, '!=', $userId);
        }

        return $participantNames->implode('name', ', ');
    }

    /**
     * Checks to see if a user is a current participant of the thread.
     */
    public function hasParticipant(int $userId): bool
    {
        $participants = $this->participants()->where('user_id', '=', $userId)->where('deleted_at', null);
        if ($participants->count() > 0) {
            return true;
        }

        return false;
    }

    /**
     * Checks to see if a user is a current participant of the thread with trashed.
     */
    public function hasTrashedParticipant(int $userId): bool
    {
        $participants = $this->participants()->where('user_id', '=', $userId)->withTrashed()->whereNotNull('deleted_at');
        if ($participants->count() > 0) {
            return true;
        }

        return false;
    }

    /**
     * Generates a select string used in participantsString().
     */
    protected function createSelectString(array $columns): string
    {
        $dbDriver = $this->getConnection()->getDriverName();
        $tablePrefix = $this->getConnection()->getTablePrefix();
        $usersTable = Models::table('users');

        switch ($dbDriver) {
            case 'pgsql':
            case 'sqlite':
                $columnString = implode(" || ' ' || ".$tablePrefix.$usersTable.'.', $columns);
                $selectString = '('.$tablePrefix.$usersTable.'.'.$columnString.') as name';
                break;
            case 'sqlsrv':
                $columnString = implode(" + ' ' + ".$tablePrefix.$usersTable.'.', $columns);
                $selectString = '('.$tablePrefix.$usersTable.'.'.$columnString.') as name';
                break;
            default:
                $columnString = implode(", ' ', ".$tablePrefix.$usersTable.'.', $columns);
                $selectString = 'concat('.$tablePrefix.$usersTable.'.'.$columnString.') as name';
        }

        return $selectString;
    }

    /**
     * Returns array of unread messages in thread for given user.
     */
    public function userUnreadMessages(int $userId): Collection
    {
        $messages = $this->messages()->get();

        try {
            $participant = $this->getParticipantFromUser($userId);
        } catch (ModelNotFoundException $e) {
            return collect();
        }

        if (! $participant->last_read) {
            return $messages;
        }

        return $messages->filter(function ($message) use ($participant) {
            return $message->updated_at->gt($participant->last_read);
        });
    }

    /**
     * Returns count of unread messages in thread for given user.
     */
    public function userUnreadMessagesCount(int $userId): int
    {
        return $this->userUnreadMessages($userId)->count();
    }

    /**
     * Returns the max number of participants allowed in a thread.
     */
    public function getMaxParticipants(): int
    {
        return $this->max_participants;
    }

    /**
     * Checks if the max number of participants in a thread has been reached.
     */
    public function hasMaxParticipants(): bool
    {
        $participants = $this->participants();
        if ($participants->count() > $this->max_participants) {
            // max number of participants reached
            return true;
        }

        return false;
    }

    /**
     * star/favourite a thread
     *
     * @param  null  $userId
     * @return mixed
     */
    public function star($userId = null)
    {
        if (! $userId) {
            $userId = Auth::id();
        }

        return $this->participants()
            ->where('user_id', $userId)
            ->firstOrFail()
            ->update(['starred' => true]);
    }

    /**
     * unstar/unfavourite a thread
     *
     * @param  null  $userId
     * @return mixed
     */
    public function unstar($userId = null)
    {
        if (! $userId) {
            $userId = Auth::id();
        }

        return $this->participants()
            ->where('user_id', $userId)
            ->firstOrFail()
            ->update(['starred' => false]);
    }

    /**
     * check if the thread has been starred
     *
     * @param  null  $userId
     */
    public function getIsStarredAttribute($userId = null): bool
    {
        if (! $userId) {
            $userId = Auth::id();
        }

        return (bool) $this->participants()
            ->where('user_id', $userId)
            ->firstOrFail()
            ->starred;
    }
}
