@extends('layouts.app')

@section('title', 'Учет техники')

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
        th.text-truncate {
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
        @media (min-width: 1360px) {
            span.text-truncate {
                max-width: 70px;
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
                max-width: none;
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
                    <a href="{{ route('building::tech_acc::technic_category.index') }}" class="table-link">Техника</a>
                </li>
                <li class="breadcrumb-item">
                    <a href="{{ route('building::tech_acc::technic_category.show' . ($data['category']->trashed() ? "_trashed" : ''), $data['category']->id) }}" class="table-link">{{ addslashes($data['category']->name) }}</a>
                </li>
                <li class="breadcrumb-item active" aria-current="page">Список техники</li>
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
                        <el-select v-if="filter_attribute === 'start_location_id'"
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
                        </el-select>
                        <el-select v-else-if="filter_attribute === 'owner'"
                                   v-model="filter_value" filterable
                                   placeholder="Выберите юр. лицо"
                                   clearable
                        >
                            <el-option
                                v-for="(item, key) in contractors"
                                :key="key"
                                :value="item"
                            ></el-option>
                        </el-select>
                        <el-select v-else-if="filter_attribute === 'status'"
                                   v-model="filter_value" filterable clearable
                                   placeholder="Выберите статус"
                        >
                            <el-option
                                v-for="(item, key) in filtered_statuses"
                                :key="key"
                                :value="item"
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
                                @{{ filter.attribute }}: @{{ filter.field === 'start_location_id' ? filter.location_name : filter.value }}
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
                            <el-input placeholder="Поиск" v-model="search_tf" clearable
                                      prefix-icon="el-icon-search" id="search-tf" @clear="doneTyping"
                            ></el-input>
                        </div>
                        @if(! $data['category']->trashed())
                            <div class="col-sm-6 col-md-8 text-right mt-10__mobile">
                                <a href="{{ route('building::tech_acc::technic_category.our_technic.index', $data['category']->id) }}" class="float-right btn btn-outline btn-sm">Просмотр обычных записей</a>
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
                                <th class="text-truncate" data-balloon-pos="up-left"  aria-label="Инв. номер"><span class="text-truncate d-inline-block">Инв. номер</span></th>
                                <th class="text-truncate" data-balloon-pos="up-left"  :aria-label="selected_characteristic_name" v-if="window_width > 975 && category.category_characteristics.filter(el => !el.is_hidden).length > 3">
                                    <el-select id="th-select"
                                               v-model="selected_characteristic_id" filterable
                                               placeholder="Выберите характеристику"
                                               style="max-width: 150px"
                                    >
                                        <el-option
                                            v-for="(item, key) in category.category_characteristics.filter(el => !el.is_hidden)"
                                            :key="key"
                                            :value="item.id"
                                            :label="item.name + (item.unit ? (', ' + item.unit) : '')"
                                            class="th-option"
                                        >
                                        </el-option>
                                    </el-select>
                                </th>
                                <template v-else>
                                    <th data-balloon-pos="up-left" :aria-label="characteristic.name + (characteristic.unit ? (', ' + characteristic.unit) : '')" v-for="characteristic in category.category_characteristics.filter(el => !el.is_hidden)">
                                       <span class="text-truncate d-inline-block">@{{characteristic.name}}@{{characteristic.unit ? (', ' + characteristic.unit) : ''}}</span>
                                    </th>
                                </template>
                                <th data-balloon-pos="up-left"  aria-label="Местоположение" class="text-truncate"><span class="text-truncate d-inline-block">Местоположение</span></th>
                                <th data-balloon-pos="up-left"  aria-label="Статус" class="text-truncate"><span class="text-truncate d-inline-block">Статус</span></th>
                                <th></th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-for="tech in techs">
                                <td data-label="#">
                                    @{{tech.id}}
                                </td>
                                <td data-label="Марка">
                                    @{{tech.brand}}
                                </td>
                                <td data-label="Модель">
                                    @{{tech.model}}
                                </td>
                                <td data-label="Инв. номер">
                                    @{{tech.inventory_number}}
                                </td>
                                <td v-if="window_width > 975 && category.category_characteristics.filter(el => !el.is_hidden).length > 3"
                                    :data-label="selected_characteristic_name">
                                    @{{tech.category_characteristics.some(char => char.id === selected_characteristic_id) ?
                                    tech.category_characteristics.find(char => char.id === selected_characteristic_id).data.value ?
                                    tech.category_characteristics.find(char => char.id === selected_characteristic_id).data.value : '-' : '-'}}
                                </td>
                                <template v-else>
                                    <td v-for="characteristic in category.category_characteristics.filter(el => !el.is_hidden)"
                                        :data-label="characteristic.name">
                                        @{{tech.category_characteristics.some(char => char.id === characteristic.id) ?
                                        tech.category_characteristics.find(char => char.id ===
                                        characteristic.id).data.value ? tech.category_characteristics.find(char => char.id ===
                                        characteristic.id).data.value : '-' : '-'}}
                                    </td>
                                </template>
                                <td data-label="Местоположение">
                                    @{{tech.start_location ? tech.start_location.address : 'не указано'}}
                                </td>
                                <td class="text-right actions">
                                    <button rel="tooltip" data-original-title="Просмотр"
                                            class="btn btn-link btn-xs btn-space btn-primary mn-0" @click="showTech(tech)">
                                        <i class="fa fa-eye"></i>
                                    </button>
                                </td>
                            </tr>
                        </tbody>
                    </table>
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
</div>

<!-- modals -->
<!-- card -->
<div class="modal fade bd-example-modal-lg show" id="card_tech" role="dialog" aria-labelledby="modal-search" style="display: none;">
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
                                        Категория
                                    </span>
                                    <span class="task-info__body-title">
                                        {{ $data['category']->name }}
                                    </span>
                                </div>
                                <div class="task-info__text-unit">
                                    <span class="task-info__head-title">
                                        Статус
                                    </span>
                                    <span :class="`task-info__body-title ${getStatusClass(tech_status)}`" >
                                        {{--TODO add tech status--}}
                                        @{{tech_status}}
                                    </span>
                                </div>
                                <div class="task-info__text-unit">
                                    <span class="task-info__head-title">
                                        Инвентарный номер
                                    </span>
                                    <span class="task-info__body-title">
                                        @{{tech.inventory_number}}
                                    </span>
                                </div>
                                <div class="task-info__text-unit">
                                    <span class="task-info__head-title">
                                        Юридическое лицо
                                    </span>
                                    <span class="task-info__body-title">
                                        @{{tech.owner}}
                                    </span>
                                </div>
                                <div class="task-info__text-unit">
                                    <span class="task-info__head-title">
                                        Местоположение
                                    </span>
                                    <span class="task-info__body-title">
                                        @{{tech.start_location ? tech.start_location.name : ''}}
                                        <br>
                                        @{{tech.start_location ? tech.start_location.address : ''}}
                                    </span>
                                </div>
                                <div class="task-info__text-unit">
                                    <span class="task-info__head-title">
                                        Дата начала эксплуатации
                                    </span>
                                    <span class="task-info__body-title">
                                        @{{tech.exploitation_start ? tech.exploitation_start.split(' ')[0].split('-').reverse().join('.') : ''}}
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-8">
                        <div class="left-bar-main">
                            <h4 class="mn-0 fw-500" style="font-size: 26px">@{{ tech.brand }} @{{ tech.model }}</h4>
                            <h6 class="decor-h6-modal">Документация</h6>
                            <div class="modal-section">
                                <div class="row">
                                    <div class="col-md-12">
                                        <template v-if="!tech.documents || tech.documents.length === 0">
                                            <a class="tech-link modal-link">
                                                Документы не найдены
                                            </a>
                                        </template>
                                        <template v-else v-for="file in tech.documents">
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
                                                    <tr v-for="characteristic in category.category_characteristics">
                                                        <td data-label="Параметр">
                                                            @{{ characteristic.name }}@{{characteristic.unit ? (', ' + characteristic.unit) : ''}}
                                                        </td>
                                                        <td class="text-center" data-label="Значение">
                                                            @{{ tech.category_characteristics ?
                                                            (tech.category_characteristics.find(el => el.id === characteristic.id) ?
                                                            ((tech.category_characteristics.find(el => el.id === characteristic.id).data.value ?
                                                            tech.category_characteristics.find(el => el.id === characteristic.id).data.value : '-') +
                                                            (characteristic.unit && tech.category_characteristics.find(el => el.id === characteristic.id).data.value ?
                                                            ('' + '') : '')) : '-') : '-' }}
                                                        </td>
                                                        <td class="text-center" data-label="Отображение в таблице">
                                                            @{{ characteristic.is_hidden ? 'нет' : 'да' }}
                                                        </td>
                                                    </tr>
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <h6 class="decor-h6-modal">Заявки</h6>
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
{{--                                        <a href="#" class="purple-link small-transition-link">Просмотр всех заявок</a><span class="purple-link"> → </span>--}}
                                    </div>
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
{{--                                        <a href="#" class="purple-link small-transition-link ">Просмотр всех заявок на ремонт</a><span class="purple-link"> → </span>--}}
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
        router: new VueRouter({
            mode: 'history',
            routes: [],
        }),
        data: {
            PAGE_SIZE: 15,
            DONE_TYPING_INTERVAL: 1000,
            techs: JSON.parse('{!! addslashes(json_encode($data['technics'])) !!}'),
            category: JSON.parse('{!! addslashes(json_encode($data['category'])) !!}'),
            selected_characteristic_id: JSON.parse('{!! addslashes(json_encode($data['category'])) !!}').category_characteristics.find(el => !el.is_hidden) ?
                JSON.parse('{!! addslashes(json_encode($data['category'])) !!}').category_characteristics.find(el => !el.is_hidden).id : '',
            search_tf: {!! json_encode(Request::get('search')) !!},
            filter_attributes: [
                {label: '№', value: 'id'},
                {label: 'Марка', value: 'brand'},
                {label: 'Модель', value: 'model'},
                {label: 'Инвентарный номер', value: 'inventory_number'},
                {label: 'Местоположение', value: 'start_location_id'},
                {label: 'Статус', value: 'status'},
                {label: 'Юр. лицо', value: 'owner'},
            ],
            filter_attribute: '',
            filter_value: '',
            filter_location_name: '',
            locations: [],
            contractors: JSON.parse('{!! json_encode(array_values($data['owners']))  !!}'),
            statuses: JSON.parse('{!! json_encode(array_values($data['statuses']))  !!}'),
            filters: [],
            window_width: 10000,
            typingTimer: null,
            currentPage: 1,
            totalItems: {!! json_encode($data['technicsCount']) !!},
        },
        created() {
            this.filter_attributes.push(...this.category.category_characteristics
            //.filter(el => !el.is_hidden)
                .map(el => ({
                    label: el.name + (el.unit ? (', ' + el.unit) : ''),
                    value: `category_characteristics.${el.id}`
                })));
            if (this.$route.query.page && !Array.isArray(this.$route.query.page)) {
                this.currentPage = +this.$route.query.page;
            }
            Object.entries(this.$route.query).forEach(entry => {
                let field = entry[0].split('[')[0];

                let value = Array.isArray(entry[1]) ? entry[1][0] : entry[1];
                if (entry[1] && this.filter_attributes.map(attr => attr.value).indexOf(field) !== -1) {
                    let customName = '';
                    if (field === 'start_location_id') {
                        this.promisedSearchLocations('')
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
            //attempt to fix modal scroll
            /*$('body').on('hidden.bs.modal', function () {
                if($('.modal.show').length > 0)
                {
                    $('body').addClass('modal-open');
                }
            });*/
            $('#filter-value-tf').on('keypress', function (e) {
                if(e.which === 13){
                    vm.addFilter();
                }
            });
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
            selected_characteristic_name() {
                const selected_characteristic = this.category.category_characteristics.find(el => el.id === this.selected_characteristic_id);
                return selected_characteristic ? (selected_characteristic.name + (selected_characteristic.unit ? (', ' + selected_characteristic.unit) : '' )) : 'Выберите характеристику';
            },
            filtered_statuses() {
                return this.statuses.filter(el => this.filters.filter(filter => filter.field === 'status').map(filter => filter.value).indexOf(el) === -1);
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
        methods: {
            doneTyping() {
                const queryObj = {};
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
                this.updateFilteredTechnics();
            },
            changePage(page) {
                this.$router.replace({query: Object.assign({}, this.$route.query, {page: page})}).catch(err => {});
                this.updateFilteredTechnics();
            },
            resetCurrentPage() {
                this.$router.replace({query: Object.assign({}, this.$route.query, {page: 1})}).catch(err => {});
                this.currentPage = 1;
                this.updateFilteredTechnics();
            },
            getStatusClass(status) {
                switch (status.toLowerCase()) {
                    case 'свободен':
                        return 'free-status';
                    case 'в работе':
                        return 'busy-status';
                    case 'ремонт':
                        return 'repairs-status';
                }
            },
            addFilter() {
                if (this.filter_value && this.filter_attribute) {
                    this.filters.push({
                        attribute: this.filter_attributes.find(el => el.value === this.filter_attribute).label,
                        field: this.filter_attribute,
                        value: this.filter_value,
                        location_name: this.filter_location_name,
                    });
                    const queryObj = {};
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
                    this.resetCurrentPage();
                    this.$forceUpdate();
                }
            },
            updateCurrentLocationName() {
              this.filter_location_name = this.locations.find(loc => loc.id === this.filter_value).name;
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

                this.filters.splice(index, 1);
                this.updateFilteredTechnics();
            },
            clearFilters() {
                this.filters = [];
                if (this.currentPage !== 1) {
                    this.$router.replace({query: {page: this.currentPage}}).catch(err => {});
                } else {
                    this.$router.replace({query: {}}).catch(err => {});
                }
                this.updateFilteredTechnics();
            },
            updateFilteredTechnics() {
                axios.post('{{ route('building::tech_acc::get_trashed_technics_paginated', $data['category']->id) }}', {url: vm.$route.fullPath, page: vm.currentPage})
                    .then(response => {
                        vm.techs = response.data.ourTechnics;
                        vm.totalItems = response.data.ourTechnicCount;
                    })
                    .catch(error => console.log(error));
            },
            handleResize() {
                this.window_width = $(window).width();
            },
            showTech(tech) {
                cardTech.update(tech);
                $('#card_tech').modal('show');
                $('#card_tech').focus();
                $('.modal').css('overflow-y', 'auto');
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
        }
    });

    var cardTech = new Vue ({
        el:'#card_tech',
        data: {
            tech_status: 'Свободен',
            category: JSON.parse('{!! addslashes(json_encode($data['category'])) !!}'),
            tech: {}
        },
        methods: {
            getStatusClass(status) {
                return vm.getStatusClass(status);
            },
            update(tech) {
                this.tech = tech;
            },
            edit_tech_modal_show() {
                formTech.update(this.tech);
                $('#card_tech').modal('hide');
                $('#form_tech').modal('show');
                $('.modal').css('overflow-y', 'auto');
                $('#form_tech').focus();
            }
        }
    });

</script>
@endsection
