<?php

declare(strict_types=1);

namespace App\Repositories\Notification;

use App\Domain\DTO\NotificationData;
use App\Domain\DTO\NotificationSortData;
use App\Domain\Enum\NotificationSortType;
use App\Models\Notification;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;

final class NotificationRepository implements NotificationRepositoryInterface
{
    public function create(NotificationData $data): Notification
    {
        $fillable = (new Notification)->getFillable();

        $notificationData = collect($data->getData())
            ->only($fillable)->toArray();

        return Notification::query()->create(
            array_merge(
                [
                    'user_id' => $data->getUserId(),
                    'name' => $data->getName(),
                    'description' => $data->getDescription(),
                    'type' => $data->getType(),
                ],
                $notificationData
            )
        );
    }

    public function getNotifications(
        int $userId,
        NotificationSortData $sort,
        int $parPage = 20
    ): LengthAwarePaginator {
        return Notification::query()
            ->with(['object', 'contractor'])
            ->where('notifications.user_id', $userId)
            ->where('notifications.is_deleted', false)
            ->leftJoin('project_objects', 'notifications.object_id', '=', 'project_objects.id')
            ->leftJoin('contractors', 'notifications.contractor_id', '=', 'contractors.id')
            ->select(['notifications.*', 'project_objects.address', 'contractors.short_name'])
            ->when($sort->getSelector() !== null, function (Builder $query) use ($sort) {

                $query->when($sort->getSelector() === NotificationSortType::NAME,
                    function (Builder $query) use ($sort) {
                        $query->orderBy('name', $sort->getDirection());
                    });

                $query->when($sort->getSelector() === NotificationSortType::DATA,
                    function (Builder $query) use ($sort) {
                        $query->orderBy('name', $sort->getDirection());
                    });

                $query->when($sort->getSelector() === NotificationSortType::OBJECT_ADDRESS,
                    function (Builder $query) use ($sort) {
                    $query->orderBy('project_objects.address', $sort->getDirection());
                });

                $query->when($sort->getSelector() === NotificationSortType::CONTRACTOR_SHORT_NAME,
                    function (Builder $query) use ($sort) {
                        $query->orderBy(DB::raw("contractors.short_name"), $sort->getDirection());
                    });
            })
            ->when($sort->getSelector() === null, function (Builder $query) {
                $query->latest();
            })
            ->paginate($parPage);
    }

    public function delete(int $idNotify): void
    {
        Notification::query()
            ->where('id', $idNotify)
            ->update([
                'is_deleted' => true
            ]);
    }

    public function view(int $idNotify): void
    {
        Notification::query()
            ->where('id', $idNotify)
            ->update([
                'is_seen' => true
            ]);
    }

    public function viewAll(int $id): void
    {
        Notification::query()
            ->where('user_id', $id)
            ->update([
                'is_seen' => true
            ]);
    }

}