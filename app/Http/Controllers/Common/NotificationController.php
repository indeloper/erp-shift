<?php

namespace App\Http\Controllers\Common;

use App\Http\Controllers\Controller;
use App\Models\Notification;
use App\Models\Notifications\NotificationTypes;
use App\Services\System\NotificationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class NotificationController extends Controller
{
    public function index()
    {
        $notifications = Notification::orderBy('is_seen', 'asc')
            ->orderBy('created_at', 'desc')
            ->with('task', 'wv_request', 'wv_request.wv', 'co_request', 'co_request.co')
            ->leftjoin('project_objects', 'project_objects.id', '=', 'notifications.object_id')
            ->leftjoin('contractors', 'contractors.id', '=', 'notifications.contractor_id')
            ->where('notifications.user_id', Auth::user()->id)
            ->where('is_deleted', 0);

        if (auth()->user()->disabledInSystemNotifications()->isNotEmpty()) {
            $notifications->whereRaw('CASE WHEN is_showing = 1 AND type IN ('.
                implode(',', auth()->user()->disabledInSystemNotifications()->pluck('notification_id')->toArray()) .')
                THEN 0 ELSE is_showing = 1 END');
        } else {
            $notifications->where('is_showing', 1);
        }

        $notifications->select('notifications.*', 'project_objects.address', 'contractors.short_name');

        return view('notifications.index', [
//            'notifications' => $notifications->paginate(20),
            'notification_types' => NotificationTypes::whereIn('id', auth()->user()->allowedNotifications())->get(),
            'disabled_in_system' => auth()->user()->disabledInSystemNotifications()->pluck('notification_id')->toArray(),
            'disabled_in_telegram' => auth()->user()->disabledInTelegramNotifications()->pluck('notification_id')->toArray(),
        ]);
    }


    public function delete(Request $request)
    {
        Notification::findOrFail($request->notify_id)->update(['is_deleted' => 1]);

        return \GuzzleHttp\json_encode(true);
    }

    public function view(Request $request)
    {
        Notification::findOrFail($request->notify_id)->update(['is_seen' => 1]);

        return \GuzzleHttp\json_encode(true);
    }

    public function view_all()
    {
        Notification::where('user_id', Auth::user()->id)->update(['is_seen' => 1]);

        return back();
    }

    public function redirect($encoded_url)
    {
        DB::beginTransaction();
        $url = (new NotificationService())->decodeNotificationUrl($encoded_url);
        DB::commit();
        return redirect($url);
    }
}
