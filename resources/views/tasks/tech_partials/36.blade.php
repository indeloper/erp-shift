<div class="card-body task-body">
    <div class="row">
        <div id="main_descr" class="col-md-12">
            <h6 style="margin-top:0">
                Описание
            </h6>
            <style>
                .dull-row::after {
                    content: "Сохраненные отметки";
                    text-align: center;
                    line-height: 5rem;
                    vertical-align: middle;
                    font-size: 24pt;
                    position: absolute;
                    top: 0;
                    left: 0;
                    z-index: 1;
                    width: 100%;
                    height: 100%;
                    background: white;
                    opacity: .5;
                    box-shadow: 0 0 10px 15px white;
                }
                @media (max-width: 1650px) {
                    .dull-row::after {
                        text-align: right;
                        font-size: 18pt;
                    }
                }
                @media (max-width: 1200px) {
                    .dull-row::after {
                        font-size: 16pt;
                    }
                }

                .dull-row-animation {
                    -webkit-animation: 0.8s linear fade_in_title;
                    -moz-animation: 0.8s linear fade_in_title;
                    -o-animation: 0.8s linear fade_in_title;
                    animation: 0.8s linear fade_in_title;
                }

                @-webkit-keyframes fade_in_title { from { color: white; } to { color: inherit; } }
                @-moz-keyframes fade_in_title { from { color: white; } to { color: inherit; } }
                @-o-keyframes fade_in_title { from { color: white; } to { color: inherit; } }
                @keyframes fade_in_title { from { color: white; } to { color: inherit; } }
            </style>
            <div id="description">
                <p>Необходимо отметить использование</p>
            </div>
            <div id="report-add" class="modal-body" v-cloak>
                @if(! $task->is_solved)
                    @if(Auth::id() == $task->responsible_user_id)
                        <template v-for="(ticket, i) in tickets">
                            @include('tasks.modules.tech_usage_report_row', ['btn_text' => 'Сохранить', 'btn_type' => 'primary', 'btn_action' => 'store'])
                        </template>
                        <div style="position:relative;" :class="reported_tickets.length === 0 ? 'dull-section' : 'dull-section dull-row-animation'" v-show="reported_tickets.length > 0">
                            <div class="dull-row tech-row"></div>
                            <template v-for="(ticket, i) in reported_tickets">
                                @include('tasks.modules.tech_usage_report_row', ['btn_text' => 'Обновить', 'btn_type' => 'warning', 'btn_action' => 'update'])
                            </template>
                        </div>
                    @endif
                    {{--@foreach($tickets as $ticket)
                        <form action="{{ route('building::tech_acc::our_technic_tickets.report.store', $ticket->id) }}">
                            <div> {{ $ticket->our_technic->name }}</div>
                            <input type="number" name="usage_duration">
                            <input type="text" name="comment">
                            <button>Отправить запрос</button>
                        </form>
                    @endforeach--}}
                @elseif($task->is_solved)
                    <div class="row">
                        <div class="col-sm-12">
                            <p>
                                {{ $task->get_result }}
                            </p>
                        </div>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>
<div class="card-footer">
    <div class="row" style="margin-top:25px">
        <div class="col-md-3 btn-center">
            <a href="{{ route('tasks::index') }}" class="btn btn-wd">Назад</a>
        </div>
        {{--<div class="col-md-9 text-right btn-center" id="bottom_buttons" v-cloak>
            @if(Auth::id() == $task->responsible_user_id and ! $task->is_solved)
                <el-button type="primary" @click.stop="submit" :loading="loadings[i]">Отправить</el-button>
            @endif
        </div>--}}
    </div>
