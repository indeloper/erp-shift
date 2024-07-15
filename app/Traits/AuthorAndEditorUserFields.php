<?php

namespace App\Traits;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Schema;

trait AuthorAndEditorUserFields
{
    public static function bootAuthorAndEditorUserFields()
    {
        static::creating(function ($model) {
            $modelTableName = $model->getTable();
            $user = Auth::user();
            if ($user) {
                if (Schema::hasColumn($modelTableName, 'author_id')) {
                    $model->author_id = $user->id;
                }

                if (Schema::hasColumn($modelTableName, 'editor_id')) {
                    $model->editor_id = $user->id;
                }
            }
        });

        static::updating(function ($model) {
            $modelTableName = $model->getTable();
            if (Schema::hasColumn($modelTableName, 'editor_id')) {
                $user = Auth::user();
                if ($user) {
                    $model->editor_id = $user->id;
                }
            }
        });

        static::deleting(function ($model) {
            $modelTableName = $model->getTable();
            if (Schema::hasColumn($modelTableName, 'editor_id')) {
                $user = Auth::user();
                if ($user) {
                    $model->editor_id = $user->id;
                }
            }
        });
    }
}
