<div class="card-body task-body">
    <div class="row">
        <div id="main_descr" class="col-md-12">
            <h6 style="margin-top:0">
                Описание
            </h6>
            <div id="description">
                <p>Необходимо назначить исполнителя на <a href="{{ $task->taskable->card_route() }}">неисправность техники</a></p>

                @if(! $task->is_solved)
                    @if(Auth::id() == $task->responsible_user_id)
                        <br>
                        <hr style="border-color:#F6F6F6">
                        <div>
                            <form id="form_select_user" class="form-horizontal" action="{{ route('building::tech_acc::defects.select_responsible', $task->taskable->id) }}" method="post">
                                @method('put')
                                @csrf
                                <div class="row">
                                    <div class="col-md-12 text-left">
                                        <label>Исполнитель<star class="star">*</star></label>
                                        <div class="form-group">
                                            <select name="user_id" id="js-select-responsible" style="width:100%;" data-title="Выберите исполнителя" data-style="btn-default btn-outline" data-menu-style="dropdown-blue" required>
                                                <option value="">Выберите исполнителя</option>
                                            </select>
                                        </div>
                                    </div>
                                </div>
                            </form>
                        </div>
                    @endif
                @elseif($task->is_solved)
                    <div class="row">
                        <div class="col-sm-12">
                            <p>
                                Исполнителем назначен пользователь
                                <a href="{{ route('users::card', $task->taskable->responsible_user_id) }}">
                                    {{ $task->taskable->responsible_user->long_full_name }}
                                </a>
                            </p>
                        </div>
                    </div>
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
                <button class="btn btn-info" form="form_select_user">Выбрать ответственного</button>
            @endif
        </div>
    </div>
</div>
