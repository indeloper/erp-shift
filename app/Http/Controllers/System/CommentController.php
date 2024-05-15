<?php

namespace App\Http\Controllers\System;

use App\Http\Controllers\Controller;
use App\Models\Comment;
use App\Models\FileEntry;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class CommentController extends Controller
{
    public function store(Request $request): Response
    {
        DB::beginTransaction();
        $attributes = $request->all();
        $attributes['author_id'] = Auth::id();

        $comment = $this->system_service->storeComment($attributes);

        DB::commit();

        return response(['data' => compact('comment')]);
    }

    public function update(Request $request, Comment $comment): Response
    {
        DB::beginTransaction();
        //update ticket
        $comment->update($request->all());

        if ($request->deleted_file_ids) {
            //detach old files
            FileEntry::whereIn('id', $request->deleted_file_ids)->delete();
        }
        if ($request->file_ids) {
            //attach new files
            $documents = FileEntry::find($request->file_ids);
            $comment->documents()->saveMany($documents);
            $comment->refresh();
        }

        DB::commit();

        return response(['data' => compact('comment')]);
    }

    public function destroy(Comment $comment): Response
    {
        $comment->delete();

        return response(['status' => 'success']);
    }
}
