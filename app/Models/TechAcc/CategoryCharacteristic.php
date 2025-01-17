<?php

namespace App\Models\TechAcc;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class CategoryCharacteristic extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $fillable = ['name', 'description', 'is_hidden', 'unit', 'required'];

    protected function casts(): array
    {
        return [
            'is_hidden' => 'boolean',
            'required' => 'boolean',
        ];
    }

    public function technic_categories(): BelongsToMany
    {
        return $this->belongsToMany(TechnicCategory::class, 'technic_category_category_characteristic');
    }
}
