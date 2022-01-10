@extends('layouts.app')

@section('title', 'Неисправности')

@section('url', route('building::tech_acc::defects.index'))

@section('css_top')
    <style media="screen">
    @media(max-width: 1200px){
        .table-responsive {
            overflow-x: auto;
        }
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
                <li class="breadcrumb-item active" aria-current="page">Неисправности</li>
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
                    <div class="col-md-4 mt-10__mobile">
                        <label for="count">Значение</label>
                        <el-select v-if="filter_attribute === 'owner'"
                                   v-model="filter_value" filterable clearable
                                   @keyup.native.enter="() => {addFilter(); $refs['owner-filter'].blur();}"
                                   ref="owner-filter"
                                   placeholder="Выберите юр. лицо"
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
                                   @keyup.native.enter="() => {addFilter(); $refs['status-filter'].blur();}"
                                   ref="status-filter"
                        >
                            <el-option
                                v-for="(item, key) in statuses"
                                :key="key"
                                :value="item"
                            ></el-option>
                        </el-select>
                        <el-select v-else-if="filter_attribute === 'defectable'"
                            v-model="filter_value"
                                   clearable filterable
                                   @keyup.native.enter="() => {addFilter(); $refs['defectable-filter'].blur();}"
                                   ref="defectable-filter"
                                   :remote-method="searchTechs"
                                   @change="updateCurrentCustomName"
                                   @clear="searchTechs('')"
                                   remote
                                   placeholder="Поиск технического устройства"
                        >
                            <el-option
                                v-for="item in techs"
                                :key="item.id"
                                :label="item.name"
                                :value="item.id + '|' + item.defectable_type">
                            </el-option>
                        </el-select>
                        <el-select v-else-if="filter_attribute === 'responsible_user_id'"
                                   v-model="filter_value"
                                   @keyup.native.enter="() => {addFilter(); $refs['ruser-filter'].blur();}"
                                   ref="ruser-filter"
                                   clearable filterable
                                   :remote-method="searchExecutors"
                                   @clear="searchExecutors('')"
                                   @change="updateCurrentCustomName"
                                   remote
                                   placeholder="Поиск исполнителя"
                        >
                            <el-option
                                v-for="item in executors"
                                :key="item.id"
                                :label="item.name"
                                :value="item.id">
                            </el-option>
                        </el-select>
                        <el-select v-else-if="filter_attribute === 'user_id'"
                                   v-model="filter_value"
                                   @keyup.native.enter="() => {addFilter(); $refs['user-filter'].blur();}"
                                   ref="user-filter"
                                   clearable filterable
                                   :remote-method="searchUsers"
                                   @clear="searchUsers('')"
                                   @change="updateCurrentCustomName"
                                   remote
                                   placeholder="Поиск автора"
                        >
                            <el-option
                                v-for="item in users"
                                :key="item.id"
                                :label="item.name"
                                :value="item.id">
                            </el-option>
                        </el-select>
                        <el-date-picker
                            v-else-if="filter_attribute === 'repair_start_date'"
                            style="cursor:pointer"
                            v-model="filter_value"
                            format="dd.MM.yyyy"
                            @keyup.native.enter="() => {addFilter(); $refs['start-filter'].blur();}"
                            ref="start-filter"
                            value-format="dd.MM.yyyy"
                            type="date"
                            placeholder="Укажите дату начала ремонта"
                            :picker-options="{firstDayOfWeek: 1}"
                            @focus="onFocus"
                        ></el-date-picker>
                        <el-date-picker
                            v-else-if="filter_attribute === 'repair_end_date'"
                            style="cursor:pointer"
                            v-model="filter_value"
                            @keyup.native.enter="() => {addFilter(); $refs['end-filter'].blur();}"
                            ref="end-filter"
                            format="dd.MM.yyyy"
                            value-format="dd.MM.yyyy"
                            type="date"
                            placeholder="Укажите дату окончания ремонта"
                            :picker-options="{firstDayOfWeek: 1}"
                            @focus="onFocus"
                        ></el-date-picker>
                        <el-input v-else placeholder="Введите значение" v-model="filter_value"
                            @keyup.native.enter="() => {addFilter(); $refs['value-filter'].blur();}"
                                ref="value-filter"
                            id="filter-value-tf" clearable></el-input>
                    </div>
                    <div class="col-md-2 text-center--mobile" style="margin:29px 10px 20px 0">
                        <button type="button" class="btn btn-primary btn-outline" @click="addFilter">
                            Добавить
                        </button>
                    </div>
                </div>
                <div class="row d-none filter-title">
                    <div class="col-md-12" style="margin: 10px 0 10px 0">
                        <h6>Выбранные фильтры</h6>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-9">
                        <div class="bootstrap-tagsinput" style="margin-top:5px">
                            <span v-for="(filter, i) in filters" class="badge badge-azure">
                                @{{ filter.attribute }}: @{{ getFilterValueLabel(filter) }}
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
                <h4 class="h4-tech fw-500" style="margin-top:0"><span v-pre>Неисправности техники</span></h4>
                <div class="fixed-table-toolbar toolbar-for-btn" style="padding-left:0">
                    <div class="row">
                        <div class="col-sm-4 col-md-3">
                            <el-input placeholder="Поиск по наименованию и номеру" v-model="search_tf" clearable
                                    @keyup.native.enter="doneTyping"
                                      prefix-icon="el-icon-search" id="search-tf" @clear="doneTyping"
                            ></el-input>
                        </div>
                        <div class="col-sm-4 col-md-3">
                            <el-checkbox v-model="show_active" @change="toggleActive" class="mt-2" label="Скрыть неактивные заявки" style="text-transform: none"></el-checkbox>
                        </div>
                        @can('tech_acc_defects_create')
                            <div class="col-sm-4 col-md-6 text-right mt-10__mobile">
                                <button type="button" name="button" class="btn btn-sm btn-primary btn-round"
                                        @click="createDefect">Создать заявку на ремонт
                                </button>
                            </div>
                        @endcan
                    </div>
                </div>
                <div class="table-responsive">
                    <table class="table table-hover mobile-table table-fix-column">
                        <thead>
                            <tr>
                                <th class="text-truncate" data-balloon-pos="up-left" aria-label="Техника"><span class="text-truncate d-inline-block">Техника</span></th>
                                <th class="text-truncate" data-balloon-pos="up-left" aria-label="Инв. номер"><span class="text-truncate d-inline-block">Инв. номер</span></th>
                                <th class="text-truncate" data-balloon-pos="up-left" aria-label="Статус"><span class="text-truncate d-inline-block">Статус</span></th>
                                <th class="text-truncate text-center" data-balloon-pos="up-left" aria-label="Нач. ремонта"><span class="text-truncate d-inline-block">Нач. ремонта</span></th>
                                <th class="text-truncate text-center" data-balloon-pos="up-left" aria-label="Оконч. ремонта"><span class="text-truncate d-inline-block">Оконч. ремонта</span></th>
                                <th class="text-truncate" data-balloon-pos="up-left" aria-label="Исполнитель"><span class="text-truncate d-inline-block">Исполнитель</span></th>
                                <th class="text-truncate" data-balloon-pos="up-left" aria-label="Юр. лицо"><span class="text-truncate d-inline-block">Юр. лицо</span></th>
                                <th></th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-for="defect in defects">
                                <td data-label="Техника">
                                    @{{ defect.defectable ? defect.defectable.name : '' }}
                                </td>
                                <td data-label="Инв. номер">
                                    @{{ defect.defectable ? (defect.defectable.inventory_number ? defect.defectable.inventory_number : defect.defectable.tank_number) : '' }}
                                </td>
                                <td data-label="Статус">
                                    <span :class="`${getStatusClass(defect.status)}`" >@{{ defect.status_name }}</span>
                                </td>
                                <td class="text-center" data-label="Начало ремонта">
                                    <span :class="isWeekendDay(defect.repair_start, 'DD.MM.YYYY') ? 'weekend-day' : ''">
                                        @{{ isValidDate(defect.repair_start, 'DD.MM.YYYY') ? weekdayDate(defect.repair_start, 'DD.MM.YYYY') : 'Не назначена' }}
                                    </span>
                                </td>
                                <td class="text-center" data-label="Окончание ремонта">
                                    <span :class="isWeekendDay(defect.repair_end, 'DD.MM.YYYY') ? 'weekend-day' : ''">
                                        @{{ isValidDate(defect.repair_end, 'DD.MM.YYYY') ? weekdayDate(defect.repair_end, 'DD.MM.YYYY') : 'Не назначена' }}
                                    </span>
                                </td>
                                <td data-label="Исполнитель">
                                    @{{ defect.responsible_user ? defect.responsible_user.full_name : 'Не назначен' }}
                                </td>
                                <td data-label="Юр. лицо">
                                    @{{ defect.defectable ? (defect.defectable.owner ? defect.defectable.owner : 'Отсутствует') : '' }}
                                </td>
                                <td class="text-right actions">
                                    <a data-balloon-pos="up" :href="'{{ route('building::tech_acc::defects.show', 'defect_id') }}'.split('defect_id').join(defect.id)"
                                       aria-label="Просмотр" class="btn btn-link btn-xs btn-space actions btn-primary mn-0">
                                        <i class="fa fa-external-link-alt"></i>
                                    </a>
                                    <button v-if="canDestroyDefect(defect)"
                                            data-balloon-pos="up"
                                            aria-label="Удалить"
                                            class="btn btn-link btn-xs btn-space btn-danger mn-0"
                                            @click="removeDefect(defect.id)">
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

@can('tech_acc_defects_create')
<!-- create -->
<div class="modal fade bd-example-modal-lg show" id="create_defect" role="dialog" aria-labelledby="modal-search" style="display: none;">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Создание заявки на ремонт</h5>
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
                                        <div class="col-md-12">
                                            <label for="">Техническое устройство<span class="star">*</span></label>
                                            <validation-provider rules="required" v-slot="v" vid="tech-select"
                                                                 ref="tech-select">
                                                <el-select v-model="tech"
                                                           :class="v.classes"
                                                           clearable filterable
                                                           id="tech-select"
                                                           :remote-method="searchTechs"
                                                           @clear="searchTechs('')"
                                                           remote
                                                           placeholder="Поиск технического устройства"
                                                >
                                                    <el-option
                                                        v-for="item in techs"
                                                        :label="item.name"
                                                        :value="item.id + '|' + item.defectable_type">
                                                    </el-option>
                                                </el-select>
                                                <div class="error-message">@{{ v.errors[0] }}</div>
                                            </validation-provider>
                                        </div>
                                    </div>
                                    <div class="row">
                                        <div class="col-md-12">
                                            <label for="">Описание неисправности<span class="star">*</span></label>
                                            <validation-provider rules="required|max:300" vid="description-input"
                                                                 ref="description-input" v-slot="v">
                                                <el-input
                                                    :class="v.classes"
                                                    type="textarea"
                                                    :rows="4"
                                                    maxlength="300"
                                                    id="description-input"
                                                    clearable
                                                    placeholder="Опишите неисправность"
                                                    v-model="description"
                                                ></el-input>
                                                <div class="error-message">@{{ v.errors[0] }}</div>
                                            </validation-provider>
                                        </div>
                                    </div>
                                    <div class="row">
                                        <div class="col-md-12">
                                            <label for="">Фото неисправности</label>
                                            <el-upload
                                                :drag="window_width > 769"
                                                action="{{ route('file_entry.store') }}"
                                                :headers="{ 'X-CSRF-TOKEN': '{{ csrf_token() }}' }"
                                                :limit="10"
                                                :before-upload="beforePhotoUpload"
                                                :on-preview="handlePreview"
                                                :on-remove="handleRemove"
                                                :on-exceed="handlePhotoExceed"
                                                :on-success="handleSuccess"
                                                :on-error="handleError"
                                                :file-list="photoList"
                                                multiple
                                            >
                                                <template v-if="window_width > 769">
                                                    <i class="el-icon-upload"></i>
                                                    <div class="el-upload__text">Перетащите сюда или <em>кликните, чтобы выбрать файлы для загрузки</em></div>
                                                </template>
                                                <el-button v-else size="small" type="primary">Загрузить</el-button>
                                                <div class="el-upload__tip" slot="tip">Файлы формата jpg/png размером до 5Мб</div>
                                            </el-upload>
                                        </div>
                                    </div>
                                    <div class="row">
                                        <div class="col-md-12">
                                            <label for="">Видеозапись неисправности</label>
                                            <el-upload
                                                :drag="window_width > 769"
                                                action="{{ route('file_entry.store') }}"
                                                :headers="{ 'X-CSRF-TOKEN': '{{ csrf_token() }}' }"
                                                :limit="3"
                                                :before-upload="beforeVideoUpload"
                                                :on-preview="handlePreview"
                                                :on-remove="handleRemove"
                                                :on-exceed="handleVideoExceed"
                                                :on-success="handleSuccess"
                                                :on-error="handleError"
                                                :file-list="videoList"
                                                multiple
                                            >
                                                <template v-if="window_width > 769">
                                                    <i class="el-icon-upload"></i>
                                                    <div class="el-upload__text">Перетащите сюда или <em>кликните, чтобы выбрать файлы для загрузки</em></div>
                                                </template>
                                                <el-button v-else size="small" type="primary">Загрузить</el-button>
                                                <div class="el-upload__tip" slot="tip">Файлы формата mp4 размером до 200Мб</div>
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
                    <el-button @click="submit" :loading="tech_loading" type="primary">Сохранить</el-button>
                </template>
                <div v-else class="col-md-12">
                    <div class="row justify-content-center mb-2">
                        <button type="button" class="btn btn-secondary w-100" data-dismiss="modal">Закрыть</button>
                    </div>
                    <div class="row justify-content-center mb-2">
                        <el-button @click="submit" :loading="tech_loading" type="primary">Сохранить</el-button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endcan

@endsection
@section('js_footer')
<script type="text/javascript">
    Vue.component('validation-provider', VeeValidate.ValidationProvider);
    Vue.component('validation-observer', VeeValidate.ValidationObserver);
</script>
<script>
    vm = new Vue({
        el: '#base',
        router: new VueRouter({
            mode: 'history',
            routes: [],
        }),
        data: {
            PAGE_SIZE: 15,
            DONE_TYPING_INTERVAL: 1000,

            SINGLE_USE_FILTERS: ['repair_start_date', 'repair_end_date'],
            //TODO substitute with blade
            totalItems: {!! json_encode($data['defects_count']) !!},
            currentPage: 1,

            defects: JSON.parse('{!! addslashes(json_encode($data['defects'])) !!}'),
            contractors: JSON.parse('{!! json_encode(array_values($data['owners']))  !!}'),
            statuses: JSON.parse('{!! json_encode(array_values($data['statuses']))  !!}'),

            search_tf: '',
            filter_attributes: [
                {label: 'Статус', value: 'status'},
                {label: 'Автор', value: 'user_id'},
                {label: 'Исполнитель', value: 'responsible_user_id'},
                {label: 'Начало ремонта', value: 'repair_start_date'},
                {label: 'Окончание ремонта', value: 'repair_end_date'},
                {label: 'Техническое устройство', value: 'defectable'},
                {label: 'Марка техники', value: 'brand'},
                {label: 'Модель техники', value: 'model'},
                {label: 'Номер топливной ёмкости', value: 'tank_number'},
                {label: 'Инвентарный номер техники', value: 'inventory_number'},
                {label: 'Юридическое лицо', value: 'owner'},
            ],
            filter_attribute: '',
            filter_value: '',
            filter_custom_name: '',
            filters: [],
            show_active: true,

            techs: [],
            users: [],
            executors: [],

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
                    if (field === 'user_id') {
                        this.promisedSearchUsers('')//entry[1]
                            .then(() => {
                                const user = this.users.find(el => el.id == value);
                                customName = user ? user.name : '';
                                this.filters.push({
                                    attribute: this.filter_attributes.find(el => el.value === field).label,
                                    field: field,
                                    value: value,
                                    custom_name: customName,
                                });
                            })
                            .catch((error) => {console.log(error)})
                    } else if (field === 'responsible_user_id') {
                        this.promisedSearchExecutors('')//entry[1]
                            .then(() => {
                                const executor = this.executors.find(el => el.id == value);
                                customName = executor ? executor.name : '';
                                this.filters.push({
                                    attribute: this.filter_attributes.find(el => el.value === field).label,
                                    field: field,
                                    value: value,
                                    custom_name: customName,
                                });
                            })
                            .catch((error) => {console.log(error)})
                    } else if (field === 'defectable') {
                        this.promisedSearchTechs('')//entry[1]
                            .then(() => {
                                const tech = this.techs.find(el => {
                                    return el.id == value.split('|')[0] && el.defectable_type == value.split('|')[1];
                                });
                                customName = tech ? tech.name : '';
                                this.filters.push({
                                    attribute: this.filter_attributes.find(el => el.value === field).label,
                                    field: field,
                                    value: value,
                                    custom_name: customName,
                                });
                            })
                            .catch((error) => {
                                console.log(error)
                            });
                    } else {
                        this.filters.push({
                            attribute: this.filter_attributes.find(el => el.value === field).label,
                            field: field,
                            value: value,
                            custom_name: customName,
                        });
                    }
                    this.$forceUpdate();
                } else if (field === 'show_active' && value === 'false') {
                    this.show_active = false;
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
            this.searchTechs('');
            this.searchExecutors('');
            this.searchUsers('');
        },
        watch: {
            filter_attribute() {
                this.filter_value = '';
                this.filter_custom_name = '';
            }
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
        },
        methods: {
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
                }
            },
            getFilterValueLabel(filter) {
                switch(filter.field) {
                    case 'user_id':
                    case 'executor_id': return filter.custom_name;
                    default: return filter.value;
                }
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
            isWeekendDay(date, format) {
                return [5, 6].indexOf(moment(date, format).weekday()) !== -1;
            },
            isValidDate(date, format) {
                return moment(date, format).isValid();
            },
            weekdayDate(date, inputFormat, outputFormat) {
                return moment(date, inputFormat).format(outputFormat ? outputFormat : 'DD.MM.YYYY dd');
            },
            toggleActive() {
                const queryObj = {};
                if (!this.show_active) {
                    const count = Object.keys(this.$route.query).filter(el => el.indexOf('show_active') !== -1).length;
                    if (!count) {
                        queryObj['show_active'] = this.show_active;
                        this.$router.replace({query: Object.assign({}, this.$route.query, queryObj)}).catch(err => {});
                    } else {
                        Object.assign(queryObj, this.$route.query);
                        queryObj['show_active'] = this.show_active;
                        this.$router.replace({query: Object.assign({}, queryObj)}).catch(err => {});
                    }
                } else {
                    Object.assign(queryObj, this.$route.query);
                    delete queryObj['show_active'];
                    this.$router.replace({query: Object.assign({}, queryObj)}).catch(err => {});
                }
                this.resetCurrentPage();
            },
            createDefect() {
                $('#create_defect').modal('show');
                $('#create_defect').focus();
                $('.modal').css('overflow-y', 'auto');
            },
            updateFilteredDefects() {
                axios.post('{{ route('building::tech_acc::defects.paginated') }}', {url: vm.$route.fullPath, page: vm.currentPage})
                    .then(response => {
                        vm.defects = response.data.data.defects;
                        vm.totalItems = response.data.data.defects_count;
                    })
                    .catch(error => console.log(error));
            },
            changePage(page) {
                this.$router.replace({query: Object.assign({}, this.$route.query, {page: page})}).catch(err => {});
                this.updateFilteredDefects();
            },
            resetCurrentPage() {
                this.$router.replace({query: Object.assign({}, this.$route.query, {page: 1})}).catch(err => {});
                this.currentPage = 1;
                this.updateFilteredDefects();
            },
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
            onFocus: function() {
                $('.el-input__inner').blur();
            },
            updateCurrentCustomName() {
                switch (this.filter_attribute) {
                    case 'defectable':
                        const foundTech = this.techs.find(el => {
                            return el.id == this.filter_value.split('|')[0] && el.defectable_type == this.filter_value.split('|')[1];
                        });
                        this.filter_custom_name = foundTech ? foundTech.name : '';
                        break;
                    case 'user_id':
                        this.filter_custom_name = this.users.find(el => el.id === this.filter_value) ? this.users.find(el => el.id === this.filter_value).name : ''; break;
                    case 'responsible_user_id':
                        this.filter_custom_name = this.executors.find(el => el.id === this.filter_value) ? this.executors.find(el => el.id === this.filter_value).name : ''; break;
                }
            },
            getFilterValueLabel(filter) {
                switch(filter.field) {
                    case 'user_id':
                    case 'responsible_user_id':
                    case 'defectable': return filter.custom_name;
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
                this.searchTechs('');
                this.searchExecutors('');
                this.searchUsers('');
                this.resetCurrentPage();
            },
            handleResize() {
                this.window_width = $(window).width();
            },
            promisedSearchTechs(query) {
                return new Promise((resolve, reject) => {
                    if (query) {
                        axios.get('{{ route('building::tech_acc::get_all_technics') }}', {params: {q: query,}})
                            .then(response => {
                                this.techs = response.data.data.map(el => ({
                                    defectable_type: el.defectable_type,
                                    name: el.name,
                                    id: el.id
                                }));
                                resolve(response);
                            })
                            .catch(error => {
                                console.log(error);
                                reject(error);
                            });
                    } else {
                        axios.get('{{ route('building::tech_acc::get_all_technics') }}')
                            .then(response => {
                                this.techs = response.data.data.map(el => ({
                                    defectable_type: el.defectable_type,
                                    name: el.name,
                                    id: el.id
                                }));
                                resolve(response);
                            })
                            .catch(error => {
                                console.log(error);
                                reject(error);
                            });
                    }
                })
            },
            searchTechs(query) {
                if (query) {
                    axios.get('{{ route('building::tech_acc::get_all_technics') }}', {params: {q: query,}})
                        .then(response => this.techs = response.data.data.map(el => ({
                            defectable_type: el.defectable_type,
                            name: el.name,
                            id: el.id
                        })))
                        .catch(error => console.log(error));
                } else {
                    axios.get('{{ route('building::tech_acc::get_all_technics') }}')
                        .then(response => this.techs = response.data.data.map(el => ({
                            defectable_type: el.defectable_type,
                            name: el.name,
                            id: el.id
                        })))
                        .catch(error => console.log(error));
                }
            },
            promisedSearchExecutors(query) {
                return new Promise((resolve, reject) => {
                    if (query) {
                        axios.get('{{ route('users::get_users_for_tech_tickets') }}', {params: {
                                q: query,
                            }})
                            .then(response => {
                                this.executors = response.data.map(el => ({
                                    name: el.label,
                                    id: el.code
                                }));
                                resolve(response);
                            })
                            .catch(error => {
                                console.log(error);
                                reject(error);
                            });
                    } else {
                        axios.get('{{ route('users::get_users_for_tech_tickets') }}')
                            .then(response => {
                                this.executors = response.data.map(el => ({
                                    name: el.label,
                                    id: el.code
                                }));
                                resolve(response);
                            })
                            .catch(error => {
                                console.log(error);
                                reject(error);
                            });
                    }
                })
            },
            promisedSearchUsers(query) {
                return new Promise((resolve, reject) => {
                    if (query) {
                        axios.get('{{ route('users::get_users_for_tech_tickets') }}', {params: {
                                q: query,
                            }})
                            .then(response => {
                                this.users = response.data.map(el => ({
                                    name: el.label,
                                    id: el.code
                                }));
                                resolve(response);
                            })
                            .catch(error => {
                                console.log(error);
                                reject(error);
                            });
                    } else {
                        axios.get('{{ route('users::get_users_for_tech_tickets') }}')
                            .then(response => {
                                this.users = response.data.map(el => ({
                                    name: el.label,
                                    id: el.code
                                }));
                                resolve(response);
                            })
                            .catch(error => {
                                console.log(error);
                                reject(error);
                            });
                    }
                })
            },
            searchExecutors(query) {
                if (query) {
                    axios.get('{{ route('users::get_users_for_tech_tickets') }}', {params: {
                            q: query,
                        }})
                        .then(response => this.executors = response.data.map(el => ({
                            name: el.label,
                            id: el.code
                        })))
                        .catch(error => console.log(error));
                } else {
                    axios.get('{{ route('users::get_users_for_tech_tickets') }}')
                        .then(response => this.executors = response.data.map(el => ({
                            name: el.label,
                            id: el.code
                        })))
                        .catch(error => console.log(error));
                }
            },
            searchUsers(query) {
                if (query) {
                    axios.get('{{ route('users::get_users_for_tech_tickets') }}', {params: {
                            q: query,
                        }})
                        .then(response => this.users = response.data.map(el => ({
                            name: el.label,
                            id: el.code
                        })))
                        .catch(error => console.log(error));
                } else {
                    axios.get('{{ route('users::get_users_for_tech_tickets') }}')
                        .then(response => this.users = response.data.map(el => ({
                            name: el.label,
                            id: el.code
                        })))
                        .catch(error => console.log(error));
                }
            },
            canDestroyDefect(defect) {
                auth_id = {{ auth()->id() }};

                return (defect.user_id === auth_id && defect.status === 1) ;
            },
            removeDefect(id) {
                if (!this.loading) {
                    swal({
                        title: 'Вы уверены?',
                        text: "Заявка на неисправность будет удалена!",
                        type: 'warning',
                        showCancelButton: true,
                        cancelButtonText: 'Назад',
                        confirmButtonText: 'Удалить'
                    }).then((result) => {
                        if (result.value) {
                            axios.delete('{{ route('building::tech_acc::defects.destroy', ['']) }}' + '/' + id)
                            .then(() => {
                                location.reload();
                            })
                            //TODO add actual error handler
                            .catch(error => console.log(error));
                        }
                    });
                }
            },
        }
    });
</script>
<script type="text/javascript">
    var formCreate = new Vue({
        el: '#create_defect',
        data: {
            observer_key: 1,
            tech: '',
            techs: [],
            description: '',
            tech_loading: false,
            videoList: [],
            photoList: [],
            files_uploaded: [],
            window_width: 10000,

            PHOTO_AMOUNT_LIMIT: 10,
            PHOTO_SIZE_LIMIT: 5_000_000,
            PHOTO_ALLOWED_EXTENSIONS: ['jpg', 'jpeg', 'png'],

            VIDEO_AMOUNT_LIMIT: 3,
            VIDEO_SIZE_LIMIT: 200_000_000,
            VIDEO_ALLOWED_EXTENSIONS: ['mp4'],

            B_IN_MB: 1_000_000,

        },
        created() {
            this.searchTechs('');
        },
        mounted() {
            $(window).on('resize', this.handleResize);
            this.handleResize();
        },
        methods: {
            reset() {
                this.searchTechs('');
                this.tech = {};
                this.description = '';
                this.videoList = [];
                this.photoList = [];
                this.files_uploaded = [];
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
            handleRemove(file, fileList) {
                if (file.hasOwnProperty('response')) {
                    this.files_uploaded.splice(this.files_uploaded.findIndex(el => el.id === file.response.data[0].id));
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
                    title: "Ошибка загрузки файла",
                    html: message,
                });
            },
            handlePreview(file) {
                window.open(file.url ? file.url : '{{ asset('storage/docs/tech_accounting/') }}' + '/' + file.filename, '_blank');
                $('#form_tech').focus();
            },
            handlePhotoExceed(files, fileList) {
                this.$message.warning(`Невозможно загрузить ${files.length} файлов. Вы можете загрузить еще ${this.PHOTO_AMOUNT_LIMIT - fileList.length} файлов`);
            },
            handleVideoExceed(files, fileList) {
                this.$message.warning(`Невозможно загрузить ${files.length} файлов. Вы можете загрузить еще ${this.VIDEO_AMOUNT_LIMIT - fileList.length} файлов`);
            },
            beforePhotoUpload(file) {
                const nameParts = file.name.split('.');
                if (this.PHOTO_ALLOWED_EXTENSIONS.indexOf(nameParts[nameParts.length - 1]) === -1) {
                    this.$message.warning(`Ошибка загрузки файла. Разрешенные форматы: ${this.PHOTO_ALLOWED_EXTENSIONS.join(', ')}.`);
                    return false;
                }
                if (file.size > this.PHOTO_SIZE_LIMIT) {
                    this.$message.warning(`Ошибка загрузки файла. Размер файла не должен превышать ${this.PHOTO_SIZE_LIMIT/this.B_IN_MB}Мб`);
                    return false;
                }
                return true;
            },
            beforeVideoUpload(file) {
                const nameParts = file.name.split('.');
                if (this.VIDEO_ALLOWED_EXTENSIONS.indexOf(nameParts[nameParts.length - 1]) === -1) {
                    this.$message.warning(`Ошибка загрузки файла. Разрешенные форматы: ${this.VIDEO_ALLOWED_EXTENSIONS.join(', ')}.`);
                    return false;
                }
                if (file.size > this.VIDEO_SIZE_LIMIT) {
                    this.$message.warning(`Ошибка загрузки файла. Размер файла не должен превышать ${this.VIDEO_SIZE_LIMIT/this.B_IN_MB}Мб`);
                    return false;
                }
                return true;
            },
            searchTechs(query) {
                if (query) {
                    axios.get('{{ route('building::tech_acc::get_all_technics') }}', {params: {q: query,}})
                        .then(response => this.techs = response.data.data.map(el => ({
                            defectable_type: el.defectable_type,
                            name: el.name,
                            id: el.id
                        }))).catch(error => console.log(error));
                } else {
                    axios.get('{{ route('building::tech_acc::get_all_technics') }}')
                        .then(response => this.techs = response.data.data.map(el => ({
                            defectable_type: el.defectable_type,
                            name: el.name,
                            id: el.id
                        }))).catch(error => console.log(error));
                }
            },
            removeFiles(file_ids, tech_id) {
                if (file_ids.length > 0) {
                    file_ids.forEach(file_id => {
                        if (file_id) {
                            axios.delete('{{ route('file_entry.destroy', '') }}' + '/' + file_id)
                                .then(() => {
                                    const tech_documents = vm.techs.find(tech => tech.id === tech_id).documents;
                                    if (tech_documents) {
                                        tech_documents.splice(tech_documents.findIndex(doc => doc.id === file_id), 1)
                                    }
                                })
                                //TODO add actual error handler
                                .catch(error => console.log(error));
                        }
                    });
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

                    this.tech_loading = true;

                    const payload = {};
                    defectable = formCreate.tech.split('|');
                    payload.defectable_id = defectable[0];
                    payload.defectable_type = defectable[1];
                    payload.description = formCreate.description;
                    if (this.files_uploaded.length > 0) { payload.file_ids = this.files_uploaded.map(file => file.id); }

                    axios.post('{{ route('building::tech_acc::defects.store') }}', payload)
                    .then((response) => {
                        window.location = response.data.redirect;
                    }).catch(error => this.handleError(error));
                });
            },
        }
    });
</script>
@endsection
