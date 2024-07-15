@extends('layouts.app')

@section('title', 'Задачи')

@section('url', route('tasks::index'))

@section('content')
<!--ОБРАБОТКА ВХОДЯЩЕГО ЗВОНКА-->

 <div class="row">
     <div class="col-md-12 col-xl-12 mr-auto ml-auto">
         <div class="row task-card">
             <div class="col-md-4 tasks-sidebar">
                 <div class="card tasks-sidebar__item tasks-sidebar__item1">
                    <div class="card-body">
                        <div class="tasks-sidebar__text-unit">
                            <span class="tasks-sidebar__head-title">
                                Время создания
                            </span>
                            <span class="tasks-sidebar__body-title">
                                {{ $call->created_at }}
                            </span>
                        </div>
                        <div class="tasks-sidebar__text-unit">
                            <span class="tasks-sidebar__head-title">
                                Автор
                            </span>
                            <span class="tasks-sidebar__body-title">
                                Система
                            </span>
                        </div>
                        <div class="tasks-sidebar__text-unit">
                            <span class="tasks-sidebar__head-title">
                                Номер телефона
                            </span>
                            <span class="tasks-sidebar__body-title">
                                +{{ substr($call->incoming_phone, 0, 1) .' '. '('. substr($call->incoming_phone, 1, 3) .')'
                                .' '.substr($call->incoming_phone, 4, 3).'-'.substr($call->incoming_phone, 7, 2).'-'.substr($call->incoming_phone, 9, 2) }}
                            </span>
                        </div>
                    </div>
                </div>
                @if(isset($contractor) or $contact)
                    <div class="card tasks-sidebar__item">
                        <div class="card-body">
                            <div class="">
                                @if(isset($contractor->id))
                                    <div class="tasks-sidebar__text-unit">
                                        <span class="tasks-sidebar__head-title">
                                            Контрагент
                                        </span>
                                        <span class="tasks-sidebar__body-title">
                                            <a href="{{ route('contractors::card', $contractor->id) }}" class="tasks-sidebar__link" target="_blank">
                                                {{ $contractor->short_name }} | {{ $call->incoming_phone }}
                                            </a>
                                        </span>
                                    </div>
                                @endif
                                @if($contact)
                                    <div class="tasks-sidebar__text-unit">
                                        <span class="tasks-sidebar__head-title">
                                            Должность
                                        </span>
                                        <span class="tasks-sidebar__body-title">
                                            {{ $contact->position }}
                                        </span>
                                    </div>
                                    <div class="tasks-sidebar__text-unit">
                                        <span class="tasks-sidebar__head-title">
                                            Контактное лицо
                                        </span>
                                        <span class="tasks-sidebar__body-title">
                                            {{ trim($contact->last_name . ' ' . $contact->first_name . ' ' . $contact->patronymic) }}
                                        </span>
                                    </div>
                                    <div class="tasks-sidebar__text-unit">
                                        <span class="tasks-sidebar__head-title">
                                            Номер
                                        </span>
                                        <span class="tasks-sidebar__body-title">
                                            +{{ substr($call->incoming_phone, 0, 1) .' '. '('. substr($call->incoming_phone, 1, 3) .')'
                                            .' '.substr($call->incoming_phone, 4, 3).'-'.substr($call->incoming_phone, 7, 2).'-'.substr($call->incoming_phone, 9, 2) }}
                                        </span>
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>
                @endif
                <div class="card tasks-sidebar__item">
                    <div class="card-body">
                        <div class="accordions" id="links-accordion">
                            <div class="card" style="margin-bottom:0">
                                <div class="card-header">
                                    <h5 class="card-title">
                                        <a class="collapsed tasks-sidebar__collapsed-link" data-target="#collapse1" href="#" data-toggle="collapse">
                                            Вспомогательные ссылки
                                            <b class="caret"></b>
                                        </a>
                                    </h5>
                                </div>
                                <div id="collapse1" class="card-collapse collapse show">
                                    <div class="card-body tasks-sidebar__body-links">
                                        <a href="{{ route('contractors::index') }}" class="tasks-sidebar__help-link" target="_blank">
                                            Контрагенты
                                        </a><br>
                                        <a href="{{ route('projects::index') }}" class="tasks-sidebar__help-link" target="_blank">
                                            Проекты
                                        </a><br>
                                        <a href="{{ route('objects::index') }}" class="tasks-sidebar__help-link" target="_blank">
                                            Объекты
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-8">
                <div class="card">
                    <div class="card-header">
                        <div class="row">
                            <div class="col-md-8 task-header__title">
                                <h4>Обработка входящего звонка</h4>
                            </div>
                            <div class="col-md-4 text-right" style="margin-top:3px;">
                                до
                                <span class="task-header__date">
                                    {{ strftime('%d %B', strtotime($call->expired_at)) }}
                                </span>
                                <span class="task-header__time">
                                    {{ \Carbon\Carbon::parse($call->expired_at)->format('h:m') }}
                                </span>
                                <!-- {{ $call->expired_at }} -->
                            </div>
                        </div>
                        <hr style="margin-top:7px;border-color:#F6F6F6">
                    </div>
                    <div class="card-body task-body">
                        <form id="form_close_call" action="{{ route('tasks::close_call', $call->id) }}" method="post" class="form-horizontal">
                            @csrf
                            <div class="accordions" id="accordion">

                                <!--Информация о контрагенте-->
                                 <div class="card">
                                    <div class="card-header">
                                        <h4 class="card-title">
                                            <a data-target="#collapseContrInfo" href="#" data-toggle="collapse">
                                                Объект разговора
                                                <b class="caret"></b>
                                            </a>
                                        </h4>
                                    </div>
                                    <div id="collapseContrInfo" class="card-collapse collapse show">
                                        <div class="card-body">
                                               @if(Auth::user()->department_id == 14/*6*/)
                                                   <div class="row">
                                                       <div class="col-sm-12">
                                                            <div class="pull-right">
                                                                <a class="btn btn-round btn-outline btn-sm btn-success" href="{{ route('contractors::create', ['task_id' => $call->id]) }}">
                                                                    <i class="glyphicon fa fa-plus"></i>
                                                                    Новый контрагент
                                                                </a>
                                                            </div>
                                                        </div>
                                                   </div>
                                               @endif
                                               <div class="row">
                                                   <label class="col-sm-3 col-form-label">Контрагент@if(Request::has('contractor_id'))<star class="star">*</star>@endif</label>
                                                   <div class="col-sm-9">
                                                       <div class="form-group">
                                                           @if(!$contractor)
                                                               <select name="contractor_id" id="js-select-contractors" style="width:100%;">
                                                                   <option value="">Не выбран</option>
                                                                   @if($contractor)
                                                                       <option value="{{ $contractor->id }}" selected>{{ $contractor->short_name }}. ИНН: {{ $contractor->inn }}</option>
                                                                   @endif
                                                               </select>
                                                           @else
                                                           <select class="form-control" name="contractor_id" id="js-select-contractors" disabled style="width:100%;">
                                                               <option value="{{ $contractor->id }}" selected>{{ $contractor->short_name }}. ИНН: {{ $contractor->inn }}</option>
                                                           </select>
                                                           <input type="hidden" name="contractor_id" value="{{ $contractor->id }}">
                                                           @endif
                                                       </div>
                                                   </div>
                                               </div>

                                               <div id="contact_choose" class="d-none">
                                                   <hr>
                                                   <div class="row">
                                                      <div class="col-sm-12">
                                                           <div class="pull-right">
                                                               <button type="button" class="btn btn-round btn-outline btn-sm btn-success" id="create_new_contact" data-toggle="modal" data-target="#add-contact">
                                                                   <i class="glyphicon fa fa-plus"></i>
                                                                   Новый контакт
                                                               </button>
                                                           </div>
                                                       </div>
                                                   </div>
                                                   <div class="row">
                                                       <label class="col-sm-3 col-form-label">Контакт<star class="star">*</star></label>
                                                       <div class="col-sm-9">
                                                           <div class="form-group">
                                                               <select name="contact_id" id="js-select-contact" style="width:100%;">
                                                                   @if($contact)
                                                                   <option value="{{ $contractor->id }}" selected>{{ $contact->last_name }} {{ $contact->first_name }} {{ $contact->patronymic }}, Должность: {{ $contact->position }}</option>
                                                                   @endif
                                                               </select>
                                                           </div>
                                                       </div>
                                                   </div>
                                               </div>

                                                <div id="project_choose" class="d-none">
                                                    <hr>
                                                    @if(Auth::user()->department_id == 14/*6*/)
                                                        <div class="row">
                                                           <div class="col-sm-12">
                                                                <div class="pull-right">
                                                                    <button type="button" href-url="{{ route('projects::create', ['task_id' => $call->id, 'contractor_id' => isset($contractor->id) ? $contractor->id : 'js_id', 'contact_id' => 'js_contact_id']) }}" id="add_project_link" class="btn btn-round btn-outline btn-sm btn-success">
                                                                        <i class="glyphicon fa fa-plus"></i>
                                                                        Новый проект
                                                                    </button>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    @endif
                                                    <div class="row">
                                                        <label class="col-sm-3 col-form-label">Проект</label>
                                                        <div class="col-sm-9">
                                                            <div class="form-group">
                                                                <select name="project_id" id="js-select-project" style="width:100%;">
                                                                    @if(Request::has('project_id') or $project)
                                                                        <option value="{{ $project->id }}" selected>Название: {{ $project->name }}</option>
                                                                    @endif
                                                                        <option value="">Не выбрано</option>
                                                                </select>
                                                            </div>
                                                        </div>
                                                    </div>
                                                 </div>
                                            </div>
                                        </div>
                                    </div>


                               <!--Подробная информация о контрагенте-->
                               <div id="contractor_edit_card" class="card d-none">
                                    <div class="card-header">
                                        <h4 class="card-title">
                                            <a data-target="#collapseAllInfo" href="#" data-toggle="collapse">
                                                 Информация о контрагенте
                                                <b class="caret"></b>
                                            </a>
                                        </h4>
                                    </div>
                                    <div id="collapseAllInfo" class="card-collapse collapse ">
                                    <div class="card-body">
                                        <div class="row">
                                            <label class="col-sm-3 col-form-label">Полное наименование<star class="star">*</star></label>
                                            <div class="col-sm-8">
                                                <div class="form-group">
                                                    <input class="form-control" id="full_name" type="text" @if(isset($result['data']['name']['full_with_opf'])) value="{{ $result['data']['name']['full_with_opf'] }}" @endif name="contractor_full_name" maxlength="200">
                                                </div>
                                            </div>
                                        </div>
                                        <div class="row">
                                            <label class="col-sm-3 col-form-label">Краткое наименование<star class="star">*</star></label>
                                            <div class="col-sm-6">
                                                <div class="form-group">
                                                    <input class="form-control" id="short_name" type="text" @if(isset($result['data']['name']['short_with_opf'])) value="{{ $result['data']['name']['short_with_opf'] }}" @endif  name="contractor_short_name" maxlength="100">
                                                </div>
                                            </div>
                                        </div>
                                        <div class="row">
                                            <label class="col-sm-3 col-form-label">ИНН</label>
                                            <div class="col-sm-4">
                                                <div class="form-group">
                                                    <input class="form-control raz" id="inn" @if(isset($result['data']['inn'])) value="{{ $result['data']['inn'] }}" @endif type="text" name="contractor_inn" minlength="10" maxlength="14">
                                                </div>
                                            </div>
                                        </div>
                                        <div class="row">
                                            <label class="col-sm-3 col-form-label">КПП</label>
                                            <div class="col-sm-4">
                                                <div class="form-group">
                                                    <input class="form-control raz" id="kpp" type="text" @if(isset($result['data']['kpp'])) value="{{ $result['data']['kpp'] }}" @endif name="contractor_kpp" minlength="9" maxlength="9">
                                                </div>
                                            </div>
                                        </div>
                                        <div class="row">
                                            <label class="col-sm-3 col-form-label">ОГРН</label>
                                            <div class="col-sm-4">
                                                <div class="form-group">
                                                    <input class="form-control raz" id="ogrn" type="text" @if(isset($result['data']['ogrn'])) value="{{ $result['data']['ogrn'] }}" @endif name="contractor_ogrn" minlength="12" maxlength="15">
                                                </div>
                                            </div>
                                        </div>
                                        <div class="row">
                                            <label class="col-sm-3 col-form-label">Юридический адрес</label>
                                            <div class="col-sm-7">
                                                <div class="form-group">
                                                    <input class="form-control" id="legal_address" type="text" @if(isset($result['data']['address']['value'])) value="{{ $result['data']['address']['value'] }}" @endif name="contractor_legal_address" maxlength="200">
                                                </div>
                                            </div>
                                        </div>
                                        <div class="row">
                                            <label class="col-sm-3 col-form-label">Физический адрес</label>
                                            <div class="col-sm-7">
                                                <div class="form-group">
                                                    <input class="form-control" type="text" name="contractor_physical_adress" id="physical_adress" maxlength="200">
                                                </div>
                                            </div>
                                        </div>
                                        <div class="row">
                                            <label class="col-sm-3 col-form-label">Генеральный директор</label>
                                            <div class="col-sm-6">
                                                <div class="form-group">
                                                    <input class="form-control" id="general_manager" type="text" @if(isset($result['data']['management']['name'])) value="{{ $result['data']['management']['name'] }}" @elseif(isset($result['data']['name']['full'])) value="{{ $result['data']['name']['full'] }}" @endif name="contractor_general_manager" maxlength="100">
                                                </div>
                                            </div>
                                        </div>
                                        <div class="row">
                                            <label class="col-sm-3 col-form-label">Телефон</label>
                                            <div class="col-sm-4">
                                                <div class="form-group">
                                                    <input class="form-control" type="tel" id="phone_number" name="contractor_phone_number" minlength="17" maxlength="17">

                                                </div>
                                            </div>
                                        </div>
                                        <div class="row">
                                            <label class="col-sm-3 col-form-label">Email</label>
                                            <div class="col-sm-4">
                                                <div class="form-group">
                                                    <input class="form-control" type="email" id="email" name="contractor_email">
                                                </div>
                                            </div>
                                        </div>
                                        <h5 style="margin-top:30px; margin-bottom:20px">Реквизиты</h5>
                                        <div class="row">
                                            <label class="col-sm-3 col-form-label">Банк</label>
                                            <div class="col-sm-7">
                                                <div class="form-group">
                                                    <input class="form-control" type="text" name="contractor_bank_name" id="bank_name" maxlength="100">
                                                </div>
                                            </div>
                                        </div>
                                        <div class="row">
                                            <label class="col-sm-3 col-form-label">Расчетный счет</label>
                                            <div class="col-sm-4">
                                                <div class="form-group">
                                                    <input class="form-control raz" type="text" id="check_account" name="contractor_check_account" maxlength="20">
                                                </div>
                                            </div>
                                        </div>
                                        <div class="row">
                                            <label class="col-sm-3 col-form-label">Корреспондентский счет</label>
                                            <div class="col-sm-4">
                                                <div class="form-group">
                                                    <input class="form-control raz" type="text" id="cor_account" name="contractor_cor_account"  maxlength="20">
                                                </div>
                                            </div>
                                        </div>
                                        <div class="row">
                                            <label class="col-sm-3 col-form-label">БИК</label>
                                            <div class="col-sm-4">
                                                <div class="form-group">
                                                    <input class="form-control raz" id="bik" type="text" name="contractor_bik" maxlength="9">
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    </div>
                               </div>

                               <!--Результат-->
                               <div class="card">
                                   <div class="card-header">
                                       <h4 class="card-title">
                                           <a data-target="#collapseССResult" href="#" data-toggle="collapse" class="collapsed" aria-expanded="false">
                                               Результат выполнения задачи
                                               <b class="caret"></b>
                                           </a>
                                       </h4>
                                   </div>
                                   <div id="collapseССResult" class="card-collapse collapse show">
                                       <div class="card-body">
                                           <div class="form-group" >
                                               <div class="row">
                                                   <label class="col-sm-3 col-form-label">Комментарий<star class="star">*</star></label>
                                                   <div class="col-sm-9">
                                                       <div class="form-group">
                                                           <textarea class="form-control textarea-rows" name="final_note" maxlength="300" required placeholder="Опишите результат выполненения задачи"></textarea>
                                                       </div>
                                                   </div>
                                               </div>
                                           </div>
                                       </div>
                                   </div>
                               </div>
                               <div class="card-footer">
                                   <div class="row" style="margin-top:25px">
                                       <div class="col-md-12 btn-center">
                                           <a href="{{ route('tasks::index') }}" class="btn btn-wd">Назад</a>
                                           <div class="pull-right">
                                               <button type="submit" id="close_call_button" class="btn btn-wd btn-info pull-right">Выполнить</button>
                                           </div>
                                       </div>
                                   </div>
                               </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
         </div>
     </div>
 </div>

 <div class="modal fade bd-example-modal-lg" id="add-contact" tabindex="-1" role="dialog" aria-labelledby="modal-search" aria-hidden="true">
   <div class="modal-dialog modal-lg" role="document">
     <div class="modal-content">
       <div class="modal-header">
         <h5 class="modal-title" id="modal-add">Добавить контактное лицо</h5>
         <button type="button" class="close" data-dismiss="modal" aria-label="Close">
           <span aria-hidden="true">&times;</span>
         </button>
       </div>
       <div class="modal-body">
           <div class="card border-0">
               <div class="card-body ">
                   <!--
                   Контактное лицо, добавленное в контрагента, сохраняется только в карточке контрагенте и становится доступным для выбора при добавлении контакта в карточке проекта.
               -->
                   <form id="form_add_contact" class="form-horizontal" action="{{ route('contractors::add_contact', ['js_con_id', 'project_id' => 'js_proj_id', 'task_id' => $call->id]) }}" method="post">
                       @csrf
                       <div class="row">
                           <label class="col-sm-3 col-form-label">Фамилия<star class="star">*</star></label>
                           <div class="col-sm-9">
                               <div class="form-group">
                                   <input class="form-control" type="text" name="last_name" required maxlength="50">
                               </div>
                           </div>
                       </div>
                       <div class="row">
                           <label class="col-sm-3 col-form-label">Имя<star class="star">*</star></label>
                           <div class="col-sm-9">
                               <div class="form-group">
                                   <input class="form-control" type="text" name="first_name" required maxlength="50">
                               </div>
                           </div>
                       </div>
                       <div class="row">
                           <label class="col-sm-3 col-form-label">Отчество</label>
                           <div class="col-sm-9">
                               <div class="form-group">
                                   <input class="form-control" type="text" name="patronymic" maxlength="50">
                               </div>
                           </div>
                       </div>
                       <div class="row">
                           <label class="col-sm-3 col-form-label">Должность<star class="star">*</star></label>
                           <div class="col-sm-9">
                               <div class="form-group">
                                   <input class="form-control" type="text" name="position" maxlength="50" required>
                               </div>
                           </div>
                       </div>
                       <div class="row">
                           <label class="col-sm-3 col-form-label">Телефон<star class="star">*</star></label>
                           <div class="col-sm-6">
                               <div class="form-group">
                                   <input class="form-control phone_number" id="contact_phone" type="text" value="{{ old('contact_phone_number') ? old('contact_phone_number') : (isset($contact->phone_number) ? $contact->phone_number : $call->incoming_phone) }}" name="phone_number" required maxlength="17">
                               </div>
                           </div>
                       </div>
                       <div class="row">
                           <label class="col-sm-3 col-form-label">Email</label>
                           <div class="col-sm-9">
                               <div class="form-group">
                                   <input class="form-control" type="text" name="email" maxlength="50">
                               </div>
                           </div>
                       </div>
                       <div class="row">
                           <label class="col-sm-3 col-form-label">Примечание</label>
                           <div class="col-sm-9">
                               <div class="form-group">
                                   <textarea class="form-control textarea-rows" name="note" maxlength="150"></textarea>
                               </div>
                           </div>
                       </div>
                   </form>
               </div>
           </div>
       </div>
       <div class="modal-footer">
         <button type="button" class="btn btn-secondary" data-dismiss="modal">Закрыть</button>
         <button type="submit" form="form_add_contact" class="btn btn-info">Добавить</button>
       </div>
     </div>
   </div>
 </div>

 <meta name="csrf-token" content="{{ csrf_token() }}" />

