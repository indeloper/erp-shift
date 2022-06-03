@extends('layouts.app')

@section('title', 'Админимстративый раздел')

@section('content')
    <div>
        <form id="notify_form" method="post" action="{{ route('admin.send_tech_update_notify') }}">
            @csrf
            Техническая поддержка. С
            <label>
                <input id="start_date" class="form-control" type="date" name="start_date" required>
            </label>
            <label>
                <input id="start_time" class="form-control" type="time" name="start_time" required>
            </label>
            по
            <label>
                <input id="finish_date" class="form-control" type="date" name="finish_date" required>
            </label>
            <label>
                <input id="finish_time" class="form-control" type="time" name="finish_time" required>
            </label>
            в ERP-системе (ТУКИ) будут проводиться технические работы. Сервис может быть временно недоступен.
            <button type="button" onclick="submitNotify()" class="btn btn-success">
                Отправить сообщение
            </button>
        </form>
<hr>
        <form method="post" action="{{ route('admin.login_as') }}">
            @csrf
            <div class="row">
                <div class="form-group col-8">
                    <select name="user_id" id="js-select-users" style="width:100%;" data-title="Выберите сотрудника" data-style="btn-default btn-outline" data-menu-style="dropdown-blue" required>
                    </select>
                </div>
                <div class="form-group">
                    <button type="submit" class="btn btn-danger">
                         Хочу стать им
                    </button>
                </div>
            </div>
        </form>
    </div>
@endsection

@push('js_footer')

    <script>
        function submitNotify()
        {
            swal({
                title: "Проверьте текст",
                text: "Техническая поддержка. С " + $('#start_date')[0].value + " " + $('#start_time')[0].value + " по " + $('#finish_date')[0].value + " "  + $('#finish_time')[0].value + "в ERP-системе (ТУКИ) будут проводиться технические работы. Сервис может быть временно недоступен.",
                type: 'warning',
                showCancelButton: true,

            }).then(result => {
                if (result.value) {
                    if ($('#notify_form')[0].reportValidity()){
                        $('#notify_form').submit();
                    }
                } else {
                }
            });
        }

        $('#js-select-users').select2({
            language: "ru",
            maximumSelectionLength: 10,
            ajax: {
                url: '/projects/ajax/get-users',
                dataType: 'json',
                delay: 250,
            },
        });
    </script>
@endpush
