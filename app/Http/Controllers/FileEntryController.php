<?php

namespace App\Http\Controllers;

use App\Models\FileEntry;
use Illuminate\Http\Request;
use ZipArchive;

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

    // для использования метода надо в модели прописать STORAGE_PATH
    // есть метод с похожим функционалом в
    public function downloadAttachments(Request $request)
    {
        if (! count($request->fliesIds)) {
            return response()->json('no files recieved', 200);
        }

        $storagePath = config('filesystems.disks')['zip_archives']['root'];

        $zip = new ZipArchive();
        $zipFileName = 'file-'.uniqid().'-'.'archive.zip';
        $zipFilePath = $storagePath.'/'.$zipFileName;
        $zip->open($zipFilePath, ZIPARCHIVE::CREATE);

        foreach ($request->fliesIds as $fileId) {
            $file = FileEntry::find($fileId);
            $filenameElems = explode('/', $file->filename);
            $filename = $filenameElems[count($filenameElems) - 1];
            $zip->addFile((new $file->documentable_type())::STORAGE_PATH.$filename, $file->original_filename);
        }

        $zip->close();

        $response = ['zipFileLink' => 'storage/docs/zip_archives/'.$zipFileName];

        return response()->json($response, 200);
    }
}
