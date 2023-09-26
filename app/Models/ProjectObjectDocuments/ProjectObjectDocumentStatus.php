<?php

namespace App\Models\ProjectObjectDocuments;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ProjectObjectDocumentStatus extends Model
{
    use SoftDeletes;

    protected $guarded = ['id'];

    public function projectObjectDocuments()
    {
        return $this->hasMany(ProjectObjectDocument::class, 'document_status_id');
    }

    public function projectObjectDocumentsStatusType()
    {
        return $this->belongsTo(ProjectObjectDocumentsStatusType::class, 'status_type_id');
    }
}
