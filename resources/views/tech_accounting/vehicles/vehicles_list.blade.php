@extends('layouts.app')

@section('title', 'Учет транспорта')

@section('css_top')
    <link rel="stylesheet" href="{{ asset('css/balloon.css') }}">
    <style>
        #th-select, .th-option  {
            color: #9A9A9A;
            font-weight: 400;
            text-transform: uppercase !important;
            font-size: 12px !important;
            letter-spacing: 1px !important;
            white-space: nowrap;
        }
        th.text-truncate, td.text-truncate {
            position: relative;
            overflow: visible;
        }
        @media (min-width: 768px) {
            span.text-truncate {
                max-width: 40px;
            }
        }
        @media (min-width: 1360px) {
            span.text-truncate {
                max-width: 60px;
            }
        }
        @media (min-width: 1560px) {
            span.text-truncate {
                max-width: 90px;
            }
        }
        @media (min-width: 1700px) {
            span.text-truncate {
                max-width: 110px;
            }
        }
        @media (min-width: 1920px) {
            span.text-truncate {
                max-width: 130px;
            }
        }

        .pre-wrap {
            white-space: pre-wrap !important;
        }
        @media (max-width: 768px) {
            .pre-wrap {
                white-space: normal !important;
            }
        }

        .reset-button-fade-enter-active, .reset-button-fade-leave-active {
            transition: opacity .5s;
        }
        .reset-button-fade-enter, .reset-button-fade-leave-to {
            opacity: 0;
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
                <li class="breadcrumb-item">
                    <a href="{{ route('building::vehicles::vehicle_categories.index') }}" class="table-link">Транспорт</a>
                </li>
                <li class="breadcrumb-item">
                    <a href="{{ route('building::vehicles::vehicle_categories.show', $data['category']->id) }}" class="table-link">{{ addslashes($data['category']->name) }}</a>
                </li>
                <li class="breadcrumb-item active" aria-current="page">Список транспорта</li>
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
                            <el-option v-for="attr in filter_attributes" :label="attr.label"
                                       :value="attr.value"
                            ></el-option>
                        </el-select>
                    </div>
                    <div class="col-md-4">
                        <label for="count">Значение</label>
                        {{--<el-select v-if="filter_attribute === 'start_location'"
                                   v-model="filter_value"
                                   clearable filterable
                                   :remote-method="searchLocations"
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
                        </el-select>--}}
                        <el-select v-if="filter_attribute === 'owner'"
                                   v-model="filter_value" filterable
                                   placeholder="Выберите юр. лицо"
                        >
                            <el-option
                                v-for="(item, key) in contractors"
                                :key="key"
                                :value="item"
                                clearable
                            ></el-option>
                        </el-select>
                        <el-input v-else placeholder="Введите значение" v-model="filter_value" id="filter-value-tf" clearable></el-input>
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
                                @{{ filter.attribute }}: @{{ filter.field === 'start_location' ? filter.location_name : filter.value }}
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
        <div class="card col-xl-10 mr-auto ml-auto pd-0-min">
            <div class="card-body card-body-tech">
                <h4 class="h4-tech fw-500" style="margin-top:0"><span v-pre>{{ $data['category']->name }}</span></h4>
                <div class="fixed-table-toolbar toolbar-for-btn" style="padding-left:0">
                    <div class="row">
                        <div class="col-sm-6 col-md-4">
                            <el-input placeholder="Поиск по наименованию" v-model="search_tf" clearable
                                      prefix-icon="el-icon-search"
                            ></el-input>
                        </div>
                        <div class="col-sm-6 col-md-8 text-right mt-10__mobile">
                            @can('tech_acc_our_vehicle_create')
                                <button type="button" name="button" class="btn btn-sm btn-primary btn-round"
                                    @click="createVehicle" style="margin-right: 10px">Добавить транспорт
                                </button>
                            @endcan
                            @can('tech_acc_vehicles_trashed')
                                <a href="{{ route('building::vehicles::vehicle_categories.our_vehicles.index_trashed', $data['category']->id) }}" class="float-right btn btn-outline btn-sm">Просмотр удалённых записей</a>
                            @endcan
                        </div>
                    </div>
                </div>
                <div class="table-responsive">
                    <table class="table table-hover mobile-table">
                        <thead>
                            <tr>
                                <th class="text-truncate" data-balloon-pos="up-left"  aria-label="#"><span class="text-truncate d-inline-block">#</span></th>
                                <th class="text-truncate" data-balloon-pos="up-left"  aria-label="Марка"><span class="text-truncate d-inline-block">Марка</span></th>
                                <th class="text-truncate" data-balloon-pos="up-left"  aria-label="Модель"><span class="text-truncate d-inline-block">Модель</span></th>
                                <th class="text-truncate" data-balloon-pos="up-left"  :aria-label="selected_characteristic_name" v-if="window_width > 975 && category.characteristics.filter(el => el.show).length > 3">
                                    <el-select id="th-select"
                                               v-model="selected_characteristic_id" filterable
                                               placeholder="Выберите характеристику"
                                               style="max-width: 150px"
                                    >
                                        <el-option
                                            v-for="(item, key) in (category.characteristics ? category.characteristics.filter(el => el.show) : [])"
                                            :key="key"
                                            :value="item.id"
                                            :label="item.name + (item.unit ? (', ' + item.unit) : '')"
                                            class="th-option"
                                            clearable
                                        >
                                        </el-option>
                                    </el-select>
                                </th>
                                <template v-else>
                                    <th data-balloon-pos="up-left" :aria-label="characteristic.name + (characteristic.unit ? (', ' + characteristic.unit) : '')" v-for="characteristic in category.characteristics.filter(el => el.show)">
                                        <span class="text-truncate d-inline-block">@{{characteristic.name}}@{{characteristic.unit ? (', ' + characteristic.unit) : ''}}</span>
                                    </th>
                                </template>
                                <th class="text-truncate" data-balloon-pos="up-left"  aria-label="Гос. номер ТС"><span class="text-truncate d-inline-block">Гос. номер ТС</span></th>
                                <th class="text-truncate" data-balloon-pos="up-left"  aria-label="Гос. номер прицепа"><span class="text-truncate d-inline-block">Гос. номер прицепа</span></th>
                                <th class="text-truncate" data-balloon-pos="up-left"  aria-label="Юр. лицо"><span class="text-truncate d-inline-block">Юр. лицо</span></th>
                                <th></th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-for="vehicle in vehicles_filtered">
                                <td data-label="#">@{{vehicle.id}}</td>
                                <td data-label="Марка" class="text-truncate">
                                    <span class="text-truncate d-inline-block pre-wrap">@{{vehicle.mark}}</span>
                                </td>
                                <td data-label="Модель" class="text-truncate">
                                    <span class="text-truncate d-inline-block pre-wrap">@{{vehicle.model}}</span>
                                </td>
                                <td v-if="window_width > 975 && category.characteristics.filter(el => el.show).length > 3"
                                    :data-label="selected_characteristic_name" class="text-truncate">
                                    <span class="text-truncate d-inline-block pre-wrap">@{{vehicle.parameters.some(char => char.characteristic_id === selected_characteristic_id) ?
                                    vehicle.parameters.find(char => char.characteristic_id === selected_characteristic_id).value ?
                                    vehicle.parameters.find(char => char.characteristic_id === selected_characteristic_id).value : '-' : '-'}}</span>
                                </td>
                                <template v-else>
                                    <td v-for="characteristic in category.characteristics.filter(el => el.show)"
                                        :data-label="characteristic.name" class="text-truncate">
                                        <span class="text-truncate d-inline-block pre-wrap">@{{vehicle.parameters.some(char => char.characteristic_id === characteristic.id) ?
                                        vehicle.parameters.find(char => char.characteristic_id === characteristic.id).value ?
                                        vehicle.parameters.find(char => char.characteristic_id === characteristic.id).value : '-' : '-'}}</span>
                                    </td>
                                </template>
                                {{--<td data-label="Местоположение">
                                    @{{tech.start_location ? tech.start_location.address : 'не указано'}}
                                </td>--}}
                                <td data-label="Гос. номер ТС">
                                    @{{vehicle.number}}
                                </td>
                                <td data-label="Гос. номер прицепа">
                                    @{{vehicle.trailer_number ? vehicle.trailer_number : 'не указано'}}
                                </td>
                                <td data-label="Юр. лицо">
                                    @{{vehicle.owner_name ? vehicle.owner_name : 'не указано'}}
                                </td>
                                <td class="text-right actions">
                                    <button rel="tooltip" data-original-title="Просмотр"
                                            class="btn btn-link btn-xs btn-space btn-primary mn-0" @click="showVehicle(vehicle)">
                                        <i class="fa fa-eye"></i>
                                    </button>
                                    @can('tech_acc_our_vehicle_update')
                                        <button rel="tooltip" data-original-title="Редактировать"
                                                class="btn btn-link btn-xs btn-space btn-success mn-0" @click="editVehicle(vehicle)">
                                            <i class="fa fa-edit"></i>
                                        </button>
                                    @endcan
                                    @can('tech_acc_vehicle_category_destroy')
                                        <button rel="tooltip" data-original-title="Удалить"
                                                class="btn btn-link btn-xs btn-space btn-danger mn-0" @click="removeVehicle(vehicle.id)">
                                            <i class="fa fa-trash"></i>
                                        </button>
                                    @endcan
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- modals -->
@canany(['tech_acc_our_vehicle_create', 'tech_acc_our_vehicle_update'])
<!-- create and update -->
<div class="modal fade bd-example-modal-lg show" id="form_vehicle" role="dialog" aria-labelledby="modal-search" style="display: none;">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">@{{ edit_mode ? 'Редактирование' : 'Добавление' }} единицы транспорта</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">×</span>
                </button>
            </div>
            <div class="modal-body">
                <div class="card border-0 m-0">
                    <div class="card-body">
                        <validation-observer ref="observer" :key="observer_key">
                            <form class="form-horizontal">
                                <template>
                                    <div class="row">
                                        <div class="col-md-6">
                                            <label for="">Марка<span class="star">*</span></label>
                                            <validation-provider rules="required|max:60" vid="brand-input"
                                                 ref="brand-input" name="названия марки" v-slot="v">
                                                <el-input
                                                    :class="v.classes"
                                                    id="brand-input"
                                                    placeholder="Введите название марки"
                                                    maxlength="60"
                                                    v-model="vehicle_mark"
                                                    clearable
                                                ></el-input>
                                                <div class="error-message">@{{ v.errors[0] }}</div>
                                            </validation-provider>
                                        </div>
                                        <div class="col-md-6">
                                            <label for="">Модель<span class="star">*</span></label>
                                            <validation-provider rules="required|max:60" vid="model-input"
                                                                 ref="model-input" name="названия модели" v-slot="v">
                                                <el-input
                                                    :class="v.classes"
                                                    id="model-input"
                                                    placeholder="Введите название модели"
                                                    v-model="vehicle_model"
                                                    maxlength="60"
                                                    clearable
                                                ></el-input>
                                                <div class="error-message">@{{ v.errors[0] }}</div>
                                            </validation-provider>
                                        </div>
                                    </div>
                                    <div class="row">
                                        <div class="col-md-6">
                                            <label for="">Юр.лицо<span class="star">*</span></label>
                                            <validation-provider rules="required" vid="owner-select" v-slot="v"
                                                                 ref="owner-select">
                                                <el-select v-model="tech_contractor" filterable
                                                           :class="v.classes"
                                                           id="owner-select"
                                                           placeholder="Выберите юр. лицо"
                                                >
                                                    <el-option
                                                        v-for="(item, key) in tech_contractors"
                                                        :key="key"
                                                        :value="key + 1"
                                                        :label="item"
                                                        clearable
                                                    ></el-option>
                                                </el-select>
                                                <div class="error-message">@{{ v.errors[0] }}</div>
                                            </validation-provider>
                                        </div>
                                    </div>
                                    <div class="row">
                                        <div class="col-md-6">
                                            <label for="">Гос. номер транспорта<span class="star">*</span></label>
                                            <validation-provider rules="required|veh_reg_number|max:12" vid="number-input"
                                                                 ref="number-input" v-slot="v">
                                                <el-input
                                                    :class="v.classes"
                                                    id="number-input"
                                                    placeholder="Введите гос. номер транспорта"
                                                    v-model="number"
                                                    maxlength="9"
                                                    clearable
                                                ></el-input>
                                                <div class="error-message">@{{ v.errors[0] }}</div>
                                            </validation-provider>
                                        </div>
                                        <div class="col-md-6">
                                            <label for="">Гос. номер прицепа</label>
                                            <validation-provider rules="max:12" vid="trailer-number-input"
                                                                 ref="trailer-number-input" v-slot="v">
                                                <el-input
                                                    :class="v.classes"
                                                    id="trailer-number-input"
                                                    placeholder="Введите гос. номер прицепа"
                                                    v-model="trailer_number"
                                                    maxlength="9"
                                                    clearable
                                                ></el-input>
                                                <div class="error-message">@{{ v.errors[0] }}</div>
                                            </validation-provider>
                                        </div>
                                    </div>
                                    <div class="row">
                                        <div class="col-md-12">
                                            <h6 class="decor-h6-modal mb-0">Параметры</h6>
                                        </div>
                                    </div>
                                    <div class="row" v-for="i in Math.ceil(category_characteristics.length / 2)">
                                        <div class="col-md-6" v-for="characteristic in [category_characteristics[(i-1)*2], category_characteristics[(i-1)*2+1]]">
                                            <template v-if="characteristic">
                                                <label for="">@{{ characteristic.name }}@{{characteristic.unit ? (', ' + characteristic.unit) : ''}}@{{characteristic.required ? (' *') : ''}}</label>
                                                <validation-provider :rules="characteristic.required ? 'required|max:60' : 'max:60'" :name="characteristic.name" :vid="'date-input-' + characteristic.id"
                                                                     :ref="'date-input-' + characteristic.id" v-slot="v">
                                                    <el-input
                                                        :class="v.classes"
                                                        placeholder="Введите значение характеристики"
                                                        v-model="characteristic.value"
                                                        :key="characteristic.id"
                                                        maxlength="60"
                                                        :id="'date-input-' + characteristic.id"
                                                        clearable
                                                    ></el-input>
                                                    <div class="error-message">@{{ v.errors[0] }}</div>
                                                </validation-provider>
                                            </template>
                                        </div>
                                    </div>
                                    <div class="row">
                                        <div class="col-md-12">
                                            <h6 class="decor-h6-modal">Документация</h6>
                                        </div>
                                    </div>
                                    <div class="row">
                                        <div class="col-md-12">
                                            <el-upload
                                                :drag="window_width > 769"
                                                action="{{ route('file_entry.store') }}"
                                                :headers="{ 'X-CSRF-TOKEN': '{{ csrf_token() }}' }"
                                                :limit="10"
                                                :before-upload="beforeUpload"
                                                :on-preview="handlePreview"
                                                :on-remove="handleRemove"
                                                :on-exceed="handleExceed"
                                                :on-success="handleSuccess"
                                                :on-error="handleError"
                                                :file-list="file_list"
                                                multiple
                                            >
                                                <template v-if="window_width > 769">
                                                    <i class="el-icon-upload"></i>
                                                    <div class="el-upload__text">Перетащите сюда или <em>кликните, чтобы выбрать файлы для загрузки</em></div>
                                                </template>
                                                <el-button v-else size="small" type="primary">Загрузить</el-button>
                                                <div class="el-upload__tip" slot="tip">Неисполняемые файлы размером до 50Мб</div>
                                            </el-upload>
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
                    <transition name="reset-button-fade">
                        <div class="row justify-content-center mb-2">
                            <button v-if="!edit_mode" @click.stop="reset" type="button" class="btn btn-warning w-100">Сброс</button>
                        </div>
                    </transition>
                    <div class="row justify-content-center mb-2">
                        <button @click.stop="submit" type="button" class="btn btn-info w-100">Сохранить</button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endcanany

{{--@can('tech_acc_our_vehicle_update')
    <!-- edit  -->
    <div class="modal fade bd-example-modal-lg show" id="edit_tech_card" role="dialog" aria-labelledby="modal-search" style="display: none;">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Редактирование</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">×</span>
                    </button>
                </div>
                <div class="modal-body">
                    <div class="card border-0" id="edit_form">
                        <div class="card-body">
                            <form id="" class="form-horizontal" action="" method="post" enctype="multipart/form-data">
                                <template>
                                    <div class="row">
                                        <div class="col-md-6">
                                            <label for="">Марка<span class="star">*</span></label>
                                            <el-input
                                                placeholder="Введите название марки"
                                                v-model="tech_name"
                                                max="60"
                                                clearable>
                                            </el-input>
                                        </div>
                                        <div class="col-md-6">
                                            <label for="">Модель<span class="star">*</span></label>
                                            <el-input
                                                placeholder="Введите название марки"
                                                v-model="tech_model"
                                                max="60"
                                                clearable>
                                            </el-input>
                                        </div>
                                    </div>
                                    <div class="row">
                                        <div class="col-md-6">
                                            <label for="">Юр.лицо<span class="star">*</span></label>
                                            <el-select v-model="tech_contractor" filterable placeholder="Выберите юр.лицо">
                                                <el-option
                                                    v-for="tech_contractor in tech_contractors"
                                                    :key="tech_contractor.id"
                                                    :value="tech_contractor.text"
                                                    clearable>
                                                </el-option>
                                            </el-select>
                                        </div>
                                    </div>
                                    <div class="row">
                                        <div class="col-md-6">
                                            <label for="">Гос. номер авто<span class="star">*</span></label>
                                            <el-input
                                                placeholder=""
                                                v-model="state_number"
                                                max="9"
                                                clearable>
                                            </el-input>
                                        </div>
                                        <div class="col-md-6">
                                            <label for="">Гос. номер прицепа<span class="star">*</span></label>
                                            <el-input
                                                placeholder=""
                                                v-model="trailer_number"
                                                max="9"
                                                clearable>
                                            </el-input>
                                        </div>
                                    </div>
                                    <div class="row">
                                        <div class="col-md-12">
                                            <h6 class="decor-h6-modal mb-0">Параметры</h6>
                                        </div>
                                    </div>
                                    <div class="row">
                                        <div class="col-md-6">
                                            <label for="">Свободный параметр категории</label>
                                            <el-input
                                                placeholder=""
                                                v-model="param1"
                                                max="60"
                                                clearable>
                                            </el-input>
                                        </div>
                                        <div class="col-md-6">
                                            <label for="">Свободный параметр категории</label>
                                            <el-input
                                                placeholder=""
                                                v-model="param2"
                                                max="60"
                                                clearable>
                                            </el-input>
                                        </div>
                                    </div>
                                    <div class="row">
                                        <div class="col-md-12">
                                            <h6 class="decor-h6-modal">Документация</h6>
                                        </div>
                                    </div>
                                    <div class="row">
                                        <div class="col-md-12">
                                            <el-upload
                                                class="upload-demo"
                                                action="https://jsonplaceholder.typicode.com/posts/"
                                                :on-preview="handlePreview"
                                                :on-remove="handleRemove"
                                                :before-remove="beforeRemove"
                                                :limit="10"
                                                :on-exceed="handleExceed"
                                                :file-list="fileList"
                                                multiple>
                                                <el-button>Выбрать файлы для загрузки</el-button>
                                            </el-upload>
                                        </div>
                                    </div>
                                </template>
                            </form>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Закрыть</button>
                    <button id="" form="" type="submit" class="btn btn-info">Сохранить</button>
                </div>
            </div>
        </div>
    </div>
@endcan--}}
<!-- card -->
<div class="modal fade bd-example-modal-lg show" id="card_vehicle" role="dialog" aria-labelledby="modal-search" style="display: none;">
    <div class="modal-dialog modal-lg" role="document" style="max-width:900px">
        <div class="modal-content">
            <div class="decor-modal__body">
                <div class="row" style="flex-direction:row-reverse">
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
                                        Категория
                                    </span>
                                    <span class="task-info__body-title">
                                        {{ $data['category']->name }}
                                    </span>
                                </div>
                                <div class="task-info__text-unit">
                                    <span class="task-info__head-title">
                                        Юридическое лицо
                                    </span>
                                    <span class="task-info__body-title">
                                        @{{vehicle.owner_name}}
                                    </span>
                                </div>
                            </div>
                            @can('tech_acc_our_vehicle_update')
                                <div class="row">
                                    <div class="col-md-12 text-center">
                                        <button id="edit_tech_btn" type="button" name="button"
                                                class="btn btn-sm btn-round btn-primary"
                                                @click="edit_vehicle_modal_show">
                                            Редактировать
                                        </button>
                                    </div>
                                </div>
                            @endcan
                        </div>
                    </div>
                    <div class="col-md-8">
                        <div class="left-bar-main">
                            <h4 class="mn-0 fw-500" style="font-size: 26px">@{{ vehicle.mark }} @{{ vehicle.model }}</h4>
                            <h6 class="decor-h6-modal">Документация</h6>
                            <div class="modal-section">
                                <div class="row">
                                    <div class="col-md-12">
                                        <template v-if="!vehicle.documents || vehicle.documents.length === 0">
                                            <a class="tech-link modal-link">
                                                Документы не найдены
                                            </a>
                                        </template>
                                        <template v-else v-for="file in vehicle.documents">
                                            <a :href="'{{ asset('storage/docs/tech_accounting/') }}' + '/' + file.filename"
                                               target="_blank" class="tech-link modal-link">
                                                @{{ file.original_filename }}
                                            </a>
                                            <br/>
                                        </template>
                                    </div>
                                </div>
                            </div>
                            <h6 class="decor-h6-modal">Параметры</h6>
                            <div class="modal-section">
                                <div class="row">
                                    <div class="col-md-12">
                                        <div class="table-responsive">
                                            <table class="table table-hover mobile-table">
                                                <thead>
                                                <tr>
                                                    <th>Параметр</th>
                                                    <th class="text-center">Значение</th>
                                                    <th class="text-center">Отображение в таблице</th>
                                                </tr>
                                                </thead>
                                                <tbody>
                                                <tr v-for="characteristic in category.characteristics">
                                                    <td data-label="Параметр">
                                                        @{{ characteristic.name }}@{{characteristic.unit ? (', ' + characteristic.unit) : ''}}
                                                    </td>
                                                    <td class="text-center" data-label="Значение">
                                                        @{{ vehicle.parameters ? (vehicle.parameters.find(el => el.characteristic_id === characteristic.id) ?
                                                        ((vehicle.parameters.find(el => el.characteristic_id === characteristic.id).value ?
                                                        vehicle.parameters.find(el => el.characteristic_id === characteristic.id).value : '-') +
                                                        (characteristic.unit && vehicle.parameters.find(el => el.characteristic_id === characteristic.id).value ?
                                                        ('' + '') : '')) : '-') : '-' }}
                                                    </td>
                                                    <td class="text-center" data-label="Отображение в таблице">
                                                        @{{ characteristic.show ? 'да' : 'нет' }}
                                                    </td>
                                                </tr>
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            {{--<h6 class="decor-h6-modal">Заявки</h6>
                            <div class="modal-section">
                                <div class="row">
                                    <div class="col-md-12">
                                        <div class="table-responsive">
                                            <table class="table table-hover mobile-table">
                                                <thead>
                                                    <tr>
                                                        <th>#</th>
                                                        <th class="text-center">Дата создания</th>
                                                        <th class="text-center">Статус</th>
                                                        <th class="">Автор</th>
                                                        <th class="">РП</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    <tr>
                                                        <td data-label="#">
                                                            12
                                                        </td>
                                                        <td  class="text-center" data-label="Дата создания">
                                                            30.09.2019
                                                        </td>
                                                        <td class="text-center" data-label="Статус">
                                                            <span class="text-primary">В работе</span>
                                                        </td>
                                                        <td data-label="Автор">
                                                            Иванов И.И.
                                                        </td>
                                                        <td data-label="РП">
                                                            Смирнов С.С.
                                                        </td>
                                                    </tr>
                                                    <tr>
                                                        <td data-label="#">
                                                            8
                                                        </td>
                                                        <td  class="text-center" data-label="Дата создания">
                                                            28.09.2019
                                                        </td>
                                                        <td class="text-center" data-label="Статус">
                                                            <span class="text-primary">В работе</span>
                                                        </td>
                                                        <td data-label="Автор">
                                                            Иванов И.И.
                                                        </td>
                                                        <td data-label="РП">
                                                            Смирнов С.С.
                                                        </td>
                                                    </tr>
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>
                                </div>
                                <div class="row mt-10">
                                    <div class="col-md-12 text-right">
                                        <a href="#" class="purple-link small-transition-link">Просмотр всех заявок</a><span class="purple-link"> → </span>
                                    </div>
                                </div>
                            </div>--}}
                        </div>
                    </div>
                </div>
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
        data: {
            vehicles: JSON.parse('{!! addslashes(json_encode($data['vehicles'])) !!}'),
            //TODO add category to page payload
            category: JSON.parse('{!! addslashes(json_encode($data['category'])) !!}'),
            selected_characteristic_id: JSON.parse('{!! addslashes(json_encode($data['category'])) !!}').characteristics.find(el => !el.is_hidden) ?
                JSON.parse('{!! addslashes(json_encode($data['category'])) !!}').characteristics.find(el => !el.is_hidden).id : '',
            search_tf: '',
            filter_attributes: [
                {label: '№', value: 'id'},
                {label: 'Марка', value: 'mark'},
                {label: 'Модель', value: 'model'},
                {label: 'Гос. номер ТС', value: 'number'},
                {label: 'Гос. номер прицепа', value: 'trailer_number'},
                {label: 'Юр. лицо', value: 'owner'},
            ],
            filter_attribute: '',
            filter_value: '',
            //filter_location_name: '',
            //locations: [],
            //TODO add owners to page payload
            contractors: JSON.parse('{!! json_encode(array_values($data['owners']))  !!}'),
            filters: [],
            window_width: 10000,
        },
        mounted() {
            $('#filter-value-tf').on('keypress', function (e) {
                if(e.which === 13){
                    vm.addFilter();
                }
            });
            $(window).on('resize', this.handleResize);
            this.handleResize();
            if (this.category.characteristics) {
                this.filter_attributes.push(...this.category.characteristics
                    .filter(el => el.show)
                    .map(el => ({
                        label: el.name + (el.unit ? (', ' + el.unit) : ''),
                        value: `characteristics.${el.id}`
                    })));
            }
            //this.searchLocations('');
        },
        computed: {
            selected_characteristic_name() {
                const selected_characteristic = this.category.characteristics.find(el => el.id === this.selected_characteristic_id);
                return selected_characteristic ? (selected_characteristic.name + (selected_characteristic.unit ? (', ' + selected_characteristic.unit) : '' )) : 'Выберите характеристику';
            },
            vehicles_filtered() {
                return this.vehicles.filter(vehicle => {
                    //Search textfield filter
                    const search_tf_pattern = new RegExp(_.escapeRegExp(this.search_tf), 'i');
                    const search_tf_filter = search_tf_pattern.test(vehicle.mark) ||
                        search_tf_pattern.test(vehicle.model) ||
                        search_tf_pattern.test(vehicle.mark + ' ' + vehicle.model);

                    let characteristics_filter = true;
                    const characteristics_filters = {};

                    //Characteristics filters
                    for (const filter of this.filters) {
                        const filter_value_pattern = new RegExp(_.escapeRegExp(filter.value), 'i');

                        console.log(filter);
                        if (/^id$/.test(filter.field)) {
                            if (vehicle.id != filter.value) {
                                if (!characteristics_filters[filter.field]){
                                    characteristics_filters[filter.field] = false;
                                }
                                continue;
                            }
                        } else if (/characteristics/.test(filter.field)) {
                            const characteristic_id = filter.field.split('.')[1];
                            const characteristic_value = vehicle.parameters.find(el => el.characteristic_id == characteristic_id) ?
                                vehicle.parameters.find(el => el.characteristic_id == characteristic_id).value : '';

                            if (!characteristic_value || !filter_value_pattern.test(characteristic_value)) {
                                if (!characteristics_filters[filter.field]){
                                    characteristics_filters[filter.field] = false;
                                }
                                continue;
                            }
                        } else if (/owner/.test(filter.field)) {
                            // plus 1 because standard arrays starts from key 0
                            // but array with contractors starts from 1
                            if (vehicle.owner != (this.contractors.indexOf(filter.value) + 1)) {
                                if (!characteristics_filters[filter.field]){
                                    characteristics_filters[filter.field] = false;
                                }
                                continue;
                            }
                            //TODO handle actual status value
                        } else if (/status/.test(filter.field)) {
                            if (!(filter_value_pattern.test('Свободен'))) {
                                if (!characteristics_filters[filter.field]){
                                    characteristics_filters[filter.field] = false;
                                }
                                continue;
                            }
                        } else if (!filter_value_pattern.test(vehicle[filter.field])) {
                            if (!characteristics_filters[filter.field]){
                                characteristics_filters[filter.field] = false;
                            }
                            continue;
                        }

                        characteristics_filters[filter.field] = true;
                    }

                    characteristics_filter = Object.values(characteristics_filters).every(el => el);

                    return search_tf_filter && characteristics_filter;
                });
            }
        },
        methods: {
           /* getStatusClass(status) {
                switch (status.toLowerCase()) {
                    case 'свободен':
                        return 'free-status';
                    case 'в работе':
                        return 'busy-status';
                    case 'ремонт':
                        return 'repairs-status';
                }
            },*/
            addFilter() {
                if (this.filter_value && this.filter_attribute) {
                    this.filters.push({
                        attribute: this.filter_attributes.find(el => el.value === this.filter_attribute).label,
                        field: this.filter_attribute,
                        value: this.filter_value,
                        location_name: this.filter_location_name,
                    });
                    this.filter_value = '';
                    this.$forceUpdate();
                }
            },
            /*updateCurrentLocationName() {
                this.filter_location_name = this.locations.find(loc => loc.id === this.filter_value).name;
            },*/
            removeFilter(index) {
                this.filters.splice(index, 1);
            },
            clearFilters() {
                this.filters = [];
            },
            handleResize() {
                this.window_width = $(window).width();
            },
            createVehicle() {
                if (formVehicle.edit_mode) {
                    formVehicle.reset();
                }
                $('#form_vehicle').modal('show');
                $('#form_vehicle').focus();
                $('.modal').css('overflow-y', 'auto');
            },
            showVehicle(vehicle) {
                cardVehicle.update(vehicle);
                $('#card_vehicle').modal('show');
                $('#card_vehicle').focus();
                $('.modal').css('overflow-y', 'auto');
            },
            editVehicle(vehicle) {
                formVehicle.update(vehicle);
                $('#form_vehicle').modal('show');
                $('#form_vehicle').focus();
                $('.modal').css('overflow-y', 'auto');
            },
            /*searchLocations(query) {
                if (query) {
                    axios.post('{{ route('building::mat_acc::report_card::get_objects') }}', {q: query})
                        .then(response => this.locations = response.data.map(el => ({ name: el.label, id: el.code })))
                        .catch(error => console.log(error));
                } else {
                    axios.post('{{ route('building::mat_acc::report_card::get_objects') }}')
                        .then(response => this.locations = response.data.map(el => ({ name: el.label, id: el.code })))
                        .catch(error => console.log(error));
                }
            },*/
            @can('tech_acc_vehicle_category_destroy')
                removeVehicle(id) {
                    swal({
                        title: 'Вы уверены?',
                        text: "Единица транспорта будет удалена!",
                        type: 'warning',
                        showCancelButton: true,
                        cancelButtonText: 'Назад',
                        confirmButtonText: 'Удалить'
                    }).then((result) => {
                        if (result.value) {
                            axios.delete('{{ route('building::vehicles::vehicle_categories.our_vehicles.destroy', [
                                $data['category']->id,
                                ''
                        ]) }}' + '/' + id)
                                .then(() => this.vehicles.splice(this.vehicles.findIndex(el => el.id === id), 1))
                                //TODO add actual error handler
                                .catch(error => console.log(error));
                        }
                    });
                },
            @endcan
            updateVehicles(vehicle) {
                const vehicle_index = this.vehicles.findIndex(el => el.id === vehicle.id);
                if (vehicle_index !== -1) {
                    this.$set(this.vehicles, vehicle_index, vehicle);
                } else {
                    this.vehicles.push(vehicle);
                }
            }
        }
    });

    @canany(['tech_acc_our_vehicle_create', 'tech_acc_our_vehicle_update'])
        var formVehicle = new Vue({
            el: '#form_vehicle',
            data: {
                edit_mode: false,
                observer_key: 1,
                vehicle: {},
                vehicle_mark: '',
                vehicle_model: '',
                vehicle_category: '{!! addslashes($data['category']->name) !!}',
                //We can't change category of a tech since we are adding it from a techincs list of a certain category
                vehicle_categories: ['{!! addslashes($data['category']->name) !!}'],
                tech_contractor: '',
                tech_contractors: JSON.parse('{!! json_encode(array_values($data['owners']))  !!}'),
                number: '',
                trailer_number: '',
                tech_location_id: null,
                tech_locations: [],
                start_date: '',
                category_characteristics: [...vm.category.characteristics.map(el => ({
                    id: el.id,
                    name: el.name,
                    value: '',
                    unit: el.unit,
                    required: el.required,
                }))],
                file_list: [],
                files_to_remove: [],
                files_uploaded: [],
                window_width: 10000,
            },
            created() {
                this.searchLocations('');
            },
            mounted() {
                $(window).on('resize', this.handleResize);
                this.handleResize();
                this.applyVehRegMask();
            },
            methods: {
                applyVehRegMask() {
                    $('#number-input').mask('A000AA00Z', {
                        translation: {
                            'Z': {
                                pattern: /[0-9]/, optional: true
                            },
                            'A': {
                                pattern: /[ABEKMHOPCTYXАВЕКМНОРСТУХabekmhopctyxавекмнорстух]/, optional: false
                            }
                        }
                    });
                },
                removeFiles(file_ids, vehicle_id) {
                    if (file_ids.length > 0) {
                        file_ids.forEach(file_id => {
                            if (file_id) {
                                axios.delete('{{ route('file_entry.destroy', '') }}' + '/' + file_id)
                                    .then(() => {
                                        const vehicle_documents = vm.vehicles.find(vehicle => vehicle.id === vehicle_id).documents;
                                        if (vehicle_documents) {
                                            vehicle_documents.splice(vehicle_documents.findIndex(doc => doc.id === file_id), 1)
                                        }
                                    })
                                    //TODO add actual error handler
                                    .catch(error => console.log(error));
                            }
                        });
                    }
                },
                update(vehicle) {
                    this.searchLocations('');
                    this.vehicle = vehicle;
                    this.vehicle_mark = vehicle.mark;
                    this.vehicle_model = vehicle.model;
                    this.tech_contractor = +vehicle.owner;
                    this.number = vehicle.number;
                    this.trailer_number = vehicle.trailer_number;
                    //Using full location name instead of id because el-select displays initial value as initial label
                    // this.tech_location_id = `${tech.start_location.name}. ${tech.start_location.address}`;
                    // this.start_date = new Date(tech.exploitation_start);
                    this.category_characteristics = [...vm.category.characteristics.map(el => {
                        const vehicle_parameter = vehicle.parameters.find(char => char.characteristic_id === el.id);
                        return {
                            id: el.id,
                            name: el.name,
                            value: vehicle_parameter ? vehicle_parameter.value ? vehicle_parameter.value  : '' : '',
                            unit: el.unit,
                            parameter_id: vehicle_parameter ? vehicle_parameter.id ? vehicle_parameter.id : '' : '',
                            required: el.required,
                        }
                    })];
                    this.file_list = vehicle.documents.map(doc => {
                        doc.name = doc.original_filename;
                        return doc;
                    });
                    this.files_to_remove = [];
                    this.files_uploaded = [];
                    this.edit_mode = true;
                    this.$nextTick(() => {
                        this.applyVehRegMask();
                        this.$refs.observer.reset();
                    });
                },
                reset() {
                    this.searchLocations('');
                    this.vehicle = {};
                    this.edit_mode = false;
                    this.vehicle_mark = '';
                    this.vehicle_model = '';
                    this.vehicle_category = '{!! addslashes($data['category']->name) !!}';
                    //We can't change category of a tech since we are adding it from a techincs list of a certain category
                    this.vehicle_categories = ['{!! addslashes($data['category']->name) !!}'];
                    this.tech_contractor = '';
                    this.tech_contractors = JSON.parse('{!! json_encode(array_values($data['owners']))  !!}');
                    this.number = '';
                    this.trailer_number = '';
                    // this.tech_location_id = null;
                    // this.tech_locations = [];
                    // this.start_date = '';
                    this.category_characteristics = [...vm.category.characteristics.map(el => ({
                        id: el.id,
                        name: el.name,
                        value: '',
                        unit: el.unit,
                        required: el.required,
                    }))];
                    this.file_list = [];
                    this.files_to_remove = [];
                    this.files_uploaded = [];
                    this.observer_key += 1;
                    this.$nextTick(() => {
                        this.applyVehRegMask();
                        this.$refs.observer.reset();
                    });
                },
                onFocus() {
                    $('.el-input__inner').blur();
                },
                handleResize() {
                    this.window_width = $(window).width();
                },
                handleRemove(file, fileList) {
                    if (file.hasOwnProperty('response')) {
                        this.files_to_remove.push(...file.response.data.map(el => el.id));
                        this.files_uploaded.splice(this.files_uploaded.findIndex(el => el.response.data[0].id === file.response.data[0].id));
                    } else {
                        this.files_to_remove.push(file.id);
                    }
                },
                handleSuccess(response, file, fileList) {
                    file.url = file.response.data[0] ? '{{ asset('storage/docs/tech_accounting/') }}' + '/' + file.response.data[0].filename : '#';
                    this.files_uploaded.push(...file.response.data.map(el => el));
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
                handlePreview(file) {
                    window.open(file.url ? file.url : '{{ asset('storage/docs/tech_accounting/') }}' + '/' + file.filename, '_blank');
                    $('#form_tech').focus();
                },
                handleExceed(files, fileList) {
                    this.$message.warning(`Невозможно загрузить ${files.length} файлов. Вы можете загрузить еще ${10 - fileList.length} файлов`);
                },
                beforeUpload(file) {
                    const FORBIDDEN_EXTENSIONS = ['exe'];
                    const FILE_MAX_LENGTH = 50000000;
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
                //TODO change routes for actual ones
                searchLocations(query) {
                    if (query) {
                        axios.post('{{ route('building::mat_acc::report_card::get_objects') }}', {q: query})
                            //TODO make field names in location objects equal (name, id in tech; label, code in following route)
                            .then(response => this.tech_locations = response.data.map(el => ({ name: el.label, id: el.code })))
                            //TODO add actual error handler
                            .catch(error => console.log(error));
                    } else {
                        axios.post('{{ route('building::mat_acc::report_card::get_objects') }}')
                            //TODO make field names in location objects equal (name, id in tech; label, code in following route)
                            .then(response => this.tech_locations = response.data.map(el => ({ name: el.label, id: el.code })))
                            //TODO add actual error handler
                            .catch(error => console.log(error));
                    }
                },
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
                        if (!this.edit_mode) {
                            axios.post('{{ route('building::vehicles::vehicle_categories.our_vehicles.store', $data['category']->id) }}', {
                                mark: this.vehicle_mark,
                                model: this.vehicle_model,
                                category_id: '{{ $data['category']->id }}',
                                owner: this.tech_contractor,
                                number: this.number,
                                trailer_number: this.trailer_number,
                                // start_location_id: this.tech_location_id,
                                // exploitation_start: this.start_date,
                                parameters: this.category_characteristics.map(el => ({
                                    characteristic_id: el.id,
                                    value: el.value
                                })),
                                file_ids: this.files_uploaded.map(file => file.id),
                            })
                                .then((response) => {
                                    vm.updateVehicles(response.data.data);
                                    this.removeFiles(this.files_to_remove, this.vehicle.id);
                                    swal({
                                        type: 'success',
                                        title: "Запись была создана",
                                    }).then(() => {
                                        $('#form_vehicle').modal('hide');
                                        $('.modal').css('overflow-y', 'auto');
                                        this.reset();
                                    });
                                })
                                .catch(error => this.handleError(error));
                        } else {
                            const payload = {};
                            payload.category_id = vm.category.id;
                            payload.mark = this.vehicle_mark !== this.vehicle.mark ? this.vehicle_mark : this.vehicle.mark;
                            payload.model = this.vehicle_model !== this.vehicle.model ? this.vehicle_model : this.vehicle.model;
                            payload.owner = this.tech_contractor !== this.vehicle.owner ? this.tech_contractor : this.vehicle.owner;
                            payload.number = this.number !== this.vehicle.number ? this.number : this.vehicle.number;
                            payload.trailer_number = this.trailer_number !== this.vehicle.trailer_number ? this.trailer_number : this.vehicle.trailer_number;
                            if (this.files_uploaded.length > 0) {
                                payload.file_ids = this.files_uploaded.map(file => file.id);
                            }
                            for (const characteristic of this.category_characteristics) {
                                const vehicle_characteristic = this.vehicle.parameters.find(el => el.characteristic_id === characteristic.id);
                                if (vehicle_characteristic && characteristic.value !== vehicle_characteristic.value || !vehicle_characteristic) {
                                    payload.parameters = this.category_characteristics.map(el => ({
                                        id: el.parameter_id ? el.parameter_id : null,
                                        characteristic_id: el.id,
                                        value: el.value ? el.value : '',
                                    }));
                                    break;
                                }
                            }
                            axios.put('{{ route('building::vehicles::vehicle_categories.our_vehicles.update', [
                                    $data['category']->id,
                                    ''
                            ]) }}' + '/' + this.vehicle.id, payload)
                                .then((response) => {
                                    vm.updateVehicles(response.data.data);
                                    this.removeFiles(this.files_to_remove, this.vehicle.id);
                                    swal({
                                        type: 'success',
                                        title: "Запись была обновлена",
                                    }).then(() => {
                                        $('#form_vehicle').modal('hide');
                                        $('.modal').css('overflow-y', 'auto');
                                        this.reset();
                                    });
                                })
                                //TODO add actual error handler
                                .catch(error => console.log(error));
                        }
                    });
                },
            }
        });
    @endcanany

    var cardVehicle = new Vue ({
        el:'#card_vehicle',
        data: {
            vehicle_status: 'Свободен',
            category: JSON.parse('{!! addslashes(json_encode($data['category'])) !!}'),
            vehicle: {}
        },
        methods: {
            getStatusClass(status) {
                return vm.getStatusClass(status);
            },
            update(vehicle) {
                this.vehicle = vehicle;
            },
            edit_vehicle_modal_show() {
                formVehicle.update(this.vehicle);
                $('#card_vehicle').modal('hide');
                $('#form_vehicle').modal('show');
                $('.modal').css('overflow-y', 'auto');
                $('#form_vehicle').focus();
            }
        }
    });
