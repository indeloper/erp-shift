@extends('layouts.app')

@section('title', 'Подрядчики')

@section('url', route('subcontractors::index'))

@section('content')
<div class="row">
    <div class="col-sm-12 col-xl-10 mr-auto ml-auto">
        <div class="card">
            <div class="card-header">
                <div class="row">
                    <div class="col-md-9">
                        <h4 class="card-title" style="margin-top: 5px">{{ $contractor->short_name }}</h4>
                    </div>
                    @if(Auth::user()->department_id == 14/*6*/)
                        <div class="col-md-3">
                            <div class="pull-right " style="margin-top: 5px">
                                <a class="btn btn-outline btn-sm" href="{{ route('subcontractors::edit', $contractor->id) }}">
                                    <i class="glyphicon fa fa-pencil-square-o"></i>
                                    Редактировать
                                </a>
                            </div>
                        </div>
                    @endif
                </div>
                <hr style="margin-top:10px">
                <div class="relative" style="width:auto; height:auto;">
                    <div class="story">
                        <a href="#" class="story-link"> История взаимодействий</a>
                    </div>
                </div>
            </div>
            <div class="card-body">
                <div class="accordions" id="accordion">
                    <div class="card">
                        <div class="card-header">
                            <h4 class="card-title">
                                <a data-target="#collapseOne" href="#" data-toggle="collapse">
                                    Основные данные
                                    <b class="caret"></b>
                                </a>
                            </h4>
                        </div>
                        <div id="collapseOne" class="card-collapse collapse show">
                            <div class="card-body">
                                <div class="row info-string">
                                    <label class="col-sm-3 col-form-label label-info-card">Полное наименование</label>
                                    <div class="col-sm-9">
                                        <p class="p-info-card">{{ $contractor->full_name }}</p>
                                    </div>
                                </div>
                                <div class="row info-string">
                                    <label class="col-sm-3 col-form-label label-info-card">Краткое наименование</label>
                                    <div class="col-sm-9">
                                        <p class="p-info-card">{{ $contractor->short_name }}</p>
                                    </div>
                                </div>
                                <div class="row info-string">
                                    <label class="col-sm-3 col-form-label label-info-card">ИНН</label>
                                    <div class="col-sm-9">
                                        <p class="p-info-card">{{ $contractor->inn }}</p>
                                    </div>
                                </div>
                                <div class="row info-string">
                                    <label class="col-sm-3 col-form-label label-info-card">КПП</label>
                                    <div class="col-sm-9">
                                        <p class="p-info-card">{{ $contractor->kpp }}</p>
                                    </div>
                                </div>
                                <div class="row info-string">
                                    <label class="col-sm-3 col-form-label label-info-card">ОГРН</label>
                                    <div class="col-sm-9">
                                        <p class="p-info-card">{{ $contractor->ogrn }}</p>
                                    </div>
                                </div>
                                <div class="row info-string">
                                    <label class="col-sm-3 col-form-label label-info-card">Юридический адрес</label>
                                    <div class="col-sm-9">
                                        <p class="p-info-card">{{ $contractor->legal_address }}</p>
                                    </div>
                                </div>
                                <div class="row info-string">
                                    <label class="col-sm-3 col-form-label label-info-card">Физический адрес</label>
                                    <div class="col-sm-9">
                                        <p class="p-info-card">{{ $contractor->physical_adress }}</p>
                                    </div>
                                </div>
                                <div class="row info-string">
                                    <label class="col-sm-3 col-form-label label-info-card">Генеральный директор</label>
                                    <div class="col-sm-9">
                                        <p class="p-info-card">{{ $contractor->general_manager }}</p>
                                    </div>
                                </div>
                                <div class="row info-string">
                                    <label class="col-sm-3 col-form-label label-info-card">Телефон</label>
                                    <div class="col-sm-9">
                                        <p class="p-info-card">{{ $contractor->phone_number }}</p>
                                    </div>
                                </div>
                                <div class="row info-string">
                                    <label class="col-sm-3 col-form-label label-info-card">email</label>
                                    <div class="col-sm-9">
                                        <p class="p-info-card">{{ $contractor->email }}</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="card">
                        <div class="card-header">
                            <h4 class="card-title">
                                <a data-target="#collapseTwo" href="#" data-toggle="collapse">
                                    Реквизиты
                                    <b class="caret"></b>
                                </a>
                            </h4>
                        </div>
                        <div id="collapseTwo" class="card-collapse collapse">
                            <div class="card-body">
                                <div class="row info-string">
                                    <label class="col-sm-3 col-form-label label-info-card">Банк</label>
                                    <div class="col-sm-9">
                                        <p class="form-control-static p-info-card">@if(isset($bank->bank_name)) {{ $bank->bank_name }} @else Информация отсутствует @endif</p>
                                    </div>
                                </div>
                                <div class="row info-string">
                                    <label class="col-sm-3 col-form-label label-info-card">Расчетный счет</label>
                                    <div class="col-sm-9">
                                        <p class="form-control-static p-info-card">@if(isset($bank->check_account)) {{ $bank->check_account }} @else Информация отсутствует @endif</p>
                                    </div>
                                </div>
                                <div class="row info-string">
                                    <label class="col-sm-3 col-form-label label-info-card">Корреспондентский счет</label>
                                    <div class="col-sm-9">
                                        <p class="form-control-static p-info-card">@if(isset($bank->cor_account)) {{ $bank->cor_account }} @else Информация отсутствует @endif</p>
                                    </div>
                                </div>
                                <div class="row info-string">
                                    <label class="col-sm-3 col-form-label label-info-card">Бик</label>
                                    <div class="col-sm-9">
                                        <p class="form-control-static p-info-card">@if(isset($bank->bik)) {{ $bank->bik }} @else Информация отсутствует @endif</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="card">
                        <div class="card-header">
                            <h4 class="card-title">
                                <a data-target="#collapseFour" href="#" data-toggle="collapse">
                                    Контакты
                                    <b class="caret"></b>
                                </a>
                            </h4>
                        </div>
                        <div id="collapseFour" class="card-collapse collapse @if(session('contacts')) show @endif">
                            <div class="card-body card-body-table">
                                <div class="card strpied-tabled-with-hover">
                                    <div class="fixed-table-toolbar toolbar-for-btn">
                                        <div class="pull-right">
                                            <button class="btn btn-round btn-outline btn-sm add-btn" data-toggle="modal" data-target="#add-contact">
                                                <i class="glyphicon fa fa-plus"></i>
                                                Добавить
                                            </button>
                                        </div>
                                    </div>
                                    @if(!$contacts->isEmpty())
                                        <div class="table-responsive">
                                            <table class="table table-hover mobile-table">
                                                <thead>
                                                    <tr>
                                                        <th>ФИО</th>
                                                        <th>Должность</th>
                                                        <th>Контактный номер</th>
                                                        <th>email</th>
                                                        <th class="text-right">Действия</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    @foreach($contacts as $contact)
                                                        <tr>
                                                            <td data-label="ФИО" data-target="#collapse{{$contact->id}}" data-toggle="collapse" class="collapsed tr-pointer" aria-expanded="false">
                                                                {{ $contact->last_name }} {{ $contact->first_name }} {{ $contact->patronymic }}
                                                            </td>
                                                            <td data-label="Должность" data-target="#collapse{{$contact->id}}" data-toggle="collapse" class="collapsed tr-pointer" aria-expanded="false">
                                                                {{ $contact->position }}
                                                            </td>
                                                            <td data-label="Контактный номер">{{ $contact->phone_number }}</td>
                                                            <td data-label="email">{{ $contact->email }}</td>
                                                            <td data-label="" class="td-actions text-right actions">
                                                                <button href="#" rel="tooltip" onClick="edit_contact({{ $contact }})"  data-toggle="modal" data-target="#edit-contact" class="btn-edit btn-link btn-xs btn mn-0 padding-actions" data-original-title="Редактировать">
                                                                    <i class="fa fa-edit"></i>
                                                                </button>
                                                                <a href="#" class="btn btn-link btn-danger remove mn-0 padding-actions" contact_id="{{ $contact->id }}"><i class="fa fa-times"></i></a>
                                                            </td>
                                                        </tr>
                                                        <tr id="collapse{{$contact->id}}" class="contact-note card-collapse collapse" style>
                                                            <td colspan="2" style="vertical-align: top;">
                                                                <div class="comment-container">
                                                                   Общее примечание: {{ $contact->note }}
                                                                </div>
                                                            </td>
                                                            <td colspan="2">
                                                                @foreach($contact->projects as $project)
                                                                <a data-toggle="collapse" href="#comment{{ $project->id }}" class="table-link">
                                                                    {{ $project->name }} </a>
                                                                <div class="comment-container collapse"  id="comment{{ $project->id }}">
                                                                    <p class="comment">
                                                                        {{ $project->note }}
                                                                    </p>
                                                                </div><br>
                                                                @endforeach

                                                            </td>
                                                            <td></td>
                                                        </tr>
                                                    @endforeach
                                                </tbody>
                                            </table>
                                        </div>
                                        @else
                                        <p class="text-center">В этом разделе пока нет ни одного контакта</p>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                    @foreach($projects as $project)
                    <div class="card">
                        <div class="card-header">
                          <h4 class="card-title">
                              <a data-target="#collapse_project_{{ $project->id }}" href="#" data-toggle="collapse">
                                  Проект {{ $project->name }}
                                  <b class="caret"></b>
                              </a>
                          </h4>
                        </div>
                        <div id="collapse_project_{{ $project->id }}" class="card-collapse collapse">
                            <div class="card-body card-body-table">
                                <div class="card strpied-tabled-with-hover">
                                    <div class="fixed-table-toolbar toolbar-for-btn">
                                        <h6 class="mb-10">Работы</h6>
                                    </div>
                                    <div class="table-responsive">
                                        <table class="table table-hover mobile-table">
                                            <thead>
                                                <tr>
                                                    <th>Наименование работы</th>
                                                    <th class="text-center">Ед. измерения</th>
                                                    <th class="text-center">Количество</th>
                                                    <th class="text-center">Стоимость за ед., руб</th>
                                                    <th class="text-center">Общая стоимость, руб</th>
                                                    <th class="text-right">Срок производства, дней</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @foreach($work_volume_works as $work)
                                                <tr>
                                                    <td data-label="Наименование работы">
                                                        {{ $work->manual->name }}
                                                        @if($work->materials->count() and $work->manual->show_materials)
                                                         (@foreach($work->materials as $material) {{ $material->name }} @endforeach)
                                                        @endif
                                                    </td>
                                                    <td data-label="Ед. измерения" class="text-center">{{ $work->manual->unit }}</td>
                                                    <td data-label="Наименование работы" class="text-center">{{ $work->count }}</td>
                                                    <td data-label="Общая стоимость, руб" class="text-center">{{ $work->price_per_one }}</td>
                                                    <td data-label="Стоимость за ед., руб" class="text-center">{{ $work->result_price }}</td>
                                                    <td data-label="Срок производства, дней" class="text-center">{{ $work->term }}</td>
                                                </tr>
                                                @endforeach
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                            <div class="card strpied-tabled-with-hover">
                                <div class="fixed-table-toolbar toolbar-for-btn">
                                    <h6 class="mb-10">Документация</h6>
                                </div>
                                <div class="table-responsive">
                                    <table class="table table-hover mobile-table">
                                        <thead>
                                            <tr>
                                                <th>Название документа</th>
                                                <th class="text-center">Дата загрузки</th>
                                                <th class="text-right">Действия</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <tr>
                                                <td data-label="Название документа"><a href="">Договор на оказание услуг №12</a></td>
                                                <td data-label="Дата загрузки" class="text-center">12.04.2019 12:00</td>
                                                <td data-label="" class="text-right actions">
                                                    <button rel="tooltip" title="" class="btn btn-danger btn-link btn-xs padding-actions mn-0 " data-original-title="Удалить">
                                                        <i class="fa fa-times"></i>
                                                    </button>
                                                </td>
                                            </tr>
                                            <tr>
                                                <td data-label="Название документа"><a href="">Договор на оказание услуг №10</a></td>
                                                <td data-label="Дата загрузки" class="text-center">10.04.2019 10:18</td>
                                                <td class="text-right actions">
                                                    <button rel="tooltip" title="" class="btn btn-danger btn-link btn-xs padding-actions mn-0" data-original-title="Удалить">
                                                        <i class="fa fa-times"></i>
                                                    </button>
                                                </td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                                <div class="col-md-12">
                                    <div class="right-edge">
                                        <div class="page-container">
                                            <a class="btn btn-sm show-all" href="{{ route('projects::card', $work->project_id) }}">
                                                Перейти на страницу проекта
                                            </a>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    @endforeach

                </div>
            </div>
        </div>
    </div>
