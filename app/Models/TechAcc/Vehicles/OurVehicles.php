<?php

namespace App\Models\TechAcc\Vehicles;

use App\Models\User;
use App\Traits\Documentable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class OurVehicles extends Model
{
    use Documentable, SoftDeletes;

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

    protected $casts = ['owner' => 'integer'];

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
     *
     * @return string
     */
    public function getFullNameAttribute()
    {
        return $this->category->name.' '.$this->mark.' '.$this->model.' '.$this->number.' '.$this->trailer_number;
    }
    /** Relations */

    /**
     * Relation to parent category
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function category()
    {
        return $this->belongsTo(VehicleCategories::class, 'category_id', 'id');
    }

    /**
     * Relation to author category
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function author()
    {
        return $this->hasOne(User::class, 'id', 'user_id');
    }

    /**
     * Relation to vehicle parameters
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function parameters()
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
