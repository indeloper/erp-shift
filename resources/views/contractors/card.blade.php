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
    .bootstrap-select>.dropdown-toggle:after {
        margin-left: -5px;
    }
    .bootstrap-select>.dropdown-toggle {
        padding-right: 15px;
        min-width: 175px;
    }
</style>

@endsection

@section('content')
<div class="row">
    <div class="col-sm-12 col-xl-10 mr-auto ml-auto">
        <div class="card">
            <div class="card-header">
                <div class="row">
                    <div class="col-md-9">
                        <h4 class="card-title" style="margin-top: 5px">{{ $contractor->short_name }}</h4>
                    </div>
{{--                    <div class="col-md-3">--}}
{{--                        @if(count($contracts->pluck('type')->toArray()))--}}
{{--                            <div class="pull-right">--}}
{{--                                <form action="{{ route('contractors::card', $contractor->id) }}">--}}
{{--                                    <div class="form-group">--}}
{{--                                        <select name="type" class="selectpicker" onchange="this.form.submit()" data-title="Тип отображения" data-style="btn-default btn-outline btn-sm" data-menu-style="dropdown-blue">--}}
{{--                                            @foreach($contractor->contractor_type as $key => $type)--}}
{{--                                                @if(in_array($key, $contracts->pluck('type')->toArray()))--}}
{{--                                                    <option value="{{ $key }}" {{ $key == Request::get('type') ? 'selected' : (!Request::has('type') ? ($key == 1 ? 'selected' : '') : '') }}>{{ $type }}</option>--}}
{{--                                                @endif--}}
{{--                                            @endforeach--}}
{{--                                        </select>--}}
{{--                                    </div>--}}
{{--                                </form>--}}
{{--                            </div>--}}
{{--                        @endif--}}
{{--                    </div>--}}
                    <div class="col-md-3">
                        <div class="pull-right text-right" style="margin-top: 5px">
                            @can('contractors_delete')
                            @if(!$contractor->hasRemoveRequest())
                                <button class="btn btn-danger btn-outline btn-sm" data-toggle="modal" data-target="#delete_request">
                                    Запросить удаление
                                </button>
                            @endif
                            @endcan


                            @can('contractors_edit')
                            <a class="btn btn-outline btn-sm" href="{{ route('contractors::edit', $contractor->id) }}">
                                <i class="glyphicon fa fa-pencil-square-o"></i>
                                Редактировать
                            </a>
                            @endcan
                        </div>
                    </div>
                </div>
                <hr style="margin-top:10px">
                <!-- <div class="relative" style="width:auto; height:auto;">
                    <div class="story">
                        <a href="#" class="story-link">История взаимодействий</a>
                    </div>
                </div> -->
            </div>
            <div class="card-body">
                <div class="accordions" id="accordion">
                    @include('contractors.modules.main_info')

                    @include('contractors.modules.bank_account')

                    @include('contractors.modules.contacts')

                    @include('contractors.modules.contracts')

                @if(!Request::has('type') or Request::get('type') == 1)
                        @include('contractors.modules.projects')

                        @include('contractors.modules.commercial_offers')

                        @include('contractors.modules.tasks')
                    @else
                        @include('contractors.modules.sub_contract')
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

@can('contractors_contacts')
<div class="modal fade bd-example-modal-lg" id="add-contact" role="dialog" aria-labelledby="modal-search" aria-hidden="true">
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
                  <form id="form_add_contact" class="form-horizontal" action="{{ route('contractors::add_contact', $contractor->id) }}" method="post">
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
                          <label class="col-sm-3 col-form-label">Телефон</label>
                          <div class="col-sm-9" id="telephones">
                              <div class="row new_phone" style="margin: -6px">
                                  <input id="phone_count" hidden name="phone_count[]" value="0">
                                  <div class="col-sm-12">
                                      <div class="row" style="margin-bottom: 10px">
                                          <div class="col-md-7 mb-10">
                                              <input class="form-control phone_number" type="text" name="phone_number[]" minlength="14" maxlength="17" placeholder="Введите номер">
                                          </div>
                                          <div class="col-md-4 prt-0">
                                              <input class="form-control phone_dop" type="text" name="phone_dop[]" maxlength="5" placeholder="Добавочный">
                                          </div>
                                      </div>
                                      <div class="row">
                                          <div class="col-md-7 col-6">
                                              <select id="phone_name" name="phone_name[]" style="width:100%;">
                                                  <option value="Моб.">Мобильный</option>
                                                  <option value="Рабочий">Рабочий</option>
                                                  <option value="Основной">Основной</option>
                                                  <option value="Раб. факс">Рабочий факс</option>
                                                  <option value="Дом. факс">Домаший факс</option>
                                                  <option value="Пейджер">Пейджер</option>
                                              </select>
                                          </div>
                                          <div class="col-md-4 col-6" style="padding-left:5px">
                                              <div class="form-check form-check-radio">
                                                  <label class="form-check-label" style="text-transform:none;font-size:13px">
                                                      <input class="form-check-input" type="radio" name="main" id="" value="0">
                                                      <span class="form-check-sign"></span>
                                                      Основной
                                                  </label>
                                              </div>
                                          </div>
                                          <div class="col-md-1 remove-contact">
                                              <button type="button" class="btn-remove-mobile-modal" onclick="del_phone(this)">
                                                  <i class="fa fa-times remove-stroke"></i>
                                              </button>
                                          </div>
                                      </div>
                                  </div>
                              </div>
                              <div class="row">
                                  <div class="col-md-12 text-center">
                                      <button type="button" class="btn-success btn" onclick="add_phone(this)" style="margin:20px 0">
                                          <i class="fa fa-plus"></i>
                                          Добавить
                                      </button>
                                  </div>
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
        <button type="button" onclick="contact_submit(this)" form="form_add_contact" class="btn btn-info">Добавить</button>
      </div>
    </div>
  </div>
