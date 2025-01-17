<?php

namespace App\Models\MatAcc;

use App\Events\MaterialAccountingOperationResponsibleUsersEvents;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MaterialAccountingOperationResponsibleUsers extends Model
{
    use HasFactory;

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

    public function operation(): BelongsTo
    {
        return $this->belongsTo(MaterialAccountingOperation::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }
}
