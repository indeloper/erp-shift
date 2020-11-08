<div class="modal fade bd-example-modal-lg show" id="usage-end" role="dialog" aria-labelledby="modal-search" style="display: none;">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content pb-3">
            <div class="modal-header">
                <h5 class="modal-title">Завершение использования</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">×</span>
                </button>
            </div>
            <div class="modal-body pt-0">
                <div class="modal-section mt-2">
                    <label for="">Комментарий<span class="star">*</span></label>
                    <validation-provider rules="required|max:300" vid="usage-end-comment-input"
                                         ref="usage-end-comment-input" v-slot="v">
                        <el-input
                            :class="v.classes"
                            type="textarea"
                            :rows="4"
                            maxlength="300"
                            id="usage-end-comment-input"
                            clearable
                            placeholder="Укажите причину завершения операции использования"
                            v-model="comment"
                        ></el-input>
                        <div class="error-message">@{{ v.errors[0] }}</div>
                    </validation-provider>
                </div>
                <div class="modal-section text-center mt-30">
                    <button class="btn btn-danger btn-sm" @click.stop="submit">Завершить использование</button>
                </div>
            </div>
        </div>
    </div>
</div>

@push('js_footer')
<script>
    var usageEnd = new Vue({
        el: '#usage-end',
        data: {
            comment: '',
        },
        methods: {
            submit() {
                this.$refs['usage-end-comment-input'].validate().then(result => {
                    if (result.valid) {
                        let that = this;
                        let route = '{{ route('building::tech_acc::our_technic_tickets.close', ["ID_TO_SUBSTITUTE"]) }}';

                        axios.post(makeUrl(route, [cardTicket.ticket.id]), {comment:this.comment})
                            .then((response) => {
                                const ticketIndex = vm.tickets.findIndex(el => el.id == cardTicket.ticket.id);
                                vm.$set(vm.tickets, ticketIndex, response.data.data);
                                vm.tickets[ticketIndex].is_loaded = true;
                                cardTicket.update(vm.tickets[ticketIndex]);
                                $('#usage-end').modal('hide');
                                formTicket.search_techs('');
                                usageEnd.comment = '';
                            })
                            .catch((error) => {
                                console.log(error)
                            })
                    }
                })
            }
        }
    });
</script>
@endpush
