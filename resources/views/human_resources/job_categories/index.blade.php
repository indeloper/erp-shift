@extends('layouts.app')

@section('title', 'Должностные категории')

@section('url', route('human_resources.job_category.index'))

@section('css_top')
    <link rel="stylesheet" href="{{ asset('css/balloon.css') }}">
    <style media="screen">
        @media (max-width: 769px) {
            .responsive-button {
                width: 100% !important;
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
                    <li class="breadcrumb-item active" aria-current="page">Должностные категории</li>
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
                        <div class="col-md-4">
                            <label>Артибут</label>
                            <el-select v-model="filter_attribute" placeholder="Выберите атрибут">
                                <el-option v-for="attr in filter_attributes_filtered" :label="attr.label"
                                           :value="attr.value"
                                ></el-option>
                            </el-select>
                        </div>
                        <div class="col-md-4 mt-10__mobile">
                            <label for="count">Значение</label>
                            <el-select v-if="filter_attribute === 'report_group_id'"
                                       v-model="filter_value"
                                       clearable filterable
                                       :remote-method="searchReportGroups"
                                       @clear="searchReportGroups('')"
                                       @change="updateCurrentCustomName"
                                       remote
                                       placeholder="Поиск отчётной группы"
                            >
                                <el-option
                                    v-for="item in report_groups"
                                    :key="item.id"
                                    :label="item.name"
                                    :value="item.id">
                                </el-option>
                            </el-select>
                            <el-input v-else placeholder="Введите значение" v-model="filter_value" id="filter-value-tf" clearable></el-input>
                        </div>
                        <div class="col-md-2 text-center--mobile" style="margin:29px 10px 20px 0">
                            <button type="button" class="btn btn-primary btn-outline responsive-button" @click="addFilter">
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
                    <h4 class="h4-tech fw-500 m-0" style="margin-top:0"><span v-pre>Должностные категории</span></h4>
                    <a class="tech-link modal-link d-block ml-1">
                        Найдено категорий: @{{ totalItems }}
                    </a>
                    <div class="fixed-table-toolbar toolbar-for-btn" style="padding-left:0">
                        <div class="row">
                            <div class="col-md-5">
                                <el-input placeholder="Поиск по наименованию" v-model="search_tf" clearable
                                          class="responsive-button"
                                          prefix-icon="el-icon-search" id="search-tf" @clear="doneTyping"
                                          style="width: 270px"
                                ></el-input>
                            </div>
                            @can('human_resources_job_category_create')
                                <div class="col-md-7 text-right mt-10__mobile">
                                    <a href="{{ route('human_resources.job_category.create') }}" class="btn btn-sm btn-primary btn-round responsive-button">
                                        Создать должностную категорию
                                    </a>
                                </div>
                            @endcan
                        </div>
                    </div>
                    <div class="table-responsive">
                        <table class="table table-hover mobile-table table-fix-column">
                            <thead>
                            <tr>
                                <th><span class="text-truncate d-inline-block">Наименование</span></th>
                                <th><span class="text-truncate d-inline-block">Отчётная группа</span></th>
                                <th><span class="text-truncate d-inline-block">Численность, чел.</span></th>
                                <th></th>
                            </tr>
                            </thead>
                            <tbody>
                            <tr v-if="job_categories.length === 0">
                                <td>
                                    Нет данных
                                </td>
                                <td></td>
                                <td></td>
                                <td></td>
                            </tr>
                            <tr v-for="job_category in job_categories">
                                <td data-label="Наименование">
                                    @{{ job_category.name }}
                                </td>
                                <td data-label="Отчётная группа">
                                    @{{ job_category.report_group ? job_category.report_group.name : 'Не указана' }}
                                </td>
                                <td data-label="Численность, чел.">
                                    @{{ job_category.users_count }}
                                </td>
                                <td class="text-right actions">
                                    <a data-balloon-pos="up" :href="'{{ route('human_resources.job_category.show', 'job_category_id') }}'.split('job_category_id').join(job_category.id)"
                                       aria-label="Просмотр" class="btn btn-link btn-xs btn-space actions btn-primary mn-0">
                                        <i class="fa fa-external-link-alt"></i>
                                    </a>
                                    @can('human_resources_job_categories_update')
                                        <a data-balloon-pos="up" :href="'{{ route('human_resources.job_category.edit', 'job_category_id') }}'.split('job_category_id').join(job_category.id)"
                                           aria-label="Редактировать" class="btn btn-link btn-xs btn-space btn-warning mn-0">
                                            <i class="fa fa-pencil"></i>
                                        </a>
                                    @endcan
                                    @can('human_resources_job_categories_destroy')
                                        <button data-balloon-pos="up" aria-label="Удалить" class="btn btn-link btn-xs btn-space btn-danger mn-0"
                                                @click="removeJobCategory(job_category.id)">
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
                DONE_TYPING_INTERVAL: 1000,
                PAGE_SIZE: 15,
                SINGLE_USE_FILTERS: ['name'],
                totalItems: {!! json_encode($data['job_categories_count']) !!},
                currentPage: 1,

                job_categories: JSON.parse('{!! addslashes(json_encode($data['job_categories'])) !!}'),

                search_tf: '',
                filter_attributes: [
                    // {label: 'Наименование', value: 'name'},
                    {label: 'Отчётная группа', value: 'report_group_id'},
                ],
                filter_attribute: '',
                filter_value: '',
                filter_custom_name: '',
                filters: [],

                report_groups: [],

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
                        if (field === 'report_group_id') {
                            this.promisedSearchReportGroups('')//entry[1]
                                .then(() => {
                                    const report_group = this.report_groups.find(el => {
                                        return el.id == value;
                                    });
                                    customName = report_group ? report_group.name : '';
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
                    } else if (field === 'name') {
                        this.search_tf = value;
                    }
                });
            },
            mounted() {
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
                this.searchReportGroups('');
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
                            return false;
                        } else {
                            return true;
                        }
                    });
                },
            },
            methods: {
                doneTyping() {
                    const queryObj = {};
                    if (this.search_tf) {
                        const count = Object.keys(this.$route.query).filter(el => el.indexOf('search') !== -1).length;
                        if (!count) {
                            queryObj['name'] = this.search_tf;
                            this.$router.replace({query: Object.assign({}, this.$route.query, queryObj)}).catch(err => {});
                        } else {
                            Object.assign(queryObj, this.$route.query);
                            queryObj['name'] = this.search_tf;
                            this.$router.replace({query: Object.assign({}, queryObj)}).catch(err => {});
                        }
                    } else {
                        Object.assign(queryObj, this.$route.query);
                        delete queryObj['name'];
                        this.$router.replace({query: Object.assign({}, queryObj)}).catch(err => {});
                    }
                    this.resetCurrentPage();
                },
                updateFilteredJobCategories() {
                    axios.post('{{ route('human_resources.job_category.paginated') }}', {url: vm.$route.fullPath, page: vm.currentPage})
                        .then(response => {
                            vm.job_categories = response.data.data.job_categories;
                            vm.totalItems = response.data.data.job_categories_count;
                        })
                        .catch(error => console.log(error));
                },
                changePage(page) {
                    this.$router.replace({query: Object.assign({}, this.$route.query, {page: page})}).catch(err => {});
                    this.updateFilteredJobCategories();
                },
                resetCurrentPage() {
                    this.$router.replace({query: Object.assign({}, this.$route.query, {page: 1})}).catch(err => {});
                    this.currentPage = 1;
                    this.updateFilteredJobCategories();
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
                getFilterValueLabel(filter) {
                    switch(filter.field) {
                        case 'report_group_id': return filter.custom_name;
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
                    this.searchReportGroups('');
                    this.resetCurrentPage();
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
                searchReportGroups(query) {
                    if (query) {
                        axios.get('{{ route('human_resources.report_groups.get') }}', {params: {q: query,}})
                            .then(response => this.report_groups = response.data.map(el => ({
                                name: el.name,
                                id: el.id
                            })))
                            .catch(error => console.log(error));
                    } else {
                        axios.get('{{ route('human_resources.report_groups.get') }}')
                            .then(response => this.report_groups = response.data.map(el => ({
                                name: el.name,
                                id: el.id
                            })))
                            .catch(error => console.log(error));
                    }
                },
                promisedSearchReportGroups(query) {
                    return new Promise((resolve, reject) => {
                        if (query) {
                            axios.get('{{ route('human_resources.report_groups.get') }}', {params: {
                                    q: query,
                                }})
                                .then(response => {
                                    this.report_groups = response.data.map(el => ({
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
                            axios.get('{{ route('human_resources.report_groups.get') }}')
                                .then(response => {
                                    this.report_groups = response.data.map(el => ({
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
                updateCurrentCustomName() {
                    switch (this.filter_attribute) {
                        case 'report_group_id':
                            const report_group = this.report_groups.find(el => {
                                return el.id === this.filter_value;
                            });
                            this.filter_custom_name = report_group ? report_group.name : '';
                            break;
                    }
                },
                @can('human_resources_job_categories_destroy')
                removeJobCategory(id) {
                    swal({
                        title: 'Вы уверены?',
                        text: "Должностная категория будет удалена!",
                        type: 'warning',
                        showCancelButton: true,
                        cancelButtonText: 'Назад',
                        confirmButtonText: 'Удалить'
                    }).then((result) => {
                        if (result.value) {
                            axios.delete('{{ route('human_resources.job_category.destroy', ['']) }}' + '/' + id)
                                .then(() => {
                                    this.job_categories.splice(this.job_categories.findIndex(el => el.id === id), 1);
                                    this.hideTooltips();
                                })
                                //TODO add actual error handler
                                .catch(error => console.log(error));
                        } else {
                            this.hideTooltips();
                        }
                    });
                },
                @endcan
            }
        });
    </script>
@endsection
