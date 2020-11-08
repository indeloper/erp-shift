<div class="modal fade bd-example-modal-lg show" id="form_ticket" role="dialog" aria-labelledby="modal-search" style="display: none;">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Создание заявки на технику</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">×</span>
                </button>
            </div>
            <div class="modal-body pt-0">
                <div class="card border-0 m-0">
                    <div class="card-body">
                        <validation-observer ref="observer" :key="observer_key">
                            <form class="form-horizontal">
                                <h6 class="decor-h6-modal">Цель заявки <span class="star">*</span></h6>
                                <div class="row">
                                    <div class="col-md-12">
                                        <validation-provider rules="required" vid="type-cb-group"
                                                             ref="type-cb-group" v-slot="v">
                                            <el-checkbox-group v-model="type"
                                                               :min="1"
                                                               :max="2"
                                                               id="type-cb-group"
                                                               :class="window_width <= 769 ? v.classes + ' mobile-cb-group' : v.classes"
                                            >
                                                <el-checkbox :class="v.classes" label="Перемещение" border></el-checkbox>
                                                <el-checkbox :class="v.classes" label="Использование" border></el-checkbox>
                                            </el-checkbox-group>
                                            <div class="error-message">@{{ v.errors[0] }}</div>
                                        </validation-provider>
                                    </div>
                                </div>
                                <h6 class="decor-h6-modal">Основная информация</h6>
                                <div class="row">
                                    <div class="col-md-12">
                                        <label for="">Направление<span class="star">*</span></label>
                                        <validation-provider rules="required" vid="specialization-select"
                                                             ref="specialization-select" v-slot="v">
                                            <el-select v-model="specialization"
                                                       :class="v.classes"
                                                       clearable
                                                       id="specialization-select"
                                                       placeholder="Выберите направление"
                                            >
                                                <el-option
                                                    v-for="item in Object.entries(specializations)"
                                                    :value="item[0]"
                                                    :label="item[1]"
                                                >
                                                </el-option>
                                            </el-select>
                                            <div class="error-message">@{{ v.errors[0] }}</div>
                                        </validation-provider>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-md-12">
                                        <label for="">Ответственный РП<span class="star">*</span></label>
                                        <validation-provider rules="required" vid="responsible-rp-select"
                                                             ref="responsible-rp-select" v-slot="v">
                                            <el-select v-model="responsible_rp"
                                                       :class="v.classes"
                                                       clearable filterable
                                                       id="responsible-rp-select"
                                                       :remote-method="search_responsible_rps"
                                                       @clear="search_responsible_rps('')"
                                                       remote
                                                       placeholder="Поиск РП"
                                            >
                                                <el-option
                                                    v-for="item in responsible_rps"
                                                    :key="item.id"
                                                    :label="item.name"
                                                    :value="item.id">
                                                </el-option>
                                            </el-select>
                                            <div class="error-message">@{{ v.errors[0] }}</div>
                                        </validation-provider>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-md-12">
                                        <label for="">Техническое устройство<span class="star">*</span></label>
                                        <validation-provider rules="required" vid="tech-select"
                                                             ref="tech-select" v-slot="v">
                                            <el-select v-model="tech"
                                                       :class="v.classes"
                                                       clearable filterable
                                                       :disabled="disabledTech"
                                                       id="tech-select"
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
                                </div>
                                <template v-if="type.includes('Перемещение')">
                                    <h6 class="decor-h6-modal">Прием техники</h6>
                                    <div class="row">
                                        <div class="col-md-12">
                                            <label for="">Желаемая дата получения<span class="star">*</span></label>
                                            <validation-provider rules="required" v-slot="v"
                                                                 vid="desired-receive-date-input"
                                                                 ref="desired-receive-date-input">
                                                <el-date-picker
                                                    style="cursor:pointer"
                                                    :class="v.classes"
                                                    v-model="desired_receive_date"
                                                    format="dd.MM.yyyy"
                                                    id="desired-receive-date-input"
                                                    value-format="dd.MM.yyyy"
                                                    type="date"
                                                    placeholder="Укажите желаемую дату получения"
                                                    :picker-options="desiredReceiveDatePickerOptions"
                                                ></el-date-picker>
                                                <div class="error-message">@{{ v.errors[0] }}</div>
                                            </validation-provider>
                                        </div>
                                    </div>
                                    <div class="row">
                                        <div class="col-md-12">
                                            <label for="">Ответственный за прием<span
                                                    class="star">*</span></label>
                                            <validation-provider rules="required" vid="responsible-receiver-select"
                                                                 ref="responsible-receiver-select" v-slot="v">
                                                <el-select v-model="responsible_receiver"
                                                           :class="v.classes"
                                                           clearable filterable
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
                                    </div>
                                    <div class="row">
                                        <div class="col-md-12">
                                            <label for="">Ответственный за назначение транспорта<span
                                                    class="star">*</span></label>
                                            <validation-provider rules="required" vid="responsible-dispatcher-select"
                                                                 ref="responsible-dispatcher-select" v-slot="v">
                                                <el-select v-model="responsible_dispatcher"
                                                           :class="v.classes"
                                                           clearable filterable
                                                           id="responsible-dispatcher-select"
                                                           :remote-method="search_responsible_dispatchers"
                                                           @clear="search_responsible_dispatchers('')"
                                                           remote
                                                           placeholder="Поиск ответственного"
                                                >
                                                    <el-option
                                                        v-for="item in responsible_dispatchers"
                                                        :key="item.id"
                                                        :label="item.name"
                                                        :value="item.id">
                                                    </el-option>
                                                </el-select>
                                                <div class="error-message">@{{ v.errors[0] }}</div>
                                            </validation-provider>
                                        </div>
                                    </div>
                                    <div class="row">
                                        <div class="col-md-12">
                                            <label for="">Место приема<span class="star">*</span></label>
                                            <validation-provider rules="required" vid="receive-location-id-select"
                                                                 ref="receive-location-id-select" v-slot="v">
                                                <el-select v-model="receive_location"
                                                           :class="v.classes"
                                                           clearable filterable
                                                           id="receive-location-id-select"
                                                           :remote-method="search_receive_locations"
                                                           @clear="search_receive_locations('')"
                                                           remote
                                                           placeholder="Поиск объекта"
                                                >
                                                    <el-option
                                                        v-for="item in receive_locations"
                                                        :key="item.id"
                                                        :label="item.name"
                                                        :value="item.id">
                                                    </el-option>
                                                </el-select>
                                                <div class="error-message">@{{ v.errors[0] }}</div>
                                            </validation-provider>
                                        </div>
                                    </div>
                                    <h6 class="decor-h6-modal">Рекомендуемый транспорт</h6>
                                    <div class="row transports mt-10 flex-md-nowrap align-content-center"
                                         v-for="(vehicle, i) in vehicles">
                                        <div :class="[vehicles.length > 1 ? 'col-10 col-md-11' : 'col-12']">
                                            <label class="mt-10__mobile" for="">
                                                Транспорт @{{i+1}}
                                            </label>
                                            <el-select v-model="vehicles[i]" clearable filterable
                                                       :remote-method="search_vehicles"
                                                       remote name="transport_id" placeholder="Поиск транспорта">
                                                <el-option
                                                    v-for="item in recommended_vehicles_filtered"
                                                    :key="item.id"
                                                    :label="item.name"
                                                    :value="item.id"
                                                ></el-option>
                                            </el-select>
                                        </div>
                                        <div class="col-2 col-md-1 text-center" v-if="vehicles.length > 1">
                                            <button rel="tooltip" type="button" @click="remove_transport(i)"
                                                    :class="window_width > 769 ? 'btn-remove-mobile' : 'btn-remove-mobile btn-remove-vehicle-mobile'"
                                                    style="margin-left: -2rem"
                                                    data-original-title="Удалить">
                                                <i class="fa fa-times remove-stroke pt-2"></i>
                                            </button>
                                        </div>
                                    </div>
                                    <div class="row">
                                        <div class="col-md-12 text-right" style="margin-top:25px">
                                            <button type="button" @click="add_transport"
                                                    v-if="vehicles.length < 5 && vehicles.length < recommended_vehicles.length && recommended_vehicles_filtered.length > 0"
                                                    class="btn btn-round btn-sm btn-success btn-outline add-material">
                                                <i class="fa fa-plus"></i>
                                                Добавить транспорт
                                            </button>
                                        </div>
                                    </div>
                                </template>
                                <template v-if="type.includes('Использование')">
                                    <h6 class="decor-h6-modal">Использование техники</h6>
                                    <div class="row">
                                        <div class="col-md-6">
                                            <label for="">Дата начала использования<span class="star">*</span></label>
                                            <validation-provider rules="required" v-slot="v" vid="usage-date-period-from-input"
                                                                 ref="usage-date-period-from-input">
                                                <el-date-picker
                                                    style="cursor:pointer"
                                                    :class="v.classes"
                                                    v-model="usage_date_period[0]"
                                                    format="dd.MM.yyyy"
                                                    id="usage-date-period-from-input"
                                                    value-format="dd.MM.yyyy"
                                                    type="date"
                                                    placeholder="Укажите дату начала использования"
                                                    :picker-options="usageDateFromPickerOptions"
                                                ></el-date-picker>
                                                <div class="error-message">@{{ v.errors[0] }}</div>
                                            </validation-provider>
                                        </div>
                                        <div class="col-md-6">
                                            <label for="">Дата окончания использования<span class="star">*</span></label>
                                            <validation-provider rules="required" v-slot="v" vid="usage-date-period-to-input"
                                                                 ref="usage-date-period-to-input">
                                                <el-date-picker
                                                    style="cursor:pointer"
                                                    :class="v.classes"
                                                    v-model="usage_date_period[1]"
                                                    format="dd.MM.yyyy"
                                                    id="usage-date-period-to-input"
                                                    value-format="dd.MM.yyyy"
                                                    type="date"
                                                    placeholder="Укажите дату окончания использования"
                                                    :picker-options="usageDateToPickerOptions"
                                                ></el-date-picker>
                                                <div class="error-message">@{{ v.errors[0] }}</div>
                                            </validation-provider>
                                        </div>
                                    </div>
                                    <div class="row">
                                        <div class="col-md-12">
                                            <label for="">Ответственный за использование<span class="star">*</span></label>
                                            <validation-provider rules="required" vid="responsible-user-select"
                                                                 ref="responsible-user-select" v-slot="v">
                                                <el-select v-model="responsible_user"
                                                           :class="v.classes"
                                                           clearable filterable
                                                           id="responsible-user-select"
                                                           :remote-method="search_responsible_users"
                                                           @clear="search_responsible_users('')"
                                                           remote
                                                           placeholder="Поиск ответственного"
                                                >
                                                    <el-option
                                                        v-for="item in responsible_users"
                                                        :key="item.id"
                                                        :label="item.name"
                                                        :value="item.id">
                                                    </el-option>
                                                </el-select>
                                                <div class="error-message">@{{ v.errors[0] }}</div>
                                            </validation-provider>
                                        </div>
                                    </div>
                                    <div class="row">
                                        <div class="col-md-12">
                                            <label for="">Место использования<span class="star">*</span></label>
                                            <validation-provider rules="required" vid="use-location-id-select"
                                                                 ref="use-location-id-select" v-slot="v">
                                                <el-select v-model="receive_location"
                                                           :class="v.classes"
                                                           clearable filterable
                                                           id="use-location-id-select"
                                                           ref="use-location-id-select-inner"
                                                           :remote-method="search_receive_locations"
                                                           @clear="search_receive_locations('')"
                                                           remote
                                                           placeholder="Поиск объекта"
                                                >
                                                    <el-option
                                                        v-for="item in receive_locations"
                                                        :key="item.id"
                                                        :label="item.name"
                                                        :value="item.id">
                                                    </el-option>
                                                </el-select>
                                                <div class="error-message">@{{ v.errors[0] }}</div>
                                            </validation-provider>
                                        </div>
                                    </div>
                                </template>
                                <div class="row" style="margin-bottom:10px">
                                    <div class="col-md-12">
                                        <label for="">
                                            Комментарий
                                        </label>
                                        <el-input
                                            type="textarea"
                                            :rows="4"
                                            maxlength="300"
                                            placeholder="Напишите комментарий"
                                            v-model="comment"
                                        ></el-input>
                                    </div>
                                </div>
                            </form>
                        </validation-observer>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <template v-if="window_width > 769">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Закрыть</button>
                    <button @click.stop="reset" type="button" class="btn btn-warning">Сброс</button>
                    <button @click.stop="submit" type="button" class="btn btn-info">Сохранить</button>
                </template>
                <div v-else class="col-md-12">
                    <div class="row justify-content-center mb-2">
                        <button type="button" class="btn btn-secondary w-100" data-dismiss="modal">Закрыть</button>
                    </div>
                    <div class="row justify-content-center mb-2">
                        <button @click.stop="reset" type="button" class="btn btn-warning w-100">Сброс</button>
                    </div>
                    <div class="row justify-content-center mb-2">
                        <button @click.stop="submit" type="button" class="btn btn-info w-100">Сохранить</button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@push('js_footer')
