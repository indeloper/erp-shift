@extends('layouts.app')

@section('title', 'Контрагенты')

@section('url', route('contractors::index'))

@section('css_top')
<style>
    .rTable {
        display: table;
        width: 100%;
        margin-bottom: 1rem;
        background-color: transparent;
        border-collapse: collapse;
        border-spacing: 2px;
        border-color: grey;
    }
    .rTableRow {
        display: table-row;
        padding: .75rem;
        vertical-align: top;
        border-top: 1px solid #dee2e6;
        font-size: 14px;
    }
    .rTableHeading {
        display: table-header-group;
        background-color: #ddd;
    }
    .rTableCell, .rTableHead {
        display: table-cell;
    }
    .rTableHeading {
        display: table-header-group;
        background-color: #ddd;
        font-weight: bold;
    }
    .rTableFoot {
        display: table-footer-group;
        font-weight: bold;
        background-color: #ddd;
    }
    .rTableBody {
        display: table-row-group;
    }
    .rTableHead span {
        font-size: 16px;
        font-weight: 600;
    }
</style>

@endsection

@section('content')

<nav aria-label="breadcrumb" role="navigation">
    <ol class="breadcrumb">
        <li class="breadcrumb-item">
            <a href="{{ route('contractors::card', $contractor->id) }}" class="table-link">{{ $contractor->short_name }}</a>
        </li>
        <li class="breadcrumb-item active" aria-current="page">События</li>
    </ol>
</nav>
<div class="card">
    <div class="fixed-table-toolbar toolbar-for-btn">
        <div class="fixed-search">
            <input class="form-control" type="text" placeholder="Поиск">
        </div>
        <div class="pull-right">
            <button class="btn btn-round btn-outline btn-sm add-btn" data-toggle="modal" data-target="#add-new-task">
                <i class="glyphicon fa fa-plus"></i>
                Добавить
            </button>
        </div>
    </div>
    <div class="table-responsive">
        <table class="table  table-hover mobile-table">
            <thead>
                <tr>
                    <th>Дата создания</th>
                    <th>Дата исполнения</th>
                    <th>Событие</th>
                    @if($solved_tasks->groupBy('project_id')->count() > 1)
                    <th>Проект</th>
                    @endif
                    <th>Исполнитель</th>
                    <th>Автор</th>
                    <th style="display:none"></th>
                </tr>
            </thead>
            @include('sections.history_for_tasks')
        </table>
    </div>
</div>

<!--Модалка добавить+-->
<div class="modal fade bd-example-modal-lg show" id="add-new-task" role="dialog" aria-labelledby="modal-search" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Новая задача</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">×</span>
                </button>
            </div>
            <div class="modal-body">
                <div class="card border-0">
                    <div class="card-body ">
                        <form id="create_task_form" class="form-horizontal" action="{{ route('tasks::store') }}" method="post" enctype="multipart/form-data">
                            @csrf
                            <div class="row">
                                <label class="col-sm-3 col-form-label">Название<star class="star">*</star></label>
                                <div class="col-sm-9">
                                    <div class="form-group">
                                        <input class="form-control" type="text" name="name" required maxlength="50">
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                <label class="col-sm-3 col-form-label">Описание<star class="star">*</star></label>
                                <div class="col-sm-9">
                                    <div class="form-group">
                                        <textarea class="form-control textarea-rows" name="description" required maxlength="250"></textarea>
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                <label class="col-sm-3 col-form-label">Контрагент<star class="star">*</star></label>
                                <div class="col-sm-6">
                                    <div class="form-group">
                                        <select id="js-select-contractor" name="contractor_id"  style="width:100%;" disabled>
                                            <option value="{{ $contractor->id }}" selected>{{ $contractor->short_name }}. ИНН: {{ $contractor->inn }}</option>
                                        </select>
                                        <input type="hidden" value="{{ $contractor->id }}" name="contractor_id">
                                    </div>
                                </div>
                            </div>
                            <div id="project_choose" class="row">
                                <label class="col-sm-3 col-form-label">Проект</label>
                                <div class="col-sm-6">
                                    <div class="form-group">
                                        <select name="project_id" id="js-select-project" style="width:100%;">
                                            <option value="">Не выбрано</option>
                                        </select>
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                <label class="col-sm-3 col-form-label">Ответственное лицо<star class="star">*</star></label>
                                <div class="col-sm-6">
                                    <div class="form-group">
                                        <select id="js-select-user" name="responsible_user_id"  style="width:100%;" required>
                                        </select>
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                <label class="col-sm-3 col-form-label">Срок выполнения<star class="star">*</star></label>
                                <div class="col-sm-6">
                                    <div class="form-group">
                                        <input id="datetimepicker" name="expired_at" type="text" min="{{ \Carbon\Carbon::now()->addMinutes(30) }}" class="form-control datetimepicker" placeholder="Укажите дату" required>
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                <label class="col-sm-3 col-form-label" for="" style="font-size:0.80">Приложенные файлы</label>
                                <div class="col-sm-6">
                                    <div class="file-container">
                                        <div id="fileName" class="file-name"></div>
                                        <div class="file-upload ">
                                            <label class="pull-right">
                                                <span><i class="nc-icon nc-attach-87" style="font-size:25px; line-height: 40px"></i></span>
                                                <input type="file" name="documents[]" accept="*" id="uploadedFile" class="form-control-file file" onchange="getFileName(this)" multiple>
                                            </label>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Закрыть</button>
                <button type="submit" form="create_task_form" class="btn btn-info">Сохранить</button>
            </div>
        </div>
    </div>
