<script>
    Vue.component('input-cell', {
        template: `
            <el-tooltip v-model="invalid"
                        manual
                        :content="errorMessage"
                        placement="top"
                        effect="light"
                        :key="tooltipKey"
            >
                <div v-if="!isEditing"
                     @click="startEdit"
                     style="min-height: 23px"
                >
                    @{{ value }}
                </div>
                <div v-else>
                    <el-input v-model="innerValue"
                              ref="material-length-input"
                              :class="{'failed': invalid}"
                              :maxlength="maxlength"
                              @keyup.native.enter="stopEdit"
                              @blur="stopEdit"
                    ></el-input>
                </div>
            </el-tooltip>
        `,
        data: () => ({
            isEditing: false,
            invalid: false,
            errorMessage: '',
            backdoor: 1,
            tooltipKey: 1,
            innerValue: '',
        }),
        props: ['rules', 'maxlength', 'handleError', 'timecardDayId', 'row', 'dayIndex', 'tableIndex', 'timecardId', 'daily', 'projectId', 'date'],
        computed: {
            day() {
                return this.dayIndex ? this.row.days[this.dayIndex] : this.row;
            },
            value() {
                this.backdoor;
                return this.day.materialLength ? this.day.materialLength : this.day.amount;
            },
        },
        methods: {
            startEdit() {
                this.innerValue = this.day.amount ? this.day.amount : (this.day.materialLength ? this.day.materialLength : '');
                this.isEditing = true;
                this.$nextTick(() => this.$refs['material-length-input'].select());
            },
            stopEdit() {
                if (this.dayIndex && this.row.hasOwnProperty('materialLength') && this.row.materialLength === '') {
                    if (this.innerValue) {
                        this.errorMessage = 'Сначала вы должны указать длину материала для этой сделки';
                        this.invalid = true;
                        this.tooltipKey += 1;
                    } else {
                        this.invalid = false;
                        this.tooltipKey += 1;
                        this.isEditing = false;
                    }
                    return;
                }
                if (this.day.hasOwnProperty('materialLength')) {
                    if (this.innerValue) {
                        if (vm.tableData.filter(el => el.tariff_id == this.day.tariff_id && el.materialLength == this.innerValue).length > 0
                        && vm.tableData.filter(el => el.tariff_id == this.day.tariff_id && el.materialLength == this.innerValue)[0] !== this.row) {
                            this.errorMessage = 'В таблице уже присутствует сделка с указанной длиной';
                            this.invalid = true;
                            this.tooltipKey += 1;
                            return;
                        }
                    } else {
                        this.invalid = false;
                        this.tooltipKey += 1;
                        this.isEditing = false;
                    }
                }
                VeeValidate.validate(this.innerValue, this.rules).then(result => {
                    this.invalid = !result.valid;
                    if (result.valid) {
                        this.save();
                        this.isEditing = false;
                        this.tooltipKey += 1;
                    } else {
                        this.errorMessage = result.errors.length > 0 ? result.errors[0] : '';
                        this.tooltipKey += 1;
                    }
                });
            },
            save() {
                if (!this.day.hasOwnProperty('materialLength')) {
                    if (this.row.tariffRow && (this.innerValue != this.value || this.innerValue === '' && String(this.value) === '0')) {
                        const payload = {
                            timecard_day_id: this.timecardDayId,
                        };
                        if (this.innerValue.length > 0 || this.day.hasOwnProperty('id') && String(this.day.id).length > 0) {
                            if (this.innerValue.length > 0 && this.innerValue != 0) {
                                payload.working_hours = [{
                                    id: this.day.id,
                                    tariff_id: this.day.hasOwnProperty('tariff_id') ? this.day.tariff_id : this.row.tariff_id,
                                    amount: this.innerValue,
                                }];
                            } else if (this.day.hasOwnProperty('id') && String(this.day.id).length > 0) {
                                payload.deleted_addition_ids = [this.day.id];
                            }
                            axios.put('{{ route('human_resources.timecard_day.update_working_hours', 'TIMECARD_DAY_ID') }}'.split('TIMECARD_DAY_ID').join(this.timecardDayId), payload)
                                .then(response => {
                                    this.row.days[this.dayIndex].tariff_id = this.row.tariff_id;
                                    this.row.days[this.dayIndex].amount = String(this.innerValue).length > 0 && this.innerValue != 0 ? +this.innerValue : '';
                                    this.row.days[this.dayIndex].timecard_day_id = this.timecardDayId;
                                    if (Array.isArray(response.data.data) && response.data.data[0].id) {
                                        this.row.days[this.dayIndex].id = response.data.data[0].id;
                                    } else {
                                        delete this.row.days[this.dayIndex].id;
                                    }
                                    this.innerValue = '';
                                    if (eventHub) {
                                        eventHub.$emit('recalculate', '');
                                    }
                                    this.backdoor += 1;
                                })
                                .catch(error => {
                                    this.handleError(error);
                                    this.innerValue = '';
                                    if (eventHub) {
                                        eventHub.$emit('recalculate', '');
                                    }
                                    this.backdoor += 1;
                                });
                        }
                    } else if (this.innerValue != this.value || this.innerValue === '' && String(this.value) === '0') {
                        const payload = {
                            timecard_day_id: this.timecardDayId,
                        };
                        if (this.innerValue.length > 0 || this.day.hasOwnProperty('id') && String(this.day.id).length > 0) {
                            if (this.innerValue.length > 0 && this.innerValue != 0) {
                                payload.deals = [{
                                    id: this.day.id,
                                    tariff_id: this.day.hasOwnProperty('tariff_id') ? this.day.tariff_id : this.row.tariff_id,
                                    length: this.day.length ? this.day.length : this.row.materialLength,
                                    amount: this.innerValue,
                                }];
                            } else if (this.day.hasOwnProperty('id') && String(this.day.id).length > 0) {
                                payload.deleted_addition_ids = [this.day.id];
                            }
                            axios.put('{{ route('human_resources.timecard_day.update_deals', 'TIMECARD_DAY_ID') }}'.split('TIMECARD_DAY_ID').join(this.timecardDayId), payload)
                                .then(response => {
                                    this.row.days[this.dayIndex].tariff_id = this.row.tariff_id;
                                    this.row.days[this.dayIndex].amount = String(this.innerValue).length > 0 && this.innerValue != 0 ? +this.innerValue : '';
                                    this.row.days[this.dayIndex].length = this.day.length ? this.day.length : this.row.materialLength;
                                    this.row.days[this.dayIndex].timecard_day_id = this.timecardDayId;
                                    if (Array.isArray(response.data.data) && response.data.data[0].id) {
                                        this.row.days[this.dayIndex].id = response.data.data[0].id;
                                    } else {
                                        delete this.row.days[this.dayIndex].id;
                                    }
                                    this.innerValue = '';
                                    if (eventHub) {
                                        eventHub.$emit('recalculate', '');
                                    }
                                    this.backdoor += 1;
                                })
                                .catch(error => {
                                    this.handleError(error);
                                    this.innerValue = '';
                                    if (eventHub) {
                                        eventHub.$emit('recalculate', '');
                                    }
                                    this.backdoor += 1;
                                });
                        }
                    }
                } else if (this.innerValue.length > 0 && this.innerValue != this.row.materialLength) {
                    if (this.row.materialLength === '') {
                        this.row.materialLength = this.innerValue;
                    } else {
                        const payload = {
                            old_tariff: this.day.hasOwnProperty('tariff_id') ? this.day.tariff_id : this.row.tariff_id,
                            old_length: this.row.materialLength,
                            new_length: this.innerValue,
                        };
                        if (!this.daily) {
                            axios.put('{{ route('human_resources.timecard.update_deals_group', 'TIMECARD_ID') }}'.split('TIMECARD_ID').join(this.timecardId), payload)
                                .then(response => {
                                    this.row.materialLength = this.innerValue;
                                    this.innerValue = '';
                                    this.backdoor += 1;
                                })
                                .catch(error => {
                                    this.handleError(error);
                                    this.innerValue = '';
                                    this.backdoor += 1;
                                });
                        } else {
                            payload.day = this.date;
                            payload.project_id = this.projectId;
                            axios.put('{{ route('human_resources.timecard_day.update_day_deals_group') }}', payload)
                                .then(response => {
                                    this.row.materialLength = this.innerValue;
                                    this.innerValue = '';
                                    this.backdoor += 1;
                                })
                                .catch(error => {
                                    this.handleError(error);
                                    this.innerValue = '';
                                    this.backdoor += 1;
                                });
                        }
                    }
                }
            },
        },
    });
</script>
