{{--example of using it in valuation.blade.php string num 108--}}
{{--also in controller TaskValuationController method valuation--}}


<tbody>
@foreach($solved_tasks as $solved_task)
    <tr
            class="@if(!$solved_task->is_solved)
                        @if ($solved_task->is_overdue()) overdue-task
                            @elseif($solved_task->is_seen != 1) new-task
                        @endif
                    @else
                    solved-task @endif"
    >
        <td
                data-label="Дата создания"
                data-target="#show_task_info{{ $solved_task->id }}"
                data-toggle="modal"
                class="collapsed tr-pointer prerendered-date-time"
                aria-expanded="false"
        >
            {{ $solved_task->created_at }}
        </td>
        <td
                data-label="Дата исполнения"
                data-target="#show_task_info{{ $solved_task->id }}"
                data-toggle="modal"
                class="collapsed tr-pointer prerendered-date-time"
                aria-expanded="false"
        >
            @if($solved_task->is_solved)
                {{ $solved_task->updated_at }}
            @else
                -
            @endif
        </td>
        <td
                data-label="Событие"
                data-target="#show_task_info{{ $solved_task->id }}"
                data-toggle="modal"
                class="collapsed tr-pointer"
                aria-expanded="false"
        >
            {{ $solved_task->name }}
        </td>
        @if($solved_tasks->groupBy('project_id')->count() > 1)
            <td data-label="Проект">
                {{ $solved_task->project_name }}
            </td>
        @endif
        <td data-label="Исполнитель">
            @if($solved_task->responsible_user_id)
                <a
                        href="{{ route('users::card', $solved_task->responsible_user_id) }}"
                        class="table-link"
                >
                    {{ $solved_task->responsible_user->long_full_name }}
                </a>
            @else
                Система
            @endif
        </td>
        <td data-label="Автор">
            @if($solved_task->user_id)
                <a
                        href="{{ route('users::card', $solved_task->user_id) }}"
                        class="table-link"
                >
                    {{ $solved_task->user->full_name }}
                </a>
            @else
                Система
            @endif
        </td>
    </tr>

    <!-- Модалка события -->
    <div
            class="modal fade bd-example-modal-lg"
            id="show_task_info{{ $solved_task->id }}"
            role="dialog"
            aria-labelledby="modal-search"
            aria-hidden="true"
    >
        <div
                class="modal-task-info modal-dialog modal-lg"
                role="document"
        >
            <div class="modal-content">
                <div class="task-info__modal-body">
                    <div
                            class="row"
                            style="flex-direction: row-reverse"
                    >
                        <div class="col-md-5">
                            <div class="right-bar-info">
                                <div class="row">
                                    <div class="col-md-12">
                                        <button
                                                type="button"
                                                class="close"
                                                data-dismiss="modal"
                                                aria-label="Close"
                                        >
                                            <span
                                                    aria-hidden="true"
                                                    style="color:#fff;font-size: 24px;font-weight: 300;"
                                            >&times;</span>
                                        </button>
                                    </div>
                                </div>
                                <div
                                        class="right-bar-info__item"
                                        style="margin-bottom:0;"
                                >
                                    <div class="task-info__text-unit">
                                            <span class="task-info__head-title">
                                                Время создания
                                            </span>
                                        <span class="task-info__body-title">
                                                {{ $solved_task->created_at }}
                                            </span>
                                    </div>
                                    <div class="task-info__text-unit">
                                            <span class="task-info__head-title">
                                                Автор
                                            </span>
                                        @if($solved_task->user_id)
                                            <a
                                                    class="task-info__link task-info__body-title"
                                                    href="{{ route('users::card', $solved_task->user_id) }}"
                                                    style="font-size:14px"
                                            >
                                                {{ $solved_task->user->full_name }}
                                            </a>
                                        @else
                                            <span class="task-info__body-title">
                                                    СИСТЕМА
                                                </span>
                                        @endif
                                    </div>
                                </div>
                                <div
                                        class="right-bar-info__item"
                                        style="margin-bottom:0;"
                                >
                                    @if($solved_task->status > 2)
                                        <div class="task-info__text-unit">
                                                <span class="task-info__head-title">
                                                    Адрес объекта
                                                </span>
                                            <span
                                                    class="task-info__body-title"
                                                    @if($solved_task->project_id) href="{{ route('projects::card', $solved_task->project_id) }}" @endif>{{ $solved_task->object_address ? $solved_task->object_address : 'Проект не указан' }}</span>
                                        </div>
                                    @endif
                                    <div class="task-info__text-unit">
                                            <span class="task-info__head-title">
                                                Проект
                                            </span>
                                        <span class="task-info__body-title">
                                                <a
                                                        @if($solved_task->project_id) href="{{ route('projects::card', $solved_task->project_id) }}"
                                                        @endif class="task-info__link task-info__body-title"
                                                        target="_blank"
                                                >{{ $solved_task->project_name ? $solved_task->project_name : 'Проект не выбран' }}</a>
                                            </span>
                                    </div>
                                    <div class="task-info__text-unit">
                                            <span class="task-info__head-title">
                                                Контрагент
                                            </span>
                                        <a
                                                class="task-info__link task-info__body-title"
                                                @if($solved_task->contractor_id) href="{{ route('contractors::card', $solved_task->contractor_id) }}" @endif>{{ $solved_task->contractor_name ? $solved_task->contractor_name : 'Контрагент не выбран' }}</a>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-7">
                            <div class="left-bar-main">
                                <h5 class="task-info__title">{{ $solved_task->name }}</h5>
                                <div class="row">
                                    <div class="col-md-12">
                                        <h6>
                                            Описание
                                        </h6>
                                        @if($solved_task->status == 1)
                                            <p class="task-info__ml">{{ $solved_task->description }}</p>
                                        @endif
                                        @if($solved_task->status > 2)
                                            <p class="task-info__ml">
                                                @if(in_array($solved_task->status, [3,4]))
                                                    Необходимо произвести расчет объемов работ по проекту
                                                @elseif($solved_task->status == 5)
                                                    Необходимо сформировать коммерческое предложение на основании данных
                                                    объёмов работ
                                                @elseif(in_array($solved_task->status, [6,16]))
                                                    Укажите результат согласования коммерческого предложения
                                            @if($com_offers->where('id', $solved_task->target_id)->first() && $com_offers->where('id', $solved_task->target_id)->first()->comments->count() > 0)
                                                <h6>
                                                    {{'Комментарий (' .  $com_offers->where('id', $solved_task->target_id)->first()->comments->last()->author->long_full_name . ')' }}
                                                </h6>
                                                <p> {{ $com_offers->where('id', $solved_task->target_id)->first()->comments->last()->comment }} </p>
                                            @endif
                                        @elseif($solved_task->status == 7)
                                            Необходимо сформировать/изменить договор на основании коммерческих
                                            предложений
                                        @elseif($solved_task->status == 8)
                                            Необходимо согласовать договор
                                        @elseif($solved_task->status == 9)
                                            Необходимо приложить подписанный договор или гарантийное письмо
                                        @elseif($solved_task->status == 10)
                                            Необходимо подтвердить подписание договора
                                        @elseif($solved_task->status == 11)
                                            Необходимо подтвердить согласование договора с заказчиком
                                        @elseif($solved_task->status == 12)
                                            Сформирована новая версия коммерческого предложения. Необходимо провести
                                            контроль изменений и при необходимости исправить существующие или
                                            сформировать новые
                                        @elseif($solved_task->status == 14)
                                            Необходимо назначить исполнителя на расчет объемов
                                        @elseif($solved_task->status == 15)
                                            Необходимо назначить исполнителя на формирование коммерческого предложения
                                        @elseif($solved_task->status == 17)
                                            Ваша заявка на объём работ была отклонена. Вы можете отказаться от заявкиили
                                            отправить её повторно (заявку можно изменить)
                                        @elseif($solved_task->status == 18)
                                            Ознакомьтесь с приведенным ОР, согласуйте или отклоните его
                                        @elseif($solved_task->status == 20)
                                            {{ $solved_task->description}}
                                        @elseif($solved_task->status == 37)
                                            @foreach($solved_task->changing_fields as $field)
                                                <p><b>@lang('fields.contractor.' . $field->field_name):</b></p>
                                                <p>{{ $field->old_value ?? 'Не заполнено' }}
                                                    <i class="fas fa-arrow-right"></i> {{ $field->value }}</p>
                                                @endforeach
                                                @endif
                                                {{-- Описание из задачи (Пример: Необходимо сформировать коммерческое предложение на основании данных объёмов работ).--}}
                                                </p>
                                                @endif
                                                @if($solved_task->status == 2)
                                                    @if($solved_task->description)
                                                        <p class="task-info__ml">{{ $solved_task->description }}</p>
                                                    @endif
                                                    @if($solved_task->status == 2)
                                                        @php $local_contact = $contacts->where('phone_number', $solved_task->incoming_phone)->first(); @endphp
                                                        @if($local_contact)
                                                            <p style="font-size:14px;">
                                                                Исходящий звонок от:
                                                                {{ $local_contact->last_name ? $local_contact->last_name : $local_contact->last_name }}
                                                                {{ $local_contact->first_name ? $local_contact->first_name : $local_contact->first_name }} {{ $local_contact->patronymic ? $local_contact->patronymic : $local_contact->patronymic }}
                                                                ,
                                                                {{ $local_contact->position }}
                                                                , {{ $local_contact->phone_number }}
                                                            </p>
                                                        @elseif($solved_task->incoming_phone && !$local_contact)
                                                            <p style="font-size:14px;">Звонок с
                                                                телефона: {{ $solved_task->incoming_phone }}</p>
                                                        @else
                                                            <p style="font-size:14px;">Отсутствует</p>
                                                        @endif
                                                    @endif
                                                @endif
                                                @if($solved_task->status > 2 and $solved_task->status != 37)
                                                    <div class="row">
                                                        <div class="col-md-12">
                                                            <div class="left-bar-main_unit">
                                                            <span
                                                                    class="task-info_label"
                                                                    style="margin-top:5px;"
                                                            >
                                                                Ссылки
                                                            </span>
                                                                @if($solved_task->status > 2 and $work_volumes->whereIn('status', [1,2]))
                                                                    @foreach($work_volumes->whereIn('status', [1,2])->whereIn('type', [0,1]) as $wv)
                                                                        @if($solved_task->project_id !== null)
                                                                            <a
                                                                                    class="task-info_file"
                                                                                    target="_blank"
                                                                                    href="{{ route('projects::work_volume::card_' . ($wv->type ? 'pile': 'tongue') , ['project_volume_id' => $solved_task->project_id, 'id' => $wv->id]) }}"
                                                                            >Карточка объема
                                                                                работ {{ $wv->type ?'(Свая)': '(Шпунт)' }}</a>
                                                                        @endif

                                                                    @endforeach
                                                                @endif
                                                                @if($solved_task->status > 4 and $com_offers->whereIn('status', [1,2,4]))
                                                                    @foreach($com_offers->whereIn('status', [1,2,4]) as $offer)
                                                                        @if($solved_task->project_id !== null)

                                                                            <a
                                                                                    class="task-info_file"
                                                                                    target="_blank"
                                                                                    href="{{ route('projects::commercial_offer::card_'. ($offer->is_tongue ? 'tongue' : 'pile'), ['id' => $solved_task->project_id, 'offer_id' => $offer->id]) }}"
                                                                            >Карточка коммерческого
                                                                                предложения {{ $offer->is_tongue ? '(Шпунт)' : '(Свая)' }}</a>
                                                                        @endif
                                                                    @endforeach
                                                                @endif
                                                                @if(in_array($solved_task->status, $solved_task::CONTR_STATUS))
                                                                    <a
                                                                            class="task-info_file"
                                                                            target="_blank"
                                                                            href="{{ route('projects::contract::card', [$solved_task->project_id, $solved_task->target_id]) }}"
                                                                    >
                                                                        Карточка договора
                                                                    </a>
                                                                @endif
                                                            </div>
                                                        </div>
                                                    </div>
                                                @endif
                                    </div>
                                </div>
                                @if(!$task_redirects->where('task_id', $solved_task->id)->isEmpty())
                                    @foreach($task_redirects->where('task_id', $solved_task->id) as $task_redirect)
                                        <div class="row">
                                            <div class="col-sm-3">
                                                <p><b>Исполнитель изменен {{ $task_redirect->created_at }}</b></p>
                                            </div>
                                            <div class="col-sm-9">
                                                <p style="padding-left:20px;">
                                                    Предыдущий исполнитель
                                                    : {{ $task_responsible_users->where('id', $task_redirect->old_user_id)->first()->long_full_name }}
                                                <p style="padding-left:20px;">
                                                    Новый исполнитель
                                                    : {{ $task_responsible_users->where('id', $task_redirect->responsible_user_id)->first()->long_full_name }}
                                                </p>
                                                <p style="padding-left:20px;">
                                                    Комментарий: {{ $task_redirect->redirect_note }}</p>
                                            </div>
                                        </div>
                                    @endforeach
                                @endif
                                @if(!$task_files->where('task_id', $solved_task->id)->isEmpty())
                                    <div class="row">
                                        <div class="col-md-12">
                                            <div class="left-bar-main_unit">
                                                    <span
                                                            class="task-info_label"
                                                            style="margin-top:5px;"
                                                    >
                                                        Приложенные файлы
                                                    </span>
                                                @foreach($task_files->where('task_id', $solved_task->id) as $task_file)
                                                    <a
                                                            class="task-info_file"
                                                            target="_blank"
                                                            href="{{ asset('storage/docs/task_files/' . $task_file->file_name) }}"
                                                            data-original-title=="{{ $task_file->created_at }} {{ $task_file->full_name }}"
                                                    >
                                                        {{ $task_file->original_name }}
                                                    </a>
                                                @endforeach
                                            </div>
                                        </div>
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>
                    <div
                            class="row"
                            style="flex-direction: row-reverse"
                    >
                        <div class="col-md-5">
                            <div class="right-bar-info__item close-date">
                                <div class="task-info__text-unit">
                                        <span class="task-info__head-title">
                                            Установленный срок исполнения
                                        </span>
                                    <span class="task-info__body-title">
                                            {{ $solved_task->expired_at }}
                                        </span>
                                </div>
                                @if($solved_task->is_solved)
                                    <div class="task-info__text-unit">
                                            <span class="task-info__head-title">
                                                Время закрытия
                                            </span>
                                        <span class="task-info__body-title">
                                                {{ $solved_task->updated_at }}
                                            </span>
                                    </div>
                                    <div class="task-info__text-unit">
                                            <span class="task-info__head-title">
                                                Исполнитель
                                            </span>
                                        <span class="task-info__body-title">
                                                @if($solved_task->responsible_user_id)
                                                @if($solved_task->status == 1 || $solved_task->status == 2)
                                                    {{ $solved_task->responsible_user->long_full_name }}
                                                @else
                                                    {{ $task_responsible_users->find($solved_task->responsible_user_id)->last_name }}
                                                    {{ $task_responsible_users->find($solved_task->responsible_user_id)->first_name }}
                                                    {{ $task_responsible_users->find($solved_task->responsible_user_id)->patronymic }}
                                                @endif
                                            @else
                                                Не найден
                                            @endif
                                            </span>
                                    </div>
                                @endif
                            </div>
                        </div>
                        <div class="col-md-7">
                            <div class="left-bar-main">
                                @if($solved_task->is_solved)
                                    <div class="row task-info__result">
                                        <div class="col-md-12">
                                            <h6 style="margin-top:5px;">
                                                Результат
                                            </h6>
                                            @if($solved_task->status == 1)
                                                <p class="task-info__ml">
                                                    Задача выполнена
                                                </p>
                                            @endif
                                            @if($solved_task->status > 2)
                                                @if(in_array($solved_task->status, [3,4,5,7]))
                                                    <p class="task-info__ml">
                                                        <i>{!! ($solved_task->revive_at ? "Задача была перенесена на  <b>" . date('d.m.Y' ,strtotime($solved_task->revive_at)) . '</b>' : 'Задача решена') !!} </i>
                                                    </p>
                                                    <br>
                                                    <span
                                                            class="task-info_label"
                                                            style="margin-top:5px; margin-left: 10px;"
                                                    >
                                                                Комментарий исполнителя
                                                        </span>
                                                    <p class="task-info__ml">   {{ $solved_task->descriptions[$solved_task->status] }}</p>
                                                @elseif(in_array($solved_task->status, [6,8,9,10,11,12,14,15,16,17,18,19,20]))

                                                    <p class="task-info__ml">
                                                        <i>{!!($solved_task->revive_at ? "Задача была перенесена на <b>" . date('d.m.Y', strtotime($solved_task->revive_at)) . '</b>' : 'Задача решена') !!} </i>
                                                    </p>
                                                    <br>
                                                    <span
                                                            class="task-info_label"
                                                            style="margin-top:5px; margin-left: 10px;"
                                                    >
                                                                Комментарий исполнителя
                                                        </span>
                                                    <p class="task-info__ml"> {{ ($solved_task->final_note ? $solved_task->final_note : '') }} </p>
                                                @elseif($solved_task->status == 37)
                                                    <span
                                                            class="task-info_label"
                                                            style="margin-top:5px; margin-left: 10px;"
                                                    >
                                                         {{ $solved_task->getResult }}
                                                        </span>
                                                @endif
                                                {{-- Результат задачи в зависимости из исхода решения задачи. (Пример: Произведен расчет объемов по проекту )--}}

                                            @endif
                                        </div>
                                    </div>
                                    @if($solved_task->final_note and !in_array($solved_task->status, [6, 8, 9, 10, 11, 12, 14, 15, 16, 17, 18, 19, 20]))
                                        <div class="row">
                                            <div class="col-md-12">
                                                <div class="left-bar-main_unit">
                                                        <span
                                                                class="task-info_label"
                                                                style="margin-top:5px;"
                                                        >
                                                            Комментарий:
                                                        </span>
                                                    <p>
                                                        {{ $solved_task->final_note }}
                                                    </p>
                                                </div>
                                            </div>
                                        </div>
                                    @endif
                                    @if(!$solved_task->status == 2)
                                        <div class="row">
                                            <div class="col-md-12">
                                                <div class="left-bar-main_unit">
                                                        <span
                                                                class="task-info_label"
                                                                style="margin-top:5px;"
                                                        >
                                                            Приложенные файлы
                                                        </span>
                                                    <a
                                                            href="#"
                                                            class="task-info_file"
                                                            target="_blank"
                                                    >
                                                        Коммерческое предложение.pdf
                                                    </a>
                                                    @if($solved_task->status > 6 and $com_offers->whereIn('status', [1,2,4])->where('file_name', '!=', null))
                                                        @foreach($com_offers->whereIn('status', [1,2,4])->where('file_name', '!=', null) as $offer)
                                                            <a
                                                                    class="task-info_file"
                                                                    target="_blank"
                                                                    href="{{ asset('storage/docs/commercial_offers/' . $offer->file_name) }}"
                                                            >
                                                                Подписанное коммерческое предложение (файл)
                                                            </a>
                                                        @endforeach
                                                    @endif
                                                </div>
                                            </div>
                                        </div>
                                    @endif
                                @endif
                                <div
                                        class="row"
                                        style="margin-top:40px"
                                >
                                    <div class="col-md-12">
                                        <button
                                                type="button"
                                                class="btn btn-sm btn-secondary"
                                                data-dismiss="modal"
                                        >Закрыть
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endforeach
</tbody>

@if ($task_to_show ?? false)
    @push('js_footer')
        <script>
          $(document).ready(function () {
            $('#show_task_info{{ $task_to_show->id }}').appendTo('body').modal('show');
          });
        </script>
    @endpush
@endif

@push('js_footer')
    <script>
      $(document).ready(function () {
        $('*[id^="show_task_info"]').appendTo('body');
      });
    </script>
@endpush
