<?php

namespace App\Models\ProjectObjectDocuments;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use App\Models\ActionLog;
use App\Models\Comment;
use App\Models\FileEntry;
use App\Models\Permission;
use App\Models\User;
use App\Traits\DevExtremeDataSourceLoadable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Auth;

class ProjectObjectDocument extends Model
{
    use DevExtremeDataSourceLoadable, SoftDeletes;

    protected $guarded = [];

    public function projectObject(): BelongsTo
    {
        return $this->belongsTo(\App\Models\ProjectObject::class, 'project_object_id');
    }

    public function type(): BelongsTo
    {
        return $this->belongsTo(ProjectObjectDocumentType::class, 'document_type_id');
    }

    public function status(): BelongsTo
    {
        return $this->belongsTo(ProjectObjectDocumentStatus::class, 'document_status_id');
    }

    public function author(): BelongsTo
    {
        return $this->belongsTo(User::class, 'author_id');
    }

    public function comments(): MorphMany
    {
        return $this->morphMany(Comment::class, 'commentable');
    }

    public function attachments(): MorphMany
    {
        return $this->morphMany(FileEntry::class, 'documentable');
    }

    public function actionLogs(): MorphMany
    {
        return $this->morphMany(ActionLog::class, 'logable');
    }

    public function getPermissionsAttribute()
    {
        $permissionsArray = [];
        $permissions = Permission::where('category', 20)->get();

        foreach ($permissions as $permission) {
            $permissionsArray[$permission->codename] = Auth::user()->can($permission->codename);
        }

        return $permissionsArray;
    }
}
