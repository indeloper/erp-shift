@extends('layouts.app')

@section('title', 'Учет транспорта')

@section('css_top')
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
                    <a href="{{ route('building::vehicles::vehicle_categories.show' . ($data['category']->trashed() ? "_trashed" : ''), $data['category']->id) }}" class="table-link">{{ addslashes($data['category']->name) }}</a>
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
                    <div class="col-md-4 mt-10__mobile">
                        <label for="count">Значение</label>
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
                        @if(! $data['category']->trashed())
                            <div class="col-sm-6 col-md-8 text-right mt-10__mobile">
                                <a href="{{ route('building::vehicles::vehicle_categories.our_vehicles.index', $data['category']->id) }}" class="float-right btn btn-outline btn-sm">Просмотр обычных записей</a>
                            </div>
                        @endif
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
    var vm = new Vue({
        el: '#base',
        data: {
            vehicles: JSON.parse('{!! addslashes(json_encode($data['vehicles'])) !!}'),
            category: JSON.parse('{!! addslashes(json_encode($data['category'])) !!}'),
            selected_characteristic_id: '{{ count($data['category']->characteristics) ? $data['category']->characteristics[0]->id : '' }}',
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
            removeFilter(index) {
                this.filters.splice(index, 1);
            },
            clearFilters() {
                this.filters = [];
            },
            handleResize() {
                this.window_width = $(window).width();
            },
            showVehicle(vehicle) {
                cardVehicle.update(vehicle);
                $('#card_vehicle').modal('show');
                $('#card_vehicle').focus();
                $('.modal').css('overflow-y', 'auto');
            },
        }
    });

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
        }
    });
</script>
@endsection
