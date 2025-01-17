<?php

namespace App\Http\Controllers\System;

use App\Http\Controllers\Controller;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class FileController extends Controller
{
    public function file($filePath): BinaryFileResponse
    {
        if (! file_exists(storage_path('app/public'.DIRECTORY_SEPARATOR.($filePath)))) {
            abort('404');
        }

        return response()->file(storage_path('app/public'.DIRECTORY_SEPARATOR.($filePath)));
    }
}
