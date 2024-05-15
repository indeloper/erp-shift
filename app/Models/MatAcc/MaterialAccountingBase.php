<?php

namespace App\Models\MatAcc;

use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use App\Models\Comment;
use App\Models\Manual\ManualMaterial;
use App\Models\ProjectObject;
use App\Traits\Commentable;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;

/**
 * @property int id
 * @property int object_id
 * @property int manual_material_id
 * @property string date
 * @property string count
 * @property string unit
 * @property int used
 * @property int ancestor_base_id
 *
 * Also there is MaterialAccountingBaseObserver. ancestor_base_id is set there.
 */
class MaterialAccountingBase extends Model
{
    use Commentable;
    use HasFactory;

    protected $fillable = [
        'object_id',
        'manual_material_id',
        'date',
        'count',
        'unit',
        'used',
        'ancestor_base_id',
    ];

    protected $appends = ['round_count', 'convert_params', 'material_name'];

    protected $casts = ['used' => 'boolean'];

    public static $filter = [
        ['id' => 0, 'text' => 'Объект', 'db_name' => 'object_id'],
        ['id' => 1, 'text' => 'Материал', 'db_name' => 'manual_material_id'],
        ['id' => 3, 'text' => 'Эталон', 'db_name' => 'manual_reference_id'],
    ];

    /**
     * Scope for operations index page
     *
     * @return Builder
     */
    public function scopeIndex(Builder $query): Builder
    {
        $query->where('date', Carbon::now()->format('d.m.Y'))
            ->with('object', 'material.parameters.attribute', 'material.convertation_parameters')
            ->where('count', '>', 0)
            ->take(20);

        return $query;
    }

    public function getRoundCountAttribute()
    {
        return number_format(round($this->count, 3), 3);
    }

    /**
     * This getter return base material name
     * with optional 'Б/У' label
     *
     * @return string
     */
    public function getMaterialNameAttribute(): string
    {
        return $this->material->name.($this->used ? ' Б/У' : '');
    }

    public function comments(): MorphMany
    {
        return $this->morphMany(Comment::class, 'commentable');
    }

    public function object(): BelongsTo
    {
        return $this->belongsTo(ProjectObject::class, 'object_id', 'id');
    }

    public function operations(): BelongsTo
    {
        return $this->belongsTo(MaterialAccountingOperation::class, 'object_id_to', 'object_id_from');
    }

    public function material(): BelongsTo
    {
        return $this->belongsTo(ManualMaterial::class, 'manual_material_id', 'id')
            ->leftJoin('manual_material_categories', 'manual_material_categories.id', '=', 'manual_materials.category_id')
            ->select('manual_materials.*', 'manual_material_categories.id as cat_id', 'manual_material_categories.category_unit')
            ->withTrashed();
    }

    public function getSiblingsAttribute()
    {
        return MaterialAccountingBase::where([
            'manual_material_id' => $this->manual_material_id,
            'date' => $this->date,
            'object_id' => $this->object_id,
        ])
            ->where('count', '>', 0)
            ->where('id', '!=', $this->id)
            ->with('comments')
            ->get()
            ->each->setAppends(['round_count', 'convert_params', 'comment_name_count', 'comment_name', 'material_name']);
    }

    /**
     * returns same bases (with same comments) for all history
     * be careful it also return current model
     *
     * to get same material (but different comment) bases use siblings accessor
     */
    public function historyBases(): HasMany
    {
        return $this->hasMany(MaterialAccountingBase::class, 'ancestor_base_id', 'ancestor_base_id');
    }

    public function getCommentsInline(): string
    {
        $comments_inline = $this->comments()->get()->implode('comment', ', ');
        if (strlen($comments_inline) == 0) {
            $comments_inline = 'Примечания отсутствуют';
        }
        if (mb_strlen($comments_inline) > 26) {
            $comments_inline = trim(mb_substr($comments_inline, 0, 23)).'...';
        }

        return $comments_inline;
    }

    public function getCommentNameAttribute()
    {
        $comments = $this->getCommentsInline();

        return $this->material_name." ($comments)";
    }

    public function getCommentNameCountAttribute()
    {
        return "$this->comment_name $this->round_count $this->unit";
    }

    public function backdating($materials, Carbon $back_date, $object_id)
    {
        for ($date = $back_date; $date->lte(Carbon::today()); $date->addDay()) {
            $dates[] = $date->format('d.m.Y');
        }

        foreach ($materials as $material) {
            foreach ($dates as $key => $date) {
                $base = MaterialAccountingBase::firstOrNew(['object_id' => $object_id, 'manual_material_id' => $material['material_id'], 'date' => Carbon::parse($date)->format('d.m.Y')]);
                if (count($dates) > $key + 1) {
                    $base->transferred_today = 1;
                }
                $base->count += $material['material_count'];
                $base->save();
            }
        }
    }

    public function getConvertParamsAttribute()
    {
        if (isset($this->material)) {
            return $this->material->convert_from($this->unit);
        } else {
            return collect();
        }
    }

    public function getAllConvertedAttribute()
    {
        $unordered_units = [[
            'count' => $this->round_count,
            'unit' => $this->unit,
        ],
        ];
        foreach ($this->convert_params as $param) {
            $unordered_units[] = [
                'count' => number_format(round(($param->value * $this->count), 3), 3),
                'unit' => $param->unit,
            ];
        }

        $unit_order = [
            'шт',
            'м.п',
            'т',
            'м2',
            'м3',
        ];

        $ordered_units = [];
        foreach ($unit_order as $unit) {
            $param = array_values(array_filter($unordered_units, function ($uno_param) use ($unit) {
                return $uno_param['unit'] == $unit;
            }));

            if (count($param)) {
                $ordered_units[] = $param[0];
            }
        }

        return $ordered_units;
    }

    public function getCreatedAtAttribute($date)
    {
        return \Carbon\Carbon::parse($date)->format('d.m.Y H:i:s');
    }

    public function getUpdatedAtAttribute($date)
    {
        return \Carbon\Carbon::parse($date)->format('d.m.Y H:i:s');
    }

    /**
     * Function make used material bases from new
     * and new from used material bases
     */
    public function moveTo(string $state, Request $request): void
    {
        $used = $state === 'new' ? 0 : 1;
        $baseCount = $this->count;
        $count = $request->count;
        $baseNewCount = round($baseCount - $count, 3);
        $this->update(['count' => $baseNewCount]);
        $existedBase = MaterialAccountingBase::where('object_id', $this->object_id)->where('manual_material_id', $this->manual_material_id)->where('date', Carbon::today()->format('d.m.Y'))->where('used', $used)->first();

        if ($existedBase) {
            $existedCount = $existedBase->count;
            if ($existedBase->unit != $this->unit) {
                $count = $count * $this->convert_params->where('unit', $existedBase->unit)->first()->value ?? 0;
            }

            if ($count == 0) {
                return;
            }

            foreach ($this->comments as $comment) {
                if (! $existedBase->comments()->where('comment', $comment->comment)->exists()) {
                    $new_comment = $comment->replicate();
                    $new_comment->commentable_id = $existedBase->id;
                    $new_comment->save();
                }
            }

            $existedBase->update(['count' => round($existedCount + round($count, 3), 3)]);
        } else {
            $newBase = $this->replicate();
            $newBase->save();
            foreach ($this->comments as $comment) {
                $new_comment = $comment->replicate();
                $new_comment->commentable_id = $newBase->id;
                $new_comment->save();
            }
            $newBase->update(['count' => round($count, 3), 'used' => $used]);
        }
    }
}