@endsection

@section('js_footer')
<script>
    var CSRF_TOKEN = $('meta[name="csrf-token"]').attr('content');

    $('#js-select-contractors').select2({
        language: "ru",
        ajax: {
            url: '/projects/ajax/get-contractors',
            dataType: 'json',
            delay: 250,
        }
    });

    $('#js-select-contractors').on('change', function() {
        $('#contractor_edit_card').removeClass('d-none');
        $('#project_choose').removeClass('d-none');
        $('#js-select-project').select2({
            language: "ru",
            ajax: {
                url: '/tasks/get-projects?contractor_id=' + $('#js-select-contractors').select2('val'),
                dataType: 'json',
                delay: 250
            }
        });
    });

    $('#js-select-project-contractors').select2({
        language: "ru",
        ajax: {
            url: '/projects/ajax/get-contractors?contractor_id=' + $('#js-select-contractors').select2('val'),
            dataType: 'json',
            delay: 250,
        }
    });

    $('#js-select-contractors').on('change', function() {
        $('#js-select-project-contractors').select2({
            language: "ru",
            ajax: {
                url: '/projects/ajax/get-contractors?contractor_id=' + $('#js-select-contractors').select2('val'),
                dataType: 'json',
                delay: 250,
            }
        });
    });
</script>

