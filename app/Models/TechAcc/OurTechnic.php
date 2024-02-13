<?php

namespace App\Models\TechAcc;

use App\Models\ProjectObject;
use App\Models\TechAcc\Defects\Defects;
use App\Models\User;
use App\Traits\DefaultSortable;
use App\Traits\Defectable;
use App\Traits\Documentable;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Collection;
use App\Traits\DevExtremeDataSourceLoadable;

class OurTechnic extends Model
{
    use DevExtremeDataSourceLoadable, Documentable, Defectable, SoftDeletes, DefaultSortable;

    protected $guarded = ['id'];

    public $defaultSortOrder = [
        'name' => 'asc'
    ];

    // protected $fillable = ['brand','model','owner','start_location_id','technic_category_id','exploitation_start','inventory_number',];

    // protected $appends = 
    // ['category_name', 
    // // 'name', 
    // 'human_status', 'release_date', 'short_tickets', 'work_link'];

    // protected $with = ['defectsLight', 'start_location'];

    public static $owners = [
        1 => 'ООО «СК ГОРОД»',
        2 => 'ООО «ГОРОД»',
        3 => 'ООО «СТРОЙМАСТЕР»',
        4 => 'ООО «РЕНТМАСТЕР»',
        5 => 'ООО «Вибродрилл Технология»',
        6 => 'ИП Исмагилов А.Д.',
        7 => 'ИП Исмагилов М.Д.',
    ];

    public static $statuses = [
        1 => 'Свободен',
        2 => 'В работе',
        3 => 'Ремонт',
    ];

    // protected static function boot()
    // {
    //     parent::boot();

    //     static::addGlobalScope('orderByUpdated', function (Builder $builder) {
    //         $builder->orderByDesc('updated_at');
    //     });

    //     static::deleted(function($technic) {
    //         $technic->tickets->each(function ($ticket) {$ticket->delete();});
    //         $technic->defects->each(function ($defect) {$defect->delete();});
    //     });
    // }

    // public function getWorkLinkAttribute()
    // {
    //     if ($this->isDefected()) {
    //         return '/building/tech_acc/defects/' . $this->defects()->whereNotIn('status', Defects::USUALLY_HIDING)->orderBy('id', 'desc')->first()->id;
    //     }
    //     if ($this->isVacated()) {
    //         return '/building/tech_acc/our_technic_tickets?ticket_id=' . $this->tickets()->whereNotIn('status', [3, 8])->orderBy('id', 'desc')->first()->id;
    //     }

    //     return '/building/tech_acc/technic_category/' . $this->technic_category_id . '/our_technic?start_location_id=' . $this->start_location_id;

    // }

    // public function getCategoryNameAttribute()
    // {
    //     if ($this->category)
    //     {
    //         return $this->category->name;
    //     } else {
    //         return 'Без категории';
    //     }
    // }

    // public function getNameAttribute()
    // {
    //     return trim($this->brand . ' ' . $this->model);
    // }

    public function category()
    {
        return $this->belongsTo(TechnicCategory::class, 'technic_category_id', 'id');
    }

    public function category_characteristics()
    {
        return $this->belongsToMany(CategoryCharacteristic::class,  'category_characteristic_technic', 'technic_id')
            ->withPivot('value')
            ->as('data');
    }

    // public function setCharacteristicsValue($characteristics)
    // {
    //     foreach ($characteristics as $characteristic) {
    //         $this->category_characteristics()->attach($characteristic['id'], ['value' => $characteristic['value']]);
    //     }
    // }

    public function start_location()
    {
        return $this->belongsTo(ProjectObject::class, 'start_location_id', 'id');
    }

    public function tickets()
    {
        return $this->hasMany(OurTechnicTicket::class);
    }

    /**
     * This is short data of tickets for summary on technics page
     *
     */
    // public function getShortTicketsAttribute()
    // {
    //     $short_tickets = $this->tickets()->select('id', 'status', 'created_at')->with('users')->get();
    //     $short_data = [];
    //     foreach ($short_tickets as $ticket) {
    //         $author = $ticket->users()->ofType('author_user_id')->first();
    //         $resp_rp = $ticket->users()->ofType('resp_rp_user_id')->first();
    //         $short_data[] = [
    //             'id' => $ticket->id,
    //             'status' => isset($ticket->statuses[$ticket->status]) ? $ticket->statuses[$ticket->status] : 'Отклонена',
    //             'created_at' => $ticket->created_at,
    //             'author' => $author ? $author->full_name : '-',
    //             'resp_rp' => $resp_rp ? $resp_rp->full_name : '-',
    //         ];
    //     }

    //     return $short_data;
    // }

