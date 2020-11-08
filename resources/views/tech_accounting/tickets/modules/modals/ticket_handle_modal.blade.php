<div class="modal fade bd-example-modal-lg show" id="ticket-handle" role="dialog" aria-labelledby="modal-search" style="display: none;">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content pb-3">
            <div class="modal-header">
                <h5 class="modal-title">@{{isMovingActive ? 'Редактирование' : 'Обработка'}} заявки на перемещение</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">×</span>
                </button>
            </div>
            <div class="modal-body pt-0">
                <validation-observer ref="observer" :key="observer_key">
                    <div class="modal-section mt-3">
                        <label for="">Техническое устройство<span
                                class="star">*</span></label>
                        <validation-provider rules="required" vid="tech-select"
                                             ref="tech-select" v-slot="v">
                            <el-select v-model="tech"
                                       :class="v.classes"
                                       clearable filterable
                                       id="tech-select"
                                       :disabled="task31_done || task32_done"
                                       ref="tech-select-inner"
                                       :remote-method="search_techs"
                                       @clear="search_techs('')"
                                       remote
                                       placeholder="Поиск техники"
                            >
                                <el-option
                                    v-for="item in techs"
                                    :key="item.id"
                                    :label="item.name"
                                    :value="item.id">
                                </el-option>
                            </el-select>
                            <div class="error-message">@{{ v.errors[0] }}</div>
                        </validation-provider>
                    </div>
                    <div class="modal-section mt-3">
                        <label for="">Ответственный за отправку<span
                                class="star">*</span></label>
                        <validation-provider rules="required" vid="responsible-sender-select"
                                             ref="responsible-sender-select" v-slot="v">
                            <el-select v-model="responsible_sender"
                                       :class="v.classes"
                                       clearable filterable
                                       id="responsible-sender-select"
                                       :disabled="task31_done"
                                       :remote-method="search_responsible_senders"
                                       @clear="search_responsible_senders('')"
                                       remote
                                       placeholder="Поиск ответственного"
                            >
                                <el-option
                                    v-for="item in responsible_senders"
                                    :key="item.id"
                                    :label="item.name"
                                    :value="item.id">
                                </el-option>
                            </el-select>
                            <div class="error-message">@{{ v.errors[0] }}</div>
                        </validation-provider>
                    </div>
                    <div class="modal-section mt-3">
                        <label for="">Ответственный за прием<span
                                class="star">*</span></label>
                        <validation-provider rules="required" vid="responsible-receiver-select"
                                             ref="responsible-receiver-select" v-slot="v">
                            <el-select v-model="responsible_receiver"
                                       :class="v.classes"
                                       clearable filterable
                                       :disabled="task32_done"
                                       id="responsible-receiver-select"
                                       :remote-method="search_responsible_receivers"
                                       @clear="search_responsible_receivers('')"
                                       remote
                                       placeholder="Поиск ответственного"
                            >
                                <el-option
                                    v-for="item in responsible_receivers"
                                    :key="item.id"
                                    :label="item.name"
                                    :value="item.id">
                                </el-option>
                            </el-select>
                            <div class="error-message">@{{ v.errors[0] }}</div>
                        </validation-provider>
                    </div>
                    <div class="modal-section mt-3">
                        <label for="">Транспорт<span class="star">*</span></label>
                        <validation-provider rules="required" vid="handle-vehicle-select"
                                             ref=handle-vehicle-select" v-slot="v">
                            <el-select v-model="vehicle" clearable filterable
                                       :class="v.classes"
                                       id="handle-vehicle-select"
                                       :remote-method="search_vehicles"
                                       :loading="loading_vehicles"
                                       @clear="search_vehicles('')"
                                       remote placeholder="Поиск транспорта">
                                <el-option
                                    v-for="item in vehicles"
                                    :key="item.id"
                                    :label="item.name"
                                    :value="item.id">
                                </el-option>
                            </el-select>
                            <div class="error-message">@{{ v.errors[0] }}</div>
                        </validation-provider>
                        <div class="font-13 font-weight-bold" v-if="recommended_vehicles && recommended_vehicles.length > 0" style="margin-top: 5px; line-height: 1.3rem">
                            Рекомендуемый транспорт:
                            <ul>
                                <template v-for="(vehicle, index) in recommended_vehicles">
                                    <li @click="selectRecommendedVehicle(vehicle.id)"><a href="#" class="font-weight-bold">@{{ vehicle.mark }} @{{ vehicle.model }}</a></li>
                                </template>
                            </ul>
                        </div>
                    </div>

                    <div class="modal-section mt-3">
                        <div class="row">
                            <div class="col-md-6">
                                <label for="">Дата начала отправки<span class="star">*</span></label>
                                <validation-provider rules="required" v-slot="v"
                                                     vid="handle-send-date-period-from-input"
                                                     ref="handle-send-date-period-from-input">
                                    <el-date-picker
                                        style="cursor:pointer"
                                        :class="v.classes"
                                        :key="`sf${observer_key}`"
                                        v-model="send_date_period[0]"
                                        format="dd.MM.yyyy"
                                        :disabled="task31_done"
                                        id="handle-send-date-period-from-input"
                                        value-format="dd.MM.yyyy"
                                        type="date"
                                        placeholder="Укажите дату начала отправки"
                                        :picker-options="sendDateFromPickerOptions"
                                    ></el-date-picker>
                                    <div class="error-message">@{{ v.errors[0] }}</div>
                                </validation-provider>
                            </div>
                            <div class="col-md-6">
                                <label for="">Дата окончания отправки<span class="star">*</span></label>
                                <validation-provider rules="required" v-slot="v"
                                                     vid="handle-send-date-period-to-input"
                                                     ref="handle-send-date-period-to-input">
                                    <el-date-picker
                                        style="cursor:pointer"
                                        :class="v.classes"
                                        :key="`st${observer_key}`"
                                        v-model="send_date_period[1]"
                                        format="dd.MM.yyyy"
                                        :disabled="task31_done"
                                        id="handle-send-date-period-to-input"
                                        value-format="dd.MM.yyyy"
                                        type="date"
                                        placeholder="Укажите дату окончания отправки"
                                        :picker-options="sendDateToPickerOptions"
                                    ></el-date-picker>
                                    <div class="error-message">@{{ v.errors[0] }}</div>
                                </validation-provider>
                            </div>
                        </div>
                    </div>
                    <div class="modal-section mt-3">
                        <div class="row">
                            <div class="col-md-6">
                                <label for="">Дата начала получения<span class="star">*</span></label>
                                <validation-provider rules="required" v-slot="v"
                                                     vid="handle-receive-date-period-from-input"
                                                     ref="handle-receive-date-period-from-input">
                                    <el-date-picker
                                        style="cursor:pointer"
                                        :class="v.classes"
                                        :key="`rf${observer_key}`"
                                        v-model="receive_date_period[0]"
                                        format="dd.MM.yyyy"
                                        :disabled="task32_done"
                                        id="handle-receive-date-period-from-input"
                                        value-format="dd.MM.yyyy"
                                        type="date"
                                        placeholder="Укажите дату начала получения"
                                        :picker-options="receiveDateFromPickerOptions"
                                    ></el-date-picker>
                                    <div class="error-message">@{{ v.errors[0] }}</div>
                                </validation-provider>
                            </div>
                            <div class="col-md-6">
                                <label for="">Дата окончания получения<span class="star">*</span></label>
                                <validation-provider rules="required" v-slot="v"
                                                     vid="handle-receive-date-period-to-input"
                                                     ref="handle-receive-date-period-to-input">
                                    <el-date-picker
                                        style="cursor:pointer"
                                        :class="v.classes"
                                        :key="`rt${observer_key}`"
                                        v-model="receive_date_period[1]"
                                        format="dd.MM.yyyy"
                                        :disabled="task32_done"
                                        id="handle-receive-date-period-to-input"
                                        value-format="dd.MM.yyyy"
                                        type="date"
                                        placeholder="Укажите дату окончания получения"
                                        :picker-options="receiveDateToPickerOptions"
                                    ></el-date-picker>
                                    <div class="error-message">@{{ v.errors[0] }}</div>
                                </validation-provider>
                            </div>
                        </div>
                        <div class="font-13 font-weight-bold" v-if="recommended_vehicles && recommended_vehicles.length > 0" style="margin-top: 5px; line-height: 1.3rem">
                            Желаемая дата получения: @{{ recommended_receive_date }}
                        </div>
                    </div>
                    <div class="modal-section text-center mt-30">
                        <button class="btn btn-primary btn-sm" @click.stop="submit">Сохранить изменения</button>
                    </div>
                </validation-observer>
            </div>
        </div>
    </div>
