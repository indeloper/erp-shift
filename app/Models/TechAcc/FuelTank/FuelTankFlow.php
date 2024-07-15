<?php

namespace App\Models\TechAcc\FuelTank;

use App\Models\Comment;
use App\Models\Contractors\Contractor;
use App\Models\FileEntry;
use App\Models\TechAcc\OurTechnic;
use App\Traits\DevExtremeDataSourceLoadable;
use App\Traits\Logable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class FuelTankFlow extends Model
{
    use DevExtremeDataSourceLoadable, Logable, SoftDeletes;

    const STORAGE_PATH = 'storage/docs/fuel_flow/';

    protected $guarded = ['id'];

    public function comments(): MorphMany
    {
        return $this->morphMany(Comment::class, 'commentable');
    }

    public function attachments(): MorphMany
    {
        return $this->morphMany(FileEntry::class, 'documentable');
    }

    public function contractor(): BelongsTo
    {
        return $this->belongsTo(Contractor::class);
    }

    public function ourTechnic(): BelongsTo
    {
        return $this->belongsTo(OurTechnic::class);
    }
}
