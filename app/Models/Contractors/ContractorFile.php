<?php

namespace App\Models\Contractors;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasOne;

class ContractorFile extends Model
{
    protected $fillable = ['file_name', 'original_name', 'commercial_offer_id', 'contractor_id'];

    /**
     * Relation from file to contractor
     */
    public function contractor(): HasOne
    {
        return $this->hasOne(Contractor::class, 'id', 'contractor_id');
    }
}
