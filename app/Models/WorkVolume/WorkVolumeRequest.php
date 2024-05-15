<?php

namespace App\Models\WorkVolume;

use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use App\Models\FileEntry;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;

class WorkVolumeRequest extends Model
{
    protected $fillable = [
        'name',
        'user_id',
        'project_id',
        'work_volume_id',
        'status',
        'tongue_description',
        'tongue_soil_description',
        'pile_description',
        'pile_soil_description',
    ];

    public $tongue_pile_names = [
        0 => 'Шпунт',
        1 => 'Сваи',
    ];

    public $request_status = [
        0 => 'Не просмотрен',
        1 => 'Положительный',
        2 => 'Отрицательный',
    ];

    public function files(): HasMany
    {
        return $this->hasMany(WorkVolumeRequestFile::class, 'request_id', 'id');
    }

    public function user(): HasOne
    {
        return $this->hasOne(User::class, 'id', 'user_id');
    }

    // this function find WV for WV Request
    public function wv(): HasOne
    {
        return $this->hasOne(WorkVolume::class, 'id', 'work_volume_id');
    }

    public function save_documents($documents = [])
    {
        if ($documents) {
            foreach ($documents as $document) {
                $file = new WorkVolumeRequestFile();

                $mime = $document->getClientOriginalExtension();
                $file_name = 'project-'.$this->project_id.'/work_volume'.$this->work_volume_id.'request_file-'.uniqid().'.'.$mime;

                Storage::disk('work_volume_request_files')->put($file_name, File::get($document));

                FileEntry::create([
                    'filename' => $file_name,
                    'size' => $document->getSize(),
                    'mime' => $document->getClientMimeType(),
                    'original_filename' => $document->getClientOriginalName(),
                    'user_id' => Auth::user()->id,
                ]);

                $file->file_name = $file_name;
                $file->request_id = $this->id;
                $file->is_result = 0;
                $file->original_name = $document->getClientOriginalName();

                $file->save();
            }
        }
    }

    public function addach_project_documents($project_documents)
    {
        if ($project_documents) {
            $project_docs = ProjectDocument::whereIn('id', $project_documents)->get();

            foreach ($project_docs as $document) {
                $file = new WorkVolumeRequestFile();

                $file->file_name = $document->file_name;
                $file->request_id = $this->id;
                $file->is_result = 0;
                $file->original_name = $document->name;
                $file->is_proj_doc = 1;

                $file->save();
            }
        }
    }

    public function getCreatedAtAttribute($date)
    {
        return \Carbon\Carbon::parse($date)->format('d.m.Y H:i:s');
    }

    public function getUpdatedAtAttribute($date)
    {
        return \Carbon\Carbon::parse($date)->format('d.m.Y H:i:s');
    }
}
