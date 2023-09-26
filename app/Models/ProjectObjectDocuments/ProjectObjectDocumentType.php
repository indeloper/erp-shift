<?php

namespace App\Models\ProjectObjectDocuments;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ProjectObjectDocumentType extends Model
{
    use SoftDeletes;

    public function projectObjectDocuments()
    {
        return $this->hasMany(ProjectObjectDocument::class, 'document_type_id');
    }

    public function projectObjectDocumentStatusTypeRelations()
    {
        return $this->hasMany(ProjectObjectDocumentStatusTypeRelation::class, 'document_type_id');
    }
}
