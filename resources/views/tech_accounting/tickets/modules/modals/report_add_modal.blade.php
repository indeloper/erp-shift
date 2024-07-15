<div class="modal fade bd-example-modal-lg show" id="report-add" role="dialog" aria-labelledby="modal-search" style="display: none;">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content pb-3">
            <div class="modal-header">
                <h5 class="modal-title">Запись в отчет об использовании</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">×</span>
                </button>
            </div>
            <div class="modal-body pt-0">
                <validation-observer ref="observer" :key="observer_key">
                    <validation-provider rules="required" v-slot="v" vid="report-date"
                                         ref="report-date">
                        <div class="modal-section mt-3">
                            <label for="">Дата<span class="star">*</span></label>
                            <el-date-picker
                                style="cursor:pointer"
                                :class="v.classes"
                                v-model="usage_date"
                                format="dd.MM.yyyy"
                                id="report-date"
                                value-format="yyyy-MM-dd"
                                type="date"
                                placeholder="Укажите дату использования"
                                :picker-options="dateOptions"
                            ></el-date-picker>
                            <div class="error-message">@{{ v.errors[0] }}</div>
                        </div>
                    </validation-provider>
                    <div class="modal-section mt-3">
                        <label for="">Количество часов<span class="star">*</span></label>
                        <validation-provider rules="required" v-slot="v" vid="usage-duration-input"
                                             ref="usage-duration-input">
                            <el-input-number
                                :min="0"
                                :max="24"
                                :maxlength="2"
                                class="d-block w-100"
                                :class="v.classes"
                                v-model="usage_duration"
                                id="usage-duration-input"
                                :precision="0"
                                :step="1"
                                placeholder="Укажите количество часов"
                            ></el-input-number>
                            <div class="error-message">@{{ v.errors[0] }}</div>
                        </validation-provider>
                    </div>
                    <div class="modal-section mt-3">
                        <label for="">Комментарий</label>
                        <validation-provider :rules="commentOptions" v-slot="v" vid="usage-comment-input"
                                             ref="usage-comment-input">
                            <el-input
                                    type="textarea"
                                    rows="4"
                                    maxlength="300"
                                    placeholder="Укажите подробности использования"
                                    id="usage-comment-input"
                                    v-model="comment"
                        ></el-input>
                            <div class="error-message">@{{ v.errors[0] }}</div>
                        </validation-provider>
                </div>
                </validation-observer>

                <div class="modal-section text-center mt-30">
                    <button @click="sendReport" class="btn btn-primary btn-sm">Отправить запрос</button>
                </div>
            </div>
        </div>
    </div>
</div>

@push('js_footer')
<script>
    var reportAdd = new Vue({
        el: '#report-add',
        data: {
            observer_key: 1,
            usage_duration: 1,
            comment: '',
            usage_date: '',
            method: 'post',
            route: '',
            commentOptions: '',
            dateOptions: {
                firstDayOfWeek: 1,
                disabledDate: date => date > moment().startOf('date')
                    || cardTicket.ticket.reports.map(el => el.date).some(el => moment(el, 'YYYY-MM-DD').isSame(date))
                    || (cardTicket.responsibleUserDeactivatedAt ? date > moment(cardTicket.responsibleUserDeactivatedAt.split(' ')[0], 'YYYY-MM-DD') : false)
                    || (cardTicket.responsibleUserCreatedAt ? date < moment(cardTicket.responsibleUserCreatedAt.split(' ')[0], 'YYYY-MM-DD') : false),
            }
        },
        watch: {
            usage_duration: function (val) {
                reportAdd.commentOptions = (val <= 0) ? 'required' : '';
            },
        },
        methods: {
            sendReport() {
                this.$refs.observer.validate().then(success => {
                    if (!success) {
                        const error_field_vid = Object.keys(this.$refs.observer.errors).find(el => this.$refs[el].errors.length > 0);
                        $('#' + error_field_vid).focus();
                        return;
                    }
                    let that = this;
                    this.route = '{{ route('building::tech_acc::our_technic_tickets.report.store', ["ID_TO_SUBSTITUTE"]) }}';

                    if (cardTicket.reports.is_update == true) {
                        this.$set(this, 'route', cardTicket.reports.url_to_update);
                        cardTicket.reports.is_update = false;
                        this.method = 'put';
                    } else {
                        this.route = makeUrl(this.route, [cardTicket.ticket.id]);
                        this.method = 'post';
                    }
                    let options = {
                        method: this.method,
                        url: that.route,
                        data: {comment: that.comment, hours: that.usage_duration, date: that.usage_date},
                    };
                    axios(options)
                        .then((response) => {
                            const ticketIndex = vm.tickets.findIndex(el => el.id == cardTicket.ticket.id);
                            vm.$set(vm.tickets, ticketIndex, response.data.data);
                            vm.tickets[ticketIndex].is_loaded = true;
                            cardTicket.update(vm.tickets[ticketIndex]);
                            $('#report-add').modal('hide');
                            that.reset();
                        })
                        .catch((error) => {
                            console.log(error)
                        })
                });
            },
            reset() {
                this.observer_key += 1;
                this.usage_duration = 1;
                this.comment = '';
                this.usage_date = '';
            }
        }
    });

</script>
@endpush
