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
    .el-mobile-table {
        font-size: 12px;
        line-height: 1;
        width: 235px;
    }

    .el-table__expanded-cell[class*=cell] {
        padding: 15px 5px;
    }

    .el-table .warning-row {
      background: oldlace;
    }

    .el-table .success-row {
      background: #f9fff5;
    }

    /* .el-table .work-row {
      background: #F0F8FF;
    } */

    ol, ul {
        padding-inline-start: 10px;
    }

    .el-mobile-table ol, .el-mobile-table ul {
        padding-inline-start: 25px;
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

    @media (min-width: 1480px) {
        .manual-etalon-range {
            margin-top: -7px !important;
        }
    }
</style>
@endsection

@section('content')
<div class="row">
    <div class="col-md-12">
        <div class="nav-container" style="margin:0 0 10px 15px">
            <ul class="nav nav-icons" role="tablist">
                <li class="nav-item ">
                    <a class="nav-link link-line " id="#" href="{{ route('building::mat_acc::report_card') }}">
                        Табель материального учёта
                    </a>
                </li>
                <li class="nav-item active">
                    <a class="nav-link link-line active-link-line" href="{{ route('building::mat_acc::operations') }}">
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
                <h6 style="margin-bottom:10px">Фильтрация</h6>
                <div class="row">
                    <div class="col-md-3" style="margin-top:10px">
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
                    <div class="col-md-5" style="margin-top:10px">
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
                    <div class="col-md-4 d-none d-md-inline-block" style="margin-top:10px">
                        <div class="left-edge">
                            <div class="page-container">
                                <button type="button" v-on:click="() => {parameter.text.toLowerCase() === 'эталон' ? createMaterial() : filter(false)}"
                                    class="btn btn-wd btn-info">Добавить</button>
                            </div>
                        </div>
                    </div>
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
                <div class="row" v-if="filter_items.length > 0">
                    <div class="col-md-12" style="margin: 10px 0 10px 0">
                        <h6>Выбранные фильтры</h6>
                    </div>
                </div>
                <template>
                    <div class="row" v-if="filter_items.length > 0">
                        <div class="col-md-9">
                            <div class="bootstrap-tagsinput">
                                <span class="badge badge-azure" v-on:click="delete_budge(index)" v-for="(item, index) in filter_items">@{{ item.parameter_text }}: @{{ item.value }}<span data-role="remove" class="badge-remove-link"></span></span>
                            </div>
                        </div>
                        <div class="col-md-3 text-right mnt-20--mobile text-center--mobile">
                            <button type="button" @click="clear_filters" class="btn btn-sm show-all">
                                Удалить фильтры
                            </button>
                        </div>
                    </div>
                </template>
            </div>
        </div>
        <div class="card strpied-tabled-with-hover" id="operations" v-cloak>
            <div class="fixed-table-toolbar toolbar-for-btn">
                <div class="row">
                    <div class="col-md-7" style="margin-top:5px;">
                        <template>
                            <el-input placeholder="Поиск" v-model="search_tf" clearable
                                      prefix-icon="el-icon-search" id="search-tf" @clear="doneTyping"
                                      class="d-inline-block"
                                      style="width: 200px; margin-bottom: 10px"
                            ></el-input>
                            <el-date-picker style="cursor:pointer; width: 200px !important; margin-bottom: 10px"
                                            v-model="search_date"
                                            format="dd.MM.yyyy"
                                            value-format="dd.MM.yyyy"
                                            type="date"
                                            placeholder="Выберите день"
                                            name="planned_date_from"
                                            :picker-options="{firstDayOfWeek: 1}"
                                            @focus = "onFocus"
                            >
                            </el-date-picker>
                            <el-checkbox v-model="with_closed" label="Закрытые" border v-on:change="updateResults" style="text-transform: none"></el-checkbox>
                        </template>
                    </div>
                    <div class="col-md-5 text-right mnt-20--mobile">
                        <!-- <button type="button" name="button" class="btn btn-wd btn-outline" style="margin-top:5px" data-toggle="modal" data-target="#co_export">
                            Экспорт из КП
                        </button> -->
                        <button type="button" data-toggle="modal" data-target="#operation_excel" class="btn btn-wd btn-outline" style="margin-top:5px;">
                            Отчет по материалам
                        </button>
                        <button type="button" v-on:click="print_rep()" name="button" class="btn btn-wd btn-outline" style="margin-top:5px;">
                            <i class="fa fa-print"></i>
                            Печать
                        </button>
                        @if(Gate::check('mat_acc_arrival_draft_create') || Gate::check('mat_acc_arrival_create')
                            || Gate::check('mat_acc_write_off_draft_create') || Gate::check('mat_acc_write_off_create')
                            || Gate::check('mat_acc_transformation_draft_create') || Gate::check('mat_acc_transformation_create')
                            || Gate::check('mat_acc_moving_draft_create') || Gate::check('mat_acc_moving_create'))
                        <div class="btn-group" role="group">
                            <button id="btnGroupDrop1" type="button" class="btn btn-secondary dropdown-toggle btn-success" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false" style="margin-bottom: 5px;">
                                Создать операцию
                            </button>
                            <div class="dropdown-menu" aria-labelledby="btnGroupDrop1">
                            @if(Gate::check('mat_acc_arrival_draft_create') || Gate::check('mat_acc_arrival_create'))
                              <a class="dropdown-item" href="{{ route('building::mat_acc::arrival::create') }}">Поступление</a>
                            @endif
                            @if(Gate::check('mat_acc_write_off_draft_create') || Gate::check('mat_acc_write_off_create'))
                              <a class="dropdown-item" href="{{ route('building::mat_acc::write_off::create') }}">Списание</a>
                            @endif
                            @if(Gate::check('mat_acc_transformation_draft_create') || Gate::check('mat_acc_transformation_create'))
                              <a class="dropdown-item" href="{{ route('building::mat_acc::transformation::create') }}">Преобразование</a>
                            @endif
                            @if(Gate::check('mat_acc_moving_draft_create') || Gate::check('mat_acc_moving_create'))
                              <a class="dropdown-item" href="{{ route('building::mat_acc::moving::create') }}">Перемещение</a>
                            @endif
                            </div>
                         </div>
                         @endif
                    </div>
                </div>
                <div class="row" v-if="search_queries.length > 0" style="margin-bottom:20px">
                    <div class="col-md-9">
                        <div class="bootstrap-tagsinput">
                                <span class="badge badge-azure" v-on:click="delete_search_budge(index)" v-for="(item, index) in search_queries">
                                    @{{ item }}<span data-role="remove" class="badge-remove-link"></span>
                                </span>
                        </div>
                    </div>
                    <div class="col-md-3 text-right mnt-20--mobile text-center--mobile">
                        <button type="button" @click="clear_search_queries" class="btn btn-sm show-all">
                            Удалить фильтры
                        </button>
                    </div>
                </div>
                <template>
                  <el-table
                      v-loading="loadingOperations"
                    :data="operations"
                    empty-text="Данных нет"
                    style="width: 100%"
                    :row-class-name="tableRowClassName"
                    :header-row-class-name="headerRowClassName"
                    @row-dblclick="link_to_operation"
                    >

                    <!-- for mobile phone -->
                    <el-table-column v-if="showMobile">
                      <template slot-scope="props">
                          <p class="el-mobile-table"><span class="table-stroke__label">Тип:</span> <span class="table-stroke__content">@{{ props.row.type_name }}</span></p>
                          <div class="el-mobile-table"><span class="table-stroke__label">Материалы:</span> <ul>
                                  <li  class="table-stroke__content" v-for="(material, i) in props.row.materials" {{--:style="i > 0 ? 'border-top: 1px solid #EBEEF5;' : ''"--}}>@{{ material.manual ? material.material_name : '' }}</li>
                              </ul></div>
                        <p class="el-mobile-table"><span class="table-stroke__label">Объекты:</span>
                          <ul>
                              <li v-if="props.row.object_id_from"><b>@{{ (props.row.object_id_to != props.row.object_id_from) ? 'С объекта: ' : 'На объекте: '}}</b>@{{ props.row.object_from_text ? props.row.object_from_text : '-' }}</li>
                              <li v-if="props.row.object_id_to && (props.row.object_id_to != props.row.object_id_from)"><b>На объект: </b>@{{ props.row.object_to_text ? props.row.object_to_text : '-' }}</li>
                          </ul>
                        </p>
                        <p class="el-mobile-table"><span class="table-stroke__label">Адрес:</span> <span class="table-stroke__content">@{{ props.row.address_text }}</span></p>
                        <p class="el-mobile-table"><span class="table-stroke__label">Автор:</span> <span class="table-stroke__content">@{{ props.row.author.full_name }}</span></p>
                        <p class="el-mobile-table"><span class="table-stroke__label">Дата создания:</span> <span class="table-stroke__content">@{{ props.row.created_date }}</span></p>
                        <p class="el-mobile-table"><span class="table-stroke__label">Дата начала:</span> <span class="table-stroke__content">@{{ props.row.actual_date_from }}</span></p>
                        <p class="el-mobile-table"><span class="table-stroke__label">Дата закрытия:</span> <span class="table-stroke__content">@{{ props.row.closed_date }}</span></p>
                        <p class="el-mobile-table"><span class="table-stroke__label">Статус:</span> <span class="table-stroke__content">@{{ props.row.status_name }}</span></p>
                          <p class="el-mobile-table text-right" style="margin-top: 20px">
                                  <a :href="props.row.url" @click="save_src">Перейти в операцию →</a>
                          </p>
                      </template>
                    </el-table-column>
                    <el-table-column
                      v-if="hideMobile"
                      prop="type_name"
                      label="Тип"
                      align="center"
                      min-width="45">
                        <template slot-scope="scope">
                            <el-tooltip :content="scope.row.type_name" placement="right" effect="light">
                                <i :class="getTypeIcon(scope.row.type_name)">
                                </i>
                            </el-tooltip>
                        </template>
                    </el-table-column>
                    <el-table-column
                      v-if="hideMobile"
                      prop="total_weigth"
                      label="Количество, т"
                      min-width="70">
                    </el-table-column>
                      <el-table-column
                          v-if="hideMobile"
                          prop="materials"
                          label="Материалы"
                          min-width="140">
                          <template slot-scope="scope">
                              <ul>
                                  <li v-for="(material, i) in scope.row.materials.filter((v, i, a) => a.map(el => el.material_name).indexOf(v.material_name) === i)" {{--:style="i > 0 ? 'border-top: 1px solid #EBEEF5;' : ''"--}}>
                                      @{{ material.manual ? material.material_name : '' }}
                                  </li>
                              </ul>
                          </template>
                      </el-table-column>
                    <el-table-column
                      v-if="hideMobile"
                      label="Объекты"
                      prop="this"
                      min-width="110">
                        <template slot-scope="scope">
                            <ul>
                                <li v-if="scope.row.object_id_from"><b>@{{ (scope.row.object_id_to != scope.row.object_id_from) ? 'С объекта: ' : 'На объекте: '}}</b>@{{ scope.row.object_from_text ? scope.row.object_from_text : '-' }}</li>
                                <li v-if="scope.row.object_id_to && (scope.row.object_id_to != scope.row.object_id_from)"><b>На объект: </b>@{{ scope.row.object_to_text ? scope.row.object_to_text : '-' }}</li>
                            </ul>
                        </template>
                    </el-table-column>
                    <el-table-column
                      v-if="hideMobile"
                      prop="author.full_name"
                      label="Автор"
                      min-width="80">
                    </el-table-column>
                    <el-table-column
                      v-if="hideMobile"
                      prop="created_date"
                      label="Дата создания"
                      min-width="80">
                    </el-table-column>
                    <el-table-column
                      v-if="hideMobile"
                      prop="actual_date_from"
                      label="Дата начала"
                      min-width="80">
                    </el-table-column>
                    <el-table-column
                      v-if="hideMobile"
                      prop="closed_date"
                      label="Дата закрытия"
                      min-width="80">
                    </el-table-column>
                    <el-table-column
                      v-if="hideMobile"
                      prop="status_name"
                      label="Статус"
                      min-width="130">
                    </el-table-column>
                  </el-table>
                    <div class="spinner-border text-primary" role="status">
                        <span class="sr-only">Loading...</span>
                    </div>
                </template>

                <!-- <div class="table-responsive">
                    <table class="table table-hover mobile-table">
                        <thead>
                            <tr>
                                <th class="sort">Тип</th>
                                <th class="sort">Объект</th>
                                <th class="sort">Адрес</th>
                                <th class="sort">Автор</th>
                                <th class="text-center sort">Дата проведения</th>
                                <th class="text-center sort">Дата закрытия</th>
                                <th class="sort">Статус</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr class="href" v-for="operation in operations" @dblclick="link_to_operation(operation)">
                                <td  data-label="Тип">@{{ operation.type_name }}</td>
                                <td  data-label="Объект">@{{ operation.object_to.name }}</td>
                                <td data-label="Адрес">@{{ operation.object_to.address }}</td>
                                <td data-label="Автор">@{{ operation.author.full_name }}</td></td>
                                <td data-label="Дата проведения" class="text-center">@{{ operation.created_at }}</td>
                                <td data-label="Дата закрытия" class="text-center">@{{ operation.actual_date_to }}</td>
                                <td data-label="Статус">@{{ operation.status_name }}</td>
                            </tr>
                            <tr>
                                <td class="actions-dropdown actions-dropdown-device">
                                    <a href="#" data-toggle="dropdown" class="btn dropdown-toggle nav-link btn-link btn-xs btn-space" data-original-title="Запланировать операцию">
                                        <i class="fa fa-bars"></i>
                                    </a>
                                </td>
                                <td data-label="Объект">База</td>
                                <td data-label="Адрес">Большой сампсониевский 28</td>
                                <td data-label="Материал">Шпунт Л5УМ 12 метров</td>
                                <td data-label="Ед. измерения" class="text-center">т.</td>
                                <td data-label="Количество" class="text-center">500</td>
                                <td class="operations-dropdown actions-dropdown-desk text-right">
                                    <a href="#" class="btn dropdown-toggle btn-link btn-xs btn-space show-operations" data-original-title="Запланировать операцию">
                                        <i class="fa fa-bars"></i>
                                    </a>
                                    <ul class="operations-menu">
                                        <a class="operations-item" href="#"><li class="firt-item">Поступление</li></a>
                                        <a class="operations-item" href="#"><li>Списание</li></a>
                                        <a class="operations-item" href="#"><li>Преобразование</li></a>
                                        <a class="operations-item" href="#"><li>Перемещение</li></a>
                                        <a class="operations-item" href="#"><li class="last-item">Использование</li></a>
                                    </ul>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div> -->
            </div>
        </div>
    </div>
</div>

<form id="print_operations" target="_blank" method="post" multisumit="true" action="{{route('building::mat_acc::operations::print')}}">
    @csrf
    <input id="print_data" type="hidden" name="results">
</form>
<!-- экспорт кп -->

<div class="modal fade bd-example-modal-lg show" id="operation_excel" role="dialog" aria-labelledby="modal-search" style="display: none;">
    <div class="modal-dialog modal-lg" role="document">
       <div class="modal-content">
           <div class="modal-header">
               <h5 class="modal-title">Экспорт операций в Excel</h5>
               <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                   <span aria-hidden="true">×</span>
               </button>
           </div>
           <div class="modal-body">
               <hr style="margin-top:0">
               <div class="card border-0" >
                   <div class="card-body ">
                       <form id="formExportToExcel" multisumit="true" target="_blank" method="post" action="{{ route('building::mat_acc::export_object_actions') }}" class="form-horizontal">
                           @csrf
                           <input type="hidden" v-model="object_id.code" name="object_id">
                           <div class="row">
                               <div class="col-md-12">
                                   <div class="form-group" style="margin-bottom: 0">
                                       <div class="row">
                                           <div class="col-md-4">
                                               <label>Объект</label>
                                           </div>
                                           <div class="col-md-8">
                                               <template>
                                                   <el-select v-model="object_id" value-key="label" clearable filterable :remote-method="search" required remote placeholder="Поиск">
                                                       <el-option
                                                           v-for="item in objects"
                                                           :key="item.code"
                                                           :label="item.label"
                                                           :value="item">
                                                       </el-option>
                                                   </el-select>
                                                   <p class="text-muted" style="font-size: 12px;">
                                                       Если в получившимся отчете в ячейке "Наименование объекта" отображается слишком длинное название - вы можете заполнить "сокращенное наименование" у объекта в системе, чтобы отображалось то, что вам нужно.
                                                   </p>
                                               </template>
                                           </div>
                                       </div>
                                    </div>
                               </div>
                           </div>
                           <div class="row">
                               <div class="col-md-4">
                                   <label>Диапазон дат</label>
                               </div>
                               <div class="col-md-8">
                                       <el-date-picker
                                           style="cursor:pointer"
                                           v-model="start_date"
                                           class="mb-3"
                                           format="dd.MM.yyyy"
                                           name="start_date"
                                           value-format="dd.MM.yyyy"
                                           type="date"
                                           placeholder="Дата с"
                                           :picker-options="{firstDayOfWeek: 1}"
                                           @focus = "onFocus">
                                       </el-date-picker>

                                       <el-date-picker
                                           style="cursor:pointer"
                                           v-model="end_date"
                                           format="dd.MM.yyyy"
                                           name="end_date"
                                           value-format="dd.MM.yyyy"
                                           type="date"
                                           placeholder="Дата по"
                                           :picker-options="{firstDayOfWeek: 1}"
                                           @focus = "onFocus">
                                       </el-date-picker>
                               </div>
                           </div>
                       </form>
                   </div>
               </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Закрыть</button>
                <button type="button" @click="checkObject" class="btn btn-info btn-outline">Подтвердить</button>
           </div>
        </div>
    </div>
</div>

@endsection

@section('js_footer')
<script src="{{ mix('js/plugins/jquery-ui.min.js') }}"></script>
<script type="text/javascript">
    Vue.component('validation-provider', VeeValidate.ValidationProvider);
    Vue.component('validation-observer', VeeValidate.ValidationObserver);

    $( document ).ready(function() {
            $('.el-select').click(function (){
                input = $(this).find('.el-input__inner');
                input.focus();
                setTimeout( function(){
                    input[0].setSelectionRange(0, 9999);
                    input.focus();
                }, 1)
            });

            $('.el-select').on('touchstart', function(){
                input = $(this).find('.el-input__inner');
                input.focus();
                setTimeout( function(){
                    input[0].setSelectionRange(0, 9999);
                    input.focus();
                }, 1)
            });

            $('.el-select').on('touchend', function(){
                input = $(this).find('.el-input__inner');
                input.focus();
                setTimeout( function(){
                    input[0].setSelectionRange(0, 9999);
                    input.focus();
                }, 1)
            });
    });
</script>
<script type="text/javascript">
    $( document ).ready(function() {
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
    });

    $(document).ready(function() {
        $('.js-example-basic-single').select2({
            language: "ru",
        });

        operations.loadingOperations = true;
        axios.post('{{ route('building::mat_acc::report_card::filter') }}', {
            date: operations.search_date,
            search: operations.search_queries_compact,
            filter: filter.filter_items,
            with_closed: operations.with_closed
        }).then(function (response) {
            window.history.pushState("", "", "?" + filter.compactFilters());
            operations.operations = response.data['result'];
            operations.loadingOperations = false;
        }).catch(function() {
            operations.loadingOperations = false;
        })
    });

</script>

<script type="text/javascript">
$( document ).ready(function() {
    $('.show-operations').click(function(){
        $(this).closest('.operations').show();
    });
});
</script>

<script type="text/javascript">
    function remove_tag(e){
        $(e).closest('.material-item').remove();
    };
</script>

<script>
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
        categories: {!! $categories !!},
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
        categoryPriority: [4, 3, 2, 0, 5, 1],
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
                operations.backdoor += 1;
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

        var date = '{{Request::get('date')}}';
        date = date === 'null' ? null : date;
        if (parameter_ids.length === value_ids.length) {
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
                        value = response.data.find(item => item.code == +value_ids[key])['label'];
                        that.filter_items.push({
                            'value': value,
                            'parameter_text': parameter,
                            'parameter_id': id,
                            'value_id': +value_ids[key],
                        });
                    }));
                } else if (id === 2) {
                    parameter = 'Автор';
                    requests.push(axios.post('{{ route('building::mat_acc::get_users') }}', {
                        'author_id': +value_ids[key],
                    }).then(response => {
                        value = response.data.find(item => item.code == +value_ids[key])['label'];
                        that.filter_items.push({
                            'value': value,
                            'parameter_text': parameter,
                            'parameter_id': id,
                            'value_id': +value_ids[key],
                        });
                    }));
                } else if (id === 3) {//status
                    parameter = 'Статус';
                    requests.push(axios.post('{{ route('building::mat_acc::get_statuses') }}', {
                        'status_id': +value_ids[key],
                    }).then(response => {
                        value = response.data[0];
                        that.filter_items.push({
                            'value': response.data[0]['label'],
                            'parameter_text': parameter,
                            'parameter_id': id,
                            'value_id': +value_ids[key],
                        });
                    }));
                } else if (id === 4) {//type
                    parameter = 'Тип';
                    requests.push(axios.post('{{ route('building::mat_acc::get_types') }}', {
                        'type_id': +value_ids[key],
                    }).then(response => {
                        value = response.data[0];
                        that.filter_items.push({
                            'value': response.data[0]['label'],
                            'parameter_text': parameter,
                            'parameter_id': id,
                            'value_id': +value_ids[key],
                        });
                    }));
                } else if (id === 5) {
                    const parsedFilter = JSON.parse(decodeURIComponent(atob(value_ids[key])));
                    that.filter_items.push(parsedFilter);
                }
            });
        }
        if(requests.length > 0) {
            var defer = $.when.apply($, requests);
            defer.done(function () {
                filter.filter(false);
            });
        }
        if (this.parameter.id == 0) {
            axios.post('{{ route('building::mat_acc::report_card::get_objects') }}').then(response => filter.filter_values = response.data)
        }
        $('a[href$="mat_acc/operations"]').each(function(){
            document.cookie = encodeURIComponent('opsource') + '=' + encodeURIComponent("{{ route('building::mat_acc::operations') }}");
            $(this).attr("href", "{{ route('building::mat_acc::operations') }}");
        });
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
                        this.send_filter(filter);
                    }
                } else {
                    const ignoreParams = Object.entries(filter.$refs).filter(entry => entry[0].indexOf('select-') > -1
                        && entry[0].indexOf('etalon') > -1 && entry[1].length > 0)[0][1][0].ignoreParams;
                    this.filter_items.push({
                        parameter_id: 5,
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
                this.send_filter(filter);
            }
        },
        globalFilter(item) {
            this.ignoreParams = false;
            if (item.type_id != 5) {
                if (!this.inArray(this.filter_items, {parameter_id: item.type_id, value: item.result_name})) {
                    this.filter_items.push({
                        parameter_id: item.type_id,
                        parameter_text: ['эталоны', 'материалы', 'объекты'].indexOf(item.type_name.toLowerCase()) !== -1 ? item.type_name.slice(0, item.type_name.length - 1) : item.type_name,
                        value: item.result_name,
                        value_id: item.result_id
                    });
                    this.send_filter(filter);
                }
                this.globalSearch = '';
                this.etalon = null;
                this.slider_key += 1;
                $("#search").catcomplete("search", "");
            } else {
                this.globalSearch = item.result_name;
                this.etalon = item;
                this.slider_key += 1;
                operations.backdoor += 1;
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
                    this.send_filter(filter);
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
                    this.send_filter(filter);
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
                    this.send_filter(filter);
                }
            }, 500);
        },
        delete_budge: function (index) {
            if (this.filter_items[index].parameter_id === 0) {
                axios.post('{{ route('building::mat_acc::delete_object_from_session') }}', {object_id: this.filter_items[index].value_id});
            }
            this.filter_items.splice(index, 1);
            this.send_filter(filter);
        },
        send_filter: function (filter) {
            var that = this;
            operations.loadingOperations = true;
            axios.post('{{ route('building::mat_acc::report_card::filter') }}', {
                date: operations.search_date,
                search: operations.search_queries_compact,
                filter: filter.filter_items,
                with_closed: operations.with_closed
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
                operations.operations = response.data['result'];
                operations.loadingOperations = false;
            }).catch(function() {
                operations.loadingOperations = false;
            })
        },
        clear_filters() {
            this.filter_items = [];
            this.send_filter(this.filter);
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
            filter_url = 'parameter_id=' + filter_params.toString() + '&value_ids=' + filter_values.toString() + '&date=' + operations.search_date + '&search=' + operations.search_queries_compact + '&with_closed=' + operations.with_closed;
            return filter_url;
        },
        changeFilterValues: function () {
            filter.filter_values = [];

            if (filter.parameter.id === 0) {
                axios.post('{{ route('building::mat_acc::report_card::get_objects') }}').then(response => filter.filter_values = response.data)
            } else if (filter.parameter.id === 1) {
                axios.post('{{ route('building::mat_acc::report_card::get_materials', ['withTrashed' => true]) }}').then(response => filter.filter_values = response.data)
            } else if (filter.parameter.id === 2) {
                axios.post('{{ route('building::mat_acc::get_users') }}').then(response => filter.filter_values = response.data)
            } else if (filter.parameter.id === 3) {//status
                axios.post('{{ route('building::mat_acc::get_statuses') }}').then(response => filter.filter_values = response.data)
            } else if (filter.parameter.id === 4) {//type
                axios.post('{{ route('building::mat_acc::get_types') }}').then(response => filter.filter_values = response.data)
            } else if (filter.parameter.id === 5) {
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
                if (filter.parameter.id == 2) {
                  setTimeout(() => {
                    axios.post('{{ route('building::mat_acc::get_users') }}', {q: query}).then(function (response) {
                        filter.filter_values = response.data;
                    })
                    }, 100);
                }
                if (filter.parameter.id == 4) {
                  setTimeout(() => {
                    axios.post('{{ route('building::mat_acc::get_statuses') }}', {q: query}).then(function (response) {
                        filter.filter_values = response.data;
                    })
                    }, 100);
                }
                if (filter.parameter.id == 5) {
                  setTimeout(() => {
                    axios.post('{{ route('building::mat_acc::get_types') }}', {q: query}).then(function (response) {
                        filter.filter_values = response.data;
                    })
                    }, 100);
                } else {
                  filter.filter_values = [];
              }
            }
        },
        promisedSearchGlobal(query) {
            return new Promise((resolve, reject) => {
                if (query) {
                    axios.post('{{ route('building::mat_acc::operations::get_search_values') }}', {
                            search_untrimmed: query,
                        })
                        .then(response => {
                            response.data.data = response.data.data.map(el => {
                                el.label = el.result_name;
                                if (el.type_id == 5) {
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
                            this.searchOptions = response.data.data.filter((el, ind, arr) => {
                                const currentCategoryPriotity = this.categoryPriority.filter((prio) => arr.filter(elem => elem.type_id === prio).length > 0);
                                const type_id_index = currentCategoryPriotity.indexOf(el.type_id);
                                return type_id_index < 3;
                            });
                            resolve(this.searchOptions);
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
                                if (el.type_id == 5) {
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
                            this.searchOptions = response.data.data.filter((el, ind, arr) => {
                                const currentCategoryPriotity = this.categoryPriority.filter((prio) => arr.filter(elem => elem.type_id === prio).length > 0);
                                const type_id_index = currentCategoryPriotity.indexOf(el.type_id);
                                return type_id_index < 3;
                            });
                            resolve(this.searchOptions);
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
})

var operations = new Vue({
    el: '#operations',
    data: {
        DONE_TYPING_INTERVAL: 5000,
        operations: [],
        loadingOperations: true,
        search_date : '{{Request::get('date')}}',
        search_tf: '',
        search_queries: [],
        windowWidth: window.innerWidth,
        showMobile: false,
        hideMobile: true,
        with_closed: {!!(Request::get('with_closed') != '' ? Request::get('with_closed') : 'false')!!},
        backdoor: 1,
    },
    mounted: function() {
        var url_date = '{{Request::get('date')}}';
        url_date = url_date === 'null' ? null : url_date;

        let url_search = '{{Request::get('search')}}';

        if (url_date != '') {
            this.search_date = url_date;
        }

        if (url_search) {
            url_search = url_search.split('•');
            for (i in url_search) {
                if (!Number.isNaN(+i)) {
                    this.search_queries.push(url_search[i]);
                }
            }
        } else if ('{{Request::get('value_ids')}}'.length === 0 && {!! json_encode($user_has_operations) !!} ) {
            this.search_queries.push('{{ auth()->user()->last_name }}');
        }

        let searchTF = $('#search-tf');

        searchTF.on('keyup', (e) => {
            clearTimeout(this.typingTimer);
            if(e.which != 13){
                // this.typingTimer = setTimeout(this.doneTyping, this.DONE_TYPING_INTERVAL);
            } else {
                this.doneTyping();
            }
        });

        // searchTF.on('keydown', () => {
        //     clearTimeout(this.typingTimer);
        // });

        searchTF.on('blur', (e) => {
            // clearTimeout(this.typingTimer);
            this.doneTyping();
        });

        if(requests.length > 0) {
            var defer = $.when.apply($, requests);
            defer.done(function () {
                // This is executed only after every ajax request has been completed
                filter.filter(false);
            });
        }

    },
    watch: {
        search_date: function (val) {
            operations.loadingOperations = true;
            axios.post('{{ route('building::mat_acc::report_card::filter') }}', {
                date: operations.search_date,
                search: operations.search_queries_compact,
                filter: filter.filter_items,
                with_closed: operations.with_closed
            }).then(function (response) {
                window.history.pushState("", "", "?" + filter.compactFilters());
                operations.operations = response.data['result'];
                operations.loadingOperations = false;
            }).catch(function() {
                operations.loadingOperations = false;
            })
        },
    },
    beforeUpdate() {
        window.addEventListener('resize', () => {
          this.windowWidth = window.innerWidth
      })
    },
    computed: {
        isMobile() {
          if (this.windowWidth <= 1024) {
              this.hideMobile = false;
              this.showMobile = true;
              return true;
        } else {
            this.hideMobile = true;
            this.showMobile = false;
            return false;
         }
       },
        search_queries_compact() {
            return this.search_queries.length > 0
                ? `${this.search_queries.join('•')}`
                : '';
        }
    },
    methods: {
        link_to_operation(row, column, cell, event) {
            this.save_src();
            window.location = row.general_url;
        },
        save_src() {
            document.cookie = encodeURIComponent('opsource') + '=' + encodeURIComponent(window.location.href);
        },
        delete_search_budge: function (index) {
            this.search_queries.splice(index, 1);
            filter.send_filter(filter);
        },
        doneTyping() {
            if(operations.search_tf.trim()) {
                this.search_queries.push(operations.search_tf.trim());
                operations.search_tf = '';
                operations.loadingOperations = true;
                axios.post('{{ route('building::mat_acc::report_card::filter') }}', {
                    date: operations.search_date,
                    search: operations.search_queries_compact,
                    filter: filter.filter_items,
                    with_closed: operations.with_closed
                }).then(function (response) {
                    window.history.pushState("", "", "?" + filter.compactFilters());
                    operations.operations = response.data['result'];
                    operations.loadingOperations = false;
                }).catch(function() {
                    operations.loadingOperations = false;
                })
            }
        },
        clear_search_queries() {
            this.search_queries.splice(0, this.search_queries.length);
            filter.send_filter(filter);
        },
        getTypeIcon(type) {
            switch (type.toLowerCase()) {
                case 'поступление':
                    return 'fa fa-plus text-success';
                case 'списание':
                    return 'fa fa-minus text-danger';
                case 'преобразование':
                    return 'fa fa-sync-alt text-primary';
                case 'перемещение':
                    // return 'fa fa-truck-moving text-primary';
                    // return 'fa fa-exchange-alt text-primary';
                    return 'fa fa-arrow-right text-primary';
                default:
                    return 'fa fa-sync-alt text-primary';

            }
        },
        tableRowClassName(row, rowIndex) {
            if (row.row.status === 1) {
                return '';
            } else if (row.row.status === 2) {
                return '';
            } else if (row.row.status === 3) {
                return 'success-row';
            } else if (row.row.status === 4) {
                return 'warning-row';
            } else if (row.row.status === 5) {
                return '';
            }
        },
        headerRowClassName(row, rowIndex) {
            if( this.isMobile ){
                return 'd-none';
            }
        },

        print_rep() {
            let that = this;
            this.variable = [];

            operations.operations.map(function(operation) { that.variable.push(operation.object_text.split('"').join('\\"'))});
            var data = ('{' + '"type":[' + operations.operations.map(function(operation) { return operation.type}) +
                '],"status":[' + operations.operations.map(function(operation) { return operation.status}) +
                '],"object_text":[' + this.variable.map(item => '"' + item + '"') +
                '],"created_at":[' + operations.operations.map(function(operation) { return '"' + operation.created_at + '"'}) +
                '],"actual_date_to":[' + operations.operations.map(function(operation) { return '"' + operation.actual_date_to + '"'}) +
                '],"actual_date_from":[' + operations.operations.map(function(operation) { return '"' + operation.actual_date_from + '"'}) +
                '],"author_id":[' + operations.operations.map(function(operation) { return '"' + operation.author_id + '"'}) + '],' +
                '"filter_params":[' + filter.filter_items.map(function(badge) { return badge.parameter_id}) +
                '],"filter_values":[' + filter.filter_items.map(function(badge) { return JSON.stringify(badge.parameter_text + ": " + badge.value)}) + ']' +
                '}'
            );
            // console.log(JSON.stringify(data));

            $('#print_data').val(data);

            $('#print_operations').submit();
        },

        updateResults() {
            operations.loadingOperations = true;
            axios.post('{{ route('building::mat_acc::report_card::filter') }}', {
                date: operations.search_date,
                search: operations.search_queries_compact,
                filter: filter.filter_items,
                with_closed: operations.with_closed
            }).then(function (response) {
                window.history.pushState("", "", "?" + filter.compactFilters());
                operations.operations = response.data['result'];
                operations.loadingOperations = false;
            }).catch(function() {
                operations.loadingOperations = false;
            })
        },
        onFocus: function() {
            $('.el-input__inner').blur();
        }
    },

})

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
            <div v-if="hasRealParams" class="text-center mt-2">\
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
                            style="width: 100%"\
                            :disabled="ignoreParams"\
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
                                :precision="0"\
                                :disabled="ignoreParams"\
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
                    this.$emit('update:attribute_etalon_id', this.mixedParameters.filter(el => el.name === value)[0].id);
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
            }
        },
        $_veeValidate: {
            value () {
                return this.default_parameter;
            }
        },
    });

var eventHub = new Vue();



var operation_excel = new Vue({
    el: '#operation_excel',
    data: {
        objects: [],
        object_id: {'code':0},
        range_dates: '',
        start_date: '',
        end_date: ''
    },
    mounted: function () {
        axios.post('{{ route('building::mat_acc::report_card::get_objects') }}').then(function (response) {
            operation_excel.objects = response.data;
        })
    },
    methods: {
        search(query) {
            if (query !== '') {
                  setTimeout(() => {
                        axios.post('{{ route('building::mat_acc::report_card::get_objects') }}', {q: query}).then(function (response) {
                            operation_excel.objects = response.data;
                        })
                    }, 100);
            } else {
                axios.post('{{ route('building::mat_acc::report_card::get_objects') }}').then(function (response) {
                    operation_excel.objects = response.data;
                })
            }
        },
        onFocus: function() {
            $('.el-input__inner').blur();
        },
        checkObject() {
            if (this.object_id == '' || this.object_id.code == 0) {
                this.$message({
                  showClose: true,
                  message: 'Заполните поле объект.',
                  type: 'error',
                  duration: 5000
                });
            } else {
                $('#formExportToExcel').submit();
            }
        }
    }
})

</script>

@endsection
