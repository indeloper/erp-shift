@foreach($related_messages as $relation)
<div class="">
    <div style="margin-bottom:5px">
        <span class="author-forwarded">{{ $relation->forwarded_message->user->full_name }}</span>
        <span class="date-forwarded">{{ $relation->forwarded_message->created_at }} @if(\Carbon\Carbon::parse($relation->forwarded_message->created_at)->ne($relation->forwarded_message->updated_at)), измененено  @endif</span>
    </div>
    <span class="message-forwarded">{{ $relation->forwarded_message->body }}</span>
    @if(count($relation->forwarded_message->files))
        @foreach($relation->forwarded_message->files as $message_file)
            <a class="task-info_file" target="_blank" href="{{ $message_file->url }}" data-original-title="{{ $message_file->created_at }} {{ $message_file->original_name }}">
                {{ $message_file->original_name }}
            </a>
        @endforeach
    @endif
    @if(is_object($relation))
        @if(count($relation->forwarded_message->related_messages))
            <blockquote cite="http://">
                @include('messages.partials.related_messages', ['related_messages' => $relation->forwarded_message->related_messages])
            </blockquote>
        @endif
    @endif
</div>
@endforeach
