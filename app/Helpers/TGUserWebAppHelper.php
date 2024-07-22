<?php

declare(strict_types=1);

namespace App\Helpers;

use App\Facades\TGUserWebApp;

final class TGUserWebAppHelper
{

    public function getQueryData(): array
    {
        return session(TGUserWebApp::REQUEST_KEY, []);
    }

    public function getUserData(): ?string
    {
        return session(TGUserWebApp::QUEUE_NAME);
    }

}