<div class="media-cont">
    <div class="media-body">
        <div class="chat-info">
            <div class="chat-name">
                {{ $thread->subject ? $thread->subject : ($thread->participantsWithTrashed()->count() >= 3 ? 'Диалог с пользователями' : 'Диалог с ' . $thread->participantsString(Auth::id())) }}
            </div>
            @if($thread->latest_message)
                <div class="last-message-time">
                    {{ (\Carbon\Carbon::parse($thread->latest_message->created_at)->isToday() ? \Carbon\Carbon::parse($thread->latest_message->created_at)->format('H:i') : \Carbon\Carbon::parse($thread->latest_message->created_at)->format('d.m.Y H:i')) }}
                </div>
            @endif
        </div>
        <div class="meta-cont">
            @if($thread->latest_message)
                <div class="last-message-info">
                    {{ ($thread->latest_message->user->id == Auth::id() ? 'Вы: ' : $thread->latest_message->user->full_name . ': ') . ($thread->latest_message->body != ' ' ? $thread->latest_message->body : 'Сообщение') }}
                </div>
                @if($thread->userUnreadMessagesCount(Auth::id()) and ! isset($noCount))
                    <div class="message-number mess-thread_{{ $thread->id }}">
                        {{ $thread->userUnreadMessagesCount(Auth::id()) }}
                    </div>
                @endif
            @else
                <div class="last-message-info">В диалоге нет сообщений</div>
            @endif
        </div>
    </div>
</div>
