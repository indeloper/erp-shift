@extends('layouts.app')

@section('title', 'Контрагенты')

@section('url', route('contractors::index'))

@section('content')
<div class="row">
    <div class="col-md-12">
        <div aria-label="breadcrumb" role="navigation">
            <ol class="breadcrumb">
                <li class="breadcrumb-item">
                    <a href="{{ route('contractors::card', $contractor->id) }}" class="table-link">{{ $contractor->short_name }}</a>
                </li>
                <li class="breadcrumb-item active" aria-current="page">Редактирование данных</li>
            </ol>
        </div>
    </div>
</div>
<div id="dadata"  class="row">
    <div class="col-sm-12 col-xl-9 mr-auto ml-auto">
        <div class="card ">
            <div class="card-header">
                <div class="row">
                    <div class="col-md-5">
                        <div class="left-edge">
                            <div class="page-container">
                                <h4 class="card-title" style="margin-top: 5px">Редактирование данных контрагента</h4>
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
                <form id="form_update_contractor" class="form-horizontal" action="{{ route('contractors::update', $contractor->id) }}" method="post" enctype="multipart/form-data">
                    @csrf
                    <h5 style="margin-bottom:20px">Основные данные</h5>
                    <div class="row">
                        <label class="col-sm-3 col-form-label">Полное наименование<star class="star">*</star></label>
                        <div class="col-sm-8">
                            <div class="form-group">
                                <input class="form-control" v-model="party_data.full_with_opf" value="" type="text" name="full_name" maxlength="400" required no-select="no-select">
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <label class="col-sm-3 col-form-label">Краткое наименование<star class="star">*</star></label>
                        <div class="col-sm-7">
                            <div class="form-group">
                                <input class="form-control" v-model="party_data.short_with_opf" type="text" value="" name="short_name" maxlength="100" required no-select="no-select">
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <label class="col-sm-3 col-form-label">Тип контрагента<star class="star">*</star></label>
                        <div class="col-sm-6">
                            <div class="form-group">
                                <select id="contractor_type" name="types[]" class="selectpicker" data-title="{{ $contractor->types }}" data-style="btn-default btn-outline" data-menu-style="dropdown-blue" multiple required>
                                    @foreach($contractorTypes as $key => $type)
                                        <option value="{{ $key }}" @if($contractor->main_type == $key || in_array($key, $contractor->additional_types->pluck('additional_type')->toArray())) selected @endif>{{ $type }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <label class="col-sm-3 col-form-label">ИНН</label>
                        <div class="col-sm-4">
                            <div class="form-group">
                                <input class="form-control raz" v-model="party_data.inn" type="text" value="" name="inn" id="inn" minlength="10" maxlength="12" no-select="no-select">
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <label class="col-sm-3 col-form-label">КПП</label>
                        <div class="col-sm-4">
                            <div class="form-group">
                                <input class="form-control raz" v-model="party_data.kpp" type="text" value="" name="kpp" id="kpp" minlength="9" maxlength="9" no-select="no-select">
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <label class="col-sm-3 col-form-label">ОГРН</label>
                        <div class="col-sm-4">
                            <div class="form-group">
                                <input class="form-control raz" v-model="party_data.ogrn" type="text" value="" name="ogrn" id="ogrn" no-select="no-select">
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <label class="col-sm-3 col-form-label">Юридический адрес</label>
                        <div class="col-sm-9">
                            <div class="form-group">
                                <input class="form-control" v-model="party_data.legal_address" type="text" value="" name="legal_address" maxlength="200" no-select="no-select">
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <label class="col-sm-3 col-form-label">Физический адрес</label>
                        <div class="col-sm-9">
                            <div class="form-group">
                                <input class="form-control" type="text" value="" name="physical_adress" maxlength="200" no-select="no-select">
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <label class="col-sm-3 col-form-label">Генеральный директор</label>
                        <div class="col-sm-9">
                            <div class="form-group">
                                <input class="form-control" v-model="party_data.general_manager" type="text" value="" name="general_manager" maxlength="100" no-select="no-select">

                            </div>
                        </div>
                    </div>
                    <div class="row" style="margin-top:10px">
                        <label class="col-sm-3 col-form-label">Телефон</label>
                        <div class="col-sm-9" id="telephones">
                            @php $phone_count = 0 @endphp
                            @foreach($contractor->phones as $phone)
                            <div class="row new_phone">
                                <input id="phone_count" hidden name="phone_count[]" value="{{ $phone_count }}">
                                <div class="col-sm-12">
                                    <div class="row" style="margin-bottom: 10px">
                                        <div class="col-md-7 mb-10__mobile">
                                            <input class="form-control phone_number" type="text" name="phone_number[]" minlength="14" maxlength="17" placeholder="Введите номер" value="{{ $phone->phone_number }}" no-select="no-select">
                                        </div>
                                        <div class="col-md-4 pr-0-max mb-10__mobile addition-number">
                                            <input class="form-control phone_dop" type="text" name="phone_dop[]" maxlength="5" placeholder="Добавочный" value="{{ $phone->dop_phone }}" no-select="no-select">
                                        </div>
                                    </div>
                                    <div class="row">
                                        <div class="col-md-7 col-6">
                                            <input class="form-control" name="phone_name[]" type="text" minlength="2" placeholder="Введите название" value="{{ $phone->name }}" style="margin-top: 4px;" no-select="no-select">
                                        </div>
                                        <div class="col-md-4 col-6 text-center" style="padding-left:5px">
                                            <div class="form-check form-check-radio">
                                                <label class="form-check-label" style="text-transform:none;font-size:13px; padding-top:10px">
                                                    <input class="form-check-input" type="radio" name="main" id="" value="{{ $phone_count++ }}" {{ $phone->is_main == 1 ? 'checked' : ''}}>
                                                    <span class="form-check-sign"></span>
                                                    Основной
                                                </label>
                                            </div>
                                        </div>
                                        <div class="col-md-1 remove-contact">
                                            <button type="button" class="btn-remove-mobile-modal" onclick="del_phone(this)" style="padding:5px">
                                                <i class="fa fa-times remove-stroke"></i>
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            @endforeach
                            @if(!$phone_count)
                                <div class="row new_phone">
                                    <input id="phone_count" hidden name="phone_count[]" value="0">
                                    <div class="col-sm-12">
                                        <div class="row" style="margin-bottom: 10px">
                                            <div class="col-md-7 mb-10__mobile">
                                                <input class="form-control phone_number" type="text" name="phone_number[]" minlength="14" maxlength="17" placeholder="Введите номер" value="" no-select="no-select">
                                            </div>
                                            <div class="col-md-4 pr-0-max mb-10__mobile addition-number">
                                                <input class="form-control phone_dop" type="text" name="phone_dop[]" maxlength="5" placeholder="Добавочный" value="" no-select="no-select">
                                            </div>
                                        </div>
                                        <div class="row">
                                            <div class="col-md-7 col-6">
                                                <input class="form-control" name="phone_name[]" type="text" minlength="2" placeholder="Введите название" value="" style="margin-top: 4px;" no-select="no-select">
                                            </div>
                                            <div class="col-md-4 col-6 text-center" style="padding-left:5px">
                                                <div class="form-check form-check-radio">
                                                    <label class="form-check-label" style="text-transform:none;font-size:13px; padding-top:10px">
                                                        <input class="form-check-input" type="radio" name="main" id="" value="">
                                                        <span class="form-check-sign"></span>
                                                        Основной
                                                    </label>
                                                </div>
                                            </div>
                                            <div class="col-md-1 remove-contact">
                                                <button type="button" class="btn-remove-mobile-modal" onclick="del_phone(this)" style="padding:5px">
                                                    <i class="fa fa-times remove-stroke"></i>
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @endif
                            <div class="row" >
                                <div class="col-md-12 text-center">
                                    <button type="button" class="btn-success btn" onclick="add_phone(this)">
                                        <i class="fa fa-plus"></i>
                                        Добавить
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="row" style="margin-top:40px">
                        <label class="col-sm-3 col-form-label">Email</label>
                        <div class="col-sm-4">
                            <div class="form-group">
                                <input class="form-control" type="email" value="{{ $contractor->email }}" name="email" no-select="no-select">
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-5">
                            <div class="left-edge">
                                <div class="page-container">
                                    <h5 style="margin-top:30px; margin-bottom:20px">Реквизиты</h5>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-7">
                            <div class="right-edge">
                                <div class="page-container">
                                    <button type="button" class="btn btn-outline btn-sm fill-ogrn" data-toggle="modal" data-target="#search-bank">
                                        <i class="glyphicon fa fa-file-text-o"></i>
                                        Заполнить автоматически
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <label class="col-sm-3 col-form-label">Банк</label>
                        <div class="col-sm-7">
                            <div class="form-group">
                                <input class="form-control" v-model="bank_data.bank_name" type="text" value="" name="bank_name" maxlength="100" no-select="no-select">
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <label class="col-sm-3 col-form-label">Расчетный счет</label>
                        <div class="col-sm-4">
                            <div class="form-group">
                                <input class="form-control raz" type="text" value="@if(isset($bank->check_account)) {{ $bank->check_account }} @endif" id="check_account" name="check_account" minlength="20" maxlength="20" no-select="no-select">
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <label class="col-sm-3 col-form-label">Корреспондентский счет</label>
                        <div class="col-sm-4">
                            <div class="form-group">
                                <input class="form-control raz" v-model="bank_data.correspondent_account" type="text" value="" id="cor_account" name="cor_account" minlength="20" maxlength="20" no-select="no-select">
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <label class="col-sm-3 col-form-label">БИК</label>
                        <div class="col-sm-4">
                            <div class="form-group">
                                <input class="form-control raz" v-model="bank_data.bic" type="text" value="" name="bik" id="bik" minlength="9" maxlength="9" no-select="no-select">
                            </div>
                        </div>
                    </div>
                </form>
                <div class="card-footer text-right">
                    <button type="button" onclick="contact_submit(this)" form="form_update_contractor" class="btn btn-info">Сохранить</button>
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
{{--                    <form id="search_inn" action="{{ route('contractors::search_dadata') }}">--}}
                        <div class="card border-0">
                            <div class="row">
                                <div class="col-sm-12">
                                    <div class="form-group">
                                        <label>Поиск</label>
                                        <input v-model="party_search" class="form-control raz" type="text" no-select="no-select" name="search">
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
                            <button type="button" v-on:click="party_find()" class="btn btn-primary">Найти</button>
                        </div>
                    {{--</form>--}}
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="search-bank" tabindex="-1" role="dialog" aria-labelledby="modal-search" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="modal-search">Поиск Банка</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    {{--<form id="search_bank" action="{{ route('contractors::search_dadata') }}">--}}
                        <div class="card border-0">
                            <div class="row">
                                <div class="col-sm-12">
                                    <div class="form-group">
                                        <label>Поиск</label>
                                        <input class="form-control raz" v-model="bank_search" type="text" no-select="no-select" name="search">
                                    </div>
                                    <input type="hidden" value="{{ Request::get('task_id') }}" name="task_id">
                                    <input type="hidden" value="bank" name="type">
                                </div>
                                <div class="col-sm-12">
                                    <label>Ищет Банки:</label>
                                    <ul>
                                        <li>
                                            <label>по названию (полному, краткому, латинскому);</label>
                                        </li>
                                        <li>
                                            <label>БИК;</label>
                                        </li>
                                        <li>
                                            <label>Корреспондентскому счету;</label>
                                        </li>
                                    </ul>
                                    <label>*Ограничение на поиск: 10 записей</label>
                                </div>
                            </div>
                            <button type="button"  v-on:click="bank_find()" form="search_bank" class="btn btn-primary">Найти</button>
                        </div>
                    {{--</form>--}}
                </div>
            </div>
        </div>
    </div>

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
                    <table class="table">
                        <thead>
                        <tr class="row">
                            <th class="col-sm-5">Краткое название</th>
                            <th class="col-sm-2">ИНН</th>
                            <th class="col-sm-3">Город</th>
                            <th class="col-sm-2"></th>
                        </tr>
                        </thead>
                        <tbody>
                        <tr class="row" v-for="(item,key) in party">
                            <td class="col-sm-5">@{{ item['data']['name']['short_with_opf'] }}</td>
                            <td class="col-sm-2">@{{ item['data']['inn'] }}</td>
                            <td class="col-sm-3">@{{ item['data']['address']['data']['city'] }}</td>
                            <td class="col-sm-2">
                                <button class="btn btn-primary" v-on:click="choose_one(key)">
                                    Выбрать
                                </button>
                            </td>
                        </tr>
                        </tbody>
                    </table>
                </div>
                <div class="modal-footer">
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade bd-example-modal-lg" id="choose_bank" tabindex="-1" role="dialog" aria-labelledby="modal-search" aria-hidden="false">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Поиск Банка</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <table class="table">
                        <thead>
                        <tr class="row">
                            <th class="col-sm-5">Краткое название</th>
                            <th class="col-sm-3">Корреспондентский счет</th>
                            <th class="col-sm-2">БИК</th>
                            <th class="col-sm-2"></th>
                        </tr>
                        </thead>
                        <tbody>
                        <tr class="row" v-for="(item,key) in bank">
                            <td class="col-sm-5">@{{ item['data']['name']['payment'] }}</td>
                            <td class="col-sm-3">@{{ item['data']['correspondent_account'] }}</td>
                            <td class="col-sm-2">@{{ item['data']['bic'] }}</td>
                            <td class="col-sm-2">
                                <button class="btn btn-primary" v-on:click="choose_bank(key)">
                                    Выбрать
                                </button>
                            </td>
                        </tr>
                        </tbody>
                    </table>
                </div>
                <div class="modal-footer">
                </div>
            </div>
        </div>
    </div>


</div>

<div class="row new_phone d-none">
    <input id="phone_count" hidden name="phone_count[]" value="">
    <div class="col-sm-12" style="margin-bottom:10px">
        <div class="row">
            <div class="col-md-7 mb-10__mobile">
                <input class="form-control phone_number" type="text" name="phone_number[]" minlength="14" maxlength="17" placeholder="Введите номер" no-select="no-select">
            </div>
            <div class="col-md-4 pr-0-max mb-10__mobile addition-number">
                <input class="form-control phone_dop" type="text" name="phone_dop[]"  maxlength="4" placeholder="Добавочный" no-select="no-select">
            </div>
        </div>
        <div class="row">
            <div class="col-md-7 col-6">
                <input class="form-control" name="phone_name[]" type="text" minlength="2" placeholder="Введите название" style="margin-top: 4px;" no-select="no-select">
            </div>
            <div class="col-md-4 col-6" style="padding-left:5px">
                <div class="form-check form-check-radio">
                    <label class="form-check-label" style="text-transform:none;font-size:13px;padding-top:10px">
                        <input class="form-check-input" type="radio" name="main" id="check" value="">
                        <span class="form-check-sign"></span>
                        Основной
                    </label>
                </div>
            </div>
            <div class="col-md-1 remove-contact">
                <button type="button" class="btn-remove-mobile-modal" onclick="del_phone(this)" style="padding:5px">
                    <i class="fa fa-times remove-stroke"></i>
                </button>
            </div>
        </div>
    </div>
</div>

@endsection

@section('js_footer')

<script>

    var CSRF_TOKEN = $('meta[name="csrf-token"]').attr('content');
    var count_phone = {{ $contractor->phones->count() }};

    var dadata = new Vue({
        el: '#dadata',
        data: {
            party: [],
            party_search: '',
            chosen_party: {
                'data': {
                    'name': {'full_with_opf': '{!! $contractor->full_name !!}',
                        'short_with_opf': '{!! $contractor->short_name !!}'},
                    'inn': '{!! $contractor->inn !!}',
                    'kpp': '{!! $contractor->kpp !!}',
                    'ogrn': '{!! $contractor->ogrn !!}',
                    'address': {'value': '{!! $contractor->legal_address !!}'},
                    'management': {'name': '{!! $contractor->general_manager !!}'},
                },
            },
            bank: [],
            bank_search: '',
            chosen_bank: {
                'data': {
                    'name': {'payment': '{!!  $bank->bank_name !!}'},
                    'correspondent_account': '{!!  $bank->cor_account !!}',
                    'bic': '{!!  $bank->bik !!}',
                },
            },
        },
        computed: {
            party_data: function() {
                var data_collector = [];
                if (this.chosen_party.data) {
                    data_collector['full_with_opf'] = this.chosen_party['data']['name']['full_with_opf'];
                    data_collector['short_with_opf'] = this.chosen_party['data']['name']['short_with_opf'];
                    data_collector['inn'] = this.chosen_party['data']['inn'];
                    data_collector['kpp'] = this.chosen_party['data']['kpp'];
                    data_collector['ogrn'] = this.chosen_party['data']['ogrn'];
                    data_collector['legal_address'] = this.chosen_party['data']['address']['value'];
                    data_collector['general_manager'] = this.chosen_party['data']['management']['name'];
                }
                return data_collector;
            },
            bank_data: function() {
                var data_collector = [];
                if(this.chosen_bank.data) {
                    data_collector['bank_name'] = this.chosen_bank['data']['name']['payment'];
                    data_collector['correspondent_account'] = this.chosen_bank['data']['correspondent_account'];
                    data_collector['bic'] = this.chosen_bank['data']['bic'];
                }
                return data_collector;
            },
        },
        methods: {
            party_find: function () {
                $.ajax({
                    url:'{{ route("contractors::search_dadata") }}',
                    type: 'GET',
                    data: {
                        _token: CSRF_TOKEN,
                        type: 'party',
                        ajax: true,
                        search: dadata.party_search,
                    },
                    dataType: 'JSON',
                    success: function (data) {
                        if (data.length === 0) {
                            swal({
                                title: 'Ничего не нашлось',
                                text: 'Пожалуйста, переформулируйте запрос',
                                type: 'error',
                            });
                        } else {
                            if (data.data) {
                                data = [data];
                            }
                            dadata.party = data;
                            $('#search-inn').modal('toggle');
                            $('#choose_contractor').modal('toggle');
                        }
                    }
                });
            },
            choose_one: key => {
                dadata.chosen_party = dadata.party[key];
                $('#choose_contractor').modal('toggle');
            },

            bank_find: function () {
                $.ajax({
                    url:'{{ route("contractors::search_dadata") }}',
                    type: 'GET',
                    data: {
                        _token: CSRF_TOKEN,
                        type: 'bank',
                        ajax: true,
                        search: dadata.bank_search,
                    },
                    dataType: 'JSON',
                    success: function (data) {
                        if (data.length === 0) {
                            swal({
                                title: 'Ничего не нашлось',
                                text: 'Пожалуйста, переформулируйте запрос',
                                type: 'error',
                            });
                        } else {
                            if (data.data) {
                                data = [data];
                            }
                            dadata.bank = data;
                            $('#search-bank').modal('toggle');
                            $('#choose_bank').modal('toggle');
                        }
                    }
                });
            },
            choose_bank: key => {
                dadata.chosen_bank = dadata.bank[key];
                $('#choose_bank').modal('toggle');
            },

        }
    });


    function add_phone(e) {
        var next_phone = count_phone;
        count_phone++;

        $(".new_phone.d-none").find('.form-check-input').val(next_phone);
        $(".new_phone.d-none").find('#phone_count').val(next_phone);

        clone = $(".new_phone.d-none").clone().removeClass('d-none').insertBefore($(e));
        clone.find('select').select2({
            tags: true,
        });

        clone.find('.phone_number').mask('7 (000) 000-00-00');
        clone.find('.phone_dop').mask('0000');
    }


    function del_phone(e) {
        if ($(e).parents('.col-sm-9').find('.new_phone').length > 1) {
            $(e).closest('.new_phone').remove();
        }
    }


    function check_unique(form) {
        return $.ajax({
            url:"{{ route('contractors::is_unique') }}",
            type: 'GET',
            data: {
                _token: CSRF_TOKEN,
                full_name: $('#'+form).find(`[name='full_name']`).val(),
                inn: $('#'+form).find(`[name='inn']`).val(),
                ogrn: $('#'+form).find(`[name='ogrn']`).val(),
                email: $('#'+form).find(`[name='email']`).val(),
                id: {{$contractor->id}},
            },
            dataType: 'JSON',
            success: function (data) {
                if (data !== true) {
                    swal({
                        title: data[1],
                        text: "Поле '" + data[1] +"' должно быть уникальным среди всех контрагентов",
                        type: 'error',
                    });
                    return false;
                } else {
                    return true;
                }
            }
        });
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
        $.when(check_unique(form)).done(function (is_unique) {
        if ($('#'+form)[0].reportValidity() && is_unique === true) {
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
        });
    }

    $('#contractor_type').change(function () {
        let select = $(this)[0];
        let selectedValuesLength = select.selectedOptions.length;
        if (selectedValuesLength === 0) {
            $(select).selectpicker({title: 'Выберите тип контрагента'}).selectpicker('render');
        }
    });

$('.phone_number').mask('7 (000) 000-00-00');
$('.phone_dop').mask('0000');
$('#inn').mask('000000000000');
$('#kpp').mask('000000000');
$('#ogrn').mask('000000000000000');
$('#bik').mask('000000000');
$('#check_account').mask('00000000000000000000');
$('#cor_account').mask('00000000000000000000');
</script>
@endsection
