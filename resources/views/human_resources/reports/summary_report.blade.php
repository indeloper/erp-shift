@extends('layouts.app')

@section('title', 'Табели')

@section('url', route('human_resources.report.detailed_report'))

@section('css_top')
    <link rel="stylesheet" href="{{ asset('css/balloon.css') }}">
    <style media="screen">
        @media (max-width: 769px) {
            .responsive-button {
                width: 100% !important;
            }
        }

        .full-section-row, .full-section-row .weekendColumn {
            background-color: #eaeaea !important;
        }

        .full-section-row-cell {
            text-align: right !important;
            font-weight: 500 !important;
        }

        .weekendColumn {
            background-color: #ecf4ff !important;
        }

        @media (max-width: 769px) {
            .mobile-button-margin {
                margin: 20px 0 0 0 !important;
            }
        }

        [data-balloon],
        [data-balloon]:before,
        [data-balloon]:after {
            z-index: 9999;
        }

        .header-text-color {
            color: #444444;
        }

        .el-table.ignore-css td, .el-table.ignore-css th {
            padding: 6px 0;
        }

        #report-sidebar {
            min-width: 25vw;
            max-width: 25vw;
            min-height: 100vh;
            margin-left: 20px;
            margin-right: 0;
            margin-right: calc(-25vw - 20px);
            opacity: 0;
        }

        .animated-sidebar {
            transition: all 0.3s;
        }

        @media (max-width: 769px) {
            #report-sidebar {
                min-width: 100%;
                max-width: 100%;
                min-height: auto;
                margin-left: 0;
            }
        }

        #report-sidebar.active {
            margin-right: 0;
            opacity: 1;
        }

        .summary-table .el-table .cell {
            word-break: normal !important;
        }

        [v-cloak] {display: flex}
        [v-cloak] > * { display:none }
        [v-cloak]::before { content: "Загрузка..." }
    </style>
    @include('human_resources.reports.modules.common.spinner_css')
@endsection

