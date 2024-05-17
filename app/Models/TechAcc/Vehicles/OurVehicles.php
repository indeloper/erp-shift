<?php

namespace App\Models\TechAcc\Vehicles;

use App\Models\User;
use App\Traits\Documentable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;

class OurVehicles extends Model
{
    use Documentable, SoftDeletes;
    use HasFactory;

    protected $fillable = [
        'category_id',
        'user_id',
        'number',
        'trailer_number',
        'mark',
        'model',
        'owner',
    ];

    protected $with = ['parameters', 'documents'];

    protected $appends = ['owner_name'];

    protected function casts(): array
    {
        return [
            'owner' => 'integer'
        ];
    }

    const OWNERS = [
        1 => 'ООО «СК ГОРОД»',
        2 => 'ООО «ГОРОД»',
        3 => 'ООО «СТРОЙМАСТЕР»',
        4 => 'ООО «РЕНТМАСТЕР»',
        5 => 'ООО «Вибродрилл Технология»',
        6 => 'ИП Исмагилов А.Д.',
        7 => 'ИП Исмагилов М.Д.',
    ];

    /** Custom getters */
    /**
     * Get entity name for model instance
     */
    public function getOwnerNameAttribute()
    {
        if (is_int($this->owner) and $this->owner > 0) {
            return self::OWNERS[$this->owner];
        } else {
            return $this->owner;
        }
    }

    /**
     * Collects full name of vehicle
     */
    public function getFullNameAttribute(): string
    {
        return $this->category->name.' '.$this->mark.' '.$this->model.' '.$this->number.' '.$this->trailer_number;
    }
    /** Relations */

    /**
     * Relation to parent category
     */
    public function category(): BelongsTo
    {
        return $this->belongsTo(VehicleCategories::class, 'category_id', 'id');
    }

    /**
     * Relation to author category
     */
    public function author(): HasOne
    {
        return $this->hasOne(User::class, 'id', 'user_id');
    }

    /**
     * Relation to vehicle parameters
     */
    public function parameters(): HasMany
    {
        return $this->hasMany(OurVehicleParameters::class, 'vehicle_id', 'id');
    }

    /** Methods */

    /**
     * This function create or update vehicle parameters
     */
    public function updateParameters(array $parameters)
    {
        foreach ($parameters as $parameter) {
            $this->parameters()->updateOrCreate(
                ['id' => $parameter['id'] ?? 0], ['value' => $parameter['value'], 'characteristic_id' => $parameter['characteristic_id'],
                ]);
        }
    }
}
