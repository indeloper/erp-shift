<?php

namespace App\Events;

use App\Models\Notification;
use Illuminate\Broadcasting\Channel;
use Illuminate\Queue\SerializesModels;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Support\Facades\Auth;
use Telegram\Bot\Laravel\Facades\Telegram;
use App\Models\User;

class NotificationCreated implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $target_user;
    public $text;
    public $notifications;
    public $type;
    public $notification_id;

    /**
     * Create a new event instance.
     *
     * @return void
     */
    public function __construct(string $text = 'Стандартное сообщение', $userId = false, $type = 3, $notification_id = null)
    {
        $this->text = $text;
        $this->type = $type;
        $this->target_user = $userId;
        $this->notification_id = $notification_id;

        $this->notifications = Notification::where('is_seen', 0)
            ->where('is_deleted', 0)
            ->where('is_showing', 1)
            ->where('user_id', $userId)->count();
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return \Illuminate\Broadcasting\Channel|array
     */
    public function broadcastOn()
    {
        return new Channel(config('app.env') . '.App.User.'. $this->target_user);
    }
}
