@extends('layouts.app')

@section('title', 'Учет топлива')

@section('css_top')
    <link rel="stylesheet" href="{{ asset('css/video-js.css') }}">
    <style>
        th.text-truncate {
            position: relative;
            overflow: visible;
        }
        @media (min-width: 768px) {
            span.text-truncate {
                max-width: 50px;
            }
        }
        @media (min-width: 1200px) {
            span.text-truncate {
                max-width: 80px;
            }
        }
        @media (min-width: 1360px) {
            span.text-truncate {
                max-width: 140px;
            }
        }
        @media (min-width: 1560px) {
            span.text-truncate {
                max-width: 220px;
            }
        }
        @media (min-width: 1920px) {
            span.text-truncate {
                max-width: 300px;
            }
        }

        .el-upload-dragger {
            width: inherit !important;
        }

        .reset-button-fade-enter-active, .reset-button-fade-leave-active {
            transition: opacity .5s;
        }
        .reset-button-fade-enter, .reset-button-fade-leave-to {
            opacity: 0;
        }

        .el-input-number .el-input__inner {
            text-align: left !important;
        }

        #value-input .el-input__inner {
            padding-left: 15px !important;
        }

        .hoverable-row {
            border-bottom: 1px solid lightgrey;
        }

        .hoverable-row:last-of-type {
            border-bottom: none;
        }

        /*.hoverable-row:hover {
            border-bottom: 2px solid #BBB;
        }*/

        [data-balloon],
        [data-balloon]:before,
        [data-balloon]:after {
            z-index: 9999;
        }
    </style>
@endsection

