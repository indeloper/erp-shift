<?php

namespace App\Models\Manual;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ManualReferenceParameter extends Model
{
    use SoftDeletes;

    protected $fillable = ['attr_id', 'manual_reference_id', 'value'];

    protected static function boot()
    {
        parent::boot();

        static::saving(function($parameter) {
            $comma_replaced = str_replace(',','.', $parameter->value);
            if (is_numeric($comma_replaced)) {
                $parameter->value = (float)$comma_replaced;
            }
        });

        static::creating(function($parameter) {
            $comma_replaced = str_replace(',','.', $parameter->value);
            if (is_numeric($comma_replaced)) {
                $parameter->value = (float)$comma_replaced;
            }
        });
    }

    function getMatIdAttribute()
    {
        return $this->manual_reference_id;
    }

    public function attribute()
    {
        return $this->belongsTo(ManualMaterialCategoryAttribute::class, 'attr_id', 'id');
    }
}
