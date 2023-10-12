@extends('layouts.app')

@section('title', 'Учет топлива')

@section('css_top')
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
                        <div class="col-md-4 mt-10__mobile">
                            <label for="count">Значение</label>
                            <el-select v-if="filter_attribute === 'object_id'"
                                       v-model="filter_value"
                                       clearable filterable
                                       @keyup.native.enter="() => {addFilter(); $refs['object_id-filter'].blur();}"
                                        ref="object_id-filter"
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
                            <el-input v-else placeholder="Введите значение" v-model="filter_value"
                                @keyup.native.enter="addFilter"
                                id="filter-value-tf" clearable></el-input>
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
                        <h4 class="h4-tech fw-500 m-0" style="margin-top:0"><span v-pre>Топливные емкости</span></h4>
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
                                <a href="{{ route('building::tech_acc::fuel_tank.index') }}" class="float-right btn btn-outline btn-sm">Просмотр обычных записей</a>
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
                                </td>
                                <td class="text-right actions">
                                    <button data-balloon-pos="up"
                                            aria-label="Просмотр"
                                            class="btn btn-link btn-xs btn-space btn-primary mn-0"
                                            @click="showFuel(fuel)">
                                        <i class="fa fa-eye"></i>
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
    <!-- card -->
    <div class="modal fade bd-example-modal-lg show" id="card_fuel" role="dialog" aria-labelledby="modal-search" style="display: none;">
        <div class="modal-dialog modal-lg" role="document" style="max-width:900px">
            <div class="modal-content pb-3">
                <div class="modal-header">
                    <h5 class="modal-title">Топливная емкость №@{{ fuel.tank_number }}</h5>
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
                            <a href="#collapse" target="_blank" class="text-primary font-weight-bold" style="font-size: 15px;"
                               data-target="#collapse" data-toggle="collapse">
                                Топливные записи
                            </a>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <a v-if="window_width > 769" href="#collapse" target="_blank" class="text-primary font-weight-bold" style="font-size: 15px;"
                       data-target="#collapse" data-toggle="collapse">
                        Топливные записи
                    </a>
                </div>
                <div class="px-3 pb-3 collapse card-collapse" id="collapse">
                    <el-table :data="fuel.trashed_operations" class="w-100">
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
                            label="Объем, л."
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
                    this.updateFilteredFuels();
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
                    axios.post('{{ route('building::tech_acc::get_trashed_fuel_tanks_paginated') }}', {url: vm.$route.fullPath, page: this.currentPage})
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
                    this.updateFilteredFuels();
                },
                clearFilters() {
                    this.filters = [];
                    if (this.currentPage !== 1) {
                        this.$router.replace({query: {page: this.currentPage}}).catch(err => {});
                    } else {
                        this.$router.replace({query: {}}).catch(err => {});
                    }
                    this.searchLocations('');
                    this.updateFilteredFuels();
                },
                resetCurrentPage() {
                    this.$router.replace({query: Object.assign({}, this.$route.query, {page: 1})}).catch(err => {});
                    this.currentPage = 1;
                    this.updateFilteredFuels();
                },
                handleResize() {
                    this.window_width = $(window).width();
                },
                showFuel(fuel) {
                    if (!vm.loading) {
                        if (fuel) {
                            if (!fuel.is_loaded) {
                                vm.loading = true;
                                let route = '{{ route('building::tech_acc::fuel_tank.show_trashed', ["ID_TO_SUBSTITUTE"]) }}';

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
                update(fuel) {
                    this.fuel = fuel;
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
    </script>
@endsection
