<?php

namespace App\Models\TechAcc;

use App\Traits\AuthorAndEditorUserFields;
use Illuminate\Database\Eloquent\Model;
use App\Traits\DevExtremeDataSourceLoadable;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Models\FileEntry;

class TechnicMovement extends Model
{
    use AuthorAndEditorUserFields, DevExtremeDataSourceLoadable, SoftDeletes;

    const STORAGE_PATH = 'storage/docs/technic_movements/';
    
    protected $guarded = ['id'];

    // public function comments()
    // {
    //     return $this->morphMany(Comment::class, 'commentable');
    // }
    
    public function attachments()
    {
        return $this->morphMany(FileEntry::class, 'documentable');
    }
}
