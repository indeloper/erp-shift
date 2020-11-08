<?php

namespace App\Models\Manual;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ManualCopiedWorks extends Model
{
    use SoftDeletes;

    protected $fillable = ['id', 'parent_work_id', 'child_work_id'];

    public function child_work()
    {
        return $this->hasOne(ManualWork::class, 'id', 'child_work_id');
    }

    public function parent_work()
    {
        return $this->hasOne(ManualWork::class, 'id', 'parent_work_id');
    }
}
