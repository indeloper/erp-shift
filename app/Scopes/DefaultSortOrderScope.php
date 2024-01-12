<?php

namespace App\Scopes;

use Illuminate\Database\Eloquent\Scope;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;

class DefaultSortOrderScope implements Scope
{
    public function apply(Builder $builder, Model $model)
    {
        if (!$builder->getQuery()->orders) {
            if (isset($model->defaultSortOrder)){
                foreach ($model->defaultSortOrder as $key => $value){
                    if ($value == 'raw'){
                        $builder->orderByRaw($key);
                    } else {
                        $builder->orderBy($model->getTable().'.'.$key, $value);
                    }
                }
            }
        }
    }
}
