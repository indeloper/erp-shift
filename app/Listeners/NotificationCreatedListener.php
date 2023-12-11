<?php

namespace App\Listeners;

use App\Events\NotificationCreated;
use App\Models\User;
use App\Services\System\NotificationService;
use App\Telegram\TelegramApi;
use App\Telegram\TelegramServices;
use Exception;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;
use Telegram\Bot\Laravel\Facades\Telegram;

class NotificationCreatedListener
{
    /**
     * Create the event listener.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event -> send Message In Telegram.
     *
     * @param  NotificationCreated  $notificationCreated
     * @return void
     */
    public function handle(NotificationCreated $notificationCreated)
    {
        $user = User::findOrFail($notificationCreated->target_user != 0 ? $notificationCreated->target_user : auth()->id());
        $userChatId = $user->chat_id;
        $text = $notificationCreated->text;
        $type = $notificationCreated->type ?? 0;
        if (config('app.env') == 'local_dev' and config('app.notifications_dumping') == true) {
            // for debug
            dump($text, $notificationCreated->type);
            $this->userHasChatIdAndAllowThisNotification($user, $type) ? dump('need send ', $user->id) : dump('no need send ', $user->id);
        }

        $text = (new NotificationService())->replaceUrl($text, $notificationCreated->notification_id);

        $telegramServices = new TelegramServices;
        $telegramNotificationTemplate = (new TelegramServices())->defineNotificationTemplate($text);
        $messageParams = [];
        if(!$telegramNotificationTemplate && str_contains($text, 'notificationHook')) {
            $messageParams = ['text' => $telegramServices->setNotificationHookLink($text)];
        }
        else {
            $notificationTemplateClassMethod = $telegramServices->defineNotificationTemplateClassMethod($telegramNotificationTemplate);

            if (count($notificationTemplateClassMethod)) {
                $notificationTemlateClass = new $notificationTemplateClassMethod['class']();
                $notificationTemlateMethod = $notificationTemplateClassMethod['method'];

                $messageParams = (new $notificationTemlateClass)->$notificationTemlateMethod($text);
            }
        }

        if (!count($messageParams)) {
            $messageParams['text'] = $text;
        }

        $message = [
            'chat_id' => $userChatId,
            'parse_mode' => 'HTML',
        ];

        if(!empty($messageParams['text'])) {
            $message['text'] = $messageParams['text'];
        }

        if(!empty($messageParams['reply_markup'])) {
            $message['reply_markup'] = $messageParams['reply_markup'];
        }

        try {
            if ($this->appInProduction() and $this->userHasChatIdAndAllowThisNotification($user, $type) and $this->userIsActive($user)) {
                new TelegramApi('sendMessage', $message);
            }
        } catch (\Throwable $e) {
            try {
                if (auth()->check() and $this->appInProduction()) {
                    if ($e instanceof ValidationException) {
                        $text = $this->reportValidationErrors($e);
                    } else {
                        $text = $this->createErrorMessage($e);
                    }

                    $message = [
                        'chat_id' => config('app.env') == 'production' ? '-1001505547789' : '-1001558926749',
                        'parse_mode' => 'HTML',
                        'text' => $text
                    ];

                    new TelegramApi('sendMessage', $message);

                    // Telegram::sendMessage([
                    //     'chat_id' => config('app.env') == 'production' ? '-1001505547789' : '-1001558926749',
                    //     'parse_mode' => 'HTML',
                    //     'text' => $text
                    // ]);
                }
            } catch (\Throwable $e) {
                $message = [
                    'chat_id' => '-1001558926749',
                    'parse_mode' => 'HTML',
                    'text' => $userChatId
                ];

                new TelegramApi('sendMessage', $message);

                // Telegram::sendMessage([
                //     'chat_id' => '-1001558926749',
                //     'parse_mode' => 'HTML',
                //     'text' => $userChatId
                // ]);
            }
        }
    }

    public function appInProduction(): bool
    {
        return in_array(config('app.env'),  ['production', 'local']);
    }

    public function userHasChatIdAndAllowThisNotification(User $user, int $type): bool
    {
        return $user->chat_id and $user->checkIfNotifyNotDisabledInTelegram($type);
    }

    public function userIsActive(User $user): bool
    {
        return $user->status == 1 and
            $user->is_deleted == 0;
    }

