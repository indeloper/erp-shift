<?php

namespace App\Console\Commands\Bitrix;

use App\Domain\Enum\Bitrix\BitrixSyncDirectionType;
use App\Domain\Enum\Bitrix\BitrixSyncType;
use App\Services\Bitrix\BitrixSyncServiceInterface;
use Illuminate\Console\Command;

use function Laravel\Prompts\progress;
use function Laravel\Prompts\select;

class BitrixSyncCommand extends Command
{

    public function __construct(
        private BitrixSyncServiceInterface $bitrixSyncService,
    ) {
        parent::__construct();
    }

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'bitrix:sync';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $type = select(
            label: 'Что хотите синхронизировать?',
            options: [
                BitrixSyncType::Company->value => BitrixSyncType::Company->name(),
            ],
            default: BitrixSyncType::Company->value,
        );

        $direction = select(
            label: 'Выберите направление синхронизации',
            options: [
                BitrixSyncDirectionType::Bitrix->value => BitrixSyncDirectionType::Bitrix->name(),
                BitrixSyncDirectionType::Erp->value    => BitrixSyncDirectionType::Erp->name(),
            ],
            default: BitrixSyncDirectionType::Bitrix->value,
        );

        $handler = $this->bitrixSyncService->defineSyncHandler(
            BitrixSyncType::from($type),
            BitrixSyncDirectionType::from($direction),
        );

        progress(
            label: 'Синхронизация данных',
            steps: $handler->collection(),
            callback: fn($item) => $handler->sync($item),
        );
    }

}