</div>

<!-- Mini Modal -->
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
                  <form id="form_add_contact" class="form-horizontal" action="{{ route('subcontractors::add_contact', $contractor->id) }}" method="post" novalidate="novalidate">
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
                                  <input class="form-control" type="text" name="position" maxlength="50">
                              </div>
                          </div>
                      </div>
                      <div class="row">
                          <label class="col-sm-3 col-form-label">Телефон<star class="star">*</star></label>
                          <div class="col-sm-6">
                              <div class="form-group">
                                  <input class="form-control phone_number" type="text" name="phone_number" required maxlength="17">
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


<div class="modal fade bd-example-modal-lg" id="edit-contact" tabindex="-1" role="dialog" aria-labelledby="modal-search" aria-hidden="true">
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
                  <form id="form_edit_contact" class="form-horizontal" action="{{ route('subcontractors::edit_contact') }}" method="post" novalidate="novalidate">
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
                                  <input class="form-control" type="text" name="position" id="edit_position" maxlength="50">
                              </div>
                          </div>
                      </div>
                      <div class="row">
                          <label class="col-sm-3 col-form-label">Телефон<star class="star">*</star></label>
                          <div class="col-sm-6">
                              <div class="form-group">
                                  <input class="form-control phone_number" type="text" name="phone_number" id="edit_phone_number" required maxlength="17">
                              </div>
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
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Закрыть</button>
                <button type="submit" form="form_edit_contact" class="btn btn-info">Сохранить</button>
              </div>
          </div>
      </div>
    </div>
  </div>
