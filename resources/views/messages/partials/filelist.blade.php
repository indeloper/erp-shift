@if(count($message->files))
    <div class="file-list">
        @foreach($message->files as $message_file)
            <div style="white-space:nowrap">
                <a class="task-info_file pull-left" target="_blank" href="{{ $message_file->url }}" data-original-title="{{ $message_file->created_at }} {{ $message_file->original_name }}" style="display:contents">
                    {{ $message_file->original_name }}
                </a>
                <button type="button" class="btn btn-link btn-xs" onclick="removeFile({{ $message_file->id }}, this)" style="position:relative; display:contents">
                    <i class="fa fa-times"></i>
                </button>
            </div>
        @endforeach
    </div>
@endif
