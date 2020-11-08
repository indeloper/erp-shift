<?php

namespace App\Http\Controllers;

use App\Models\FileEntry;
use Illuminate\Http\Request;

class FileEntryController extends Controller
{
    public function store(Request $request)
    {
        $file_ids = $this->system_service->storeFileEntries($request->all());

        return response()->json([
            'data' => $file_ids,
            'result' => 'success',
        ]);
    }

    public function destroy(FileEntry $fileEntry)
    {
        $this->system_service->destroyFileEntry($fileEntry);

        return \GuzzleHttp\json_encode([
            'result' => 'success',
        ]);
    }
}
