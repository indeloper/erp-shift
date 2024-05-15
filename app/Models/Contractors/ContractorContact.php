<?php

namespace App\Models\Contractors;

use App\Models\ProjectContact;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ContractorContact extends Model
{
    use HasFactory;

    protected $fillable = [
        'first_name', 'last_name', 'patronymic', 'position', 'email', 'phone_number', 'note', 'contractor_id',
    ];

    public function projects()
    {
        return $this->hasMany(ProjectContact::class, 'contact_id', 'id')
            ->leftjoin('projects', 'projects.id', '=', 'project_contacts.project_id')
            ->select('project_contacts.*', 'projects.name');
    }

    public function phones()
    {
        return $this->hasMany(ContractorContactPhone::class, 'contact_id', 'id');
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
