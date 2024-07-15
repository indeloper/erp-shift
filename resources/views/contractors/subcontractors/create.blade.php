@extends('layouts.app')

@section('title', 'Подрядчики')

@section('url', route('subcontractors::index'))

@section('content')
<div class="row">
    <div class="col-sm-12 col-xl-9 mr-auto ml-auto">
        <div class="card ">
            <div class="card-header">
                <div class="row">
                    <div class="col-md-5">
                        <div class="left-edge">
                            <div class="page-container">
                                <h4 class="card-title" style="margin-top: 5px">Новый подрядчик</h4>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-7">
                        <div class="right-edge">
                            <div class="page-container">
                                <button class="btn btn-outline btn-sm fill-ogrn" data-toggle="modal" data-target="#search-inn">
                                    <i class="glyphicon fa fa-file-text-o"></i>
                                    Заполнить автоматически
                                </button>
                            </div>
                       </div>
                   </div>
                </div>
                <hr>
            </div>
            <div class="card-body">
                <h5 style="margin-bottom:20px">Основные данные</h5>
                <form id="form_create_contractor" class="form-horizontal" action="{{ route('subcontractors::store') }}" method="post" novalidate="novalidate" enctype="multipart/form-data">
                    @csrf
                    <div class="row">
                        <label class="col-sm-3 col-form-label">Полное наименование<star class="star">*</star></label>
                        <div class="col-sm-8">
                            <div class="form-group">
                                <input class="form-control" type="text" @if(isset($result['data']['name']['full_with_opf'])) value="{{ $result['data']['name']['full_with_opf'] }}" @endif name="full_name" required maxlength="200" required>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <label class="col-sm-3 col-form-label">Краткое наименование<star class="star">*</star></label>
                        <div class="col-sm-6">
                            <div class="form-group">
                                <input class="form-control" type="text" @if(isset($result['data']['name']['short_with_opf'])) value="{{ $result['data']['name']['short_with_opf'] }}" @endif  name="short_name" required maxlength="100" required>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <label class="col-sm-3 col-form-label">ИНН</label>
                        <div class="col-sm-4">
                            <div class="form-group">
                                <input class="form-control raz" id="inn" @if(isset($result['data']['inn'])) value="{{ $result['data']['inn'] }}" @endif type="text" name="inn" minlength="10" maxlength="14">
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <label class="col-sm-3 col-form-label">КПП</label>
                        <div class="col-sm-4">
                            <div class="form-group">
                                <input class="form-control raz" id="kpp" type="text" @if(isset($result['data']['kpp'])) value="{{ $result['data']['kpp'] }}" @endif name="kpp" minlength="9" maxlength="9">
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <label class="col-sm-3 col-form-label">ОГРН</label>
                        <div class="col-sm-4">
                            <div class="form-group">
                                <input class="form-control raz" id="ogrn" type="text" @if(isset($result['data']['ogrn'])) value="{{ $result['data']['ogrn'] }}" @endif name="ogrn" minlength="12" maxlength="15">
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <label class="col-sm-3 col-form-label">Юридический адрес</label>
                        <div class="col-sm-7">
                            <div class="form-group">
                                <input class="form-control" type="text" @if(isset($result['data']['address']['value'])) value="{{ $result['data']['address']['value'] }}" @endif name="legal_address" maxlength="200">
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <label class="col-sm-3 col-form-label">Физический адрес</label>
                        <div class="col-sm-7">
                            <div class="form-group">
                                <input class="form-control" type="text" name="physical_adress" maxlength="200">
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <label class="col-sm-3 col-form-label">Генеральный директор</label>
                        <div class="col-sm-6">
                            <div class="form-group">
                                <input class="form-control" type="text" @if(isset($result['data']['management']['name'])) value="{{ $result['data']['management']['name'] }}" @elseif(isset($result['data']['name']['full'])) value="{{ $result['data']['name']['full'] }}" @endif name="general_manager" maxlength="100">
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <label class="col-sm-3 col-form-label">Телефон</label>
                        <div class="col-sm-4">
                            <div class="form-group">
                                <input class="form-control" type="tel" id="phone_number" name="phone_number" minlength="17" maxlength="17">
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <label class="col-sm-3 col-form-label">Email</label>
                        <div class="col-sm-4">
                            <div class="form-group">
                                <input class="form-control" type="email" name="email">
                            </div>
                        </div>
                    </div>
                    <h5 style="margin-top:30px; margin-bottom:20px">Реквизиты</h5>
                    <div class="row">
                        <label class="col-sm-3 col-form-label">Банк</label>
                        <div class="col-sm-7">
                            <div class="form-group">
                                <input class="form-control" type="text" name="bank_name" maxlength="100">
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <label class="col-sm-3 col-form-label">Расчетный счет</label>
                        <div class="col-sm-4">
                            <div class="form-group">
                                <input class="form-control raz" type="text" id="check_account" name="check_account" minlength="20" maxlength="20">
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <label class="col-sm-3 col-form-label">Корреспондентский счет</label>
                        <div class="col-sm-4">
                            <div class="form-group">
                                <input class="form-control raz" type="text" id="cor_account" name="cor_account" minlength="20" maxlength="20">
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <label class="col-sm-3 col-form-label">БИК</label>
                        <div class="col-sm-4">
                            <div class="form-group">
                                <input class="form-control raz" id="bik" type="text" name="bik" minlength="9" maxlength="9">
                            </div>
                        </div>
                    </div>
                    <input type="hidden" value="{{ Request::get('task_id') }}" name="task_id">
                </form>
                <div class="card-footer text-right">
                    <button type="submit" form="form_create_contractor" class="btn btn-info">Сохранить</button>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="search-inn" tabindex="-1" role="dialog" aria-labelledby="modal-search" aria-hidden="true">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="modal-search">Поиск Юридического лица</h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <div class="modal-body">
        <form id="search_inn" action="{{ route('subcontractors::search_dadata') }}">
            <div class="card border-0">
                    <div class="row">
                        <div class="col-sm-12">
                            <div class="form-group">
                                <label>Поиск</label>
                                <input class="form-control raz" type="text" name="search">
                            </div>
                            <input type="hidden" value="{{ Request::get('task_id') }}" name="task_id">
                        </div>
                        <div class="col-sm-12">
                            <label>Ищет организации и индивидуальных предпринимателей:</label>
                            <ul>
                                <li>
                                    <label>по ИНН / ОГРН;</label>
                                </li>
                                <li>
                                    <label>названию (полному, краткому, латинскому);</label>
                                </li>
                                <li>
                                    <label>ФИО (для индивидуальных предпринимателей);</label>
                                </li>
                                <li>
                                    <label>ФИО руководителя компании;</label>
                                </li>
                                <li>
                                    <label>адресу до улицы.</label>
                                </li>
                            </ul>
                            <label>*Ограничение на поиск: 10 записей</label>
                        </div>
                    </div>
                <button type="submit" form="search_inn" class="btn btn-primary">Найти</button>
            </div>
        </form>
      </div>
    </div>
  </div>
