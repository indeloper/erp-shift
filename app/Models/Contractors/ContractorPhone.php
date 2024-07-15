<?php

namespace App\Models\Contractors;

use Illuminate\Database\Eloquent\Model;

class ContractorPhone extends Model
{
    protected $fillable = ['name', 'phone_number', 'dop_phone', 'type', 'is_main', 'contractor_id'];

    public $phone_types = ['Мобильный', 'Городской'];

    public $phone_names = ['Рабочий', 'Личный', 'Другой'];
}
