<?php

namespace App\Services\Common;

use App\Models\FileEntry;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;

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
}