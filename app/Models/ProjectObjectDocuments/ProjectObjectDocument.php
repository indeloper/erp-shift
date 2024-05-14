<?php

namespace App\Models\ProjectObjectDocuments;

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

    public function projectObject()
    {
        return $this->belongsTo('App\Models\ProjectObject', 'project_object_id');
    }

    public function type()
    {
        return $this->belongsTo(ProjectObjectDocumentType::class, 'document_type_id');
    }

    public function status()
    {
        return $this->belongsTo(ProjectObjectDocumentStatus::class, 'document_status_id');
    }

    public function author()
    {
        return $this->belongsTo(User::class, 'author_id');
    }

    public function comments()
    {
        return $this->morphMany(Comment::class, 'commentable');
    }

    public function attachments()
    {
        return $this->morphMany(FileEntry::class, 'documentable');
    }

    public function actionLogs()
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
