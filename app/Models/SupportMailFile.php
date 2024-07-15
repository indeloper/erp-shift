<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SupportMailFile extends Model
{
    protected $fillable = ['support_mail_id', 'path', 'original_name'];
}
