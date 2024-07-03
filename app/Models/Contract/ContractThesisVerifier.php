<?php

namespace App\Models\Contract;

use Illuminate\Database\Eloquent\Model;

class ContractThesisVerifier extends Model
{
    protected $fillable = ['user_id', 'thesis_id', 'status'];

    public $statuses = [
        1 => 'Не просмотрен',
        2 => 'Отклонен',
        3 => 'Согласован',
    ];
}
