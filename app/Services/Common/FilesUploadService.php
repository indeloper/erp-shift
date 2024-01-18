<?php

namespace App\Services\Common;

use App\Models\FileEntry;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;
use ZipArchive;

class FilesUploadService {
    
    public function uploadFile($uploadedFile, $documentable_id, $documentable_type, $storage_name, $storage_path=null)
    {
        if(!$storage_path)
        $storage_path = 'storage/docs/'.$storage_name.'/';
        $fileExtension = $uploadedFile->getClientOriginalExtension();
        $fileName =  'file-' . uniqid() . '.' . $fileExtension;

        Storage::disk($storage_name)->put($fileName, File::get($uploadedFile));

        $fileEntry = FileEntry::create([
            'filename' =>  $storage_path. $fileName,
            'size' => $uploadedFile->getSize(),
            'mime' => $uploadedFile->getClientMimeType(),
            'original_filename' => $uploadedFile->getClientOriginalName(),
            'user_id' => Auth::user()->id,
            'documentable_id' => $documentable_id,
            'documentable_type' => $documentable_type
        ]);

        return [$fileEntry, $fileName];
    }

    public function attachFiles($entity, $newAttachments)
    {
        foreach($newAttachments as $fileId)
            FileEntry::find($fileId)->update(['documentable_id' => $entity->id]);
    }

    public function getDownloadableAttachments($fliesIds, $storage_path)
    {
        if(!count($fliesIds))
        return response()->json('no files recieved', 200);

        $storagePath = config('filesystems.disks')['zip_archives']['root'];

        $zip = new ZipArchive();
        $zipFileName = "file-". uniqid(). "-" . "archive.zip";
        $zipFilePath = $storagePath."/".$zipFileName ;
        $zip->open($zipFilePath, ZIPARCHIVE::CREATE);

        foreach($fliesIds as $fileId)
        {
            $file = FileEntry::find($fileId);
            $filenameElems = explode('/', $file->filename);
            $filename = $filenameElems[count($filenameElems) - 1];
            $zip->addFile($storage_path.'/'.$filename, $file->original_filename);
        }

        $zip->close();

        return['zipFileLink'=>'storage/docs/zip_archives/'.$zipFileName];
    }
}