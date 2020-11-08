<div class="chat-item chat-{{ $message->user->id == Auth::id() ? 'right' : 'left' }} msg_id_{{ $message->id }}">
    <div class="chat-details">
        <div class="chat-text">
            {{ $message->body }}
            @if(count($message->files))
                @foreach($message->files as $message_file)
                    @if($message_file->type == 2)
                    <a class="task-info_file" target="_blank" href="{{ $message_file->url }}">
                        <img src="{{ asset($message_file->url) }}" width="250" class="message-img">
                    </a>
                    @else
                    <a class="task-info_file" target="_blank" href="{{ $message_file->url }}">
                        {{ $message_file->original_name }}
                    </a>
                    @endif
                @endforeach
            @endif
            @if(count($message->related_messages))
                <!-- <p>пересланные сообщения</p> -->
                @foreach($message->related_messages as $relation)
                    <blockquote class="blockquote-chat">
                        <span class="blockquote-message">{{ $relation->forwarded_message->body }}</span><br/>
                        <span class="blockquote-author">{{ $relation->forwarded_message->user->full_name }}</span>
                    </blockquote>
                    @if(count($relation->forwarded_message->related_messages))
                        <button class="btn btn-sm btn-link show-all-messages pull-right" onclick="showAllRelatedMessages({{ $message }})">Показать все сообщения</button>
                        @break
                    @endif
                @endforeach
            @endif
        </div>
        <div class="chat-time">
            {{ ($message->updated_at->gt($message->created_at) ? 'Изменено. ' : '')
            . ($message->user->id == Auth::id() ? 'Вы: ' : $message->user->full_name . ':')
             . ' ' . (\Carbon\Carbon::parse($message->created_at)->isToday() ? \Carbon\Carbon::parse($message->created_at)->format('H:i') : \Carbon\Carbon::parse($message->created_at)->format('d.m.Y H:i')) }}
        </div>
            <div class="message-check" onclick="selectMsg({{ $message }}, $(this).parent().parent())" style="height: 100%;width: 100%;">
                <!-- <img src="{{ mix('img/check-message.png') }}"  style="width: 15px"> -->
            </div>
    </div>
</div>
