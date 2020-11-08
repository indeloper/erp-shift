<div class="modal fade bd-example-modal-lg show" id="card_ticket" role="dialog" aria-labelledby="modal-search"
     style="display: none;">
    <div class="modal-dialog modal-lg" role="document" style="max-width:900px">
        <div class="modal-content">
            <div class="decor-modal__body">
                <div class="row" style="flex-direction: row-reverse">
                    <div class="col-md-4">
                        <div class="right-bar-info border-0">
                            <div>
                                <div class="row">
                                    <div class="col-md-12">
                                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                                <span aria-hidden="true"
                                                      style="color:#202020;font-size: 26px;font-weight: 500;">&times;</span>
                                        </button>
                                    </div>
                                </div>
                                <div class="task-info__text-unit text-right">
                                        <span class="task-info__head-title">
                                            Время создания
                                        </span>
                                    <span class="task-info__body-title">
                                            @{{ ticket.created_at ? customConvertDateFormat(ticket.created_at) : '' }}
                                    </span>
                                </div>
                                <div class="task-info__text-unit text-right">
                                        <span class="task-info__head-title">
                                            Автор заявки
                                        </span>
                                    <a {{--href="#"--}} class="task-info__body-title pd-0 task-info__link">
                                        @{{ ticket.active_users ? (ticket.active_users.find(user => user.ticket_responsible.type === 5) ? ticket.active_users.find(user => user.ticket_responsible.type === 5).full_name : ''): '' }}
                                    </a>
                                </div>
                                <hr>
                                <div class="task-info__text-unit">
                                    <span class="task-info__head-title">
                                        Статус
                                    </span>
                                    <span class="task-info__body-title">
                                            <span :class="getStatusClass(ticket.short_data ? ticket.short_data.status_name : '')">@{{ ticket.short_data ? ticket.short_data.status_name : '' }}</span><br>
                                    </span>
                                </div>
                                <div class="task-info__text-unit">
                                    <span class="task-info__head-title">
                                        Направление
                                    </span>
                                    <a {{--href="#"--}} class="task-info__body-title pd-0 task-info__link">
                                        @{{ ticket.human_specialization ? ticket.human_specialization : '' }}
                                    </a>
                                </div>
                                <div class="task-info__text-unit">
                                        <span class="task-info__head-title">
                                            Руководитель проекта
                                        </span>
                                    <a {{--href="#"--}} class="task-info__body-title pd-0 task-info__link">
                                        @{{ ticket.active_users ? (ticket.active_users.find(user => user.ticket_responsible.type === 1) ? ticket.active_users.find(user => user.ticket_responsible.type === 1).full_name : ''): '' }}
                                    </a>
                                </div>
                                <h6 class="decor-h6-modal">Информация о технике</h6>
                                <div class="task-info__text-unit">
                                    <span class="task-info__head-title">
                                         Техника
                                    </span>
                                    <span v-if="ticket.our_technic" class="task-info__body-title">
                                        @{{ ticket.our_technic.brand }} @{{ ticket.our_technic.model }}
                                    </span>
                                    <span v-else class="task-info__body-title text-danger">Не назначена</span>
                                </div>
                                <div class="task-info__text-unit">
                                        <span class="task-info__head-title">
                                            Инвентарный номер
                                        </span>
                                    <span class="task-info__body-title">
                                            @{{ ticket.our_technic ? ticket.our_technic.inventory_number : '' }}
                                        </span>
                                </div>
                                <div class="task-info__text-unit">
                                        <span class="task-info__head-title">
                                            Категория техники
                                        </span>
                                    <span class="task-info__body-title">
                                            @{{ ticket.our_technic ? ticket.our_technic.category_name : '' }}
                                        </span>
                                </div>
                                <template v-if="ticket.active_users && ticket.active_users.find(user => user.ticket_responsible.type === 3)">
                                    <h6 class="decor-h6-modal">Перемещение техники</h6>
                                    <div class="task-info__text-unit">
                                        <span class="task-info__head-title">
                                            Логист
                                        </span>
                                        <a {{--href="#"--}} v-if="ticket.users_ordered ? ticket.users_ordered.find(user => user.ticket_responsible.type === 6) : false" class="task-info__body-title pd-0 task-info__link">
                                            @{{ ticket.users_ordered ? (ticket.users_ordered.find(user => user.ticket_responsible.type === 6) ? ticket.users_ordered.find(user => user.ticket_responsible.type === 6).full_name : ''): '' }}
                                        </a>
                                        <span v-else class="task-info__body-title text-danger">Не назначен</span>
                                    </div>
                                    <div v-if="ticket.sending_object" class="task-info__text-unit">
                                        <span class="task-info__head-title">
                                            Перемещение
                                        </span>
                                        <span class="task-info__body-title">
                                            <a {{--href="#"--}} class="pd-0 task-info__link">@{{ ticket.sending_object.name_tag }}</a>
                                             ->
                                            <a {{--href="#"--}} class="pd-0 task-info__link" style="color: inherit">@{{ ticket.getting_object.name_tag }}</a>
                                        </span>
                                    </div>
                                    <div class="task-info__text-unit">
                                        <span class="task-info__head-title">
                                            Период отправки
                                        </span>
                                        <span v-if="ticket.sending_from_date && ticket.sending_to_date" class="task-info__body-title">
                                            @{{ convertDateFormat(ticket.sending_from_date, false) }} - @{{ convertDateFormat(ticket.sending_to_date, false) }}
                                        </span>
                                        <span v-else class="task-info__body-title text-danger">Не назначен</span>
                                    </div>
                                    <div class="task-info__text-unit">
                                        <span class="task-info__head-title">
                                            Период приемки
                                        </span>
                                        <span v-if="ticket.getting_from_date && ticket.getting_to_date" class="task-info__body-title">
                                            @{{ convertDateFormat(ticket.getting_from_date, false) }} - @{{  convertDateFormat(ticket.getting_to_date, false) }}
                                        </span>
                                        <span v-else class="task-info__body-title text-danger">Не назначен</span>
                                    </div>
                                    <div class="task-info__text-unit">
                                        <span class="task-info__head-title">
                                            Транспорт
                                        </span>
                                        <span v-if="ticket.vehicles && ticket.vehicles.length > 0 && ticket.status > 4" class="task-info__body-title">
                                            @{{ ticket.vehicles[0].mark }} @{{ ticket.vehicles[0].model }}
                                        </span>
                                        <span v-else class="task-info__body-title text-danger">Не назначен</span>
                                    </div>
                                    <div class="task-info__text-unit">
                                        <span class="task-info__head-title">
                                            Ответственный за отправку
                                        </span>
                                        <a {{--href="#"--}} v-if="ticket.users_ordered ? ticket.users_ordered.find(user => user.ticket_responsible.type === 2) : false" class="task-info__body-title pd-0 task-info__link">
                                            @{{ ticket.users_ordered ? (ticket.users_ordered.find(user => user.ticket_responsible.type === 2) ? ticket.users_ordered.find(user => user.ticket_responsible.type === 2).full_name : ''): '' }}
                                        </a>
                                        <span v-else class="task-info__body-title text-danger">Не назначен</span>
                                    </div>
                                    <div class="task-info__text-unit">
                                        <span class="task-info__head-title">
                                            Ответственный за приемку
                                        </span>
                                        <a {{--href="#"--}} v-if="ticket.users_ordered ? ticket.users_ordered.find(user => user.ticket_responsible.type === 3) : false" class="task-info__body-title pd-0 task-info__link">
                                            @{{ ticket.users_ordered ? (ticket.users_ordered.find(user => user.ticket_responsible.type === 3) ? ticket.users_ordered.find(user => user.ticket_responsible.type === 3).full_name : ''): '' }}
                                        </a>
                                        <span v-else class="task-info__body-title text-danger">Не назначен</span>
                                    </div>
                                </template>
                                <template v-if="ticket.active_users && ticket.active_users.find(user => user.ticket_responsible.type === 4)">
                                    <h6 class="decor-h6-modal">Использование техники</h6>
                                    <div v-if="ticket.getting_object" class="task-info__text-unit">
                                        <span class="task-info__head-title">
                                            Место использования
                                        </span>
                                        <span class="task-info__body-title">
                                            <a {{--href="#"--}} class="pd-0 task-info__link" style="color: inherit">@{{ ticket.getting_object.short_name ? ticket.getting_object.short_name : (ticket.getting_object.name + ', ' + ticket.getting_object.address) }}</a>
                                        </span>
                                    </div>
                                    <div class="task-info__text-unit">
                                        <span class="task-info__head-title">
                                            Плановое начало использования
                                        </span>
                                        <span class="task-info__body-title">
                                            @{{ convertDateFormat(ticket.usage_from_date, false) }}
                                        </span>
                                    </div>
                                    <div class="task-info__text-unit">
                                        <span class="task-info__head-title">
                                            Плановое окончание использования
                                        </span>
                                        <span class="task-info__body-title">
                                            @{{ convertDateFormat(ticket.usage_to_date, false) }}
                                        </span>
                                    </div>
                                    <div class="task-info__text-unit">
                                        <span class="task-info__head-title">
                                            Ответственный за использование
                                        </span>
                                        <a {{--href="#"--}} class="task-info__body-title pd-0 task-info__link">
                                            @{{ ticket.active_users ? (ticket.active_users.find(user => user.ticket_responsible.type === 4) ? ticket.active_users.find(user => user.ticket_responsible.type === 4).full_name : ''): '' }}
                                        </a>
                                    </div>
                                </template>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-8">
                        <div class="left-bar-main">
                            <div class="row">
                                <div class="col-md-12">
                                    <h4 class="m-0 text-uppercase" style="font-size: 20px">
                                        <span class="font-weight-bold"># @{{ ticket.id }}</span>
                                        @{{ ticketTypeName }}
                                        <a {{--href="#"--}} v-if="ticket.our_technic" class="text-dark pd-0 task-info__link">
                                            @{{ ticket.our_technic.brand }} @{{ ticket.our_technic.model }}
                                        </a>
                                    </h4>
                                </div>
                            </div>
                            <template v-if="ticket.comment">
                                <h6 class="decor-h6-modal">Комментарий</h6>
                                <div class="font-13 mb-4">
                                    @{{ ticket.comment }}
                                </div>
                            </template>
                            <template v-if="ticket.active_users && ticket.active_users.find(user => user.ticket_responsible.type === 3)">
                                <h5 class="h5-tech" style="margin-right: 15px">Перемещение</h5>
                                <template {{--v-show="ticket.vehicles.length > 0 && ticket.status > 4 && ticket.type > 1"--}}>
                                    <h6 class="decor-h6-modal">Описание</h6>
                                    {{--<div v-if="ticket.active_users && !ticket.active_users.some(user => user.ticket_responsible.type === 4)" class="modal-section font-13">
                                        @{{ ticket.comment }}
                                    </div>--}}
                                    <em class="font-13 mb-20 d-block" v-if="ticket.status > 4 && ticket.vehicles.length == 1">
                                        Назначенный транспорт: <span :class="ticket.vehicles.length === 0 ? 'text-danger font-weight-bold' : 'font-weight-bold'">@{{ ticket.vehicles.length > 0 ? ticket.vehicles.map(el => el.mark + ' ' + el.model).join(',') : 'Не назначен' }}</span>
                                    </em>
                                </template>
                                <div class="task-info__text-unit">
                                    <span class="task-info__head-title">
                                        Период отправки
                                    </span>
                                    <span v-if="ticket.sending_from_date && ticket.sending_to_date" class="task-info__body-title">
                                        @{{ convertDateFormat(ticket.sending_from_date, false) }} - @{{ convertDateFormat(ticket.sending_to_date, false) }}
                                    </span>
                                    <span v-else class="task-info__body-title text-danger">Не назначен</span>
                                </div>
                                <div class="task-info__text-unit">
                                    <span class="task-info__head-title">
                                        Период приемки
                                    </span>
                                    <span v-if="ticket.getting_from_date && ticket.getting_to_date" class="task-info__body-title">
                                        @{{ convertDateFormat(ticket.getting_from_date, false) }} - @{{  convertDateFormat(ticket.getting_to_date, false) }}
                                    </span>
                                    <span v-else class="task-info__body-title text-danger">Не назначен</span>
                                </div>
                            </template>
                            <h5 class="h5-tech" v-if="ticket.active_users && ticket.active_users.find(user => user.ticket_responsible.type === 4)" style="margin-bottom: 15px; margin-right: 15px">Использование</h5>
                            <template v-if="ticket.active_users && ticket.active_users.find(user => user.ticket_responsible.type === 4) && (ticket.type == 1 || ticket.type == 3) && ticket.status > 5">
                                <button v-if="canReassignUsageUser(ticket)" class="btn btn-sm btn-outline" data-toggle="modal" data-target="#tech-usage-reassignment">Передать контроль за использование</button>
                                <h6 class="decor-h6-modal">Отчет об использовании</h6>
                                <div v-if="ticket.status == 7" class="modal-section text-right"
                                     :style="window_width > 769 ? 'margin-top: -45px;' : ''">
                                    <button v-if="userWasResponsibleUser" type="button"
                                            @click="reports.is_update = false; resetReportAdd();"
                                            class="btn btn-round btn-sm btn-success" data-toggle="modal"
                                            data-target="#report-add">
                                        Добавить запись
                                    </button>
                                </div>
                                <div class="modal-section mb-2" :style="window_width <= 769 ? 'margin: 0 0 0 -30px;' : ''">
                                    <template>
                                        <el-table
                                            v-if="window_width > 769"
                                            :data="reversedReports"
                                            :key="table_key"
                                            style="width: 100%">
                                            <el-table-column
                                                prop="date_carbon"
                                                label="Дата">
                                            </el-table-column>
                                            <el-table-column
                                                prop="hours"
                                                label="Количество часов">
                                            </el-table-column>
                                            <el-table-column
                                                prop="user.full_name"
                                                label="Ответственный">
                                            </el-table-column>
                                            <el-table-column
                                                prop="comment"
                                                label="Комментарий">
                                            </el-table-column>
                                            <el-table-column align="right">
                                                <template v-if="ticket.status == 7 && isMyReport(scope.row)" slot-scope="scope">
                                                    <el-button
                                                        size="mini"
                                                        type="primary" icon="el-icon-edit" circle
                                                        @click="handleEditReport(scope.$index, scope.row)"></el-button>
                                                    <el-button
                                                        size="mini"
                                                        type="danger"
                                                        type="danger" icon="el-icon-delete" circle
                                                        @click="handleDeleteReport(scope.$index, scope.row)"></el-button>
                                                </template>
                                            </el-table-column>
                                        </el-table>
                                        <el-table :data="reversedReports"
                                                  class="w-100"
                                                  v-else
                                        >
                                            <el-table-column
                                                v-if="window_width <= 769"
                                            >
                                                <template slot-scope="scope">
                                                    <div class="d-flex justify-content-between"
                                                         style="border-bottom: 2px solid lightgrey;"
                                                    >
                                                        <div class="font-weight-bold mr-2">Дата</div>
                                                        <div class="wrapword">@{{ scope.row.date_carbon }}</div>
                                                    </div>
                                                    <div class="d-flex justify-content-between"
                                                         style="border-bottom: 2px solid lightgrey;"
                                                    >
                                                        <div class="font-weight-bold mr-2">Количество часов</div>
                                                        <div class="wrapword">@{{ scope.row.hours }}</div>
                                                    </div>
                                                    <div class="d-flex justify-content-between"
                                                         style="border-bottom: 2px solid lightgrey;"
                                                    >
                                                        <div class="font-weight-bold mr-2">Ответственный</div>
                                                        <div class="wrapword">@{{ scope.row.user.full_name }}</div>
                                                    </div>
                                                    <div class="d-flex justify-content-between"
                                                    >
                                                        <div class="font-weight-bold mr-2">Комментарий</div>
                                                        <div class="wrapword">@{{ scope.row.comment ? scope.row.comment : '-' }}</div>
                                                    </div>
                                                    <div class="d-flex justify-content-between mt-3"
                                                         v-if="ticket.status == 7 && isMyReport(scope.row)"
                                                    >
                                                        <el-button
                                                            size="mini"
                                                            class="d-block"
                                                            type="danger"
                                                            type="danger" icon="el-icon-delete" circle
                                                            @click="handleDeleteReport(scope.$index, scope.row)">
                                                        </el-button>
                                                        <el-button
                                                            size="mini"
                                                            class="d-block"
                                                            type="primary" icon="el-icon-edit" circle
                                                            @click="handleEditReport(scope.$index, scope.row)">
                                                        </el-button>
                                                    </div>
                                                </template>
                                            </el-table-column>
                                        </el-table>
                                    </template>
                                </div>
                                {{--<div class="modal-section text-right task-info__text-unit">
                                    <a href="#" target="_blank" class="modal-link task-info__body-title pd-0">
                                        Просмотр всех записей
                                    </a>
                                </div>--}}
                            </template>
                            <div v-if="ticket.show_buttons" class="modal-section text-right task-info__text-unit responsive-buttons-group" style="padding-left: 0; margin: 0 0 18px -15px;">
                                <template v-if="ticket.status == 1">
                                    <button class="btn btn-sm btn-danger btn-outline" data-toggle="modal" data-target="#ticket-reject">Отклонить заявку</button>
                                    <button class="btn btn-sm btn-success" @click="approveTicket">Согласовать заявку</button>
                                </template>
                                <template v-else-if="ticket.status == 5">
                                    <button class="btn btn-sm btn-primary btn-outline" @click="beginUsage">Начать использование</button>
                                </template>
                                <template v-else-if="ticket.status == 2">
                                    <button class="btn btn-sm btn-danger btn-outline" data-toggle="modal" data-target="#ticket-reject">Отклонить заявку</button>
                                    <button class="btn btn-sm btn-warning btn-outline" data-toggle="modal" data-target="#ticket-hold">Удержать</button>
                                    <button class="btn btn-sm btn-primary" data-toggle="modal" data-target="#ticket-handle" @click="updateTicketHandleModal">Обработать</button>
                                </template>
                                <template v-else-if="ticket.status == 6">
                                    <button v-if="isUserSender() || isUserReceiver()" class="btn btn-sm btn-danger btn-outline" data-toggle="modal" data-target="#ticket-rollback">Сообщить о проблеме</button>
                                    <button v-if="isUserSender() || isUserReceiver()" class="btn btn-sm btn-primary btn-outline" data-toggle="modal" @click="fillTtnData" data-target="#ticket-make-ttn">Сформировать ттн</button>
                                    <button v-if="isUserSender()" class="btn btn-sm btn-success" data-toggle="modal" data-target="#tech-send-confirm">Зафиксировать отправку техники</button>
                                    <button v-if="isUserFirstSender()" class="btn btn-sm btn-outline" data-toggle="modal" data-target="#tech-send-reassignment">Передать контроль отправки техники</button>
                                    <button v-if="isUserReceiver()" class="btn btn-sm btn-success" data-toggle="modal" data-target="#tech-receive-confirm">Зафиксировать получение техники</button>
                                    <button v-if="isUserFirstReceiver()" class="btn btn-sm btn-outline" data-toggle="modal" data-target="#tech-receive-reassignment">Передать контроль получения техники</button>
                                    <button v-if="isUserLogist()" class="btn btn-sm btn-primary" data-toggle="modal" data-target="#ticket-handle" @click="updateTicketHandleModal">Редактировать</button>
                                </template>
                                <template v-else-if="ticket.status == 7">
                                    <button class="btn btn-sm btn-danger btn-outline" data-toggle="modal" data-target="#usage-end">Завершить использование</button>
                                    <button v-if="ticket.can_extension" class="btn btn-sm btn-primary btn-outline" data-toggle="modal" data-target="#ticket-renewal">Запросить продление использования</button>
                                </template>
                                <template v-else-if="ticket.status == 4">
                                    <button class="btn btn-sm btn-danger btn-outline" data-toggle="modal" data-target="#ticket-reject">Отклонить заявку</button>
                                    <button class="btn btn-sm btn-primary" data-toggle="modal" data-target="#ticket-handle">Обработать</button>
                                </template>
                                {{--DEBUG--}}
                                {{--<button class="btn btn-sm btn-danger btn-outline" data-toggle="modal" data-target="#ticket-reject">Отклонить заявку</button>
                                <button class="btn btn-sm btn-success" @click="approveTicket">Согласовать заявку</button>
                                <button class="btn btn-sm btn-primary btn-outline" @click="beginUsage">Начать использование</button>
                                <button class="btn btn-sm btn-warning btn-outline" data-toggle="modal" data-target="#ticket-hold">Удержать</button>
                                <button class="btn btn-sm btn-primary" data-toggle="modal" data-target="#ticket-handle">Обработать</button>
                                <button class="btn btn-sm btn-danger btn-outline" data-toggle="modal" data-target="#usage-end">Завершить использование</button>
                                <button class="btn btn-sm btn-primary btn-outline" data-toggle="modal" data-target="#ticket-renewal">Запросить продление использования</button>
                                <button class="btn btn-sm btn-success" data-toggle="modal" data-target="#tech-send-confirm">Зафиксировать отправку техники</button>
                                <button class="btn btn-sm btn-success" data-toggle="modal" data-target="#tech-receive-confirm">Зафиксировать получение техники</button>--}}
                            </div>
                            <h6 class="modal-section task-info__text-unit" style="margin: 0 0 0 -15px;">
                                Активные задачи
                            </h6>
                            <div class="modal-section task-info__text-unit" style="padding-left: 0; margin: 0 0 18px -15px;">
                                <template>
                                    <el-table
                                        :data="ticket.active_tasks"
                                        style="width: 100%">
                                        <el-table-column
                                            prop="created_at"
                                            label="Дата создания">
                                        </el-table-column>
                                        <el-table-column
                                            prop="name"
                                            label="Название">
                                        </el-table-column>
                                        <el-table-column
                                            prop="responsible_user.full_name"
                                            label="Ответств.">
                                        </el-table-column>
                                    </el-table>
                                </template>
                            </div>
                            <h6 class="modal-section task-info__text-unit" style="margin: 0 0 18px -15px;">
                                Активности по заявке
                            </h6>
                            <div v-if="ticket.comments && ticket.comments.length > 0" class="modal-section task-info__text-unit" style="margin: 0 0 18px -15px;">
                                <el-timeline class="activity-timeline">
                                    {{-- <el-timeline-item
                                         v-for="(activity, index) in activities"
                                         :key="index"
                                         :icon="activity.icon"
                                         :type="activity.type"
                                         :color="activity.color"
                                         :size="activity.size"
                                         :timestamp="activity.timestamp"
                                         placement="top"
                                     >
                                         <span class="activity-author"><a href="#">@{{activity.author}}</a></span>
                                         <p v-if="activity.executor" class="activity-content">
                                             <a href="" class="activity-content__link">Смирнов Сергей Сергеевич</a> назначен исполнителем на заявку.
                                         </p>
                                         <p v-else-if="activity.periodAssign" class="activity-content">
                                             Назначен период производства ремонтных работ: <span class="fw-500">02.10.2019 10:00 - 03.10.2019 10:00</span>
                                         </p>
                                         <p v-else-if="activity.periodEdit" class="activity-content">
                                             Назначен период производства ремонтных работ: <span class="fw-500">02.10.2019 10:00 - 03.10.2019 10:00</span>
                                         </p>
                                         <p v-else-if="activity.comment" class="activity-content">
                                             @{{ activity.comment.comment }}
                                             <a href="#" class="activity-content_link" data-toggle="modal" data-target="#activity-comment-files">
                                                 Приложенных файлов: 3
                                             </a>
                                             <span class="comment-actions">
                                             <button type="button" name="button" class="btn btn-link">
                                                 <i class="fa fa-edit"></i>
                                                 Редактировать
                                             </button>
                                             <button type="button" name="button" class="btn btn-link" onclick="removeComment(index)">
                                                 <i class="fa fa-trash"></i>
                                                 Удалить
                                             </button>
                                         </span>
                                         </p>
                                         <p v-show="activity.close" class="activity-content">
                                             <span class="text-danger">Заявка отклонена.</span>
                                             Неисправность не выявил, ремонт не нужен
                                         </p>
                                     </el-timeline-item>--}}
                                    <el-timeline-item
                                        v-for="(comment, index) in ticket.comments"
                                        :key="index"
                                        :icon="comment.system ? 'el-icon-edit' : 'el-icon-chat-line-square'"
                                        {{--:type="activity.type"
                                        :color="activity.color"--}}
                                        :size="'large'"
                                        :timestamp="customConvertDateFormat(comment.created_at).split(':').slice(0,2).join(':')"
                                        placement="top"
                                    >
                                        <span class="activity-author">@{{ comment.author ? comment.author.full_name : 'Система' }}</span>
                                        <p class="activity-content">
                                            @{{ comment.comment }}
                                            <template v-if="comment.files.length > 0">
                                                <a href="#" class="activity-content_link mb-0" :data-target="'#comment-' + comment.id + '-files'" data-toggle="collapse">
                                                    Приложенных файлов: @{{ comment.files.length }}
                                                </a>
                                                <div :id="'comment-' + comment.id + '-files'" class="card-collapse collapse mb-1">
                                                    <template v-for="file in comment.files">
                                                        <a :href="'{{ asset('storage/docs/tech_accounting/') }}' + '/' + file.filename"
                                                           target="_blank" class="tech-link modal-link">
                                                            @{{ file.original_filename }}
                                                        </a>
                                                        <br/>
                                                    </template>
                                                </div>
                                            </template>
                                            <span class="comment-actions" v-if="!comment.system && canManageComment(comment)">
                                                <button type="button" name="button" class="btn btn-link" @click="editComment(comment.id)">
                                                    <i class="fa fa-edit"></i>
                                                    Редактировать
                                                </button>
                                                <button type="button" name="button" class="btn btn-link" @click="removeComment(comment.id)">
                                                    <i class="fa fa-trash"></i>
                                                    Удалить
                                                </button>
                                            </span>
                                        </p>
                                    </el-timeline-item>
                                </el-timeline>
                            </div>
                            <div v-else class="modal-section font-13">
                                Нет данных
                            </div>
                            <template v-if="[1,2,4,5,6,7].includes(ticket.status)">
                                <div class="row">
                                    <div class="col-md-6">
                                        <h6 class="decor-h6-modal border-0">@{{ edit_comment ? 'Редактирование комментария' : 'Комментарий' }}</h6>
                                    </div>
                                    <div v-if="edit_comment" class="col-md-6 text-right">
                                        <h6 class="decor-h6-modal border-0" style="text-transform: none">
                                            <a href="#" class="tech-link modal-link" @click="resetCommentEdit">
                                                Отмена
                                            </a>
                                        </h6>
                                    </div>
                                </div>
                                <div class="row modal-section task-info__text-unit" style="padding-left: 0; margin: 0 0 18px -15px;">
                                    <div class="col-md-12">
                                        <validation-provider rules="max:300" vid="ticket-comment-input"
                                                             ref="ticket-comment-input" v-slot="v">
                                            <el-input
                                                :class="v.classes"
                                                type="textarea"
                                                :rows="4"
                                                maxlength="300"
                                                id="ticket-comment-input"
                                                clearable
                                                placeholder="Оставьте комментарий"
                                                v-model="comment.text"
                                            ></el-input>
                                            <div class="error-message" style="padding-top: 0; margin-top: -2px;">@{{ v.errors[0] }}</div>
                                        </validation-provider>
                                        <div class="comment-files modal-section" :style="comment.files.length > 0 ? 'padding-bottom: 20px;' : ''">
                                            <div class="row">
                                                <div class="files col-12" id="comment-files">
                                                    <el-upload
                                                        action="{{ route('file_entry.store') }}"
                                                        :headers="{ 'X-CSRF-TOKEN': '{{ csrf_token() }}' }"
                                                        ref="comment_files_upload"
                                                        :limit="10"
                                                        :before-upload="beforeUpload"
                                                        :on-preview="handlePreview"
                                                        :on-remove="handleRemove"
                                                        :on-exceed="handleExceed"
                                                        :on-success="handleSuccess"
                                                        :on-error="handleError"
                                                        :file-list="file_list"
                                                        multiple
                                                    >
                                                        <el-button class="pull-right" icon="el-icon-upload" type="text" style="margin-bottom:0!important; padding: 8px 0!important;">Приложить файлы</el-button>
                                                    </el-upload>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="row modal-section text-right responsive-buttons-group" style="padding-left: 0; margin: 0 0 18px -15px;">
                                    <div class="col-md-12">
                                        <button class="btn btn-sm btn-primary" @click="addComment">@{{ edit_comment ? 'Сохранить' : 'Оставить комментарий' }}</button>
                                    </div>
                                </div>
                            </template>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@push('js_footer')
    <script>
        var cardTicket = new Vue({
            el: "#card_ticket",
            data: {
                USING_RESPONSIBLE_USER_TYPE: 4,
                USING_RESPONSIBLE_MOVING_TYPE: 3,

                ticket: {},
                comment_id: null,
                comment: {
                    text: '',
                    files: [],
                },
                file_list: [],
                files_to_remove: [],
                edit_comment: false,
                reports: {
                    is_update: false,
                    url_to_update: ''
                },
                sending_comment: false,
                logist:  {!! json_encode($data['main_logist'])  !!},
                curr_user_id: {{ auth()->id() }},
                window_width: 10000,
                table_key: 1,
            },
            watch: {
                ticket(ticket) {
                    techSendConfirm.updateTicket(ticket);
                    techReceiveConfirm.updateTicket(ticket);
                    techUsageConfirm.updateTicket(ticket);
                }
            },
            computed: {
                ticketTypeName() {
                    if (this.ticket.active_users && this.ticket.active_users.some(user => user.ticket_responsible.type === this.USING_RESPONSIBLE_USER_TYPE) && !this.ticket.active_users.some(user => user.ticket_responsible.type === this.MOVING_RESPONSIBLE_USER_TYPE)) {
                        return 'Использование';
                    } else if (this.ticket.active_users && this.ticket.active_users.some(user => user.ticket_responsible.type === this.USING_RESPONSIBLE_USER_TYPE) && this.ticket.active_users.some(user => user.ticket_responsible.type === this.MOVING_RESPONSIBLE_USER_TYPE)) {
                        return 'Использование и перемещение';
                    } else {
                        return 'Перемещение';
                    }
                    return '';
                },
                reversedReports() {
                    return this.ticket.reports.sort((a, b) => moment(b.date).diff(moment(a.date)));
                },
                userWasResponsibleUser() {
                    const responsibleUsersIds = this.ticket.users.filter(el => el.ticket_responsible.type === this.USING_RESPONSIBLE_USER_TYPE).map(el => el.id);
                    return responsibleUsersIds.indexOf(this.curr_user_id) !== -1;
                },
                responsibleUserCreatedAt() {
                    if (this.userWasResponsibleUser) {
                        const currUserIndex = this.ticket.users.filter(el => el.ticket_responsible.type === this.USING_RESPONSIBLE_USER_TYPE).map(el => el.id).indexOf(this.curr_user_id);
                        const currUser = currUserIndex === -1 ? null : this.ticket.users.filter(el => el.ticket_responsible.type === this.USING_RESPONSIBLE_USER_TYPE)[currUserIndex];
                        return currUser === null ? null : currUser.ticket_responsible.created_at;
                    } else {
                        return null;
                    }
                },
                responsibleUserDeactivatedAt() {
                    if (this.userWasResponsibleUser) {
                        const currUserIndex = this.ticket.users.filter(el => el.ticket_responsible.type === this.USING_RESPONSIBLE_USER_TYPE).map(el => el.id).indexOf(this.curr_user_id);
                        const currUser = currUserIndex === -1 ? null : this.ticket.users.filter(el => el.ticket_responsible.type === this.USING_RESPONSIBLE_USER_TYPE)[currUserIndex];
                        return currUser === null ? null : currUser.ticket_responsible.deactivated_at;
                    } else {
                        return null;
                    }
                },
            },
            mounted() {
                $('#ticket-comment-input').on('keydown', function (e) {
                    if (e.which == 13 && !e.shiftKey)
                    {
                        e.preventDefault();ticket_responsible
                        cardTicket.addComment();
                    }
                });
                $('#card_ticket').on('hide.bs.modal', () => vm.resetPath());
                $(window).on('resize', this.handleResize);
                this.handleResize();
            },
            methods: {
                update(ticket) {
                    this.ticket = ticket;
                    this.$forceUpdate();
                },
                updateTicketHandleModal() {
                    ticketHandle.updateResponsibleReceiver();
                    ticketHandle.updateTech();
                    if (this.ticket.status === 6) {
                        ticketHandle.updateResponsibleSender();
                        ticketHandle.updateVehicle();
                        ticketHandle.updateDates();
                    }
                },
                resetReportAdd()
                {
                    reportAdd.reset();
                },
                isMyReport(report) {
                    return report.user_id === this.curr_user_id;
                },
                handleResize() {
                    this.window_width = $(window).width();
                    this.table_key += 1;
                },
                getStatusClass(status_id) {
                    return vm.getStatusClass(status_id);
                },
                convertDateFormat(dateString) {
                    return vm.convertDateFormat(dateString);
                },
                customConvertDateFormat(dateString) {
                    return dateString.split(' ')[0].split('-').reverse().join('.') + ' ' + dateString.split(' ')[1];
                },
                addComment() {
                    if (this.comment.text) {
                        this.$refs['ticket-comment-input'].validate().then(result => {
                            if (result.valid && !this.sending_comment) {
                                this.sending_comment = true;
                                if (!this.edit_comment) {
                                    axios.post('{{ route('comments.store') }}', {
                                        comment: this.comment.text,
                                        commentable_id: this.ticket.id,
                                        commentable_type: this.ticket.class_name,
                                        file_ids: this.comment.files.map(el => el.id),
                                    })
                                        .then((response) => {
                                            if (this.ticket.comments) {
                                                this.ticket.comments.unshift(response.data.data.comment);
                                            } else {
                                                this.$set(this.ticket, 'comments', [response.data.data.comment]);
                                            }
                                            this.resetCommentEdit();
                                            this.sending_comment = false;
                                        })
                                        .catch((error) => { console.log(error) });
                                } else {
                                    axios.put('{{ route('comments.update', '') }}' + '/' + this.comment_id, {
                                        comment: this.comment.text,
                                        deleted_file_ids: this.files_to_remove,
                                        file_ids: this.comment.files.map(el => el.id),
                                    })
                                        .then((response) => {
                                            const comment_index = this.ticket.comments.findIndex(el => el.id === response.data.data.comment.id);
                                            this.$set(this.ticket.comments, comment_index, response.data.data.comment);
                                            this.resetCommentEdit();
                                            this.sending_comment = false;
                                        })
                                        .catch((error) => { console.log(error) });
                                }
                            }
                        });
                    }
                },
                editComment(id) {
                    this.edit_comment = true;
                    this.comment_id = id;
                    const comment_to_edit = this.ticket.comments.find(el => el.id === id);
                    this.comment = {
                        text: comment_to_edit.comment,
                        files: comment_to_edit.files.slice(),
                    };
                    this.file_list = comment_to_edit.files.map(el => {el.name = el.original_filename; return el;});
                },
                resetCommentEdit() {
                    this.edit_comment = false;
                    this.$refs.comment_files_upload.clearFiles();
                    this.comment = {
                        text: '',
                        files: [],
                    };
                    this.file_list = [];
                },
                removeComment(id) {
                    this.$confirm('Комментарий будет удален.', 'Вы уверены?', {
                        confirmButtonText: 'Удалить',
                        cancelButtonText: 'Отмена',
                        type: 'warning'
                    }).then(() => {
                        axios.delete('{{ route('comments.destroy', '') }}' + '/' + id)
                            .then((response) => {
                                this.ticket.comments.splice(this.ticket.comments.findIndex(el => el.id === id), 1);
                                this.resetCommentEdit();
                            })
                            .catch((error) => { console.log(error) });
                    }).catch(() => {
                    });
                },
                handleRemove(file, fileList) {
                    if (file.hasOwnProperty('response')) {
                        this.files_to_remove.push(...file.response.data.map(el => el.id));
                        //this.comment.files.splice(this.comment.files.findIndex(el => el.response.data[0].id === file.response.data[0].id));
                    } else {
                        this.files_to_remove.push(file.id);
                    }
                },
                handleSuccess(response, file, fileList) {
                    file.url = file.response.data[0] ? '{{ asset('storage/docs/tech_accounting/') }}' + '/' + file.response.data[0].filename : '#';
                    this.comment.files.push(...file.response.data.map(el => el));
                },
                handleError(error, file, fileList) {
                    let message = '';
                    let errors = error.response.data.errors;
                    for (let key in errors) {
                        message += errors[key][0] + '<br>';
                    }
                    swal({
                        type: 'error',
                        title: "Ошибка загрузки файла",
                        html: message,
                    });
                },
                handlePreview(file) {
                    window.open(file.url ? file.url : '{{ asset('storage/docs/tech_accounting/') }}' + '/' + file.filename, '_blank');
                    $('#form_tech').focus();
                },
                handleExceed(files, fileList) {
                    this.$message.warning(`Невозможно загрузить ${files.length} файлов. Вы можете загрузить еще ${10 - fileList.length} файлов`);
                },
                beforeUpload(file) {
                    const FORBIDDEN_EXTENSIONS = ['exe'];
                    const FILE_MAX_LENGTH = 50000000;
                    const nameParts = file.name.split('.');
                    if (FORBIDDEN_EXTENSIONS.indexOf(nameParts[nameParts.length - 1]) !== -1) {
                        this.$message.warning(`Ошибка загрузки файла. Файл не должен быть исполняемым`);
                        return false;
                    }
                    if (file.size > FILE_MAX_LENGTH) {
                        this.$message.warning(`Ошибка загрузки файла. Размер файла не должен превышать 50Мб`);
                        return false;
                    }
                    return true;
                },
                approveTicket() {
                    swal({
                        title: 'Подтверждение',
                        text: "Подтвердите согласование заявки",
                        type: 'warning',
                        showCancelButton: true,
                        confirmButtonColor: '#87CB16',
                        cancelButtonColor: '#3085d6',
                        cancelButtonText: 'Назад',
                        confirmButtonText: 'Подтвердить'
                    }).then((result) => {
                        if(result.value) {
                            axios.put('{{ route('building::tech_acc::our_technic_tickets.update', ['']) }}' + '/' + cardTicket.ticket.id, {
                                acceptance: 'confirm',
                            })
                                .then((response) => {
                                    const ticketIndex = vm.tickets.findIndex(el => el.id == cardTicket.ticket.id);
                                    vm.$set(vm.tickets, ticketIndex, response.data.data);
                                    vm.tickets[ticketIndex].is_loaded = true;
                                    cardTicket.update(vm.tickets[ticketIndex]);
                                })
                                .catch((error) => { console.log(error) })
                        }
                    });
                },
                beginUsage() {
                    swal({
                        title: 'Подтверждение',
                        text: "Подтвердите начало использования",
                        type: 'warning',
                        showCancelButton: true,
                        confirmButtonColor: '#87CB16',
                        cancelButtonColor: '#3085d6',
                        cancelButtonText: 'Назад',
                        confirmButtonText: 'Подтвердить'
                    }).then((result) => {
                        if(result.value) {
                            if(result.value) {
                                axios.put('{{ route('building::tech_acc::our_technic_tickets.update', ['']) }}' + '/' + cardTicket.ticket.id, {
                                    result: 'confirm',
                                })
                                    .then((response) => {
                                        const ticketIndex = vm.tickets.findIndex(el => el.id == cardTicket.ticket.id);
                                        vm.$set(vm.tickets, ticketIndex, response.data.data);
                                        vm.tickets[ticketIndex].is_loaded = true;
                                        cardTicket.update(vm.tickets[ticketIndex]);
                                    })
                                    .catch((error) => { console.log(error) })
                            }
                        }
                    });
                },
                handleEditReport(index, row) {
                    let route = '{{ route('building::tech_acc::our_technic_tickets.report.update', ["ID_TO_SUBSTITUTE", "ID_TO_SUBSTITUTE"]) }}';
                    this.reports.url_to_update = makeUrl(route, [cardTicket.ticket.id, row.id]);
                    this.reports.is_update = true;
                    reportAdd.usage_duration = row.hours;
                    reportAdd.comment = row.comment;
                    reportAdd.usage_date = row.date;
                    $('#report-add').modal('show');
                },
                handleDeleteReport(index, row) {
                    let route = '{{ route('building::tech_acc::our_technic_tickets.report.destroy', ["ID_TO_SUBSTITUTE", "ID_TO_SUBSTITUTE"]) }}';
                    axios.delete(makeUrl(route, [cardTicket.ticket.id, row.id]))
                        .then((response) => {
                            const ticketIndex = vm.tickets.findIndex(el => el.id == cardTicket.ticket.id);
                            vm.$set(vm.tickets, ticketIndex, response.data.data);
                            vm.tickets[ticketIndex].is_loaded = true;
                            cardTicket.update(vm.tickets[ticketIndex]);
                        })
                        .catch((error) => {
                            console.log(error)
                        })
                },
                fillTtnData() {
                    ttn.fillData();
                },
                isUserLogist() {
                    const userIndex = this.ticket.users_ordered.map(user => user.ticket_responsible.type).indexOf(6);
                    return userIndex !== -1 ? this.curr_user_id === this.ticket.users_ordered[userIndex].id : false;
                },
                isUserSender() {
                    responsible_sender_tasks = this.ticket.active_tasks.filter($t => $t.status == 31);
                    if (responsible_sender_tasks.length > 0) {
                        return (responsible_sender_tasks.find($r => $r.responsible_user_id === this.curr_user_id) || {{ json_encode(auth()->user()->isProjectManager()) }});
                    }
                    return false;
                },
                isUserReceiver() {
                    responsible_receiver_tasks = this.ticket.active_tasks.filter($t => $t.status == 32);
                    if (responsible_receiver_tasks.length > 0) {
                        return (responsible_receiver_tasks.find($r => $r.responsible_user_id === this.curr_user_id) || {{ json_encode(auth()->user()->isProjectManager()) }});
                    }
                    return false;
                },
                isUserFirstSender() {
                    sender_tasks = this.ticket.active_tasks.filter($t => $t.status == 31);
                    if (sender_tasks.length > 0 && (sender_tasks[0].responsible_user_id === this.curr_user_id || {{ json_encode(auth()->user()->isProjectManager()) }})) {
                        return true;
                    }
                    return false;
                },
                isUserFirstReceiver() {
                    receiver_tasks = this.ticket.active_tasks.filter($t => $t.status == 32);
                    if (receiver_tasks.length > 0 && (receiver_tasks[0].responsible_user_id === this.curr_user_id || {{ json_encode(auth()->user()->isProjectManager()) }})) {
                        return true;
                    }
                    return false;
                },
                canManageComment(comment) {
                    return comment.author_id === this.curr_user_id;
                },
                canReassignUsageUser(ticket) {
                    auth_id = {{ auth()->id()}};
                    return ((ticket.active_users.find(user => user.ticket_responsible.type === 1).id === auth_id && ticket.status === 7) || auth_id === 1) ;
                },
            }
        });
    </script>
@endpush
