<?php

namespace App\Models\Contractors;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ContractorAdditionalTypes extends Model
{
    use SoftDeletes;

    protected $guarded = ['id'];

    // protected $fillable = [
    //     'contractor_id',
    //     'additional_type',
    //     'user_id'
    // ];

    protected $appends = ['type_name'];

    // const CONTRACTOR_TYPES = Contractor::CONTRACTOR_TYPES;

    public function getTypeNameAttribute()
    {
        // return self::CONTRACTOR_TYPES[$this->main_type] ?? 'Не указан';
        return ContractorType::find($this->main_type)->name ?? 'Не указан';
    }

    /**
     * Relation for contractor
     */
    public function contractor(): BelongsTo
    {
        return $this->belongsTo(Contractor::class, 'contractor_id', 'id');
    }

    /**
     * Relation for user
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }
}
