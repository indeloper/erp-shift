<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Queue\SerializesModels;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;

class MessageStored implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $messageData, $recipientID, $messagesCount;

    /**
     * Create a new event instance.
     *
     * @return void
     */
    public function __construct(int $recipientID, array $messageData, int $messagesCount = 0)
    {
        $this->recipientID = $recipientID;
        $this->messageData = $messageData;
        $this->messagesCount = $messagesCount;
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return \Illuminate\Broadcasting\Channel|array
     */
    public function broadcastOn()
    {
        return new Channel(config('app.env') . '.App.User.'. $this->recipientID);
    }

    public function broadcastAs()
    {
        return 'message-stored';
    }
}
