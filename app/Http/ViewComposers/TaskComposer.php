<?php

namespace App\Http\ViewComposers;

use App\Models\Notification\Notification;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class TaskComposer
{
    protected $users;

    public function __construct()
    {
        $notifications = Notification::where('is_seen', 0)
            ->where('is_deleted', 0)
            ->where('user_id', auth()->id());

        if (auth()->user()->disabledInSystemNotifications()->isNotEmpty()) {
            $notifications->whereRaw('CASE WHEN is_showing = 1 AND type IN ('.
                implode(',', auth()->user()->disabledInSystemNotifications()->pluck('notification_id')->toArray()) .')
                THEN 0 ELSE is_showing = 1 END');
        } else {
            $notifications->where('is_showing', 1);
        }

        $this->messages = Auth::user()->unreadMessagesCount();

        $this->notifications = $notifications->count();
    }

    public function compose(View $view)
    {
        $view->with(['notifications' => $this->notifications, 'messages' => $this->messages]);
    }
}
