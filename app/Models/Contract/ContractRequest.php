<?php

namespace App\Models\Contract;

use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Model;

class ContractRequest extends Model
{
    public $request_status = [
        1 => 'Не просмотрен',
        2 => 'Положительный',
        3 => 'Отрицательный',
    ];

    protected $fillable = ['name', 'user_id', 'contract_id', 'project_id', 'description', 'status', 'result_comment'];

    public function files(): HasMany
    {
        return $this->hasMany(ContractRequestFile::class, 'request_id', 'id');
    }

    public function getCreatedAtAttribute($date)
    {
        return \Carbon\Carbon::parse($date)->format('d.m.Y H:i:s');
    }

    public function getUpdatedAtAttribute($date)
    {
        return \Carbon\Carbon::parse($date)->format('d.m.Y H:i:s');
    }
}
