<?php

namespace App\Model\Messenger;

use Illuminate\Database\Eloquent\Model;

class MessageFile extends Model
{
    const FILE_PATH = 'storage/docs/message_files/';
    const PICTURES = [
        'bmp', 'gif', 'jpe', 'jpeg', 'jpg', 'svg',
        'tif', 'tiff', 'ico', 'rgb', 'rgb', 'png'
    ];

    protected $fillable = [
        'message_id',
        'user_id',
        'file_name',
        'original_name',
        'path',
        'type'
    ];

    // WDIM -> What Does It Mean
    public $WDIM_type = [
        1 => 'any file, but not pictures',
        2 => 'pictures'
    ];

    protected $appends = ['url'];

    public function message()
    {
        return $this->belongsTo(Message::class);
    }

    // return url to file
    public function getUrlAttribute()
    {
        return asset($this::FILE_PATH . $this->file_name);
    }
}