</div>
@endcan

@can('contractors_contacts')
<div class="modal fade bd-example-modal-lg" id="edit-contact" role="dialog" aria-labelledby="modal-search" aria-hidden="true">
  <div class="modal-dialog modal-lg" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="modal-edit">Изменить контактное лицо</h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <div class="modal-body">
          <div class="card border-0">
              <div class="card-body">
                  <!--
                  Контактное лицо, добавленное в контрагента, сохраняется только в карточке контрагенте и становится доступным для выбора при добавлении контакта в карточке проекта.
              -->
                  <form id="form_edit_contact" class="form-horizontal" action="{{ route('contractors::edit_contact') }}" method="post">
                      @csrf
                      <input type="hidden" id="edit_contact_id" name="id">
                      <div class="row">
                          <label class="col-sm-3 col-form-label">Фамилия<star class="star">*</star></label>
                          <div class="col-sm-9">
                              <div class="form-group">
                                  <input class="form-control" type="text" name="last_name" id="edit_last_name" required maxlength="50">
                              </div>
                          </div>
                      </div>
                      <div class="row">
                          <label class="col-sm-3 col-form-label">Имя<star class="star">*</star></label>
                          <div class="col-sm-9">
                              <div class="form-group">
                                  <input class="form-control" type="text" name="first_name" id="edit_first_name" required maxlength="50">
                              </div>
                          </div>
                      </div>
                      <div class="row">
                          <label class="col-sm-3 col-form-label">Отчество</label>
                          <div class="col-sm-9">
                              <div class="form-group">
                                  <input class="form-control" type="text" name="patronymic" id="edit_patronymic" maxlength="50">
                              </div>
                          </div>
                      </div>
                      <div class="row">
                          <label class="col-sm-3 col-form-label">Должность<star class="star">*</star></label>
                          <div class="col-sm-9">
                              <div class="form-group">
                                  <input class="form-control" type="text" name="position" id="edit_position" maxlength="50" required>
                              </div>
                          </div>
                      </div>
                      <div class="row">
                          <label class="col-sm-3 col-form-label">Телефон</label>
                          <div class="col-sm-9" id="edit_phones">
                          </div>
                      </div>
                      <div class="row">
                          <label class="col-sm-3 col-form-label">Email</label>
                          <div class="col-sm-9">
                              <div class="form-group">
                                  <input class="form-control" type="text" name="email" id="edit_email" maxlength="50">
                              </div>
                          </div>
                      </div>
                      <div class="row">
                          <label class="col-sm-3 col-form-label">Примечание</label>
                          <div class="col-sm-9">
                              <div class="form-group">
                                  <textarea class="form-control textarea-rows" name="note" id="edit_note" maxlength="150"></textarea>
                              </div>
                          </div>
                      </div>
                  </form>
              </div>
              <div class="modal-footer">
                <button type="button" class="btn btn-secondary" onclick="" data-dismiss="modal">Закрыть</button>
                <button type="button" form="form_edit_contact" onclick="contact_submit(this)" class="btn btn-info">Сохранить</button>
              </div>
          </div>
      </div>
    </div>
  </div>
</div>
@endcan