</div>
@endsection

@section('js_footer')
<script src="{{ mix('js/form-validation.js') }}" type="text/javascript"></script>
<script src="{{ asset('js/plugins/jquery.mask.min.js') }}"></script>

<meta name="csrf-token" content="{{ csrf_token() }}" />

<script>

var CSRF_TOKEN = $('meta[name="csrf-token"]').attr('content');

$('.phone_number').mask('7 (000) 000-00-00');


function edit_contact(data) {
    $('#edit_contact_id').val(data.id);
    $('#edit_first_name').val(data.first_name);
    $('#edit_last_name').val(data.last_name);
    $('#edit_patronymic').val(data.patronymic);
    $('#edit_phone_number').val(data.phone_number);
    $('#edit_position').val(data.position);
    $('#edit_email').val(data.email);
    $('#edit_note').val(data.note);
}

$('#form_add_contact').validate();
$('#form_edit_contact').validate();

$('#js-select-user').select2({
    language: "ru",
    ajax: {
        url: '/tasks/get-users',
        dataType: 'json',
        delay: 250
    }
});

</script>

<script type="text/javascript">

    var CSRF_TOKEN = $('meta[name="csrf-token"]').attr('content');

    $('.remove').on('click', function() {
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
                    url:"{{ route('subcontractors::contact_delete') }}",
                    type: 'POST',
                    data: {
                        _token: CSRF_TOKEN,
                        contact_id: $(this).attr("contact_id")
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

@endsection
