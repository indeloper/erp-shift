<?php

namespace App\Models\TechAcc;

use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class OurTechnicTicketReport extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $fillable = ['our_technic_ticket_id', 'hours', 'user_id', 'comment', 'date'];

    protected $with = ['user'];

    protected $appends = ['date_carbon'];

    //    protected $casts = [
    //        'date' => 'date:d.m.Y',
    //    ];

    public function ticket(): BelongsTo
    {
        return $this->belongsTo(OurTechnicTicket::class, 'our_technic_ticket_id', 'id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function getDateCarbonAttribute()
    {
        return Carbon::parse($this->date)->addHours(3)->format('d.m.Y');
    }
}
