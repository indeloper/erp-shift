<?php

namespace App\Http\Controllers\Common;

use Illuminate\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use App\Domain\DTO\Notification\NotificationSettingsData;
use App\Domain\DTO\Notification\NotificationSortData;
use App\Http\Controllers\Controller;
use App\Http\Requests\Notification\DeleteNotificationRequest;
use App\Http\Requests\Notification\NotificationRequest;
use App\Http\Requests\Notification\NotificationSettingsItemsRequest;
use App\Http\Requests\Notification\ViewNotificationRequest;
use App\Http\Resources\Notification\NotificationItemResource;
use App\Http\Resources\Notification\NotificationResource;
use App\Services\Notification\NotificationServiceInterface;
use App\Services\NotificationItem\NotificationItemServiceInterface;
use App\Services\System\NotificationService;
use Illuminate\Support\Facades\DB;

use function auth;

class NotificationController extends Controller
{
    private $notificationService;

    private $notificationItemService;

    public function __construct(
        NotificationServiceInterface $notificationService,
        NotificationItemServiceInterface $notificationItemService
    ) {
        $this->notificationService = $notificationService;
        $this->notificationItemService = $notificationItemService;
    }

    public function index(): View
    {
        return view('notifications.index');
    }

    public function loadNotifications(NotificationRequest $request)
    {
        return NotificationResource::collection(
            $this->notificationService->getNotifications(
                auth()->id(),
                new NotificationSortData($request->get('sort_selector'),
                    $request->get('sort_direction', 'asc'))
            )
        );
    }

    public function delete(DeleteNotificationRequest $request): JsonResponse
    {
        $this->notificationService->delete(
            $request->get('notify_id')
        );

        return response()->json(true);
    }

    public function view(ViewNotificationRequest $request): JsonResponse
    {
        $this->notificationService->view(
            $request->get('notify_id')
        );

        return response()->json(true);
    }

    public function items()
    {
        return NotificationItemResource::collection(
            $this->notificationItemService->getNotificationItems(
                auth()->id()
            )
        );
    }

    public function settings(NotificationSettingsItemsRequest $request)
    {
        $this->notificationItemService->settings(
            auth()->id(),
            new NotificationSettingsData(
                $request->get('items', [])
            )
        );
    }

    public function viewAll(): JsonResponse
    {
        $this->notificationService
            ->viewAll(auth()->id());

        return response()->json(true);
    }

    public function redirect($encoded_url): RedirectResponse
    {
        DB::beginTransaction();
        $url = (new NotificationService())->decodeNotificationUrl($encoded_url);
        DB::commit();

        return redirect($url);
    }
}
