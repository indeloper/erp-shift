@extends('layouts.app')

@section('title', 'Учет топлива')

@section('css_top')
    <link rel="stylesheet" href="{{ asset('css/balloon.css') }}">
    <style>
        th.text-truncate {
            position: relative;
            overflow: visible;
        }
        /*@media (min-width: 768px) {
            span.text-truncate {
                max-width: 130px;
            }
        }
        @media (min-width: 1360px) {
            span.text-truncate {
                max-width: 150px;
            }
        }
        @media (min-width: 1700px) {
            span.text-truncate {
                max-width: 160px;
            }
        }
        @media (min-width: 1920px) {
            span.text-truncate {
                max-width: 180px;
            }
        }*/

        .reset-button-fade-enter-active, .reset-button-fade-leave-active {
            transition: opacity .5s;
        }
        .reset-button-fade-enter, .reset-button-fade-leave-to {
            opacity: 0;
        }

        .el-input-number .el-input__inner {
            text-align: left !important;
        }

        #level-input .el-input__inner, #level-input-2 .el-input__inner {
            padding-left: 15px !important;
        }

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
                    <li class="breadcrumb-item active" aria-current="page">Топливные емкости</li>
                </ol>
            </div>
            <div class="card col-xl-10 mr-auto ml-auto pd-0-min" style="border:1px solid rgba(0,0,0,.125);">
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
                        <div class="col-md-4">
                            <label for="count">Значение</label>
                            <el-select v-if="filter_attribute === 'object_id'"
                                       v-model="filter_value"
                                       clearable filterable
                                       :remote-method="searchLocations"
                                       @keyup.native.enter="() => {addFilter(); $refs['object_id-filter'].blur();}"
                                        ref="object_id-filter"
                                       @change="updateCurrentLocationName"
                                       remote
                                       placeholder="Поиск объекта"
                            >
                                <el-option
                                    v-for="item in locations"
                                    :key="item.id"
                                    :label="item.name"
                                    :value="item.id">
                                </el-option>
                            </el-select>
                            <el-input-number
                                v-else-if="['fuel_level_from', 'fuel_level_to'].indexOf(filter_attribute) !== -1"
                                class="w-100"
                                maxlength="10"
                                @keyup.native.enter="addFilter"
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
                                @{{ filter.attribute }}: @{{ filter.field === 'object_id' ? filter.location_name : filter.value }}
                                <span data-role="remove" @click="removeFilter(i)" class="badge-remove-link"></span>
                            </span>
                            </div>
                        </div>
                        <div v-if="filters.length > 0"class="col-md-3 text-right mnt-20--mobile text-center--mobile">
                            <button id="clearAll" type="button" class="btn btn-sm show-all" @click="clearFilters">
                                Снять фильтры
                            </button>
                        </div>
                    </div>
                </div>
            </div>
            <div class="card col-xl-10 mr-auto ml-auto pd-0-min">
                <div class="card-body card-body-tech">
                    <div class="row">
                        <h4 class="h4-tech fw-500 m-0" style="margin-top:0"><span v-pre>Топливные ёмкости</span></h4>
                    </div>
                    <div class="fixed-table-toolbar toolbar-for-btn" style="padding-left:0">
                        <div class="row">
                            <div class="col-sm-6 col-md-4">
                                <el-input placeholder="Поиск по номеру и местоположению" v-model="search_tf" clearable
                                        @keyup.native.enter="doneTyping"
                                          prefix-icon="el-icon-search" id="search-tf" @clear="doneTyping"
                                ></el-input>
                            </div>
                            <div class="col-sm-6 col-md-8 text-right mt-10__mobile">
                                @can('store', $data['class'])
                                    <button type="button" name="button" class="btn btn-sm btn-primary btn-round"
                                            style="margin-right: 10px" @click="createFuel">Добавить топливную ёмкость
                                    </button>
                                @endcan
                                @can('tech_acc_fuel_tanks_trashed')
                                    <a href="{{ route('building::tech_acc::fuel_tank.display_trashed') }}" class="float-right btn btn-outline btn-sm">Просмотр удалённых записей</a>
                                @endcan
                            </div>
                        </div>
                    </div>
                    <div class="table-responsive">
                        <table class="table table-hover mobile-table">
                            <thead>
                            <tr>
                                <th class="text-truncate" data-balloon-pos="up-left" aria-label="#"><span class="text-truncate d-inline-block">#</span></th>
                                <th class="text-truncate" data-balloon-pos="up-left" aria-label="Местоположение"><span class="text-truncate d-inline-block">Местоположение</span></th>
                                <th class="text-truncate" data-balloon-pos="up-left" aria-label="Номер"><span class="text-truncate d-inline-block">Номер</span></th>
                                <th class="text-truncate" data-balloon-pos="up-left" aria-label="Уровень топлива, л"><span class="text-truncate d-inline-block">Уровень топлива, л</span></th>
                                <th></th>
                            </tr>
                            </thead>
                            <tbody>
                            <tr v-if="fuels.length === 0">
                                <td></td>
                                <td>Записей не найдено.</td>
                                <td></td>
                                <td></td>
                                <td></td>
                            </tr>
                            <tr v-else v-for="fuel in fuels">
                                <td data-label="#">
                                    @{{ fuel.id }}
                                </td>
                                <td data-label="Местоположение">
                                    @{{ fuel.object.address }}
                                </td>
                                <td data-label="Номер">
                                    @{{ fuel.tank_number }}
                                </td>
                                <td data-label="Уровень топлива, л">
                                    @{{ fuel.fuel_level }}
                                    <button data-balloon-pos="up"
                                            aria-label="Изменить уровень топлива"
                                            class="btn btn-link btn-xs btn-space btn-primary mn-0" @click="editFuelLevel(fuel)"
                                            style="padding: 0 !important; padding-bottom: 4px !important; font-size: 12px !important;"
                                    >
                                        <i class="fa fa-pen"></i>
                                    </button>
                                </td>
                                <td class="text-right actions">
                                    <button data-balloon-pos="up"
                                            aria-label="Просмотр"
                                            class="btn btn-link btn-xs btn-space btn-primary mn-0"
                                            @click="showFuel(fuel)">
                                        <i class="fa fa-eye"></i>
                                    </button>
                                    @can('update', $data['class'])
                                    <button data-balloon-pos="up"
                                            aria-label="Редактировать"
                                            class="btn btn-link btn-xs btn-space btn-success mn-0" @click="editFuel(fuel)">
                                        <i class="fa fa-edit"></i>
                                    </button>
                                    @endcan
                                    @can('destroy', $data['class'])
                                    <button data-balloon-pos="up"
                                            aria-label="Удалить"
                                            class="btn btn-link btn-xs btn-space btn-danger mn-0" @click="removeFuel(fuel.id)">
                                        <i class="fa fa-trash"></i>
                                    </button>
                                    @endcan
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
    <div class="modal fade bd-example-modal-lg show" id="form_fuel" role="dialog" aria-labelledby="modal-search" style="display: none;">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content" id="create_form">
                <div class="modal-header">
                    <h5 class="modal-title">@{{ edit_mode ? 'Редактирование' : 'Добавление' }} топливной ёмкости</h5>
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
                                        <div class="row">
                                            <div class="col-md-12">
                                                <label for="">Номер<span class="star">*</span></label>
                                                <validation-provider rules="required|max:10" vid="number-input"
                                                                     ref="number-input"
                                                                     v-slot="v"
                                                >
                                                    <el-input
                                                        :class="v.classes"
                                                        id="number-input"
                                                        maxlength="10"
                                                        placeholder="Введите номер топливной ёмкости"
                                                        v-model="tank_number"
                                                        clearable
                                                    ></el-input>
                                                    <div class="error-message">@{{ v.errors[0] }}</div>
                                                </validation-provider>
                                            </div>
                                        </div>
                                        <div class="row" v-if="!edit_mode">
                                            <div class="col-md-12">
                                                <label for="">Уровень топлива, л.<span class="star">*</span></label>
                                                <validation-provider rules="required|max:10" vid="level-input"
                                                                     ref="level-input"
                                                                     v-slot="v"
                                                >
                                                    <el-input-number
                                                        :class="v.classes"
                                                        class="w-100"
                                                        id="level-input"
                                                        maxlength="10"
                                                        :min="0"
                                                        :step="1"
                                                        :precision="3"
                                                        controls-position="right"
                                                        placeholder="Введите уровень топлива"
                                                        v-model="fuel_level"
                                                    ></el-input-number>
                                                    <div class="error-message">@{{ v.errors[0] }}</div>
                                                </validation-provider>
                                            </div>
                                        </div>
                                        <div class="row">
                                            <div class="col-md-12">
                                                <label for="">Дата ввода в эксплуатацию<span class="star">*</span></label>
                                                <validation-provider rules="required" v-slot="v" vid="date-input"
                                                                     ref="date-input">
                                                    <el-date-picker
                                                        style="cursor:pointer"
                                                        :class="v.classes"
                                                        v-model="explotation_start"
                                                        format="dd.MM.yyyy"
                                                        value-format="dd.MM.yyyy"
                                                        id="date-input"
                                                        type="date"
                                                        placeholder="Укажите дату ввода в эксплуатацию"
                                                        :picker-options="{firstDayOfWeek: 1}"
                                                        @focus="onFocus"
                                                    ></el-date-picker>
                                                    <div class="error-message">@{{ v.errors[0] }}</div>
                                                </validation-provider>
                                            </div>
                                        </div>
                                        <div class="row">
                                            <div class="col-md-12">
                                                <label for="">Местоположение<span class="star">*</span></label>
                                                <validation-provider rules="required" v-slot="v" vid="location-select"
                                                                     ref="location-select">
                                                    <el-select v-model="object_id"
                                                               :class="v.classes"
                                                               clearable filterable
                                                               id="location-select"
                                                               :remote-method="searchLocations"
                                                               @clear="searchLocations('')"
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
                                        <!-- <div class="row">
                                            <div class="col-md-12">
                                                <label for="">Юридическое лицо<span class="star">*</span></label>
                                                <validation-provider rules="required" vid="owner-select" v-slot="v"
                                                                     ref="owner-select">
                                                    <el-select v-model="contractor" filterable
                                                               :class="v.classes"
                                                               id="owner-select"
                                                               placeholder="Выберите юр. лицо"
                                                    >
                                                        <el-option
                                                            v-for="(item, key) in contractors"
                                                            :key="key"
                                                            :value="item"
                                                            clearable
                                                        ></el-option>
                                                    </el-select>
                                                    <div class="error-message">@{{ v.errors[0] }}</div>
                                                </validation-provider>
                                            </div>
                                        </div> -->
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
    <div class="modal fade bd-example-modal-lg show" id="card_fuel" role="dialog" aria-labelledby="modal-search" style="display: none;">
        <div class="modal-dialog modal-lg" role="document" style="max-width:900px">
            <div class="modal-content pb-3">
                <div class="modal-header">
                    <h5 class="modal-title">Топливная ёмкость №@{{ fuel.tank_number }}</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">×</span>
                    </button>
                </div>
                <div class="modal-body pt-0">
                    <div class="row mt-3">
                        <div class="col-md-4 inline-label">
                            Уровень топлива
                        </div>
                        <div class="col-md-8 font-weight-bold">
                            @{{ fuel.fuel_level }} л.
                        </div>
                    </div>
                    <div class="row mt-3">
                        <div class="col-md-4 inline-label">
                            Местоположение
                        </div>
                        <div class="col-md-8 font-weight-bold">
                            @{{ fuel.object.short_name ? fuel.object.short_name : fuel.object.address }}
                        </div>
                    </div>
                    <div class="row mt-3">
                        <div class="col-md-4 inline-label">
                            Дата ввода в эксплуатацию
                        </div>
                        <div class="col-md-8 font-weight-bold">
                            @{{ convertDateFormat(fuel.explotation_start) }}
                        </div>
                    </div>
                    <h6 class="decor-h6-modal">Заявки на ремонт</h6>
                    <div class="modal-section">
                        <div class="row">
                            <div class="col-md-12">
                                <div class="table-responsive">
                                    <table class="table table-hover mobile-table">
                                        <thead>
                                        <tr>
                                            <th>Нач. ремонта</th>
                                            <th>Оконч. ремонта</th>
                                            <th>Исполнитель</th>
                                            <th>Юр. лицо</th>
                                            <th>Автор</th>
                                            <th>Статус</th>
                                        </tr>
                                        </thead>
                                        <tbody>
                                        <tr v-if="!fuel.defects_light || fuel.defects_light.length === 0">
                                            <td data-label="Нач. ремонта">
                                            </td>
                                            <td data-label="Оконч. ремонта">
                                            </td>
                                            <td data-label="Исполнитель">
                                                Заявок не найдено
                                            </td>
                                            <td data-label="Юр. лицо">
                                            </td>
                                            <td data-label="Автор">
                                            </td>
                                            <td data-label="Статус">
                                            </td>
                                        </tr>
                                        <template v-else>
                                            <tr v-for="(defect, index) in fuel.defects_light" v-if="index < 10" class="href" :data-href="'{{ route('building::tech_acc::defects.show', '') }}' + '/' + defect.id">
                                                <td data-label="Нач. ремонта">
                                                    @{{ defect.repair_start_date ? convertDateFormat(defect.repair_start_date) : 'Не назначено' }}
                                                </td>
                                                <td data-label="Оконч. ремонта">
                                                    @{{ defect.repair_end_date ? convertDateFormat(defect.repair_end_date) : 'Не назначено' }}
                                                </td>
                                                <td data-label="Исполнитель">
                                                    @{{ defect.responsible_user_name ? defect.responsible_user_name : 'Не назначен' }}
                                                </td>
                                                <td data-label="Юр. лицо">
                                                    Неизвестно
                                                </td>
                                                <td data-label="Автор">
                                                    @{{ defect.author_name ? defect.author_name : 'Неизвестен' }}
                                                </td>
                                                <td data-label="Статус">
                                                    <span :class="`${getStatusClass(defect.status)}`" >@{{ defect.status_name ? defect.status_name : 'Неизвестен' }}</span>
                                                </td>
                                            </tr>
                                        </template>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                        <div class="row mt-10" v-if="fuel.defects_light && fuel.defects_light.length > 0">
                            <div class="col-md-12 text-right">
                                <a :href="'{{ route('building::tech_acc::defects.index') }}' + `?defectable=${fuel.id}|2&page=1`" class="blue-link small-transition-link ">Просмотр всех заявок на
                                    ремонт</a><span class="blue-link"> → </span>
                            </div>
                        </div>
                    </div>
                    <!-- <div class="row mt-3">
                        <div class="col-md-4 inline-label">
                            Юр. лицо
                        </div>
                        <div class="col-md-8 font-weight-bold">
                            @{{ fuel.owner }}
                        </div>
                    </div> -->
                    <div v-if="window_width <= 769" class="row mt-3">
                        <div class="col-md-12">
                            <a href="#collapse" class="text-primary font-weight-bold" style="font-size: 15px;"
                               data-target="#collapse" data-toggle="collapse">
                                Топливные записи
                            </a>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <a v-if="window_width > 769" href="#collapse" class="text-primary font-weight-bold" style="font-size: 15px;"
                       data-target="#collapse" data-toggle="collapse">
                        Топливные записи
                    </a>
                    <button v-if="window_width > 769" class="btn btn-primary btn-round btn-sm" @click="edit_fuel_modal_show">Редактировать</button>
                    <button v-else class="btn btn-primary w-100" @click="edit_fuel_modal_show">Редактировать</button>
                </div>
                <div class="px-3 pb-3 collapse card-collapse" id="collapse">
                    <el-table :data="fuel.operations" class="w-100">
                        <el-table-column
                            prop="operation_date"
                            label="Дата"
                        >
                            <template slot-scope="scope">
                                @{{  convertDateFormat(scope.row.operation_date)  }}
                            </template>
                        </el-table-column>
                        <el-table-column
                            prop="type_name"
                            label="Вид действия"
                        ></el-table-column>
                        <el-table-column
                            prop="value"
                            label="Объём, л."
                        ></el-table-column>
                        <el-table-column
                            prop="result_value"
                            label="Уровень топлива, л."
                        ></el-table-column>
                        <el-table-column
                            prop="author.full_name"
                            label="Автор"
                        ></el-table-column>
                    </el-table>
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
                        ></el-pagination>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- fuel-level-edit -->
    <div class="modal fade bd-example-modal-lg show" id="fuel-level-edit" role="dialog" aria-labelledby="modal-search" style="display: none;">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content pb-3">
                <div class="modal-header">
                    <h5 class="modal-title">Изменение уровня топлива</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">×</span>
                    </button>
                </div>
                <div class="modal-body pt-0">
                    <validation-observer ref="observer" :key="observer_key">
                        <div class="modal-section mt-3">
                            <label for="">Уровень топлива, л.<span class="star">*</span></label>
                            <validation-provider rules="required|max:10" vid="level-input"
                                                 ref="level-input"
                                                 v-slot="v"
                            >
                                <el-input-number
                                    :class="v.classes"
                                    class="w-100"
                                    id="level-input-2"
                                    maxlength="10"
                                    :min="0"
                                    :step="1"
                                    :precision="3"
                                    controls-position="right"
                                    placeholder="Введите уровень топлива"
                                    v-model="fuel_level"
                                ></el-input-number>
                                <div class="error-message">@{{ v.errors[0] }}</div>
                            </validation-provider>
                        </div>
                        <div class="modal-section mt-3">
                            <label for="">Комментарий<span class="star">*</span></label>
                            <validation-provider rules="required|max:300" vid="renewal-comment-input"
                                                 ref="renewal-comment-input" v-slot="v">
                                <el-input
                                    :class="v.classes"
                                    type="textarea"
                                    :rows="4"
                                    maxlength="300"
                                    id="renewal-comment-input"
                                    clearable
                                    placeholder="Укажите причину ручного изменения уровня топлива"
                                    v-model="description"
                                ></el-input>
                                <div class="error-message">@{{ v.errors[0] }}</div>
                            </validation-provider>
                        </div>
                        <div class="modal-section text-center mt-30">
                            <button class="btn btn-primary btn-sm" @click.stop="submit">Сохранить</button>
                        </div>
                    </validation-observer>
                </div>
            </div>
        </div>
    </div>

    <!-- end modals -->
