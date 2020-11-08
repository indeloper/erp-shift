<div class="card-body task-body">
    <div class="row">
        <div id="main_descr" class="col-md-12">
            <h6 style="margin-top:0">
                Описание
            </h6>
            <div id="description">
                <p>
                    Сформирована новая версия коммерческого предложения. Необходимо провести контроль изменений и при необходимости исправить существующие или сформировать новые.
                </p>
                @if ($com_offers->find($task->target_id))
                    <a href="{{ route('projects::commercial_offer::card_' . ($com_offers->find($task->target_id)->is_tongue ? 'tongue' : 'pile'), [$task->project_id, $com_offers->find($task->target_id)->id]) }}">
                        @if ($com_offers->find($task->target_id)->version > 1) Обновлённое коммерческое @else Коммерческое @endif предложение
                    </a>
                @endif
            </div>
        </div>
    </div>
</div>
<div class="card-footer">
    <div class="row" style="margin-top:25px">
        <div class="col-md-3 btn-center">
            <a href="{{ route('tasks::index') }}" class="btn btn-wd">Назад</a>
        </div>
        <div class="col-md-9 text-right btn-center">
            @if(Auth::id() == $task->responsible_user_id and ! $task->is_solved)
                <a href="{{ $target }}" rel="tooltip" class="btn btn-success">
                    Подтвердить
                </a>
            @endif
        </div>
    </div>
</div>
