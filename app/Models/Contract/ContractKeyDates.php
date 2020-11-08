<?php

namespace App\Models\Contract;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ContractKeyDates extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'contract_id',
        'key_date_id',
        'name',
        'sum',
        'date_from',
        'date_to',
        'note',
    ];

    protected $dates = [
        'date_from',
        'date_to',
    ];

    public function related_key_dates()
    {
        return $this->hasMany(ContractKeyDates::class, 'key_date_id', 'id');
    }

    public function contract()
    {
        return $this->belongsTo(Contract::class);
    }
}