@endsection
@section('js_footer')
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
                PAGE_SIZE: 15,
                SINGLE_USE_FILTERS: ['fuel_level_from', 'fuel_level_to'],
                DONE_TYPING_INTERVAL: 1000,
                loading: false,
                //TODO substitute with blade
                totalItems: {!! json_encode($data['fuel_tanks_count']) !!},
                currentPage: 1,
                fuels: {!! json_encode($data['fuel_tanks']) !!},
                search_tf: '{!! request()->search !!}',
                filter_attributes: [
                    {label: 'Местоположение', value: 'object_id'},
                    {label: 'Номер', value: 'tank_number'},
                    {label: 'Уровень топлива от, л', value: 'fuel_level_from'},
                    {label: 'Уровень топлива до, л', value: 'fuel_level_to'},
                ],
                filter_attribute: '',
                filter_value: '',
                filter_location_name: '',
                locations: [],
                contractors: {{--{!! json_encode(array_values($data['owners']))  !!}--}}['ООО "СК ГОРОД"'],
                filters: [],
                window_width: 10000,

                typingTimer: null,
            },
            watch: {
                filter_attribute() {
                    this.filter_value = '';
                    this.filter_location_name = '';
                },
                loading(val) {
                    if (val) {
                        this.loadingInstance = ELEMENT.Loading.service({ fullscreen: true });
                    } else {
                        this.loadingInstance.close();
                    }
                },
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
                        if (field === 'object_id') {
                            this.promisedSearchLocations('')//entry[1]
                                .then(() => {
                                    const location = this.locations.find(el => el.id == value);
                                    customName = location ? location.name : '';
                                    this.filters.push({
                                        attribute: this.filter_attributes.find(el => el.value === field).label,
                                        field: field,
                                        value: value,
                                        location_name: customName,
                                    });
                                })
                                .catch(() => {})
                        } else {
                            this.filters.push({
                                attribute: this.filter_attributes.find(el => el.value === field).label,
                                field: field,
                                value: value,
                                location_name: customName,
                            });
                        }
                        this.$forceUpdate();
                    }
                });
            },
            mounted() {
                // $('#filter-value-tf').on('keypress', function (e) {
                //     if(e.which === 13){
                //         vm.addFilter();
                //     }
                // });

                let searchTF = $('#search-tf');

                searchTF.on('keyup', () => {
                    clearTimeout(this.typingTimer);
                    this.typingTimer = setTimeout(this.doneTyping, this.DONE_TYPING_INTERVAL);
                });

                searchTF.on('keydown', () => {
                    clearTimeout(this.typingTimer);
                });

                $(window).on('resize', this.handleResize);
                this.handleResize();
                this.searchLocations('');
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
                }
            },
            methods: {
                changePage(page) {
                    this.$router.replace({query: Object.assign({}, this.$route.query, {page: page})}).catch(err => {});
                    this.updateFilteredFuels();
                },
                doneTyping() {
                    const queryObj = {};
                    clearTimeout(this.typingTimer);
                    if (this.search_tf) {
                        const count = Object.keys(this.$route.query).filter(el => el.indexOf('search') !== -1).length;
                        if (!count) {
                            queryObj['search'] = this.search_tf;
                            this.$router.replace({query: Object.assign({}, this.$route.query, queryObj)}).catch(err => {});
                        } else {
                            Object.assign(queryObj, this.$route.query);
                            queryObj['search'] = this.search_tf;
                            this.$router.replace({query: Object.assign({}, queryObj)}).catch(err => {});
                        }
                    } else {
                        Object.assign(queryObj, this.$route.query);
                        delete queryObj['search'];
                        this.$router.replace({query: Object.assign({}, queryObj)}).catch(err => {});
                    }
                    this.resetCurrentPage();
                },
                addFilter() {
                    if (this.filter_value && this.filter_attribute) {
                        const queryObj = {};
                        this.filters.push({
                            attribute: this.filter_attributes.find(el => el.value === this.filter_attribute).label,
                            field: this.filter_attribute,
                            value: this.filter_value,
                            location_name: this.filter_location_name,
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
                        /*if (!this.$route.query[this.filter_attribute]) {
                            queryObj[this.filter_attribute] = this.filter_value;
                            this.$router.replace({query: Object.assign({}, this.$route.query, queryObj)}).catch(err => {});
                        } else {
                            if (!Array.isArray(this.$route.query[this.filter_attribute])) {
                                queryObj[this.filter_attribute] = [this.$route.query[this.filter_attribute], this.filter_value];
                                this.$router.replace({query: Object.assign({}, this.$route.query, queryObj)}).catch(err => {});
                            } else {
                                queryObj[this.filter_attribute] = [...this.$route.query[this.filter_attribute], this.filter_value];
                                this.$router.replace({query: Object.assign({}, this.$route.query, queryObj)}).catch(err => {});
                            }
                        }*/
                        this.filter_value = '';
                        if (this.SINGLE_USE_FILTERS.indexOf(this.filter_attribute) !== -1) {
                            this.filter_attribute = '';
                        }
                        this.resetCurrentPage();
                        this.$forceUpdate();
                    }
                },
                updateFilteredFuels() {
                    axios.post('{{ route('building::tech_acc::get_fuel_tanks_paginated') }}', {url: vm.$route.fullPath, page: this.currentPage})
                        .then(response => {
                            vm.fuels = response.data.fuelTanks;
                            vm.totalItems = response.data.fuelTanksCount;
                        })
                        .catch(error => console.log(error));
                },
                updateCurrentLocationName() {
                    this.filter_location_name = this.locations.find(loc => loc.id === this.filter_value) ? this.locations.find(loc => loc.id === this.filter_value).name : '';
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
                    this.searchLocations('');
                    this.resetCurrentPage();
                },
                resetCurrentPage() {
                    this.$router.replace({query: Object.assign({}, this.$route.query, {page: 1})}).catch(err => {});
                    this.currentPage = 1;
                    this.updateFilteredFuels();
                },
                handleResize() {
                    this.window_width = $(window).width();
                },
                createFuel() {
                    if (formFuel.edit_mode) {
                        formFuel.reset();
                    }
                    $('#form_fuel').modal('show');
                    $('#form_fuel').focus();
                    $('.modal').css('overflow-y', 'auto');
                },
                showFuel(fuel) {
                    if (!vm.loading) {
                        if (fuel) {
                            if (!fuel.is_loaded) {
                                vm.loading = true;
                                let route = '{{ route('building::tech_acc::fuel_tank.show', ["ID_TO_SUBSTITUTE"]) }}';

                                axios.get(makeUrl(route, [fuel.id]))
                                    .then(response => {
                                        const fuelIndex = vm.fuels.findIndex(el => el.id == fuel.id);
                                        this.$set(vm.fuels, fuelIndex, response.data.fuelTank);
                                        vm.fuels[fuelIndex].is_loaded = true;
                                        cardFuel.update(response.data.fuelTank);
                                        $('#card_fuel').modal('show');
                                        $('#card_fuel').focus();
                                        vm.loading = false;
                                    })
                                    .catch(error => {
                                        console.log(error);
                                        this.loading = false;
                                    });
                            } else {
                                cardFuel.update(fuel);
                                $('#card_fuel').modal('show');
                                $('#card_fuel').focus();
                            }
                        }
                        $('.modal').css('overflow-y', 'auto');
                    }
                },
                editFuel(fuel) {
                    formFuel.update(fuel);
                    $('#form_fuel').modal('show');
                    $('#form_fuel').focus();
                    $('.modal').css('overflow-y', 'auto');
                },
                editFuelLevel(fuel) {
                    fuelLevelEdit.update(fuel);
                    $('#fuel-level-edit').modal('show');
                    $('#fuel-level-edit').focus();
                    $('.modal').css('overflow-y', 'auto');
                },
                removeFuel(id) {
                    swal({
                        title: 'Вы уверены?',
                        text: "Топливная ёмкость будет удалена!",
                        type: 'warning',
                        showCancelButton: true,
                        cancelButtonText: 'Назад',
                        confirmButtonText: 'Удалить'
                    }).then((result) => {
                        if (result.value) {
                            let route = '{{ route('building::tech_acc::fuel_tank.destroy', ["ID_TO_SUBSTITUTE"]) }}';

                            axios.delete(makeUrl(route, [id]))
                                .then(() => {
                                    this.fuels.splice(this.fuels.findIndex(el => el.id === id), 1);
                                    this.totalItems -= 1;
                                    this.updateFilteredFuels();
                                    this.hideTooltips();
                                })
                                //TODO add actual error handler
                                .catch(error => swal({
                                    type: 'error',
                                    title: "Ошибка удаления ",
                                }));
                        } else {
                            this.hideTooltips();
                        }
                    });
                },
                promisedSearchLocations(query) {
                    return new Promise((resolve, reject) => {
                        if (query) {
                            axios.post('{{ route('building::mat_acc::report_card::get_objects') }}', {q: query})
                                .then(response => {
                                    this.locations = response.data.map(el => ({ name: el.label, id: el.code }));
                                    resolve(response);
                                })
                                .catch(error => {
                                    console.log(error);
                                    reject(error);
                                });
                        } else {
                            axios.post('{{ route('building::mat_acc::report_card::get_objects') }}')
                                .then(response => {
                                    this.locations = response.data.map(el => ({ name: el.label, id: el.code }));
                                    resolve(response);
                                })
                                .catch(error => {
                                    console.log(error);
                                    reject(error);
                                });
                        }
                    });
                },
                searchLocations(query) {
                    if (query) {
                        axios.post('{{ route('building::mat_acc::report_card::get_objects') }}', {q: query})
                            .then(response => this.locations = response.data.map(el => ({ name: el.label, id: el.code })))
                            .catch(error => console.log(error));
                    } else {
                        axios.post('{{ route('building::mat_acc::report_card::get_objects') }}')
                            .then(response => this.locations = response.data.map(el => ({ name: el.label, id: el.code })))
                            .catch(error => console.log(error));
                    }
                },
                updateFuels(fuel) {
                    const fuel_index = this.fuels.findIndex(el => el.id === fuel.id);
                    if (fuel_index !== -1) {
                        this.$set(this.fuels, fuel_index, fuel);
                    } else {
                        this.totalItems += 1;
                        this.fuels.unshift(fuel);
                    }
                },
                hideTooltips() {
                    for (let ms = 50; ms <= 1050; ms += 100) {
                        setTimeout(() => {
                            $('[data-balloon-pos]').blur();
                        }, ms);
                    }
                },
            }
        });
    </script>
    <script type="text/javascript">
        var cardFuel = new Vue ({
            el:'#card_fuel',
            data: {
                fuel: {object: []},
                window_width: 10000,
                PAGE_SIZE: 10,
                totalItems: 20,
                currentPage: 1,
            },
            mounted() {
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
            },
            methods: {
                convertDateFormat(dateString, full) {
                    return full ? moment(dateString, 'YYYY-MM-DD HH:mm:ss').format('DD.MM.YYYY HH:mm:ss') :
                        moment(dateString, 'YYYY-MM-DD HH:mm:ss').format('DD.MM.YYYY');
                },
                getStatusClass(status_id) {
                    switch (status_id) {
                        case 1:
                            return 'text-success';
                        case 2:
                            return 'text-warning';
                        case 3:
                            return 'text-primary';
                        case 4:
                            return '';
                        case 5:
                            return 'text-danger';
                        default:
                            return '';
                    }
                },
                update(fuel) {
                    this.fuel = fuel;

                    setTimeout(() => {
                        var touchtime = 0;
                        $("table .href").on("click", function() {
                            if (touchtime == 0) {
                                // set first click
                                touchtime = new Date().getTime();
                            } else {
                                // compare first click to this click and see if they occurred within double click threshold
                                if (((new Date().getTime()) - touchtime) < 800) {
                                    // double click occurred
                                    window.location = $(this).attr('data-href');
                                    return false;
                                    touchtime = 0;
                                } else {
                                    // not a double click so set as a new first click
                                    touchtime = new Date().getTime();
                                }
                            }
                        });
                    }, 1234);

                    this.changePage(1);
                },
                edit_fuel_modal_show() {
                    formFuel.update(this.fuel);
                    $('#card_fuel').modal('hide');
                    $('#form_fuel').modal('show');
                    $('.modal').css('overflow-y', 'auto');
                    $('#form_fuel').focus();
                },
                handleResize() {
                    this.window_width = $(window).width();
                },
                changePage(page) {
                    this.currentPage = page;
                    let that = this;
                    let route = '{{ route('building::tech_acc::fuel_tank_operations_paginated') }}';

                    axios.post(makeUrl(route, []), {
                        fuel_tank_id: that.fuel.id,
                        page: page,
                    })
                    .then((response) => {
                        that.$set(that.fuel, 'operations', response.data.fuelTankOperations);
                        that.totalItems = response.data.fuelTankOperationCount;
                    })
                    //TODO add actual error handler
                    .catch(error => {
                        console.log(error);
                    });
                },
            }
        });

        var formFuel = new Vue({
            el: '#form_fuel',
            data: {
                fuel: {},
                loading: false,
                edit_mode: false,
                observer_key: 1,
                // contractor: '',
                // contractors: {{--JSON.parse('{!! json_encode(array_values($data['owners']))  !!}')--}}['ООО "СК ГОРОД"'],
                tank_number: '',
                object_id: null,
                objects: [],
                explotation_start: '',
                fuel_level: 0,
                window_width: 10000,
            },
            created() {
                this.searchLocations('');
            },
            mounted() {
                $(window).on('resize', this.handleResize);
                this.handleResize();
            },
            methods: {
                update(fuel) {
                    this.searchLocations('');
                    this.fuel = fuel;
                    this.tank_number = fuel.tank_number;
                    //Using full location name instead of id because el-select displays initial value as initial label
                    this.object_id = fuel.object.name + '. ' + fuel.object.address;
                    this.explotation_start = new Date(fuel.explotation_start);
                    this.edit_mode = true;
                    this.$nextTick(() => {
                        this.$refs.observer.reset();
                    });
                },
                reset() {
                    this.searchLocations('');
                    this.fuel = {};
                    this.tank_number = '';
                    this.object_id = null;
                    this.explotation_start = "";
                    this.objects = [];
                    this.edit_mode = false;
                    this.fuel_level = 0;
                    this.observer_key += 1;
                    this.$nextTick(() => {
                        this.$refs.observer.reset();
                    });
                },
                onFocus() {
                    $('.el-input__inner').blur();
                },
                handleResize() {
                    this.window_width = $(window).width();
                },
                searchLocations(query) {
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
                handleError(error, file, fileList) {
                    let message = error.response.data.message;
                    let errors = error.response.data.errors;
                    swal({
                        type: 'error',
                        message: 'Ошибка',
                        html: message,
                    });
                },
                submit() {
                    let that = this;
                    this.$refs.observer.validate().then(success => {
                        if (!success) {
                            const error_field_vid = Object.keys(this.$refs.observer.errors).find(el => this.$refs[el].errors.length > 0);
                            $('.modal').animate({
                                scrollTop: $('#' + error_field_vid).offset().top
                            }, 1200);
                            $('#' + error_field_vid).focus();
                            return;
                        }
                        if (!this.loading) {
                            if (!this.edit_mode) {
                                this.loading = true;
                                axios.post('{{ route('building::tech_acc::fuel_tank.store') }}', {
                                    tank_number: that.tank_number,
                                    fuel_level: that.fuel_level,
                                    object_id: that.object_id,
                                    explotation_start: moment(that.explotation_start, 'DD.MM.YYYY').startOf('day').add(8, 'hours'),
                                })
                                    .then((response) => {
                                        vm.updateFuels(response.data.data);

                                        swal({
                                            type: 'success',
                                            title: "Запись была создана",
                                        }).then(() => {
                                            $('#form_fuel').modal('hide');
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
                                let route = '{{ route('building::tech_acc::fuel_tank.update', ["ID_TO_SUBSTITUTE"]) }}';
                                this.loading = true;
                                axios.put(makeUrl(route, [that.fuel.id]), {
                                    tank_number: that.tank_number,
                                    object_id: String(that.object_id).indexOf('. ') !== -1 ? that.fuel.object_id : that.object_id,
                                    explotation_start: moment(that.explotation_start, 'DD.MM.YYYY').startOf('day').add(8, 'hours'),
                                })
                                    .then((response) => {
                                        vm.updateFuels(response.data.data);

                                        swal({
                                            type: 'success',
                                            title: "Запись была обновлена",
                                        }).then(() => {
                                            $('#form_fuel').modal('hide');
                                            $('.modal').css('overflow-y', 'auto');
                                            this.reset();
                                        });
                                        this.loading = false;
                                    })
                                    //TODO add actual error handler
                                    .catch(error => {
                                        swal({
                                            type: 'error',
                                            title: "Ошибка обновления ",
                                        });
                                        this.loading = false;
                                    });
                            }
                        }
                    });
                },
            }
        });

        var fuelLevelEdit = new Vue({
            el: '#fuel-level-edit',
            data: {
                fuel: null,
                observer_key: 1,
                description: '',
                fuel_level: 0,
                window_width: 10000,
            },
            methods: {
                handleError(error, file, fileList) {
                    let message = error.response.data.message;
                    let errors = error.response.data.errors;
                    swal({
                        type: 'error',
                        message: 'Ошибка изменения уровня топлива',
                        html: message,
                    });
                },
                reset() {
                    this.fuel = null;
                    this.fuel_level = 0;
                    this.comment = '';
                },
                update(fuel) {
                    this.fuel = fuel;
                    this.fuel_level = fuel.fuel_level;
                    this.comment = '';
                    this.observer_key += 1;
                },
                submit() {
                    this.$refs.observer.validate().then(success => {
                        if (!success) {
                            const error_field_vid = Object.keys(this.$refs.observer.errors).find(el => this.$refs[el].errors.length > 0);
                            $('#' + error_field_vid).focus();
                            return;
                        }

                        let route = '{{ route('building::tech_acc::fuel_tank.change_fuel_level', ["ID_TO_SUBSTITUTE"]) }}';

                        const payload = {
                            fuel_level: this.fuel_level,
                            description: this.description,
                        };
                        axios.post(makeUrl(route, [fuelLevelEdit.fuel.id]), payload)
                            .then((response) => {
                                this.fuel.fuel_level = this.fuel_level;
                                swal({
                                    type: 'success',
                                    title: "Уровень топлива успешно изменен",
                                }).then(() => {
                                    $('#fuel-level-edit').modal('hide');
                                    $('.modal').css('overflow-y', 'auto');
                                    this.reset();
                                });
                            })
                            .catch((error) => {
                                this.handleError(error);
                            });
                    });
                }
            }
        });

    </script>
@endsection