</div>
@if(! $task->is_solved and Auth::id() == $task->responsible_user_id)
    @push('js_footer')
        <script>
            var reportAdd = new Vue({
                el: '#report-add',
                data: {
                    tickets: {!! json_encode($tickets) !!},
                    reported_tickets: {!! json_encode($reported_tickets) !!},
                    usage_date: {!! json_encode($task->created_at) !!},
                    usage_durations: {},
                    comments: {},
                    displays: {},
                    loadings: {},
                    method: 'post',
                    route: '{{ route('building::tech_acc::our_technic_tickets.report.store', ["ID_TO_SUBSTITUTE"]) }}',
                    update_route: '{{ route('building::tech_acc::our_technic_tickets.report.update', ["ID_TO_SUBSTITUTE", "ID_TO_SUBSTITUTE"]) }}',
                },
                created() {
                    this.usage_durations = this.tickets.map(el => 1);
                    for (let i in this.tickets) {
                        if (!Number.isNaN(+i)) {
                            const ticketId = this.tickets[i].id;
                            this.usage_durations[ticketId] = 1;
                            this.displays[ticketId] = true;
                            this.loadings[ticketId] = false;
                            this.comments[ticketId] = '';
                        }
                    }
                    for (let i in this.reported_tickets) {
                        if (!Number.isNaN(+i)) {
                            const ticket = this.reported_tickets[i];
                            if (!this.usage_durations.hasOwnProperty(ticket.id)) {
                                this.usage_durations[ticket.id] = ticket.reports[0].hours;
                                this.displays[ticket.id] = true;
                                this.loadings[ticket.id] = false;
                                this.comments[ticket.id] = ticket.reports[0].comment;
                            }
                        }
                    }
                },
                mounted() {
                    this.applyEventHandlers();
                },
                methods: {
                    submit(id, action) {
                        const key = 'observer-' + id;
                        const observer = Array.isArray(this.$refs[key]) ? this.$refs[key][0] : this.$refs[key];
                        observer.validate().then(success => {
                            if (!success) {
                                const error_field_vid = Object.keys(observer.errors).find(el => Array.isArray(this.$refs[el])
                                    ? this.$refs[el][0].errors.length > 0 : this.$refs[el].errors.length > 0);
                                $('#' + error_field_vid).focus();
                                return;
                            }

                            let options = this.createPayloadFor(id, action);
                            this.$set(this.loadings, id, true);
                            this.$forceUpdate();

                            axios(options)
                                .then((response) => {
                                    this.$set(this.loadings, id, false);
                                    this.$forceUpdate();
                                    if (action === 'store') {
                                        this.$set(this.displays, id, false);
                                        this.$forceUpdate();
                                        const ticketIndex = this.tickets.map(el => el.id).indexOf(id);
                                        if (ticketIndex !== -1) {
                                            setTimeout(() => {
                                                this.tickets.splice(ticketIndex, 1);
                                                this.$set(this.displays, id, true);
                                                this.$message({
                                                    message: 'Отметка об использовании техники успешно сохранена',
                                                    type: 'success'
                                                });
                                                this.$forceUpdate();
                                            }, 800);
                                        }
                                        this.reported_tickets.unshift(response.data.data);
                                        if (this.reported_tickets.length === 1) {
                                            this.$nextTick(() => {
                                                this.applyEventHandlers();
                                                this.$forceUpdate();
                                            });
                                        }
                                    } else {
                                        this.$message({
                                            message: 'Отметка об использовании техники успешно обновлена',
                                            type: 'success'
                                        });
                                    }
                                })
                                .catch((error) => {
                                    console.log(error);
                                    this.$set(this.loadings, id, false);
                                    this.$forceUpdate();
                                })
                        });
                    },

                    createPayloadFor(id, action) {
                        if (action === 'store') {
                            return {
                                method: this.method,
                                url: makeUrl(this.route, [id]),
                                data: {comment: this.comments[id], hours: this.usage_durations[id], date: moment(this.usage_date, 'DD.MM.YYYY HH:mm:ss').format('YYYY-MM-DD')},
                            };
                        } else {
                            return {
                                method: 'put',
                                url: makeUrl(this.update_route, [id, this.reported_tickets[this.reported_tickets.map(el => el.id).indexOf(id)].reports[0].id]),
                                data: {comment: this.comments[id], hours: this.usage_durations[id], date: moment(this.usage_date, 'DD.MM.YYYY HH:mm:ss').format('YYYY-MM-DD')},
                            };
                        }
                    },

                    applyEventHandlers() {
                        $('.dull-section').mouseenter(() => {
                            $('.dull-row').removeClass('dull-row');
                        });
                        $('.dull-section').mouseleave(() => {
                            $('.tech-row').addClass('dull-row')
                        });
                    }
                }
            });

        </script>
    @endpush
@endif
