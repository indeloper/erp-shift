<div class="modal fade bd-example-modal-lg show" id="ticket-reject" role="dialog" aria-labelledby="modal-search" style="display: none;">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content pb-3">
            <div class="modal-header">
                <h5 class="modal-title">Отклонение заявки</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">×</span>
                </button>
            </div>
            <div class="modal-body pt-0">
                <div class="modal-section mt-2">
                    <label for="">Комментарий<span class="star">*</span></label>
                    <validation-provider rules="required|max:300" vid="ticket-reject-comment-input"
                                         ref="ticket-reject-comment-input" v-slot="v">
                        <el-input
                            :class="v.classes"
                            type="textarea"
                            :rows="4"
                            maxlength="300"
                            id="ticket-reject-comment-input"
                            clearable
                            placeholder="Укажите причину отклонения заявки на перемещение"
                            v-model="comment"
                        ></el-input>
                        <div class="error-message">@{{ v.errors[0] }}</div>
                    </validation-provider>
                </div>
                <div class="modal-section text-center mt-30">
                    <button class="btn btn-danger btn-sm" @click.stop="submit">Отклонить заявку</button>
                </div>
            </div>
        </div>
    </div>
</div>

@push('js_footer')
<script>
    var ticketReject = new Vue({
        el: '#ticket-reject',
        data: {
            comment: '',
        },
        methods: {
            submit() {
                this.$refs['ticket-reject-comment-input'].validate().then(result => {
                    if (result.valid) {
                        const payload = cardTicket.ticket.status == 1 ? ({acceptance: 'reject',}) : ({result: 'reject', comment: this.comment});
                        axios.put('{{ route('building::tech_acc::our_technic_tickets.update', ['']) }}' + '/' + cardTicket.ticket.id, payload)
                            .then((response) => {
                                const ticketIndex = vm.tickets.findIndex(el => el.id == cardTicket.ticket.id);
                                vm.$set(vm.tickets, ticketIndex, response.data.data);
                                vm.tickets[ticketIndex].is_loaded = true;
                                cardTicket.update(vm.tickets[ticketIndex]);
                                $('#ticket-reject').modal('hide');
                                formTicket.search_techs('');
                            })
                            .catch((error) => { console.log(error) })
                    }
                })
            }
        }
    });
</script>
@endpush
