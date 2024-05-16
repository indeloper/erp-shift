<?php

namespace App\Models\TechAcc\Vehicles;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class VehicleCategories extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $fillable = [
        'user_id',
        'name',
        'description',
    ];

    protected $with = [
        'author',
        'characteristics',
    ];

    // Relations
    /**
     * Relation for vehicle category author
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function author()
    {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }

    /**
     * Relation for vehicle category author
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function characteristics()
    {
        return $this->hasMany(VehicleCategoryCharacteristics::class, 'category_id', 'id');
    }

    /**
     * Relation for vehicles
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function vehicles()
    {
        return $this->hasMany(OurVehicles::class, 'category_id', 'id');
    }

    /**
     * Relation for trashed vehicles
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function trashed_vehicles()
    {
        return $this->hasMany(OurVehicles::class, 'category_id', 'id')->onlyTrashed();
    }

    // Methods
    /**
     * This function delete vehicle category characteristics
     * and update characteristics values in vehicle category
     * examples
     */
    public function deleteCharacteristics($deleted_characteristic_ids)
    {
        foreach ($deleted_characteristic_ids as $characteristic_id) {
            $this->deleteCharacteristic($characteristic_id);
        }
    }

    /**
     * This function delete vehicle category characteristic
     * and also delete parameters from this category vehicles
     */
    public function deleteCharacteristic($characteristic_id): void
    {
        $this->characteristics()->findOrFail($characteristic_id)->delete();
    }

    /**
     * This function create or update vehicle category characteristics
     */
    public function updateCharacteristics(array $characteristics)
    {
        foreach ($characteristics as $characteristic) {
            (isset($characteristic['id']) and $characteristic['id'] != -1)
                ? $this->updateExistedCharacteristic($characteristic)
                : $this->createNewCharacteristic($characteristic);
        }
    }

    public function updateExistedCharacteristic($characteristic)
    {
        $this->characteristics()->findOrFail($characteristic['id'])->update([
            'name' => $characteristic['name'],
            'short_name' => $characteristic['short_name'] ?? null,
            'unit' => $characteristic['unit'] ?? null,
            'show' => $characteristic['show'],
            'required' => $characteristic['required'],
        ]);
    }

    public function createNewCharacteristic($characteristic)
    {
        $this->characteristics()->create([
            'name' => $characteristic['name'],
            'short_name' => $characteristic['short_name'] ?? null,
            'unit' => $characteristic['unit'] ?? null,
            'show' => $characteristic['show'],
            'required' => $characteristic['required'],
        ]);
    }
}
