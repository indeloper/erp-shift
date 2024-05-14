<?php

namespace App\Exceptions;

use App\Models\MatAcc\MaterialAccountingOperation;
use App\Telegram\TelegramApi;
use Exception;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Illuminate\Session\TokenMismatchException;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;
use Telegram\Bot\Laravel\Facades\Telegram;

class Handler extends ExceptionHandler
{
    /**
     * A list of the exception types that are not reported.
     *
     * @var array
     */
    protected $dontReport = [
        //
    ];

    /**
     * A list of the inputs that are never flashed for validation exceptions.
     *
     * @var array
     */
    protected $dontFlash = [
        'password',
        'password_confirmation',
    ];

    /**
     * Report or log an exception.
     *
     * @return void
     *
     * @throws Exception
     */
    public function report(Exception $exception)
    {
        if (auth()->check() and $this->isDeployedEnvironment()) {
            if ($exception instanceof ValidationException) {
                $text = $this->reportValidationErrors($exception);
            } elseif ($exception instanceof ModelNotFoundException) {/* nothing */
            } elseif ($exception instanceof TokenMismatchException) {/* nothing */
            } else {
                $text = mb_substr($this->createErrorMessage($exception), 0, 4096);
            }

            if (isset($text)) {
                $message = [
                    'chat_id' => config('app.env') == 'production' ? '-1001505547789' : '-1001558926749',
                    'parse_mode' => 'HTML',
                    'text' => $text,
                ];

                new TelegramApi('sendMessage', $message);

                /*Telegram::sendMessage([
                    'chat_id' => config('app.env') == 'production' ? '-1001505547789' : '-1001558926749',
                    'parse_mode' => 'HTML',
                    'text' => $text
                ]);*/
            }
        }

        parent::report($exception);
    }

    /**
     * Render an exception into an HTTP response.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function render($request, Exception $exception)
    {

        if (auth()->check()) {
            if ($exception instanceof ModelNotFoundException and $exception->getModel() === MaterialAccountingOperation::class) {
                $operations = MaterialAccountingOperation::find($exception->getIds());
                if ($operations->isNotEmpty()) {
                    return redirect()->action('\App\Http\Controllers\Building\MaterialAccounting\MaterialAccountingController@redirector', ['operation_id' => $operations->first()->id]);
                }
            }
        }

        return parent::render($request, $exception);
    }

    protected function whoopsHandler()
    {
        try {
            return app(\Whoops\Handler\HandlerInterface::class);
        } catch (\Illuminate\Contracts\Container\BindingResolutionException $e) {
            return parent::whoopsHandler();
        }
    }

    public function isDeployedEnvironment(): bool
    {
        return in_array(config('app.env'), ['production', 'local']);
    }

    public function reportValidationErrors(ValidationException $exception)
    {
        $user = auth()->user();
        $errorsBag = $exception->errors();
        $error = $exception->getMessage() ? 'Ошибка: '.$exception->getMessage() : 'Неизвестная ошибка.';
        $exceptionClass = 'Класс ошибки - '.get_class($exception).'.';
        $file = $exception->getFile() ? 'В файле: '.
            preg_replace('/\/var\/www\/html/', '', $exception->getFile()) : '';
        $line = $exception->getLine() ? 'На строке '.$exception->getLine().'.' : '';

        $general_info = 'Пользователь '.$user->long_full_name.
            ' с id '.$user->id.' не прошёл валидацию на стороне сервера.'.PHP_EOL.
            'URL: '.request()->fullUrl().'. Метод: '.request()->method().'.'.PHP_EOL;

        $data = '';
        foreach (request()->all() as $name => $value) {
            if ($name != 'password' && $name != 'password_confirmation' && is_string($name) && is_string($value)) {
                $data .= $name.' => '.(is_array($value) ? implode(' ', $value) : $value).PHP_EOL;
            }
        }

        $dataInfo = ($data != '') ? ('Переданные данные: '.PHP_EOL.$data) : '';

        $fileErrors = '';
        foreach (request()->allFiles() as $name => $files) {
            $fileErrors .= $name.' => ['.PHP_EOL;
            foreach ($files as $key => $file) {
                $fileErrors .= "{$key} -> Название: {$file->getClientOriginalName()}.".
                    " Расширение: {$file->getClientOriginalExtension()}.".
                    " MIME-тип: {$file->getMimeType()}. Размер: {$file->getSize()} байт(ов).".PHP_EOL;
            }

            $fileErrors .= ']';
        }

        $filesInfo = ($fileErrors != '') ? ('Переданные файлы: '.PHP_EOL.$fileErrors) : '';

        if ($errorsBag) {
            $errors = '';
            $fails = $errorsBag;
            foreach ($fails as $name => $fail) {
                $errors .= $name.' => '.implode($fail).PHP_EOL;
            }
        } else {
            $errors = 'Отсутствуют';
        }

        $error_bag = 'Ошибки валидации: '.PHP_EOL.$errors;

        $final = $error.PHP_EOL.$exceptionClass.PHP_EOL.$file.PHP_EOL.$line.
            PHP_EOL.$general_info.$dataInfo.PHP_EOL.$filesInfo.PHP_EOL.PHP_EOL.$error_bag;

        return $final;
    }

    public function createErrorMessage(Exception $exception): string
    {
        $data = '';
        foreach (request()->all() as $name => $value) {
            if ($name != 'password' && $name != 'password_confirmation' && is_string($name) && is_string($value)) {
                $data .= $name.' => '.(is_array($value) ? implode(' ', $value) : $value).PHP_EOL;
            }
        }

        $error = $exception->getMessage() ? 'Ошибка: '.$exception->getMessage() : 'Неизвестная ошибка.';
        $exceptionClass = 'Класс ошибки - '.get_class($exception).'.';
        $file = $exception->getFile() ? 'В файле: '.
            preg_replace('/\/var\/www\/html/', '', $exception->getFile()) : '';
        $line = $exception->getLine() ? 'На строке '.$exception->getLine().'.' : '';
        $path = request()->path() ? 'На странице: '.request()->path().'.' : '';
        $userInfo = (Auth::user() ? 'Пользователь: '.Auth::user()->full_name : 'Нет пользователя').
            ', ip - '.request()->ip();
        $dataInfo = ($data != '') ? ('Переданные данные: '.PHP_EOL.$data) : '';
        $text = $error.PHP_EOL.$exceptionClass.PHP_EOL.$file.PHP_EOL.$line.
            PHP_EOL.$path.PHP_EOL.$userInfo.PHP_EOL.$dataInfo;

        return $text;
    }
}
