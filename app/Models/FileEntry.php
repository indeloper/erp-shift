<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FileEntry extends Model
{
    use HasFactory;

    protected $fillable = ['filename', 'size', 'mime', 'original_filename', 'user_id', 'documentable_id', 'documentable_type'];

    protected $appends = ['source_link', 'label', 'name'];

    /**
     * Getter for tech acc files source link attribute
     *
     * @return string|null
     */
    public function getSourceLinkAttribute()
    {
        if (! $this->documentable_type) {
            return null;
        }

        return asset('storage/docs/tech_accounting/').'/'.$this->filename;
    }

    public function documentable(): MorphTo
    {
        return $this->morphTo();
    }

    public function author(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }

    public function getNameAttribute()
    {
        return $this->original_filename;
    }

    public function getLabelAttribute()
    {
        return $this->original_filename;
    }
}
