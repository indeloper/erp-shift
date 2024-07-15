<?php

namespace App\Models\Contract;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ContractKeyDatesPreselectedNames extends Model
{
    use SoftDeletes;

    protected $fillable = ['value'];
}
