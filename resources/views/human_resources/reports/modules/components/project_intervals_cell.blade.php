<script>
    Vue.component('project-intervals-cell', {
        template: `
            <el-popover
                trigger="click"
                placement="top"
                v-on:show="update"
                :width="windowWidth > 768 ? 400 : 260"
                v-model="display"
            >
                <div class="row pt-2">
                    <div class="col text-center">
                        <h6>Проект</h6>
                    </div>
                </div>
                <div class="row">
                    <div class="col">
                        <el-select v-model="location"
                                   clearable filterable
                                   :remote-method="searchLocations"
                                   @clear="searchLocations('')"
                                   remote
                                   placeholder="Поиск проекта"
                        >
                            <el-option
                                v-for="(item, index) in locations"
                                :key="index + '-' + item.code"
                                :label="item.name"
                                :value="item.code"
                            ></el-option>
                        </el-select>
                    </div>
                </div>
                <div class="row pt-3">
                    <div class="col text-center">
                        <h6>Интервалы</h6>
                    </div>
                </div>
                <div class="row align-content-end pb-2" v-for="(interval, i) in intervals">
                    <div class="col pr-1">
                        <el-time-picker
                            v-model="interval.timeFrom"
                            placeholder="Начало"
                            format="H:mm"
                            value-format="H-mm"
                        ></el-time-picker>
                    </div>
                    <div class="col px-1">
                        <el-time-picker
                            v-model="interval.timeTo"
                            placeholder="Окончание"
                            format="H:mm"
                            value-format="H-mm"
                        ></el-time-picker>
                    </div>
                    <div class="col-auto align-self-center justify-self-center pl-1" style="-ms-flex-positive: 0; flex-grow: 0;">
                        <el-button type="danger"
                                   @click="removeInterval(i)"
                                   data-balloon-pos="up"
                                   style="margin-bottom: 0 !important;"
                                   size="small"
                                   aria-label="Удалить"
                                   icon="el-icon-delete"
                                   circle
                        ></el-button>
                    </div>
                </div>
                <div class="row">
                    <div class="col">
                        <div class="btn-group w-100" role="group" v-if="windowWidth > 768">
                            <button type="button" @click="addInterval" v-if="intervals.length < 20"
                                    class="btn btn-success btn-outline add-material">
                                <i class="fa fa-plus"></i>
                                Добавить интервал
                            </button>
                            <button type="button" @click="save"
                                    :key="submitButtonKey"
                                    class="btn btn-primary btn-block add-material">
                                Сохранить
                            </button>
                        </div>
                        <div v-else>
                            <button type="button" @click="addInterval" v-if="intervals.length < 20"
                                    class="btn btn-success btn-outline add-material d-block w-100">
                                <i class="fa fa-plus"></i>
                                Добавить интервал
                            </button>
                            <button type="button" @click="save"
                                    :key="submitButtonKey"
                                    class="btn btn-primary btn-block add-material d-block w-100">
                                Сохранить
                            </button>
                        </div>
                    </div>
                </div>
                <div slot="reference">
                    <div style="min-height: 23px">
                        <div v-if="project.location">
                            <div class="row" :style="project.intervals.length > 0 && project.intervals[0].timeFrom ? 'border-bottom: 1px solid lightgrey;' : ''">
                                <div class="col text-truncate">
                                    @{{ getLocationNameById(project.location) }}
                                </div>
                            </div>
                            <div class="row text-left" v-for="interval in project.intervals">
                                <div class="col">
                                    <small style="font-weight: 800">@{{ interval.timeFrom ? (interval.timeFrom.split('-').join(':') + ' -') : '' }} @{{ interval.timeTo ? interval.timeTo.split('-').join(':') : '' }}</small>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </el-popover>
        `,
        props: ['project', 'hideTooltips', 'locations', 'searchLocations', 'timecardDayId', 'handleError', 'row', 'dayIndex', 'windowWidth'],
        data: () => ({
            location: '',
            intervals: [],
            deleted_addition_ids: [],
            loading: false,
            submitButtonKey: 1,
            display: false,
        }),
        methods: {
            removeInterval(i) {
                const intervalId = this.intervals[i].id;
                if (intervalId) {
                    this.deleted_addition_ids.push(intervalId);
                }
                this.intervals.splice(i, 1);
                this.hideTooltips();
            },
            addInterval() {
                this.intervals.push({
                    timeFrom: '',
                    timeTo: '',
                });
            },
            getLocationNameById(id) {
                const index = this.locations.map(el => String(el.code)).indexOf(String(id));
                return index > -1 ? this.locations[index].name : '';
            },
            save() {
                if (this.location) {
                    const payload = {
                        timecard_day_id: this.timecardDayId,
                        periods: [],
                    };
                    if (this.deleted_addition_ids.length > 0) {
                        payload.deleted_addition_ids = this.deleted_addition_ids;
                    }
                    this.intervals.forEach((interval) => {
                        payload.periods.push({
                            id: interval.id,
                            project_id: this.location,
                            start: interval.timeFrom ? interval.timeFrom : undefined,
                            end: interval.timeTo ? interval.timeTo : undefined,
                        });
                    });
                    axios.put('{{ route('human_resources.timecard_day.update_time_periods', 'TIMECARD_DAY_ID') }}'.split('TIMECARD_DAY_ID').join(this.timecardDayId), payload)
                        .then(response => {
                            if (Array.isArray(response.data.data) && response.data.data.length > 0) {
                                response.data.data.forEach(period => {
                                    const index = this.intervals.map(interval => (interval.timeFrom ? interval.timeFrom : 'null')  + ',' + (interval.timeTo ? interval.timeTo : 'null')).indexOf(
                                        (period.start ? period.start : 'null') +
                                        ',' + (period.end ? period.end : 'null')
                                    );
                                    if (index > -1) {
                                        this.intervals[index].id = period.id;
                                    }
                                });
                            }
                            this.row.days[this.dayIndex].location = this.location;
                            this.row.days[this.dayIndex].intervals = this.intervals;
                            this.deleted_addition_ids = [];
                            this.$message.success('Изменения успешно сохранены.');
                            this.display = false;
                        })
                        .catch(error => {
                            this.handleError(error);
                        });
                }
            },
            update() {
                if (!(this.location || this.intervals.length > 0 && (this.intervals[0].timeFrom || this.intervals[0].timeTo) || this.intervals.length > 1)) {
                    this.location = this.project.location;
                    this.intervals = [];
                    this.project.intervals.forEach(el => this.intervals.push({
                        id: el.id,
                        timeFrom: el.timeFrom,
                        timeTo: el.timeTo,
                    }));
                }
            }
        },
    });
</script>
