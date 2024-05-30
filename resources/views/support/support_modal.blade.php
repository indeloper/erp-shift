<div class="modal fade bd-example-modal-lg show" id="support_modal" role="dialog" aria-labelledby="modal-search" style="display: none;">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Заявка в техническую поддержку</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">×</span>
                </button>
            </div>
            <div class="modal-body">
                <hr style="margin-top:0">
                <div class="card border-0" >
                    <div class="card-body">
                        <form id="support_modal_form" class="form-horizontal" action="{{ route('support::support_send_mail') }}" method="post" enctype="multipart/form-data">
                            @csrf
                            <input name="page_path" type="hidden" value="{{ Request::getRequestUri() }}">
                            <div class="row">
                                <div class="col-md-12">
                                    <label>Тема<span class="star">*</span></label>
                                    <div class="form-group">
                                        <input class="form-control" type="text" name="title" required maxlength="200">
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-12">
                                    <label>Описание<span class="star">*</span></label>
                                    <div class="form-group">
                                        <textarea class="form-control textarea-rows" name="description" maxlength="3000" required></textarea>
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-12" style="padding-top:0px;">
                                    <label>Файлы</label>
                                    <div class="file-container">
                                        <div id="fileName" class="file-name"></div>
                                        <div class="file-upload ">
                                            <label class="pull-right">
                                                <span><i class="nc-icon nc-attach-87" style="font-size:25px; line-height: 40px"></i></span>
                                                <input type="file" accept="*" name="images[]" onchange="getFileName(this)" class="form-control-file file" multiple>
                                            </label>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            @if(Auth::user()->id == 1)
                            <div class="row">
                                <div class="col-md-12">
                                    <label>Инициатор задачи</label>
                                    <div class="form-group">
                                        <select id="user" name="user_id" style="width:100%;">
                                        </select>
                                    </div>
                                </div>
                            </div>
                            @endif
                        </form>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Закрыть</button>
                <button form="support_modal_form" type="submit" class="btn btn-info">Отправить</button>
            </div>
        </div>
    </div>
</div>
@if(Auth::user()->id == 1)

@push('js_footer')
<script>

$('#user').select2({
    language: "ru",
    ajax: {
        url: '{{ route('tasks::get_users') }}',
        dataType: 'json',
        delay: 250,
    }
});

</script>
@endpush

@endif
