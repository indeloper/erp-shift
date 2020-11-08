<?php

namespace App\Http\Controllers\System;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class FileController extends Controller
{
    public function file($filePath)
    {
        if (!file_exists(storage_path('app/public'.DIRECTORY_SEPARATOR.($filePath)))){
            abort('404');
        }

        return response()->file(storage_path('app/public'.DIRECTORY_SEPARATOR.($filePath)));
    }
}
