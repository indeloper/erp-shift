<?php

namespace App\Models\Messenger;

use Illuminate\Database\Eloquent\Model as Eloquent;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class Participant extends Eloquent
{
    use SoftDeletes;

    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'participants';

    /**
     * The attributes that can be set with Mass Assignment.
     *
     * @var array
     */
    protected $fillable = ['thread_id', 'user_id', 'last_read', 'starred'];

    /**
     * {@inheritDoc}
     */
    public function __construct(array $attributes = [])
    {
        $this->table = Models::table('participants');

        parent::__construct($attributes);
    }

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'last_read' => 'datetime',
            'starred' => 'boolean',
        ];
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
}
