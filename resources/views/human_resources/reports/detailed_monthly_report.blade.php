@extends('layouts.app')

@section('title', 'Табели')

@section('url', route('human_resources.report.detailed_report'))

@section('css_top')
    <link rel="stylesheet" href="{{ asset('css/balloon.css') }}">
    <style media="screen">
        html {
            overflow-x: hidden;
        }

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

        .cell .el-input__inner {
            height: auto;
            line-height: 21px;
            padding: 0;
            text-align: center;
        }

        .el-table_1_column_1 .cell .el-select .el-input__inner {
            padding: 0 15px;
            text-align: left;
        }

        .cell .el-input__icon {
            line-height: 21px;
        }

        .el-input-number .el-input__inner {
            padding-left: 10px !important;
            padding-right: 10px !important;
        }

        .el-range-separator {
            width: inherit !important;
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
                    <li class="nav-item active">
                        <a class="nav-link link-line active-link-line" href="#">
                            Детальный месячный табель сотрудника
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link link-line" href="{{ route('human_resources.report.summary_report') }}">
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
                            <div class="col-md-3 col-lg-4">
                                <label for="">Сотрудник<span class="star">*</span></label>
                                <validation-provider rules="required" vid="user-select"
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
                            <div class="col-md-3 col-lg-4">
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
                            <div class="col-md-3 col-lg-2">
                                <button type="button"
                                        style="margin:27px 0 0 0"
                                        class="btn btn-primary btn-outline d-none d-md-block w-100"
                                        @click="show">
                                    Показать
                                </button>
                            </div>
                            <div class="col-md-3 col-lg-2">
                                <div v-if="window_width > 768 && displayTable" class="btn-group" style="width: 100%" role="group" aria-label="control-panel">
                                    <button v-if="displayTable" type="button" class="btn btn-primary btn-outline mobile-button-margin d-block w-100"
                                        style="margin:27px 0 0 0; border-right: 0"
                                        @click="displayReportSidebar = !displayReportSidebar">
                                        Управление
                                    </button>
                                    <button v-if="displayTable" type="button" class="btn btn-primary mobile-button-margin"
                                        :class="!displayReportSidebar ? 'btn-outline' : ''"
                                        style="margin:27px 0 0 0"
                                        @click="displayReportSidebar = !displayReportSidebar">
                                        <i v-if="!displayReportSidebar" class="fa fa-arrow-left"></i>
                                        <i v-else class="fa fa-arrow-right"></i>
                                    </button>
                                </div>
                                <button v-else-if="displayTable" type="button" class="btn btn-primary mobile-button-margin d-block w-100"
                                    :class="!displayReportSidebar ? 'btn-outline' : ''"
                                    style="margin:27px 0 0 0"
                                    @click="displayReportSidebar = !displayReportSidebar">
                                    Управление <i v-if="!displayReportSidebar" class="fa fa-arrow-down"></i>
                                    <i v-else class="fa fa-arrow-up"></i>
                                </button>
                            </div>
                        </div>
                        <div class="row d-md-none">
                            <div class="col">
                                <button type="button"
                                        class="btn btn-primary btn-outline d-block mobile-button-margin w-100"
                                        @click="show">
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
                    Укажите сотрудника и месяц
                </div>
            </div>
            <div v-else :class="window_width > 769 ? 'd-flex flex-row-reverse justify-content-center' : ''">
                <div v-if="window_width > 769 || displayReportSidebar" :class="{'card': true, 'active': displayReportSidebar, 'animated-sidebar': initSidebar }" id="report-sidebar">
                    <div class="card-body card-body-tech">
                        <h6 class="decor-h6-modal mt-0">КТУ</h6>
                        <div class="row">
                            <div class="col">
                                <validation-observer ref="report-sidebar-observer-ktu">
                                    <validation-provider rules="{{--|positive--}}" vid="ktu-input"
                                                         ref="ktu-input" v-slot="v">
                                        <el-input-number
                                            class="w-100"
                                            :class="v.classes"
                                            id="ktu-input"
                                            :min="0"
                                            :max="100"
                                            :step="1"
                                            :precision="2"
                                            placeholder="Введите КТУ"
                                            v-model="ktu"
                                        ></el-input-number>
                                        <div class="error-message">@{{ v.errors[0] }}</div>
                                    </validation-provider>
                                </validation-observer>
                            </div>
                        </div>
                        <h6 class="decor-h6-modal">Поощрения и компенсации</h6>
                        <div class="row">
                            <div class="col">
                                <el-collapse v-model="activeName" accordion>
                                    <el-collapse-item title="Премии" name="1">
                                        @include('human_resources.reports.modules.sidebar_sections.bonuses_section')
                                    </el-collapse-item>
                                    <el-collapse-item title="Компенсации" name="2">
                                        @include('human_resources.reports.modules.sidebar_sections.compensations_section')
                                    </el-collapse-item>
                                    <el-collapse-item title="Штрафы" name="3">
                                        @include('human_resources.reports.modules.sidebar_sections.fines_section')
                                    </el-collapse-item>
                                </el-collapse>
                            </div>
                        </div>
                        <h6 class="decor-h6-modal">Действия</h6>
                        <div class="row">
                            <div class="col">
                                <button type="button" class="btn btn-primary d-block w-100" :disabled="loadingSidebar" @click.stop="saveSidebar">
                                    Сохранить изменения
                                </button>
                                <button type="button" v-if="is_opened" class="btn btn-danger d-block w-100" :disabled="loadingClose" @click.stop="closeReport">
                                    Закрыть месячный табель
                                </button>
                                <button type="button" v-else class="btn btn-success btn-outline d-block w-100" :disabled="loadingOpen" @click.stop="openReport">
                                    Открыть месячный табель
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="card">
                    <div class="card-body card-body-tech">
                        <div class="row">
                            <div class="font-weight-bold text-md-right col-12 col-md order-md-1">Детальный табель (@{{ reportPeriodString }})</div>
                            <h4 class="h4-tech fw-500 m-0 col-12 col-md-auto order-md-0" style="margin-top:0">
                                <span>@{{ userName }}</span>
                            </h4>
                        </div>
                        <div>
                            @{{ userInfo }}
                        </div>
                        <el-table border
                                  :data="tableData"
                                  :cell-class-name="cellClassMethod"
                                  :cell-style="cellStyleMethod"
                                  :row-class-name="rowClassMethod"
                                  :header-cell-class-name="headerCellClassMethod"
                                  :max-height="max_table_height"
                                  class="ignore-css mt-2"
                        >
                            <el-table-column
                                :fixed="window_width > 769"
                                width="270"
                            >
                                <template slot-scope="scope">
                                    <div v-if="scope.row.addObjectRow">
                                        <button type="button"
                                                class="btn btn-secondary btn-sm btn-outline d-none d-block w-100"
                                                @click="addObjectRow"
                                        >
                                            <i class="fa fa-plus"></i>
                                            Добавить проект
                                        </button>
                                    </div>
                                    <div v-else-if="scope.row.addOfferRow">
                                        <button type="button"
                                                class="btn btn-secondary btn-sm btn-outline d-none d-block w-100"
                                                @click="addOfferRow"
                                        >
                                            <i class="fa fa-plus"></i>
                                            Добавить сделку
                                        </button>
                                    </div>
                                    <offer-row-title v-else-if="scope.row.offerRow"
                                                     :offer-types="tariffs_t3"
                                                     :row="scope.row"
                                                     :handle-error="handleError"
                                                     :timecard-id="timecardData.id"
                                                     :value="scope.row.title"
                                    ></offer-row-title>
                                    <div style="min-height: 23px" v-else>
                                        @{{ scope.row.hasOwnProperty('title') ? scope.row.title : '' }}
                                    </div>
                                </template>
                            </el-table-column>
                            <el-table-column width="90">
                                <template slot-scope="scope">
                                    <input-cell v-if="scope.row.offerRow"
                                                :row="scope.row"
                                                :handle-error="handleError"
                                                :tariff-id="scope.row.tariff_id"
                                                :timecard-id="timecardData.id"
                                                :table-index="scope.$index"
                                                rules="numeric|positive|max_value:30"
                                                maxlength="5"
                                    ></input-cell>
                                </template>
                            </el-table-column>
                            <el-table-column
                                v-for="i in daysInMonth"
                                width="90"
                                :class-name="weekends[i - 1] == '1'  ? 'weekendColumn' : ''"
                                :label="String(i)"
                            >
                                <template slot-scope="scope">
                                    <sum-cell   v-if="scope.row.sumRow"
                                                :row="scope.row"
                                                :day-index="i"
                                                :abbreviations="abbreviations"
                                                :timecard-day-id="timecardDays[i] ? timecardDays[i].id : ''"
                                                :day-tariffs="tableData.filter(el => el.tariffRow).map(el => el.days[i])"
                                                {{-- :day-amounts="tableData.filter(el => el.tariffRow || el.offerRow).map(el => el.days[i])" --}}
                                                :handle-error="handleError"
                                    ></sum-cell>
                                    <input-cell v-else-if="!scope.row.objectRow && !scope.row.immutableRow"
                                                :row="scope.row"
                                                :day-index="i"
                                                :table-index="scope.$index"
                                                :handle-error="handleError"
                                                :timecard-day-id="timecardDays[i] ? timecardDays[i].id : ''"
                                                rules="natural|max_value:24"
                                                maxlength="2"
                                    ></input-cell>
                                    <project-intervals-cell v-else-if="scope.row.objectRow"
                                                    :project="scope.row.days[i]"
                                                    :row="scope.row"
                                                    :day-index="i"
                                                    :locations="locations"
                                                    :hide-tooltips="hideTooltips"
                                                    :handle-error="handleError"
                                                    :window-width="window_width"
                                                    :timecard-day-id="timecardDays[i] ? timecardDays[i].id : ''"
                                                    :search-locations="searchLocations"
                                    ></project-intervals-cell>
                                </template>
                            </el-table-column>
                        </el-table>
                        <el-table border
                                  :data="summarized_data"
                                  :cell-style="summaryCellStyleMethod"
                                  :max-height="max_table_height"
                                  style="width: 351px"
                                  :show-header="false"
                                  class="ignore-css mt-4"
                        >
                            <el-table-column
                                width="270"
                                prop="tariff_name"
                            ></el-table-column>
                            <el-table-column
                                align="right"
                                prop="sum"
                            ></el-table-column>
                        </el-table>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('js_footer')
    <script>
        Vue.component('validation-provider', VeeValidate.ValidationProvider);
        Vue.component('validation-observer', VeeValidate.ValidationObserver);
    </script>
    @include('human_resources.reports.modules.components.offer_row_title')
    @include('human_resources.reports.modules.components.input_cell')
    @include('human_resources.reports.modules.components.sum_cell')
    @include('human_resources.reports.modules.components.project_intervals_cell')
    <script>
        const vm = new Vue({
            el: '#base',
            router: new VueRouter({
                mode: 'history',
                routes: [],
            }),
            data: {
                displayTable: false, // false
                displayReportSidebar: false, // false
                initSidebar: false,
                observer_key: 1,
                bonus_observer_key: 1,
                compensation_observer_key: 1,
                fine_observer_key: 1,
                user: '',
                month: '',
                location: '',
                foundLocations: [],
                preloadedLocations: [],
                allLocations: {!! json_encode($projects) !!},
                users: [],
                bonuses: [],
                bonuses_deleted: [],
                fines: [],
                fines_deleted: [],
                compensations: [],
                compensations_deleted: [],
                bonuses_names: [],
                compensation_names: [],
                fine_names: [],

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

                daysInMonth: 31,
                weekendDay: 2,
                weekends: '',
                summarized_data: [],
                tableData: [],
                timecardData: {!! json_encode($data) !!},

                dealsColors: {
                    'Погружение вибро': '#fb7981',
                    'Погружение вдвоём вибро': '#fdb2b6',
                    'Извлечение вибро': '#fdd9db',
                    'Погружение статика': '#4e9eff',
                    'Извлечение статика': '#99c8fe',
                },

                abbreviations: ['Б', 'БИР', 'Д', 'З', 'Н', 'О', 'П', 'У'],

                ktu: 0,
                timecardId: 1,
                timecardDays: [],
                tariffs: [],
                activeName: '',
                is_opened: false,
                timecardUser: null,

                userName: '',
                userInfo: '',

                loading: false,
                loadingSidebar: false,
                sidebarError: false,
                loadingKtu: false,
                loadingCompensations: false,
                loadingBonuses: false,
                loadingFines: false,
                loadingClose: false,
                loadingOpen: false,
                window_width: 10000,
                window_height: 10000,
            },
            computed: {
                max_table_height() {
                    return this.window_width <= 768 ? Math.ceil(this.window_height * 0.75) : 10000;
                },
                isCurrentMonth() {
                    return this.timecardData && this.timecardData.year && this.timecardData.month
                        && moment().year() === this.timecardData.year && moment().month() + 1 === this.timecardData.month;
                },
                locations() {
                    const uniquePreloadedLocations = this.preloadedLocations
                        .filter(el => el ? this.foundLocations.map(elem => String(elem.code)).indexOf(String(el.code)) === -1 : false);
                    return [ ...this.foundLocations, ...uniquePreloadedLocations.map(el => ({code: String(el.code), name: el.name})) ];
                },
                reportPeriodString() {
                    let date = this.$route.query.month;
                    if (date) {
                        return moment(date, 'YYYY-MM').locale('ru').format('MMMM YYYY');
                    }
                    return '';
                },
                tariffs_t2() {
                    return this.tariffs.filter(el => el.type === 2);
                },
                tariffs_t3() {
                    return this.tariffs.filter(el => el.type === 3);
                }
            },
            created() {
                let that = this;
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
                    } else if (field === 'month') {
                        this.month = value;
                    }
                });
                if (keys.indexOf('user_id') === -1) {
                    this.searchUsers('');
                }
                this.searchLocations('');
                this.searchBonuses('');
                this.searchCompensations('');
                this.searchFines('');

                if (!Array.isArray(this.timecardData)) {
                    this.timecardId = this.timecardData.id;
                    this.userName = this.timecardData.user.long_full_name;
                    this.userInfo = this.timecardData.user.group_name + ' / ' + this.timecardData.user.company_name;
                    this.ktu = this.timecardData.ktu;
                    this.bonuses = this.timecardData.bonuses.map(bonus => { bonus.project_id = String(bonus.project_id); return bonus; });
                    this.fines = this.timecardData.fines;
                    this.compensations = this.timecardData.compensations.map(el => { el.prolonged = el.prolonged == 1 ? true : false; return el; });
                    this.timecardDays = this.timecardData.days_id;
                    this.tariffs = this.timecardData.tariff_manual.map(el => {
                        el.type += 1;
                        return el;
                    });
                    this.summarized_data = Object.values(this.timecardData.summarized_data).map(el => {
                        if (!el.tariff_name) {
                            el.tariff_name = 'Сумма часов';
                        }
                        return el;
                    });
                    this.is_opened = this.timecardData.is_opened === 1;
                    this.timecardUser = this.timecardData.user;
                    this.daysInMonth = moment(`${this.timecardData.year}-${this.timecardData.month}`, 'YYYY-MM').daysInMonth();
                    this.weekendDay = 7 - moment(`${this.timecardData.year}-${this.timecardData.month}`, 'YYYY-MM').isoWeekday();
                    this.updateTable(this.timecardData.detailed_data);
                    this.displayTable = true;
                    this.displayReportSidebar = false;
                    if (!this.initSidebar) {
                        setTimeout(() => {this.initSidebar = true}, 1000);
                    }
                    this.loadLocations();
                }
            },
            mounted() {
                let that = this;
                $(window).on('resize', this.handleResize);
                this.handleResize();
                axios.get('https://isdayoff.ru/api/getdata?year=' + this.timecardData.year + '&month=' + this.timecardData.month, { transformResponse: [data => data] }).then(response => {
                    that.weekends = response.data.toString().split('');
                });
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
                            queryObj.location = this.location;
                        }
                        if (this.month) {
                            queryObj.month = this.month;
                        }
                        this.$router.replace({query: queryObj}).catch(err => {});

                        this.reset();

                        this.loading = true;
                        axios.post('{{ route('human_resources.report.detailed_data') }}', queryObj)
                            .then(response => {
                                this.timecardData = response.data.data;
                                if (!Array.isArray(this.timecardData)) {
                                    this.timecardId = this.timecardData.id;
                                    this.userName = this.timecardData.user.long_full_name;
                                    this.userInfo = this.timecardData.user.group_name + ' / ' + this.timecardData.user.company_name;
                                    this.ktu = this.timecardData.ktu;
                                    this.bonuses = this.timecardData.bonuses.map(bonus => { bonus.project_id = String(bonus.project_id); return bonus; });
                                    this.fines = this.timecardData.fines;
                                    this.compensations = this.timecardData.compensations.map(el => { el.prolonged = el.prolonged == 1 ? true : false; return el; });
                                    this.timecardDays = this.timecardData.days_id;
                                    this.tariffs = this.timecardData.tariff_manual.map(el => {
                                        el.type += 1;
                                        return el;
                                    });
                                    this.summarized_data = Object.values(this.timecardData.summarized_data).map(el => {
                                        if (!el.tariff_name) {
                                            el.tariff_name = 'Сумма часов';
                                        }
                                        return el;
                                    });
                                    this.is_opened = this.timecardData.is_opened === 1;
                                    this.timecardUser = this.timecardData.user;
                                    this.daysInMonth = moment(`${this.timecardData.year}-${this.timecardData.month}`, 'YYYY-MM').daysInMonth();
                                    this.weekendDay = 7 - moment(`${this.timecardData.year}-${this.timecardData.month}`, 'YYYY-MM').isoWeekday();
                                    this.updateTable(this.timecardData.detailed_data);
                                    this.displayTable = true;
                                    this.displayReportSidebar = false;
                                    if (!this.initSidebar) {
                                        setTimeout(() => {this.initSidebar = true}, 1000);
                                    }
                                    this.loadLocations();
                                } else {
                                    this.displayTable = false;
                                    this.displayReportSidebar = false;
                                    this.$message.error({
                                        message: 'Указанного месячного табеля не существует',
                                        showClose: true,
                                        duration: 10000,
                                    });
                                }
                                this.loading = false;
                            })
                            .catch(error => {
                                this.displayTable = false;
                                this.displayReportSidebar = false;
                                this.handleError(error);
                                this.loading = false;
                            });
                    });
                },
                cellClassMethod({ row, column, rowIndex, columnIndex }) {
                    let classString = 'ignore-css';
                    if (row.fullRow && columnIndex === 0) {
                        classString += ' full-section-row-cell';
                    }
                    if (columnIndex !== 0) {
                        classString += ' text-center';
                    }
                    return classString;
                },
                headerCellClassMethod({ row, column, rowIndex, columnIndex }) {
                    return 'text-center header-text-color';
                },
                cellStyleMethod({ row, column, rowIndex, columnIndex }) {
                    const colorfulCells = Object.keys(this.dealsColors);
                    let styleString = '';
                    if (row.objectRow) {
                        styleString += 'vertical-align: top; ';
                    }
                    if (colorfulCells.indexOf(row.title) !== -1 && columnIndex === 0) {
                        styleString += `background-color: ${this.dealsColors[row.title]} !important`;
                    }
                    return styleString;
                },
                summaryCellStyleMethod({ row, column, rowIndex, columnIndex }) {
                    const colorfulCells = Object.keys(this.dealsColors);
                    if (colorfulCells.indexOf(row.tariff_name) !== -1 && columnIndex === 0) {
                        return `background-color: ${this.dealsColors[row.tariff_name]} !important`;
                    }
                    return '';
                },
                rowClassMethod({ row, rowIndex }) {
                    if (row.fullRow) {
                        return 'full-section-row';
                    }
                    return '';
                },
                reset() {
                    this.observer_key += 1;
                    // this.user = '';
                    // this.month = '';
                    // this.location = '';
                },
                updateTable(detailedData) {
                    this.tableData = this.getNewTable(this.daysInMonth);
                    let cells = [].concat(...Object.values(detailedData));
                    let uniqueOffers = cells.filter(cell => cell.type === 3)
                        .filter((cell, i, a) => {
                            return a.map(el => el.tariff_id + ' ' + el.length).indexOf(cell.tariff_id + ' ' + cell.length) === i;
                        });
                    let offerRows = [];
                    uniqueOffers.forEach(offer => {
                        const offerRow = this.getNewDataRow(offer.tariff_name, offer.tariff_id, this.daysInMonth);
                        offerRow.offerRow = true;
                        offerRow.materialLength = offer.length;
                        offerRows.push(offerRow);
                    });
                    offerRows.sort((a, b) => {
                        if (a.title < b.title) {
                            return 1;
                        }
                        if (a.title > b.title) {
                            return -1;
                        }
                        return a.materialLength - b.materialLength;
                    });
                    this.tableData.push(...offerRows);
                    this.tableData.push(this.getNewAddOfferRow());
                    this.tableData.push(this.getNewFullRow('Проекты'));
                    let objectRowsCount = 0;
                    cells.filter(cell => cell.type === 1)
                        .map(cell => cell.day + ' ' + cell.project_id)
                        .filter((cell, i, a) => a.indexOf(cell) === i)
                        .map(cell => cell.split(' ')[0])
                        .forEach((day, i, a) => objectRowsCount = a.filter(el => el === day).length > objectRowsCount ? a.filter(el => el === day).length : objectRowsCount);
                    for (let i = 0; i < objectRowsCount; i++) {
                        this.tableData.push(this.getNewObjectRow(this.daysInMonth));
                    }
                    this.tableData.push(this.getNewAddObjectRow());
                    const objectRowOffset = this.tableData.map(el => el.objectRow).indexOf(true);
                    cells.forEach(cell => {
                        switch (cell.type) {
                            case 1:
                                if (!cell.end) {
                                    cell.end = '';
                                }
                                if (!cell.start) {
                                    cell.start = '';
                                }
                                if (!cell.commentary) {
                                    const objectRowIndex = cells.filter(el => el.type === 1 && el.day === cell.day).map(el => el.project_id).filter((el, i, a) => a.indexOf(el) === i).indexOf(cell.project_id);
                                    this.tableData[objectRowOffset + objectRowIndex].days[cell.day].location = String(cell.project_id);
                                    if (!(this.tableData[objectRowOffset + objectRowIndex].days[cell.day].intervals.length === 1
                                        && this.tableData[objectRowOffset + objectRowIndex].days[cell.day].intervals[0].timeFrom === ''
                                        && this.tableData[objectRowOffset + objectRowIndex].days[cell.day].intervals[0].timeTo === '')) {
                                        this.tableData[objectRowOffset + objectRowIndex].days[cell.day].intervals.push({
                                            timeFrom: cell.start ? cell.start.split(':').join('-') : null,
                                            timeTo: cell.end ? cell.end.split(':').join('-') : null,
                                            id: cell.id,
                                        })
                                    } else {
                                        this.tableData[objectRowOffset + objectRowIndex].days[cell.day].intervals[0] = {
                                            timeFrom: cell.start ? cell.start.split(':').join('-'): null,
                                            timeTo: cell.end ? cell.end.split(':').join('-') : null,
                                            id: cell.id,
                                        }
                                    }
                                } else {
                                    const sumRowIndex = 0;
                                    this.tableData[sumRowIndex].days[cell.day] = {id: cell.id, tariff_id: cell.tariff_id, commentary: cell.commentary, amount: cell.amount, timecard_day_id: cell.timecard_day_id, project_id: cell.project_id};
                                }
                                break;
                            case 2:
                                const tariffRowIndex = this.tableData.map(el => el.tariff_id).indexOf(cell.tariff_id);
                                this.tableData[tariffRowIndex].days[cell.day] = {id: cell.id, tariff_id: cell.tariff_id, amount: cell.amount, timecard_day_id: cell.timecard_day_id};
                                break;
                            case 3:
                                const offerRowIndex = this.tableData.map(el => el.tariff_id + ' ' + el.materialLength).indexOf(cell.tariff_id + ' ' + cell.length);
                                this.tableData[offerRowIndex].days[cell.day] = {id: cell.id, tariff_id: cell.tariff_id, amount: cell.amount, length: cell.length};
                                break;
                        }
                    });
                },
                getNewTable(daysInMonth) {
                    let newTable = [];
                    let sumRow = this.getNewDataRow('Сумма часов', -1, daysInMonth);
                    sumRow.sumRow = true;
                    newTable.push(sumRow);
                    newTable.push(this.getNewFullRow('Часы'));
                    this.tariffs_t2.forEach(el => {
                        let tariffRow = this.getNewDataRow(el.name, el.id, daysInMonth);
                        tariffRow.tariffRow = true;
                        newTable.push(tariffRow);
                    });
                    newTable.push(this.getNewFullRow('Сделки'));
                    return newTable;
                },
                getNewDataRow(title, tariff_id, daysInMonth) {
                    let newRow = {};
                    newRow.days = {};
                    newRow.title = title;
                    newRow.tariff_id = tariff_id;
                    for (let i = 1; i <= daysInMonth; i++) {
                        newRow.days[i] = {};
                    }
                    return newRow;
                },
                getNewFullRow(title) {
                    let newRow = {};
                    newRow.immutableRow = true;
                    newRow.fullRow = true;
                    newRow.title = title;
                    return newRow;
                },
                getNewAddOfferRow() {
                    let newRow = {};
                    newRow.immutableRow = true;
                    newRow.addOfferRow = true;
                    return newRow;
                },
                getNewAddObjectRow() {
                    let newRow = {};
                    newRow.immutableRow = true;
                    newRow.addObjectRow = true;
                    return newRow;
                },
                getNewObjectRow(daysInMonth) {
                    let newRow = {};
                    newRow.title = '';
                    newRow.objectRow = true;
                    newRow.days = {};
                    for (let i = 1; i <= daysInMonth; i++) {
                        newRow.days[i] = {
                            location: '',
                            intervals: [{
                                timeFrom: '',
                                timeTo: '',
                            }],
                        };
                    }
                    return newRow;
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
                addOfferRow() {
                    const buttonRowIndex = this.tableData.map(el => el.addOfferRow).indexOf(true);
                    const newOfferRow = this.getNewDataRow(this.tariffs_t3[0].name, this.tariffs_t3[0].id, this.daysInMonth);
                    newOfferRow.offerRow = true;
                    newOfferRow.materialLength = '';
                    this.tableData.splice(buttonRowIndex, 0, newOfferRow);
                },
                addObjectRow() {
                    const buttonRowIndex = this.tableData.map(el => el.addObjectRow).indexOf(true);
                    this.tableData.splice(buttonRowIndex, 0, this.getNewObjectRow(this.daysInMonth));
                },
                openReport() {
                    this.$confirm('Вы уверены, что хотите открыть месячный табель сотрудника?', 'Внимание', {
                        confirmButtonText: 'Продолжить',
                        cancelButtonText: 'Отмена',
                        type: 'warning'
                    }).then(() => {
                        const payload = {};
                        payload.is_opened = 1;
                        payload.timecard_id = this.timecardId;
                        this.loadingOpen = true;
                        axios.put('{{ route('human_resources.timecard.update_openness', 'TIMECARD_ID') }}'.split('TIMECARD_ID').join(this.timecardId), payload)
                            .then(response => {
                                this.loadingOpen = false;
                                this.is_opened = true;
                            })
                            .catch(error => {
                                this.handleError(error);
                                this.loadingOpen = false;
                            });
                    }).catch(() => {});
                },
                closeReport() {
                    this.$confirm('Вы уверены, что хотите закрыть месячный табель сотрудника?', 'Внимание', {
                        confirmButtonText: 'Продолжить',
                        cancelButtonText: 'Отмена',
                        type: 'warning'
                    }).then(() => {
                        const payload = {};
                        payload.is_opened = 0;
                        payload.timecard_id = this.timecardId;
                        this.loadingClose = true;
                        axios.put('{{ route('human_resources.timecard.update_openness', 'TIMECARD_ID') }}'.split('TIMECARD_ID').join(this.timecardId), payload)
                            .then(response => {
                                this.loadingClose = false;
                                this.is_opened = false;
                            })
                            .catch(error => {
                                this.handleError(error);
                                this.loadingClose = false;
                                });
                    }).catch(() => {});
                },
                saveSidebar() {
                    this.loadingKtu = true;
                    this.loadingCompensations = true;
                    this.loadingBonuses = true;
                    this.loadingFines = true;
                    const ktuPayload = {};
                    this.ktu = this.ktu ? this.ktu : 0;
                    ktuPayload.ktu = this.ktu ? this.ktu : 0;
                    ktuPayload.timecard_id = this.timecardId;
                    const compensationsPayload = {};
                    compensationsPayload.timecard_id = this.timecardId;
                    compensationsPayload.compensations = this.compensations;
                    if (this.compensations_deleted.length > 0) {
                        compensationsPayload.deleted_addition_ids = this.compensations_deleted;
                    }
                    const bonusesPayload = {};
                    bonusesPayload.timecard_id = this.timecardId;
                    bonusesPayload.bonuses = this.bonuses;
                    if (this.bonuses_deleted.length > 0) {
                        bonusesPayload.deleted_addition_ids = this.bonuses_deleted;
                    }
                    const finesPayload = {};
                    finesPayload.timecard_id = this.timecardId;
                    finesPayload.fines = this.fines;
                    if (this.fines_deleted.length > 0) {
                        finesPayload.deleted_addition_ids = this.fines_deleted;
                    }
                    this.loadingSidebar = true;
                    axios.put('{{ route('human_resources.timecard.update_ktu', 'TIMECARD_ID') }}'.split('TIMECARD_ID').join(this.timecardId), ktuPayload)
                        .then(response => {
                            this.loadingKtu = false;
                            this.setSidebarLoading();
                        })
                        .catch(error => {
                            this.handleError(error);
                            this.sidebarError = true;
                            this.loadingKtu = false;
                            this.setSidebarLoading();
                        });
                    axios.put('{{ route('human_resources.timecard.update_compensations', 'TIMECARD_ID') }}'.split('TIMECARD_ID').join(this.timecardId), compensationsPayload)
                        .then(response => {
                            this.loadingCompensations = false;
                            this.setSidebarLoading();
                        })
                        .catch(error => {
                            this.handleError(error);
                            this.sidebarError = true;
                            this.loadingCompensations = false;
                            this.setSidebarLoading();
                        });
                    axios.put('{{ route('human_resources.timecard.update_bonuses', 'TIMECARD_ID') }}'.split('TIMECARD_ID').join(this.timecardId), bonusesPayload)
                        .then(response => {
                            this.loadingBonuses = false;
                            this.setSidebarLoading();
                        })
                        .catch(error => {
                            this.handleError(error);
                            this.sidebarError = true;
                            this.loadingBonuses = false;
                            this.setSidebarLoading();
                        });
                    axios.put('{{ route('human_resources.timecard.update_fines', 'TIMECARD_ID') }}'.split('TIMECARD_ID').join(this.timecardId), finesPayload)
                        .then(response => {
                            this.loadingFines = false;
                            this.setSidebarLoading();
                        })
                        .catch(error => {
                            this.handleError(error);
                            this.sidebarError = true;
                            this.loadingFines = false;
                            this.setSidebarLoading();
                        });
                },
                setSidebarLoading() {
                    this.loadingSidebar = this.loadingKtu || this.loadingCompensations
                        || this.loadingBonuses || this.loadingFines;
                    if (!this.loadingSidebar) {
                        if (!this.sidebarError) {
                            this.$message.success({
                                message: 'Изменения успешно сохранены.',
                                showClose: true,
                            });
                        }
                        this.loadingSidebar = true;
                        axios.post('{{ route('human_resources.report.detailed_data') }}', { user_id: this.timecardData.user_id, month: `${this.timecardData.year}-${this.timecardData.month}` })
                            .then(response => {
                                this.loadingSidebar = false;
                                const tempTimecardData = response.data.data;
                                this.ktu = tempTimecardData.ktu;
                                this.bonuses = tempTimecardData.bonuses.map(bonus => { bonus.project_id = String(bonus.project_id); return bonus; });
                                this.fines = tempTimecardData.fines;
                                this.compensations = tempTimecardData.compensations.map(el => { el.prolonged = el.prolonged == 1 ? true : false; return el; });
                            })
                        this.compensations_deleted = [];
                        this.bonuses_deleted = [];
                        this.fines_deleted = [];
                        this.sidebarError = false;
                    }
                },
                addBonus() {
                    this.bonuses.push({ name: '', project_id: '', amount: 0, });
                    this.bonus_observer_key += 1;
                },
                removeBonus(index) {
                    if (this.bonuses[index].id) {
                        this.bonuses_deleted.push(this.bonuses[index].id)
                    }
                    this.bonuses.splice(index, 1);
                    this.bonus_observer_key += 1;
                    this.hideTooltips();
                },
                addCompensation() {
                    this.compensations.push({ name: '', prolonged: false, amount: 0, });
                    this.compensation_observer_key += 1;
                },
                removeCompensation(index) {
                    if (this.compensations[index].id) {
                        this.compensations_deleted.push(this.compensations[index].id)
                    }
                    this.compensations.splice(index, 1);
                    this.compensation_observer_key += 1;
                    this.hideTooltips();
                },
                addFine() {
                    this.fines.push({ name: '', amount: 0, });
                    this.fine_observer_key += 1;
                },
                removeFine(index) {
                    if (this.fines[index].id) {
                        this.fines_deleted.push(this.fines[index].id)
                    }
                    this.fines.splice(index, 1);
                    this.fine_observer_key += 1;
                    this.hideTooltips();
                },
                hideTooltips() {
                    for (let ms = 50; ms <= 1050; ms += 100) {
                        setTimeout(() => {
                            $('[data-balloon-pos]').blur();
                        }, ms);
                    }
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
                getSelectedLocationsIdsByDay(day) {
                    let locationsIds = this.tableData
                        .filter(el => el.objectRow)
                        .map(el => el.days[day])
                        .map(el => el.location)
                        .filter(el => el)
                        .filter((el, i, a) => a.indexOf(el) === i);
                    return locationsIds;
                },
                loadLocations() {
                    let locationsIds = this.tableData
                        .filter(el => el.objectRow)
                        .map(el => Object.values(el.days))
                        .map(el => el.map(elem => elem.location));
                    locationsIds = [].concat(...locationsIds)
                        .concat(this.bonuses.map(bonus => String(bonus.project_id)))
                        .filter(el => el)
                        .filter((el, i, a) => a.indexOf(el) === i);
                    locationsIds.forEach(el => {
                        if (this.preloadedLocations.map(elem => elem.code).indexOf(el) === -1) {
                            const searchResult = this.allLocations.filter(elem => elem.code == el);
                            if (searchResult.length > 0) {
                                this.preloadedLocations.push(searchResult[0]);
                            }
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
                searchLocations(query) {
                    this.promisedSearchLocations(query)
                        .then(response => this.foundLocations = response.data.map(el => ({ name: el.label, code: el.code })))
                        .catch(error => console.log(error));
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
                searchUsers(query) {
                    this.promisedSearchUsers(query)
                        .then(response => this.users = response.data.results.map(el => { el.name = el.text; el.id = String(el.id); return el; }))
                        .catch(error => console.log(error));
                },
                searchBonuses(query) {
                    if (query) {
                        axios.post('{{ route('human_resources.timecard.get_addition_names') }}', {q: query, type: 3})
                            .then(response => this.bonuses_names = response.data.map(el => { return el; }))
                            .catch(error => console.log(error));
                    } else {
                        axios.post('{{ route('human_resources.timecard.get_addition_names') }}', {type: 3})
                            .then(response => this.bonuses_names = response.data.map(el => { return el; }))
                            .catch(error => console.log(error));
                    }
                },
                searchCompensations(query) {
                    if (query) {
                        axios.post('{{ route('human_resources.timecard.get_addition_names') }}', {q: query, type: 1})
                            .then(response => this.compensation_names = response.data.map(el => { return el; }))
                            .catch(error => console.log(error));
                    } else {
                        axios.post('{{ route('human_resources.timecard.get_addition_names') }}', {type: 1})
                            .then(response => this.compensation_names = response.data.map(el => { return el; }))
                            .catch(error => console.log(error));
                    }
                },
                searchFines(query) {
                    if (query) {
                        axios.post('{{ route('human_resources.timecard.get_addition_names') }}', {q: query, type: 2})
                            .then(response => this.fine_names = response.data.map(el => { return el; }))
                            .catch(error => console.log(error));
                    } else {
                        axios.post('{{ route('human_resources.timecard.get_addition_names') }}', {type: 2})
                            .then(response => this.fine_names = response.data.map(el => { return el; }))
                            .catch(error => console.log(error));
                    }
                },
            }
        });

        const eventHub = new Vue();
    </script>
@endsection
