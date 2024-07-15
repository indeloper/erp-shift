<?php

namespace App\Telegram;

use App\Jobs\TelegramApiRequest;

class TelegramApi
{
    protected $method;

    protected $data;

    protected $options;

    public function __construct($method, $data, $options = [])
    {
        $this->method = $method;
        $this->data = $data;
        $this->options = $options;
        $this->handle();
    }

    public function handle()
    {
        $url = 'https://api.telegram.org/bot'.config('telegram.bot_token').'/'.$this->method;
        dispatch(new TelegramApiRequest($url, $this->data, $this->options));
    }
}