    public function reportValidationErrors(ValidationException $exception)
    {
        $user = auth()->user();
        $errorsBag = $exception->errors();
        $error = $exception->getMessage() ? 'Ошибка: ' . $exception->getMessage() : 'Неизвестная ошибка.';
        $exceptionClass = 'Класс ошибки - ' . get_class($exception) . '.';
        $file = $exception->getFile() ? 'В файле: ' .
            preg_replace('/\/var\/www\/html/', '', $exception->getFile()) : '';
        $line = $exception->getLine() ? 'На строке ' . $exception->getLine() . '.' : '';

        $general_info = 'Пользователь ' . $user->long_full_name .
            ' с id ' . $user->id . ' не прошёл валидацию на стороне сервера.'.PHP_EOL.
            'URL: ' . request()->fullUrl() . '. Метод: ' . request()->method() . '.' .PHP_EOL;

        $data = '';
        foreach (request()->all() as $name => $value) {
            if ($name != 'password' && $name != 'password_confirmation' && is_string($name) && is_string($value)) {
                $data .= $name . ' => ' . (is_array($value) ? implode(' ', $value) : $value) . PHP_EOL;
            }
        }

        $dataInfo = ($data != '') ? ('Переданные данные: '. PHP_EOL . $data) : '';

        $fileErrors = '';
        foreach (request()->allFiles() as $name => $files) {
            $fileErrors .= $name . ' => [' . PHP_EOL;
            foreach ($files as $key => $file) {
                $fileErrors .= "{$key} -> Название: {$file->getClientOriginalName()}." .
                    " Расширение: {$file->getClientOriginalExtension()}." .
                    " MIME-тип: {$file->getMimeType()}. Размер: {$file->getSize()} байт(ов)." . PHP_EOL;
            }

            $fileErrors .= ']';
        }

        $filesInfo = ($fileErrors != '') ? ('Переданные файлы: '. PHP_EOL . $fileErrors) : '';

        if ($errorsBag) {
            $errors = '';
            $fails = $errorsBag;
            foreach ($fails as $name => $fail) {
                $errors .= $name . ' => ' . implode($fail).PHP_EOL;
            }
        } else {
            $errors = 'Отсутствуют';
        }

        $error_bag = 'Ошибки валидации: '.PHP_EOL. $errors;

        $final = $error . PHP_EOL . $exceptionClass . PHP_EOL . $file . PHP_EOL . $line .
            PHP_EOL . $general_info . $dataInfo . PHP_EOL . $filesInfo. PHP_EOL . PHP_EOL .$error_bag;

        return $final;
    }

    public function createErrorMessage(Exception $exception): string
    {
        $data = '';
        foreach (request()->all() as $name => $value) {
            if ($name != 'password' && $name != 'password_confirmation' && is_string($name) && is_string($value)) {
                $data .= $name . ' => ' . (is_array($value) ? implode(' ', $value) : $value) . PHP_EOL;
            }
        }

        $error = $exception->getMessage() ? 'Ошибка: ' . $exception->getMessage() : 'Неизвестная ошибка.';
        $exceptionClass = 'Класс ошибки - ' . get_class($exception) . '.';
        $file = $exception->getFile() ? 'В файле: ' .
            preg_replace('/\/var\/www\/html/', '', $exception->getFile()) : '';
        $line = $exception->getLine() ? 'На строке ' . $exception->getLine() . '.' : '';
        $path = request()->path() ? 'На странице: ' . request()->path() . '.' : '';
        $userInfo = (Auth::user() ? 'Пользователь: ' . Auth::user()->full_name : 'Нет пользователя') .
            ', ip - ' . request()->ip();
        $dataInfo = ($data != '') ? ('Переданные данные: ' . PHP_EOL . $data) : '';
        $text = $error . PHP_EOL . $exceptionClass . PHP_EOL . $file . PHP_EOL . $line .
            PHP_EOL . $path . PHP_EOL . $userInfo . PHP_EOL . $dataInfo;

        return $text;
    }

    public function sendTelegramMessage($message)
    {
        $ch = curl_init('https://api.telegram.org/bot' . config('telegram.bot_token') . '/sendMessage');
        curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type:application/json'));
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($message, JSON_UNESCAPED_UNICODE));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_HEADER, false);
        $res = curl_exec($ch);
        curl_close($ch);

        $res = json_encode($res, JSON_UNESCAPED_UNICODE);
    }
}
