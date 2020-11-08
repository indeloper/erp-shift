<?php

namespace App\Traits;


use App\Models\FileEntry;

trait Documentable
{
    public function documents()
    {
        return $this->morphMany(FileEntry::class, 'documentable');
    }

    public function files()
    {
        return $this->documents();
    }

    public function attachFiles($file_ids)
    {
        $files_to_delete = $this->files->pluck('id')->diff($file_ids);
        FileEntry::whereIn('id', $files_to_delete)->delete();

        FileEntry::whereIn('id', $file_ids)->update([
            'documentable_type' => $this->getMorphClass(),
            'documentable_id' => $this->id,
        ]);
    }

    /**
     * Relation for photos
     * @return mixed
     */
    public function photos()
    {
        return $this->documents()->where('mime', 'like', '%image%');
    }

    /**
     * Relation for videos
     * @return mixed
     */
    public function videos()
    {
        return $this->documents()->where('mime', 'like', '%video%');
    }

    public function not_videos()
    {
        return $this->documents()->where('mime', 'not like', '%video%');

    }
}