</div>

@push('js_footer')
<script>
    var ticketHandle = new Vue({
        el: '#ticket-handle',
        data: {
            observer_key: 1,
            found_techs: [],
            tech: '',
            found_responsible_senders: [],
            responsible_sender: '',
            found_responsible_receivers: [],
            responsible_receiver: '',
            receive_date_period:['', ''],
            send_date_period: ['', ''],
            found_vehicles: [],
            vehicle: '',
            loading_vehicles: false,

            receiveDateFromPickerOptions: {
                firstDayOfWeek: 1,
                disabledDate: date =>
                    (date < moment().startOf('date')) ||
                    (ticketHandle.receive_date_period[1] ? (date > moment(ticketHandle.receive_date_period[1], "DD.MM.YYYY")) : false) ||
                    (ticketHandle.send_date_period[0] ? (date < moment(ticketHandle.send_date_period[0], "DD.MM.YYYY")) : false) ||
                    (ticketHandle.send_date_period[1] ? (date < moment(ticketHandle.send_date_period[1], "DD.MM.YYYY")) : false),
            },
            receiveDateToPickerOptions: {
                firstDayOfWeek: 1,
                disabledDate: date =>
                    (date < moment().startOf('date')) ||
                    (ticketHandle.receive_date_period[0] ? (date < moment(ticketHandle.receive_date_period[0], "DD.MM.YYYY")) : false) ||
                    (ticketHandle.send_date_period[0] ? (date < moment(ticketHandle.send_date_period[0], "DD.MM.YYYY")) : false) ||
                    (ticketHandle.send_date_period[1] ? (date < moment(ticketHandle.send_date_period[1], "DD.MM.YYYY")) : false),
            },
            sendDateFromPickerOptions: {
                firstDayOfWeek: 1,
                disabledDate: date =>
                    (date < moment().startOf('date')) ||
                    (ticketHandle.send_date_period[1] ? (date > moment(ticketHandle.send_date_period[1], "DD.MM.YYYY")) : false) ||
                    (ticketHandle.receive_date_period[0] ? (date > moment(ticketHandle.receive_date_period[0], "DD.MM.YYYY")) : false) ||
                    (ticketHandle.receive_date_period[1] ? (date > moment(ticketHandle.receive_date_period[1], "DD.MM.YYYY")) : false),
            },
            sendDateToPickerOptions: {
                firstDayOfWeek: 1,
                disabledDate: date =>
                    (date < moment().startOf('date')) ||
                    (ticketHandle.send_date_period[0] ? (date < moment(ticketHandle.send_date_period[0], "DD.MM.YYYY")) : false) ||
                    (ticketHandle.receive_date_period[0] ? (date > moment(ticketHandle.receive_date_period[0], "DD.MM.YYYY")) : false) ||
                    (ticketHandle.receive_date_period[1] ? (date > moment(ticketHandle.receive_date_period[1], "DD.MM.YYYY")) : false),
            }
        },
        computed: {
            recommended_vehicles() {
                return cardTicket.ticket.vehicles;
            },
            old_responsible_receiver() {
                const old_responsible_receiver_index = cardTicket.ticket.users_ordered ?
                    cardTicket.ticket.users_ordered.map(user => user.ticket_responsible.type).indexOf(3) : -1;
                return cardTicket.ticket.users_ordered ?
                    (old_responsible_receiver_index !== -1 ? cardTicket.ticket.users_ordered[old_responsible_receiver_index] : null) : null;
            },
            old_responsible_sender() {
                const old_responsible_sender_index = cardTicket.ticket.users_ordered ?
                    cardTicket.ticket.users_ordered.map(user => user.ticket_responsible.type).indexOf(2) : -1;
                return cardTicket.ticket.users_ordered ?
                    (old_responsible_sender_index !== -1 ? cardTicket.ticket.users_ordered[old_responsible_sender_index] : null) : null;
            },
            old_tech() {
                return cardTicket.ticket.our_technic ? cardTicket.ticket.our_technic : null;
            },
            old_vehicle() {
                return Array.isArray(cardTicket.ticket.vehicles) && cardTicket.ticket.vehicles.length > 0
                ? cardTicket.ticket.vehicles[0] : null;
            },
            recommended_receive_date(){
                return vm.convertDateFormat(cardTicket.ticket.getting_to_date);
            },
            responsible_receivers() {
                const old_responsible_receiver_formatted = this.old_responsible_receiver ? {
                    id: String(this.old_responsible_receiver.id),
                    name: this.old_responsible_receiver.full_name
                } : null;
                return old_responsible_receiver_formatted ?
                [old_responsible_receiver_formatted].concat(this.found_responsible_receivers)
                    .filter((el, i, a) => a.map(user => user.id).indexOf(el.id) === i)
                    : this.found_responsible_receivers;
            },
            responsible_senders() {
                const old_responsible_sender_formatted = this.old_responsible_sender ? {
                    id: String(this.old_responsible_sender.id),
                    name: this.old_responsible_sender.full_name
                } : null;
                return old_responsible_sender_formatted ?
                [old_responsible_sender_formatted].concat(this.found_responsible_senders)
                    .filter((el, i, a) => a.map(user => user.id).indexOf(el.id) === i)
                    : this.found_responsible_senders;
            },
            isMovingActive() {
                return cardTicket.ticket.status === 6;
            },
            task31_done() {
                return this.isMovingActive && cardTicket.ticket.active_tasks.map(task => task.status).indexOf(31) === -1;
            },
            task32_done() {
                return this.isMovingActive && cardTicket.ticket.active_tasks.map(task => task.status).indexOf(32) === -1;
            },
            techs() {
                const old_tech_formatted = this.old_tech ? {
                    start_location: this.old_tech.start_location,
                    name: this.old_tech.category_name + ': ' + this.old_tech.brand + ' ' + this.old_tech.model
                        + (this.old_tech.inventory_number ? (' Инв.№' + this.old_tech.inventory_number) : '')
                        + (this.old_tech.start_location.short_name ? ` (${this.old_tech.start_location.short_name})` : ` (${this.old_tech.start_location.name})`),
                    id: this.old_tech.id
                } : null;
                return old_tech_formatted ?
                [old_tech_formatted].concat(this.found_techs)
                    .filter((el, i, a) => a.map(tech => tech.id).indexOf(el.id) === i)
                    : this.found_techs;
            },
            vehicles() {
                const old_vehicle_formatted = this.old_vehicle ? {
                    name: this.old_vehicle.mark + ' ' + this.old_vehicle.model,
                    id: this.old_vehicle.id
                } : null;
                return old_vehicle_formatted ?
                [old_vehicle_formatted].concat(this.found_vehicles)
                    .filter((el, i, a) => a.map(tech => tech.id).indexOf(el.id) === i)
                    : this.found_vehicles;
            },
        },
        mounted() {
            this.search_vehicles('');
            this.search_responsible_senders('');
            this.search_responsible_receivers('');
            this.search_techs('');
        },
        methods: {
            onFocus() {
                $('.el-input__inner').blur();
            },
            selectRecommendedVehicle(id) {
                this.found_vehicles = this.recommended_vehicles.map(el => {
                    el.name = el.mark + ' ' + el.model;
                    return el;
                });
                this.vehicle = id;
                this.search_vehicles_promised(this.recommended_vehicles.find(el => el.id === id).model)
                    .then((response) => {
                        this.found_vehicles = response.data.data.map(el => ({
                            name: el.mark + ' ' + el.model,
                            id: el.id
                        }));
                        this.found_vehicles.push(...this.recommended_vehicles.filter(el => !this.found_vehicles.some(veh => veh.id == el.id)));
                        this.vehicle = id;
                    })
                    .catch(error => console.log(error));
            },
            updateResponsibleReceiver() {
                this.responsible_receiver = this.old_responsible_receiver ? String(this.old_responsible_receiver.id) : '';
            },
            updateResponsibleSender() {
                this.responsible_sender = this.old_responsible_sender ? String(this.old_responsible_sender.id) : '';
            },
            updateTech() {
                this.tech = this.old_tech ? this.old_tech.id : '';
            },
            updateVehicle() {
                this.vehicle = this.old_vehicle ? this.old_vehicle.id : '';
            },
            updateDates() {
                this.receive_date_period[0] = cardTicket.ticket.getting_from_date ? vm.convertDateFormat(cardTicket.ticket.getting_from_date) : '';
                this.receive_date_period[1] = cardTicket.ticket.getting_to_date ? vm.convertDateFormat(cardTicket.ticket.getting_to_date) : '';
                this.send_date_period[0] = cardTicket.ticket.sending_from_date ? vm.convertDateFormat(cardTicket.ticket.sending_from_date) : '';
                this.send_date_period[1] = cardTicket.ticket.sending_to_date ? vm.convertDateFormat(cardTicket.ticket.sending_to_date) : '';
                this.observer_key += 1;
            },
            search_vehicles_promised() {
                this.loading_vehicles = true;
                return new Promise((resolve, reject) => {
                    axios.get('{{ route('building::vehicles::get_vehicles') }}')
                        .then(response => {
                            this.loading_vehicles = false;
                            resolve(response);
                        })
                        .catch(error => {
                            console.log(error);
                            this.loading_vehicles = false;
                            reject();
                        });
                });
            },
            search_responsible_senders(query) {
                if (query) {
                    axios.get('{{ route('users::get_users_for_tech_tickets') }}', {params: {
                            q: query,
                        }})
                        .then(response => this.found_responsible_senders = response.data.map(el => ({
                            name: el.label,
                            id: el.code
                        })))
                        .catch(error => console.log(error));
                } else {
                    axios.get('{{ route('users::get_users_for_tech_tickets') }}')
                        .then(response => this.found_responsible_senders = response.data.map(el => ({
                            name: el.label,
                            id: el.code
                        })))
                        .catch(error => console.log(error));
                }
            },
            search_responsible_receivers(query) {
                if (query) {
                    axios.get('{{ route('users::get_users_for_tech_tickets') }}', {params: {
                            q: query,
                        }})
                        .then(response => this.found_responsible_receivers = response.data.map(el => ({
                            name: el.label,
                            id: el.code
                        })))
                        .catch(error => console.log(error));
                } else {
                    axios.get('{{ route('users::get_users_for_tech_tickets') }}')
                        .then(response => this.found_responsible_receivers = response.data.map(el => ({
                            name: el.label,
                            id: el.code
                        })))
                        .catch(error => console.log(error));
                }
            },
            search_techs(query) {
                if (query) {
                    axios.get('{{ route('building::tech_acc::get_technics') }}', {params: {
                            q: query,
                            free_only: false,
                            relations: ['start_location'],
                        }})
                        .then(response => this.found_techs = response.data.data.map(el => ({
                            start_location: el.start_location,
                            name: el.category_name + ': ' + el.brand + ' ' + el.model
                                + (el.inventory_number ? (' Инв.№' + el.inventory_number) : '')
                                + (el.start_location.short_name ? ` (${el.start_location.short_name})` : ` (${el.start_location.name})`),
                            id: el.id
                        })))
                        .catch(error => console.log(error));
                } else {
                    axios.get('{{ route('building::tech_acc::get_technics') }}', {params: {
                            free_only: false,
                            relations: ['start_location'],
                        }})
                        .then(response => this.found_techs = response.data.data.map(el => ({
                            start_location: el.start_location,
                            name: el.category_name + ': ' + el.brand + ' ' + el.model
                                + (el.inventory_number ? (' Инв.№' + el.inventory_number) : '')
                                + (el.start_location.short_name ? ` (${el.start_location.short_name})` : ` (${el.start_location.name})`),
                            id: el.id
                        })))
                        .catch(error => console.log(error));
                }
            },
            search_vehicles(query) {
                this.loading_vehicles = true;
                if (query) {
                    axios.get('{{ route('building::vehicles::get_vehicles') }}', {params: {
                            q: query,
                        }})
                        .then(response => {
                            this.found_vehicles = response.data.data.map(el => ({
                                name: el.mark + ' ' + el.model,
                                id: el.id
                            }));
                            this.loading_vehicles = false;
                        })
                        .catch(error => {
                            console.log(error);
                            this.loading_vehicles = false;
                        });
                } else {
                    axios.get('{{ route('building::vehicles::get_vehicles') }}')
                        .then(response => {
                            this.found_vehicles = response.data.data.map(el => ({
                                name: el.mark + ' ' + el.model,
                                id: el.id
                            }));
                            this.loading_vehicles = false;
                        })
                        .catch(error => {
                            console.log(error);
                            this.loading_vehicles = false;
                        });
                }
            },
            reset() {
                this.vehicle = '';
                this.tech = '';
                this.responsible_sender = '';
                this.responsible_receiver = '';
                this.receive_date_period = ['', ''];
                this.send_date_period = ['', ''];
                this.found_vehicles = [];
                this.search_vehicles('');
                this.search_responsible_senders('');
                this.search_techs('');
                this.search_responsible_receivers('');
                this.observer_key += 1;
            },
            submit() {
                this.$refs.observer.validate().then(success => {
                    if (!success) {
                        const error_field_vid = Object.keys(this.$refs.observer.errors).find(el => this.$refs[el].errors.length > 0);
                        $('#' + error_field_vid).focus();
                        return;
                    }
                    const payload = {
                        result: cardTicket.ticket.status === 6 ? 'update' : 'confirm',
                        our_technic_id: this.tech,
                        vehicle_ids: [this.vehicle],//vehicle_id: this.vehicle
                        ticket_resp_user_id: this.responsible_sender,
                        recipient_user_id: this.responsible_receiver,
                        sending_from_date: this.send_date_period[0],
                        sending_to_date: this.send_date_period[1],
                        getting_from_date: this.receive_date_period[0],
                        getting_to_date: this.receive_date_period[1],
                    };
                    axios.put('{{ route('building::tech_acc::our_technic_tickets.update', ['']) }}' + '/' + cardTicket.ticket.id, payload)
                        .then((response) => {
                            const ticketIndex = vm.tickets.findIndex(el => el.id == cardTicket.ticket.id);
                            vm.$set(vm.tickets, ticketIndex, response.data.data);
                            vm.tickets[ticketIndex].is_loaded = true;
                            cardTicket.update(vm.tickets[ticketIndex]);
                            $('#ticket-handle').modal('hide');
                            this.$message.success('Изменения успешно сохранены.');
                            this.reset();
                        })
                        .catch((error) => {
                            console.log(error);
                        })
                });
            }
        }
    });
</script>
@endpush
