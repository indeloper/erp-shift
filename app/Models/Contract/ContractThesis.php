<?php

namespace App\Models\Contract;

use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Model;

class ContractThesis extends Model
{
    public $statuses = [
        1 => 'Не просмотрен',
        2 => 'Отклонен',
        3 => 'Согласован',
    ];

    public function get_verifiers(): HasMany
    {
        return $this->hasMany(ContractThesisVerifier::class, 'thesis_id', 'id');
    }

    public function verifiers(): HasMany
    {
        return $this->hasMany(ContractThesisVerifier::class, 'thesis_id', 'id')
            ->leftJoin('users', 'users.id', 'contract_thesis_verifiers.user_id')
            ->select('contract_thesis_verifiers.*', 'users.last_name', 'users.first_name', 'users.patronymic');
    }

    public function files(): HasMany
    {
        return $this->hasMany(ContractThesisFile::class, 'thesis_id', 'id');
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