@if(!$contact)
    <script>
        $('#js-select-contractors').on('change', function() {
            $('#contact_choose').removeClass('d-none');
            $('#js-select-contact').select2({
                language: "ru",
                ajax: {
                    url: '/tasks/ajax/get_contacts/' + $('#js-select-contractors').select2('val'),
                    dataType: 'json',
                    delay: 250
                }
            });
        });
    </script>
@else
    <script>
        $('#contact_choose').removeClass('d-none');
    </script>
@endif


<script>

$( "#js-select-contractors" ).change(function() {
    $.ajax({
        url:'{{ route("tasks::choose_contractor") }}',
        type: 'POST',
        data: {
            _token: CSRF_TOKEN,
            contractor_id: $( "#js-select-contractors" ).val(),
        },
        dataType: 'JSON',
        success: function (data) {
            $('#full_name').val(data.full_name);
            $('#short_name').val(data.short_name);
            $('#inn').val(data.inn);
            $('#kpp').val(data.kpp);
            $('#ogrn').val(data.ogrn);
            $('#legal_address').val(data.legal_address);
            $('#physical_adress').val(data.physical_adress);
            $('#general_manager').val(data.general_manager);
            $('#phone_number').val(data.phone_number);
            $('#email').val(data.email);
            $('#check_account').val(data.check_account);
            $('#bik').val(data.bik);
            $('#cor_account').val(data.cor_account);
            $('#bank_name').val(data.bank_name);
        }
    });
});
</script>

