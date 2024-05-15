<?php

namespace App\Jobs;

use App\Actions\Fuel\FuelActions;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class TelegramApiRequest implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    protected $url;

    protected $data;

    protected $options;

    public function __construct($url, $data, $options = [])
    {
        // $data['chat_id'] = '324921539'; // Сергей
        // $data['chat_id'] = '563513336'; // Антон
        $this->url = $url;
        $this->data = $data;
        $this->options = $options;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle(): void
    {
        $ch = curl_init($this->url);
        curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type:application/json']);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($this->data, JSON_UNESCAPED_UNICODE));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_HEADER, false);
        $response = json_decode(curl_exec($ch));

        if (isset($this->options['tankId'])) {
            (new FuelActions)->storeFuelTankChatMessageTmp(
                $this->options['tankId'],
                $this->data['chat_id'],
                $this->data['text'],
                $response->result->message_id
            );
        }
    }
}