@section('content')
    {{--  PROTO: human_resources.job_category.index  --}}
    <div class="row" id="base" v-cloak>
        <div class="col-md-12 mobile-card">
            <div class="nav-container" style="margin:0 0 10px 15px">
                <ul class="nav nav-icons" role="tablist">
                    <li class="nav-item">
                        <a class="nav-link link-line" href="{{ route('human_resources.report.detailed_report') }}">
                            Детальный месячный табель сотрудника
                        </a>
                    </li>
                    <li class="nav-item active">
                        <a class="nav-link link-line active-link-line" href="#">
                            Сводный табель
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link link-line" href="{{ route('human_resources.report.daily_report') }}">
                            Суточный табель
                        </a>
                    </li>
                </ul>
            </div>
            {{--<div aria-label="breadcrumb" role="navigation">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item active" aria-current="page">Табели</li>
                </ol>
            </div>--}}
            <div class="card">
                <div class="card-body-tech">
                    <div class="row">
                        <div class="col-md-12">
                            <h6 class="mb-20" style="margin-top:5px">Фильтры</h6>
                        </div>
                    </div>
                    <validation-observer ref="observer" :key="observer_key">
                        <div class="row">
                            <div class="col-md-5">
                                <label for="">Сотрудник{{--<span class="star">*</span>--}}</label>
                                <validation-provider rules="{{--required--}}" vid="user-select"
                                                     ref="user-select" v-slot="v">
                                    <el-select v-model="user"
                                               :class="v.classes"
                                               clearable filterable
                                               id="user-select"
                                               :remote-method="searchUsers"
                                               @clear="searchUsers('')"
                                               ref="user-select"
                                               remote
                                               placeholder="Поиск сотрудника"
                                    >
                                        <el-option
                                            v-for="item in users"
                                            :key="item.id"
                                            :label="item.name"
                                            :value="item.id"
                                        ></el-option>
                                    </el-select>
                                    <div class="error-message">@{{ v.errors[0] }}</div>
                                </validation-provider>
                            </div>
                            <div class="col-md-5">
                                <label for="">
                                    Проект{{--<span class="star">*</span>--}}
                                </label>
                                <validation-provider rules="{{--required--}}" vid="location-id-select"
                                                     ref="location-id-select" v-slot="v">
                                    <el-select v-model="location"
                                               :class="v.classes"
                                               clearable filterable
                                               id="location-id-select"
                                               :remote-method="searchLocations"
                                               @clear="searchLocations('')"
                                               remote
                                               placeholder="Поиск проекта"
                                    >
                                        <el-option
                                            v-for="item in locations"
                                            :key="item.id"
                                            :label="item.name"
                                            :value="item.id">
                                        </el-option>
                                    </el-select>
                                    <div class="error-message">@{{ v.errors[0] }}</div>
                                </validation-provider>
                            </div>
                            <div class="col-md-2">
                                <button type="button"
                                        style="margin:27px 0 0 0"
                                        :disabled="loading"
                                        class="btn btn-primary btn-outline d-none d-md-block w-100"
                                        @click="show"
                                >
                                    Показать
                                </button>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-4">
                                <el-checkbox v-model="exactPeriod" label="Точный период" border
                                             class="d-block w-100 text-center"
                                             style="margin:27px 0 20px 0"></el-checkbox>
                            </div>
                            <template v-if="!exactPeriod">
                                <div class="col-md-3" key="period-month">
                                    <label>Месяц<span class="star">*</span></label>
                                    <validation-provider rules="required" v-slot="v"
                                                         vid="month-input"
                                                         ref="month-input"
                                    >
                                        <el-date-picker
                                            style="cursor:pointer"
                                            :class="v.classes"
                                            v-model="month"
                                            format="MM.yyyy"
                                            value-format="yyyy-MM"
                                            id="month-input"
                                            type="month"
                                            placeholder="Укажите месяц"
                                        ></el-date-picker>
                                        <div class="error-message">@{{ v.errors[0] }}</div>
                                    </validation-provider>
                                </div>
                                <div class="col-md-3"></div>
                            </template>
                            <template v-else>
                                <div class="col-md-3 mt-10__mobile" key="period-date-from">
                                    <label for="">Дата&nbsp;начала&nbsp;периода<span class="star">*</span></label>
                                    <validation-provider rules="required" v-slot="v"
                                                         vid="date-period-from-input"
                                                         ref="date-period-from-input"
                                    >
                                        <el-date-picker
                                            style="cursor:pointer"
                                            :class="v.classes"
                                            v-model="dateFrom"
                                            format="dd.MM.yyyy"
                                            id="date-period-from-input"
                                            value-format="yyyy-MM-dd"
                                            type="date"
                                            placeholder="Укажите дату начала отчётного периода"
                                            :picker-options="dateFromPickerOptions"
                                        ></el-date-picker>
                                        <div class="error-message">@{{ v.errors[0] }}</div>
                                    </validation-provider>
                                </div>
                                <div class="col-md-3 mt-10__mobile" key="period-date-to">
                                    <label for="">Дата&nbsp;окончания&nbsp;периода<span class="star">*</span></label>
                                    <validation-provider rules="required" v-slot="v"
                                                         vid="date-period-to-input"
                                                         ref="date-period-to-input"
                                    >
                                        <el-date-picker
                                            style="cursor:pointer"
                                            :class="v.classes"
                                            v-model="dateTo"
                                            format="dd.MM.yyyy"
                                            id="date-period-to-input"
                                            value-format="yyyy-MM-dd"
                                            type="date"
                                            placeholder="Укажите дату окончания отчётного периода"
                                            :picker-options="dateToPickerOptions"
                                        ></el-date-picker>
                                        <div class="error-message">@{{ v.errors[0] }}</div>
                                    </validation-provider>
                                </div>
                            </template>
                            <div class="col-md-2" v-if="tableData.length > 0">
                                <button type="button"
                                        style="margin:27px 0 0 0"
                                        :disabled="loading"
                                        class="btn btn-primary btn-outline d-block w-100"
                                        @click="getXLS"
                                >
                                    Отчёт (xls) <i class="fas fa-file-download"></i>
                                </button>
                                <a id="xls-link" :href="'{{ route('human_resources.work_time_report') }}' + '?' + Object.entries($route.query).map(entry => `${entry[0]}=${entry[1]}`).join(`&`)"></a>
                            </div>
                        </div>
                        <div class="row d-md-none">
                            <div class="col">
                                <button type="button"
                                        :disabled="loading"
                                        class="btn btn-primary btn-outline d-block mobile-button-margin w-100"
                                        @click="show"
                                >
                                    Показать
                                </button>
                            </div>
                        </div>
                    </validation-observer>
                </div>
            </div>
            <div class="card" v-if="loading">
                <div class="card-body card-body-tech">
                    <div class="row justify-content-center align-content-center">
                        <div class="col-auto">
                            @include('human_resources.reports.modules.common.spinner_html')
                        </div>
                    </div>
                </div>
            </div>
            <div class="card" v-else-if="!displayTable">
                <div class="card-body card-body-tech">
                    Укажите параметры фильтрации
                </div>
            </div>
            <div class="card" v-else-if="tableData.length === 0">
                <div class="card-body card-body-tech">
                    Нет данных
                </div>
            </div>
            <div v-else class="card summary-table">
                    <div class="card-body card-body-tech">
                        <div class="row">
                            <div class="font-weight-bold text-md-right col-12 col-md order-md-1">Сводный табель (@{{ reportPeriodString }})</div>
                            <h4 v-if="nameString || reportLocationName" class="h4-tech fw-500 m-0 col-12 col-md-auto order-md-0" style="margin-top:0">
                                <span>@{{ shortReportLocationName ? shortReportLocationName : nameString }}</span>
                            </h4>
                        </div>
                        <div v-if="reportLocationName && nameString">
                            @{{ nameString }}
                        </div>
                        <div v-if="userInfoString">
                            @{{ userInfoString }}
                        </div>
                        <el-table border
                                  :data="tableData"
                                  :cell-class-name="cellClassMethod"
                                  :header-cell-class-name="headerCellClassMethod"
                                  :header-cell-style="headerCellStyleMethod"
                                  :max-height="max_table_height"
                                  class="ignore-css mt-2"
                        >
                            <el-table-column
                                type="index"
                                :fixed="window_width > 769"
                            ></el-table-column>
                            <el-table-column
                                :fixed="window_width > 769"
                                width="200"
                                label="ФИО"
                                prop="full_name"
                            ></el-table-column>
                            <el-table-column
                                v-for="col in summaryColumns"
                                :label="col[1]"
                                min-width="120"
                            >
                                <template slot-scope="scope">
                                    @{{ scope.row.timecard.hasOwnProperty(col[0]) ? scope.row.timecard[col[0]].sum : '' }}
                                </template>
                            </el-table-column>
                        </el-table>
                    </div>
                </div>
        </div>
    </div>
