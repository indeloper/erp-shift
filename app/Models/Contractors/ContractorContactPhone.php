<?php

namespace App\Models\Contractors;

use Illuminate\Database\Eloquent\Model;

class ContractorContactPhone extends Model
{
    protected $fillable = ['name', 'phone_number', 'dop_phone', 'type', 'is_main', 'contact_id'];

    public $phone_types = ['Мобильный', 'Городской'];

    public $phone_names = ['Рабочий', 'Личный', 'Другой'];
}
