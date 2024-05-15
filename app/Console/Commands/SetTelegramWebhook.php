<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class SetTelegramWebhook extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'telegram:setWebhook';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'set / remove telegram webhook';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle(): void
    {
        $operationType = $this->choice('Что требуется сделать?', [1 => 'Установить webhook', 2 => 'Удалить webhook']);

        $webhookUrl = config('app.url').'/api/telegram/'.config('telegram.internal_bot_token');

        $telegramUrl = 'https://api.telegram.org/bot'.config('telegram.bot_token').'/setWebhook?url=';

        if ($operationType === 'Установить webhook') {
            $telegramUrl = $telegramUrl.$webhookUrl;
        }

        $ch = curl_init($telegramUrl);

        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_HEADER, false);

        $response = json_decode(curl_exec($ch));
        curl_close($ch);

        foreach ($response as $key => $value) {
            echo PHP_EOL.$key.': '.$value;
        }

        echo PHP_EOL.PHP_EOL.PHP_EOL;
    }
}
