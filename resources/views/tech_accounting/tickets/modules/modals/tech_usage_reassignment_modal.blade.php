<div class="modal fade bd-example-modal-lg show" id="tech-usage-reassignment" role="dialog" aria-labelledby="modal-search" style="display: none;">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content pb-3">
            <div class="modal-header">
                <h5 class="modal-title">Передача контроля за использование</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">×</span>
                </button>
            </div>
            <div class="modal-body pt-0">
                <validation-observer ref="observer" :key="observer_key">
                    <div class="modal-section mt-3">
                        <label for="">Исполнитель<span class="star">*</span></label>
                        <validation-provider rules="required" vid="usage-select"
                                             ref="usage-select" name="исполнитель" v-slot="v">
                            <el-select v-model="user"
                                       id="usage-select"
                                       :class="v.classes"
                                       clearable filterable
                                       :remote-method="search_responsible_users"
                                       @clear="search_responsible_users('')"
                                       remote
                                       placeholder="Поиск исполнителя"
                            >
                                <el-option
                                    v-for="item in users"
                                    :key="item.id"
                                    :label="item.name"
                                    :value="item.id">
                                </el-option>
                            </el-select>
                            <div class="error-message">@{{ v.errors[0] }}</div>
                        </validation-provider>
                    </div>
                    <div class="modal-section text-center mt-30">
                        <el-button :class="{'btn btn-success btn-sm' : window_width > 769, 'btn btn-success w-100' : window_width <= 769}" @click.stop="submit" :loading="loading">Подтвердить передачу</el-button>
                    </div>
                </validation-observer>
            </div>
        </div>
    </div>
</div>
@push('js_footer')
    <script>
        var techUsageConfirm = new Vue({
            el: '#tech-usage-reassignment',
            data: {
                observer_key: 1,
                users: [],
                user: '',
                window_width: 10000,
                ticket: {},
                loading: false
            },
            mounted() {
                $(window).on('resize', this.handleResize);
                this.handleResize();
                this.search_responsible_users('');
            },
            methods: {
                handleResize() {
                    this.window_width = $(window).width();
                },
                reset() {
                    this.observer_key += 1;
                    this.user = '';
                    this.search_responsible_users('');
                },
                submit() {
                    this.loading = true;
                    this.$refs.observer.validate().then(success => {
                        if (!success) { this.loading = false; return; }
                        const payload = {
                            result: 'usage',
                            user: this.user,
                            task_status: 36,
                        };
                        axios.post('{{ route('building::tech_acc::our_technic_tickets.reassignment', ['']) }}' + '/' + this.ticket.id, payload)
                            .then((response) => {
                                this.loading = false;
                                location.reload();
                            })
                            .catch((error) => {
                                location.reload();
                            })
                    });
                },
                updateTicket(ticket) {
                    this.ticket = ticket;
                },
                search_responsible_users(query, without = '{{ auth()->id() }}') {
                    if (query) {
                        axios.get('{{ route('users::get_users_for_tech_tickets') }}', {params: {
                                q: query,
                                without: without
                            }})
                            .then(response => this.users = response.data.map(el => ({
                                name: el.label,
                                id: el.code
                            })))
                            .catch(error => console.log(error));
                    } else {
                        axios.get('{{ route('users::get_users_for_tech_tickets') }}', {params: {
                                without: without
                            }})
                            .then(response => this.users = response.data.map(el => ({
                                name: el.label,
                                id: el.code
                            })))
                            .catch(error => console.log(error));
                    }
                },
            }
        });
    </script>
@endpush