@if($contractor)
    <script>
        $('#contractor_edit_card').removeClass('d-none');
        $('#project_choose').removeClass('d-none');
        $('#js-select-project').select2({
            language: "ru",
            ajax: {
                url: '/tasks/get-projects?contractor_id=' + $('#js-select-contractors').select2('val'),
                dataType: 'json',
                delay: 250
            }
        });

        $('#contact_choose').removeClass('d-none');
        $('#js-select-contact').select2({
            language: "ru",
            ajax: {
                url: '/tasks/ajax/get_contacts/' + $('#js-select-contractors').select2('val'),
                dataType: 'json',
                delay: 250
            }
        });

        $.ajax({
            url:'{{ route("tasks::choose_contractor") }}',
            type: 'POST',
            data: {
                _token: CSRF_TOKEN,
                contractor_id: $( "#js-select-contractors" ).val(),
            },
            dataType: 'JSON',
            success: function (data) {
                $('#full_name').val(data.full_name);
                $('#short_name').val(data.short_name);
                $('#inn').val(data.inn);
                $('#kpp').val(data.kpp);
                $('#ogrn').val(data.ogrn);
                $('#legal_address').val(data.legal_address);
                $('#physical_adress').val(data.physical_adress);
                $('#general_manager').val(data.general_manager);
                $('#phone_number').val(data.phone_number);
                $('#email').val(data.email);
                $('#check_account').val(data.check_account);
                $('#bik').val(data.bik);
                $('#cor_account').val(data.cor_account);
                $('#bank_name').val(data.bank_name);
            }
        });
    </script>
@endif

<script>
$('#add_project_link').on('click', function() {
    var url_project = $('#add_project_link').attr('href-url');
    url_project = url_project.replace('js_id', $( "#js-select-contractors" ).val());
    url_project = url_project.replace('js_contact_id', $( "#js-select-contact" ).val());
    window.location.replace(url_project);
});

$('#contact_phone').mask('7 (000) 000-00-00');


$('#create_new_contact').on('click', function() {
    var url_from = $('#form_add_contact').attr('action');
    url_from = url_from.replace('js_proj_id', $("#js-select-project").val());
    url_from = url_from.replace('js_con_id', $('#js-select-contractors').val());
    $('#form_add_contact').attr('action', url_from);
});
</script>

@endsection
