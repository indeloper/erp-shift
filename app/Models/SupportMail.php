<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Http\Request;

class SupportMail extends Model
{
    use HasFactory;

    protected $fillable = ['title', 'description', 'user_id', 'page_path', 'status', 'solved_at', 'estimate', 'result_description', 'gitlab_link'];

    protected $appends = ['status_name'];

    // array with ticket statuses
    public $statuses = [
        'new' => 'Новая',
        'in_work' => 'В работе',
        'matching' => 'Согласование',
        'resolved' => 'Закрыта',
        'accept' => 'Согласовано',
        'decline' => 'Не согласовано',
        'development' => 'Разработка',
        'check' => 'Проверка',
    ];

    // this relation return all ticket files
    public function files(): HasMany
    {
        return $this->hasMany(SupportMailFile::class, 'support_mail_id', 'id');
    }

    // this relation return ticket sender
    public function sender(): HasOne
    {
        return $this->hasOne(User::class, 'id', 'user_id');
    }

    public function getStatusNameAttribute()
    {
        return $this->statuses[$this->status];
    }

    /**
     * Basic scope
     */
    public function scopeBasic(Builder $query, Request $request): Builder
    {
        $query->orderByRaw("(CASE WHEN status IN ('new', 'in_work', 'matching', 'accept', 'development', 'check') THEN id END) DESC")
            ->orderByRaw("CASE WHEN status = 'decline' THEN 1 ELSE 2 END ASC")
            ->with('files', 'sender');

        if (auth()->user()->hasLimitMode(0)) {
            $query->where('user_id', Auth::id());
        }

        if ($request->search) {
            $query->where(function ($que) use ($request) {
                $que->where('title', 'like', '%'.$request->search.'%')
                    ->orWhere('description', 'like', '%'.$request->search.'%')
                    ->orWhere('id', 'like', '%'.$request->search.'%')
                    ->orWhereHas('sender', function ($q) use ($request) {
                        $q->where('last_name', 'like', '%'.$request->search.'%')
                            ->orWhere('first_name', 'like', '%'.$request->search.'%')
                            ->orWhere('patronymic', 'like', '%'.$request->search.'%');
                    });
            });
        }

        return $query;
    }
}
