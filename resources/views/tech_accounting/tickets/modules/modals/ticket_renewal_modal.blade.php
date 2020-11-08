<div class="modal fade bd-example-modal-lg show" id="ticket-renewal" role="dialog" aria-labelledby="modal-search" style="display: none;">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content pb-3">
            <div class="modal-header">
                <h5 class="modal-title">Запрос о продлении использования</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">×</span>
                </button>
            </div>
            <div class="modal-body pt-0">
                <validation-observer ref="observer" :key="observer_key">
                    <div class="modal-section mt-3">
                        <label for="">Новая дата окончания использования<span class="star">*</span></label>
                        <validation-provider rules="required" v-slot="v"
                                             vid="renewal-date-input"
                                             ref="renewal-date-input">
                            <el-date-picker
                                style="cursor:pointer"
                                :class="v.classes"
                                v-model="date"
                                format="dd.MM.yyyy"
                                id="renewal-date-input"
                                value-format="dd.MM.yyyy"
                                type="date"
                                placeholder="Укажите дату начала приемки"
                                :picker-options="datePickerOptions"
                            ></el-date-picker>
                            <div class="error-message">@{{ v.errors[0] }}</div>
                        </validation-provider>
                    </div>
                    <div class="modal-section mt-3">
                        <label for="">Комментарий<span class="star">*</span></label>
                        <validation-provider rules="required|max:300" vid="renewal-comment-input"
                                             ref="renewal-comment-input" v-slot="v">
                            <el-input
                                :class="v.classes"
                                type="textarea"
                                :rows="4"
                                maxlength="300"
                                id="renewal-comment-input"
                                clearable
                                placeholder="Укажите причину запроса о продлении использования"
                                v-model="comment"
                            ></el-input>
                            <div class="error-message">@{{ v.errors[0] }}</div>
                        </validation-provider>
                    </div>
                    <div class="modal-section text-center mt-30">
                        <button class="btn btn-primary btn-sm" @click.stop="submit">Отправить запрос</button>
                    </div>
                </validation-observer>
            </div>
        </div>
    </div>
</div>

@push('js_footer')
<script>
    var ticketRenewal = new Vue({
        el: '#ticket-renewal',
        data: {
            observer_key: 1,
            comment: '',
            date: '',
            window_width: 10000,
            datePickerOptions: {
                firstDayOfWeek: 1,
                disabledDate: date => date < moment().startOf('date') || (cardTicket.ticket.usage_to_date ? (date < moment(cardTicket.ticket.usage_to_date, "DD.MM.YYYY")) : false),
            },
        },
        methods: {
            onFocus() {
                $('.el-input__inner').blur();
            },
            reset() {
                this.date = '';
                this.comment = '';
                this.observer_key += 1;
            },
            submit() {
                this.$refs.observer.validate().then(success => {
                    if (!success) {
                        const error_field_vid = Object.keys(this.$refs.observer.errors).find(el => this.$refs[el].errors.length > 0);
                        $('#' + error_field_vid).focus();
                        return;
                    }

                    let route = '{{ route('building::tech_acc::our_technic_tickets.request_extension', ["ID_TO_SUBSTITUTE"]) }}';

                    const payload = {
                        usage_to_date: this.date,
                        comment: this.comment,
                    };
                    axios.post(makeUrl(route, [cardTicket.ticket.id]), payload)
                        .then((response) => {
                            const ticketIndex = vm.tickets.findIndex(el => el.id == cardTicket.ticket.id);
                            vm.$set(vm.tickets, ticketIndex, response.data.data);
                            vm.tickets[ticketIndex].is_loaded = true;
                            cardTicket.update(vm.tickets[ticketIndex]);
                            $('#ticket-renewal').modal('hide');
                        })
                        .catch((error) => {
                            console.log(error)
                        })
                });
            }
        }
    });

</script>
@endpush
