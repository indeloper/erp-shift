<?php

namespace App\Models\q3wMaterial;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Auth;

class q3wMaterialSnapshot extends Model
{
    use SoftDeletes;

    protected $guarded = ['id'];

    public function takeSnapshot($operation, $projectObject)
    {
        $this->operation_id = $operation->id;
        $this->project_object_id = $projectObject->id;

        $this->save();

        $actualMaterials = q3wMaterial::where('project_object', '=', $projectObject->id)->get();
        foreach ($actualMaterials as $actualMaterial) {
            if (isset($actualMaterial->comment_id)) {
                $snapshotCommentText = q3wMaterialComment::findOrFail($actualMaterial->comment_id)->comment;

                $snapshotComment = new q3wMaterialSnapshotMaterialComment([
                    'comment' => $snapshotCommentText,
                    'author_id' => Auth::id(),
                ]);

                $snapshotComment->save();
                $snapshotCommentId = $snapshotComment->id;
            } else {
                $snapshotCommentId = null;
            }

            $snapshotMaterial = new q3wMaterialSnapshotMaterial([
                'snapshot_id' => $this->id,
                'standard_id' => $actualMaterial->standard_id,
                'amount' => $actualMaterial->amount,
                'quantity' => $actualMaterial->quantity,
                'comment_id' => $snapshotCommentId,
            ]);

            $snapshotMaterial->save();
        }
    }
}
