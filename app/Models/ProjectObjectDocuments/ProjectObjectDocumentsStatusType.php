<?php

namespace App\Models\ProjectObjectDocuments;

use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Model;

class ProjectObjectDocumentsStatusType extends Model
{
    protected $guarded = ['id'];

    public function projectObjectDocumentStatuses(): HasMany
    {
        return $this->hasMany(ProjectObjectDocumentStatus::class, 'status_type_id');
    }
}