@if(Gate::check('tasks_default_myself') || Gate::check('tasks_default_others'))
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
                                       <select id="js-select-contractor" name="contractor_id"  style="width:100%;" required>
                                           @if(Request::has('contractor_id'))
                                               <option value="{{ $contractor->id }}" selected>{{ $contractor->short_name }}. ИНН: {{ $contractor->inn }}</option>
                                           @endif
                                           <option value=""></option>
                                       </select>
                                   </div>
                               </div>
                           </div>
                           <div id="project_choose" class="row d-none">
                               <label class="col-sm-3 col-form-label">Проект</label>
                               <div class="col-sm-6">
                                   <div class="form-group">
                                       <select name="project_id" id="js-select-project" style="width:100%;">
                                           @if(Request::has('project_id'))
                                               <option value="{{ $project->id }}" selected>Название: {{ $project->name }}</option>
                                           @endif
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
                <button id="submit" type="submit" form="create_task_form" class="btn btn-info">Сохранить</button>
           </div>
        </div>
    </div>
</div>
@endif

<div class="row new_phone d-none" style="margin: -6px">
    <input id="phone_count" hidden name="phone_count[]" value="">
    <div class="col-sm-12">
        <hr>
        <div class="row" style="margin-bottom:10px">
            <div class="col-md-7 mb-10">
                <input class="form-control phone_number" type="text" name="phone_number[]" minlength="14" maxlength="17" placeholder="Введите номер">
            </div>
            <div class="col-md-4 prt-0">
                <input class="form-control phone_dop" type="text" name="phone_dop[]"  maxlength="4" placeholder="Добавочный">
            </div>
        </div>
        <div class="row">
            <div class="col-md-7 col-6">
                <select name="phone_name[]" style="width:100%;">
                    <option value="Моб.">Мобильный</option>
                    <option value="Рабочий">Рабочий</option>
                    <option value="Основной">Основной</option>
                    <option value="Раб. факс">Рабочий факс</option>
                    <option value="Дом. факс">Домашний факс</option>
                    <option value="Пейджер">Пейджер</option>
                </select>
            </div>
            <div class="col-md-4 col-6" style="padding-left:5px">
                <div class="form-check form-check-radio">
                    <label class="form-check-label" style="text-transform:none;font-size:13px">
                        <input class="form-check-input" type="radio" name="main" id="check" value="">
                        <span class="form-check-sign"></span>
                        Основной
                    </label>
                </div>
            </div>
            <div class="col-md-1 remove-contact">
                <button type="button" class="btn-remove-mobile-modal" onclick="del_phone(this)">
                    <i class="fa fa-times remove-stroke"></i>
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Modal for contractor delete -->
@can('contractors_edit')
<div class="modal fade bd-example-modal-lg show" id="delete_request" role="dialog" aria-labelledby="modal-search" style="display: none;">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Запрос на удаление контрагента</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">×</span>
                </button>
            </div>
            <div class="modal-body">
                <hr style="margin-top:0">
                <div class="card border-0" >
                    <div class="card-body">
                        <form id="contractor_delete_request" class="form-horizontal" action="{{ route('contractors::contractor_delete_request') }}" method="post">
                            @csrf
                            <input type="hidden" name="contractor_id" value="{{ $contractor->id }}">
                            <input type="hidden" name="contractor_name" value="{{ $contractor->short_name }}">
                            <div class="row">
                                <div class="col-md-12">
                                    <div class="form-group">
                                        <label>Основание<span class="star">*</span></label>
                                        <input type="text" name="reason" placeholder="Укажите основание" class="form-control" maxlength="250" required>
                                    </div>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Закрыть</button>
                <button type="submit" form="contractor_delete_request" class="btn btn-info">Подтвердить</button>
            </div>
        </div>
    </div>
</div>
@endcan

@endsection

@section('js_footer')
<script src="{{ mix('js/form-validation.js') }}" type="text/javascript"></script>
<script src="{{ asset('js/plugins/jquery.mask.min.js') }}"></script>

