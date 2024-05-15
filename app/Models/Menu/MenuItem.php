<?php

namespace App\Models\Menu;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class MenuItem extends Model
{
    use HasFactory;

    use SoftDeletes;

    protected $fillable = [
        'title',
        'parent_id',
        'route_name',
        'icon_path',
        'gates',
        'is_su',
        'status',
        'actives',
    ];

    protected $casts = [
        'status' => 'boolean',
        'is_su' => 'boolean',
        'gates' => 'array',
        'actives' => 'array',
    ];

    public function scopeActive($query)
    {
        return $query->where('status', true);
    }

    public function parent()
    {
        return $this->belongsTo(MenuItem::class);
    }

    public function children()
    {
        return $this->hasMany(MenuItem::class, 'parent_id', 'id')
            ->where('status', true);
    }

    public function users()
    {
        return $this->belongsToMany(User::class, 'favorite_menu_item_user');
    }
}
