@extends('layouts.app')

@section('title', 'Контрагенты')

@section('url', route('contractors::index'))

@section('content')
<div id="dadata" class="row">
    <div class="col-sm-12 col-xl-9 mr-auto ml-auto">
        <div class="card ">
            <div class="card-header">
                <div class="row">
                    <div class="col-md-5">
                        <div class="left-edge">
                            <div class="page-container">
                                <h4 class="card-title" style="margin-top: 5px">Новый контрагент</h4>
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
                <form id="form_create_contractor" class="form-horizontal" action="{{ route('contractors::store') }}" method="post" enctype="multipart/form-data">
                    @csrf
                    <div class="row">
                        <label class="col-sm-3 col-form-label">Полное наименование<star class="star">*</star></label>
                        <div class="col-sm-8">
                            <div class="form-group">
                                <input class="form-control" type="text" v-model="party_data.full_with_opf" no-select="no-select" name="full_name" required maxlength="400">
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <label class="col-sm-3 col-form-label">Краткое наименование<star class="star">*</star></label>
                        <div class="col-sm-6">
                            <div class="form-group">
                                <input class="form-control" type="text" v-model="party_data.short_with_opf" no-select="no-select" name="short_name" required maxlength="100">
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <label class="col-sm-3 col-form-label">Тип контрагента<star class="star">*</star></label>
                        <div class="col-sm-6">
                            <div class="form-group">
                                <select id="contractor_type" name="types[]" class="selectpicker" data-title="Выберите тип контрагента" data-style="btn-default btn-outline" data-menu-style="dropdown-blue" multiple required>
                                    @foreach($contractorTypes as $key => $type)
                                        <option value="{{ $key }}">{{ $type }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <label class="col-sm-3 col-form-label">ИНН</label>
                        <div class="col-sm-4">
                            <div class="form-group">
                                <input class="form-control raz" id="inn" v-model="party_data.inn" type="text" no-select="no-select" name="inn" minlength="10" maxlength="14">
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <label class="col-sm-3 col-form-label">КПП</label>
                        <div class="col-sm-4">
                            <div class="form-group">
                                <input class="form-control raz" id="kpp" v-model="party_data.kpp" type="text" no-select="no-select" name="kpp" minlength="9" maxlength="9">
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <label class="col-sm-3 col-form-label">ОГРН</label>
                        <div class="col-sm-4">
                            <div class="form-group">
                                <input class="form-control raz" id="ogrn" v-model="party_data.ogrn" type="text" no-select="no-select"  name="ogrn" minlength="12" maxlength="15">
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <label class="col-sm-3 col-form-label">Юридический адрес</label>
                        <div class="col-sm-7">
                            <div class="form-group">
                                <input class="form-control" v-model="party_data.legal_address" type="text" no-select="no-select" name="legal_address" maxlength="200">
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <label class="col-sm-3 col-form-label">Физический адрес</label>
                        <div class="col-sm-7">
                            <div class="form-group">
                                <input class="form-control" type="text" no-select="no-select" name="physical_adress" maxlength="200">
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <label class="col-sm-3 col-form-label">Генеральный директор</label>
                        <div class="col-sm-6">
                            <div class="form-group">
                                <input class="form-control" v-model="party_data.general_manager" type="text" no-select="no-select" name="general_manager" maxlength="100">
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <label class="col-sm-3 col-form-label">Телефон</label>
                        <div class="col-sm-9" id="telephones">
                            <div class="row new_phone" style="margin: -6px">
                                <input id="phone_count" hidden name="phone_count[]" value="0">
                                <div class="col-sm-12">
                                    <div class="row">
                                        <div class="col-md-8">
                                            <input class="form-control" name="phone_name[]" type="text" no-select="no-select" minlength="2" placeholder="Введите название" style="margin-top: 4px;">
                                        </div>
                                        <div class="col-md-4">
                                            <div class="form-check form-check-radio">
                                                <label class="form-check-label" style="text-transform:none;font-size:13px; padding-top:10px">
                                                    <input class="form-check-input" type="radio" name="main" id="" value="0">
                                                    <span class="form-check-sign"></span>
                                                    Основной
                                                </label>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="row">
                                        <div class="col-sm-8">
                                            <input class="form-control phone_number" type="text" no-select="no-select" name="phone_number[]" minlength="14" maxlength="17" placeholder="Введите номер">
                                        </div>
                                        <div class="col-sm-3" style="margin-top:7px">
                                            <input class="form-control phone_dop" type="text" no-select="no-select" name="phone_dop[]" maxlength="5" placeholder="Добавочный">
                                        </div>
                                        <div class="col-sm-1 text-center">
                                            <button type="button" class="btn-danger btn-link btn-xs btn pd-0" onclick="del_phone(this)" style="margin-top:8px">
                                                <i class="fa fa-times"></i>
                                            </button>
                                        </div>
                                    </div>
                                    <hr class="hr-work">
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="row" style="margin-top:20px">
                        <div class="col-md-3">
                        </div>
                        <div class="col-md-9 text-center">
                            <button type="button" class="btn-success btn btn-outline btn-round" onclick="add_phone(this)">
                                <i class="fa fa-plus"></i>
                                Добавить
                            </button>
                        </div>
                    </div>
                    <div class="row" style="margin-top:30px">
                        <label class="col-sm-3 col-form-label">Email</label>
                        <div class="col-sm-4">
                            <div class="form-group">
                                <input class="form-control" no-select="no-select" type="email" name="email">
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
                                <input class="form-control" v-model="bank_data.bank_name" type="text" no-select="no-select" name="bank_name" maxlength="100">
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <label class="col-sm-3 col-form-label">Расчетный счет</label>
                        <div class="col-sm-4">
                            <div class="form-group">
                                <input class="form-control raz" type="text" no-select="no-select" id="check_account" name="check_account" minlength="20" maxlength="20">

                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <label class="col-sm-3 col-form-label">Корреспондентский счет</label>
                        <div class="col-sm-4">
                            <div class="form-group">
                                <input class="form-control raz" v-model="bank_data.correspondent_account" type="text" no-select="no-select" id="cor_account" name="cor_account" minlength="20" maxlength="20">
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <label class="col-sm-3 col-form-label">БИК</label>
                        <div class="col-sm-4">
                            <div class="form-group">
                                <input class="form-control raz" v-model="bank_data.bic" id="bik" type="text" no-select="no-select" name="bik" minlength="9" maxlength="9">
                            </div>
                        </div>
                    </div>
                    <input type="hidden" value="{{ Request::get('task_id') }}" name="task_id">
                </form>
                <div class="card-footer text-right">
                    <button type="button" onclick="contact_submit(this)" form="form_create_contractor" class="btn btn-info">Сохранить</button>
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
                            <button type="button" v-on:click="party_find()" form="search_inn" class="btn btn-primary">Найти</button>
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


<div class="row new_phone d-none" style="margin: -6px">
    <input id="phone_count" hidden name="phone_count[]" value="">
    <div class="col-sm-12">
        <div class="row">
            <div class="col-sm-8">
                <input class="form-control" name="phone_name[]" type="text" no-select="no-select" minlength="2" placeholder="Введите название" style="margin-top: 4px;">
            </div>
            <div class="col-4">
                <div class="form-check form-check-radio">
                    <label class="form-check-label" style="text-transform:none;font-size:13px;padding-top:10px">
                        <input class="form-check-input" type="radio" name="main" id="check" value="">
                        <span class="form-check-sign"></span>
                        Основной
                    </label>
                </div>
            </div>
            <!-- <div class="col-1">
                <button type="button" class="btn-success btn-link btn-xs btn pd-0" onclick="add_phone(this)">
                    <i class="fa fa-plus"></i>
                </button>
            </div> -->
        </div>
        <div class="row">
            <div class="col-sm-8">
                <input class="form-control phone_number" type="text" no-select="no-select" name="phone_number[]" minlength="14" maxlength="17" placeholder="Введите номер">
            </div>
            <div class="col-sm-3">
                <input class="form-control phone_dop" type="text" no-select="no-select" name="phone_dop[]"  maxlength="4" placeholder="Добавочный">
            </div>
            <div class="col-sm-1 text-center">
                <button type="button" class="btn-danger btn-link btn-xs btn pd-0" onclick="del_phone(this)" style="margin-top:8px">
                    <i class="fa fa-times"></i>
                </button>
            </div>

        </div>
        <hr class="hr-work">
    </div>
</div>

@endsection

@section('js_footer')

<meta name="csrf-token" content="{{ csrf_token() }}" />
<script>

    var count_phone = 1;

    var dadata = new Vue({
        el: '#dadata',
        data: {
            party: [],
            party_search: '',
            chosen_party: [],
            bank: [],
            bank_search: '',
            chosen_bank: [],
        },
        computed: {
            party_data: function() {
                var data_collector = [];
                if(this.chosen_party.data) {
                    data_collector['full_with_opf'] = this.chosen_party['data']['name']['full_with_opf'] ? this.chosen_party['data']['name']['full_with_opf'] : '';
                    data_collector['short_with_opf'] = this.chosen_party['data']['name']['short_with_opf'] ? this.chosen_party['data']['name']['short_with_opf'] : '';
                    data_collector['inn'] = this.chosen_party['data']['inn'] ? this.chosen_party['data']['inn'] : '';
                    data_collector['kpp'] = this.chosen_party['data']['kpp'] ? this.chosen_party['data']['kpp'] : '';
                    data_collector['ogrn'] = this.chosen_party['data']['ogrn'] ? this.chosen_party['data']['ogrn'] : '';
                    data_collector['legal_address'] = this.chosen_party['data']['address']['value'] ? this.chosen_party['data']['address']['value'] : '';
                    data_collector['general_manager'] = this.chosen_party['data']['management'] !== undefined ? this.chosen_party['data']['management']['name'] : '';
                }
                return data_collector;
            },
            bank_data: function() {
                var data_collector = [];
                if(this.chosen_bank.data) {
                    data_collector['bank_name'] = this.chosen_bank['data']['name']['payment'] ? this.chosen_bank['data']['name']['payment'] : '';
                    data_collector['correspondent_account'] = this.chosen_bank['data']['correspondent_account'] ? this.chosen_bank['data']['correspondent_account'] : '';
                    data_collector['bic'] = this.chosen_bank['data']['bic'] ? this.chosen_bank['data']['bic'] : '';
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

    var CSRF_TOKEN = $('meta[name="csrf-token"]').attr('content');
    function add_phone(e) {
        var next_phone = count_phone;
        count_phone++;

        $(".new_phone.d-none").find('.form-check-input').val(next_phone);
        $(".new_phone.d-none").find('#phone_count').val(next_phone);

        clone = $(".new_phone.d-none").clone().removeClass('d-none').appendTo('#telephones');
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
                if (unique_phones.length == phones.length) { //unлique
                    if (phones.length == 1) {
                        if (names.indexOf('') != -1 && phones[0] != '') {
                            swal({
                                title: "Внимание",
                                text: "Заполните названия телефонов",
                                type: 'warning',
                                // timer: 2500,
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

$('.phone_number').mask('7 (000) 000-00-00');
$('.phone_dop').mask('0000');
$('#inn').mask('00000000000000');
$('#kpp').mask('000000000');
$('#ogrn').mask('000000000000000');
$('#bik').mask('000000000');
$('#check_account').mask('00000000000000000000');
$('#cor_account').mask('00000000000000000000');
</script>

@endsection
