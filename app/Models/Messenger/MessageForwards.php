<?php

namespace App\Models\Messenger;

use App\Model\Messenger\Message;
use Illuminate\Database\Eloquent\Model;

class MessageForwards extends Model
{
    protected $table = 'message_forwards';

    protected $fillable = [
        'message_id',
        'forwarded_message_id',
    ];

    // message relation
    public function message()
    {
        return $this->belongsTo(Message::class, 'message_id', 'id');
    }

    // forwarder/replied message relation
    public function forwarded_message()
    {
        return $this->belongsTo(Message::class, 'forwarded_message_id', 'id');
    }

}
