<?php

namespace App\Models\TechAcc\FuelTank;

use Illuminate\Database\Eloquent\SoftDeletes;
use App\Traits\Logable;
use App\Traits\DevExtremeDataSourceLoadable;
use Illuminate\Database\Eloquent\Model;
use App\Models\Comment;
use App\Models\Contractors\Contractor;
use App\Models\FileEntry;
use App\Models\TechAcc\OurTechnic;

class FuelTankFlow extends Model
{
    use SoftDeletes, DevExtremeDataSourceLoadable, Logable;

    protected $guarded = ['id'];

    public function comments()
    {
        return $this->morphMany(Comment::class, 'commentable');
    }

    public function attachments()
    {
        return $this->morphMany(FileEntry::class, 'documentable');
    }

    public function contractor()
    {
        return $this->belongsTo(Contractor::class);
    }

    public function ourTechnic()
    {
        return $this->belongsTo(OurTechnic::class);
    }
}
