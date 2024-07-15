@if(count($notifications->where('is_seen', 0)) > 0)
    <form action="{{ route('notifications::view_all') }}" method="post">
        @csrf
        <button class="btn btn-round btn-outline btn-sm add-btn pull-right mt-10__mobile"
                style="margin-right: 10px;">
            Прочитать всё
        </button>
    </form>
@endif