</script>
{{--<script type="text/javascript">
    $('#edit_tech_btn').click(function(){
        $('#card_tech').modal('hide');
        $('#edit_tech_card').modal('show');
        setTimeout(function(){
          $('body').addClass('modal-open');
      }, 350);
    });

    @can('tech_acc_vehicle_category_destroy')
        function delete_vehicle(e, id) {
            swal({
                title: 'Вы уверены?',
                text: "Категория будет удалена!",
                type: 'warning',
                showCancelButton: true,
                cancelButtonText: 'Назад',
                confirmButtonText: 'Удалить'
            }).then((result) => {

            });
        }
    @endcan

    @can('tech_acc_our_vehicle_create')
        var addTech = new Vue({
            el:'#create_form',
            data: {
                tech_name: '',
                vehicle_model: '',
                owner: '',
                tech_contractor: '',
                tech_contractors: [
                    {id:1, text: 'ООО "СК ГОРОД"'},
                    {id:2, text: 'ООО "ГОРОД"'},
                ],
                trailer_number: '',
                state_number: '',
                param1: '',
                param2: '',
                fileList: []
            },
            methods: {
                onFocus: function() {
                    $('.el-input__inner').blur();
                },

                handleRemove(file, fileList) {
                   console.log(file, fileList);
                 },

                 handlePreview(file) {
                   console.log(file);
                 },

                 handleExceed(files, fileList) {
                   this.$message.warning(`Невозможно загрузить ${files.length} файлов. Вы можете загрузить ${files.length - fileList.length} файлов`);
                 },

                 beforeRemove(file, fileList) {
                   return this.$confirm(`Удалить файл ${ file.name } ?`);
                 }
            }
        });
    @endcan

    var edit = new Vue({
        el:'#edit_form',
        data: {
            tech_name: '',
            vehicle_model: '',
            owner: '',
            tech_contractor: '',
            tech_contractors: [
                {id:1, text: 'ООО "СК ГОРОД"'},
                {id:2, text: 'ООО "ГОРОД"'},
            ],
            trailer_number: '',
            state_number: '',
            param1: '',
            param2: '',
            fileList: [{name: 'food.jpeg', url: 'https://fuss10.elemecdn.com/3/63/4e7f3a15429bfda99bce42a18cdd1jpeg.jpeg?imageMogr2/thumbnail/360x360/format/webp/quality/100'}]
        },
        methods: {
            onFocus: function() {
                $('.el-input__inner').blur();
            },

            handleRemove(file, fileList) {
               console.log(file, fileList);
             },

             handlePreview(file) {
               console.log(file);
             },

             handleExceed(files, fileList) {
               this.$message.warning(`Невозможно загрузить ${files.length} файлов. Вы можете загрузить ${files.length - fileList.length} файлов`);
             },

             beforeRemove(file, fileList) {
               return this.$confirm(`Удалить файл ${ file.name } ?`);
             }
        }
    });
</script>--}}
@endsection
