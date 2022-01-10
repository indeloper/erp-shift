<script>
    Vue.component('offer-row-title', {
        template: `
            <div class="row" v-if="!isEditing">
                <div class="col offer-row-title-content" @click="startEdit"
                     style="min-height: 23px"
                >
                    @{{ value }}
                </div>
                <div class="col-auto offer-row-title-content" @click="remove">
                    <i class="el-icon-delete"></i>
                </div>
            </div>
            <div v-else>
                <el-select v-model="innerValue"
                           ref="offer-select"
                           @blur="save"
                           @change="save"
                >
                    <el-option
                        v-for="item in offerTypes"
                        :label="item.name"
                        :value="item.name"
                    ></el-option>
                </el-select>
            </div>
        `,
        data: () => ({
            BLUR_TIMEOUT: 200,
            isEditing: false,
            innerValue: '',
        }),
        props: ['offerTypes', 'value', 'row', 'timecardId', 'handleError', 'daily', 'day', 'projectId'],
        computed: {
            innerId() {
                return this.offerTypes.filter(el => el.name === this.innerValue)[0].id;
            }
        },
        methods: {
            startEdit() {
                this.innerValue = this.value;
                this.isEditing = true;
                this.$nextTick(() => this.$refs['offer-select'].$el.click());
            },
            stopEdit() {
                setTimeout(() => {
                    this.isEditing = false;
                }, this.BLUR_TIMEOUT);
            },
            save() {
                if (this.row.materialLength && this.innerValue !== this.value && Object.values(this.row.days).filter(el => el.amount).length > 0) {
                    const payload = {
                        old_tariff: this.row.tariff_id,
                        new_tariff: this.innerId,
                        old_length: this.row.materialLength,
                        new_length: null
                    }
                    if (!this.daily) {
                        axios.put('{{ route('human_resources.timecard.update_deals_group', 'TIMECARD_ID') }}'.split('TIMECARD_ID').join(this.timecardId), payload)
                            .then(response => {
                                this.row.title = this.innerValue;
                                this.row.tariff_id = this.innerId;
                                this.stopEdit();
                            })
                            .catch(error => {
                                this.handleError(error);
                                this.stopEdit();
                            });
                    } else {
                        payload.day = this.day;
                        payload.project_id = this.projectId;
                        axios.put('{{ route('human_resources.timecard_day.update_day_deals_group') }}', payload)
                            .then(response => {
                                this.row.title = this.innerValue;
                                this.row.tariff_id = this.innerId;
                                this.stopEdit();
                            })
                            .catch(error => {
                                this.handleError(error);
                                this.stopEdit();
                            });
                    }
                } else if (this.innerValue !== this.value) {
                    this.row.title = this.innerValue;
                    this.row.tariff_id = this.innerId;
                    this.stopEdit();
                } else {
                    this.stopEdit();
                }
            },
            remove() {
                if (this.row.materialLength && Object.values(this.row.days).filter(el => el.amount).length > 0) {
                    this.$confirm('Подтвердите удаление сделки.', 'Внимание', {
                        confirmButtonText: 'Подтвердить',
                        cancelButtonText: 'Назад',
                        type: 'warning'
                    }).then(() => {
                        const payload = {
                            tariff_id: this.row.tariff_id,
                            length: this.row.materialLength,
                        }
                        if (!this.daily) {
                            payload.timecard_id = this.timecardId;
                            axios.delete('{{ route('human_resources.timecard.destroy_deals_group') }}', { params: payload })
                                .then(response => {
                                    vm.tableData.splice(vm.tableData.indexOf(this.row), 1);
                                })
                                .catch(error => {
                                    this.handleError(error);
                                });
                        } else {
                            payload.project_id = this.projectId;
                            payload.day = this.day;
                            axios.delete('{{ route('human_resources.timecard_day.destroy_day_deals_group') }}', { params: payload })
                                .then(response => {
                                    vm.tableData.splice(vm.tableData.indexOf(this.row), 1);
                                })
                                .catch(error => {
                                    this.handleError(error);
                                });
                        }
                    }).catch(() => {});
                } else {
                    vm.tableData.splice(vm.tableData.indexOf(this.row), 1);
                }
            }
        },
    });
</script>
