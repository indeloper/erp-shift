<?php

namespace App\Http\Middleware;

use App\Facades\TGUserWebApp;
use App\Services\TelegramWebAppService;
use App\Services\User\UserService;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Micromagicman\TelegramWebApp\Dto\TelegramUser;

/**
 * Middleware that provides a mechanism for validating Telegram MiniApp users
 */
class WebAppDataValidationMiddleware
{

    public function __construct(
        private readonly TelegramWebAppService $webAppService,
        private readonly UserService $userRepository,
    ) {}

    public function handle(Request $request, Closure $next)
    {
        Auth::logout();

        $tgWebAppData = TGUserWebApp::getUserData() ??
            $request->get(TGUserWebApp::QUEUE_NAME);

        if ($tgWebAppData === null) {
            $this->webAppService->abortWithError();
        }

        $parseTGUser = json_decode($tgWebAppData, true, 512,
            JSON_THROW_ON_ERROR);

        if (
            isset($parseTGUser['id']) === false
            || isset($parseTGUser['username']) === false
        ) {
            $this->webAppService->abortWithError();
        }

        $enabled = webAppConfig('enabled');

        if ($enabled
            && ! $this->webAppService->verifyInitDataArray($request->only([
                'auth_date',
                'query_id',
                'chat_instance',
                'chat_type',
                'hash',
                'user',
            ]))
        ) {
            $this->webAppService->abortWithError();
        }

        $telegramUser = new TelegramUser($parseTGUser);

        $user = $this->userRepository->getUserTelegram(
            $telegramUser->getId(),
        );

        if ($user === null) {
            $this->webAppService->abortWithError();
        }

        session()->put(TGUserWebApp::QUEUE_NAME, $tgWebAppData);
        session()->put(TGUserWebApp::REQUEST_KEY, $request->only([
            'auth_date',
            'query_id',
            'chat_instance',
            'chat_type',
            'hash',
            'user',
        ]));

        Auth::login($user);

        return $next($request);
    }

}