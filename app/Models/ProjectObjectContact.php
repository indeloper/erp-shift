<?php

namespace App\Models;

use App\Models\Contractors\ContractorContact;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProjectObjectContact extends Model
{

    protected $fillable
        = [
            'project_object_id',
            'contact_id',
            'note',
        ];

    public function projectObject(): BelongsTo
    {
        return $this->belongsTo(ProjectObject::class);
    }

    public function contact(): BelongsTo
    {
        return $this->belongsTo(ContractorContact::class, 'contact_id');
    }

}