@section('content')
    <div class="row" id="base" v-cloak>
        <div class="col-md-12 mobile-card">
            <div aria-label="breadcrumb" role="navigation">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item active" aria-current="page">Топливный журнал</li>
                </ol>
            </div>
            <div :class="{'card': true, 'col-10 mr-auto ml-auto pd-0-min': window_width > 2000}" style="border:1px solid rgba(0,0,0,.125);">
                <div class="card-body-tech">
                    <div class="row">
                        <div class="col-md-12">
                            <h6 class="mb-20" style="margin-top:5px">Фильтры</h6>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-5">
                            <label>Артибут</label>
                            <el-select v-model="filter_attribute" placeholder="Выберите атрибут">
                                <el-option v-for="attr in filter_attributes_filtered" :label="attr.label"
                                           :value="attr.value"
                                ></el-option>
                            </el-select>
                        </div>
                        <div class="col-md-4 mt-10__mobile">
                            <label for="count">Значение</label>

                            <el-select v-if="filter_attribute === 'contractor'"
                                       v-model="filter_value"
                                       filterable clearable
                                       placeholder="Выберите поставщика"
                                       @keyup.native.enter="() => {addFilter(); $refs['contractor-filter'].blur();}"
                                        ref="contractor-filter"
                                       @clear="searchSuppliers('')"
                                       remote
                                       :remote-method="searchSuppliers"
                                       @change="updateCurrentCustomName"
                            >
                                <el-option
                                    v-for="(item, key) in contractors"
                                    :key="key"
                                    :value="item.id"
                                    :label="item.name"
                                    clearable
                                ></el-option>
                            </el-select>
                            <el-select v-else-if="filter_attribute === 'type'"
                                       v-model="filter_value" filterable clearable
                                       @keyup.native.enter="() => {addFilter(); $refs['type-filter'].blur();}"
                                        ref="type-filter"
                                       placeholder="Выберите вид записи"
                                       @change="updateCurrentCustomName"
                            >
                                <el-option
                                    v-for="(item, key) in types"
                                    :key="key"
                                    :value="item.id"
                                    :label="item.name"
                                ></el-option>
                            </el-select>
                            <el-select v-else-if="filter_attribute === 'author'"
                                       v-model="filter_value"
                                       @keyup.native.enter="() => {addFilter(); $refs['author-filter'].blur();}"
                                       ref="author-filter"
                                       clearable filterable
                                       :remote-method="searchAuthors"
                                       @change="updateCurrentCustomName"
                                       @clear="searchAuthors('')"
                                       remote
                                       placeholder="Поиск автора"
                            >
                                <el-option
                                    v-for="item in authors"
                                    :key="item.id"
                                    :label="item.name"
                                    :value="item.id">
                                </el-option>
                            </el-select>
                            <el-select v-else-if="filter_attribute === 'our_technic'"
                                       v-model="filter_value"
                                       @keyup.native.enter="() => {addFilter(); $refs['our_technic-filter'].blur();}"
                                       ref="our_technic-filter"
                                       clearable filterable
                                       :remote-method="searchTechs"
                                       @change="updateCurrentCustomName"
                                       @clear="searchTechs('')"
                                       remote
                                       placeholder="Поиск заправляемой техники"
                            >
                                <el-option
                                    v-for="item in techs"
                                    :key="item.id"
                                    :label="item.name"
                                    :value="item.id">
                                </el-option>
                            </el-select>
                            <el-select v-else-if="filter_attribute === 'tank_number'"
                                       v-model="filter_value"
                                       clearable filterable
                                       @keyup.native.enter="() => {addFilter(); $refs['tank_number-filter'].blur();}"
                                       ref="tank_number-filter"
                                       :remote-method="searchFuelCapacities"
                                       @change="updateCurrentCustomName"
                                       @clear="searchFuelCapacities('')"
                                       remote
                                       placeholder="Поиск топливной ёмкости"
                            >
                                <el-option
                                    v-for="item in fuelCapacities"
                                    :key="item.id"
                                    :label="item.name"
                                    :value="item.id">
                                </el-option>
                            </el-select>
                            <el-date-picker
                                v-else-if="['date_updated_from', 'date_updated_to', 'operation_date_from', 'operation_date_to'].includes(filter_attribute)"
                                style="cursor:pointer"
                                @keyup.native.enter="() => {addFilter(); $refs['date-filter'].blur();}"
                                ref="date-filter"
                                v-model="filter_value"
                                format="dd.MM.yyyy"
                                value-format="yyyy-MM-dd"
                                type="date"
                                placeholder="Укажите дату"
                                :picker-options="{firstDayOfWeek: 1}"
                                @focus="onFocus"
                            ></el-date-picker>
                            <el-input-number
                                v-else-if="['value_from', 'value_to'].includes(filter_attribute)"
                                class="w-100"
                                @keyup.native.enter="addFilter"
                                maxlength="10"
                                :min="0"
                                :step="1"
                                :precision="3"
                                placeholder="Введите уровень топлива"
                                v-model="filter_value"
                            ></el-input-number>
                            <el-input v-else placeholder="Введите значение"
                             @keyup.native.enter="addFilter"
                             v-model="filter_value" id="filter-value-tf" clearable></el-input>
                        </div>
                        <div class="col-md-2 text-center--mobile" style="margin:29px 10px 20px 0">
                            <button type="button" class="btn btn-primary btn-outline" @click="addFilter">
                                Добавить
                            </button>
                        </div>
                    </div>
                    <div v-if="filters.length > 0" class="row">
                        <div class="col-md-12" style="margin: 10px 0 10px 0">
                            <h6>Выбранные фильтры</h6>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-9">
                            <div class="bootstrap-tagsinput" style="margin-top:5px">
                            <span v-for="(filter, i) in filters" class="badge badge-azure">
                                @{{ filter.attribute }}:
                                @{{ getFilterValueLabel(filter) }}
                                <span data-role="remove" @click="removeFilter(i)" class="badge-remove-link"></span>
                            </span>
                            </div>
                        </div>
                        <div v-if="filters.length > 0"
                             class="col-md-3 text-right mnt-20--mobile text-center--mobile"
                        >
                            <button id="clearAll" type="button" class="btn btn-sm show-all" @click="clearFilters">
                                Снять фильтры
                            </button>
                        </div>
                    </div>
                </div>
            </div>
            <div :class="{'card': true, 'col-10 mr-auto ml-auto pd-0-min': window_width > 2000}">
                <div class="card-body card-body-tech">
                    <div class="fixed-table-toolbar toolbar-for-btn" style="padding-left:0">
                        <div class="row">
                            <div class="col-sm-6 col-md-4">
                                <h4 class="h4-tech fw-500 m-0" style="margin-top:0"><span v-pre>Топливные записи</span></h4>
                                <a class="tech-link modal-link d-block ml-1">
                                    Найдено записей: @{{ totalItems }}
                                </a>
                                {{--<el-input placeholder="Поиск по наименованию" v-model="search_tf" clearable
                                          prefix-icon="el-icon-search"
                                ></el-input>--}}
                            </div>
                            <div class="col-sm-6 col-md-8 text-right mt-10__mobile">
                                {{--TODO move this button to a different place--}}
                                <button type="button" name="button" class="btn btn-sm btn-primary btn-round btn-outline"
                                        data-toggle="modal" data-target="#report-create" @click="openReportCreate">Сформировать топливный отчёт
                                </button>
                                <button type="button" name="button" class="btn btn-sm btn-primary btn-round"
                                        @click="addRecord">Добавить запись
                                </button>
                            </div>
                        </div>
                    </div>
                    <div class="table-responsive">
                        <table class="table table-hover mobile-table">
                            <thead>
                            <tr>
                                <th class="text-truncate" data-balloon-pos="up-left" aria-label="Дата записи"><span class="text-truncate d-inline-block">Дата записи</span></th>
                                {{--<th class="text-truncate" data-balloon-pos="up-left" aria-label="#"><span class="text-truncate d-inline-block">#</span></th>--}}
                                <th class="text-truncate" data-balloon-pos="up-left" aria-label="Номер топливной ёмкости"><span class="text-truncate d-inline-block">Номер топливной ёмкости</span></th>
                                <th class="text-truncate" data-balloon-pos="up-left" aria-label="Вид записи"><span class="text-truncate d-inline-block">Вид записи</span></th>
                                <th class="text-truncate" data-balloon-pos="up-left" aria-label="Объём, л."><span class="text-truncate d-inline-block">Объём, л.</span></th>
                                <th v-if="hasAnyConsumption" class="text-truncate" data-balloon-pos="up-left" aria-label="Заправляемая техника"><span class="text-truncate d-inline-block">Заправляемая техника</span></th>
                                <th v-if="hasAnySupply" class="text-truncate" data-balloon-pos="up-left" aria-label="Поставщик"><span class="text-truncate d-inline-block">Поставщик</span></th>
                                <th class="text-truncate" data-balloon-pos="up-left" aria-label="Дата операции"><span class="text-truncate d-inline-block">Дата операции</span></th>
                                <th class="text-truncate" data-balloon-pos="up-left" aria-label="Автор записи"><span class="text-truncate d-inline-block">Автор записи</span></th>
                                <th></th>
                            </tr>
                            </thead>
                            <tbody>
                            <tr v-if="records.length === 0">
                                <td></td>
                                <td>Записей не найдено.</td>
                                <td></td>
                                <td></td>
                                <td></td>
                                <td></td>
                            </tr>
                            <tr v-else v-for="record in records">
                                <td data-label="Дата записи">
                                    <span :class="isWeekendDay(convertDateFormat(record.updated_at), 'DD.MM.YYYY') ? 'weekend-day' : ''">
                                        @{{ isValidDate(convertDateFormat(record.updated_at), 'DD.MM.YYYY') ? weekdayDate(convertDateFormat(record.updated_at), 'DD.MM.YYYY') : '-' }}
                                    </span>
                                </td>
                                {{--<td data-label="#">
                                    @{{record.id}}
                                </td>--}}
                                <td data-label="Номер топливной ёмкости">
                                    @{{record.fuel_tank ? record.fuel_tank.tank_number : ''}}
                                </td>
                                <td data-label="Вид записи">
                                    @{{record.type_name}}
                                </td>
                                <td data-label="Объём, л.">
                                    @{{record.value}}
                                </td>
                                <td v-if="hasAnyConsumption" data-label="Заправляемая техника">
                                    @{{record.our_technic ? record.our_technic.name : '-'}}
                                </td>
                                <td v-if="hasAnySupply" data-label="Поставщик">
                                    @{{record.contractor ? record.contractor.full_name : '-'}}
                                </td>
                                <td data-label="Дата операции">
                                    <span :class="isWeekendDay(convertDateFormat(record.operation_date), 'DD.MM.YYYY') ? 'weekend-day' : ''">
                                        @{{ isValidDate(convertDateFormat(record.operation_date), 'DD.MM.YYYY') ? weekdayDate(convertDateFormat(record.operation_date), 'DD.MM.YYYY') : '-' }}
                                    </span>
                                </td>
                                <td data-label="Автор записи">
                                    @{{record.author.full_name}}
                                </td>
                                <td class="text-right actions">
                                    <button data-balloon-pos="up"
                                            aria-label="Просмотр"
                                            class="btn btn-link btn-xs btn-space btn-primary mn-0" @click="showRecord(record.id)">
                                        <i class="fa fa-eye"></i>
                                    </button>
                                    <button data-balloon-pos="up"
                                            aria-label="Редактировать"
                                            v-if="record.type != 3"
                                            class="btn btn-link btn-xs btn-space btn-success mn-0" @click="editRecord(record)">
                                        <i class="fa fa-edit"></i>
                                    </button>
                                    <button data-balloon-pos="up"
                                            aria-label="Удалить"
                                            class="btn btn-link btn-xs btn-space btn-danger mn-0" @click="removeRecord(record.id)">
                                        <i class="fa fa-trash"></i>
                                    </button>
                                </td>
                            </tr>
                            </tbody>
                        </table>
                    </div>
                    <div class="d-flex justify-content-end mt-2">
                        <el-pagination
                            :background="pagerBackground"
                            :page-size="PAGE_SIZE"
                            :total="totalItems"
                            :small="smallPager"
                            :current-page.sync="currentPage"
                            :pagerCount="pagerCount"
                            layout="prev, pager, next"
                            @prev-click="changePage"
                            @next-click="changePage"
                            @current-change="changePage"
                        >
                        </el-pagination>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- modals -->
    <!-- form -->
    <div class="modal fade bd-example-modal-lg show" id="form_record" role="dialog" aria-labelledby="modal-search" style="display: none;">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content" id="create_form">
                <div class="modal-header">
                    <h5 class="modal-title">@{{ edit_mode ? 'Редактирование' : 'Добавление' }} записи о @{{ type === 'Поставка' ? 'поставке' : 'заправке' }}</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">×</span>
                    </button>
                </div>
                <div class="modal-body pt-0">
                    <div class="card border-0 m-0">
                        <div class="card-body">
                            <validation-observer ref="observer" :key="observer_key">
                                <form class="form-horizontal">
                                    <template>
                                        <div class="row" v-show="!edit_mode">
                                            <div class="col-md-12">
                                                <label for="">Тип записи<span class="star">*</span></label>
                                                <el-radio-group v-model="type" class="d-block">
                                                    <el-radio-button label="Поставка"></el-radio-button>
                                                    <el-radio-button label="Заправка"></el-radio-button>
                                                </el-radio-group>
                                            </div>
                                        </div>
                                        <div class="row">
                                            <div class="col-md-6">
                                                <label for="">Топливная ёмкость<span class="star">*</span></label>
                                                <validation-provider rules="required" v-slot="v" vid="fuel-capacity-select"
                                                                     ref="fuel-capacity-select">
                                                    <el-select v-model="fuel_tank_id"
                                                               :class="v.classes"
                                                               clearable filterable
                                                               id="fuel-capacity-select"
                                                               :remote-method="searchFuelCapacities"
                                                               @clear="searchFuelCapacities('')"
                                                               @change="loadFuelTankLocation"
                                                               @focus="firstLoad('fuel_tank_id')"
                                                               remote
                                                               autocomplete="none"
                                                               placeholder="Поиск топливной ёмкости"
                                                    >
                                                        <el-option
                                                            v-for="item in fuel_capacities"
                                                            :key="item.id"
                                                            :label="item.name"
                                                            :value="item.id">
                                                        </el-option>
                                                    </el-select>
                                                    <div class="error-message">@{{ v.errors[0] }}</div>
                                                </validation-provider>
                                            </div>
                                            <div class="col-md-6">
                                            <label for="">Дата @{{ type === 'Поставка' ? 'поставки' : 'заправки' }}<span class="star">*</span></label>
                                                <validation-provider rules="required" v-slot="v" vid="date-input"
                                                                     ref="date-input">
                                                    <el-date-picker
                                                        style="cursor:pointer"
                                                        :class="v.classes"
                                                        v-model="date"
                                                        format="dd.MM.yyyy"
                                                        value-format="dd.MM.yyyy"
                                                        id="date-input"
                                                        type="date"
                                                        :placeholder="`Укажите дату ${type === 'Поставка' ? 'поставки' : 'заправки'}`"
                                                        :picker-options="datePickerOptions"
                                                        @focus="onFocus"
                                                    ></el-date-picker>
                                                    <div class="error-message">@{{ v.errors[0] }}</div>
                                                </validation-provider>
                                            </div>
                                        </div>

                                        <div class="row">
                                            <div class="col-md-6">
                                                <label for="">Местоположение<span class="star">*</span></label>
                                                <validation-provider rules="required" v-slot="v" vid="object-select"
                                                                     ref="object-select">
                                                    <el-select v-model="object_id"
                                                               :class="v.classes"
                                                               clearable filterable
                                                               autocomplete="none"
                                                               id="object-select"
                                                               :remote-method="searchObjects"
                                                               @clear="searchObjects('')"
                                                               @focus="firstLoad('object_id')"
                                                               remote
                                                               placeholder="Поиск объекта"
                                                    >
                                                        <el-option
                                                            v-for="item in objects"
                                                            :key="item.id"
                                                            :label="item.name"
                                                            :value="item.id">
                                                        </el-option>
                                                    </el-select>
                                                    <div class="error-message">@{{ v.errors[0] }}</div>
                                                </validation-provider>
                                            </div>
                                            <div class="col-md-6">
                                                <label for="">Объём @{{ type === 'Поставка' ? 'поставки' : 'заправленного' }} топлива, л.<span class="star">*</span></label>
                                                <validation-provider rules="required|positive|max:10" vid="value-input"
                                                                     ref="value-input"
                                                                     v-slot="v"
                                                >
                                                    <el-input-number
                                                        :class="v.classes"
                                                        class="w-100"
                                                        id="value-input"
                                                        maxlength="10"
                                                        :min="0"
                                                        :step="1"
                                                        :precision="0"
                                                        controls-position="right"
                                                        autocomplete="none"
                                                        placeholder="Укажите поставляемый объём"
                                                        v-model="value"
                                                    ></el-input-number>
                                                    <div class="error-message">@{{ v.errors[0] }}</div>
                                                </validation-provider>
                                            </div>
                                        </div>

                                        <div class="row" v-show="type === 'Поставка'">
                                            <div class="col-md-6">
                                                <label for="">Поставщик<span class="star">*</span></label>
                                                <validation-provider rules="required" v-slot="v" vid="contractor-select"
                                                                     ref="contractor-select">
                                                    <el-select v-model="contractor_id"
                                                               :class="v.classes"
                                                               clearable filterable
                                                               autocomplete="none"
                                                               id="contractor-select"
                                                               :remote-method="searchSuppliers"
                                                               @clear="searchSuppliers('')"
                                                               @focus="firstLoad('contractor_id')"
                                                               remote
                                                               placeholder="Поиск поставщиков"
                                                    >
                                                        <el-option
                                                            v-for="item in contractors"
                                                            :key="item.id"
                                                            :label="item.name"
                                                            :value="item.id">
                                                        </el-option>
                                                    </el-select>
                                                    <div class="error-message">@{{ v.errors[0] }}</div>
                                                </validation-provider>
                                            </div>
                                            <div class="col-md-6">
                                                <label for="">Собственник<span class="star">*</span></label>
                                                <validation-provider rules="required" v-slot="v" vid="owner-select"
                                                                     ref="owner-select">
                                                    <el-select v-model="owner"
                                                               :class="v.classes"
                                                               clearable filterable
                                                               autocomplete="none"
                                                               id="owner-select"
                                                               placeholder="Поиск собственников"
                                                    >
                                                        <el-option
                                                            v-for="(item, i) in owners"
                                                            :key="i"
                                                            :label="item"
                                                            :value="item">
                                                        </el-option>
                                                    </el-select>
                                                    <div class="error-message">@{{ v.errors[0] }}</div>
                                                </validation-provider>
                                            </div>
                                        </div>

                                        <div class="row">
                                            <div class="col-md-6" id="video-upload-section">
                                                <label for="">Видео процесса<span class="star">*</span></label>
                                                <el-upload
                                                    :drag="window_width > 1100"
                                                    action="{{ route('file_entry.store') }}"
                                                    :headers="{ 'X-CSRF-TOKEN': '{{ csrf_token() }}' }"
                                                    :limit="2"
                                                    ref="video_upload"
                                                    :before-upload="beforeUploadVideo"
                                                    :on-preview="handlePreview"
                                                    :on-remove="handleRemoveVideo"
                                                    :on-exceed="handleExceedVideo"
                                                    :on-success="handleSuccessVideo"
                                                    :on-error="handleError"
                                                    :file-list="file_list_video"
                                                    multiple
                                                >
                                                    <template v-if="window_width > 1100">
                                                        <i class="el-icon-upload"></i>
                                                        <div class="el-upload__text">Перетащите сюда или <em>кликните, чтобы выбрать файлы для загрузки</em></div>
                                                    </template>
                                                    <el-button v-else size="small" type="primary">Загрузить</el-button>
                                                    <div class="el-upload__tip" slot="tip">
                                                        <span class="important-tip" style="font-size: 14px">Необходимо прикрепить две видеозаписи - показания в начале процесса и показания в конце процесса</span>
                                                        <br>Файлы формата mp4 размером до 300Мб
                                                    </div>
                                                </el-upload>
                                                <div class="error-message d-none" id="video-upload-section-error">Обязательное поле</div>
                                            </div>
                                            <div v-show="type === 'Поставка'" class="col-md-6" id="doc-upload-section">
                                                <label for="">Сопроводительные документы<span class="star">*</span></label>
                                                <el-upload
                                                    :drag="window_width > 1100"
                                                    action="{{ route('file_entry.store') }}"
                                                    :headers="{ 'X-CSRF-TOKEN': '{{ csrf_token() }}' }"
                                                    ref="doc_upload"
                                                    :limit="10"
                                                    :before-upload="beforeUploadDoc"
                                                    :on-preview="handlePreview"
                                                    :on-remove="handleRemoveDoc"
                                                    :on-exceed="handleExceedDoc"
                                                    :on-success="handleSuccessDoc"
                                                    :on-error="handleError"
                                                    :file-list="file_list_docs"
                                                    multiple
                                                >
                                                    <template v-if="window_width > 1100">
                                                        <i class="el-icon-upload"></i>
                                                        <div class="el-upload__text">Перетащите сюда или <em>кликните, чтобы выбрать файлы для загрузки</em></div>
                                                    </template>
                                                    <el-button v-else size="small" type="primary">Загрузить</el-button>
                                                    <div class="el-upload__tip" slot="tip">Неисполняемые файлы размером до 50Мб</div>
                                                </el-upload>
                                                <div class="error-message d-none" id="doc-upload-section-error">Обязательное поле</div>
                                            </div>
                                            <div v-show="type === 'Заправка'" class="col-md-6">
                                                <label for="">Заправляемая техника<span class="star">*</span></label>
                                                <validation-provider rules="required" v-slot="v" vid="our_technic-select"
                                                                     ref="our_technic-select">
                                                    <el-select v-model="our_technic_id"
                                                               :class="v.classes"
                                                               clearable filterable
                                                               autocomplete="none"
                                                               id="our_technic-select"
                                                               @focus="firstLoad('our_technic_id')"
                                                               :remote-method="searchTechs"
                                                               @clear="searchTechs('')"
                                                               remote
                                                               placeholder="Поиск техники"
                                                    >
                                                        <el-option
                                                            v-for="item in techs"
                                                            :key="item.id"
                                                            :label="item.name"
                                                            :value="item.id">
                                                        </el-option>
                                                    </el-select>
                                                    <div class="error-message">@{{ v.errors[0] }}</div>
                                                </validation-provider>
                                            </div>
                                        </div>
                                        <div class="row" style="margin-bottom:10px">
                                            <div class="col-md-12">
                                                <label for="">
                                                    Комментарий<span class="star">*</span>
                                                </label>
                                                <validation-provider rules="required|max:300" v-slot="v" vid="comment-input"
                                                                     ref="comment-input">
                                                    <el-input
                                                        type="textarea"
                                                        :rows="4"
                                                        :class="v.classes"
                                                        id="comment-input"
                                                        maxlength="300"
                                                        placeholder="Напишите комментарий"
                                                        v-model="description"
                                                    ></el-input>
                                                    <div class="error-message">@{{ v.errors[0] }}</div>
                                                </validation-provider>
                                            </div>
                                        </div>

                                    </template>
                                </form>
                            </validation-observer>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <template v-if="window_width > 769">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Закрыть</button>
                        <transition name="reset-button-fade">
                            <button v-if="!edit_mode" @click.stop="reset" type="button" class="btn btn-warning">Сброс</button>
                        </transition>
                        <button @click.stop="submit" type="button" class="btn btn-info">Сохранить</button>
                    </template>
                    <div v-else class="col-md-12">
                        <div class="row justify-content-center mb-2">
                            <button type="button" class="btn btn-secondary w-100" data-dismiss="modal">Закрыть</button>
                        </div>
                        <div class="row justify-content-center mb-2">
                            <transition name="reset-button-fade">
                                <button v-if="!edit_mode" @click.stop="reset" type="button" class="btn btn-warning w-100">Сброс</button>
                            </transition>
                        </div>
                        <div class="row justify-content-center mb-2">
                            <button @click.stop="submit" type="button" class="btn btn-info w-100">Сохранить</button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- card -->
    <div class="modal fade bd-example-modal-lg show" id="card_record" role="dialog" aria-labelledby="modal-search" style="display: none;">
        <div class="modal-dialog modal-lg" role="document" style="max-width:900px">
            <div class="modal-content">
                <div class="decor-modal__body">
                    <div class="row" style="flex-direction: row-reverse">
                        <div class="col-md-4">
                            <div class="right-bar-info border-0">
                                <div>
                                    <div class="row">
                                        <div class="col-md-12">
                                            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                                <span aria-hidden="true" style="color:#202020;font-size: 26px;font-weight: 500;">&times;</span>
                                            </button>
                                        </div>
                                    </div>
                                    <div class="task-info__text-unit">
                                        <span class="task-info__head-title">
                                            Дата @{{ record.type == 3 ? 'ручного изменения' : (record.contractor ? 'поставки' : 'заправки') }}
                                        </span>
                                        <span class="task-info__body-title">
                                            <span :class="isWeekendDay(convertDateFormat(record.operation_date), 'DD.MM.YYYY') ? 'weekend-day' : ''">
                                                @{{ isValidDate(convertDateFormat(record.operation_date), 'DD.MM.YYYY') ? weekdayDate(convertDateFormat(record.operation_date), 'DD.MM.YYYY') : '-' }}
                                            </span>
                                        </span>
                                    </div>
                                    <div class="task-info__text-unit">
                                        <span class="task-info__head-title">
                                            Местоположение
                                        </span>
                                        <span class="task-info__body-title">
                                            <template v-if="record.object">
                                                <template v-if="record.object.short_name">
                                                    @{{ record.object.short_name }}
                                                </template>
                                                <template v-else>
                                                    @{{ record.object.name ? record.object.name : '' }},<br>
                                                    @{{ record.object.address ? record.object.address : '' }}
                                                </template>
                                            </template>
                                        </span>
                                    </div>
                                    <div class="task-info__text-unit">
                                        <span v-if="record.type == 3" class="task-info__head-title">
                                            Уровень топлива после изменения
                                        </span>
                                        <span v-else class="task-info__head-title">
                                            Объём @{{ record.contractor ? 'поступившего' : 'заправленного' }} топлива
                                        </span>
                                        <span class="task-info__body-title">
                                            @{{ record.value ? record.value : '' }} литров
                                        </span>
                                    </div>
                                    <div class="task-info__text-unit">
                                        <span class="task-info__head-title">
                                            Номер ёмкости
                                        </span>
                                        <span class="task-info__body-title">
                                            @{{ record.fuel_tank ? record.fuel_tank.tank_number : '' }}
                                        </span>
                                    </div>
                                    <div v-if="record.our_technic" class="task-info__text-unit">
                                        <span class="task-info__head-title">
                                            Заправляемая техника
                                        </span>
                                        <span class="task-info__body-title">
                                            @{{ record.our_technic ? record.our_technic.name : '' }}
                                        </span>
                                    </div>
                                    <div class="task-info__text-unit">
                                        <span class="task-info__head-title">
                                            Дата записи в журнале
                                        </span>
                                        <span class="task-info__body-title">
                                            <span :class="isWeekendDay(convertDateFormat(record.created_at), 'DD.MM.YYYY') ? 'weekend-day' : ''">
                                                @{{ isValidDate(convertDateFormat(record.created_at), 'DD.MM.YYYY') ? weekdayDate(convertDateFormat(record.created_at), 'DD.MM.YYYY') : '-' }}
                                            </span>
                                        </span>
                                    </div>
                                    <div class="task-info__text-unit">
                                        <span class="task-info__head-title">
                                            Автор записи
                                        </span>
                                        <span class="task-info__body-title">
                                            @{{ record.author ? record.author.full_name : '' }}
                                        </span>
                                    </div>
                                    <div v-if="record.contractor" class="task-info__text-unit">
                                        <span class="task-info__head-title">
                                            Поставщик
                                        </span>
                                        <span class="task-info__body-title">
                                            @{{record.contractor ? record.contractor.full_name : '-'}}
                                        </span>
                                    </div>
                                    <div v-if="record.type === 1" class="task-info__text-unit">
                                        <span class="task-info__head-title">
                                            Собственник
                                        </span>
                                        <span class="task-info__body-title">
                                            @{{ record.owner ? record.owner : '' }}
                                        </span>
                                    </div>
                                </div>
                                <div class="row" v-if="record.type != 3">
                                    <div class="col-md-12 text-center">
                                        <button type="button" name="button"
                                                @click="edit_record_modal_show"
                                                class="btn btn-sm btn-round btn-primary">
                                            Редактировать
                                        </button>
                                    </div>
                                </div>
                                @can('see_fuel_operation_history')
                                <div class="row mt-2">
                                    <div class="col-md-12 text-center">
                                        <a v-if="window_width > 769 & record.type != 3" href="#collapse" class="text-primary font-weight-bold" style="font-size: 15px;"
                                           data-target="#collapse" data-toggle="collapse">
                                            История изменений
                                        </a>
                                    </div>
                                </div>
                                @endcan()
                            </div>
                        </div>
                        <div class="col-md-8">
                            <div class="left-bar-main">
                                <h4 class="mn-0 fw-500" style="font-size: 26px">@{{ cardTitle }}</h4>
                                <template v-if="record.type != 3">
                                    <h6 class="decor-h6-modal">Видеозаписи</h6>
                                    <div class="modal-section">
                                        <div class="row">
                                            <div class="col-md-12" id="video-section">
                                                <a v-if="!record.videos || record.videos.length === 0" class="tech-link modal-link">
                                                    Видеозаписи отсутствуют
                                                </a>
                                            </div>
                                        </div>
                                    </div>
                                </template>
                                <template v-if="record.contractor">
                                    <h6 class="decor-h6-modal">Приложенные файлы</h6>
                                    <div class="modal-section">
                                        <div class="row">
                                            <div class="col-md-12">
                                                <template v-if="!record.not_videos || record.not_videos.length === 0">
                                                    <a class="tech-link modal-link">
                                                        Приложенные файлы не найдены
                                                    </a>
                                                </template>
                                                <template v-else v-for="file in record.not_videos">
                                                    <a :href="'{{ asset('storage/docs/tech_accounting/') }}' + '/' + file.filename"
                                                       target="_blank" class="tech-link modal-link">
                                                        @{{ file.original_filename }}
                                                    </a>
                                                    <br/>
                                                </template>
                                            </div>
                                        </div>
                                    </div>
                                </template>
                                <h6 class="decor-h6-modal">Комментарий</h6>
                                <div class="modal-section">
                                    <div class="row">
                                        <div class="col-md-12">
                                            @{{ record.description ? record.description : 'Комментарий отсутствует' }}
                                        </div>
                                    </div>
                                </div>
                                @can('see_fuel_operation_history')
                                <div v-if="window_width <= 769 && record.type != 3" class="modal-section">
                                    <div class="row mt-3">
                                        <div class="col-md-12">
                                            <a href="#collapse" class="text-primary font-weight-bold" style="font-size: 15px;"
                                               data-target="#collapse" data-toggle="collapse">
                                                История изменений
                                            </a>
                                        </div>
                                    </div>
                                </div>
                                @endcan()
                            </div>
                        </div>
                    </div>
                </div>
                @can('see_fuel_operation_history')
                <div class="px-3 pb-3 collapse card-collapse modal-section" id="collapse" v-if="record.type != 3">
                    <el-table :data="record.history"
                              class="w-100"
                              v-if="window_width > 769"
                    >
                        <el-table-column
                                label="Дата"
                                width="175"
                        >
                            <template slot-scope="scope">
                                <span :class="isWeekendDay(convertDateFormat(scope.row.created_at, true), 'DD.MM.YYYY HH:mm:ss') ? 'weekend-day' : ''">
                                    @{{ isValidDate(convertDateFormat(scope.row.created_at, true), 'DD.MM.YYYY HH:mm:ss') ? weekdayDate(convertDateFormat(scope.row.created_at, true), 'DD.MM.YYYY HH:mm:ss', 'DD.MM.YYYY dd HH:mm:ss') : '-' }}
                                </span>
                            </template>
                        </el-table-column>
                        <el-table-column
                                prop="user.full_name"
                                label="Автор"
                                width="180"
                        ></el-table-column>
                        <el-table-column
                                prop="changed_fields_parsed"
                                label="Изменения"
                        >
                            <template slot-scope="scope">
                                <div v-if="scope.row.changed_fields_parsed.old_values">
                                    <div class="d-flex justify-content-between hoverable-row"
                                        v-for="(field, i) in Object.keys(scope.row.changed_fields_parsed.old_values)"
                                        :style="i > 0 ? 'border-top: 1px solid #EBEEF5;' : ''"
                                    >
                                        <div style="padding-right: 10px;">@{{ getFieldLabel(field) }}</div>
                                        <div style="min-width: 350px; width: 350px; padding-left: 10px; border-left: 1px solid lightgrey" v-if="field !== 'date'">@{{ scope.row.changed_fields_parsed.old_values[field] }} → @{{ scope.row.changed_fields_parsed.new_values[field] }}</div>
                                        <div style="min-width: 350px; width: 350px; padding-left: 10px; border-left: 1px solid lightgrey" v-else>@{{ convertDateFormat(scope.row.changed_fields_parsed.old_values[field]) }} → @{{ convertDateFormat(scope.row.changed_fields_parsed.new_values[field]) }}</div>
                                    </div>
                                </div>
                            </template>
                        </el-table-column>
                    </el-table>
                    <el-table :data="record.history"
                              class="w-100"
                              v-else
                    >
                        <el-table-column
                                prop="created_at"
                                label="История изменений"
                        >
                            <template slot-scope="scope">
                                <div class="font-weight-bold">
                                    <span :class="isWeekendDay(convertDateFormat(scope.row.created_at, true), 'DD.MM.YYYY HH:mm:ss') ? 'weekend-day' : ''">
                                        @{{ isValidDate(convertDateFormat(scope.row.created_at, true), 'DD.MM.YYYY HH:mm:ss') ? weekdayDate(convertDateFormat(scope.row.created_at, true), 'DD.MM.YYYY HH:mm:ss', 'DD.MM.YYYY dd HH:mm:ss') : '-' }}
                                    </span>
                                </div>
                                <div class="font-weight-bold">@{{  scope.row.user.full_name  }}</div>
                                <div v-for="(field, i) in Object.keys(scope.row.changed_fields_parsed.old_values)"
                                     style="border-top: 1px solid #EBEEF5;"
                                >
                                    <div>@{{ getFieldLabel(field) }}</div>
                                    <div v-if="field !== 'date'">@{{ scope.row.changed_fields_parsed.old_values[field] }} → @{{ scope.row.changed_fields_parsed.new_values[field] }}</div>
                                    <div v-else>@{{ convertDateFormat(scope.row.changed_fields_parsed.old_values[field]) }} → @{{ convertDateFormat(scope.row.changed_fields_parsed.new_values[field]) }}</div>
                                </div>
                            </template>
                        </el-table-column>
                    </el-table>
                    {{--                    <div class="d-flex justify-content-end mt-2">--}}
                    {{--                        <el-pagination--}}
                    {{--                            :background="pagerBackground"--}}
                    {{--                            :page-size="PAGE_SIZE"--}}
                    {{--                            :total="totalItems"--}}
                    {{--                            :small="smallPager"--}}
                    {{--                            :current-page.sync="currentPage"--}}
                    {{--                            :pagerCount="pagerCount"--}}
                    {{--                            layout="prev, pager, next"--}}
                    {{--                            @prev-click="changePage"--}}
                    {{--                            @next-click="changePage"--}}
                    {{--                            @current-change="changePage"--}}
                    {{--                        ></el-pagination>--}}
                    {{--                    </div>--}}
                </div>
                @endcan()
            </div>
        </div>
    </div>
    <!-- report-create -->
    <div class="modal fade bd-example-modal-lg show" id="report-create" role="dialog" aria-labelledby="modal-search" style="display: none;">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content pb-3">
                <div class="modal-header">
                    <h5 class="modal-title">Формирование топливного отчета</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">×</span>
                    </button>
                </div>
                <div class="modal-body pt-0">
                    <div class="card border-0 m-0">
                        <div class="card-body">
                            <validation-observer ref="observer" :key="observer_key">
                                <form class="form-horizontal" method="get"
                                      target="_blank" id="report-create-form" multisubmit="true"
                                      action="{{ route('building::tech_acc::fuel_tank_operation.report') }}">

                                    <input type="hidden" name="object_id" id="report-location-input" v-model="object_id" />
                                    <input type="hidden" name="operation_date_from" id="report-date-from-input" v-model="operation_date_from" value="" />
                                    <input type="hidden" name="operation_date_to" id="report-date-to-input" v-model="operation_date_to" value="" />
                                    <input type="hidden" name="responsible_receiver_id" id="report-responsible-receiver-input" v-model="responsible_receiver" value="" />
                                    <input type="hidden" name="fuel_tank_id" id="report-fuel-tank-input" v-model="fuelTank" value="" />
                                    <input type="hidden" name="mode" id="report-mode-input" v-model="mode" value="" />
                                    <template>
                                        <div class="row">
                                            <div class="col-md-12">
                                                <label for="">Форма отчёта<span class="star">*</span></label>
                                                <validation-provider rules="required" vid="mode-select"
                                                                     ref="mode-select" v-slot="v">
                                                    <el-select v-model="mode"
                                                               :class="v.classes"
                                                               clearable filterable
                                                               id="mode-select"
                                                               placeholder="Выберите форму отчёта"
                                                    >
                                                        <el-option
                                                            v-for="item in modes"
                                                            :key="item.id"
                                                            :label="item.label"
                                                            :value="item.id">
                                                        </el-option>
                                                    </el-select>
                                                    <div class="error-message">@{{ v.errors[0] }}</div>
                                                </validation-provider>
                                            </div>
                                        </div>
                                        <template v-if="mode === 1">
                                            <div class="row">
                                                <div class="col-md-12">
                                                    <label for="">Ёмкость<span class="star">*</span></label>
                                                    <validation-provider rules="required" vid="fuel-tank-search"
                                                                         ref="fuel-tank-search" v-slot="v">
                                                        <el-select v-model="fuelTank"
                                                                   :class="v.classes"
                                                                   clearable filterable
                                                                   id="fuel-tank-search"
                                                                   :remote-method="searchFuelTanks"
                                                                   @clear="searchFuelTanks('')"
                                                                   remote
                                                                   placeholder="Поиск топливной ёмкости"
                                                        >
                                                            <el-option
                                                                v-for="item in fuelTanks"
                                                                :key="item.id"
                                                                :label="item.name"
                                                                :value="item.id">
                                                            </el-option>
                                                        </el-select>
                                                        <div class="error-message">@{{ v.errors[0] }}</div>
                                                    </validation-provider>
                                                </div>
                                            </div>
                                        </template>
                                        <template v-else-if="mode === 2">
                                            <div class="row">
                                                <div class="col-md-12">
                                                    <label for="">Объект<span class="star">*</span></label>
                                                    <validation-provider rules="required" vid="location-id-select"
                                                                         ref="location-id-select" v-slot="v">
                                                        <el-select v-model="object_id"
                                                                   :class="v.classes"
                                                                   clearable filterable
                                                                   id="location-id-select"
                                                                   :remote-method="search_locations"
                                                                   @clear="search_locations('')"
                                                                   remote
                                                                   placeholder="Поиск объекта"
                                                        >
                                                            <el-option
                                                                v-for="item in objects"
                                                                :key="item.id"
                                                                :label="item.name"
                                                                :value="item.id">
                                                            </el-option>
                                                        </el-select>
                                                        <div class="error-message">@{{ v.errors[0] }}</div>
                                                    </validation-provider>
                                                </div>
                                            </div>
                                        </template>
                                        <template v-else-if="mode === 3">
                                            <div class="row col-md-12">
                                                <div class="col-md-6">
                                                    <label for="">Объект<span class="star">*</span></label>
                                                    <validation-provider rules="required" vid="location-id-select"
                                                                         ref="location-id-select" v-slot="v">
                                                        <el-select v-model="object_id"
                                                                   :class="v.classes"
                                                                   clearable filterable
                                                                   id="location-id-select"
                                                                   :remote-method="search_locations"
                                                                   @clear="search_locations('')"
                                                                   remote
                                                                   placeholder="Поиск объекта"
                                                        >
                                                            <el-option
                                                                v-for="item in objects"
                                                                :key="item.id"
                                                                :label="item.name"
                                                                :value="item.id">
                                                            </el-option>
                                                        </el-select>
                                                        <div class="error-message">@{{ v.errors[0] }}</div>
                                                    </validation-provider>
                                                </div>
                                                <div class="col-md-6">
                                                    <label for="">Ёмкость<span class="star">*</span></label>
                                                    <validation-provider rules="required" vid="fuel-tank-search-by-object"
                                                                         ref="fuel-tank-search-by-object" v-slot="v">
                                                        <el-select v-model="fuelTank"
                                                                   :class="v.classes"
                                                                   clearable filterable
                                                                   id="fuel-tank-search-by-object"
                                                                   :remote-method="searchFuelTanksByObject"
                                                                   @clear="searchFuelTanksByObject('')"
                                                                   remote
                                                                   placeholder="Поиск топливной ёмкости"
                                                        >
                                                            <el-option
                                                                v-for="item in fuelTanks"
                                                                :key="item.id"
                                                                :label="item.name"
                                                                :value="item.id">
                                                            </el-option>
                                                        </el-select>
                                                        <div class="error-message">@{{ v.errors[0] }}</div>
                                                    </validation-provider>
                                                </div>
                                            </div>
                                        </template>
                                        <div class="row">
                                            <div class="col-md-6">
                                                <label for="">Дата начала отчётного периода</label>
                                                <validation-provider v-slot="v" vid="date-from" ref="date-from">
                                                    <el-date-picker
                                                        style="cursor:pointer"
                                                        :class="v.classes"
                                                        v-model="operation_date_from"
                                                        format="dd.MM.yyyy"
                                                        id="date-from"
                                                        value-format="yyyy-MM-dd"
                                                        type="date"
                                                        placeholder="Укажите дату начала отчётного периода"
                                                        :picker-options="dateFromPickerOptions"
                                                    ></el-date-picker>
                                                    <div class="error-message">@{{ v.errors[0] }}</div>
                                                </validation-provider>
                                            </div>
                                            <div class="col-md-6">
                                                <label for="">Дата окончания отчётного периода</label>
                                                <validation-provider v-slot="v" vid="date-to" ref="date-to">
                                                    <el-date-picker
                                                        style="cursor:pointer"
                                                        :class="v.classes"
                                                        v-model="operation_date_to"
                                                        format="dd.MM.yyyy"
                                                        id="date-to"
                                                        value-format="yyyy-MM-dd"
                                                        type="date"
                                                        placeholder="Укажите дату окончания отчётного периода"
                                                        :picker-options="dateToPickerOptions"
                                                    ></el-date-picker>
                                                    <div class="error-message">@{{ v.errors[0] }}</div>
                                                </validation-provider>
                                            </div>
                                        </div>
                                        <div class="row">
                                            <div class="col-md-12">
                                                <label for="">Ответственный за приём отчёта<span
                                                        class="star">*</span></label>
                                                <validation-provider rules="required" vid="responsible-receiver-select"
                                                                     ref="responsible-receiver-select" v-slot="v">
                                                    <el-select v-model="responsible_receiver"
                                                               :class="v.classes"
                                                               clearable filterable
                                                               id="responsible-receiver-select"
                                                               :remote-method="search_responsible_receivers"
                                                               @clear="search_responsible_receivers('')"
                                                               remote
                                                               placeholder="Поиск ответственного за приём отчёта"
                                                    >
                                                        <el-option
                                                            v-for="item in responsible_receivers"
                                                            :key="item.id"
                                                            :label="item.name"
                                                            :value="item.id">
                                                        </el-option>
                                                    </el-select>
                                                    <div class="error-message">@{{ v.errors[0] }}</div>
                                                </validation-provider>
                                            </div>
                                        </div>
                                    </template>
                                </form>
                            </validation-observer>
                        </div>
                    </div>
                </div>
                <div class="modal-footer justify-content-center">
                    <button @click.stop="submit" type="button" class="btn btn-primary">Сформировать</button>
                </div>
            </div>
        </div>
    </div>
    <!-- end modals -->
@endsection
@section('js_footer')
    <script src="{{ mix('js/video.js') }}"></script>
    <script type="text/javascript">
        Vue.component('validation-provider', VeeValidate.ValidationProvider);
        Vue.component('validation-observer', VeeValidate.ValidationObserver);
    </script>
    <script type="text/javascript">
        var vm = new Vue({
            el: '#base',
            router: new VueRouter({
                mode: 'history',
                routes: [],
            }),
            data: {
                PAGE_SIZE: 10,
                SINGLE_USE_FILTERS: ['date_updated_from', 'date_updated_to', 'operation_date_from', 'operation_date_to', 'value_from', 'value_to'],
                DATE_FILTERS: ['date_updated_from', 'date_updated_to', 'operation_date_from', 'operation_date_to'],
                //TODO substitute with blade
                totalItems: {{ $data['total_count'] }},
                currentPage: 1,

                loading: false,
                loadingInstance: {},

                records:
                        {!! json_encode($data['operations']) !!}
                ,
                //search_tf: '',
                filter_attributes: [
                    //{label: '№', value: 'id'},
                    {label: 'Топливная ёмкость №', value: 'tank_number'},
                    {label: 'Дата записи от', value: 'date_updated_from'},
                    {label: 'Дата записи до', value: 'date_updated_to'},
                    {label: 'Дата операции от', value: 'operation_date_from'},
                    {label: 'Дата операции до', value: 'operation_date_to'},
                    {label: 'Вид записи', value: 'type'},
                    {label: 'Объём от, л', value: 'value_from'},
                    {label: 'Объём до, л', value: 'value_to'},
                    {label: 'Заправляемая техника', value: 'our_technic'},
                    {label: 'Поставщик', value: 'contractor'},
                    {label: 'Автор записи', value: 'author'},
                ],
                loaded_filters: [],
                filter_attribute: '',
                filter_value: '',
                filter_custom_name: '',
                types: {!! json_encode(array_values($data['types']))  !!},
                contractors: [],
                authors: [],
                techs: [],
                fuelCapacities: [],
                filters: [],
                window_width: 10000,
            },
            created() {
                if (this.$route.query.page && !Array.isArray(this.$route.query.page)) {
                    this.currentPage = +this.$route.query.page;
                }
                Object.entries(this.$route.query).forEach(entry => {
                    let field = entry[0].split('[')[0];
                    let value = Array.isArray(entry[1]) ? entry[1][0] : entry[1];
                    if (entry[1] && this.filter_attributes_filtered.map(attr => attr.value).indexOf(field) !== -1) {
                        let customName = '';
                        if (field === 'tank_number') {
                            this.promisedSearchFuelCapacities('')//entry[1]
                                .then(() => {
                                    const fuelCapacity = this.fuelCapacities.find(el => el.tank_number == value);
                                    customName = fuelCapacity ? fuelCapacity.tank_number : '';
                                    this.filters.push({
                                        attribute: this.filter_attributes.find(el => el.value === field).label,
                                        field: field,
                                        value: value,
                                        custom_name: customName,
                                    });
                                })
                                .catch((error) => {console.log(error)})
                        } else if (field === 'our_technic') {
                            this.promisedSearchTechs('')//entry[1]
                                .then(() => {
                                    const tech = this.techs.find(el => el.id == value);
                                    customName = tech ? tech.name : '';
                                    this.filters.push({
                                        attribute: this.filter_attributes.find(el => el.value === field).label,
                                        field: field,
                                        value: value,
                                        custom_name: customName,
                                    });
                                })
                                .catch((error) => {console.log(error)})
                        } else if (field === 'type') {
                                    const type = this.types.find(el => el.id == value);
                                    customName = type ? type.name : '';
                                    this.filters.push({
                                        attribute: this.filter_attributes.find(el => el.value === field).label,
                                        field: field,
                                        value: value,
                                        custom_name: customName,
                                    })
                        } else if (field === 'contactor') {
                            const contactor = this.contactors.find(el => el.id == value);
                            customName = contactor ? contactor.name : '';
                            this.filters.push({
                                attribute: this.filter_attributes.find(el => el.value === field).label,
                                field: field,
                                value: value,
                                custom_name: customName,
                            })
                        } else if (field === 'author') {
                            this.promisedSearchAuthors('')//entry[1]
                                .then(() => {
                                    const author = this.authors.find(el => el.id == value);
                                    customName = author ? author.name : '';
                                    this.filters.push({
                                        attribute: this.filter_attributes.find(el => el.value === field).label,
                                        field: field,
                                        value: value,
                                        custom_name: customName,
                                    });
                                })
                                .catch((error) => {console.log(error)})
                        } else if (field === 'contractor') {
                            this.promisedSearchSuppliers('')//entry[1]
                                .then(() => {
                                    const contractor = this.contractors.find(el => el.id == value);
                                    customName = contractor ? contractor.name : '';
                                    this.filters.push({
                                        attribute: this.filter_attributes.find(el => el.value === field).label,
                                        field: field,
                                        value: value,
                                        custom_name: customName,
                                    });
                                })
                                .catch((error) => {console.log(error)})
                        } else {
                            this.filters.push({
                                attribute: this.filter_attributes.find(el => el.value === field).label,
                                field: field,
                                value: value,
                                custom_name: customName,
                            });
                        }
                        this.$forceUpdate();
                    }
                });
            },
            watch: {
                filter_attribute(val) {
                    this.filter_value = '';
                    this.filter_custom_name = '';
                    if (!this.loaded_filters.some(el => el === val)) {
                        switch(val) {
                            case 'author': this.searchAuthors('');
                            break;
                            case 'contractor': this.searchSuppliers('');
                                break;
                            case 'our_technic': this.searchTechs('');
                                break;
                            case 'tank_number': this.searchFuelCapacities('');
                        }
                        this.loaded_filters.push(val);
                    }
                },
                loading(val) {
                    if (val) {
                        this.loadingInstance = ELEMENT.Loading.service({ fullscreen: true });
                    } else {
                        this.loadingInstance.close();
                    }
                }
            },
            mounted() {
                $('#filter-value-tf').on('keypress', function (e) {
                    if(e.which === 13){
                        vm.addFilter();
                    }
                });
                $(window).on('resize', this.handleResize);
                this.handleResize();
            },
            computed: {
                pagerCount() {
                    return this.window_width > 1000 ? 7 : 5;
                },
                smallPager() {
                    return this.window_width < 1000;
                },
                pagerBackground() {
                    return this.window_width > 300;
                },
                filter_attributes_filtered() {
                    return this.filter_attributes.filter(el => {
                        if (this.SINGLE_USE_FILTERS.indexOf(el.value) !== -1 && this.filters.some(filter => filter.field === el.value)) {
                            return false
                        } else {
                            return true;
                        }
                    });
                },
                hasAnyConsumption() {
                    return this.records.some(el => el.our_technic);
                },
                hasAnySupply() {
                    return this.records.some(el => el.contractor);
                },
            },
            methods: {
                addFilter() {
                    if (this.filter_value && this.filter_attribute) {
                        const queryObj = {};
                        this.filters.push({
                            attribute: this.filter_attributes.find(el => el.value === this.filter_attribute).label,
                            field: this.filter_attribute,
                            value: this.filter_value,
                            custom_name: this.filter_custom_name,
                        });
                        const count = Object.keys(this.$route.query).filter(el => el.indexOf(this.filter_attribute) !== -1).length;
                        if (!count) {
                            queryObj[this.filter_attribute] = this.filter_value;
                            this.$router.replace({query: Object.assign({}, this.$route.query, queryObj)}).catch(err => {});
                        } else if (count === 1) {
                            Object.assign(queryObj, this.$route.query);
                            queryObj[this.filter_attribute + '[0]'] = queryObj[this.filter_attribute];
                            delete queryObj[this.filter_attribute];
                            queryObj[this.filter_attribute + `[${count}]`] = this.filter_value;
                            this.$router.replace({query: Object.assign({}, queryObj)}).catch(err => {});
                        } else {
                            queryObj[this.filter_attribute + `[${count}]`] = this.filter_value;
                            this.$router.replace({query: Object.assign({}, this.$route.query, queryObj)}).catch(err => {});
                        }
                        this.filter_value = '';
                        if (this.SINGLE_USE_FILTERS.indexOf(this.filter_attribute) !== -1) {
                            this.filter_attribute = '';
                        }
                        this.resetCurrentPage();
                        this.$forceUpdate();
                    }
                },
                changePage(page) {
                    this.$router.replace({query: Object.assign({}, this.$route.query, {page: page})}).catch(err => {});
                    this.updateFilteredRecords();
                },
                openReportCreate() {
                    createReport.open();
                },
                updateFilteredRecords() {
                    axios.post('{{ route('building::tech_acc::fuel_tank_operations_paginated') }}', {url: vm.$route.fullPath, page: vm.currentPage})
                        .then(response => {
                            vm.records = response.data.fuelTankOperations;
                            vm.totalItems = response.data.fuelTankOperationCount;
                        })
                        .catch(error => console.log(error));
                },
                updateCurrentCustomName() {
                    switch (this.filter_attribute) {
                        case 'tank_number':
                            this.filter_custom_name = this.fuelCapacities.find(el => el.id === this.filter_value) ? this.fuelCapacities.find(el => el.id === this.filter_value).tank_number : ''; break;
                        case 'type':
                            this.filter_custom_name = this.types.find(el => el.id === this.filter_value) ? this.types.find(el => el.id === this.filter_value).name : ''; break;
                        case 'our_technic':
                            this.filter_custom_name = this.techs.find(el => el.id === this.filter_value) ? this.techs.find(el => el.id === this.filter_value).name : ''; break;
                        case 'contractor':
                            this.filter_custom_name = this.contractors.find(el => el.id === this.filter_value) ? this.contractors.find(el => el.id === this.filter_value).name : ''; break;
                        case 'author':
                            this.filter_custom_name = this.authors.find(el => el.id === this.filter_value) ? this.authors.find(el => el.id === this.filter_value).name : ''; break;
                    }
                },
                getFilterValueLabel(filter) {
                    switch(filter.field) {
                        case 'fuel_tank_id':
                        case 'our_technic':
                        case 'author':
                        case 'contractor':
                        case 'type': return filter.custom_name;
                        case 'date_updated_from':
                        case 'date_updated_to':
                        case 'operation_date_from':
                        case 'operation_date_to': return moment(filter.value, 'YYYY-MM-DD').format('DD.MM.YYYY');
                        default: return filter.value;
                    }
                },
                removeFilter(index) {
                    const queryObj = {};
                    Object.assign(queryObj, this.$route.query);
                    const count = Object.keys(this.$route.query).filter(el => el.indexOf(this.filters[index].field) !== -1).length;
                    if (count === 1) {
                        delete queryObj[this.filters[index].field];
                    } else if (count === 2) {
                        const queryIndex = this.$route.query[this.filters[index].field + '[0]'] === this.filters[index].value ? 0 : 1;
                        if (queryIndex === 0) {
                            queryObj[this.filters[index].field] = queryObj[this.filters[index].field + '[1]'];
                            delete queryObj[this.filters[index].field + '[0]'];
                            delete queryObj[this.filters[index].field + '[1]'];
                        } else {
                            queryObj[this.filters[index].field] = queryObj[this.filters[index].field + '[0]'];
                            delete queryObj[this.filters[index].field + '[0]'];
                            delete queryObj[this.filters[index].field + '[1]'];
                        }
                    } else {
                        const queryIndex = Object.entries(this.$route.query).find(query => query[0].split('[')[0] === this.filters[index].field && query[1] === this.filters[index].value)[0].split('[')[1].split(']')[0];
                        if (queryIndex != count - 1) {
                            queryObj[this.filters[index].field + `[${queryIndex}]`] = queryObj[this.filters[index].field + `[${count - 1}]`];
                            delete queryObj[this.filters[index].field + `[${count - 1}]`];
                        } else {
                            delete queryObj[this.filters[index].field + `[${count - 1}]`];
                        }
                    }
                    this.$router.replace({query: Object.assign({}, queryObj)}).catch(err => {});

                    /*if (!Array.isArray(queryObj[this.filters[index].field])) {
                        delete queryObj[this.filters[index].field];
                        this.$router.replace({query: Object.assign({}, queryObj)}).catch(err => {});
                    } else {
                        let newArr = queryObj[this.filters[index].field].slice();
                        newArr.splice(queryObj[this.filters[index].field].findIndex(el => el == this.filters[index].value), 1);
                        queryObj[this.filters[index].field] = newArr;
                        this.$router.replace({query: Object.assign({}, queryObj)}).catch(err => {});
                    }*/
                    this.filters.splice(index, 1);
                    this.resetCurrentPage();
                },
                clearFilters() {
                    this.filters = [];
                    if (this.currentPage !== 1) {
                        this.$router.replace({query: {page: this.currentPage}}).catch(err => {});
                    } else {
                        this.$router.replace({query: {}}).catch(err => {});
                    }
                    this.searchAuthors('');
                    this.searchSuppliers('');
                    this.searchTechs('');
                    this.searchFuelCapacities('');
                    this.resetCurrentPage();
                },
                resetCurrentPage() {
                    this.$router.replace({query: Object.assign({}, this.$route.query, {page: 1})}).catch(err => {});
                    this.currentPage = 1;
                    this.updateFilteredRecords();
                },
                handleResize() {
                    this.window_width = $(window).width();
                },
                addRecord() {
                    if (formRecord.edit_mode) {
                        formRecord.reset();
                    }
                    $('#form_record').modal('show');
                    $('#form_record').focus();
                    $('.modal').css('overflow-y', 'auto');
                },
                showRecord(record_id) {
                    if (!this.loading) {
                        if (record_id) {
                            const record = this.records.find(el => el.id == record_id);
                            if (record) {
                                if (!record.is_loaded) {
                                    this.loading = true;
                                    axios.get('{{ route('building::tech_acc::fuel_tank_operations.show', '') }}' + '/' + record_id)
                                        .then(response => {
                                            const recordIndex = this.records.findIndex(el => el.id == record_id);
                                            this.$set(this.records, recordIndex, response.data.data.operation);
                                            this.records[recordIndex].is_loaded = true;
                                            cardRecord.update(this.records[recordIndex]);
                                            $('#card_record').modal('show');
                                            $('#card_record').focus();
                                            this.loading = false;
                                        })
                                        .catch(error => {
                                            console.log(error);
                                            this.loading = false;
                                        });
                                } else {
                                    const recordIndex = this.records.findIndex(el => el.id == record_id);
                                    cardRecord.update(this.records[recordIndex]);
                                    $('#card_record').modal('show');
                                    $('#card_record').focus();
                                }
                            }
                        }
                        $('.modal').css('overflow-y', 'auto');
                    }
                    $('.modal').css('overflow-y', 'auto');
                },
                editRecord(record) {
                    if (!this.loading) {
                        if (record) {
                            if (!record.is_loaded) {
                                this.loading = true;
                                axios.get('{{ route('building::tech_acc::fuel_tank_operations.show', '') }}' + '/' + record.id)
                                    .then(response => {
                                        const recordIndex = this.records.findIndex(el => el.id == record.id);
                                        this.$set(this.records, recordIndex, response.data.data.operation);
                                        this.records[recordIndex].is_loaded = true;
                                        formRecord.update(this.records[recordIndex]);
                                        $('#form_record').modal('show');
                                        $('#form_record').focus();
                                        this.loading = false;
                                    })
                                    .catch(error => {
                                        console.log(error);
                                        this.loading = false;
                                    });
                            } else {
                                const recordIndex = this.records.findIndex(el => el.id == record.id);
                                formRecord.update(this.records[recordIndex]);
                                $('#form_record').modal('show');
                                $('#form_record').focus();
                                $('.modal').css('overflow-y', 'auto');
                            }
                        }
                        $('.modal').css('overflow-y', 'auto');
                    }
                    $('.modal').css('overflow-y', 'auto');
                },
                removeRecord(id) {
                    swal({
                        title: 'Вы уверены?',
                        text: "Запись будет удалена!",
                        type: 'warning',
                        showCancelButton: true,
                        cancelButtonText: 'Назад',
                        confirmButtonText: 'Удалить',
                    }).then((result) => {
                        if (result.value) {
                            axios.delete('{{ route('building::tech_acc::fuel_tank_operations.destroy', '')}}' + '/' + id)
                                .then((response) => {
                                    this.records.splice(this.records.findIndex(el => el.id === id), 1);
                                    this.totalItems -= 1;
                                    this.hideTooltips();
                                })
                                .catch(error => swal({
                                    type: 'error',
                                    title: "Ошибка удаления ",
                                }));
                        } else {
                            this.hideTooltips();
                        }
                    });
                },
                hideTooltips() {
                    for (let ms = 50; ms <= 1050; ms += 100) {
                        setTimeout(() => {
                            $('[data-balloon-pos]').blur();
                        }, ms);
                    }
                },
                onFocus() {
                    $('.el-input__inner').blur();
                },
                convertDateFormat(dateString, full) {
                    return full ? moment(dateString, 'YYYY-MM-DD HH:mm:ss').format('DD.MM.YYYY HH:mm:ss') :
                        moment(dateString, 'YYYY-MM-DD HH:mm:ss').format('DD.MM.YYYY');
                },
                isWeekendDay(date, format) {
                    return [5, 6].indexOf(moment(date, format).weekday()) !== -1;
                },
                isValidDate(date, format) {
                    return moment(date, format).isValid();
                },
                weekdayDate(date, inputFormat, outputFormat) {
                    return moment(date, inputFormat).format(outputFormat ? outputFormat : 'DD.MM.YYYY dd');
                },
                promisedSearchTechs(query) {
                    return new Promise((resolve, reject) => {
                        if (query) {
                            axios.get('{{ route('building::tech_acc::get_technics') }}', {params: {
                                    q: query,
                                    free_only: false,
                                }})
                                .then(response => {
                                    this.techs = response.data.data.map(el => ({
                                        name: el.category_name + ', ' + el.brand + ' ' + el.model,
                                        id: el.id
                                    }));
                                    resolve(response);
                                })
                                .catch(error => {
                                    console.log(error);
                                    reject(error);
                                });
                        } else {
                            axios.get('{{ route('building::tech_acc::get_technics') }}', {params: {
                                    free_only: false,
                                }})
                                .then(response => {
                                    this.techs = response.data.data.map(el => ({
                                        name: el.category_name + ', ' + el.brand + ' ' + el.model,
                                        id: el.id
                                    }));
                                    resolve(response);
                                })
                                .catch(error => {
                                    console.log(error);
                                    reject(error);
                                });
                        }
                    });
                },
                searchTechs(query) {
                    if (query) {
                        //TODO change route
                        axios.get('{{ route('building::tech_acc::get_technics') }}', {params: {
                                q: query,
                                free_only: false,
                            }})
                            .then(response => this.techs = response.data.data.map(el => ({
                                name: el.category_name + ', ' + el.brand + ' ' + el.model,
                                id: el.id
                            })))
                            .catch(error => console.log(error));
                    } else {
                        //TODO change route
                        axios.get('{{ route('building::tech_acc::get_technics') }}', {params: {
                                q: query,
                                free_only: false,
                            }})
                            .then(response => this.techs = response.data.data.map(el => ({
                                name: el.category_name + ', ' + el.brand + ' ' + el.model,
                                id: el.id
                            })))
                            .catch(error => console.log(error));
                    }
                },
                promisedSearchAuthors(query) {
                    return new Promise((resolve, reject) => {
                        if (query) {
                            axios.get('{{ route('users::get_users_for_tech_tickets') }}', {params: {
                                    q: query
                                }})
                                .then(response => {
                                    this.authors = response.data.map(el => ({ name: el.label, id: el.code }));
                                    resolve(response);
                                })
                                .catch(error => {
                                    console.log(error);
                                    reject(error);
                                });
                        } else {
                            axios.get('{{ route('users::get_users_for_tech_tickets') }}')
                                .then(response => {
                                    this.authors = response.data.map(el => ({ name: el.label, id: el.code }));
                                    resolve(response);
                                })
                                .catch(error => {
                                    console.log(error);
                                    reject(error);
                                });
                        }
                    });
                },
                searchAuthors(query) {
                    if (query) {
                        //TODO change route
                        axios.get('{{ route('users::get_users_for_tech_tickets') }}', {params: {
                                q: query,
                            }})
                            .then(response => this.authors = response.data.map(el => ({ name: el.label, id: el.code })))
                            .catch(error => console.log(error));
                    } else {
                        //TODO change route
                        axios.get('{{ route('users::get_users_for_tech_tickets') }}')
                            .then(response => this.authors = response.data.map(el => ({ name: el.label, id: el.code })))
                            .catch(error => console.log(error));
                    }
                },
                promisedSearchSuppliers(query) {
                    return new Promise((resolve, reject) => {
                        if (query) {
                            axios.get('/projects/ajax/get-contractors', {params: {
                                    q: query
                                }})
                                .then(response => {
                                    this.contractors = response.data.results.map(el => ({ name: el.text, id: el.id }));
                                    resolve(response);
                                })
                                .catch(error => {
                                    console.log(error);
                                    reject(error);
                                });
                        } else {
                            axios.get('/projects/ajax/get-contractors')
                                .then(response => {
                                    this.contractors = response.data.results.map(el => ({ name: el.text, id: el.id }));
                                    resolve(response);
                                })
                                .catch(error => {
                                    console.log(error);
                                    reject(error);
                                });
                        }
                    });
                },
                searchSuppliers(query) {
                    if (query) {
                        //TODO change route
                        axios.get('/projects/ajax/get-contractors', {params: {q: query}})
                            .then(response => this.contractors = response.data.results.map(el => ({ name: el.text, id: el.id })))
                            .catch(error => console.log(error));
                    } else {
                        //TODO change route
                        axios.get('/projects/ajax/get-contractors')
                            .then(response => this.contractors = response.data.results.map(el => ({ name: el.text, id: el.id })))
                            .catch(error => console.log(error));
                    }
                },
                promisedSearchFuelCapacities(query) {
                    return new Promise((resolve, reject) => {
                        if (query) {
                            axios.post('{{ route('building::tech_acc::get_fuel_tanks') }}', {q: query})
                                .then(response => {
                                    this.fuelCapacities = response.data.map(el => ({ name: el.name, id: el.id }));
                                    resolve(response);
                                })
                                .catch(error => {
                                    console.log(error);
                                    reject(error);
                                });
                        } else {
                            axios.post('{{ route('building::tech_acc::get_fuel_tanks') }}')
                                .then(response => {
                                    this.fuelCapacities = response.data.map(el => ({ name: el.name, id: el.id }));
                                    resolve(response);
                                })
                                .catch(error => {
                                    console.log(error);
                                    reject(error);
                                });
                        }
                    });
                },
                searchFuelCapacities(query) {
                    if (query) {
                        //TODO change route
                        axios.post('{{ route('building::tech_acc::get_fuel_tanks') }}', {q: query})
                            .then(response => this.fuelCapacities = response.data.map(el => ({ name: el.tank_number, id: el.tank_number })))
                            .catch(error => console.log(error));
                    } else {
                        //TODO change route
                        axios.post('{{ route('building::tech_acc::get_fuel_tanks') }}')
                            .then(response => this.fuelCapacities = response.data.map(el => ({ name: el.tank_number, id: el.tank_number })))
                            .catch(error => console.log(error));
                    }
                },
                updateRecords(record) {
                    const record_index = this.records.findIndex(el => el.id === record.id);
                    if (record_index !== -1) {
                        this.$set(this.records, record_index, record);
                    } else {
                        this.totalItems += 1;
                        this.records.unshift(record);
                    }
                }
            }
        });
    </script>
    <script type="text/javascript">
        var cardRecord = new Vue ({
            el:'#card_record',
            data: {
                record: {},
                window_width: 10000,
                player1: null,
                player2: null,

                PAGE_SIZE: 10,
                totalItems: 20,
                currentPage: 1,

                //TODO delete after backend is complete
                // dummy_operations: [
                //     {
                //         operation_date: '2020-01-25 16:23:23',
                //         author: {
                //             full_name: 'Случайное Довольно Длинное Имя, Прямо Редкость',
                //         },
                //         changed_fields_parsed: {
                //             old_values: {
                //                 'value': '10000.0',
                //                 'fuel_tank_number': '46',
                //                 'date': '2020-01-24',
                //                 'keke': '123',
                //             },
                //             new_values: {
                //                 'value': '20000.0',
                //                 'fuel_tank_number': '47',
                //                 'date': '2020-01-23',
                //                 'keke': '321',
                //             },
                //         }
                //     },
                //     {
                //         operation_date: '2020-01-25 16:23:23',
                //         author: {
                //             full_name: 'Дядя Гриша',
                //         },
                //         changed_fields_parsed: {
                //             old_values: {
                //                 'value': '10000.0',
                //                 'fuel_tank_number': '46',
                //                 'date': '2020-01-24',
                //                 'keke': '123',
                //             },
                //             new_values: {
                //                 'value': '20000.0',
                //                 'fuel_tank_number': '47',
                //                 'date': '2020-01-23',
                //                 'keke': '321',
                //             },
                //         }
                //     }
                // ]
            },
            computed: {
                cardTitle() {
                    return this.record.type != 3 ? (this.record.contractor ?
                        ('Поставка топлива на ' + (this.record.object ? this.record.object.name : '')) :
                        ('Заправка топлива в ' + (this.record.our_technic ? this.record.our_technic.name : ''))) :
                        'Ручное изменение уровня топлива\nтопливной ёмкости ' + (this.record.fuel_tank ? this.record.fuel_tank.tank_number : '');
                },
                pagerCount() {
                    return this.window_width > 1000 ? 7 : 5;
                },
                smallPager() {
                    return this.window_width < 1000;
                },
                pagerBackground() {
                    return this.window_width > 300;
                },
            },
            mounted() {
                $(window).on('resize', this.handleResize);
                $('#card_record').on('hide.bs.modal', () => {
                    if (this.player1) {
                        this.player1.pause();
                        this.player1.dispose();
                        this.player1 = null;
                    }
                    if (this.player2) {
                        this.player2.pause();
                        this.player2.dispose();
                        this.player2 = null;
                    }
                });
                this.handleResize();
            },
            methods: {
                update(record) {
                    this.record = record;
                    if (this.player1) {
                        this.player1.dispose();
                        this.player1 = null;
                    }
                    if (this.player2) {
                        this.player2.dispose();
                        this.player2 = null;
                    }
                    if (this.record.videos && this.record.videos.length > 0) {
                        $('#video-section').append('<video id="videoPlayer1" class="video-js"></video>');
                        this.player1 = videojs('#videoPlayer1', {
                            autoplay: false,
                            controls: true,
                            aspectRatio: '16:9',
                            sources: [{
                                src: this.record.videos[0].source_link,
                                type: 'video/mp4'
                            }]
                        });
                        if (this.record.videos.length > 1) {
                            $('#video-section').append('<video id="videoPlayer2" class="video-js mt-2"></video>');
                            this.player2 = videojs('videoPlayer2', {
                                autoplay: false,
                                controls: true,
                                aspectRatio: '16:9',
                                sources: [{
                                    src: this.record.videos[1].source_link,
                                    type: 'video/mp4'
                                }]
                            });
                        }
                        $('.video-js').removeClass('d-none');
                    } else {
                        $('.video-js').addClass('d-none');
                    }
                    this.changePage(1);
                },
                edit_record_modal_show() {
                    formRecord.update(this.record);
                    $('#card_record').modal('hide');
                    $('#form_record').modal('show');
                    $('.modal').css('overflow-y', 'auto');
                    $('#form_record').focus();
                },
                isWeekendDay(date, format) {
                    return [5, 6].indexOf(moment(date, format).weekday()) !== -1;
                },
                isValidDate(date, format) {
                    return moment(date, format).isValid();
                },
                weekdayDate(date, inputFormat, outputFormat) {
                    return moment(date, inputFormat).format(outputFormat ? outputFormat : 'DD.MM.YYYY dd');
                },
                changePage(page) {
                    this.currentPage = page;
                    let that = this;
                    let route = '{{ route('building::tech_acc::fuel_tank_operations_paginated') }}';

                    /*axios.post(makeUrl(route, []), {
                        fuel_tank_id: that.fuel.id,
                        page: page,
                    })
                        .then((response) => {
                            that.$set(that.record, 'operations', response.data.fuelTankOperations);
                            that.totalItems = response.data.fuelTankOperationCount;
                        })
                        //TODO add actual error handler
                        .catch(error => {
                            console.log(error);
                        });*/
                },
                handleResize() {
                    this.window_width = $(window).width();
                },
                convertDateFormat(dateString, full) {
                    return vm.convertDateFormat(dateString, full);
                },
                getFieldLabel(field) {
                    switch (field) {
                        case 'value': return 'Объём';
                        case 'operation_date': return 'Дата операции';
                        case 'our_technic_id': return 'Техника';
                        case 'fuel_tank_id': return 'Топливная ёмкость';
                        case 'contractor_id': return 'Поставщик';
                        case 'owner_id': return 'Собственник';
                        case 'description': return 'Комментарий';
                        case 'object_id': return 'Объект';
                        default: return 'Неизвестная характеристика';
                    }
                }
            },
            beforeDestroy() {
                if (this.player1) {
                    this.player1.dispose();
                    this.player1 = null;
                }
                if (this.player2) {
                    this.player2.dispose();
                    this.player2 = null;
                }
            }
        });

        var formRecord = new Vue({
            el: '#form_record',
            data: {
                loading: false,

                type: 'Поставка',
                record: {},
                edit_mode: false,
                observer_key: 1,

                description: '',
                owner: '',
                date: '',
                value: 0,
                contractor_id: null,
                object_id: null,
                fuel_tank_id: null,
                our_technic_id: null,

                loaded_selects: [],
                fuel_capacities: [],
                objects: [],
                contractors: [],
                techs: [],
                owners: {!! json_encode(array_values($data['owners']))  !!},

                datePickerOptions: {
                    firstDayOfWeek: 1,
                    disabledDate: date => date > moment().startOf('date'),
                },

                file_list_docs: [],
                file_list_video: [],
                files_to_remove: [],
                files_uploaded_docs: [],
                files_uploaded_video: [],

                window_width: 10000,

                was_type_changed: false,

            },
            mounted() {
                $(window).on('resize', this.handleResize);
                this.handleResize();
            },
            methods: {
                firstLoad(field) {
                    if (!this.loaded_selects.some(el => el === field)) {
                        switch(field) {
                            case 'object_id': this.searchObjects('');
                                break;
                            case 'fuel_tank_id': this.searchFuelCapacities('');
                                break;
                            case 'contractor_id': this.searchSuppliers('');
                                break;
                            case 'our_technic_id': this.searchTechs('');
                        }
                        this.loaded_selects.push(field);
                    }
                },
                removeFiles(file_ids, record_id) {
                    if (file_ids.length > 0) {
                        file_ids.forEach(file_id => {
                            if (file_id) {
                                axios.delete('{{ route('file_entry.destroy', '') }}' + '/' + file_id)
                                    .then(() => {
                                        const record = vm.records.find(record => record.id === record_id);
                                        if (record) {
                                            const [record_docs, record_video] = [record.not_videos, record.videos];
                                            if (record_docs) {
                                                if (record_docs.findIndex(doc => doc.id === file_id) !== -1) {
                                                    record_docs.splice(record_docs.findIndex(doc => doc.id === file_id), 1)
                                                }
                                            }
                                            if (record_video) {
                                                if (record_video.findIndex(doc => doc.id === file_id) !== -1) {
                                                    record_video.splice(record_video.findIndex(doc => doc.id === file_id), 1)
                                                }
                                            }
                                        }
                                    })
                                    //TODO add actual error handler
                                    .catch(error => console.log(error));
                            }
                        });
                    }
                },
                update(record) {
                    this.searchObjects('');
                    this.searchFuelCapacities('');
                    this.searchSuppliers('');
                    this.searchTechs('');
                    this.record = record;
                    this.type = record.owner_id ? 'Поставка' : 'Заправка';
                    this.description = record.description;
                    this.owner = record.owner;
                    this.date = new Date(record.operation_date);
                    this.value = record.value;
                    this.contractor_id = record.contractor ? record.contractor.full_name + ', ИНН: ' + (record.contractor.inn ? record.contractor.inn : '') : '';
                    this.fuel_tank_id = record.fuel_tank ? record.fuel_tank.name : '';
                    //Using full object name instead of id because el-select displays initial value as initial label
                    this.object_id = record.object ? `${record.object.name}. ${record.object.address}` : '';
                    this.our_technic_id = record.our_technic ? record.our_technic.name : '';

                    this.files_to_remove = [];
                    this.files_uploaded_docs = [];
                    this.files_uploaded_video = [];
                    if (this.$refs.doc_upload) {
                        this.$refs.doc_upload.clearFiles();
                    }
                    if (this.$refs.video_upload) {
                        this.$refs.video_upload.clearFiles();
                    }

                    this.file_list_docs = record.not_videos ? record.not_videos.map(doc => {
                        doc.name = doc.original_filename;
                        return doc;
                    }) : [];
                    this.file_list_video = record.videos ? record.videos.map(video => {
                        video.name = video.original_filename;
                        return video;
                    }) : [];

                    this.was_type_changed = false;
                    this.edit_mode = true;
                    this.$nextTick(() => {
                        this.$refs.observer.reset();
                    });
                    $('#doc-upload-section').removeClass('failed');
                    $('#doc-upload-section-error').addClass('d-none');
                    $('#video-upload-section').removeClass('failed');
                    $('#video-upload-section-error').addClass('d-none');
                },
                reset() {
                    this.searchObjects('');
                    this.searchFuelCapacities('');
                    this.searchSuppliers('');
                    this.searchTechs('');

                    this.record = {};
                    this.type = 'Поставка';

                    this.description = '';
                    this.owner = '';
                    this.date = '';
                    this.value = 0;
                    this.contractor_id = null;
                    this.object_id = null;
                    this.fuel_tank_id = null;
                    this.our_technic_id = null;

                    this.file_list_docs = [];
                    this.file_list_video = [];
                    this.files_to_remove = [];
                    this.files_uploaded_docs = [];
                    this.files_uploaded_video = [];
                    if (this.$refs.doc_upload) {
                        this.$refs.doc_upload.clearFiles();
                    }
                    if (this.$refs.video_upload) {
                        this.$refs.video_upload.clearFiles();
                    }

                    this.edit_mode = false;
                    this.observer_key += 1;
                    this.was_type_changed = false;

                    $('#doc-upload-section').removeClass('failed');
                    $('#doc-upload-section-error').addClass('d-none');
                    $('#video-upload-section').removeClass('failed');
                    $('#video-upload-section-error').addClass('d-none');
                    this.$nextTick(() => {
                        this.$refs.observer.reset();
                    });
                },
                onFocus() {
                    $('.el-input__inner').blur();
                },
                loadFuelTankLocation() {
                    if (this.fuel_tank_id) {
                        const linkedObject = this.fuel_capacities.find(el => el.id === this.fuel_tank_id).object;
                        this.promisedSearchObjects(linkedObject.name)
                            .then(() => {
                                const loadedLinkedObject = this.objects.find(el => el.id === String(linkedObject.id));
                                this.promisedSearchObjects()
                                    .then(() => {
                                        if (!this.objects.some(el => el.id === String(linkedObject.id))) {
                                            this.objects.unshift(loadedLinkedObject);
                                        }
                                        this.object_id = String(linkedObject.id);
                                    })
                                    .catch(() => {});
                            })
                            .catch(() => {});
                    }
                },
                handleResize() {
                    this.window_width = $(window).width();
                },
                promisedSearchObjects(query) {
                    return new Promise((resolve, reject) => {
                        if (query) {
                            axios.get('{{ route('building::mat_acc::report_card::get_objects') }}', {params: {
                                    q: query
                                }})
                                .then(response => {
                                    this.objects = response.data.map(el => ({ name: el.label, id: el.code }));
                                    resolve(response);
                                })
                                .catch(error => {
                                    console.log(error);
                                    reject(error);
                                });
                        } else {
                            axios.get('{{ route('building::mat_acc::report_card::get_objects') }}')
                                .then(response => {
                                    this.objects = response.data.map(el => ({ name: el.label, id: el.code }));
                                    resolve(response);
                                })
                                .catch(error => {
                                    console.log(error);
                                    reject(error);
                                });
                        }
                    });
                },
                searchObjects(query) {
                    if (query) {
                        axios.post('{{ route('building::mat_acc::report_card::get_objects') }}', {q: query})
                            .then(response => this.objects = response.data.map(el => ({ name: el.label, id: el.code })))
                            .catch(error => console.log(error));
                    } else {
                        axios.post('{{ route('building::mat_acc::report_card::get_objects') }}')
                            .then(response => this.objects = response.data.map(el => ({ name: el.label, id: el.code })))
                            .catch(error => console.log(error));
                    }
                },
                searchFuelCapacities(query) {
                    if (query) {
                        //TODO change route
                        axios.post('{{ route('building::tech_acc::get_fuel_tanks') }}', {q: query})
                            .then(response => this.fuel_capacities = response.data.map(el => ({ name: el.name, id: el.id, object: el.object })))
                            .catch(error => console.log(error));
                    } else {
                        //TODO change routee
                        axios.post('{{ route('building::tech_acc::get_fuel_tanks') }}')
                            .then(response => this.fuel_capacities = response.data.map(el => ({ name: el.name, id: el.id, object: el.object })))
                            .catch(error => console.log(error));
                    }
                },
                searchSuppliers(query) {
                    if (query) {
                        //TODO change route
                        axios.get('/projects/ajax/get-contractors', {params: {q: query}})
                            .then(response => this.contractors = response.data.results.map(el => ({ name: el.text, id: el.id })))
                            .catch(error => console.log(error));
                    } else {
                        //TODO change route
                        axios.get('/projects/ajax/get-contractors')
                            .then(response => this.contractors = response.data.results.map(el => ({ name: el.text, id: el.id })))
                            .catch(error => console.log(error));
                    }
                },
                searchTechs(query) {
                    if (query) {
                        //TODO change route
                        axios.get('{{ route('building::tech_acc::get_technics') }}', {params: {q: query}})
                            .then(response => this.techs = response.data.data.map(el => ({ name: el.name, id: el.id })))
                            .catch(error => console.log(error));
                    } else {
                        //TODO change route
                        axios.get('{{ route('building::tech_acc::get_technics') }}')
                            .then(response => this.techs = response.data.data.map(el => ({ name: el.name, id: el.id })))
                            .catch(error => console.log(error));
                    }
                },
                handleRemoveDoc(file, fileList) {
                    if (file.hasOwnProperty('response')) {
                        this.files_to_remove.push(...file.response.data.map(el => el.id));
                        this.files_uploaded_docs.splice(this.files_uploaded_docs.findIndex(el => el.id === file.response.data[0].id), 1);
                    } else {
                        this.files_to_remove.push(file.id);
                        this.file_list_docs.splice(this.file_list_docs.findIndex(el => el.id === file.id), 1);
                    }
                },
                handleRemoveVideo(file, fileList) {
                    if (file.hasOwnProperty('response')) {
                        this.files_to_remove.push(...file.response.data.map(el => el.id));
                        this.files_uploaded_video.splice(this.files_uploaded_video.findIndex(el => el.id === file.response.data[0].id), 1);
                    } else {
                        this.files_to_remove.push(file.id);
                        this.file_list_video.splice(this.file_list_video.findIndex(el => el.id === file.id), 1);
                    }
                },
                handleSuccessDoc(response, file, fileList) {
                    file.url = file.response.data[0] ? '{{ asset('storage/docs/tech_accounting/') }}' + '/' + file.response.data[0].filename : '#';
                    this.files_uploaded_docs.push(...file.response.data.map(el => el));
                    $('#doc-upload-section').removeClass('failed');
                    $('#doc-upload-section-error').addClass('d-none');
                },
                handleSuccessVideo(response, file, fileList) {
                    file.url = file.response.data[0] ? '{{ asset('storage/docs/tech_accounting/') }}' + '/' + file.response.data[0].filename : '#';
                    this.files_uploaded_video.push(...file.response.data.map(el => el));
                    if (this.files_uploaded_video.concat(this.file_list_video).length > 1) {
                        $('#video-upload-section').removeClass('failed');
                        $('#video-upload-section-error').addClass('d-none');
                    }
                },
                handleError(error, file, fileList) {
                    let message = 'Произошла непредвиденная ошибка';
                    if (error.response) {
                        message = error.response.data.message;
                        let errors = error.response.data.errors;
                    }
                    swal({
                        type: 'error',
                        message: 'Ошибка данных',
                        html: message,
                    });
                },
                handlePreview(file) {
                    window.open(file.url ? file.url : '{{ asset('storage/docs/tech_accounting/') }}' + '/' + file.filename, '_blank');
                    $('#form_record').focus();
                },
                handleExceedDoc(files, fileList) {
                    this.$message.warning(`Невозможно загрузить ${files.length} файлов. Вы можете загрузить еще ${10 - fileList.length} файлов`);
                },
                handleExceedVideo(files, fileList) {
                    this.$message.warning(`Невозможно загрузить ${files.length} файлов. Вы можете загрузить только 1 файл`);
                },
                beforeUploadDoc(file) {
                    const FORBIDDEN_EXTENSIONS = ['exe'];
                    const FILE_MAX_LENGTH = 50_000_000;
                    const nameParts = file.name.split('.');
                    if (FORBIDDEN_EXTENSIONS.indexOf(nameParts[nameParts.length - 1]) !== -1) {
                        this.$message.warning(`Ошибка загрузки файла. Файл не должен быть исполняемым`);
                        return false;
                    }
                    if (file.size > FILE_MAX_LENGTH) {
                        this.$message.warning(`Ошибка загрузки файла. Размер файла не должен превышать 50Мб`);
                        return false;
                    }
                    return true;
                },
                beforeUploadVideo(file) {
                    const ALLOWED_EXTENSIONS = ['mp4'];
                    const FILE_MAX_LENGTH = 300_000_000;
                    const nameParts = file.name.split('.');
                    if (ALLOWED_EXTENSIONS.indexOf(nameParts[nameParts.length - 1]) === -1) {
                        this.$message.warning(`Ошибка загрузки файла. Допустимые форматы: mp4.`);
                        return false;
                    }
                    if (file.size > FILE_MAX_LENGTH) {
                        this.$message.warning(`Ошибка загрузки файла. Размер файла не должен превышать 300Мб`);
                        return false;
                    }
                    return true;
                },
                submit() {
                    const ERRORS_TYPE_1_ONLY= ['contractor-select', 'owner-select'];
                    const ERRORS_TYPE_2_ONLY= ['our_technic-select'];
                    this.$refs.observer.validate().then(success => {
                        let error = false;
                        if (this.type === 'Поставка') {
                            if (this.files_uploaded_docs.concat(this.file_list_docs).length > 0) {
                                $('#doc-upload-section').removeClass('failed');
                                $('#doc-upload-section-error').addClass('d-none');
                            } else {
                                $('#doc-upload-section').addClass('failed');
                                $('#doc-upload-section-error').removeClass('d-none');
                                error = true;
                            }
                        }
                        if (this.files_uploaded_video.concat(this.file_list_video).length > 1) {
                            $('#video-upload-section').removeClass('failed');
                            $('#video-upload-section-error').addClass('d-none');
                        } else {
                            $('#video-upload-section').addClass('failed');
                            $('#video-upload-section-error').removeClass('d-none');
                            error = true;
                        }
                        if (error) {
                            return;
                        }
                        if (!success) {
                            const error_field_vids = this.type === 'Поставка' ?
                                Object.keys(this.$refs.observer.errors).filter(el => this.$refs[el].errors.length > 0).filter(el => ERRORS_TYPE_2_ONLY.indexOf(el) === -1) :
                                Object.keys(this.$refs.observer.errors).filter(el => this.$refs[el].errors.length > 0).filter(el => ERRORS_TYPE_1_ONLY.indexOf(el) === -1);
                            const error_field_vid = error_field_vids.find(el => this.$refs[el].errors.length > 0);
                            if (error_field_vid) {
                                $('.modal').animate({
                                    scrollTop: $('#' + error_field_vid).offset().top
                                }, 1200);
                                $('#' + error_field_vid).focus();
                                return;
                            }
                        }
                        if (!this.loading) {
                            if (!this.edit_mode) {
                                this.loading = true;
                                axios.post('{{ route('building::tech_acc::fuel_tank_operations.store') }}', {
                                    type: (this.type == 'Поставка') ? '1' : '2',
                                    description: this.description,
                                    owner_id: this.type == 'Поставка' && this.owners.findIndex(el => el === this.owner) > -1 ? this.owners.findIndex(el => el === this.owner) + 1 : null,
                                    contractor_id: (this.type == 'Поставка') ? this.contractor_id : null,
                                    operation_date: moment(this.date, 'DD.MM.YYYY').startOf('day').add(8, 'hours'),
                                    value: this.value,
                                    object_id: this.object_id,
                                    fuel_tank_id: this.fuel_tank_id,
                                    our_technic_id: (this.type == 'Поставка') ? null : this.our_technic_id,
                                    file_ids: (this.type == 'Поставка') ? this.files_uploaded_docs.map(file => file.id)
                                            .concat(this.files_uploaded_video.map(file => file.id))
                                            .concat(this.file_list_docs.map(file => file.id))
                                            .concat(this.file_list_video.map(file => file.id)) :
                                        this.files_uploaded_video.map(file => file.id)
                                            .concat(this.file_list_video.map(file => file.id)),
                                })
                                    .then((response) => {
                                        vm.updateRecords(response.data.data.operation);
                                        this.removeFiles(this.files_to_remove, response.data.data.operation.id);
                                        swal({
                                            type: 'success',
                                            title: "Запись была создана",
                                        }).then(() => {
                                            $('#form_record').modal('hide');
                                            $('.modal').css('overflow-y', 'auto');
                                            this.reset();
                                        });
                                        this.loading = false;
                                    })
                                    .catch(error => {
                                        this.handleError(error);
                                        this.loading = false;
                                    });
                            } else {
                                this.loading = true;
                                let route = '{{ route('building::tech_acc::fuel_tank_operations.update', ["ID_TO_SUBSTITUTE"]) }}';

                                axios.put(makeUrl(route, [this.record.id]), {
                                    type: (this.type == 'Поставка') ? '1' : '2',
                                    description: this.description,
                                    owner_id: this.type == 'Поставка' && this.owners.findIndex(el => el === this.owner) > -1 ? this.owners.findIndex(el => el === this.owner) + 1 : null,
                                    contractor_id: (this.type == 'Поставка') ? (String(this.contractor_id).indexOf('ИНН') !== -1 ? this.record.contractor_id : this.contractor_id) : null,
                                    operation_date: moment(this.date, 'DD.MM.YYYY').startOf('day').add(8, 'hours'),
                                    value: this.value,
                                    object_id: String(this.object_id).indexOf('. ') !== -1 ? this.record.object_id : this.object_id,
                                    fuel_tank_id: String(this.fuel_tank_id).indexOf('Топливная') !== -1 ? this.record.fuel_tank_id : this.fuel_tank_id,
                                    our_technic_id: (this.type == 'Поставка') ? null : (String(this.our_technic_id).indexOf(' ') !== -1 ? this.record.our_technic_id : this.our_technic_id),
                                    file_ids: (this.type == 'Поставка') ? this.files_uploaded_docs.map(file => file.id)
                                            .concat(this.files_uploaded_video.map(file => file.id))
                                            .concat(this.file_list_docs.map(file => file.id))
                                            .concat(this.file_list_video.map(file => file.id)) :
                                        this.files_uploaded_video.map(file => file.id)
                                            .concat(this.file_list_video.map(file => file.id)),
                                })
                                    .then((response) => {
                                        vm.updateRecords(response.data.data.operation);
                                        this.removeFiles(this.files_to_remove, response.data.data.operation.id);
                                        swal({
                                            type: 'success',
                                            title: "Запись была обновлена",
                                        }).then(() => {
                                            $('#form_record').modal('hide');
                                            $('.modal').css('overflow-y', 'auto');
                                            this.reset();
                                        });
                                        this.loading = false;
                                    })
                                    //TODO add actual error handler
                                    .catch(error => {
                                        this.handleError(error);
                                        this.loading = false;
                                    });
                            }
                        }
                    });
                },
            }
        });

        var createReport = new Vue({
            el: '#report-create',
            data: {
                observer_key: 1,
                mode: '',

                objects: [],
                responsible_receivers: [],
                fuelTanks: [],

                opened: false,
                responsible_receiver: '',
                object_id: '',
                fuelTank: '',
                operation_date_from: '',
                operation_date_to: '',

                dateFromPickerOptions: {
                    firstDayOfWeek: 1,
                    disabledDate: date =>
                        (date > moment().startOf('date')) ||
                        (createReport.operation_date_to ? (date > moment(createReport.operation_date_to, "YYYY-MM-DD")) : false)
                },

                dateToPickerOptions: {
                    firstDayOfWeek: 1,
                    disabledDate: date =>
                        (date > moment().startOf('date')) ||
                        (createReport.operation_date_from ? (date < moment(createReport.operation_date_from, "YYYY-MM-DD")) : false)
                },
                modes: [
                    {id: 1, label: 'По ёмкости'},
                    {id: 2, label: 'По объекту'},
                    {id: 3, label: 'По ёмкости и объекту'},
                ],
            },
            watch: {
                object_id() {
                    if (this.mode === 3) {
                        this.searchFuelTanksByObject('');
                    }
                },
            },
            methods: {
                open() {
                    if (!this.opened) {
                        this.search_responsible_receivers('');
                        this.search_locations('');
                        this.searchFuelTanks('');
                        this.opened = true;
                    }
                },
                search_responsible_receivers(query) {
                    if (query) {
                        axios.get('{{ route('users::get_users_for_tech_tickets') }}', {params: {
                                q: query,
                            }})
                            .then(response => this.responsible_receivers = response.data.map(el => ({
                                name: el.label,
                                id: el.code
                            })))
                            .catch(error => console.log(error));
                    } else {
                        axios.get('{{ route('users::get_users_for_tech_tickets') }}')
                            .then(response => this.responsible_receivers = response.data.map(el => ({
                                name: el.label,
                                id: el.code
                            })))
                            .catch(error => console.log(error));
                    }
                },
                search_locations(query) {
                    if (query) {
                        axios.post('{{ route('building::mat_acc::report_card::get_objects') }}', {q: query})
                            .then(response => this.objects = response.data.map(el => ({ name: el.label, id: el.code })))
                            .catch(error => console.log(error));
                    } else {
                        axios.post('{{ route('building::mat_acc::report_card::get_objects') }}')
                            .then(response => this.objects = response.data.map(el => ({ name: el.label, id: el.code })))
                            .catch(error => console.log(error));
                    }
                },
                searchFuelTanks(query) {
                    if (query) {
                        axios.post('{{ route('building::tech_acc::get_fuel_tanks') }}', {q: query})
                            .then(response => this.fuelTanks = response.data.map(el => ({ name: el.name, id: el.id })))
                            .catch(error => console.log(error));
                    } else {
                        axios.post('{{ route('building::tech_acc::get_fuel_tanks') }}')
                            .then(response => this.fuelTanks = response.data.map(el => ({ name: el.name, id: el.id })))
                            .catch(error => console.log(error));
                    }
                },
                searchFuelTanksByObject(query) {
                    if (query) {
                        axios.post('{{ route('building::tech_acc::get_fuel_tanks_by_object') }}', {q: query, object_id: this.object_id})
                            .then(response => this.fuelTanks = response.data.map(el => ({ name: el.name, id: el.id })))
                            .catch(error => console.log(error));
                    } else {
                        axios.post('{{ route('building::tech_acc::get_fuel_tanks_by_object') }}', {object_id: this.object_id})
                            .then(response => this.fuelTanks = response.data.map(el => ({ name: el.name, id: el.id })))
                            .catch(error => console.log(error));
                    }
                },
                handleError(error, file, fileList) {
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
                submit() {
                    this.$refs.observer.validate().then(success => {
                        if (!success) {
                            const error_field_vid = Object.keys(this.$refs.observer.errors).find(el => this.$refs[el].errors.length > 0);
                            /*$('.modal').animate({
                                scrollTop: $('#' + error_field_vid).offset().top
                            }, 1200);*/
                            $('#' + error_field_vid).focus();
                            return;
                        }

                        axios.post('{{ route('building::tech_acc::get_fuel_tank_operations') }}', {
                            object_id: this.object_id,
                            operation_date_from: this.operation_date_from,
                            operation_date_to: this.operation_date_to,
                            responsible_receiver_id: this.responsible_receiver,
                            fuel_tank_id: this.fuelTank,
                        }).then(response => {
                            if (response.data === 0) {
                                swal({
                                    title: 'Внимание',
                                    text: 'Не найдено операций по указанным фильтрам',
                                    type: 'warning',
                                });
                                this.reset();
                            } else {
                                $('#report-create').modal('hide');
                                $('.modal').css('overflow-y', 'auto');
                                $('#report-create-form').submit();
                                this.reset();
                            }
                        }).catch(error => console.log(error));
                    });
                },
                reset() {
                    this.search_responsible_receivers('');
                    this.search_locations('');
                    this.observer_key += 1;
                    this.responsible_receiver = '';
                    this.object_id = '';
                    this.fuelTank = '';
                    this.mode = '';
                    this.operation_date_from = '';
                    this.operation_date_to = '';
                    this.$nextTick(() => {
                        this.$refs.observer.reset();
                    });
                },
            },
        });
    </script>
@endsection