<script>
    var formTicket = new Vue({
        el: "#form_ticket",
        data: {
            specializations: {!! json_encode($data['specializations'])  !!},

            observer_key: 1,

            type: [],

            responsible_rps: [],
            techs: [],
            responsible_receivers: [],
            responsible_dispatchers: [],
            receive_locations: [],
            recommended_vehicles: [],
            responsible_users: [],

            responsible_rp: '',
            tech: '',
            responsible_dispatcher: '',
            responsible_receiver: '',
            receive_location: '',
            responsible_user: '',
            desired_receive_date: '',
            specialization: '',

            usage_date_period: ['', ''],

            initialized: false,
            disabledTech: false,

            usageDateFromPickerOptions: {
                firstDayOfWeek: 1,
                disabledDate: date =>
                    (date < moment().startOf('date')) ||
                    (formTicket.usage_date_period[1] ? (date > moment(formTicket.usage_date_period[1], "DD.MM.YYYY")) : false) ||
                    ((formTicket.desired_receive_date && formTicket.type.includes('Перемещение')) ? (date < moment(formTicket.desired_receive_date, "DD.MM.YYYY")) : false),
            },
            usageDateToPickerOptions: {
                firstDayOfWeek: 1,
                disabledDate: date =>
                    (date < moment().startOf('date')) ||
                    (formTicket.usage_date_period[0] ? (date < moment(formTicket.usage_date_period[0], "DD.MM.YYYY")) : false) ||
                    ((formTicket.desired_receive_date && formTicket.type.includes('Перемещение')) ? (date < moment(formTicket.desired_receive_date, "DD.MM.YYYY")) : false),
            },
            desiredReceiveDatePickerOptions: {
                firstDayOfWeek: 1,
                disabledDate: date =>
                    (date < moment().startOf('date')) ||
                    ((formTicket.usage_date_period[0] && formTicket.type.includes('Использование')) ? (date > moment(formTicket.usage_date_period[0], "DD.MM.YYYY")) : false) ||
                    ((formTicket.usage_date_period[1] && formTicket.type.includes('Использование')) ? (date > moment(formTicket.usage_date_period[1], "DD.MM.YYYY")) : false),
            },

            comment: '',

            vehicles: [''],
            window_width: 10000,
        },
        computed: {
            recommended_vehicles_filtered() {
                return this.recommended_vehicles.filter(el => !this.vehicles.includes(el.id));
            },
            isTypeUsage() {
                return this.type.indexOf('Использование') !== -1;
            },
            isTypeMove() {
                return this.type.indexOf('Перемещение') !== -1;
            },
            techLocation() {
                const foundTech = this.techs.find(el => el.id == this.tech);
                return foundTech ? foundTech.start_location : '';
            }
        },
        watch: {
            tech(val) {
                if (val) {
                    if (this.receive_locations.map(el => el.id).indexOf(String(this.techLocation.id)) === -1) {
                        this.receive_locations.unshift({
                            name: this.techLocation.name,
                            id: String(this.techLocation.id),
                        });
                    }
                    if (!this.receive_location || !this.isTypeUsage) {
                        this.receive_location = String(this.techLocation.id);
                    } else if (this.receive_location !== String(this.techLocation.id) && this.isTypeUsage) {
                        this.suggestMove();
                    }
                }
            },
            receive_location(val) {
                if (val) {
                    if (this.tech && this.techLocation && String(this.techLocation.id) !== val
                        && !this.isTypeMove && this.isTypeUsage) {
                        this.suggestMove();
                    }
                }
            },
        },
        mounted() {
            $(window).on('resize', this.handleResize);
            this.handleResize();
            this.search_responsible_rps('');
            this.search_responsible_users('');
            this.search_responsible_receivers('');
            this.search_responsible_dispatchers('');
            this.search_receive_locations('');
            this.search_techs('');
            this.search_vehicles('');
        },
        methods: {
            add_transport() {
                this.vehicles.push({});
                this.vehicles[this.vehicles.length - 1] = '';
            },
            remove_transport(index) {
                this.vehicles.splice(index, 1);
            },
            onFocus() {
                $('.el-input__inner').blur();
            },
            handleError(error, file, fileList) {
                let message = '';
                let errors = error.response.data.errors;
                for (let key in errors) {
                    message += errors[key][0] + '<br>';
                }
                swal({
                    type: 'error',
                    title: "Ошибка",
                    html: message,
                });
            },
            suggestMove() {
                this.$confirm('Выбранная единица техники находится на объекте, отличным от места использования. ' +
                    'Перед началом использования её необходимо переместить.<br>Добавить перемещение в заявку?' +
                    '<br><span class="error-message">' +
                    'В случае отмены место использования будет ' +
                    'установлено в соответствии с местом нахождения единицы техники</span>',
                    'Подтвердите действие', {
                    dangerouslyUseHTMLString: true,
                    confirmButtonText: 'Подтвердить',
                    cancelButtonText: 'Отмена',
                    type: 'warning',
                }).then(() => {
                    this.type.push('Перемещение');
                    this.$refs['tech-select-inner'].blur();
                    this.$refs['use-location-id-select-inner'].blur();
                }).catch(() => {
                    if (this.receive_locations.map(el => el.id).indexOf(String(this.techLocation.id)) === -1) {
                        this.receive_locations.unshift({
                            name: this.techLocation.name,
                            id: String(this.techLocation.id),
                        });
                    }
                    this.receive_location = String(this.techLocation.id);
                    this.$refs['tech-select-inner'].blur();
                    this.$refs['use-location-id-select-inner'].blur();
                });
            },
            submit() {
                this.$refs.observer.validate().then(success => {
                    if (!success) {
                        const error_field_vid = Object.keys(this.$refs.observer.errors).find(el => this.$refs[el].errors.length > 0);
                        /*$('.modal').animate({
                            scrollTop: $('#' + error_field_vid).offset().top
                        }, 1200);*/
                        $('#' + error_field_vid).focus();
                        return;
                    }
                    axios.post('{{ route('building::tech_acc::our_technic_tickets.store') }}', {
                        our_technic_id: this.tech,
                        resp_rp_user_id: this.responsible_rp,
                        //ticket_resp_user_id: this.responsible_sender,
                        recipient_user_id: this.isTypeMove ? this.responsible_receiver : '',
                        usage_resp_user_id: this.isTypeUsage ? this.responsible_user : '',
                        process_resp_user_id: this.isTypeMove ? this.responsible_dispatcher : '',
                        //sending_object_id: this.send_location,
                        getting_object_id: this.receive_location,
                        usage_from_date:  this.isTypeUsage ? this.usage_date_period[0] : '',
                        usage_to_date:  this.isTypeUsage ? this.usage_date_period[1] : '',
                        //sending_from_date: this.send_date_period[0],
                        //sending_to_date: this.send_date_period[1],
                        //getting_from_date: this.receive_date_period[0],
                        getting_to_date: this.isTypeMove ? this.desired_receive_date : '',
                        vehicle_ids: this.isTypeMove ? this.vehicles : '',
                        specialization: this.specialization,
                        comment: this.comment,
                    })
                        .then((response) => {
                            if (this.disabledTech) {
                                vm.updateTechsAfterTicketCreate(response.data.our_technic);
                            } else {
                                vm.updateTickets(response.data);
                            }
                            swal({
                                type: 'success',
                                title: "Запись была создана",
                            }).then(() => {
                                $('#form_ticket').modal('hide');
                                $('.modal').css('overflow-y', 'auto');
                                this.reset();
                            });
                        })
                        .catch(error => this.handleError(error));
                });
            },
            reset() {
                this.search_responsible_rps('');
                this.search_responsible_users('');
                this.search_responsible_receivers('');
                this.search_responsible_dispatchers('');
                this.search_receive_locations('');
                this.search_techs('');
                this.search_vehicles('');
                this.observer_key += 1;
                this.type = [];

                this.responsible_rp = '';
                this.tech = '';
                this.responsible_dispatcher = '';
                this.responsible_receiver = '';
                this.receive_location = '';
                this.responsible_user = '';
                this.specialization = '';

                this.usage_date_period = [];

                this.comment = '';

                this.vehicles = [''];

                this.$nextTick(() => {
                    this.$refs.observer.reset();
                });
            },
            search_responsible_rps(query) {
                if (query) {
                    axios.get('{{ route('users::get_users_for_tech_tickets') }}', {params: {
                            group_ids: [27, 19, 13, 8],
                            q: query,
                        }})
                        .then(response => this.responsible_rps = response.data.map(el => ({
                            name: el.label,
                            id: el.code
                        })))
                        .catch(error => console.log(error));
                } else {
                    axios.get('{{ route('users::get_users_for_tech_tickets') }}', { params: {
                            group_ids: [27, 19, 13, 8],
                        }})
                        .then(response => this.responsible_rps = response.data.map(el => ({
                            name: el.label,
                            id: el.code
                        })))
                        .catch(error => console.log(error));
                }
            },
            promised_search_techs(query) {
                return new Promise((resolve, reject) => {
                    if (query) {
                    axios.get('{{ route('building::tech_acc::get_technics') }}', {params: {
                            q: query,
                            free_only: true,
                            relations: ['start_location'],
                        }})
                        .then(response => {
                            this.techs = response.data.data.map(el => ({
                                start_location: el.start_location,
                                name: el.category_name + ': ' + el.brand + ' ' + el.model
                                    + (el.inventory_number ? (' Инв.№' + el.inventory_number) : '')
                                    + (el.start_location.short_name ? ` (${el.start_location.short_name})` : ` (${el.start_location.name})`),
                                id: el.id
                            }));
                            resolve();
                    })
                            .catch(error => {
                                console.log(error);
                                reject();
                            });
                    } else {
                        axios.get('{{ route('building::tech_acc::get_technics') }}', {params: {
                                free_only: true,
                                relations: ['start_location'],
                            }})
                            .then(response => {
                                this.techs = response.data.data.map(el => ({
                                    start_location: el.start_location,
                                    name: el.category_name + ': ' + el.brand + ' ' + el.model
                                        + (el.inventory_number ? (' Инв.№' + el.inventory_number) : '')
                                        + (el.start_location.short_name ? ` (${el.start_location.short_name})` : ` (${el.start_location.name})`),
                                    id: el.id
                                }));
                                resolve();
                            })
                            .catch(error => {
                                console.log(error);
                                reject();
                            });
                    }
                })
            },
            search_techs(query) {
                if (query) {
                    axios.get('{{ route('building::tech_acc::get_technics') }}', {params: {
                            q: query,
                            free_only: true,
                            relations: ['start_location'],
                        }})
                        .then(response => this.techs = response.data.data.map(el => ({
                            start_location: el.start_location,
                            name: el.category_name + ': ' + el.brand + ' ' + el.model
                                + (el.inventory_number ? (' Инв.№' + el.inventory_number) : '')
                                + (el.start_location.short_name ? ` (${el.start_location.short_name})` : ` (${el.start_location.name})`),
                            id: el.id
                        })))
                        .catch(error => console.log(error));
                } else {
                    axios.get('{{ route('building::tech_acc::get_technics') }}', {params: {
                            free_only: true,
                            relations: ['start_location'],
                        }})
                        .then(response => this.techs = response.data.data.map(el => ({
                            start_location: el.start_location,
                            name: el.category_name + ': ' + el.brand + ' ' + el.model
                                + (el.inventory_number ? (' Инв.№' + el.inventory_number) : '')
                                + (el.start_location.short_name ? ` (${el.start_location.short_name})` : ` (${el.start_location.name})`),
                            id: el.id
                        })))
                        .catch(error => console.log(error));
                }
            },
            search_responsible_dispatchers(query) {
                if (query) {
                    axios.get('{{ route('users::get_users_for_tech_tickets') }}', {params: {
                            q: query,
                        }})
                        .then(response => this.responsible_dispatchers = response.data.map(el => ({
                            name: el.label,
                            id: el.code
                        })))
                        .catch(error => console.log(error));
                } else {
                    axios.get('{{ route('users::get_users_for_tech_tickets') }}')
                        .then(response => this.responsible_dispatchers = response.data.map(el => ({
                            name: el.label,
                            id: el.code
                        })))
                        .catch(error => console.log(error));
                }
            },
            search_receive_locations(query) {
                if (query) {
                    axios.post('{{ route('building::mat_acc::report_card::get_objects') }}', {q: query})
                        .then(response => this.receive_locations = response.data.map(el => ({ name: el.label, id: el.code })))
                        .catch(error => console.log(error));
                } else {
                    axios.post('{{ route('building::mat_acc::report_card::get_objects') }}')
                        .then(response => this.receive_locations = response.data.map(el => ({ name: el.label, id: el.code })))
                        .catch(error => console.log(error));
                }
            },
            search_responsible_receivers(query) {
                if (query) {
                    axios.get('{{ route('users::get_users_for_tech_tickets') }}', {params: {
                            q: query,
                        }})
                        .then(response => this.responsible_receivers = response.data.map(el => ({
                            name: el.label,
                            id: el.code
                        })))
                        .catch(error => console.log(error));
                } else {
                    axios.get('{{ route('users::get_users_for_tech_tickets') }}')
                        .then(response => this.responsible_receivers = response.data.map(el => ({
                            name: el.label,
                            id: el.code
                        })))
                        .catch(error => console.log(error));
                }
            },
            search_responsible_users(query) {
                if (query) {
                    axios.get('{{ route('users::get_users_for_tech_tickets') }}', {params: {
                            q: query,
                        }})
                        .then(response => this.responsible_users = response.data.map(el => ({
                            name: el.label,
                            id: el.code
                        })))
                        .catch(error => console.log(error));
                } else {
                    axios.get('{{ route('users::get_users_for_tech_tickets') }}')
                        .then(response => this.responsible_users = response.data.map(el => ({
                            name: el.label,
                            id: el.code
                        })))
                        .catch(error => console.log(error));
                }
            },
            search_vehicles(query) {
                if (query) {
                    axios.get('{{ route('building::vehicles::get_vehicles') }}', {params: {
                            q: query,
                        }})
                        .then(response => this.recommended_vehicles = response.data.data.map(el => ({
                            name: el.mark + ' ' + el.model,
                            id: el.id
                        })))
                        .catch(error => console.log(error));
                } else {
                    axios.get('{{ route('building::vehicles::get_vehicles') }}')
                        .then(response => this.recommended_vehicles = response.data.data.map(el => ({
                            name: el.mark + ' ' + el.model,
                            id: el.id
                        })))
                        .catch(error => console.log(error));
                    if (!this.initialized || this.vehicles.length === 1) {
                        this.vehicles[0] = '';
                    }
                }
            },
            handleResize() {
                this.window_width = $(window).width();
            },
        },
    });
</script>
@endpush
