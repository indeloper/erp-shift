<?php


namespace App\Services\System;


use App\Models\Notification;
use App\Models\Notifications\TgNotificationUrl;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;
use Exception;
use Telegram\Bot\Laravel\Facades\Telegram;

class NotificationService
{
    /**
     * @param $notification_id
     * @param $url
     * @return string
     */
    public function encodeNotificationUrl($notification_id, $url)
    {
        $tg_url = TgNotificationUrl::create([
            'target_url' => $url,
            'notification_id' => $notification_id,
        ]);
        $encoded_url = substr(md5($tg_url->id), 0, 16);

        if (TgNotificationUrl::where('encoded_url', $encoded_url)->exists()) {
            $encoded_url = $this->encodeBetter();
        }

        $tg_url->encoded_url = $encoded_url;
        $tg_url->save();

        return route('notifications::redirect', $encoded_url);
    }

    private function encodeBetter()
    {
        do {
            $encoded_url = substr(md5(hrtime(true)), 0, 16);
        } while (TgNotificationUrl::where('encoded_url', $encoded_url)->exists());

        return $encoded_url;
    }

    /**
     * @param $encoded_url
     * @return mixed
     */
    public function decodeNotificationUrl($encoded_url)
    {
        $url = TgNotificationUrl::where('encoded_url', $encoded_url)->firstOrFail();
        Notification::findOrFail($url->notification_id)->update(['is_seen' => 1]);

        return $url->target_url;
    }

    public function replaceUrl($message, $notification_id)
    {
        if (!$notification_id) return $message;
        try {
            $predicted_url = mb_stristr($message, 'http');
            if ($predicted_url === false) return $message;

            if (mb_stripos($predicted_url, ' ') !== false) {
                $predicted_url = substr($predicted_url, 0, mb_stripos($predicted_url, ' '));
            }


            $exploded_message = explode($predicted_url, $message);
            $encoded_url = $this->encodeNotificationUrl($notification_id, $predicted_url);
            $new_message = implode($encoded_url, $exploded_message);

            return $new_message;

        } catch (\Throwable $e) {
            if ($e instanceof ValidationException) {
                $text = $this->reportValidationErrors($e);
            } else {
                $text = $this->createErrorMessage($e);
            }
            try {
                Telegram::sendMessage([
                    'chat_id' => config('app.env') == 'production' ? '-1001481434440' : '-1001245014814',
                    'text' => $text
                ]);
            } catch (\Throwable $t) {
                // unsuccess
            }
            return $message;
        }
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

}
