<?php

namespace App\Models\Messenger;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Model;

class MessageForwards extends Model
{
    protected $table = 'message_forwards';

    protected $fillable = [
        'message_id',
        'forwarded_message_id',
    ];

    // message relation
    public function message(): BelongsTo
    {
        return $this->belongsTo(Message::class, 'message_id', 'id');
    }

    // forwarder/replied message relation
    public function forwarded_message(): BelongsTo
    {
        return $this->belongsTo(Message::class, 'forwarded_message_id', 'id');
    }
}