</div>

@endsection

@section('js_footer')
    <script>
        $('input#uploadedFile4').change(function(){
            var files = $(this)[0].files;
            document.getElementById('fileName4').innerHTML = 'Количество файлов: ' + files.length;
            if(files.length === 1) {
                document.getElementById('fileName4').innerHTML = 'Имя файла: ' + $('#uploadedFile4').val().split('\\').pop();
                $('#submit').removeClass('d-none');
            } else if(files.length > 10){
                swal({
                    title: "Внимание",
                    text: "К задаче можно прикрепить не более десяти файлов!",
                    type: 'warning',
                });
                $('#submit').addClass('d-none');
            } else {
                $('#submit').removeClass('d-none');
            }
        });

        $('input#uploadedFile3').change(function(){
            var files = $(this)[0].files;
            document.getElementById('fileName3').innerHTML = 'Количество файлов: ' + files.length;
            if(files.length === 1) {
                document.getElementById('fileName3').innerHTML = 'Имя файла: ' + $('#uploadedFile3').val().split('\\').pop();
                $('#submit').removeClass('d-none');
            } else if(files.length > 10){
                swal({
                    title: "Внимание",
                    text: "К задаче можно прикрепить не более десяти файлов!",
                    type: 'warning',
                });
                $('#submit').addClass('d-none');
            } else {
                $('#submit').removeClass('d-none');
            }
        });

        $('#js-select-contractor').select2({
            language: "ru",
            ajax: {
                url: '/tasks/get-contractors',
                dataType: 'json',
                delay: 250
            }
        });

        $('#js-select-project').select2({
            language: "ru",
            ajax: {
                url: '/tasks/get-projects?contractor_id=' + $('#js-select-contractor').select2('val'),
                dataType: 'json',
                delay: 250
            }
        });

        $('#js-select-user').select2({
            language: "ru",
            ajax: {
                url: '/tasks/get-users',
                dataType: 'json',
                delay: 250
            }
        });

        Date.prototype.addMinutes = function(m) {
            this.setMinutes(this.getMinutes() + m);
            return this;
        }

        add_datetime();

        setInterval(function(){
            $('#datetimepicker').datetimepicker('destroy');
            add_datetime();
        }, 55000);

        function add_datetime() {
            $('#datetimepicker').datetimepicker({
                minDate: new Date().addMinutes(32),
                icons: {
                    time: "fa fa-clock-o",
                    date: "fa fa-calendar",
                    up: "fa fa-chevron-up",
                    down: "fa fa-chevron-down",
                    previous: 'fa fa-chevron-left',
                    next: 'fa fa-chevron-right',
                    today: 'fa fa-screenshot',
                    clear: 'fa fa-trash',
                    close: 'fa fa-remove'
                },
            });
        }
    </script>
@endsection