    /**
     * This function find responsible RP for technic
     * @return User|Collection
     */
    // public function getResponsibleUser()
    // {
    //     return $this->tickets->first()->users()->ofType('resp_rp_user_id')->first() ?? collect(['id' => null]);
    // }

    // public function getHumanStatusAttribute()
    // {
    //     if ($this->isDefected()) {
    //         return 'Ремонт';
    //     }
    //     if ($this->isVacated()) {
    //         return 'В работе';
    //     }
    //     return 'Свободен';
    // }

    // public function getReleaseDateAttribute()
    // {
    //     if ($this->isDefected() or $this->isVacated()) {
    //         $maxUsageDate = Carbon::parse($this->tickets()->max('usage_to_date'));
    //         $maxRepairDate = Carbon::parse($this->defects()->max('repair_end_date'));
    //         $max = $maxUsageDate->greaterThanOrEqualTo($maxRepairDate) ? $maxUsageDate : $maxRepairDate;
    //         return $max->isoFormat('DD.MM.YYYY');
    //     }
    //     return '-';
    // }

    // public function isVacated()
    // {
    //     return $this->tickets()->whereNotIn('status', [3, 8])->exists();
    // }

    // public function isDefected()
    // {
    //     return $this->defects()->whereNotIn('status', Defects::USUALLY_HIDING)->exists();
    // }

    /**
     * Scope for technics without tickets in some statuses or defects
     * @param $query
     * @return mixed
     */
    // public function scopeFree($query)
    // {
    //     return $query->whereDoesntHave('tickets', function ($q) {
    //         $q->whereNotIn('status', [3, 8]);
    //     })->whereDoesntHave('defects', function ($q) {
    //         $q->whereNotIn('status', Defects::USUALLY_HIDING);
    //     });
    // }

    // public function scopeHaveTickets($query)
    // {
    //     return $query->whereHas('tickets', function ($q) {
    //         $q->whereNotIn('status', [3, 8]);
    //     });
    // }

    // public function scopeHaveDefects($query)
    // {
    //     return $query->whereHas('defects', function ($q) {
    //         $q->whereNotIn('status', Defects::USUALLY_HIDING);
    //     });
    // }

    // public function scopeFilter($query, array $request)
    // {
    //     $query->with('category_characteristics', 'documents', 'start_location');

    //     if (isset($request['status'])) {
    //         $query->where(function ($query) use($request){
    //             foreach ((array) $request['status'] as $status) {
    //                 $query->orWhere(function ($query) use($status) {
    //                     switch ($status) {
    //                         case 'Свободен':
    //                             $query->free();
    //                             break;
    //                         case 'В работе':
    //                             $query->haveTickets();
    //                             break;
    //                         case 'Ремонт':
    //                             $query->haveDefects();
    //                             break;
    //                     }
    //                 });
    //             }
    //         });
    //     }

    //     $fillablePlusId = array_merge(array_flip($this->fillable), ['id' => 0]);
    //     foreach (array_intersect_key($request, $fillablePlusId) as $param => $values) {
    //         $query->where(function ($query) use ($param, $values) {
    //              foreach ((array) $values as $item) {
    //                 $query->orWhere($param, 'like',  '%' . $item .'%');
    //              }
    //         });
    //     }

    //     foreach (array_diff_key($request, array_flip($this->fillable), ['page' => 1, 'id' => 0, 'search' => 1, 'status' => 0]) as $param => $values) {
    //         $query->whereHas('category_characteristics', function ($query) use ($param, $values) {
    //             $query->where(function($q) use ($param, $values) {
    //                 $q->where('category_characteristics.id', (int) filter_var($param, FILTER_SANITIZE_NUMBER_INT));
    //                 $q->where(function($que) use ($values) {
    //                     foreach ((array) $values as $value) {
    //                         $que->orWhere('value', 'like', '%' . $value . '%');
    //                     }
    //                 });
    //             });
    //         });
    //     }

    //     if (isset($request['search'])) {
    //         $query->where(function($q) use ($request, $fillablePlusId) {
    //             foreach ($fillablePlusId as $attr => $value) {
    //                 $q->orWhere($attr, 'like', '%' . $request['search'] . '%');
    //             }
    //             $q->orWhereHas('start_location', function($que) use ($request) {
    //                 $que->where(function($query) use ($request) {
    //                     $query->orWhere('address', 'like', '%' . $request['search'] . '%');
    //                     $query->orWhere('name', 'like', '%' . $request['search'] . '%');
    //                 });
    //             });
    //             $q->orWhereHas('category_characteristics', function ($que) use ($request) {
    //                 $que->where(function($query) use ($request) {
    //                     $query->where('value', 'like', '%' . $request['search'] . '%');
    //                 });
    //             });
    //         });
    //     }

    //     return $query;
    // }
}
