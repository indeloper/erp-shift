<style>
    .btn-drop {
        border-radius: 3px!important;
        margin-top: 3px!important;
    }

    .btn-drop li button {
        font-size: 14px;
    }

    .btn-drop li {
        padding: 3px 20px;
        border-bottom: 1px solid #dadada;
    }

    .btn-drop li:last-child {
        border-bottom: 0;
        padding-bottom: 4px;
    }
</style>

<div class="card-body task-body">
    <div class="row">
        <div id="main_descr" class="col-md-12" style="display: inline-block">
            <h6 style="margin-top:0">
                Описание
            </h6>
            <div id="description">
                <p>Необходимо согласовать коммерческое предложение.</p>
                @if($com_offers->where('id', $task->target_id)->first()->comments->count() > 0)
                    <h6>
                        {{'Комментарий (' .  $com_offers->where('id', $task->target_id)->first()->comments->last()->author->long_full_name . ')' }}
                    </h6>
                    <p> {{ $com_offers->where('id', $task->target_id)->first()->comments->last()->comment }} </p>
                @endif
                @includeWhen(auth()->user()->canWorkWithImportance(), 'projects.modules.importance_toggling')
                @if(! $task->is_solved)
                    @if(Auth::id() == $task->responsible_user_id)
                        <br>
                        <hr style="border-color:#F6F6F6">
                        <form id="form_solve_task" class="form-horizontal" action="{{ route('tasks::solve_task', $task->id) }}" method="post" enctype="multipart/form-data">
                            @csrf
                            <div class="form-group">
                                <div class="row">
                                    <div class="col-sm-6">
                                        <label class="">Результат<star class="star">*</star></label>
                                        <div class="form-group">
                                            <select id="status_result" name="status_result" class="selectpicker" data-title="Результат" data-style="btn-default btn-outline" data-menu-style="dropdown-blue" required>
                                                <option value="accept">Согласовано</option>
                                                <option value="decline">Отклонить</option>
                                            </select>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="form-group collapse" id="comment">
                                <div class="row">
                                    <div class="col-sm-12">
                                        <label class="">Комментарий<star class="star">*</star></label>
                                        <div class="form-group">
                                            <textarea id="result_note" class="form-control textarea-rows" maxlength="300" name="final_note" placeholder="Укажите комментарий" required></textarea>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div id="fromСontainer" class="row d-none">
                                <label class="col-sm-3 col-form-label">Перенести на дату<star class="star">*</star></label>
                                <div class="col-sm-3">
                                    <div class="form-group">
                                        <input id="from" name="revive_at" class="form-control datepicker" autocomplete="off">
                                    </div>
                                </div>
                            </div>
                        </form>
                    @endif
                @else
                    <div class="row">
                        <div class="col-sm-3">
                            <p style="font-size:16px;">
                                <b>Комментарий: </b></p>
                        </div>
                        <div class="col-sm-8">
                            <p style="font-size:14px; padding-left:20px;">
                                Коммерческое предложение {{ $com_offers->where('id', $task->target_id)->first() ? ($com_offers->where('id', $task->target_id)->first()->status == 5 ? 'согласовано' : ' не согласовано') : ' отсутствует' }}
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

                @if(Auth::id() == 6 and $com_offers->where('id', $task->target_id)->first() and !$com_offers->where('id', $task->target_id)->first()->is_signed and $com_offers->where('id', $task->target_id)->first()->status != 1 and $com_offers->where('id', $task->target_id)->first()->file_name and !$com_offers->where('id', $task->target_id)->first()->is_uploaded)
                <div class="dropdown d-inline-block">
                  <button class="btn btn-primary dropdown-toggle mb-2" type="button" data-toggle="dropdown">Выполнить
                  <span class="caret"></span></button>
                  <ul class="dropdown-menu btn-drop">
                      <li><button style="padding: 0;" form="form_solve_task" class="btn btn-link">Выполнить</button></li>
                      <li>
                          <button style="padding: 0;" id="sign_com_offer_btn" type="submit" class="btn btn-link" onclick="open_sign_modal()">
                              Выполнить и подписать с ЭЦП
                          </button>
                      </li>

                  </ul>
                </div>
                @else
                <button form="form_solve_task" class="btn btn-info">Выполнить</button>
                @endif

                @if($com_offers->find($task->target_id)->mat_splits->count() > 0 and auth()->id() == 6)
                    <a href="{{ route('projects::commercial_offer::card_'. ($com_offers->find($task->target_id)->is_tongue ? 'tongue' : 'pile'), [$task->project_id, $com_offers->find($task->target_id)->id, 'review_mode' => 1])  }}" class="btn btn-success mb-2" style="white-space: pre-wrap">Перейти в режим согласования</a>
                @endif
            @endif
        </div>
    </div>
</div>

@can('work_with_digital_signature')
    @include('sections.ecp')
@endcan
