@extends('layouts.app')

@section('title', 'Учет техники')

@section('css_top')
    <link rel="stylesheet" href="{{ asset('css/balloon.css') }}">
    <style>
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

        .card .decor-h6-modal, .modal .decor-h6-modal {
            margin-bottom: 18px;
        }

        .right-bar-info .task-info__link {
            color: inherit;
        }

        .left-bar-main a.task-info__body-title, a {
            color: #164aad;
            font-weight: 400;
        }
        .left-bar-main a.task-info__body-title:hover, a:hover {
            color: #3067d1;
            text-decoration: underline;
        }

        .modal-link {
            text-decoration: underline;
        }

        .mobile-cb-group label.el-checkbox {
            width: 100% !important;
            margin-left: 0 !important;
        }

        .responsive-buttons-group button{
            width: 48%;
        }

        @media (max-width: 769px) {
            .responsive-buttons-group button{
                width: 100%;
                margin-left: -20px
            }
        }

        button.btn-remove-vehicle-mobile {
            margin-left: -2rem !important;
            padding: 0 !important;
            width: 40px !important;
            border: 0 !important;
            background: 0 !important;
            margin-bottom: 0 !important;
            margin-top: 39px !important;
        }
    </style>
@endsection

@section('content')
    <div class="row" id="base" v-cloak>
        <div class="col-md-12 mobile-card">
            <div class="card col-xl-10 mr-auto ml-auto pd-0-min" style="border:1px solid rgba(0,0,0,.125);">
                <div class="card-body-tech">
                    <div class="row">
                        <div class="col-md-12">
                            <h6 class="mb-20" style="margin-top:5px">Фильтры</h6>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-5">
                            <label>Атрибут</label>
                            <el-select v-model="filter_attribute" placeholder="Выберите атрибут">
                                <el-option v-for="attr in filter_attributes" :label="attr.label"
                                           :value="attr.value"
                                ></el-option>
                            </el-select>
                        </div>
                        <div class="col-md-4">
                            <label for="count">Значение</label>
                            <el-select
                                v-if="filter_attribute === 'sending_object_id'"
                                v-model="filter_value"
                                clearable filterable
                                :remote-method="searchStartLocations"
                                @keyup.native.enter="() => {addFilter(); $refs['sending_object_id-filter'].blur();}"
                                ref="sending_object_id-filter"
                                @change="updateCurrentCustomName"
                                @clear="searchStartLocations('')"
                                remote
                                placeholder="Поиск объекта"
                            >
                                <el-option
                                    v-for="item in startLocations"
                                    :key="item.id"
                                    :label="item.name"
                                    :value="item.id"
                                ></el-option>
                            </el-select>
                            <el-select
                                v-else-if="filter_attribute === 'getting_object_id'"
                                v-model="filter_value"
                                clearable filterable
                                @keyup.native.enter="() => {addFilter(); $refs['getting_object_id-filter'].blur();}"
                                ref="getting_object_id-filter"
                                :remote-method="searchEndLocations"
                                @change="updateCurrentCustomName"
                                @clear="searchEndLocations('')"
                                remote
                                placeholder="Поиск объекта"
                            >
                                <el-option
                                    v-for="item in endLocations"
                                    :key="item.id"
                                    :label="item.name"
                                    :value="item.id"
                                ></el-option>
                            </el-select>
                            <el-select
                                v-else-if="filter_attribute === 'status'"
                                v-model="filter_value"
                                clearable filterable
                                @keyup.native.enter="() => {addFilter(); $refs['status-filter'].blur();}"
                                ref="status-filter"
                                placeholder="Выберите статус"
                                @change="updateCurrentCustomName"
                            >
                                <el-option
                                    v-for="(value, key) in statuses"
                                    :key="key"
                                    :label="value"
                                    :value="value"
                                ></el-option>
                            </el-select>
                            <el-select v-else-if="filter_attribute === 'author_user_id'"
                                       v-model="filter_value"
                                       clearable filterable
                                       @keyup.native.enter="() => {addFilter(); $refs['author_user_id-filter'].blur();}"
                                       ref="author_user_id-filter"
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
                                    :value="item.id"
                                ></el-option>
                            </el-select>
                            <el-select v-else-if="filter_attribute === 'resp_rp_user_id'"
                                       v-model="filter_value"
                                       clearable filterable
                                       @keyup.native.enter="() => {addFilter(); $refs['resp_rp_user_id-filter'].blur();}"
                                       ref="resp_rp_user_id-filter"
                                       :remote-method="searchResponsibleRPs"
                                       @change="updateCurrentCustomName"
                                       @clear="searchResponsibleRPs('')"
                                       remote
                                       placeholder="Поиск ответственного РП"
                            >
                                <el-option
                                    v-for="item in responsibleRPs"
                                    :key="item.id"
                                    :label="item.name"
                                    :value="item.id">
                                </el-option>
                            </el-select>
                            <el-select v-else-if="filter_attribute === 'logist'"
                                       v-model="filter_value"
                                       clearable filterable
                                       @keyup.native.enter="() => {addFilter(); $refs['logist-filter'].blur();}"
                                       ref="logist-filter"
                                       :remote-method="searchResponsibleLogists"
                                       @change="updateCurrentCustomName"
                                       @clear="searchResponsibleLogists('')"
                                       remote
                                       placeholder="Поиск ответственного логиста"
                            >
                                <el-option
                                    v-for="item in responsibleLogists"
                                    :key="item.id"
                                    :label="item.name"
                                    :value="item.id">
                                </el-option>
                            </el-select>
                            <el-select v-else-if="filter_attribute === 'usage_resp_user_id'"
                                       v-model="filter_value"
                                       clearable filterable
                                       @keyup.native.enter="() => {addFilter(); $refs['usage_resp_user_id-filter'].blur();}"
                                       ref="usage_resp_user_id-filter"
                                       :remote-method="searchResponsibleUsers"
                                       @change="updateCurrentCustomName"
                                       @clear="searchResponsibleUsers('')"
                                       remote
                                       placeholder="Поиск ответственного за использования"
                            >
                                <el-option
                                    v-for="item in responsibleUsers"
                                    :key="item.id"
                                    :label="item.name"
                                    :value="item.id">
                                </el-option>
                            </el-select>
                            <el-input v-else placeholder="Введите значение" v-model="filter_value" id="filter-value-tf"
                                      clearable>
                            </el-input>
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
            <div class="card col-xl-10 mr-auto ml-auto pd-0-min">
                <div class="card-body card-body-tech">
                    <h4 class="h4-tech fw-500" style="margin-top:0"><span v-pre>Заявки на технику</span></h4>
                    <div class="fixed-table-toolbar toolbar-for-btn" style="padding-left:0">
                        <div class="row">
                            <div class="col-sm-4 col-md-3">
                                <el-input placeholder="Поиск по наименованию" v-model="search_tf" clearable
                                          @keyup.native.enter="doneTyping"
                                          prefix-icon="el-icon-search" id="search-tf" @clear="doneTyping"
                                ></el-input>
                            </div>
                            <div class="col-sm-3 col-md-3">
                                <el-checkbox v-model="show_active" @change="toggleActive" class="mt-2" label="Скрыть неактивные заявки" style="text-transform: none">
                            </div>
                            @can('create.OurTechnicTicket')
                            <div class="col-sm-5 col-md-6 text-right mt-10__mobile">
                                <button type="button" name="button" class="btn btn-sm btn-primary btn-round"
                                        @click="createTicket">Создать заявку
                                </button>
                            </div>
                            @endcan
                        </div>
                    </div>
                    <div class="table-responsive">
                        <table class="table table-hover mobile-table">
                            <thead>
                            <tr>
                                <th class="text-truncate" data-balloon-pos="up-left" aria-label="#"><span class="text-truncate d-inline-block">#</span></th>
                                <th class="text-truncate" data-balloon-pos="up-left" aria-label="Дата создания"><span class="text-truncate d-inline-block">Дата создания</span></th>
                                <th class="text-truncate" data-balloon-pos="up-left" aria-label="Марка"><span class="text-truncate d-inline-block">Марка</span></th>
                                <th class="text-truncate" data-balloon-pos="up-left" aria-label="Модель"><span class="text-truncate d-inline-block">Модель</span></th>
                                <th class="text-truncate" data-balloon-pos="up-left" aria-label="Ответственный РП"><span class="text-truncate d-inline-block">Ответственный РП</span></th>
                                <th class="text-truncate" data-balloon-pos="up-left" aria-label="Логист"><span class="text-truncate d-inline-block">Логист</span></th>
                                <th class="text-truncate" data-balloon-pos="up-left" aria-label="Автор"><span class="text-truncate d-inline-block">Автор</span></th>
                                <th class="text-truncate" data-balloon-pos="up-left" aria-label="Местоположение"><span class="text-truncate d-inline-block">Местоположение</span></th>
                                <th class="text-truncate" data-balloon-pos="up-left" aria-label="Статус"><span class="text-truncate d-inline-block">Статус</span></th>
                                <th class="text-truncate text-right"></th>
                            </tr>
                            </thead>
                            <tbody>
                            <tr v-for="ticket in tickets">
                                <td data-label="#">
                                    @{{ ticket.id }}
                                </td>
                                <td data-label="Дата создания">
                                    @{{ convertDateFormat(ticket.created_at, true) }}
                                </td>
                                <td data-label="Марка">
                                    @{{ ticket.short_data ? ticket.short_data.brand : '' }}
                                </td>
                                <td data-label="Модель">
                                    @{{ ticket.short_data ? ticket.short_data.model : '' }}
                                </td>
                                <td data-label="Ответственный РП">
                                    @{{ ticket.short_data ? ticket.short_data.resp_rp_name : '' }}
                                </td>
                                <td data-label="Логист">
                                    @{{ ticket.type > 1 && ticket.short_data ? ticket.short_data.process_resp_name : '-' }}
                                </td>
                                <td data-label="Автор">
                                    @{{ ticket.short_data ? ticket.short_data.author_name : '' }}
                                </td>
                                <td data-label="Местоположение">
                                    @{{ ticket.short_data ? ticket.short_data.object_adress : '' }}
                                </td>
                                <td data-label="Статус">
                                    <span :class="getStatusClass(ticket.short_data ? ticket.short_data.status_name : '')">@{{ ticket.short_data ? ticket.short_data.status_name : '' }}</span>
                                </td>
                                <td class="text-right actions">
                                    <button {{--rel="tooltip" data-original-title="Просмотр"--}}
                                            data-balloon-pos="up"
                                            aria-label="Просмотр"
                                            class="btn btn-link btn-xs btn-space btn-primary mn-0"
                                            @click="showTicket(ticket.id)">
                                        <i class="fa fa-eye"></i>
                                    </button>
                                    <button v-if="canRemoveTicket(ticket)"{{-- rel="tooltip" data-original-title="Удалить"--}}
                                            data-balloon-pos="up"
                                            aria-label="Удалить"
                                            class="btn btn-link btn-xs btn-space btn-danger mn-0"
                                            @click="removeTicket(ticket.id)">
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
<!-- main -->
    <!-- card -->
    @include('tech_accounting.tickets.modules.modals.all_modals')
    <!-- end extra -->
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
                loading: false,
                loadingInstance: {},
                tickets: {!! json_encode($data['tickets']) !!},
                search_tf: '',
                show_active: true,
                filter_attributes: [
                    {label: '№', value: 'id'},
                    {label: 'Марка', value: 'brand'},
                    {label: 'Модель', value: 'model'},
                    //{label: 'Инвентарный номер', value: 'inventory_number'},
                    {label: 'Место отправки', value: 'sending_object_id'},
                    {label: 'Место приемки', value: 'getting_object_id'},
                    // {label: 'Период отправки', value: 'start_period'},
                    // {label: 'Период приемки', value: 'end_period'},
                    {label: 'Статус', value: 'status'},
                    {label: 'Автор', value: 'author_user_id'},
                    {label: 'Ответственный РП', value: 'resp_rp_user_id'},
                    // {label: 'Ответственный логист', value: 'logist'},
                    {label: 'Пользователь', value: 'usage_resp_user_id'},
                ],

                loaded_filters: [],
                filter_attribute: '',
                filter_value: '',
                filter_name: '',
                currentPage: 1,
                totalItems: {!! json_encode($data['ticketsCount']) !!},
                DONE_TYPING_INTERVAL: 1000,

                startLocations: [],
                endLocations: [],
                responsibleRPs: [],
                responsibleLogists: [],
                responsibleUsers: [],
                authors: [],

                statuses: {!! json_encode($data['statuses']) !!},
                logist:  {!! json_encode($data['main_logist'])  !!},
                filters: [],
                typingTimer: null,
                window_width: 10000,
            },
            created() {
                if (this.$route.query.page && !Array.isArray(this.$route.query.page)) {
                    this.currentPage = +this.$route.query.page;
                }
                Object.entries(this.$route.query).forEach(entry => {
                    let field = entry[0].split('[')[0];
                    let value = Array.isArray(entry[1]) ? entry[1][0] : entry[1];
                    if (entry[1] && this.filter_attributes.map(attr => attr.value).indexOf(field) !== -1) {
                        let customName = '';
                        if (field === 'sending_object_id') {
                            this.promisedSearchStartLocations('')//entry[1]
                                .then(() => {
                                    const location = this.startLocations.find(el => el.id == value);
                                    customName = location ? location.name : '';
                                    this.filters.push({
                                        attribute: this.filter_attributes.find(el => el.value === field).label,
                                        field: field,
                                        value: value,
                                        custom_name: customName,
                                    });
                                })
                                .catch((error) => {console.log(error)})
                        } else if (field === 'getting_object_id') {
                            this.promisedSearchEndLocations('')//entry[1]
                                .then(() => {
                                    const location = this.endLocations.find(el => el.id == value);
                                    customName = location ? location.name : '';
                                    this.filters.push({
                                        attribute: this.filter_attributes.find(el => el.value === field).label,
                                        field: field,
                                        value: value,
                                        custom_name: customName,
                                    });
                                })
                                .catch((error) => {console.log(error)})
                        } else if (field === 'author_user_id') {
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
                        } else if (field === 'resp_rp_user_id') {
                            this.promisedSearchResponsibleRPs('')//entry[1]
                                .then(() => {
                                    const rp = this.responsibleRPs.find(el => el.id == value);
                                    customName = rp ? rp.name : '';
                                    this.filters.push({
                                        attribute: this.filter_attributes.find(el => el.value === field).label,
                                        field: field,
                                        value: value,
                                        custom_name: customName,
                                    });
                                })
                                .catch((error) => {console.log(error)})
                        } else if (field === 'logist') {
                            this.promisedSearchResponsibleLogists('')//entry[1]
                                .then(() => {
                                    const logist = this.responsibleLogists.find(el => el.id == value);
                                    customName = logist ? logist.name : '';
                                    this.filters.push({
                                        attribute: this.filter_attributes.find(el => el.value === field).label,
                                        field: field,
                                        value: value,
                                        custom_name: customName,
                                    });
                                })
                                .catch((error) => {console.log(error)})
                        } else if (field === 'usage_resp_user_id') {
                            this.promisedSearchResponsibleUsers('')//entry[1]
                                .then(() => {
                                    const user = this.responsibleUsers.find(el => el.id == value);
                                    customName = user ? user.name : '';
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
                    } else if (field === 'show_active' && value === 'false') {
                        this.show_active = false;
                    }
                });
            },
            watch: {
                filter_attribute(val) {
                    this.filter_value = '';
                    this.filter_custom_name = '';
                    if (!this.loaded_filters.some(el => el === val)) {
                        switch(val) {
                            case 'sending_object_id': this.searchStartLocations('');
                                break;
                            case 'getting_object_id': this.searchEndLocations('');
                                break;
                            case 'author_user_id': this.searchAuthors('');
                                break;
                            case 'resp_rp_user_id': this.searchResponsibleRPs('');
                                break;
                            case 'logist': this.searchResponsibleLogists('');
                                break;
                            case 'usage_resp_user_id': this.searchResponsibleUsers('');
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
                },
            },
            mounted() {
                //attempt to fix modal scroll
                /*$('body').on('hidden.bs.modal', function () {
                    if($('.modal.show').length > 0)
                    {
                        $('body').addClass('modal-open');
                    }
                });*/
                $(document).on('show.bs.modal', '.modal', function () {
                    var zIndex = 1040 + (10 * $('.modal:visible').length);
                    $(this).css('z-index', zIndex);
                    setTimeout(function() {
                        $('.modal-backdrop').not('.modal-stack').css('z-index', zIndex - 1).addClass('modal-stack');
                    }, 0);
                });
                $('#filter-value-tf').on('keypress', function (e) {
                    if (e.which === 13) {
                        vm.addFilter();
                    }
                });
                $(window).on('resize', this.handleResize);
                this.handleResize();

                let searchTF = $('#search-tf');

                searchTF.on('keyup', () => {
                    clearTimeout(this.typingTimer);
                    this.typingTimer = setTimeout(this.doneTyping, this.DONE_TYPING_INTERVAL);
                });

                searchTF.on('keydown', () => {
                    clearTimeout(this.typingTimer);
                });

                const ticket_id = this.$route.query.ticket_id;
                if (ticket_id) {
                    this.loading = true;
                    const ticket = this.tickets.find(el => el.id == ticket_id);
                    if (ticket === undefined) {
                        this.$message({
                            showClose: true,
                            message: 'Такой заявки на технику не существует',
                            type: 'error',
                            duration: 10000
                        });

                        this.loading = false;
                    } else if (!ticket.comments) {
                        axios.get('{{ route('building::tech_acc::our_technic_tickets.show', '') }}' + '/' + ticket_id)
                            .then(response => {
                                const ticketIndex = this.tickets.findIndex(el => el.id == ticket_id);
                                this.$set(this.tickets, ticketIndex, response.data.data.ticket);
                                this.tryShowCard(ticketIndex);
                                this.loading = false;
                            })
                            .catch(error => {
                                console.log(error);
                                this.loading = false;
                            });
                    } else {
                        const ticketIndex = this.tickets.findIndex(el => el.id == ticket_id);
                        this.tryShowCard(ticketIndex);
                        this.loading = false;
                    }
                }
                this.addHideCardEventListener();
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
                changePage(page) {
                    this.$router.replace({query: Object.assign({}, this.$route.query, {page: page})}).catch(err => {});
                    this.updateFilteredTickets();
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
                tryShowCard(ticketIndex){
                    setTimeout(() => {
                        if (cardTicket) {
                            cardTicket.update(this.tickets[ticketIndex]);
                            $('#card_ticket').modal('show');
                            $('#card_ticket').focus();
                            $('.modal').css('overflow-y', 'auto');
                        } else {
                            this.tryShowCard(ticketIndex);
                        }
                    }, 500)
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
                updateCurrentCustomName() {
                    switch (this.filter_attribute) {
                        case 'sending_object_id':
                            this.filter_custom_name = this.startLocations.find(el => el.id === this.filter_value) ? this.startLocations.find(el => el.id === this.filter_value).name : ''; break;
                        case 'getting_object_id':
                            this.filter_custom_name = this.endLocations.find(el => el.id === this.filter_value) ? this.endLocations.find(el => el.id === this.filter_value).name : ''; break;
                        case 'author_user_id':
                            this.filter_custom_name = this.authors.find(el => el.id === this.filter_value) ? this.authors.find(el => el.id === this.filter_value).name : ''; break;
                        case 'resp_rp_user_id':
                            this.filter_custom_name = this.responsibleRPs.find(el => el.id === this.filter_value) ? this.responsibleRPs.find(el => el.id === this.filter_value).name : ''; break;
                        case 'logist':
                            this.filter_custom_name = this.responsibleLogists.find(el => el.id === this.filter_value) ? this.responsibleLogists.find(el => el.id === this.filter_value).name : ''; break;
                        case 'usage_resp_user_id':
                            this.filter_custom_name = this.responsibleUsers.find(el => el.id === this.filter_value) ? this.responsibleUsers.find(el => el.id === this.filter_value).name : ''; break;
                    }
                },
                getFilterValueLabel(filter) {
                    switch(filter.field) {
                        case 'sending_object_id':
                        case 'getting_object_id':
                        case 'author_user_id':
                        case 'usage_resp_user_id':
                        case 'logist':
                        case 'resp_rp_user_id': return filter.custom_name;
                        default: return filter.value;
                    }
                },
                //TODO fill with correct status classes
                getStatusClass(status_id) {
                    switch (status_id) {
                        case 'Новая заявка':
                            return 'text-success';
                        case 'Отклонена':
                            return 'text-danger';
                        case 'Ожидает назначения':
                        case 'Удержание':
                            return 'text-warning';
                        case 'Ожидает начала использования':
                        case 'Перемещение':
                        case 'Использование':
                            return 'text-primary';
                        default:
                            return '';
                    }
                },
                changePage(page) {
                    this.$router.replace({query: Object.assign({}, this.$route.query, {page: page})}).catch(err => {});
                    this.updateFilteredTickets();
                },
                resetCurrentPage() {
                    this.$router.replace({query: Object.assign({}, this.$route.query, {page: 1})}).catch(err => {});
                    this.currentPage = 1;
                    this.updateFilteredFuels();
                },
                convertDateFormat(dateString, full) {
                    return full ? moment(dateString, 'YYYY-MM-DD HH:mm:ss').format('DD.MM.YYYY HH:mm:ss') :
                        moment(dateString, 'YYYY-MM-DD HH:mm:ss').format('DD.MM.YYYY');
                },
                addFilter() {
                    if (this.filter_value && this.filter_attribute) {

                        this.filters.push({
                            attribute: this.filter_attributes.find(el => el.value === this.filter_attribute).label,
                            field: this.filter_attribute,
                            value: this.filter_value,
                            custom_name: this.filter_custom_name,
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
                updateFilteredTickets() {
                    axios.post('{{ route('building::tech_acc::get_technic_tickets') }}', {url: vm.$route.fullPath, page: vm.currentPage})
                        .then(response => {
                            vm.tickets = response.data.ourTechnicTickets;
                            vm.totalItems = response.data.ourTechnicTicketsCount;
                        })
                        .catch(error => console.log(error));
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
                    this.searchStartLocations('');
                    this.searchEndLocations('');
                    this.searchAuthors('');
                    this.searchResponsibleRPs('');
                    this.searchResponsibleLogists('');
                    this.searchResponsibleUsers('');
                    this.resetCurrentPage();
                },
                resetCurrentPage() {
                    this.$router.replace({query: Object.assign({}, this.$route.query, {page: 1})}).catch(err => {});
                    this.currentPage = 1;
                    this.updateFilteredTickets();
                },
                handleResize() {
                    this.window_width = $(window).width();
                },
                hideTooltips() {
                    for (let ms = 50; ms <= 1050; ms += 100) {
                        setTimeout(() => {
                            $('[data-balloon-pos]').blur();
                        }, ms);
                    }
                },
                createTicket() {
                    $('#form_ticket').modal('show');
                    $('#form_ticket').focus();
                    $('.modal').css('overflow-y', 'auto');
                },
                createTicketMockup() {
                    $('#form_ticket_mockup').modal('show');
                    $('#form_ticket_mockup').focus();
                    $('.modal').css('overflow-y', 'auto');
                },
                showTicket(ticket_id) {
                    const queryObj = {};
                    if (!this.loading) {
                        const count = Object.keys(this.$route.query).filter(el => el.indexOf('ticket_id') !== -1).length;
                        if (!count) {
                            queryObj['ticket_id'] = ticket_id;
                            this.$router.replace({query: Object.assign({}, this.$route.query, queryObj)}).catch(err => {});
                        } else {
                            Object.assign(queryObj, this.$route.query);
                            queryObj['ticket_id'] = ticket_id;
                            this.$router.replace({query: Object.assign({}, queryObj)}).catch(err => {});
                        }
                        if (ticket_id) {
                            const ticket = this.tickets.find(el => el.id == ticket_id);
                            if (ticket) {
                                if (!ticket.is_loaded) {
                                    this.loading = true;
                                    axios.get('{{ route('building::tech_acc::our_technic_tickets.show', '') }}' + '/' + ticket_id)
                                        .then(response => {
                                            const ticketIndex = this.tickets.findIndex(el => el.id == ticket_id);
                                            this.$set(this.tickets, ticketIndex, response.data.data.ticket);
                                            this.tickets[ticketIndex].is_loaded = true;
                                            cardTicket.update(this.tickets[ticketIndex]);
                                            $('#card_ticket').modal('show');
                                            $('#card_ticket').focus();
                                            this.loading = false;
                                        })
                                        .catch(error => {
                                            console.log(error);
                                            this.loading = false;
                                        });
                                } else {
                                    const ticketIndex = this.tickets.findIndex(el => el.id == ticket_id);
                                    cardTicket.update(ticket);
                                    $('#card_ticket').modal('show');
                                    $('#card_ticket').focus();
                                    this.loading = false;
                                }
                            }
                        }
                        $('.modal').css('overflow-y', 'auto');
                        this.addHideCardEventListener();
                    }
                },
                promisedSearchStartLocations(query) {
                    return new Promise((resolve, reject) => {
                        if (query) {
                            axios.post('{{ route('building::mat_acc::report_card::get_objects') }}', {q: query})
                                .then(response => {
                                    this.startLocations = response.data.map(el => ({ name: el.label, id: el.code }));
                                    resolve(response);
                                })
                                .catch(error => {
                                    console.log(error);
                                    reject(error);
                                });
                        } else {
                            axios.post('{{ route('building::mat_acc::report_card::get_objects') }}')
                                .then(response => {
                                    this.startLocations = response.data.map(el => ({ name: el.label, id: el.code }));
                                    resolve(response);
                                })
                                .catch(error => {
                                    console.log(error);
                                    reject(error);
                                });
                        }
                    });
                },
                searchStartLocations(query) {
                    if (query) {
                        axios.post('{{ route('building::mat_acc::report_card::get_objects') }}', {q: query})
                            .then(response => this.startLocations = response.data.map(el => ({ name: el.label, id: el.code })))
                            .catch(error => console.log(error));
                    } else {
                        axios.post('{{ route('building::mat_acc::report_card::get_objects') }}')
                            .then(response => this.startLocations = response.data.map(el => ({ name: el.label, id: el.code })))
                            .catch(error => console.log(error));
                    }
                },
                promisedSearchEndLocations(query) {
                    return new Promise((resolve, reject) => {
                        if (query) {
                            axios.post('{{ route('building::mat_acc::report_card::get_objects') }}', {q: query})
                                .then(response => {
                                    this.endLocations = response.data.map(el => ({ name: el.label, id: el.code }));
                                    resolve(response);
                                })
                                .catch(error => {
                                    console.log(error);
                                    reject(error);
                                });
                        } else {
                            axios.post('{{ route('building::mat_acc::report_card::get_objects') }}')
                                .then(response => {
                                    this.endLocations = response.data.map(el => ({ name: el.label, id: el.code }));
                                    resolve(response);
                                })
                                .catch(error => {
                                    console.log(error);
                                    reject(error);
                                });
                        }
                    });
                },
                searchEndLocations(query) {
                    if (query) {
                        axios.post('{{ route('building::mat_acc::report_card::get_objects') }}', {q: query})
                            .then(response => this.endLocations = response.data.map(el => ({ name: el.label, id: el.code })))
                            .catch(error => console.log(error));
                    } else {
                        axios.post('{{ route('building::mat_acc::report_card::get_objects') }}')
                            .then(response => this.endLocations = response.data.map(el => ({ name: el.label, id: el.code })))
                            .catch(error => console.log(error));
                    }
                },
                promisedSearchResponsibleRPs(query) {
                    return new Promise((resolve, reject) => {
                        if (query) {
                            axios.get('{{ route('users::get_users_for_tech_tickets') }}', {params: {
                                    group_ids: [27, 19, 13, 8],
                                    q: query,
                                }})
                                .then(response => {
                                    this.responsibleRPs = response.data.map(el => ({ name: el.label, id: el.code }));
                                    resolve(response);
                                })
                                .catch(error => {
                                    console.log(error);
                                    reject(error);
                                });
                        } else {
                            axios.get('{{ route('users::get_users_for_tech_tickets') }}', { params: {
                                    group_ids: [27, 19, 13, 8],
                                }})
                                .then(response => {
                                    this.responsibleRPs = response.data.map(el => ({ name: el.label, id: el.code }));
                                    resolve(response);
                                })
                                .catch(error => {
                                    console.log(error);
                                    reject(error);
                                });
                        }
                    });
                },
                searchResponsibleRPs(query) {
                    if (query) {
                        axios.get('{{ route('users::get_users_for_tech_tickets') }}', {params: {
                                group_ids: [27, 19, 13, 8],
                                q: query,
                            }})
                            .then(response => this.responsibleRPs = response.data.map(el => ({
                                name: el.label,
                                id: el.code
                            })))
                            .catch(error => console.log(error));
                    } else {
                        axios.get('{{ route('users::get_users_for_tech_tickets') }}', { params: {
                                group_ids: [27, 19, 13, 8],
                            }})
                            .then(response => this.responsibleRPs = response.data.map(el => ({
                                name: el.label,
                                id: el.code
                            })))
                            .catch(error => console.log(error));
                    }
                },
                promisedSearchResponsibleLogists(query) {
                    return new Promise((resolve, reject) => {
                        if (query) {
                            axios.get('{{ route('users::get_users_for_tech_tickets') }}', {params: {
                                    q: query,
                                }})
                                .then(response => {
                                    this.responsibleLogists = response.data.map(el => ({ name: el.label, id: el.code }));
                                    resolve(response);
                                })
                                .catch(error => {
                                    console.log(error);
                                    reject(error);
                                });
                        } else {
                            axios.get('{{ route('users::get_users_for_tech_tickets') }}', {params: {
                                    q: query,
                                }})
                                .then(response => {
                                    this.responsibleLogists = response.data.map(el => ({ name: el.label, id: el.code }));
                                    resolve(response);
                                })
                                .catch(error => {
                                    console.log(error);
                                    reject(error);
                                });
                        }
                    });
                },
                searchResponsibleLogists(query) {
                    if (query) {
                        axios.get('{{ route('users::get_users_for_tech_tickets') }}', {params: {
                                q: query,
                            }})
                            .then(response => this.responsibleLogists = response.data.map(el => ({
                                name: el.label,
                                id: el.code
                            })))
                            .catch(error => console.log(error));
                    } else {
                        axios.get('{{ route('users::get_users_for_tech_tickets') }}')
                            .then(response => this.responsibleLogists = response.data.map(el => ({
                                name: el.label,
                                id: el.code
                            })))
                            .catch(error => console.log(error));
                    }
                },
                promisedSearchResponsibleUsers(query) {
                    return new Promise((resolve, reject) => {
                        if (query) {
                            axios.get('{{ route('users::get_users_for_tech_tickets') }}', {params: {
                                    q: query,
                                }})
                                .then(response => {
                                    this.responsibleUsers = response.data.map(el => ({ name: el.label, id: el.code }));
                                    resolve(response);
                                })
                                .catch(error => {
                                    console.log(error);
                                    reject(error);
                                });
                        } else {
                            axios.get('{{ route('users::get_users_for_tech_tickets') }}', {params: {
                                    q: query,
                                }})
                                .then(response => {
                                    this.responsibleUsers = response.data.map(el => ({ name: el.label, id: el.code }));
                                    resolve(response);
                                })
                                .catch(error => {
                                    console.log(error);
                                    reject(error);
                                });
                        }
                    });
                },
                searchResponsibleUsers(query) {
                    if (query) {
                        axios.get('{{ route('users::get_users_for_tech_tickets') }}', {params: {
                                q: query,
                            }})
                            .then(response => this.responsibleUsers = response.data.map(el => ({
                                name: el.label,
                                id: el.code
                            })))
                            .catch(error => console.log(error));
                    } else {
                        axios.get('{{ route('users::get_users_for_tech_tickets') }}')
                            .then(response => this.responsibleUsers = response.data.map(el => ({
                                name: el.label,
                                id: el.code
                            })))
                            .catch(error => console.log(error));
                    }
                },
                promisedSearchAuthors(query) {
                    return new Promise((resolve, reject) => {
                        if (query) {
                            axios.get('{{ route('users::get_users_for_tech_tickets') }}', {params: {
                                    q: query,
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
                            axios.get('{{ route('users::get_users_for_tech_tickets') }}', {params: {
                                    q: query,
                                }})
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
                        axios.get('{{ route('users::get_users_for_tech_tickets') }}', {params: {
                                q: query,
                            }})
                            .then(response => this.authors = response.data.map(el => ({
                                name: el.label,
                                id: el.code
                            })))
                            .catch(error => console.log(error));
                    } else {
                        axios.get('{{ route('users::get_users_for_tech_tickets') }}')
                            .then(response => this.authors = response.data.map(el => ({
                                name: el.label,
                                id: el.code
                            })))
                            .catch(error => console.log(error));
                    }
                },
                removeTicket(id) {
                    if (!this.loading) {
                        swal({
                            title: 'Вы уверены?',
                            text: "Заявка будет удалена!",
                            type: 'warning',
                            showCancelButton: true,
                            cancelButtonText: 'Назад',
                            confirmButtonText: 'Удалить'
                        }).then((result) => {
                            if (result.value) {
                                axios.delete('{{ route('building::tech_acc::our_technic_tickets.destroy', [
                                ''
                        ]) }}' + '/' + id)
                                    .then(() => {
                                        this.tickets.splice(this.tickets.findIndex(el => el.id === id), 1);
                                        formTicket.search_techs('');
                                        this.hideTooltips();
                                    })
                                    //TODO add actual error handler
                                    .catch(error => console.log(error));
                            } else {
                                this.hideTooltips();
                            }
                        });
                    }
                },
                updateTickets(ticket) {
                    this.tickets.unshift(ticket);
                },
                addHideCardEventListener() {
                    $('#card_ticket').on('hide.bs.modal', () => this.resetPath());
                },
                resetPath() {
                    const queryObj = {};
                    Object.assign(queryObj, this.$route.query);
                    delete queryObj['ticket_id'];
                    this.$router.replace({query: Object.assign({}, queryObj)}).catch(err => {});
                    this.addHideCardEventListener();
                },
                canRemoveTicket(ticket) {
                    auth_id = {{ auth()->id()}};
                    auth_group = {{ auth()->user()->group_id}};
                    project_managers = {!! json_encode($data['project_managers'])  !!};
                    if (project_managers.includes(auth_group) && [1, 2, 5].includes(ticket.status)) {
                        return true;
                    }
                    return ((ticket.users.find(user => user.ticket_responsible.type === 5).id === auth_id && ticket.status === 1) || auth_id === 1) ;
                }
            }
        });

        function removeDuplicates(myArr, prop) {
            return myArr.filter((obj, pos, arr) => {
                return arr.map(mapObj => mapObj[prop]).indexOf(obj[prop]) === pos;
            });
        }
    </script>
@endsection