@endsection

@section('js_footer')
    <script type="text/javascript">
        Vue.component('validation-provider', VeeValidate.ValidationProvider);
        Vue.component('validation-observer', VeeValidate.ValidationObserver);

        vm = new Vue({
            el: '#base',
            router: new VueRouter({
                mode: 'history',
                routes: [],
            }),
            data: {
                displayTable: true, // false
                observer_key: 1,
                exactPeriod: false,
                user: '',
                month: '',
                location: '',
                dateFrom: '',
                dateTo: '',
                locations: [],
                users: [],

                reportLocationName: '',

                dateFromPickerOptions: {
                    firstDayOfWeek: 1,
                    disabledDate: date =>
                        vm.dateTo ? date > moment(vm.dateTo, "YYYY-MM-DD") : false,
                },
                dateToPickerOptions: {
                    firstDayOfWeek: 1,
                    disabledDate: date =>
                        vm.dateFrom ? date < moment(vm.dateFrom, "YYYY-MM-DD") : false,
                },

                tableData: [],
                {{-- tableData1: {!! json_encode($data) !!}, --}}

                dealsColors: {
                    'Погружение вибро': '#fb7981',
                    'Погружение вдвоём вибро': '#fdb2b6',
                    'Извлечение вибро': '#fdd9db',
                    'Погружение статика': '#4e9eff',
                    'Извлечение статика': '#99c8fe',
                },

                summaryColumns: [
                    [0, 'Сумма часов'],
                    [1, 'Обычный час'],
                    [2, 'Переработки'],
                    [3, 'Монтаж крепления'],
                    [4, 'Монтаж крепления (переработка)'],
                    [5, 'Демонтаж крепления'],
                    [6, 'Демонтаж крепления (переработка)'],
                    [7, 'Простой'],
                    [8, 'Погружение вибро'],
                    [9, 'Погружение вдвоём вибро'],
                    [10, 'Извлечение вибро'],
                    [11, 'Погружение статика'],
                    [12, 'Извлечение статика']
                ],

                loading: false,
                window_width: 10000,
                window_height: 10000,
            },
            computed: {
                max_table_height() {
                    return this.window_width <= 768 ? Math.ceil(this.window_height * 0.75) : 10000;
                },
                reportPeriodString() {
                    let date = this.$route.query.date;
                    if (!date) {
                        return moment().locale('ru').format('MMMM YYYY');
                    }
                    if (date.indexOf('|') === -1) {
                        return moment(date).locale('ru').format('MMMM YYYY');
                    } else {
                        let dateFrom = date.split('|')[0];
                        let dateTo = date.split('|')[1];
                        if (moment(dateFrom).isSame(dateTo)) {
                            return `${moment(dateFrom).locale('ru').format('D MMMM YYYY')}`;
                        }
                        return `${moment(dateFrom).locale('ru').format('D MMMM YYYY')} - ${moment(dateTo).locale('ru').format('D MMMM YYYY')}`;
                    }
                },
                nameString() {
                    let queryUser = this.$route.query.user_id;
                    if (queryUser) {
                        let userIndex = this.tableData.map(el => String(el.id)).indexOf(queryUser);
                        return this.tableData[userIndex] ? this.tableData[userIndex].long_full_name : '';
                    }
                    return '';
                },
                userInfoString() {
                    let queryUser = this.$route.query.user_id;
                    if (queryUser) {
                        let userIndex = this.tableData.map(el => String(el.id)).indexOf(queryUser);
                        return this.tableData[userIndex] ? this.tableData[userIndex].user_info : '';
                    }
                    return '';
                },
                shortReportLocationName() {
                    return this.reportLocationName.split(' - ')[0];
                }
            },
            created() {
                const keys = Object.keys(this.$route.query);
                const entries = Object.entries(this.$route.query);
                entries.forEach(entry => {
                    let field = entry[0].split('[')[0];
                    let value = Array.isArray(entry[1]) ? entry[1][0] : entry[1];
                    if (field === 'user_id') {
                        this.promisedSearchUsers(value)
                            .then(response => {
                                this.users = response.data.results.map(el => { el.name = el.text; el.id = String(el.id); return el; });
                                this.user = value;
                            })
                            .catch(error => console.log(error));
                    } else if (field === 'project_id') {
                        this.promisedSearchLocationsById(value)
                            .then(response => {
                                this.locations = response.data.map(el => ({ name: el.label, id: el.code }));
                                this.reportLocationName = this.locations[0].name;
                                this.location = value;
                            })
                            .catch(error => console.log(error));
                    } else if (field === 'date') {
                        if (value.indexOf('|') === -1) {
                            this.month = value;
                        } else {
                            this.exactPeriod = true;
                            this.dateFrom = value.split('|')[0];
                            this.dateTo = value.split('|')[0];
                        }
                    }
                });
                if (keys.indexOf('user_id') === -1) {
                    this.searchUsers('');
                }
                if (keys.indexOf('project_id') === -1) {
                    this.searchLocations('');
                }
                if (keys.indexOf('date') === -1) {
                    this.month = moment().format('YYYY-MM');
                }
            },
            mounted() {
                $(window).on('resize', this.handleResize);
                this.show();
                this.handleResize();
            },
            methods: {
                show() {
                    this.$refs.observer.validate().then(success => {
                        if (!success) {
                            const error_field_vid = Object.keys(this.$refs.observer.errors)
                                .find(el => Array.isArray(this.$refs[el])
                                    ? this.$refs[el][0].errors.length > 0 : this.$refs[el].errors.length > 0);
                            $('#' + error_field_vid).focus();
                            return;
                        }

                        const queryObj = {};
                        if (this.user) {
                            queryObj.user_id = this.user;
                        }
                        if (this.location) {
                            queryObj.project_id = this.location;
                        }
                        if (this.exactPeriod && this.dateFrom && this.dateTo) {
                            queryObj.date = `${this.dateFrom}|${this.dateTo}`;
                        } else if (this.month) {
                            queryObj.date = this.month;
                        }
                        this.$router.replace({query: queryObj}).catch(err => {});

                        this.loading = true;
                        axios.post('{{ route('human_resources.timecard.get_summary_report') }}', queryObj)
                            .then(response => {
                                this.tableData = response.data.data.users;
                                if (queryObj.project_id) {
                                    this.reportLocationName = this.locations[this.locations.map(el => el.id).indexOf(this.location)].name;
                                } else {
                                    this.reportLocationName = '';
                                }
                                /*this.summaryColumns = response.data.data.users
                                    .map(el => Object.entries(el.timecard));
                                this.summaryColumns = [].concat(...this.summaryColumns)
                                    .map(el => [el[0], el[1].name])
                                    .filter((el, i, a) => a.map(elem => elem[1]).indexOf(el[1]) === i)
                                    .sort((a, b) => a[0] - b[0]);*/
                                this.loading = false;
                            })
                            .catch(error => {
                                this.handleError(error);
                                this.loading = false;
                            });
                        this.displayTable = true;
                        this.reset();
                    });
                },
                getXLS() {
                    axios.get('{{ route('human_resources.work_time_report') }}', { params: this.$route.query })
                            .then(response => {
                                document.getElementById("xls-link").click();
                                this.loading = false;
                            })
                            .catch(error => {
                                if (error.response && error.response.status === 404) {
                                    this.$message.error({
                                        dangerouslyUseHTMLString: true,
                                        message: 'Среди сотрудников указанного сводного табеля не найдено ни одной принадлежности к отчётной группе. Попробуйте присвоить должностным категориям сотрудников соответствующие отчётные группы.',
                                        showClose: true,
                                        duration: 10000,
                                    });
                                } else {
                                    this.handleError(error);
                                }
                                this.loading = false;
                            });
                },
                cellClassMethod({ row, column, rowIndex, columnIndex }) {
                    let classString = 'ignore-css';
                    if (columnIndex > 1) {
                        classString += ' text-center';
                    }
                    return classString;
                },
                headerCellClassMethod({ row, column, rowIndex, columnIndex }) {
                    return 'text-center';
                },
                headerCellStyleMethod({ row, column, rowIndex, columnIndex }) {
                    const colorfulCells = Object.keys(this.dealsColors);
                    if (colorfulCells.indexOf(column.label) !== -1) {
                        return `background-color: ${this.dealsColors[column.label]} !important; color: #444444;`;
                    }
                    return 'color: #444444;';
                },
                reset() {
                    this.observer_key += 1;
                    // this.user = '';
                    // this.month = '';
                    // this.location = '';
                    // this.dateFrom = '';
                    // this.dateTo = '';
                },
                handleResize() {
                    this.window_width = $(window).width();
                    this.window_height = $(window).height();
                },
                handleError(error) {
                    let msg = '';
                    if (error.response && error.response.data.errors) {
                        const keys = Object.keys(error.response.data.errors);
                        for (let i in keys) {
                            msg += `${error.response.data.errors[keys[i]][0]}<br>`;
                            /* switch (keys[i]) {
                                case 'name':
                                    msg += `${error.response.data.errors[keys[i]][0]}<br>`;
                                    break;
                                default:
                                    msg += `${error.response.data.errors[keys[i]][0]}<br>`;
                                    this.$message.error(error.response.data.errors[keys[i]][0]);
                            } */
                        }
                    } else {
                        console.log(error);
                        msg = 'Произошла ошибка. Пожалуйста, попробуйте снова или свяжитесь с тех. поддержкой.'
                    }
                    if (msg) {
                        this.$message.error({
                            dangerouslyUseHTMLString: true,
                            message: msg,
                            showClose: true,
                            duration: 10000,
                        });
                    }
                },
                promisedSearchUsers(query) {
                    return new Promise((resolve, reject) => {
                        if (query) {
                            axios.get('{{ route('tasks::get_users') }}', {
                                params: {
                                    q: query,
                                }
                            })
                                .then(response => resolve(response))
                                .catch(error => reject(error));
                        } else {
                            axios.get('{{ route('tasks::get_users') }}')
                                .then(response => resolve(response))
                                .catch(error => reject(error));
                        }
                    });
                },
                promisedSearchLocations(query) {
                    return new Promise((resolve, reject) => {
                        if (query) {
                            axios.post('{{ route('projects::get_projects_for_human') }}', {q: query})
                                .then(response => resolve(response))
                                .catch(error => reject(error));
                        } else {
                            axios.post('{{ route('projects::get_projects_for_human') }}')
                                .then(response => resolve(response))
                                .catch(error => reject(error));
                        }
                    });
                },
                promisedSearchLocationsById(id) {
                    return new Promise((resolve, reject) => {
                        if (id) {
                            axios.post('{{ route('projects::get_projects_for_human') }}', {selected: id})
                                .then(response => resolve(response))
                                .catch(error => reject(error));
                        } else {
                            axios.post('{{ route('projects::get_projects_for_human') }}')
                                .then(response => resolve(response))
                                .catch(error => reject(error));
                        }
                    });
                },
                searchUsers(query) {
                    this.promisedSearchUsers(query)
                        .then(response => this.users = response.data.results.map(el => { el.name = el.text; el.id = String(el.id); return el; }))
                        .catch(error => console.log(error));
                },
                searchLocations(query) {
                    this.promisedSearchLocations(query)
                        .then(response => this.locations = response.data.map(el => ({ name: el.label, id: el.code })))
                        .catch(error => console.log(error));
                },
            }
        });
    </script>
@endsection
