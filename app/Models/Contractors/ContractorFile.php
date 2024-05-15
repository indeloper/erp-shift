<?php

namespace App\Models\Contractors;

use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Model;

class ContractorFile extends Model
{
    protected $fillable = ['file_name', 'original_name', 'commercial_offer_id', 'contractor_id'];

    /**
     * Relation from file to contractor
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function contractor(): HasOne
    {
        return $this->hasOne(Contractor::class, 'id', 'contractor_id');
    }
}
