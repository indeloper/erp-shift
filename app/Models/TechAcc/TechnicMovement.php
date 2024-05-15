<?php

namespace App\Models\TechAcc;

use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Models\FileEntry;
use App\Models\ProjectObject;
use App\Traits\AuthorAndEditorUserFields;
use App\Traits\DevExtremeDataSourceLoadable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class TechnicMovement extends Model
{
    use AuthorAndEditorUserFields, DevExtremeDataSourceLoadable, SoftDeletes;

    const STORAGE_PATH = 'storage/docs/technic_movements/';

    protected $guarded = ['id'];

    // public function comments()
    // {
    //     return $this->morphMany(Comment::class, 'commentable');
    // }

    public function attachments(): MorphMany
    {
        return $this->morphMany(FileEntry::class, 'documentable');
    }

    public function object(): BelongsTo
    {
        return $this->belongsTo(ProjectObject::class);
    }
}
