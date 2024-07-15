<?php

namespace App\Models\ProjectObjectDocuments;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ProjectObjectDocumentsStatusType extends Model
{
    protected $guarded = ['id'];

    public function projectObjectDocumentStatuses(): HasMany
    {
        return $this->hasMany(ProjectObjectDocumentStatus::class, 'status_type_id');
    }
}
