<?php

namespace App\Jobs\Notification;

use App\Domain\DTO\Notification\NotificationData;
use App\Services\Notification\NotificationServiceInterface;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class NotificationJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    private $notificationData;
    private $notificationService;

    public $tries = 1;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(
        NotificationData $data
    )
    {
        $this->notificationData = $data;

        $notificationService = app(NotificationServiceInterface::class);

        $this->notificationService = $notificationService;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $this->notificationService->sendNotify(
            $this->notificationData
        );
    }
}
