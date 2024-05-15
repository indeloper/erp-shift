<?php

namespace App\Models\ProjectObjectDocuments;

use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ProjectObjectDocumentStatus extends Model
{
    use SoftDeletes;

    protected $guarded = ['id'];

    public function projectObjectDocuments(): HasMany
    {
        return $this->hasMany(ProjectObjectDocument::class, 'document_status_id');
    }

    public function projectObjectDocumentsStatusType(): BelongsTo
    {
        return $this->belongsTo(ProjectObjectDocumentsStatusType::class, 'status_type_id');
    }
}
