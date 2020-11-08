@extends('layouts.app')

@section('title', 'Материальный учет')

@section('url', '')

@section('css_top')
<link href="{{ mix('css/jquery-ui.min.css') }}" rel="stylesheet" />
<style>
    .el-select {width: 100%}
    .el-date-editor.el-input {width: inherit;}
    .margin-top-15 {
        margin-top: 15px;
    }

    .el-date-editor.el-input {
        width: 100%;
    }

    .el-date-table__row,
    .el-picker-panel__content,
    .el-date-table,
    .el-date-table tbody,
    .el-picker-panel__body,
    .el-picker-panel__body-wrapper,
    .el-picker-panel {
        cursor: pointer!important;
    }

    .el-date-table td {
        cursor: pointer!important;
    }
    .el-date-table td div{
        cursor: pointer!important;
    }
    .el-date-table td div span{
        cursor: pointer!important;
    }

    .el-input__inner {
        cursor: pointer;
    }

    .tooltip-inner {background-color: white; color: #212529; border: 1px solid black}
    .tooltip-arrow { border-bottom-color: inherit; }

    .calculation-results {
        min-height: 100px;
    }

    .mobile-hint {
        display: none;
        font-style: italic;
    }

    .floating-calculator {
        position: fixed;
        background: white;
        border: 1px solid #e3e3e3;
        border-radius: 4px;
        box-shadow: 0 2px 4px rgba(0, 0, 0, .12), 0 0 6px rgba(0, 0, 0, .04);
        bottom: 3%;
        right: 5%;
    }

    .ui-autocomplete-category {
        font-weight: bold;
        padding: .2em .4em;
        margin: .8em 0 .2em;
        line-height: 1.5;
    }

    .el-checkbox__label {
        text-transform: none;
    }

    .el-input-number--mini {
        width: 70px;
    }

    .el-input-number .el-input__inner {
        padding-left: 10px !important;
        padding-right: 10px !important;
    }

    .etalon-parameter {
        -webkit-transform:scale(0.85, 0.85);
        transform: scale(0.85);
        padding-left: 0 !important;
        padding-right: 0 !important;
    }

    @media (max-width: 768px) {
        .floating-calculator {
            text-align: center !important;
            left: -2%;
            bottom: 0;
            width: 105%;
        }

        .calculation-results {
            text-align: center !important;
            font-size: 0.8rem;
            min-height: 170px;
        }

        .mobile-hint {
            display: initial;
        }

        .ui-autocomplete {
            max-height: 200px;
            overflow-y: auto;
            /* prevent horizontal scrollbar */
            overflow-x: hidden;
            /* add padding to account for vertical scrollbar */
            padding-right: 20px;
        }
    }

    @media (max-width: 1025px){
        .amount-materials {
            margin-left: 88px;
        }
        .amount-materials:first-child {
            margin-left: 0px;
        }
    }

    @media (min-width: 1480px) {
        .manual-etalon-range {
            margin-top: -7px !important;
        }
    }
</style>
@endsection

@section('content')
<div class="row" style="margin-bottom:80px">
    <div class="col-md-12">
        <div class="nav-container" style="margin:0 0 10px 15px">
            <ul class="nav nav-icons" role="tablist">
                <li class="nav-item active">
                    <a class="nav-link link-line active-link-line" href="{{ route('building::mat_acc::report_card') }}">
                        Табель материального учёта
                    </a>
                </li>
                <li class="nav-item ">
                    <a class="nav-link link-line" href="{{ route('building::mat_acc::operations') }}">
                        Журнал операций
                    </a>
                </li>
                @can('see_certificateless_operations')
                    <li class="nav-item">
                        <a class="nav-link link-line" href="{{ route('building::mat_acc::certificateless_operations') }}">
                            Журнал операций без сертификата
                        </a>
                    </li>
                @endcan
            </ul>
        </div>
        <div class="card strpied-tabled-with-hover" id="filter">
            <div class="card-body">
                <h6>Фильтрация</h6>
                <div class="row">
                    <div class="col-md-3" style="margin:15px 5px 0 0">
                        <template>
                            <el-select v-model="parameter" @change="changeFilterValues" value-key="text" filterable :remote-method="search" remote placeholder="Поиск">
                                <el-option
                                    v-for="param in parameter_options"
                                    :selected="param.id == 1"
                                    :value="param"
                                    :key="param.id"
                                    :label="param.text">
                                </el-option>
                            </el-select>
                        </template>
                    </div>
                    <div class="col-md-4" style="margin:15px 5px 0 0">
                        <el-select v-if="parameter.text.toLowerCase() !== 'эталон'" v-model="param_value" value-key="label" @keyup.native.enter="() => {filter(false); $refs['main-filter'].blur();}"
                            ref="main-filter"
                            clearable filterable :remote-method="search" remote placeholder="Поиск">
                            <el-option
                                v-for="item in filter_values"
                                :key="item.code"
                                :label="item.label"
                                :value="item">
                            </el-option>
                        </el-select>
                        <validation-provider rules="required" vid="select-category"
                                                ref="select-category" v-slot="v" v-else>
                            <el-select v-model="category_id" :class="v.classes" @change="getNeedAttributes" placeholder="Выберите категорию материала">
                                <el-option
                                    v-for="item in categories"
                                    :key="item.id"
                                    id="select-category"
                                    :label="item.name"
                                    :value="item.id">
                                </el-option>
                            </el-select>
                        </validation-provider>
                    </div>
                    <div class="col-md-2 d-none d-md-inline-block" style="margin:15px 5px 0 0">
                        <div class="left-edge">
                            <div class="page-container">
                                <button type="button" v-on:click="() => {parameter.text.toLowerCase() === 'эталон' ? createMaterial() : filter(false)}"
                                    class="btn btn-wd btn-info">Добавить</button>
                            </div>
                        </div>
                    </div>
                    @if (config("app.env") == "local_dev")
                        <div class="col-md-2" style="margin:15px 5px 0 0">
                            <div class="left-edge">
                                <div class="page-container">
                                    <button type="button" onclick="manualTransfer()" class="btn btn-wd btn-outline" title="запустить команду mat_acc:transfer" style="color: antiquewhite;background-color: aliceblue;border-color: aliceblue;">Ручной перенос операций</button>
                                </div>
                            </div>
                        </div>
                    @endif
                </div>
                <div class="row" v-if="parameter.text.toLowerCase() === 'эталон'">
                    <div class="col-md-12 col-xl-12">
                        <template>
                                <el-form label-position="top">
                                    <validation-observer ref="observer" :key="observer_key">
                                        <div class="row" v-if="need_attributes.length < 4 && i === 1 || need_attributes.length >= 4 && i % 2 === 1" v-for="i in need_attributes.length">
                                            <template v-for="(attribute, index) in need_attributes">
                                                    <div
                                                        is="material-attribute"
                                                        v-if="need_attributes.length >= 4 && (index === i || index === i - 1)  || need_attributes.length < 4"
                                                        :key="attribute.id"
                                                        :id="'select-' + attribute.id"
                                                        class="col-md-3"
                                                        style="margin: 0 5px 25px 0px;"
                                                        :index="index"
                                                        :ref="'select-' + attribute.id"
                                                        :attribute_id.sync="attribute.id"
                                                        :attribute_unit.sync="attribute.unit"
                                                        :attribute_name.sync="attribute.name"
                                                        :attribute_value.sync="attribute.value"
                                                        :attribute_etalon_id.sync="attribute.etalon_id"
                                                        :attribute_is_required.sync="attribute.is_required"
                                                        :category_id.sync="attribute.category_id"
                                                    >
                                                    </div>
                                            </template>
                                        </div>
                                    </validation-observer>
                              </el-form>
                      </template>
                    </div>
                </div>
                <h6 class="mt-3">Поиск</h6>
                <div class="row mb-2">
                    <div class="col-md-3 el-select" style="margin: 15px 5px 0px 0px;">
                        <div class="el-input el-input--prefix el-input--suffix">
                            <input  type="text"
                                    placeholder="Глобальный поиск"
                                    v-model="globalSearch"
                                    class="el-input__inner"
                                    id="search"
                            ></input>
                            <span class="el-input__prefix">
                                <i class="el-input__icon el-icon-search"></i>
                            </span>
                            <span v-if="globalSearch" @click="globalSearch = ''" class="el-input__suffix" style="cursor: pointer">
                                <span class="el-input__suffix-inner">
                                    <i class="el-input__icon el-icon-circle-close"></i>
                                </span>
                            </span>
                        </div>
                        <div v-if="hasRealParams" class="mt-2">
                            <el-checkbox v-model="ignoreParams" @change="onChangeEtalonRange" label="Без параметров"></el-checkbox>
                        </div>
                    </div>
                </div>
                <template v-if="etalon">
                    <div class="row mb-2">
                        <div class="col-md-3 mb-4 etalon-parameter" v-for="(entry, i) in Object.entries(etalon.attr_ids).filter(el => el[1].min !== null && el[1].max !== null)">
                            <div class="row">
                                <div class="col-12">
                                    <label class="d-block">@{{ entry[1].name  }}</label>
                                    <el-slider v-model="entry[1].range"
                                            range
                                            :key="slider_key + i"
                                            :disabled="ignoreParams"
                                            style="width: 100%"
                                            @change="onChangeEtalonRange"
                                            :min="entry[1].min"
                                            :max="entry[1].max"
                                            :step="1"
                                            :marks="entry[1].marks"
                                    ></el-slider>

                                </div>
                                <div class="col-6 text-center mt-3 manual-etalon-range">
                                    {{-- <label class="d-block">От<span class="star">*</span></label> --}}
                                    <el-input-number
                                                @change="onChangeEtalonRange"
                                                v-model="entry[1].range[0]"
                                                :precision="0"
                                                :controls="false"
                                                size="mini"
                                                label="От"
                                                :disabled="ignoreParams"
                                                :key="'f' + slider_key + i"
                                                :step="1"
                                                :min="entry[1].min"
                                                :max="entry[1].range[1]"
                                                placeholder="От"
                                                required
                                    ></el-input-number>
                                </div>
                                <div class="col-2 text-center mt-3 ml-3 manual-etalon-range">
                                    {{-- <label class="d-block">До<span class="star">*</span></label> --}}
                                    <el-input-number
                                                @change="onChangeEtalonRange"
                                                v-model="entry[1].range[1]"
                                                :disabled="ignoreParams"
                                                :controls="false"
                                                size="mini"
                                                label="До"
                                                :precision="0"
                                                :key="'t' + slider_key + i"
                                                :step="1"
                                                :min="entry[1].range[0]"
                                                :max="entry[1].max"
                                                placeholder="До"
                                                required
                                    ></el-input-number>
                                </div>
                            </div>
                        </div>
                    </div>
                </template>
                <div class="row d-md-none mt-3">
                    <div class="col">
                        <div class="left-edge">
                            <div class="page-container">
                                <button type="button" v-on:click="() => {parameter.text.toLowerCase() === 'эталон' ? createMaterial() : filter(false)}"
                                    class="btn btn-wd btn-info">Добавить</button>
                            </div>
                        </div>
                    </div>
                </div>
                <template>
                    <div class="row mt-4" v-if="filter_items.length > 0">
                        <div class="col-md-12" style="margin: 10px 0 10px 0">
                            <h6>Выбранные фильтры</h6>
                        </div>
                    </div>
                    <div class="row" v-if="filter_items.length > 0">
                        <div class="col-md-9">
                            <div class="bootstrap-tagsinput">
                                <span class="badge badge-azure" v-on:click="delete_budge(index)" v-for="(item, index) in filter_items">@{{ item.parameter_text }}: @{{ item.value }}<span data-role="remove" class="badge-remove-link"></span></span>
                            </div>
                        </div>
                        <div class="col-md-3 text-right mnt-20--mobile text-center--mobile">
                            <button type="button" @click="clearFilters" class="btn btn-sm show-all">
                                Удалить фильтры
                            </button>
                        </div>
                    </div>
                </template>
            </div>
        </div>
        <div class="card strpied-tabled-with-hover" id="bases">
            <div class="fixed-table-toolbar toolbar-for-btn">
                <div class="row border-bottom--mobile" style="margin-bottom:10px">
                    <div class="col-md-3" style="margin-top:5px">
                        <!-- <span style="font-size:14px; font-weight:600; margin:7px 15px 0 0;">
                            Дата
                        </span> -->
                        <template>
                            <div class="block">
                                <el-date-picker style="cursor:pointer"
                                                v-model="search_date"
                                                format="dd.MM.yyyy"
                                                value-format="dd.MM.yyyy"
                                                type="date"
                                                placeholder="Выберите день"
                                                name="planned_date_from"
                                                @focus = "onFocus"
                                                :picker-options="endDatePickerOptions"
                                >
                                </el-date-picker>
                            </div>
                        </template>
                    </div>
                    <div class="col-md-9 text-right mnt-20--mobile" >
                        <button type="button" v-on:click="print_rep()" class="btn btn-wd btn-outline" style="margin-top:5px">
                            <i class="fa fa-print"></i>
                            Печать
                        </button>
                    </div>
                </div>
            </div>
            <div class="table-responsive">
                <table class="table table-hover mobile-table">
                    <thead>
                    <tr>
                        <th class="sort">Объект</th>
                        <th class="sort">Материал</th>
                        <th class="sort">Количество</th>
                        <th class="text-right">Операции</th>
                        <th>
                            <div class="form-check">
                                <label class="form-check-label">
                                    <input class="form-check-input" type="checkbox" v-model="check_all">
                                    <span class="form-check-sign"></span>
                                </label>
                            </div>
                        </th>
                    </tr>
                    </thead>
                    <tbody>
                    <template>
                        <tr v-for="(base, key) in bases">
                            <td style="width: 35%" data-label="Объект">@{{ base.object.name_tag }}</td>
                            <td data-label="Материал"><b>@{{ base.material_name }}</b></td>
                            <td data-label="Количество">
                                <b>@{{ base.round_count }}</b> @{{ base.unit }}<br>
                                <span v-for="(item, item_key) in base.convert_params" class="amount-materials">
                                    <b>@{{ calculateConvertedAmount(base.round_count, item.value) }}</b> @{{ item.unit }}<br>
                                </span>
                            </td>
                            <td data-label="Действия" class="actions-dropdown actions-dropdown-desk">
                                <a href="#" data-toggle="dropdown" class="btn dropdown-toggle btn-link btn-xs btn-space" data-original-title="Запланировать операцию">
                                    <i class="fa fa-bars"></i>
                                </a>
                                <ul class="dropdown-menu dropdown-menu-right">
                                    <a class="dropdown-item" href="{{ route('building::mat_acc::arrival::create') }}">Поступление</a>
                                    <a class="dropdown-item" href="{{ route('building::mat_acc::write_off::create') }}">Списание</a>
                                    <a class="dropdown-item" href="{{ route('building::mat_acc::transformation::create') }}">Преобразование</a>
                                    <a class="dropdown-item" href="{{ route('building::mat_acc::moving::create') }}">Перемещение</a>
{{--                                    @canany(['mat_acc_base_move_to_new', 'mat_acc_base_move_to_used'])--}}

{{--                                        @can('mat_acc_base_move_to_new')--}}
                                            <template v-if="base.used === true">
                                                <button class="dropdown-item" @click.stop="moveToNew(base)">Отметить как новый</button>
                                            </template>
{{--                                        @endcan--}}
{{--                                        @can('mat_acc_base_move_to_used')--}}
                                            <template v-if="base.used === false">
                                                <button class="dropdown-item" @click.stop="moveToUsed(base)">Отметить как Б/У</button>
                                            </template>
{{--                                        @endcan--}}
{{--                                    @endcanany--}}
                                </ul>
                            </td>
                            <td>
                                <div class="form-check">
                                    <label class="form-check-label">
                                        <input class="form-check-input" type="checkbox" v-model="base.checked" @change="recalculate(false)">
                                        <span class="form-check-sign"></span>
                                    </label>
                                </div>
                            </td>
                        </tr>
                    </template>
                    </tbody>
                </table>
            </div>
            <div class="calculation-results">
                <div v-if="bases.length > 0"
                     :class="{ 'text-right p-3': true, 'floating-calculator': bases.length > 1 && untilPageEnd > 270 }"
                     style="border-top: 1px solid #e3e3e3"
                >
                    <div>
                        Итог по всем материалам: <span id="sum-total" class="font-weight-bold text-uppercase">–</span>
                    </div>
                    <div :class="{ 'd-none': !any_checked }">
                        Итог по выбранным материалам: <span id="sum-partial" class="font-weight-bold text-uppercase">–</span>
                    </div>
                    <div class="mobile-hint d-none text-right">
                        <button
                            type="button"
                            class="btn btn-link btn-primary btn-xs pd-0"
                        >
                            <i class="fa fa-info-circle"></i>
                        </button>
                        Не все материалы поддерживают данную единицу измерения, они не были включены в это число.
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<form id="print_report" target="_blank" multisumit="true" method="post" action="{{route('building::mat_acc::report::print')}}">
    @csrf
    <input id="print_data" type="hidden" name="results">
</form>
@canany(['mat_acc_base_move_to_new', 'mat_acc_base_move_to_used'])
    @can('mat_acc_base_move_to_new')
        <div class="modal fade bd-example-modal-wd show" id="toNew" role="dialog" aria-labelledby="modal-search" style="display: none;">
            <div class="modal-dialog modal-wd" role="document">
                <validation-observer ref="observer" :key="observer_key">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title">Перевод Б/У материала в состояние нового</h5>
                            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                <span aria-hidden="true">×</span>
                            </button>
                        </div>
                        <div class="modal-body">
                            <div class="row">
                                <div class="col-md-12 mb-20 mt-20">
                                    <label for="">Материал (Б/У)</label>
                                    <validation-provider rules="required" vid="material-select" ref="material-select" name="материал" v-slot="v">
                                        <el-select v-model="base_id" id="material-select" :class="v.classes" disabled>
                                            <el-option
                                                :key="base.id"
                                                :label="base.material_name"
                                                :value="base.id">
                                            </el-option>
                                        </el-select>
                                        <div class="error-message">@{{ v.errors[0] }}</div>
                                    </validation-provider>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-12 mb-20 mt-20">
                                    <label for="">Доступное количество</label>
                                    <el-input-number disabled v-model="base_count" :precision="3" :step="0.001" class="d-block w-100"></el-input-number>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-12 mb-20 mt-20">
                                    <validation-provider rules="required" vid="count" ref="count" name="материал" v-slot="v">
                                        <label for="">Перевести в состояние нового</label>
                                        <el-input-number id="count" v-model="count" :precision="3" :step="0.001" :min="0.001" :max="base_count" class="d-block w-100"></el-input-number>
                                        <div class="error-message">@{{ v.errors[0] }}</div>
                                    </validation-provider>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-12 text-center mt-30">
                                    <div class="row justify-content-center mb-2">
                                        <el-button @click.stop="submit" :loading="loading_submit" type="primary" class="btn btn-info">Перевести</el-button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </validation-observer>
            </div>
        </div>
    @endcan
    @can('mat_acc_base_move_to_used')
        <div class="modal fade bd-example-modal-wd show" id="toUsed" role="dialog" aria-labelledby="modal-search" style="display: none;">
            <div class="modal-dialog modal-wd" role="document">
                <validation-observer ref="observer" :key="observer_key">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title">Перевод нового материала в состояние Б/У</h5>
                            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                <span aria-hidden="true">×</span>
                            </button>
                        </div>
                        <div class="modal-body">
                            <div class="row">
                                <div class="col-md-12 mb-20 mt-20">
                                    <label for="">Материал</label>
                                    <validation-provider rules="required" vid="material-select" ref="material-select" name="материал" v-slot="v">
                                        <el-select v-model="base_id" id="material-select" :class="v.classes" disabled>
                                            <el-option
                                                :key="base.id"
                                                :label="base.material_name"
                                                :value="base.id">
                                            </el-option>
                                        </el-select>
                                        <div class="error-message">@{{ v.errors[0] }}</div>
                                    </validation-provider>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-12 mb-20 mt-20">
                                    <label for="">Доступное количество</label>
                                    <el-input-number disabled v-model="base_count" :precision="3" :step="0.001" class="d-block w-100"></el-input-number>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-12 mb-20 mt-20">
                                    <validation-provider rules="required" vid="count" ref="count" name="материал" v-slot="v">
                                        <label for="">Перевести в Б/У</label>
                                        <el-input-number id="count" v-model="count" :precision="3" :step="0.001" :min="0.001" :max="base_count" class="d-block w-100"></el-input-number>
                                        <div class="error-message">@{{ v.errors[0] }}</div>
                                    </validation-provider>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-12 text-center mt-30">
                                    <div class="row justify-content-center mb-2">
                                        <el-button @click.stop="submit" :loading="loading_submit" type="primary" class="btn btn-info">Перевести</el-button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </validation-observer>
            </div>
        </div>
    @endcan
@endcanany
@endsection

@section('js_footer')
<script src="{{ mix('js/plugins/jquery-ui.min.js') }}"></script>
<script type="text/javascript">
    Vue.component('validation-provider', VeeValidate.ValidationProvider);
    Vue.component('validation-observer', VeeValidate.ValidationObserver);
    $("#materials").datetimepicker({
        format: 'DD.MM.YYYY',
        locale: 'ru',
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
        maxDate: moment(),
        date: null
    });

    $(document).ready(function() {
        $('.js-example-basic-single').select2({
            language: "ru",
        });
    });
    var requests = Array();

    var filter = new Vue({
        el: '#filter',
        data: {
            parameter: {!! json_encode($filter_params) !!}[0],
            param_value: '',
            filter_items: [],
            filter_values: [],
            parameter_options: {!! json_encode($filter_params) !!},
            //material_search
            categories: {!! $categories->filter(function ($cat) { return $cat->references()->count();}) !!},
            category_id: '',
            need_attributes: [],
            parameters: [],
            loading: false,
            materials: [],
            attrs_all: [],
            observer_key: 1,

            globalSearch: '',
            ignoreParams: false,
            slider_key: 1,
            searchOptions: [],
            etalon: null,
            etalonRangeTimeout: null,
            session_object_id: {!! json_encode(Session::get('object_id')) !!},
        },
        computed: {
            hasRealParams() {
                return this.etalon && Object.values(this.etalon.attr_ids).filter(el => (el.min || el.min == 0) && (el.max || el.max == 0)).length > 0;
            }
        },
        watch: {
            globalSearch(val) {
                if (this.etalon && val !== this.etalon.result_name) {
                    this.etalon = null;
                    this.slider_key += 1;
                    bases.backdoor += 1;
                }
            }
        },
        mounted: function () {
            //init autocomplete
            var that = this;

            $.widget( "custom.catcomplete", $.ui.autocomplete, {
                _create: function() {
                    this._super();
                    this.widget().menu( "option", "items", "> :not(.ui-autocomplete-category)" );
                },
                _renderMenu: function( ul, items ) {
                    var that1 = this,
                    currentCategory = "";
                    $.each( items, function( index, item ) {
                        var li;
                        if ( item.type_name != currentCategory ) {
                            ul.append( "<li class='ui-autocomplete-category'>" + item.type_name + "</li>" );
                            currentCategory = item.type_name;
                        }
                        li = that1._renderItemData( ul, item );
                        if ( item.type_name ) {
                            li.attr( "aria-label", item.type_name + " : " + item.result_name );
                        }
                    });
                }
            });

            $( "#search" ).catcomplete({
                delay: 0,
                inLength: 0,
                autoFocus: true,
                source: (request, response) => {
                    this.promisedSearchGlobal(request.term)
                        .then(result => response(result));
                },
                select: (event, ui) => { that.globalFilter(ui.item) },
                close: function(){  if (that.searchOptions.length > 0 && that.globalSearch.length > 0) { $(this).blur(); } }
            });

            var parameter_ids = [{{Request::get('parameter_id')}}];
            var value_ids = '{{Request::get('value_ids')}}'.split(',');
            var date = '{{Request::get('date')}}';

            if (that.session_object_id) {
                $.each(that.session_object_id, function (index, item) {
                    if (value_ids[0] == '') {
                        value_ids = [];
                    }

                    if (parameter_ids[value_ids.indexOf(item.toString())] == -1 || parameter_ids[value_ids.indexOf(item)] == -1 || (parameter_ids[value_ids.indexOf(item.toString())] == undefined && parameter_ids[value_ids.indexOf(item)] == undefined)) {
                        parameter_ids.push(0);
                        value_ids.push(item);
                    }
                });
            }

            date = date === 'null' ? null : date;
            if (parameter_ids.length === value_ids.length && parameter_ids.length > 0) {
                parameter_ids.forEach(function(id, key) {
                    var value = '';
                    var parameter = '';
                    if (id === 0) {
                        parameter = 'Объект';
                        requests.push(axios.post('{{ route('building::mat_acc::report_card::get_objects') }}', {
                            'object_id': +value_ids[key],
                        }).then(response => {
                            value = response.data.find(item => item.code == +value_ids[key])['label'];
                            that.filter_items.push({
                                'value': value,
                                'parameter_text': parameter,
                                'parameter_id': id,
                                'value_id': +value_ids[key],
                            });
                        }));
                    } else if (id === 1) { //DO SMTH WITH THIS (CODE AND ID ARE NOT THE SAME)
                        parameter = 'Материал';
                        requests.push(axios.post('{{ route('building::mat_acc::report_card::get_materials', ['withTrashed' => true]) }}', {
                            'material_ids': [+value_ids[key]],
                        }).then(response => {
                            value = response.data.find(mat => mat.id == +value_ids[key])['label'];
                            that.filter_items.push({
                                'value': value,
                                'parameter_text': parameter,
                                'parameter_id': id,
                                'value_id': +value_ids[key],
                            });
                        }));
                    } else if (id === 3) {
                        const parsedFilter = JSON.parse(decodeURIComponent(atob(value_ids[key])));
                        that.filter_items.push(parsedFilter);
                    }
                });
            }

            var defer = $.when.apply($, requests);
            defer.done(function(){
                // This is executed only after every ajax request has been completed
                that.filter(false);
            });

            if (this.parameter.id == 0) {
                axios.post('{{ route('building::mat_acc::report_card::get_objects') }}').then(response => filter.filter_values = response.data)
            }
        },
        methods: {
            filter(range_param) {
                if (!range_param) {
                    if (!this.inArray(this.filter_items, {parameter_id: this.parameter.id, value: this.param_value.label})) {
                        if(this.param_value != '') {
                            this.filter_items.push({
                                parameter_id: this.parameter.id,
                                parameter_text: this.parameter.text,
                                value: this.param_value.label,
                                value_id: this.param_value.code
                            });
                        }
                        this.send_filter(this.filter_items);
                    }
                } else {
                    const ignoreParams = Object.entries(filter.$refs).filter(entry => entry[0].indexOf('select-') > -1
                        && entry[0].indexOf('etalon') > -1 && entry[1].length > 0)[0][1][0].ignoreParams;
                    this.filter_items.push({
                        parameter_id: 3,
                        parameter_text: this.need_attributes[0].value,
                        value: this.need_attributes.slice(1).map((el, i, a) => `${el.name}` + ((Number.isFinite(el.value[0]) && Number.isFinite(el.value[1]) && !ignoreParams) ? ` от ${el.value[0]} до ${el.value[1]}` : ' отсутствует')).join(', '),
                        value_id: {
                            // reference_name: this.need_attributes[0].value,
                            reference_id: this.need_attributes[0].etalon_id,
                            parameters: this.need_attributes.slice(1).map(el => ({
                                attr_id: el.attr_id,
                                value: {
                                    from: !ignoreParams ? el.value[0] : null,
                                    to: !ignoreParams ? el.value[1] : null,
                                }
                            })),
                        }
                    });
                    this.send_filter(this.filter_items);
                }
            },
            globalFilter(item) {
                this.ignoreParams = false;
                if (item.type_id != 3) {
                    if (!this.inArray(this.filter_items, {parameter_id: item.type_id, value: item.result_name})) {
                        this.filter_items.push({
                            parameter_id: item.type_id,
                            parameter_text: item.type_name.slice(0, item.type_name.length - 1),
                            value: item.result_name,
                            value_id: item.result_id
                        });
                        this.send_filter(this.filter_items);
                    }
                    this.globalSearch = '';
                    this.etalon = null;
                    this.slider_key += 1;
                    $("#search").catcomplete("search", "");
                } else {
                    this.globalSearch = item.result_name;
                    this.etalon = item;
                    this.slider_key += 1;
                    bases.backdoor += 1;
                    const etalonFilterItems = this.filter_items.filter(el => el.parameter_id === item.type_id && el.value_id.reference_id === item.result_id);
                    if (etalonFilterItems.length === 0) {
                        this.filter_items.push({
                            parameter_id: item.type_id,
                            parameter_text: item.result_name,
                            value: Object.values(item.attr_ids).map((el, i, a) => `${el.name}` + ((Number.isFinite(el.range[0]) && Number.isFinite(el.range[1]) && !this.ignoreParams) ? ` от ${el.range[0]} до ${el.range[1]}` : ' отсутствует')).join(', '),
                            value_id: {
                                reference_id: item.result_id,
                                parameters: Object.entries(item.attr_ids).map(entry => ({
                                    attr_id: entry[0],
                                    value: {
                                        from: !this.ignoreParams ? entry[1].range[0] : null,
                                        to: !this.ignoreParams ? entry[1].range[1] : null,
                                    }
                                })),
                            }
                        });
                        this.send_filter(this.filter_items);
                    } else if (etalonFilterItems[0].value_id.parameters && etalonFilterItems[0].value_id.parameters.length > 0) {
                        this.ignoreParams = etalonFilterItems[0].value.indexOf('отсутствует') !== -1;
                        etalonFilterItems[0].value_id.parameters.forEach((parameter) => {
                            this.etalon.attr_ids[parameter.attr_id].range = this.ignoreParams ?
                                [this.etalon.attr_ids[parameter.attr_id].min, this.etalon.attr_ids[parameter.attr_id].max] : [parameter.value.from, parameter.value.to];
                        });
                    }
                }
            },
            onChangeEtalonRange() {
                this.slider_key += 1;
                clearTimeout(this.etalonRangeTimeout);
                this.etalonRangeTimeout = setTimeout(() => {
                    const etalonFilterItems = this.filter_items.filter(el => el.parameter_id === this.etalon.type_id && el.value_id.reference_id === this.etalon.result_id);
                    if (etalonFilterItems.length > 0) {
                        etalonFilterItems[0].value = Object.values(this.etalon.attr_ids).map((el, i, a) => `${el.name}` + ((Number.isFinite(el.range[0]) && Number.isFinite(el.range[1]) && !this.ignoreParams) ? ` от ${el.range[0]} до ${el.range[1]}` : ' отсутствует')).join(', ');
                        etalonFilterItems[0].value_id = {
                            reference_id: this.etalon.result_id,
                            parameters: Object.entries(this.etalon.attr_ids).map(entry => ({
                                attr_id: entry[0],
                                value: {
                                    from: !this.ignoreParams ? entry[1].range[0] : null,
                                    to: !this.ignoreParams ? entry[1].range[1] : null,
                                }
                            })),
                        };
                        this.send_filter(this.filter_items);
                    } else {
                        this.filter_items.push({
                            parameter_id: this.etalon.type_id,
                            parameter_text: this.etalon.result_name,
                            value: Object.values(this.etalon.attr_ids).map((el, i, a) => `${el.name}` + ((Number.isFinite(el.range[0]) && Number.isFinite(el.range[1]) && !this.ignoreParams) ? ` от ${el.range[0]} до ${el.range[1]}` : ' отсутствует')).join(', '),
                            value_id: {
                                reference_id: this.etalon.result_id,
                                parameters: Object.entries(this.etalon.attr_ids).map(entry => ({
                                    attr_id: entry[0],
                                    value: {
                                        from: !this.ignoreParams ? entry[1].range[0] : null,
                                        to: !this.ignoreParams ? entry[1].range[1] : null,
                                    }
                                })),
                            }
                        });
                        this.send_filter(this.filter_items);
                    }
                }, 500);
            },
            clearFilters() {
                this.filter_items = [];
                this.send_filter(this.filter_items);
            },
            delete_budge: function (index) {
                if (this.filter_items[index].parameter_id === 0) {
                    axios.post('{{ route('building::mat_acc::delete_object_from_session') }}', {object_id: this.filter_items[index].value_id});
                }
                if (this.filter_items[index].parameter_id === 3) {
                    this.globalSearch = '';
                    this.etalon = null;
                    this.slider_key += 1;
                    $("#search").catcomplete("search", "");
                }
                this.filter_items.splice(index, 1);
                this.send_filter(this.filter_items);
            },
            send_filter: function (filter) {
                var that = this;
                var date = '{{Request::get('date')}}';
                date = date === 'null' ? null : date;
                var is_free = '{{Request::get('is_free')}}';
                var is_using = '{{Request::get('is_using')}}';
                if(bases) {
                    date = bases.search_date;
                    is_free = bases.is_free;
                    is_using = bases.is_using;
                }
                axios.post('{{ route('building::mat_acc::report_card::filter_base') }}', {
                    filter: filter,
                    date: date,
                    is_free: is_free,
                    is_using: is_using
                }).then(function (response) {
                    eventHub.$emit('addEvent', '');
                    that.need_attributes.map(el => el.value = '');
                    that.observer_key += 1;
                    that.$nextTick(() => {
                        if (that.$refs.observer) {
                            that.$refs.observer.reset();
                        }
                    });
                    window.history.pushState("", "", "?" + that.compactFilters());
                    bases.bases = response.data['result'];
                })
            },
            compactFilters: () => {
                var filter_url = '';
                var filter_params = [];
                var filter_values = [];
                filter.filter_items.forEach(filter_item => {
                    if (typeof filter_item['value_id'] !== 'object') {
                        filter_params.push(filter_item['parameter_id']);
                        filter_values.push(filter_item['value_id']);
                    } else {
                        filter_params.push(filter_item['parameter_id']);
                        filter_values.push(btoa(encodeURIComponent(JSON.stringify(filter_item))));
                    }
                });
                filter_url = 'parameter_id=' + filter_params.toString() + '&value_ids=' + filter_values.toString() + '&date=' + bases.search_date;
                return filter_url;
            },
            changeFilterValues: function () {
                filter.filter_values = [];

                if (filter.parameter.id === 0) {
                    axios.post('{{ route('building::mat_acc::report_card::get_objects') }}').then(response => filter.filter_values = response.data)
                } else if (filter.parameter.id === 1) {
                    axios.post('{{ route('building::mat_acc::report_card::get_materials', ['withTrashed' => true]) }}').then(response => filter.filter_values = response.data)
                } else if (filter.parameter.id === 2) {
                    filter.filter_values = [];
                }
            },
            search(query) {
                if (query !== '') {
                    if (filter.parameter.id == 0) {
                      setTimeout(() => {
                            axios.post('{{ route('building::mat_acc::report_card::get_objects') }}', {q: query}).then(function (response) {
                                filter.filter_values = response.data;
                            })
                        }, 100);
                    }
                    if (filter.parameter.id == 1) {
                      setTimeout(() => {
                        axios.post('{{ route('building::mat_acc::report_card::get_materials', ['withTrashed' => true]) }}', {q: query}).then(function (response) {
                            filter.filter_values = response.data;
                        })
                        }, 100);
                    }
                    else {
                      filter.filter_values = [];
                    }
                }
            },
            promisedSearchGlobal(query) {
                return new Promise((resolve, reject) => {
                    if (query) {
                        axios.post('{{ route('building::mat_acc::report_card::get_search_values') }}', {
                                search: query,
                            })
                            .then(response => {
                                response.data.data = response.data.data.map(el => {
                                    el.label = el.result_name;
                                    if (el.type_id == 3) {
                                        Object.values(el.attr_ids).forEach(attr => {
                                            attr.min = parseInt(attr.min, 10);
                                            attr.max = parseInt(attr.max, 10);
                                            attr.min = !Number.isNaN(attr.min) ? attr.min : (!Number.isNaN(attr.max) ? attr.max : null);
                                            attr.max = !Number.isNaN(attr.max) ? attr.max : (!Number.isNaN(attr.min) ? attr.min : null);
                                            attr.range = [attr.min, attr.max];
                                            attr.marks = {};
                                            attr.marks[attr.min] = String(attr.min);
                                            attr.marks[attr.max] = String(attr.max);
                                        });
                                    }
                                    return el;
                                });
                                this.searchOptions = response.data.data;
                                resolve(response.data.data);
                            })
                            .catch(error => {
                                console.log(error);
                                reject(error);
                            });
                    } else {
                        axios.post('{{ route('building::mat_acc::report_card::get_search_values') }}')
                            .then(response => {
                                response.data.data = response.data.data.map(el => {
                                    el.label = el.result_name;
                                    if (el.type_id == 3) {
                                        Object.values(el.attr_ids).forEach(attr => {
                                            attr.min = parseInt(attr.min, 10);
                                            attr.max = parseInt(attr.max, 10);
                                            attr.min = !Number.isNaN(attr.min) ? attr.min : (!Number.isNaN(attr.max) ? attr.max : null);
                                            attr.max = !Number.isNaN(attr.max) ? attr.max : (!Number.isNaN(attr.min) ? attr.min : null);
                                            attr.range = [attr.min, attr.max];
                                            attr.marks = {};
                                            attr.marks[attr.min] = String(attr.min);
                                            attr.marks[attr.max] = String(attr.max);
                                        });
                                    }
                                    return el;
                                });
                                this.searchOptions = response.data.data;
                                resolve(response.data.data);
                            })
                            .catch(error => {
                                console.log(error);
                                reject(error);
                            });
                    }
                })
            },
            inArray: function(array, element) {
                var length = array.length;
                for(var i = 0; i < length; i++) {
                    if(array[i].parameter_id == element.parameter_id && array[i].value == element.value) return true;
                }
                return false;
            },
            getNeedAttributes() {
                let that = this;
                that.need_attributes = [];
                axios.post('{{ route('building::materials::category::get_need_attrs') }}', { category_id: that.category_id }).then(function (response) {
                    that.attrs_all = response.data;
                    that.attrs_all = that.attrs_all.reverse();

                    that.attrs_all.forEach(function(attribute) {
                        that.need_attributes.push({
                            id: attribute.id,
                            attr_id: attribute.id,
                            category_id: attribute.category_id,
                            name: attribute.name,
                            unit: attribute.unit,
                            value: '',
                            etalon_id: '',
                            is_required: attribute.is_required,
                            from: attribute.from,
                            to: attribute.to,
                            step: attribute.step,
                        });
                    });
                });
            },
            createMaterial() {
                let that = this;

                this.$refs.observer.validate().then(success => {
                    if (!success) {
                        return;
                    }
                    this.filter(true);
                });
            },
            handleError(error) {
                let message = '';
                let errors = error.response.data.errors;
                for (let key in errors) {
                    message += errors[key][0] + '<br>';
                }

                swal({
                    type: 'error',
                    title: "Ошибка",
                    html: message,
                });
            },
            inArray_attribute: function(array, element) {
                var length = array.length;
                for(var i = 0; i < length; i++) {
                    if(array[i].id == element.id) return true;
                }
                return false;
            },
        }
    });

    function infoTemplate() {
        return `<button
                    type="button"
                    class="btn btn-link btn-primary btn-xs pd-0 custom-tooltip"
                    data-toggle="tooltip"
                    data-placement="top"
                    title="Не все материалы поддерживают данную единицу измерения, они не были включены в это число."
            >
                <i class="fa fa-info-circle"></i>
            </button>`;
    }

    Vue.component('material-attribute', {
            template: '\
        <div style="padding-left:15px; padding-right: 15px" class="mt-4" v-if="isEtalon || parameters.length > 0">\
            <template v-if="isEtalon">\
                <validation-provider :rules="attribute_is_required ? `required` : ``" :vid="\'select-\' + attribute_id"\
                                                                            :ref="\'select-\' + attribute_id" v-slot="v">\
                    <label class="d-block">@{{ attribute_name + (attribute_unit ? (comma + attribute_unit) : empty) }}<span v-if="attribute_is_required" class="star">*</span></label>\
                    <el-select\
                    v-model="default_parameter"\
                    @change="onChange"\
                    :allow-create="attribute_name.toLowerCase() !== \'эталон\'"\
                    filterable\
                    clearable\
                    :class="v.classes"\
                    :id="\'select-\' + attribute_id"\
                    :ref="\'select-\' + attribute_id + \'-inner\'"\
                    remote\
                    :remote-method="search"\
                    :loading="loading"\
                    @keydown.native.enter="keyHandler"\
                    placeholder="">\
                    <el-option\
                        v-for="item in mixedParameters"\
                        :label="item.name"\
                        :value="item.name">\
                    </el-option>\
                    </el-select>\
                    <div class="error-message" style="padding-left: 15px;">@{{ v.errors[0] }}</div>\
                </validation-provider>\
                <div v-if="hasRealParams" class="mt-2">\
                    <el-checkbox v-model="ignoreParams" label="Без параметров" border></el-checkbox>\
                </div>\
            </template>\
            <template v-else>\
                <div class="row">\
                    <div class="col-12">\
                        <label class="d-block">@{{ attribute_name + (attribute_unit ? (comma + attribute_unit) : empty) }}<span v-if="attribute_is_required" class="star">*</span></label>\
                        <el-slider v-model="default_parameter"\
                                range\
                                :id="\'select-\' + attribute_id"\
                                :key="slider_key"\
                                :disabled="ignoreParams"\
                                style="width: 100%"\
                                @change="onChange"\
                                :min="minValue"\
                                :max="maxValue"\
                                :step="1"\
                                :marks="marks"\
                        ></el-slider>\
                    </div>\
                    <div class="col-12 text-center mt-3">\
                        <label class="d-block">От<span class="star">*</span></label>\
                        <el-input-number\
                                    @change="onChangeManual"\
                                    v-model="default_parameter[0]"\
                                    :precision="0"\
                                    :disabled="ignoreParams"\
                                    :key="\'f\' + slider_key"\
                                    :step="1"\
                                    :min="minValue"\
                                    :max="default_parameter[1]"\
                                    placeholder="От"\
                                    required\
                        ></el-input-number>\
                    </div>\
                    <div class="col-12 text-center mt-3" style="padding: 6px;">\
                        <label class="d-block">До<span class="star">*</span></label>\
                        <el-input-number\
                                    @change="onChangeManual"\
                                    v-model="default_parameter[1]"\
                                    :disabled="ignoreParams"\
                                    :precision="0"\
                                    :key="\'t\' + slider_key"\
                                    :step="1"\
                                    :min="default_parameter[0]"\
                                    :max="maxValue"\
                                    placeholder="До"\
                                    required\
                        ></el-input-number>\
                    </div>\
                </div>\
            </template>\
         </div>\
        ',
            props: ['index', 'attribute_id', 'attribute_unit', 'attribute_name', 'attribute_value', 'attribute_etalon_id', 'attribute_is_required', 'category_id'],
            created() {
                eventHub.$on('addEvent', (e) => {
                    this.default_parameter = '';
                });
            },
            mounted() {
                const that = this;

                if (this.isEtalon) {
                    axios.post('{{ route('building::materials::category::get_need_attrs_values') }}', {attribute_id: this.attribute_id, category_id: this.category_id}).then(function (response) {
                        that.parameters = [];
                        that.parameters = response.data;
                    });
                    this.updateHasRealParams();
                }
            },
            watch: {
                ignoreParams(val) {
                    if (this.isEtalon) {
                        Object.entries(filter.$refs).forEach(entry => {
                            if (entry[0].indexOf('select-') > -1 && entry[0].indexOf('etalon') === -1 && entry[0].indexOf('category') === -1) {
                                if (entry[1].length > 0) {
                                    entry[1][0].ignoreParams = val;
                                }
                            }
                        });
                    }
                }
            },
            methods: {
                onChange(value) {
                    this.$emit('update:attribute_value', value);
                    this.default_parameter = value;
                    if (this.isEtalon) {
                        this.ignoreParams = false;
                        if (this.mixedParameters.filter(el => el.name === value).length > 0) {
                            this.$emit('update:attribute_etalon_id', this.mixedParameters.filter(el => el.name === value)[0].id);
                        } else {
                            this.$emit('update:attribute_etalon_id', null);
                        }
                        Object.entries(filter.$refs).forEach(entry => {
                            if (entry[0].indexOf('select-') > -1 && entry[0].indexOf('etalon') === -1 && entry[0].indexOf('category') === -1) {
                                if (entry[1].length > 0) {
                                    entry[1][0].searchRange();
                                }
                            }
                        });
                    }
                },
                onChangeManual() {
                    if (this.default_parameter[0] === undefined) {
                        this.default_parameter[0] = this.minValue;
                    }
                    if (this.default_parameter[1] === undefined) {
                        this.default_parameter[1] = this.maxValue;
                    }
                    this.onChange(this.default_parameter);
                    this.slider_key += 1;
                },
                keyHandler() {
                    materials_create.createMaterial();
                },
                updateHasRealParams() {
                    this.hasRealParams = Object.entries(filter.$refs).filter(entry => entry[0].indexOf('select-') > -1
                        && entry[0].indexOf('etalon') === -1 && entry[0].indexOf('category') === -1 && entry[1].length > 0
                        && entry[1][0].parameters.length > 0).length > 0;
                    setTimeout(() => {this.updateHasRealParams()}, 500);
                },
                searchRange() {
                    const that = this;
                    if (filter.need_attributes[0].value) {
                        axios.post('{{ route('building::materials::select_attr_value') }}', {reference_id: filter.need_attributes[0].etalon_id, attr_id: this.attribute_id}).then(function (response) {
                            that.parameters = [];
                            that.parameters = Object.values(response.data).map(el => parseInt(el, 10)).filter(el => !Number.isNaN(el));
                            that.onChange([that.minValue, that.maxValue]);
                        });
                    }
                },
                search(query) {
                    const that = this;

                    clearTimeout(this.searchTimeout);
                    this.searchTimeout = setTimeout(() => {
                        if (query !== '') {
                            axios.post('{{ route('building::materials::category::get_need_attrs_values') }}', {attribute_id: this.attribute_id, q: query, category_id: this.category_id }).then(function (response) {
                                that.parameters = response.data;
                            })
                        } else {
                            axios.post('{{ route('building::materials::category::get_need_attrs_values') }}', {attribute_id: this.attribute_id, category_id: this.category_id }).then(function (response) {
                                that.parameters = response.data;
                            })
                        }
                    }, 350);
                }
            },
            data: function () {
                return {
                    searchTimeout: null,
                    default_parameter: '',
                    ignoreParams: false,
                    hasRealParams: false,
                    slider_key: 1,
                    comma: ', ',
                    empty: '',
                    parameters: [],
                    loading: false,
                }
            },
            computed: {
                mixedParameters() {
                    return this.$refs.hasOwnProperty('select')
                    && this.attribute_value
                    && Array.isArray(this.parameters)
                        ? this.parameters.concat([this.attribute_value]) : this.parameters;
                },
                isEtalon() {
                    return this.attribute_name.toLowerCase() === 'эталон';
                },
                minValue() {
                    return Math.min(...this.parameters.map(el => parseInt(el, 10)));
                },
                maxValue() {
                    return Math.max(...this.parameters.map(el => parseInt(el, 10)));
                },
                marks() {
                    const marks = {};
                    this.parameters.forEach(el => marks[el] = [this.minValue, this.maxValue].indexOf(el) > -1 ? String(el) : '');
                    return marks;
                },
            },
            $_veeValidate: {
                value () {
                    return this.default_parameter;
                }
            },
        });

    var eventHub = new Vue();

    var bases = new Vue({
        el: '#bases',
        data: {
            bases: {!! $bases !!},
            search_date : "{!!(Request::get('with_closed')?: \Carbon\Carbon::today()->format('d.m.Y')) !!}",
            is_free: true,
            is_using: false,
            check_all: false,
            any_checked: false,
            sum_partial: {},
            untilPageEnd: 10000,
            backdoor: 1,
            endDatePickerOptions: {
                firstDayOfWeek: 1,
                disabledDate: date => date > moment(),
            },
        },
        mounted: function() {
            var url_date = '{{Request::get('date')}}';
            url_date = url_date === 'null' ? null : url_date;

            if (url_date != '') {
                this.search_date = url_date;
            }
            if(requests.length > 0) {
                var defer = $.when.apply($, requests);
                defer.done(function(){
                    // This is executed only after every ajax request has been completed
                    filter.filter();
                    bases.untilPageEnd = document.body.scrollHeight - window.pageYOffset - document.body.clientHeight;
                });
            }

            $(window).on('scroll', function(){
                bases.untilPageEnd = document.body.scrollHeight - window.pageYOffset - document.body.clientHeight;
                $('.tooltip').remove();
            });

            this.recalculate(true);
        },
        updated: function() {
            this.recalculate(true);
        },
        watch: {
            search_date: function (val) {
                axios.post('{{ route('building::mat_acc::report_card::filter_base') }}', {date: bases.search_date, filter: filter.filter_items, is_free: bases.is_free, is_using: bases.is_using}).then(function (response) {
                    bases.bases = response.data['result'];
                    window.history.pushState("", "", "?" + filter.compactFilters());
                })
            },
            is_free: function (val) {
                axios.post('{{ route('building::mat_acc::report_card::filter_base') }}', {date: bases.search_date, filter: filter.filter_items, is_free: bases.is_free, is_using: bases.is_using}).then(function (response) {
                    window.history.pushState("", "", "?" + filter.compactFilters());
                    bases.bases = response.data['result'];
                })
            },
            is_using: function (val) {
                axios.post('{{ route('building::mat_acc::report_card::filter_base') }}', {date: bases.search_date, filter: filter.filter_items, is_free: bases.is_free, is_using: bases.is_using}).then(function (response) {
                    window.history.pushState("", "", "?" + filter.compactFilters());
                    bases.bases = response.data['result'];
                })
            },
            sum_partial: function (val) {
                $('#sum-partial').html(this.stringifyUnitTable(val));
                $(infoTemplate()).replaceAll('.hint-span');
                $(function () {
                    $('[data-toggle="tooltip"]').tooltip()
                })
            },
            check_all: function (val) {
                this.bases.forEach(base => base.checked = val);
                this.recalculate(false);
            }
        },
        methods: {
            // convert: function(e, mat) {
            //     var selected_param_id = $(e).val();
            //     var base_id = $(e).attr('id');
            //     if (selected_param_id === '0') {
            //         bases.bases[base_id].round_count = parseFloat(bases.bases[base_id].count).toFixed(3);
            //     } else {
            //         var material = bases.bases[base_id].material;
            //         var selected_param = material.convertation_parameters.filter(param => {
            //             return param.id == selected_param_id
            //         });
            //         bases.bases[base_id].round_count = parseFloat(bases.bases[base_id].count * selected_param[0].value).toFixed(3);
            //     }
            // },
            print_rep: function() {
                var data = ('{' + '"object_ids":[' + bases.bases.map(function(base) { return base.object_id}) +
                    '],"material_ids":[' + bases.bases.map(function(base) { return base.material.id}) +
                    '],"used":[' + bases.bases.map(function(base) { return base.used}) +
                    '],"material_unit":[' + bases.bases.map(function(base) { return '"' + base.unit + '"'}) +
                    '],"count":[' + bases.bases.map(function(base) { return base.count}) + '],' +
                    '"filter_params":[' + filter.filter_items.map(function(badge) { return badge.parameter_id}) +
                    '],"filter_values":[' + filter.filter_items.map(function(badge) { return JSON.stringify(badge.parameter_text + ": " + badge.value)}) + ']' +
                    ',"date":"' + bases.search_date +
                    '","is_using":"' + bases.is_using +
                    '","is_free":"' + bases.is_free + '"}'
                );

                // console.log(data);
                $('#print_data').val(data);


                $('#print_report').submit();

                // location.reload();
            },
            onFocus: function() {
                $('.el-input__inner').blur();
            },
            parseCustomFloat: function(str) {
                str = String(str);
                return +str.split(',').join('');
            },
            prettifyNumber: function(num) {
                const [wholePart, decimalPart] = num.toFixed(3).split('.');
                const wholePartDestructed = wholePart.split('');
                const prettyWholePartDestructed = [];

                for (let i = 0; i < wholePartDestructed.length; i++) {
                    prettyWholePartDestructed.unshift(wholePartDestructed[wholePartDestructed.length - 1 - i]);
                    if (i !== 0 && i % 3 === 2 && i !== wholePartDestructed.length - 1) {
                        prettyWholePartDestructed.unshift(',');
                    }
                }

                return `${prettyWholePartDestructed.join('')}.${decimalPart}`;
            },
            calculateConvertedAmount: function(base, coef) {
                return this.prettifyNumber(this.parseCustomFloat(base) * this.parseCustomFloat(coef));
            },
            stringifyUnitTable: function(table) {
                let output = '';
                Object.entries(table).forEach((entry, i, arr) => {
                    output += `${this.prettifyNumber(entry[1].value)}&nbsp;${entry[0]}&nbsp;${entry[1].unreliable ? '<span class="hint-span"></span>' : ''}${i === arr.length - 1 ? '' : ' / '}`;
                });
                return output ? output : '–';
            },
            recalculate: function(isTotal) {
                let sum = {};
                let checkedCount = 0;
                this.bases.forEach((base) => {
                    $('.mobile-hint').addClass('d-none');

                    const main_unit = base.unit;
                    let parameters = base.convert_params;

                    if (base.checked || isTotal) {

                        checkedCount += 1;

                        if (!sum[main_unit]) {
                            sum[main_unit] = {
                                counter: 0,
                                value: 0.0,
                                unreliable: false,
                            };
                        }
                        sum[main_unit].value += +base.round_count.split(',').join('');
                        sum[main_unit].counter += 1;

                        if (parameters && !Array.isArray(parameters)) {
                            parameters = Object.values(parameters);
                        }
                        if (parameters.length > 0) {
                            parameters.forEach((parameter) => {

                                if (!sum[parameter.unit]) {
                                    sum[parameter.unit] = {
                                        counter: 0,
                                        value: 0.0,
                                        unreliable: false,
                                    };
                                }
                                sum[parameter.unit].value += (+(String(parameter.value).split(',').join(''))) * (+(String(base.round_count).split(',').join('')));
                                sum[parameter.unit].counter += 1;
                            })
                        }
                    }
                });

                Object.values(sum).forEach((unit) => {
                    if (unit.counter < checkedCount || isTotal && unit.counter < this.bases.length) {
                        unit.unreliable = true;
                        $('.mobile-hint').removeClass('d-none');
                    }
                });

                if (isTotal) {
                    $('#sum-total').html(this.stringifyUnitTable(sum));
                    $(infoTemplate()).replaceAll('.hint-span');
                    $(function () {
                        $('[data-toggle="tooltip"]').tooltip()
                    });
                } else {
                    this.sum_partial = sum;
                }

                this.any_checked = this.bases.find(base => base.checked);
            },
            moveToNew(base) {
                $('body').click();
                toNew.base = base;
                toNew.base_id = base.id;
                toNew.base_count = base.count;
                $('#toNew').modal('show');
                $('.modal').css('overflow-y', 'auto');
                $('#toNew').focus();
            },
            moveToUsed(base) {
                $('body').click();
                toUsed.base = base;
                toUsed.base_id = base.id;
                toUsed.base_count = base.count;
                $('#toUsed').modal('show');
                $('.modal').css('overflow-y', 'auto');
                $('#toUsed').focus();
            }
        }
    })

    @if (config("app.env") == "local_dev")
        function manualTransfer() {
            swal({
                position: 'top-end',
                title: "Проверка",
                text: "Эта кнопка запускает команду php artisan mat_acc:transfer. Вы уверены?",
                type: 'info',
                showCancelButton: 'true',
                showConfirmButton: 'true',
            }).then((result) => {
                if (result.value) {
                    swal({
                        position: 'top-start',
                        title: "Проверка",
                        text: "Это системная команда, будьте осторожны. Вы точно уверены, что делаете?",
                        type: 'warning',
                        showCancelButton: 'true',
                        showConfirmButton: 'true',
                    }).then((result) => {
                        if (result.value) {
                            swal({
                                title: "Опасно",
                                text: "Последняя проверка, если вы не из команды разработки, свяжитесь, пожалуйста с tucki industrial.",
                                type: 'danger',
                                showCancelButton: 'true',
                                showConfirmButton: 'true',
                            }).then((result) => {
                                axios.get('{{ route('building::mat_acc::manual_transfer') }}').then((result) => {
                                    window.location.reload();
                                });
                            });
                        }
                    });
                }
            });
        }
    @endif

    @canany(['mat_acc_base_move_to_new', 'mat_acc_base_move_to_used'])
        Vue.component('validation-provider', VeeValidate.ValidationProvider);
        Vue.component('validation-observer', VeeValidate.ValidationObserver);
        @can('mat_acc_base_move_to_new')
            var toNew = new Vue ({
                el: '#toNew',
                data: {
                    base: '',
                    base_id: '',
                    base_count: '',
                    count: '',
                    loading_submit: false,
                    observer_key: 1
                },
                methods: {
                    submit() {
                        this.$refs.observer.validate().then(success => {
                            if (!success) {
                                const error_field_vid = Object.keys(this.$refs.observer.errors).find(el => this.$refs[el].errors.length > 0);
                                $('.modal').animate({
                                    scrollTop: $('#' + error_field_vid).offset().top
                                }, 1200);
                                $('#' + error_field_vid).focus();
                                return;
                            }

                            this.loading_submit = true;
                            axios.post('{{ route('building::mat_acc::move_to_new') }}', {
                                base_id: this.base_id,
                                count: this.count,
                            }).then(() => {
                                location.reload();
                            }).catch(() => {
                                location.reload();
                            });
                        });
                    }
                }
            });
        @endcan
        @can('mat_acc_base_move_to_used')
            var toUsed = new Vue ({
                el: '#toUsed',
                data: {
                    base: '',
                    base_id: '',
                    base_count: '',
                    count: '',
                    loading_submit: false,
                    observer_key: 2
                },
                methods: {
                    submit() {
                        this.$refs.observer.validate().then(success => {
                            if (!success) {
                                const error_field_vid = Object.keys(this.$refs.observer.errors).find(el => this.$refs[el].errors.length > 0);
                                $('.modal').animate({
                                    scrollTop: $('#' + error_field_vid).offset().top
                                }, 1200);
                                $('#' + error_field_vid).focus();
                                return;
                            }

                            this.loading_submit = true;
                            axios.post('{{ route('building::mat_acc::move_to_used') }}', {
                                base_id: this.base_id,
                                count: this.count,
                            }).then(() => {
                                location.reload();
                            }).catch(() => {
                                location.reload();
                            });
                        });
                    }
                }
            });
        @endcan
    @endcanany
</script>
@endsection
