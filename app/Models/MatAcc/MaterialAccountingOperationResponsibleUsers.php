<?php

namespace App\Models\MatAcc;

use App\Events\MaterialAccountingOperationResponsibleUsersEvents;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;

class MaterialAccountingOperationResponsibleUsers extends Model
{
    protected $fillable = [
        'operation_id',
        'user_id',
        'type',
    ];

    public $additional_info = [];

    // type using only in moving operations
    public $type_names = [
        0 => 'standard responsible',
        1 => 'from responsible',
        2 => 'to responsible',
    ];

    public static function boot()
    {

        parent::boot();

        static::created(function ($user) {
            event((new MaterialAccountingOperationResponsibleUsersEvents)->respUserCreated($user));
        });

    }

    public function operation()
    {
        return $this->belongsTo(MaterialAccountingOperation::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }
}
