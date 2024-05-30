<?php

namespace App\Models\Contract;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ContractFiles extends Model
{
    protected $fillable = ['file_name', 'origin_name', 'contract_id', 'name'];

    public function contract(): BelongsTo
    {
        return $this->belongsTo(Contract::class, 'contract_id', 'id');
    }
}
