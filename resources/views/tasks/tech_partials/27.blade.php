<div class="card-body task-body">
    <div class="row">
        <div id="main_descr" class="col-md-12">
            <h6 style="margin-top:0">
                Описание
            </h6>
            <div id="description">
                <p>Согласование продления использования</p>
                <p>{{ $task->description }}</p>

                @if(!$task->is_solved)
                    @if(Auth::id() == $task->responsible_user_id)
                        <br>
                        <hr style="border-color:#F6F6F6">
                        <div class="text-center">
                            <form id="form_agree" class="form-horizontal" action="{{ route('building::tech_acc::our_technic_tickets.agree_extension', $task->taskable->id) }}" method="post">
                                @csrf
                                <div class="row">
                                    <div class="col-md-12 text-left">
                                        <label>Согласование<star class="star">*</star></label>
                                        <div class="form-group">
                                            <select name="agree" id="js_agree_extension" class="selectpicker" style="width:100%;" data-style="btn-default btn-outline" data-menu-style="dropdown-blue" required>
                                                <option value="1">Согласовать</option>
                                                <option value="0">Отклонить</option>
                                            </select>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-12 text-left">
                                    <label id="final_note_label" class="">Комментарий</label>
                                    <div class="form-group">
                                        <textarea id="final_note" class="form-control textarea-rows" maxlength="1000" name="final_note" placeholder="Укажите комментарий"></textarea>
                                    </div>
                                </div>
                            </form>
                        </div>
                    @endif
                @elseif($task->is_solved)
                    <div class="row">
                        <div class="col-sm-12">
                            <p style="font-size:16px;">
                                <b>Комментарий: </b><br>
                                {{ $task->final_note }}
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
        <div class="col-md-10 btn-center">
            <a href="{{ route('tasks::index') }}" class="btn btn-wd">Назад</a>
        </div>
        @if(!$task->is_solved)
            @if(Auth::id() == $task->responsible_user_id)
            <div class="col-md-2 btn-center">
                <button class="btn btn-primary" form="form_agree">Подтвердить</button>
            </div>
            @endif
        @endif

    </div>
</div>

@push('js_footer')
<script>
$('#js_agree_extension').on('change', function() {
    if ($('#js_agree_extension').val() == 0) {
        $('#final_note').attr('required', 'required');
        $('#final_note_label').html('Комментарий<star class="star">*</star>')

    } else {
        $('#final_note').removeAttr('required');
        $('#final_note_label').html('Комментарий')

    }
});
</script>
@endpush
