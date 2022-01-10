@extends('layouts.app')

@section('title', 'Выплаты и удержания')

@section('url', route('human_resources.payment.index'))

@section('css_top')
    <link rel="stylesheet" href="{{ asset('css/balloon.css') }}">
    <style media="screen">
        @media (max-width: 769px) {
            .responsive-button {
                width: 100% !important;
            }
        }
        .el-radio-button__inner, .el-radio-group {
            width: 100%;
        }
        .el-radio-button {
            width: 50%;
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
                    <li class="breadcrumb-item active" aria-current="page">Выплаты и удержания</li>
                </ol>
            </div>
            <div class="card col-xl-10 mr-auto ml-auto pd-0-min">
                <div class="card-body card-body-tech">
                    <h4 class="h4-tech fw-500 m-0" style="margin-top:0"><span v-pre>Выплаты и удержания</span></h4>
                    <a class="tech-link modal-link d-block ml-1">
                        Найдено @{{ isFirstOption ? 'выплат' : 'удержаний' }}: @{{ filteredData.length }}
                    </a>
                    <div class="fixed-table-toolbar toolbar-for-btn" style="padding-left:0">
                        <div class="row flex-md-row-reverse">
                            <div :class="{'col-xl-4': window_width >= 1600, 'col-md-6 text-right': true}">
                                <el-radio-group v-model="displayType">
                                    <el-radio-button label="Выплаты"></el-radio-button>
                                    <el-radio-button label="Удержания"></el-radio-button>
                                </el-radio-group>
                            </div>
                            <div :class="{'d-none': window_width < 1600, 'col-md-2': true}"></div>
                            <div class="col-md-6">
                                <div class="d-md-flex">
                                    <el-input placeholder="Поиск" v-model="search_tf" clearable
                                              class="responsive-button mt-10__mobile"
                                              prefix-icon="el-icon-search" id="search-tf"
                                              style="width: 270px"
                                    ></el-input>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="table-responsive">
                        <table class="table table-hover mobile-table table-fix-column">
                            <thead>
                            <tr>
                                <th><span class="text-truncate d-inline-block">Наименование</span></th>
                                <th><span class="text-truncate d-inline-block">Сокращенное наименование</span></th>
                                <th></th>
                            </tr>
                            </thead>
                            <tbody>
                            <tr v-if="filteredData.length === 0">
                                <td>
                                    Нет данных
                                </td>
                                <td></td>
                                <td></td>
                            </tr>
                            <tr v-for="payment in filteredData">
                                <td data-label="Наименование">
                                    @{{ payment.name }}
                                </td>
                                <td data-label="Сокращенное наименование">
                                    @{{ payment.short_name }}
                                </td>
                                <td class="text-right actions">
                                    @can('human_resources_pay_and_hold_edit')
                                        <a data-balloon-pos="up"
                                           :href="'{{ route('human_resources.payment.edit', 'payment_id') }}'.split('payment_id').join(payment.id)"
                                           aria-label="Изменить сокращенное наименование"
                                           class="btn btn-link btn-xs btn-space btn-warning mn-0">
                                            <i class="fa fa-pencil"></i>
                                        </a>
                                    @endcan
                                </td>
                            </tr>
                            </tbody>
                        </table>
                    </div>
                    {{--<div class="d-flex justify-content-end mt-2">
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
                    </div>--}}
                </div>
            </div>
        </div>
    </div>
@endsection

@section('js_footer')
    <script type="text/javascript">
        vm = new Vue({
            el: '#base',
            /* router: new VueRouter({
                mode: 'history',
                routes: [],
            }), */
            data: {
                payments: {!! json_encode($data['payments']) !!},
                penalties: {!! json_encode($data['holds']) !!},
                search_tf: '',
                displayType: 'Выплаты',

                window_width: 10000,
            },
            watch: {
                displayType() {
                    this.search_tf = '';
                },
            },
            /* created() {
                if (this.$route.query.page && !Array.isArray(this.$route.query.page)) {
                    this.currentPage = +this.$route.query.page;
                }
                Object.entries(this.$route.query).forEach(entry => {
                    let field = entry[0].split('[')[0];
                    let value = Array.isArray(entry[1]) ? entry[1][0] : entry[1];
                    if (field === 'name') {
                        this.search_tf = value;
                    }
                });
            }, */
            mounted() {
                $(window).on('resize', this.handleResize);
                this.handleResize();
            },
            computed: {
                isFirstOption() {
                    return this.displayType === 'Выплаты';
                },
                filteredData() {
                    const selectedData = this.isFirstOption ? this.payments : this.penalties;
                    return selectedData.filter(el => {
                        //Search textfield filter
                        const search_tf_pattern = new RegExp(_.escapeRegExp(this.search_tf), 'i');
                        const search_tf_filter = search_tf_pattern.test(el.name) || search_tf_pattern.test(el.short_name);
                        return search_tf_filter;
                    });
                },
                /*totalItems() {
                    return this.isFirstOption
                        ? this.payments.length : this.penalties.length;
                },
                pagerCount() {
                    return this.window_width > 1000 ? 7 : 5;
                },
                smallPager() {
                    return this.window_width < 1000;
                },
                pagerBackground() {
                    return this.window_width > 300;
                },*/
            },
            methods: {
                handleResize() {
                    this.window_width = $(window).width();
                },
            {{-- doneTyping() {
                    const queryObj = {};
                    if (this.search_tf) {
                        const count = Object.keys(this.$route.query).filter(el => el.indexOf('search') !== -1).length;
                        if (!count) {
                            queryObj['name'] = this.search_tf;
                            this.$router.replace({query: Object.assign({}, this.$route.query, queryObj)}).catch(err => {
                            });
                        } else {
                            Object.assign(queryObj, this.$route.query);
                            queryObj['name'] = this.search_tf;
                            this.$router.replace({query: Object.assign({}, queryObj)}).catch(err => {
                            });
                        }
                    } else {
                        Object.assign(queryObj, this.$route.query);
                        delete queryObj['name'];
                        this.$router.replace({query: Object.assign({}, queryObj)}).catch(err => {
                        });
                    }
                    this.resetCurrentPage();
                },
                updateFilteredPayments() {
                    axios.post('{{ route('human_resources.report_group.paginated') }}', {url: vm.$route.fullPath, page: vm.currentPage})
                        .then(response => {
                            vm.report_groups = response.data.data.report_groups;
                            vm.totalItems = response.data.data.report_groups_count;
                        })
                        .catch(error => console.log(error));
                },
                changePage(page) {
                    this.$router.replace({query: Object.assign({}, this.$route.query, {page: page})}).catch(err => {});
                    this.updateFilteredPayments();
                },
                resetCurrentPage() {
                    this.$router.replace({query: Object.assign({}, this.$route.query, {page: 1})}).catch(err => {});
                    this.currentPage = 1;
                    this.updateFilteredPayments();
                },
                hideTooltips() {
                    for (let ms = 50; ms <= 1050; ms += 100) {
                        setTimeout(() => {
                            $('[data-balloon-pos]').blur();
                        }, ms);
                    }
                },
                --}}
            }
        });
    </script>
@endsection
