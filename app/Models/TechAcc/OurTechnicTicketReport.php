<?php

namespace App\Models\TechAcc;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Carbon\Carbon;
use App\Models\User;

class OurTechnicTicketReport extends Model
{
    use SoftDeletes;

    protected $fillable = ['our_technic_ticket_id', 'hours', 'user_id', 'comment', 'date'];

    protected $with = ['user'];

    protected $appends = ['date_carbon'];

//    protected $casts = [
//        'date' => 'date:d.m.Y',
//    ];

    public function ticket()
    {
        return $this->belongsTo(OurTechnicTicket::class, 'our_technic_ticket_id', 'id');
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function getDateCarbonAttribute()
    {
        return Carbon::parse($this->date)->addHours(3)->format('d.m.Y');
    }
}
