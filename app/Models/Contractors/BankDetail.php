<?php

namespace App\Models\Contractors;

use Illuminate\Database\Eloquent\Model;

class BankDetail extends Model
{
    protected $fillable = ['contractor_id', 'check_account', 'bik', 'cor_account', 'bank_name'];
}