</div>

@if(isset($result) and !Request::has('id') and !Request::has('is_search') and count($results) > 1)
<div class="modal fade bd-example-modal-lg" id="choose_contractor" tabindex="-1" role="dialog" aria-labelledby="modal-search" aria-hidden="false">
  <div class="modal-dialog modal-lg" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">Поиск Юридического лица</h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <div class="modal-body">
          @if(!$results)
            <p>Записи не найдены</p>
          @else
            <table class="table mobile-table">
                <thead>
                    <tr class="row">
                        <th class="col-sm-5">Краткое название</th>
                        <th class="col-sm-2">ИНН</th>
                        <th class="col-sm-3">Город</th>
                        <th class="col-sm-2"></th>
                    </tr>
                </thead>
                <tbody>

                @for ($i = 0; $i < count($results); $i++)
                <tr class="row">
                    <td data-label="Краткое название" class="col-sm-5">{{ $results[$i]['data']['name']['short_with_opf'] }}</td>
                    <td data-label="ИНН" class="col-sm-2">{{ $results[$i]['data']['inn'] }}</td>
                    <td data-label="Город" cclass="col-sm-3">{{ $results[$i]['data']['address']['data']['city'] }}</td>
                    <td class="col-sm-2 actions text-right">
                        <a href="{{ route('subcontractors::search_dadata', ['search' => $old_search, 'id' => $i, 'task_id' => Request::get('task_id')]) }}">
                            Выбрать
                        </a>
                    </td>
                </tr>
                @endfor
                </tbody>
            </table>
        @endif
      </div>
      <div class="modal-footer">
      </div>
    </div>
  </div>
</div>
@endif

@endsection

@section('js_footer')

@if(isset($result) and !Request::has('id') and !Request::has('is_search'))
<script>
 $("#choose_contractor").modal();
</script>
@endif

<script>
$('#phone_number').mask('7 (000) 000-00-00');

$('#inn').mask('00000000000000');
$('#kpp').mask('000000000');
$('#ogrn').mask('000000000000');
$('#bik').mask('000000000');
$('#check_account').mask('00000000000000000000');
$('#cor_account').mask('00000000000000000000');

$('#form_create_contractor').validate();


</script>

@endsection
