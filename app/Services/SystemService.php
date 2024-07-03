<?php

namespace App\Services;

use App\Models\Comment;
use App\Models\FileEntry;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;

class SystemService
{
    public function storeFileEntries($attributes)
    {
        $stored_file_ids = [];
        if (isset($attributes['file'])) {
            $mime = $attributes['file']->getClientOriginalExtension();
            $file_name = 'File'.uniqid().'.'.$mime;
            Storage::disk('technics')->put($file_name, File::get($attributes['file']));
            $file = FileEntry::create([
                'filename' => $file_name,
                'size' => $attributes['file']->getSize(),
                'mime' => $attributes['file']->getClientMimeType(),
                'original_filename' => $attributes['file']->getClientOriginalName(),
                'user_id' => Auth::user()->id,
            ]);
            $stored_file_ids[] = [
                'id' => $file->id,
                'filename' => $file->filename,
                'label' => $attributes['file']->getClientOriginalName(),
                'name' => $attributes['file']->getClientOriginalName(),
            ];
        }

        return $stored_file_ids;
    }

    public function destroyFileEntry($fileEntry)
    {
        $fileEntry->delete();
    }

    /**
     * @param  array  $attributes  [
     *                             int|string  'commentable_id',
     *                             int|string  'commentable_type',
     *                             string      'comment',
     *                             int|string  'author_id',
     *                             array       'file_ids' [int|string *]
     *                             ]
     */
    public function storeComment(array $attributes, $commentable = null, $text = null): Comment
    {
        $attributes = $this->overrideCommentRequest($attributes, $commentable, $text);

        $comment = Comment::create($attributes);

        $documents = FileEntry::find($attributes['file_ids'] ?? []);
        $comment->documents()->saveMany($documents);
        $comment->load(['files', 'author']);

        return $comment->refresh();
    }

    /**
     * @return mixed
     */
    public function overrideCommentRequest($attributes, $commentable, $text)
    {
        if ($commentable) {
            if (method_exists($commentable, 'comments')) {
                $attributes = array_merge($attributes, [
                    'commentable_id' => $commentable->id,
                    'commentable_type' => $commentable->getMorphClass(),
                ]);
            }
        }
        if ($text) {
            $attributes = array_merge($attributes, [
                'comment' => $text,
            ]);
        }
        if (! array_key_exists('author_id', $attributes)) {
            $attributes['author_id'] = Auth::id();
        }

        return $attributes;
    }

    public static function determineClientDeviceType($userAget)
    {
        if (preg_match("/(android|avantgo|blackberry|bolt|boost|cricket|docomo|fone|hiptop|mini|mobi|palm|phone|pie|tablet|up\.browser|up\.link|webos|wos)/i", $userAget)) {
            return 'mobile';
        }

        return 'desktop';
    }
}
