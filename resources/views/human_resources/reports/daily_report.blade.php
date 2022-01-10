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
                    <li class="nav-item">
                        <a class="nav-link link-line" href="{{ route('human_resources.report.detailed_report') }}">
                            Детальный месячный табель сотрудника
                        </a>
                    </li>
                    <li class="nav-item ">
                        <a class="nav-link link-line" href="{{ route('human_resources.report.summary_report') }}">
                            Сводный табель
                        </a>
                    </li>
                    <li class="nav-item active">
                        <a class="nav-link link-line active-link-line" href="#">
                            Суточный табель
                        </a>
                    </li>
                </ul>
            </div>
            {{-- <div aria-label="breadcrumb" role="navigation">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item active" aria-current="page">Табели</li>
                </ol>
            </div> --}}
            <div class="card">
                <div class="card-body-tech">
                    <div class="row">
                        <div class="col-md-12">
                            <h6 class="mb-20" style="margin-top:5px">Фильтры</h6>
                        </div>
                    </div>
                    <validation-observer ref="observer" :key="observer_key">
                        <div class="row mb-2">
                            <div class="col-md-4 col-xl-5">
                                <label for="">Проект<span class="star">*</span></label>
                                <validation-provider rules="required" vid="location-id-select"
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
                                            :key="item.code"
                                            :label="item.name"
                                            :value="item.code">
                                        </el-option>
                                    </el-select>
                                    <div class="error-message">@{{ v.errors[0] }}</div>
                                </validation-provider>
                            </div>
                            <div class="col-md-4 col-xl-5">
                                <label for="">
                                    Дата<span class="star">*</span>
                                </label>
                                <validation-provider rules="required" v-slot="v"
                                                         vid="date-input"
                                                         ref="date-input"
                                >
                                    <el-date-picker
                                        style="cursor:pointer"
                                        :class="v.classes"
                                        v-model="date"
                                        format="dd.MM.yyyy"
                                        id="date-input"
                                        value-format="dd.MM.yyyy"
                                        type="date"
                                        placeholder="Укажите дату рабочего дня"
                                        :picker-options="{firstDayOfWeek: 1}"
                                    ></el-date-picker>
                                    <div class="error-message">@{{ v.errors[0] }}</div>
                                </validation-provider>
                            </div>
                            <div class="col-md-4 col-xl-2">
                                <button type="button"
                                        :disabled="loading"
                                        style="margin:27px 0 0 0"
                                        class="btn btn-primary btn-outline d-none d-md-block w-100"
                                        @click="show">
                                    Показать
                                </button>
                            </div>
                        </div>
                        <div v-if="taskId" class="row justify-content-end">
                            <div class="col-md-4 col-xl-2">
                                <button type="button"
                                        :disabled="loading"
                                        class="btn btn-primary btn-outline d-none d-md-block w-100"
                                        @click="solveWorkingTimeTask">
                                    Закрыть задачу<br>"Контроль<br>рабочего времени"
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
                    Укажите проект и дату
                </div>
            </div>
            <div v-else  class="card">
                <div class="card-body card-body-tech">
                    <div class="row mb-2">
                        <div class="font-weight-bold text-md-right col-12 col-md order-md-1">Суточный табель
                            (<span :class="isWeekendDay(reportPeriodString, 'D MMMM YYYY dd') ? 'weekend-day' : ''">@{{ reportPeriodString }}</span>)
                        </div>
                        <div class="h4-tech fw-500 m-0 col-12 col-md-8 order-md-0" style="margin-top:0">
                            <span>@{{ projectName }}</span>
                        </div>
                    </div>
                    <el-table   border
                                :data="tableData"
                                :cell-class-name="cellClassMethod"
                                :cell-style="cellStyleMethod"
                                :row-class-name="rowClassMethod"
                                :header-cell-class-name="headerCellClassMethod"
                                :max-height="max_table_height"
                                class="ignore-css"
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
                                                    :daily="true"
                                                    :offer-types="tariffs_t3"
                                                    :row="scope.row"
                                                    :handle-error="handleError"
                                                    :timecard-id="timecardData.id"
                                                    :value="scope.row.title"
                                                    :day="$route.query.date"
                                                    :project-id="$route.query.project_id"
                                ></offer-row-title>
                                <div style="min-height: 23px" v-else>
                                    @{{ scope.row.hasOwnProperty('title') ? scope.row.title : '' }}
                                </div>
                            </template>
                        </el-table-column>
                        <el-table-column width="120">
                            <template slot-scope="scope">
                                <input-cell v-if="scope.row.offerRow"
                                            :row="scope.row"
                                            :handle-error="handleError"
                                            :tariff-id="scope.row.tariff_id"
                                            :timecard-id="timecardData.id"
                                            :table-index="scope.$index"
                                            rules="numeric|positive|max_value:30"
                                            :daily="true"
                                            :date="$route.query.date"
                                            :project-id="$route.query.project_id"
                                            maxlength="5"
                                ></input-cell>
                            </template>
                        </el-table-column>
                        <el-table-column
                            v-for="i in usersCount"
                            width="120"
                            :label="worker_names[i-1]"
                        >
                            <template slot-scope="scope">
                                <sum-cell   v-if="scope.row.sumRow"
                                            :row="scope.row"
                                            :day-index="timecard_ids[i-1]"
                                            :abbreviations="abbreviations"
                                            :timecard-day-id="timecardData.workers.filter(el => el.timecard_id === timecard_ids[i-1])[0].id"
                                            :day-tariffs="tableData.filter(el => el.tariffRow).map(el => el.days[timecard_ids[i-1]])"
                                            {{-- :day-amounts="tableData.filter(el => el.tariffRow || el.offerRow).map(el => el.days[i])" --}}
                                            :handle-error="handleError"
                                ></sum-cell>
                                <input-cell v-else-if="!scope.row.objectRow && !scope.row.immutableRow"
                                            :row="scope.row"
                                            :day-index="timecard_ids[i-1]"
                                            :table-index="scope.$index"
                                            :handle-error="handleError"
                                            :timecard-day-id="timecardData.workers.filter(el => el.timecard_id === timecard_ids[i-1])[0].id"
                                            rules="natural|max_value:24"
                                            maxlength="2"
                                ></input-cell>
                                <project-intervals-cell v-else-if="scope.row.objectRow"
                                                :project="scope.row.days[timecard_ids[i-1]]"
                                                :row="scope.row"
                                                :day-index="timecard_ids[i-1]"
                                                :locations="locations"
                                                :hide-tooltips="hideTooltips"
                                                :handle-error="handleError"
                                                :window-width="window_width"
                                                :timecard-day-id="timecardData.workers.filter(el => el.timecard_id === timecard_ids[i-1])[0].id"
                                                :search-locations="searchLocations"
                                ></project-intervals-cell>
                            </template>
                        </el-table-column>
                    </el-table>
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
    @include('human_resources.reports.modules.components.project_intervals_cell')
    @include('human_resources.reports.modules.components.sum_cell')
    <script>
        const vm = new Vue({
            el: '#base',
            router: new VueRouter({
                mode: 'history',
                routes: [],
            }),
            data: {
                displayTable: false, // false
                observer_key: 1,
                location: '',
                date: '',
                foundLocations: [],
                preloadedLocations: [],
                allLocations: {!! json_encode($projects) !!},

                abbreviations: ['Б', 'БИР', 'Д', 'З', 'Н', 'О', 'П', 'У'],

                usersCount: 10,
                // summarized_data: [],
                timecard_ids: [],
                worker_names: [],
                tableData: [],
                timecardData: {!! json_encode($data) !!},

                dealsColors: {
                    'Погружение вибро': '#fb7981',
                    'Погружение вдвоём вибро': '#fdb2b6',
                    'Извлечение вибро': '#fdd9db',
                    'Погружение статика': '#4e9eff',
                    'Извлечение статика': '#99c8fe',
                },

                timecardId: 1,
                taskId: null,
                tariffs: [],
                is_opened: false,
                timecardUser: null,

                loading: false,
                window_width: 10000,
                window_height: 10000,
            },
            computed: {
                max_table_height() {
                    return this.window_width <= 768 ? Math.ceil(this.window_height * 0.75) : 10000;
                },
                locations() {
                    return [ ...this.foundLocations, ...this.preloadedLocations ].filter((el, i, a) => a.map(elem => elem.code).indexOf(el.code) === i);
                },
                reportPeriodString() {
                    let date = this.$route.query.date;
                    if (date) {
                        return moment(date, 'DD.MM.YYYY').locale('ru').format('D MMMM YYYY dd');
                    }
                    return '';
                },
                projectName() {
                    let project_id = this.$route.query.project_id;
                    if (project_id) {
                        return this.allLocations.filter(el => el.code == project_id)[0].name;
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
                const keys = Object.keys(this.$route.query);
                const entries = Object.entries(this.$route.query);
                entries.forEach(entry => {
                    let field = entry[0].split('[')[0];
                    let value = Array.isArray(entry[1]) ? entry[1][0] : entry[1];
                    if (field === 'project_id') {
                        this.promisedSearchLocationsById(value)
                            .then(response => {
                                const responseData = response.data.map(loc => ({ name: loc.label, code: loc.code }));
                                const targetLocation = responseData[responseData.map(elem => elem.code).indexOf(value)];
                                this.preloadedLocations.push(targetLocation);
                                this.location = value;
                            })
                            .catch(error => console.log(error));
                    } else if (field === 'date') {
                        this.date = value;
                    }
                });
                if (keys.indexOf('project_id') === -1) {
                    this.searchLocations('');
                }
                this.searchUsers('');

                if (this.timecardData.workers.length > 0) {
                    this.tariffs = this.timecardData.tariff_manual.map(el => {
                        el.type += 1;
                        return el;
                    });
                    this.timecard_ids = this.timecardData.workers.map(el => el.timecard_id);
                    this.worker_names = this.timecardData.workers.map(el => el.user_name);
                    this.usersCount = this.timecardData.workers.length;
                    this.taskId = this.timecardData.task_id;
                    this.updateTable(this.timecardData.workers);
                    this.displayTable = true;
                    this.loadLocations();
                }
            },
            mounted() {
                $(window).on('resize', this.handleResize);
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
                        if (this.location) {
                            queryObj.project_id = this.location;
                        }
                        if (this.date) {
                            queryObj.date = this.date;
                        }
                        this.$router.replace({query: queryObj}).catch(err => {});

                        this.reset();

                        this.loading = true;
                        axios.post('{{ route('human_resources.timecard_day.get') }}', queryObj)
                            .then(response => {
                                this.timecardData = response.data.data;
                                 if (this.timecardData.workers.length > 0) {
                                    this.tariffs = this.timecardData.tariff_manual.map(el => {
                                        el.type += 1;
                                        return el;
                                    });
                                    this.timecard_ids = this.timecardData.workers.map(el => el.timecard_id);
                                    this.worker_names = this.timecardData.workers.map(el => el.user_name);
                                    this.usersCount = this.timecardData.workers.length;
                                    this.taskId = this.timecardData.task_id;
                                    this.updateTable(this.timecardData.workers);
                                    this.displayTable = true;
                                    this.loadLocations();
                                } else {
                                   this.$message.error({
                                       message: 'Указанный суточный табель пуст.',
                                   });
                                }
                                this.loading = false;
                            })
                            .catch(error => {
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
                isWeekendDay(date, format) {
                    return [5, 6].indexOf(moment(date, format).weekday()) !== -1;
                },
                isValidDate(date, format) {
                    return moment(date, format).isValid();
                },
                weekdayDate(date, inputFormat, outputFormat) {
                    return moment(date, inputFormat).format(outputFormat ? outputFormat : 'DD.MM.YYYY dd');
                },
                solveWorkingTimeTask() {
                    if (this.taskId && this.$route.query.project_id) {
                        this.$confirm('Подтвердите закрытие задачи.', 'Внимание', {
                            confirmButtonText: 'Подтвердить',
                            cancelButtonText: 'Назад',
                            type: 'warning'
                        }).then(() => {
                            const payload = {
                                project_id: this.$route.query.project_id,
                                task_id: this.taskId
                            }
                            this.loading = true;
                            axios.post('{{ route('human_resources.timecard_day.solve_working_time_task') }}', payload)
                                .then(response => {
                                    this.$message.success({
                                        message: 'Задача контроля рабочего времени успешно закрыта.',
                                        showClose: true,
                                    });
                                    this.taskId = null;
                                    this.loading = false;
                                })
                                .catch(error => {
                                    this.handleError(error);
                                    this.loading = false;
                                });
                        }).catch(() => {});
                    }
                },
                updateTable(workers) {
                    this.tableData = this.getNewTable(this.usersCount);
                    let cells = [].concat(...workers.map(el =>
                        el.time_periods.concat(el.working_hours, el.deals).map(elem => {elem.timecard_id = el.timecard_id; elem.timecard_day_id = el.id; return elem;})
                    ));
                    let uniqueOffers = cells.filter(cell => cell.type === 3)
                        .filter((cell, i, a) => {
                            return a.map(el => el.tariff_id + ' ' + el.length).indexOf(cell.tariff_id + ' ' + cell.length) === i;
                        });
                    let offerRows = [];
                    uniqueOffers.forEach(offer => {
                        const offerRow = this.getNewDataRow(offer.tariff_name, offer.tariff_id, this.usersCount);
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
                        .map(cell => cell.timecard_id + ' ' + cell.project_id)
                        .filter((cell, i, a) => a.indexOf(cell) === i)
                        .map(cell => cell.split(' ')[0])
                        .forEach((day, i, a) => objectRowsCount = a.filter(el => el === day).length > objectRowsCount ? a.filter(el => el === day).length : objectRowsCount);
                    for (let i = 0; i < objectRowsCount; i++) {
                        this.tableData.push(this.getNewObjectRow(this.usersCount));
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
                                    const objectRowIndex = cells.filter(el => el.type === 1 && el.timecard_id === cell.timecard_id).map(el => el.project_id).filter((el, i, a) => a.indexOf(el) === i).indexOf(cell.project_id);
                                    this.tableData[objectRowOffset + objectRowIndex].days[cell.timecard_id].location = String(cell.project_id);
                                    if (!(this.tableData[objectRowOffset + objectRowIndex].days[cell.timecard_id].intervals.length === 1
                                        && this.tableData[objectRowOffset + objectRowIndex].days[cell.timecard_id].intervals[0].timeFrom === ''
                                        && this.tableData[objectRowOffset + objectRowIndex].days[cell.timecard_id].intervals[0].timeTo === '')) {
                                        this.tableData[objectRowOffset + objectRowIndex].days[cell.timecard_id].intervals.push({
                                            timeFrom: cell.start.split(':').join('-'),
                                            timeTo: cell.end.split(':').join('-'),
                                            id: cell.id,
                                        })
                                    } else {
                                        this.tableData[objectRowOffset + objectRowIndex].days[cell.timecard_id].intervals[0] = {
                                            timeFrom: cell.start.split(':').join('-'),
                                            timeTo: cell.end.split(':').join('-'),
                                            id: cell.id,
                                        }
                                    }
                                } else {
                                    const sumRowIndex = 0;
                                    this.tableData[sumRowIndex].days[cell.timecard_id] = {id: cell.id, tariff_id: cell.tariff_id, commentary: cell.commentary, amount: cell.amount, timecard_day_id: cell.timecard_day_id};
                                }
                                break;
                            case 2:
                                const tariffRowIndex = this.tableData.map(el => el.tariff_id).indexOf(cell.tariff_id);
                                this.tableData[tariffRowIndex].days[cell.timecard_id] = {id: cell.id, tariff_id: cell.tariff_id, amount: cell.amount, timecard_day_id: cell.timecard_day_id};
                                break;
                            case 3:
                                const offerRowIndex = this.tableData.map(el => el.tariff_id + ' ' + el.materialLength).indexOf(cell.tariff_id + ' ' + cell.length);
                                this.tableData[offerRowIndex].days[cell.timecard_id] = {id: cell.id, tariff_id: cell.tariff_id, amount: cell.amount, length: cell.length, timecard_day_id: cell.timecard_day_id};
                                break;
                        }
                    });
                },
                getNewTable(usersCount) {
                    let newTable = [];
                    let sumRow = this.getNewDataRow('Сумма часов', -1, usersCount);
                    sumRow.sumRow = true;
                    newTable.push(sumRow);
                    newTable.push(this.getNewFullRow('Часы'));
                    this.tariffs_t2.forEach(el => {
                        let tariffRow = this.getNewDataRow(el.name, el.id, usersCount);
                        tariffRow.tariffRow = true;
                        newTable.push(tariffRow);
                    });
                    newTable.push(this.getNewFullRow('Сделки'));
                    return newTable;
                },
                getNewDataRow(title, tariff_id, usersCount) {
                    let newRow = {};
                    newRow.days = {};
                    newRow.title = title;
                    newRow.tariff_id = tariff_id;
                    for (let i = 0; i < usersCount; i++) {
                        newRow.days[this.timecard_ids[i]] = {};
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
                getNewObjectRow(usersCount) {
                    let newRow = {};
                    newRow.title = '';
                    newRow.objectRow = true;
                    newRow.days = {};
                    for (let i = 0; i < usersCount; i++) {
                        newRow.days[this.timecard_ids[i]] = {
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
                    const newOfferRow = this.getNewDataRow(this.tariffs_t3[0].name, this.tariffs_t3[0].id, this.usersCount);
                    newOfferRow.offerRow = true;
                    newOfferRow.materialLength = '';
                    this.tableData.splice(buttonRowIndex, 0, newOfferRow);
                },
                addObjectRow() {
                    const buttonRowIndex = this.tableData.map(el => el.addObjectRow).indexOf(true);
                    this.tableData.splice(buttonRowIndex, 0, this.getNewObjectRow(this.usersCount));
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
                            axios.post('{{ route('projects::get_projects_for_human') }}', {q: query, daily: true})
                                .then(response => resolve(response))
                                .catch(error => reject(error));
                        } else {
                            axios.post('{{ route('projects::get_projects_for_human') }}', {daily: true})
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
                        .filter(el => el)
                        .filter((el, i, a) => a.indexOf(el) === i);
                    locationsIds.forEach(el => {
                        if (this.preloadedLocations.map(elem => elem.code).indexOf(el) === -1) {
                            this.promisedSearchLocationsById(el)
                                .then(response => {
                                    const responseData = response.data.map(loc => ({ name: loc.label, code: loc.code }));
                                    const targetLocation = responseData[responseData.map(elem => elem.code).indexOf(el)];
                                    this.preloadedLocations.push(targetLocation);
                                })
                                .catch(error => console.log(error));
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
            }
        });

        const eventHub = new Vue();
    </script>
@endsection
