@extends('layouts.app')

@section('title', 'Договоры')

@section('url', route('contracts::index'))

@section('css_top')
    <style>
        @media (min-width: 4000px)  {
            .tooltip {
                left:65px!important;
            }
        }

        @media (min-width: 3600px) and (max-width: 4000px)  {
            .tooltip {
                left:45px!important;
            }
        }

        svg {
            width: 100%;
            height: 100%;
            stroke: #444;
            fill: #444;
        }

        div.spinner {
            width: 28px;
            height: 28px;
            margin-left: auto;
            margin-right: auto;
        }

        th.text-truncate {
            position: relative;
            overflow: visible;
            cursor: default !important;
        }
        @media (min-width: 768px) {
            span.text-truncate {
                max-width: 30px;
            }
        }
        @media (min-width: 1200px) {
            span.text-truncate {
                max-width: 50px;
            }
        }
        @media (min-width: 1360px) {
            span.text-truncate {
                max-width: 80px;
            }
        }
        @media (min-width: 1560px) {
            span.text-truncate {
                max-width: 140px;
            }
        }
        @media (min-width: 1920px) {
            span.text-truncate {
                max-width: 220px;
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
        <div class="card col-xl-12 mr-auto ml-auto pd-0-min" style="border:1px solid rgba(0,0,0,.125);">
            <div class="card-body-tech">
                <div class="row">
                    <div class="col-md-12">
                        <h6 class="mb-20" style="margin-top:5px">Фильтры</h6>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-4">
                        <label>Артибут</label>
                        <el-select v-model="filter_attribute" clearable placeholder="Выберите атрибут">
                            <el-option v-for="attr in filter_attributes_filtered" :label="attr.label"
                                       :value="attr.value"
                            ></el-option>
                        </el-select>
                    </div>
                    <div class="col-md-5 mt-10__mobile">
                        <label for="count">Значение</label>
                        <el-select v-if="filter_attribute === 'contractors.short_name'"
                                   v-model="filter_value"
                                   clearable filterable
                                   :remote-method="searchContractors"
                                   @keyup.native.enter="() => {addFilter(); $refs['short_name-filter'].blur();}"
                                    ref="short_name-filter"
                                   @change="updateCurrentCustomName"
                                   @clear="searchContractors('')"
                                   remote
                                   placeholder="Выберите контрагента"
                        >
                            <el-option
                                v-for="item in contractors"
                                :key="item.id"
                                :label="item.name"
                                :value="item.name"
                            ></el-option>
                        </el-select>
                        <el-select v-else-if="filter_attribute === 'project_objects.address'"
                                   v-model="filter_value"
                                   clearable filterable
                                   @keyup.native.enter="() => {addFilter(); $refs['project_objects-filter'].blur();}"
                                    ref="project_objects-filter"
                                   :remote-method="searchObjects"
                                   @change="updateCurrentCustomName"
                                   @clear="searchObjects('')"
                                   remote
                                   placeholder="Поиск объекта"
                        >
                            <el-option
                                v-for="item in objects"
                                :key="item.id"
                                :label="item.name"
                                :value="item.name"
                            ></el-option>
                        </el-select>
                        <el-select v-else-if="filter_attribute === 'projects.name'"
                                   v-model="filter_value" filterable clearable
                                   clearable filterable
                                   @keyup.native.enter="() => {addFilter(); $refs['projects-filter'].blur();}"
                                    ref="projects-filter"
                                   :remote-method="searchProjects"
                                   @change="updateCurrentCustomName"
                                   @clear="searchProjects('')"
                                   remote
                                   placeholder="Поиск проекта"
                        >
                            <el-option
                                v-for="item in projects"
                                :key="item.id"
                                :label="item.name"
                                :value="item.name">
                            </el-option>
                        </el-select>
                        <el-select v-else-if="filter_attribute === 'contracts.name'"
                                   v-model="filter_value"
                                   @keyup.native.enter="() => {addFilter(); $refs['name-filter'].blur();}"
                                    ref="name-filter"
                                   @change="updateCurrentCustomName"
                                   clearable
                                   placeholder="Укажите тип договора"
                        >
                            <el-option
                                v-for="(item, index) in types"
                                :key="index"
                                :label="item"
                                :value="item">
                            </el-option>
                        </el-select>
                        <el-select v-else-if="filter_attribute === 'contracts.status'"
                                    @keyup.native.enter="() => {addFilter(); $refs['status-filter'].blur();}"
                                    ref="status-filter"
                                   v-model="filter_value"
                                   filterable
                                   @change="updateCurrentCustomName"
                                   placeholder="Укажите статус договора"
                        >
                            <el-option
                                v-for="(item, index) in statuses"
                                :key="index"
                                :label="item"
                                :value="index">
                            </el-option>
                        </el-select>
                        <el-select v-else-if="filter_attribute === 'projects.entity'"
                                    @keyup.native.enter="() => {addFilter(); $refs['entity-filter'].blur();}"
                                    ref="entity-filter"
                                   v-model="filter_value"
                                   @change="updateCurrentCustomName"
                                   placeholder="Укажите юридическое лицо"
                        >
                            <el-option
                                v-for="(item, index) in entities"
                                :key="index"
                                :label="item"
                                :value="index">
                            </el-option>
                        </el-select>
                        <div :class="window_width > 769 ? 'd-flex' : ''" v-else-if="filter_attribute === 'contracts.created_at'">
                            <el-date-picker
                                style="cursor:pointer; margin-right: 30px"
                                v-model="filter_value"
                                @keyup.native.enter="() => {addFilter(); $refs['datef-filter'].blur();}"
                                    ref="datef-filter"
                                format="dd.MM.yyyy"
                                value-format="dd.MM.yyyy"
                                type="date"
                                @change="updateCurrentCustomName"
                                placeholder="Дата от"
                                :picker-options="{firstDayOfWeek: 1}"
                            ></el-date-picker>
                            <el-date-picker
                                style="cursor:pointer"
                                @keyup.native.enter="() => {addFilter(); $refs['datet-filter'].blur();}"
                                    ref="datet-filter"
                                v-model="filter_value_extra"
                                format="dd.MM.yyyy"
                                value-format="dd.MM.yyyy"
                                type="date"
                                @change="updateCurrentCustomName"
                                :class="window_width < 769 ? 'mt-10__mobile' : ''"
                                placeholder="Дата до"
                                :picker-options="{firstDayOfWeek: 1}"
                            ></el-date-picker>
                        </div>
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
        <div class="card col-xl-12 mr-auto ml-auto pd-0-min">
            <div class="card-body card-body-tech">
                <h4 class="h4-tech fw-500" style="margin-top:0"><span v-pre>Договоры</span></h4>
                <div class="fixed-table-toolbar toolbar-for-btn" style="padding-left:0">
                    <div class="row">
                        <div class="col-sm-4 col-md-3">
                            <el-input placeholder="Поиск" v-model="search_tf" clearable
                                    @keyup.native.enter="doneTyping"
                                      prefix-icon="el-icon-search" id="search-tf" @clear="doneTyping"
                            ></el-input>
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
                                Снять фильтры
                            </button>
                        </div>
                    </div>
                </div>
                <div class="table-responsive">
                    <table class="table table-hover mobile-table table-fix-column">
                        <thead>
                        <tr>
                            <th class="text-truncate text-center" data-balloon-pos="up-left" aria-label="№"><span class="text-truncate d-inline-block">№</span></th>
                            <th class="text-truncate" data-balloon-pos="up-left" aria-label="Внешний №"><span class="text-truncate d-inline-block">Внешний №</span></th>
                            <th class="text-truncate" data-balloon-pos="up-left" aria-label="Контрагент"><span class="text-truncate d-inline-block">Контрагент</span></th>
                            <th class="text-truncate" data-balloon-pos="up-left" aria-label="Адрес"><span class="text-truncate d-inline-block">Адрес</span></th>
                            <th class="text-truncate" data-balloon-pos="up-left" aria-label="Проект"><span class="text-truncate d-inline-block">Проект</span></th>
                            <th class="text-truncate" data-balloon-pos="up-left" aria-label="Тип"><span class="text-truncate d-inline-block">Тип</span></th>
                            <th class="text-truncate" data-balloon-pos="up-left" aria-label="Дата добавления"><span class="text-truncate d-inline-block">Дата добавления</span></th>
                            <th class="text-truncate text-center" data-balloon-pos="up-left" aria-label="Версия"><span class="text-truncate d-inline-block">Версия</span></th>
                            <th class="text-truncate" data-balloon-pos="up-left" aria-label="Статус"><span class="text-truncate d-inline-block">Статус</span></th>
                            <th class="text-truncate" data-balloon-pos="up-left" aria-label="Юр. Лицо"><span class="text-truncate d-inline-block">Юр. Лицо</span></th>
                            <th class="text-truncate text-right" data-balloon-pos="up-left" aria-label="Действия"><span class="text-truncate d-inline-block">Действия</span></th>
                            <th></th>
                        </tr>
                        </thead>
                        <tbody>
                            <tr v-if="loading">
                                <td></td>
                                <td></td>
                                <td></td>
                                <td></td>
                                <td></td>
                                <td><div class="spinner"><span icon="lines" class="spinner spinner-lines"><svg viewBox="0 0 64 64"><g stroke-width="7" stroke-linecap="round"><line x1="10" x2="10" y1="20.3828" y2="43.6172"><animate attributeName="y1" dur="750ms" values="16;18;28;18;16;16" repeatCount="indefinite"></animate><animate attributeName="y2" dur="750ms" values="48;46;36;44;48;48" repeatCount="indefinite"></animate><animate attributeName="stroke-opacity" dur="750ms" values="1;.4;.5;.8;1;1" repeatCount="indefinite"></animate></line><line x1="24" x2="24" y1="16.4766" y2="47.5234"><animate attributeName="y1" dur="750ms" values="16;16;18;28;18;16" repeatCount="indefinite"></animate><animate attributeName="y2" dur="750ms" values="48;48;46;36;44;48" repeatCount="indefinite"></animate><animate attributeName="stroke-opacity" dur="750ms" values="1;1;.4;.5;.8;1" repeatCount="indefinite"></animate></line><line x1="38" x2="38" y1="16" y2="48"><animate attributeName="y1" dur="750ms" values="18;16;16;18;28;18" repeatCount="indefinite"></animate><animate attributeName="y2" dur="750ms" values="44;48;48;46;36;44" repeatCount="indefinite"></animate><animate attributeName="stroke-opacity" dur="750ms" values=".8;1;1;.4;.5;.8" repeatCount="indefinite"></animate></line><line x1="52" x2="52" y1="17.5234" y2="44.9531"><animate attributeName="y1" dur="750ms" values="28;18;16;16;18;28" repeatCount="indefinite"></animate><animate attributeName="y2" dur="750ms" values="36;44;48;48;46;36" repeatCount="indefinite"></animate><animate attributeName="stroke-opacity" dur="750ms" values=".5;.8;1;1;.4;.5" repeatCount="indefinite"></animate></line></g></svg></span>
                                    </div>
                                </td>
                                <td></td>
                                <td></td>
                                <td></td>
                                <td></td>
                                <td></td>
                                <td></td>
                            </tr>
                            <tr v-else-if="contracts.length === 0">
                                <td></td>
                                <td></td>
                                <td></td>
                                <td></td>
                                <td></td>
                                <td>Нет данных</td>
                                <td></td>
                                <td></td>
                                <td></td>
                                <td></td>
                                <td></td>
                                <td></td>
                            </tr>
                            <template v-if="!loading && contracts.length > 0" v-for="parent in contracts.filter(el => !el.main_contract_id)">
                                <tr v-for="contract in [parent]"
                                    {{--:class="contract.version < contract_map[contract.contract_id].length && contract_map[contract.contract_id].length > 1
                                    ? `collapseContract${contract.contract_id} contact-note card-collapse collapse activity-detailed` : ''"--}}
                                    style="cursor:default">
                                    @include('contracts.modules.contract_row_columns')
                                </tr>
                                <tr v-for="contract in contracts.filter(el => el.main_contract_id === parent.id)"
                                    {{--:class="contract.version < contract_map[contract.contract_id].length && contract_map[contract.contract_id].length > 1
                                    ? `collapseContract${contract.contract_id} contact-note card-collapse collapse activity-detailed` : ''"--}}
                                    style="cursor:default">
                                    @include('contracts.modules.contract_row_columns')
                                </tr>
                            </template>
                            <template v-if="!loading && contracts.length > 0">
                                <tr v-for="contract in contracts_headless"
                                    {{--:class="contract.version < contract_map[contract.contract_id].length && contract_map[contract.contract_id].length > 1
                                    ? `collapseContract${contract.contract_id} contact-note card-collapse collapse activity-detailed` : ''"--}}
                                    style="cursor:default">
                                    @include('contracts.modules.contract_row_columns')
                                </tr>
                            </template>
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

@endsection

@section('js_footer')
    <script type="text/javascript">
        vm = new Vue({
            el: '#base',
            router: new VueRouter({
                mode: 'history',
                routes: [],
            }),
            data: {
                PAGE_SIZE: 30,
                SINGLE_USE_FILTERS: ['contracts.created_at', 'projects.entity'],
                totalItems: {!! json_encode($data['contracts_count']) !!},
                currentPage: 1,

                contracts: {!! json_encode($data['contracts']) !!},
                entities: {!! json_encode($data['entities'])  !!},
                contract_map: {!! json_encode($data['contract_map'])  !!},
                types: {!! json_encode($data['types'])  !!},
                statuses: {!! json_encode($data['statuses'])  !!},

                search_tf: '',
                search_queries: [],

                filter_attributes: [
                    {label: '№', value: 'contracts.contract_id'},
                    {label: 'Внешний №', value: 'contracts.foreign_id'},
                    {label: 'Дата добавления', value: 'contracts.created_at'},
                    {label: 'Контрагент', value: 'contractors.short_name'},
                    {label: 'Адрес', value: 'project_objects.address'},
                    {label: 'Проект', value: 'projects.name'},
                    {label: 'Тип', value: 'contracts.name'},
                    {label: 'Статус', value: 'contracts.status'},
                    {label: 'Юр. Лицо', value: 'projects.entity'},
                ],
                filter_attribute: '',
                filter_value: '',
                filter_value_extra: '',
                filter_custom_name: '',
                filters: [],
                show_active: true,

                contractors: [],
                objects: [],
                projects: [],

                loading: false,

                window_width: 10000,
            },
            created() {
                if (this.$route.query.page && !Array.isArray(this.$route.query.page)) {
                    this.currentPage = +this.$route.query.page;
                }
                let url_search = '{{Request::get('search')}}';

                if (url_search) {
                    url_search = url_search.split('•');
                    for (i in url_search) {
                        if (!Number.isNaN(+i)) {
                            this.search_queries.push(url_search[i]);
                        }
                    }
                }
                Object.entries(this.$route.query).forEach(entry => {
                    let field = entry[0].split('[')[0];
                    let value = Array.isArray(entry[1]) ? entry[1][0] : entry[1];
                    if (entry[1] && this.filter_attributes_filtered.map(attr => attr.value).indexOf(field) !== -1) {
                        let customName = '';
                        if (field === 'contracts.status') {
                            customName = this.statuses[value] ? this.statuses[value] : '';
                            this.filters.push({
                                attribute: this.filter_attributes.find(el => el.value === field).label,
                                field: field,
                                value: value,
                                custom_name: customName,
                            });
                        } else if (field === 'projects.entity') {
                            customName = this.entities[value] ? this.entities[value] : '';
                            this.filters.push({
                                attribute: this.filter_attributes.find(el => el.value === field).label,
                                field: field,
                                value: value,
                                custom_name: customName,
                            });
                        } else if (field === 'contracts.created_at') {
                            customName = `от ${value.split('|')[0]} до ${value.split('|')[1]}`;
                            this.filters.push({
                                attribute: this.filter_attributes.find(el => el.value === field).label,
                                field: field,
                                value: value,
                                custom_name: customName,
                            });
                        } else {
                            this.filters.push({
                                attribute: this.filter_attributes.find(el => el.value === field).label,
                                field: field,
                                value: value,
                                custom_name: customName,
                            });
                            this.$forceUpdate();
                        }
                    }
                });
                this.searchContractors('');
                this.searchObjects('');
                this.searchProjects('');
            },
            mounted() {
                let searchTF = $('#search-tf');

                // searchTF.on('keyup', (e) => {
                //     clearTimeout(this.typingTimer);
                //     if(e.which != 13){
                //         // this.typingTimer = setTimeout(this.doneTyping, this.DONE_TYPING_INTERVAL);
                //     } else {
                //         searchTF.blur();
                //     }
                // });

                searchTF.on('blur', (e) => {
                    // clearTimeout(this.typingTimer);
                    this.doneTyping();
                });

                $(window).on('resize', this.handleResize);
                this.handleResize();
            },
            watch: {
                filter_attribute() {
                    this.filter_value = '';
                    this.filter_custom_name = '';
                },
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
                            return false;
                        } else {
                            return true;
                        }
                    });
                },
                search_queries_compact() {
                    return this.search_queries.length > 0
                        ? `${this.search_queries.join('•')}`
                        : '';
                },
                contracts_headless() {
                    return this.contracts.filter(el =>
                        el.main_contract_id
                        && this.contracts.map(contr => contr.id).indexOf(el.main_contract_id) === -1
                    );
                },
            },
            methods: {
                doneTyping() {
                    const queryObj = {};
                    if (this.search_tf.trim()) {
                        this.search_queries.push(this.search_tf.trim());
                        this.search_tf = '';
                        Object.assign(queryObj, this.$route.query);
                        queryObj['search'] = this.search_queries_compact;
                        this.$router.replace({query: Object.assign({}, queryObj)}).catch(err => {});
                    } else {
                        // Object.assign(queryObj, this.$route.query);
                        // delete queryObj['search'];
                        // this.$router.replace({query: Object.assign({}, queryObj)}).catch(err => {});
                    }
                    this.resetCurrentPage();
                },
                delete_search_budge: function (index) {
                    const queryObj = {};
                    this.search_queries.splice(index, 1);
                    if (this.search_queries_compact) {
                        Object.assign(queryObj, this.$route.query);
                        queryObj['search'] = this.search_queries_compact;
                        this.$router.replace({query: Object.assign({}, queryObj)}).catch(err => {});
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
                clear_search_queries() {
                    this.search_queries.splice(0, this.search_queries.length);
                    this.resetCurrentPage();
                },
                updateFilteredContracts() {
                    this.loading = true;
                    axios.post('{{ route('contracts::filtered') }}', {url: vm.$route.fullPath, page: vm.currentPage})
                        .then(response => {
                            vm.contracts = response.data.contracts;
                            vm.contracts_map = response.data.contracts_map;
                            vm.totalItems = response.data.contracts_count;
                            this.loading = false;
                        })
                        .catch(error => {
                            console.log(error);
                            this.loading = false;
                        });
                },
                changePage(page) {
                    this.$router.replace({query: Object.assign({}, this.$route.query, {page: page})}).catch(err => {});
                    this.updateFilteredContracts();
                },
                resetCurrentPage() {
                    this.$router.replace({query: Object.assign({}, this.$route.query, {page: 1})}).catch(err => {});
                    this.currentPage = 1;
                    this.searchContractors('');
                    this.searchObjects('');
                    this.searchProjects('');
                    this.updateFilteredContracts();
                },
                addFilter() {
                    if (this.filter_value_extra && this.filter_value && this.filter_attribute || this.filter_value && this.filter_attribute !== 'contracts.created_at') {
                        const queryObj = {};
                        const combined_filter_value = this.filter_value_extra ? this.filter_value + '|' + this.filter_value_extra : this.filter_value;
                        this.filters.push({
                            attribute: this.filter_attributes.find(el => el.value === this.filter_attribute).label,
                            field: this.filter_attribute,
                            value: combined_filter_value,
                            custom_name: this.filter_custom_name,
                        });
                        const count = Object.keys(this.$route.query).filter(el => el.indexOf(this.filter_attribute) !== -1).length;
                        if (!count) {
                            queryObj[this.filter_attribute] = combined_filter_value;
                            this.$router.replace({query: Object.assign({}, this.$route.query, queryObj)}).catch(err => {});
                        } else if (count === 1) {
                            Object.assign(queryObj, this.$route.query);
                            queryObj[this.filter_attribute + '[0]'] = queryObj[this.filter_attribute];
                            delete queryObj[this.filter_attribute];
                            queryObj[this.filter_attribute + `[${count}]`] = combined_filter_value;
                            this.$router.replace({query: Object.assign({}, queryObj)}).catch(err => {});
                        } else {
                            queryObj[this.filter_attribute + `[${count}]`] = combined_filter_value;
                            this.$router.replace({query: Object.assign({}, this.$route.query, queryObj)}).catch(err => {});
                        }
                        this.filter_value = '';
                        this.filter_value_extra = '';
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
                rowTypeCondition(contract) {
                    return contract.version === this.contract_map[contract.contract_id].length;
                },
                updateCurrentCustomName() {
                    switch (this.filter_attribute) {
                        case 'contracts.created_at':
                            this.filter_custom_name = `от ${this.filter_value} до ${this.filter_value_extra}`;
                            break;
                        case 'contracts.status':
                            this.filter_custom_name = this.statuses[this.filter_value];
                            break;
                        case 'projects.entity':
                            this.filter_custom_name = this.entities[this.filter_value];
                    }
                },
                getFilterValueLabel(filter) {
                    switch(filter.field) {
                        case 'contracts.created_at':
                        case 'projects.entity':
                        case 'contracts.status': return filter.custom_name;
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
                    this.searchContractors('');
                    this.searchObjects('');
                    this.searchProjects('');
                    this.resetCurrentPage();
                },
                handleResize() {
                    this.window_width = $(window).width();
                },
                searchContractors(query) {
                    if (query) {
                        axios.get('{{ route('contractors::get_contractors') }}', {params: {q: query,}})
                            .then(response => this.contractors = response.data.map(el => ({
                                name: el.label,
                                id: el.code
                            })))
                            .catch(error => console.log(error));
                    } else {
                        axios.get('{{ route('contractors::get_contractors') }}')
                            .then(response => this.contractors = response.data.map(el => ({
                                name: el.label,
                                id: el.code
                            })))
                            .catch(error => console.log(error));
                    }
                },
                searchObjects(query) {
                    if (query) {
                        axios.post('{{ route('objects::get_objects') }}', {q: query,})
                            .then(response => this.objects = response.data.map(el => ({
                                name: el.label,
                                id: el.code
                            })))
                            .catch(error => console.log(error));
                    } else {
                        axios.post('{{ route('objects::get_objects') }}')
                            .then(response => this.objects = response.data.map(el => ({
                                name: el.label,
                                id: el.code
                            })))
                            .catch(error => console.log(error));
                    }
                },
                searchProjects(query) {
                    if (query) {
                        axios.get('{{ route('projects::get_projects') }}', {params: {
                                q: query,
                            }})
                            .then(response => this.projects = response.data.map(el => ({
                                name: el.label,
                                id: el.code
                            })))
                            .catch(error => console.log(error));
                    } else {
                        axios.get('{{ route('projects::get_projects') }}')
                            .then(response => this.projects = response.data.map(el => ({
                                name: el.label,
                                id: el.code
                            })))
                            .catch(error => console.log(error));
                    }
                },
            }
        });
    </script>
@endsection