<script>
    var count_phone = 1;
    var count_edit_phone = 1;

    $('#phone_name').select2({
        tags: true,
    });

    $('.phone_dop').mask('00000');
    $('.phone_number').mask('7 (000) 000-00-00');


    function edit_contact(data) {
        $('#edit_contact_id').val(data.id);
        $('#edit_first_name').val(data.first_name);
        $('#edit_last_name').val(data.last_name);
        $('#edit_patronymic').val(data.patronymic);
        $('#edit_position').val(data.position);
        $('#edit_email').val(data.email);
        $('#edit_note').val(data.note);

        $('#edit_phones').children().remove();
        count_edit_phone = 1;
        if (data.phones.length == 0) {
            clone = $(".new_phone.d-none").clone().removeClass('d-none').appendTo("#edit_phones");
            clone.find('select').select2({
                tags: true,
            });
            count_edit_phone++;
        }
        data.phones.forEach(function (phone) {
            clone = $(".new_phone.d-none").clone().removeClass('d-none').appendTo("#edit_phones");
            clone.find('.form-check-input').val(count_edit_phone);
            clone.find(`[name='phone_count[]']`).val(count_edit_phone);
            clone.find(`[name='phone_number[]']`).val(phone.phone_number);
            clone.find(`[name='phone_dop[]']`).val(phone.dop_phone);
            clone.find(`[name='main']`).attr('checked', (phone.is_main == 1));
            clone.find('select').select2({
                tags: true,
            });
            if (clone.find('select').find("option[value='" + phone.name + "']").length) {
                clone.find('select').val(phone.name).trigger('change');
            } else {
                var newOption = new Option(phone.name, phone.name, true, true);
                clone.find('select').append(newOption).trigger('change');
            }
            count_edit_phone++;
        });
        clone.find('.phone_dop').mask('00000');
        clone.find('.phone_number').mask('7 (000) 000-00-00');
    }


    function add_phone(e) {
        var next_phone = count_phone;
        if ($(e).parents('.col-sm-9')[0].getAttribute('id') == 'edit_phones') {
            next_phone = count_edit_phone;
            count_edit_phone++;
        } else {
            count_phone++;
        }
        $(".new_phone.d-none").find('.form-check-input').val(next_phone);
        $(".new_phone.d-none").find('#phone_count').val(next_phone);

        clone = $(".new_phone.d-none").clone().removeClass('d-none').insertBefore($(e));
        clone.find('select').select2({
            tags: true,
        });

        clone.find('.phone_dop').mask('00000');
        clone.find('.phone_number').mask('7 (000) 000-00-00');
    }


    function del_phone(e) {
        if ($(e).parents('.col-sm-9').find('.new_phone').length > 1) {
            $(e).closest('.new_phone').remove();
        }
    }

    function contact_submit(e) {
        var form = $(e).attr('form');
        var phones = [];
        var names = [];
        $('#'+form).find(`[name='phone_number[]']`).map(function(key,el) {
            phones.push(el.value);
        });
        $('#'+form).find(`[name='phone_name[]']`).map(function(key,el) {
            names.push(el.value.trim());
        });
        var unique_phones = [...new Set(phones)];
        if ($('#'+form)[0].reportValidity()) {
            if (unique_phones.length == phones.length) { //unique
                if (phones.length == 1) {
                    if (names.indexOf('') != -1 && phones[0] != '') {
                        swal({
                            title: "Внимание",
                            text: "Заполните названия телефонов",
                            type: 'warning',
                            timer: 2500,
                        });
                    } else {
                        $('#'+form).submit();
                    }
                } else if (names.indexOf('') != -1) {
                    swal({
                        title: "Внимание",
                        text: "Заполните названия телефонов",
                        type: 'warning',
                        timer: 2500,
                    });
                } else {
                    $('#'+form).submit();
                }

            } else {
                swal({
                    title: "Внимание",
                    text: "Некоторые номера повторяются.",
                    type: 'warning',
                    timer: 2500,
                });
            }
        }
    }

    $('#js-select-contractor').select2({
        language: "ru",
        ajax: {
            url: '/tasks/get-contractors',
            dataType: 'json',
            delay: 250
        }
    });

    $('#js-select-contractor').on('change', function() {
        $('#project_choose').removeClass('d-none');
        $('#js-select-project').select2({
            language: "ru",
            ajax: {
                url: '/tasks/get-projects?contractor_id=' + $('#js-select-contractor').select2('val'),
                dataType: 'json',
                delay: 250
            }
        });
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
    };

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

<meta name="csrf-token" content="{{ csrf_token() }}" />

<script type="text/javascript">

    var CSRF_TOKEN = $('meta[name="csrf-token"]').attr('content');

    $('.remove').on('click', function() {
        var contact = this;
        swal({
            title: 'Вы уверены?',
            text: "Контакт будет удален!",
            type: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#3085d6',
            cancelButtonColor: '#d33',
            cancelButtonText: 'Назад',
            confirmButtonText: 'Удалить'
        }).then((result) => {
            if(result.value) {
                $.ajax({
                    url:"{{ route('contractors::contact_delete') }}",
                    type: 'POST',
                    data: {
                        _token: CSRF_TOKEN,
                        contact_id: $(contact).attr("contact_id")
                    },
                    dataType: 'JSON',
                    success: function (data) {
                        setTimeout(function() {
                            location.reload()
                        }, 1000);
                    }
                });
            }
        })
    });
</script>

@if(Request::has('project_id') or Request::has('contractor_id'))
    <script>
        $('#project_choose').removeClass('d-none');
        $('#js-select-project').select2({
            language: "ru",
            ajax: {
                url: '/tasks/get-projects?contractor_id=' + $('#js-select-contractor').select2('val'),
                dataType: 'json',
                delay: 250
            }
        });
    </script>
@endif
@endsection
