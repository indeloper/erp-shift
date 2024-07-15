@foreach($projects as $project)
@if(isset($project->last_task->id))
<div class="modal fade bd-example-modal-lg"  id="show_task_info{{ $project->last_task->id }}" tabindex="-1" role="dialog" aria-labelledby="modal-search" aria-hidden="true">
    <div class="modal-task-info modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="task-info__modal-body">
                <div class="row" style="flex-direction: row-reverse">
                    <div class="col-md-5">
                        <div class="right-bar-info">
                            <div class="row">
                                <div class="col-md-12">
                                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                        <span aria-hidden="true" style="color:#fff;font-size: 24px;font-weight: 300;">&times;</span>
                                    </button>
                                </div>
                            </div>
                            <div class="right-bar-info__item" style="margin-bottom:0;">
                                <div class="task-info__text-unit">
                                        <span class="task-info__head-title">
                                            Время создания
                                        </span>
                                    <span class="task-info__body-title">
                                            {{ $project->last_task->created_at }}
                                        </span>
                                </div>
                                <div class="task-info__text-unit">
                                        <span class="task-info__head-title">
                                            Автор
                                        </span>
                                    @if($project->last_task->user_id)
                                        <a class="task-info__link task-info__body-title" href="{{ route('users::card', $project->last_task->user_id) }}" style="font-size:14px">
                                            {{ $project->last_task->user->full_name }}
                                        </a>
                                    @else
                                        <span class="task-info__body-title">
                                            СИСТЕМА
                                        </span>
                                    @endif
                                </div>
                            </div>
                            <div class="right-bar-info__item" style="margin-bottom:0;">
                                @if($project->last_task->status > 2)
                                    <div class="task-info__text-unit">
                                            <span class="task-info__head-title">
                                                Адрес объекта
                                            </span>
                                        <span class="task-info__body-title" @if($project->last_task->project_id) href="{{ route('projects::card', $project->last_task->project_id) }}" @endif>{{ $project->last_task->object_address ? $project->last_task->object_address : 'Проект не указан' }}</span>
                                    </div>
                                @endif
                                <div class="task-info__text-unit">
                                        <span class="task-info__head-title">
                                            Проект
                                        </span>
                                    <span class="task-info__body-title">
                                            <a href="{{ route('projects::card', $project->last_task->project_id) }}" class="task-info__link task-info__body-title" target="_blank">{{ $project->last_task->project_name }}</a>
                                        </span>
                                </div>
                                <div class="task-info__text-unit">
                                        <span class="task-info__head-title">
                                            Контрагент
                                        </span>
                                    <a class="task-info__link task-info__body-title" @if($project->last_task->contractor_id) href="{{ route('contractors::card', $project->last_task->contractor_id) }}" @endif>{{ $project->last_task->contractor_name ? $project->last_task->contractor_name : 'Контрагент не выбран' }}</a>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-7">
                        <div class="left-bar-main">
                            <h5 class="task-info__title">{{ $project->last_task->name }}</h5>
                            <div class="row">
                                <div class="col-md-12">
                                    <h6>
                                        Описание
                                    </h6>
                                    @if($project->last_task->status == 1)
                                        <p class="task-info__ml">{{ $project->last_task->description }}</p>
                                    @endif
                                    @if($project->last_task->status > 2)
                                        <p class="task-info__ml">
                                            @if(in_array($project->last_task->status, [3,4]))
                                                Необходимо произвести расчет объемов работ по проекту
                                            @elseif($project->last_task->status == 5)
                                                Необходимо сформировать коммерческое предложение на основании данных объёмов работ
                                            @elseif(in_array($project->last_task->status, [6,16]))
                                                Укажите результат согласования коммерческого предложения
                                            @elseif($project->last_task->status == 7)
                                                Необходимо сформировать/изменить договор на основании коммерческих предложений
                                            @elseif($project->last_task->status == 8)
                                                Необходимо согласовать договор
                                            @elseif($project->last_task->status == 9)
                                                Необходимо приложить подписанный договор или гарантийное письмо
                                            @elseif($project->last_task->status == 10)
                                                Необходимо подтвердить подписание договора
                                            @elseif($project->last_task->status == 11)
                                                Необходимо подтвердить согласование договора с заказчиком
                                            @elseif($project->last_task->status == 12)
                                                Сформирована новая версия коммерческого предложения. Необходимо провести контроль изменений и при необходимости исправить существующие или сформировать новые
                                            @elseif($project->last_task->status == 14)
                                                Необходимо назначить исполнителя на расчет объемов
                                            @elseif($project->last_task->status == 15)
                                                Необходимо назначить исполнителя на формирование коммерческого предложения
                                            @elseif($project->last_task->status == 17)
                                                Ваша заявка на объём работ была отклонена. Вы можете отказаться от заявки или отправить её повторно (заявку можно изменить)
                                            @elseif($project->last_task->status == 18)
                                                Ознакомьтесь с приведенным ОР, согласуйте или отклоните его
                                            @endif
                                            {{-- Описание из задачи (Пример: Необходимо сформировать коммерческое предложение на основании данных объёмов работ).--}}
                                        </p>
                                    @endif
{{--                                    comment because this task isn't work now--}}
{{--                                    @if($project->last_task->status == 2)--}}
{{--                                        @if($project->last_task->description)<p class="task-info__ml">{{ $project->last_task->description }}</p>@endif--}}
{{--                                        @if($project->last_task->status == 2)--}}
{{--                                            @php $local_contact = $contacts->where('phone_number', $project->last_task->incoming_phone)->first(); @endphp--}}
{{--                                            @if($local_contact)--}}
{{--                                                <p style="font-size:14px;">--}}
{{--                                                    Исходящий звонок от:--}}
{{--                                                    {{ $local_contact->last_name ? $local_contact->last_name : $local_contact->last_name }}--}}
{{--                                                    {{ $local_contact->first_name ? $local_contact->first_name : $local_contact->first_name }} {{ $local_contact->patronymic ? $local_contact->patronymic : $local_contact->patronymic }},--}}
{{--                                                    {{ $local_contact->position }}, {{ $local_contact->phone_number }}--}}
{{--                                                </p>--}}
{{--                                            @elseif($project->last_task->incoming_phone && !$local_contact)--}}
{{--                                                <p style="font-size:14px;">Звонок с телефона: {{ $project->last_task->incoming_phone }}</p>--}}
{{--                                            @else--}}
{{--                                                <p style="font-size:14px;">Отсутствует</p>--}}
{{--                                            @endif--}}
{{--                                        @endif--}}
{{--                                    @endif--}}
                                    @if($project->last_task->status > 2)
                                        <div class="row">
                                            <div class="col-md-12">
                                                <div class="left-bar-main_unit">
                                                        <span class="task-info_label" style="margin-top:5px;">
                                                            Ссылки
                                                        </span>
                                                    @if($project->last_task->status > 2 and $project->wvs->whereIn('status', [1,2]))
                                                        @foreach($project->wvs->whereIn('status', [1,2]) as $wv)
                                                            <a class="task-info_file" target="_blank" href="{{ route('projects::work_volume::card_' . ($wv->type ? 'pile': 'tongue') , [$project->last_task->project_id, $wv->id]) }}">Карточка объема работ {{ $wv->type ?'(Свая)': '(Шпунт)' }}</a>
                                                        @endforeach
                                                    @endif
                                                    @if($project->last_task->status > 4 and $project->com_offers->whereIn('status', [1,2,4]))
                                                        @foreach($project->com_offers->whereIn('status', [1,2,4]) as $offer)
                                                            <a class="task-info_file" target="_blank" href="{{ route('projects::commercial_offer::card_'. ($offer->is_tongue ? 'tongue' : 'pile'), [$project->last_task->project_id, $offer->id]) }}">Карточка коммерческого предложения {{ $offer->is_tongue ? '(Шпунт)' : '(Свая)' }}</a>
                                                        @endforeach
                                                    @endif
                                                    @if(in_array($project->last_task->status, $project->last_task::CONTR_STATUS))
                                                        <a class="task-info_file" target="_blank" href="{{ $project->last_task->target_id }}">
                                                            Карточка договора
                                                        </a>
                                                    @endif
                                                </div>
                                            </div>
                                        </div>
                                    @endif
                                </div>
                            </div>
                            @if(!$project->last_task->redirects->where('task_id', $project->last_task->id)->isEmpty())
                                @foreach($project->last_task->redirects->where('task_id', $project->last_task->id) as $task_redirect)
                                    <div class="row">
                                        <div class="col-sm-3">
                                            <p><b>Исполнитель изменен {{ $task_redirect->created_at }}</b></p>
                                        </div>
                                        <div class="col-sm-9">
                                            <p style="padding-left:20px;">
                                                Предыдущий исполнитель : {{ $project->last_task->responsible_user->where('id', $task_redirect->old_user_id)->first()->long_full_name }}
                                            <p style="padding-left:20px;">
                                                Новый исполнитель : {{ $project->last_task->responsible_user->where('id', $task_redirect->responsible_user_id)->first()->long_full_name }}
                                            </p>
                                            <p style="padding-left:20px;">Комментарий: {{ $task_redirect->redirect_note }}</p>
                                        </div>
                                    </div>
                                @endforeach
                            @endif
                            @if(!$project->last_task->task_files->where('task_id', $project->last_task->id)->isEmpty())
                                <div class="row">
                                    <div class="col-md-12">
                                        <div class="left-bar-main_unit">
                                                <span class="task-info_label" style="margin-top:5px;">
                                                    Приложенные файлы
                                                </span>
                                            @foreach($project->last_task->task_files->where('task_id', $project->last_task->id) as $task_file)
                                                <a class="task-info_file" target="_blank" href="{{ asset('storage/docs/task_files/' . $task_file->file_name) }}" data-original-title=="{{ $task_file->created_at }} {{ $task_file->full_name }}">
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
                <div class="row" style="flex-direction: row-reverse">
                    <div class="col-md-5">
                        <div class="right-bar-info__item close-date" >
                            <div class="task-info__text-unit">
                                    <span class="task-info__head-title">
                                        Установленный срок исполнения
                                    </span>
                                <span class="task-info__body-title">
                                        {{ $project->last_task->expired_at }}
                                    </span>
                            </div>
                            @if($project->last_task->is_solved)
                                <div class="task-info__text-unit">
                                        <span class="task-info__head-title">
                                            Время закрытия
                                        </span>
                                    <span class="task-info__body-title">
                                            {{ $project->last_task->updated_at }}
                                        </span>
                                </div>
                                <div class="task-info__text-unit">
                                        <span class="task-info__head-title">
                                            Исполнитель
                                        </span>
                                    <span class="task-info__body-title">
                                            @if($project->last_task->responsible_user_id)
                                            @if($project->last_task->status == 1 || $project->last_task->status == 2)
                                                {{ $project->last_task->responsible_user->long_full_name }}
                                            @else
                                                {{ $project->last_task->responsible_user->find($project->last_task->responsible_user_id)->last_name }}
                                                {{ $project->last_task->responsible_user->find($project->last_task->responsible_user_id)->first_name }}
                                                {{ $project->last_task->responsible_user->find($project->last_task->responsible_user_id)->patronymic }}
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
                            @if($project->last_task->is_solved)
                                <div class="row task-info__result">
                                    <div class="col-md-12">
                                        <h6 style="margin-top:5px;">
                                            Результат
                                        </h6>
                                        @if($project->last_task->status == 1)
                                            <p class="task-info__ml">
                                                Задача выполнена
                                            </p>
                                        @endif
                                        @if($project->last_task->status > 2)
                                            <p class="task-info__ml">
                                                @if(in_array($project->last_task->status, [3,4]))
                                                    {{ $project->last_task->descriptions[$project->last_task->status] }}
                                                @elseif($project->last_task->status == 5)
                                                    {{ $project->last_task->descriptions[$project->last_task->status] }}
                                                @elseif($project->last_task->status == 6)
                                                    {{ $project->last_task->final_note ? $project->last_task->final_note : 'Задача решена' }}
                                                @elseif($project->last_task->status == 7)
                                                    {{ $project->last_task->descriptions[$project->last_task->status] }}
                                                @elseif($project->last_task->status == 8)
                                                    {{ $project->last_task->final_note ? $project->last_task->final_note : 'Задача решена' }}
                                                @elseif($project->last_task->status == 9)
                                                    {{ $project->last_task->final_note ? $project->last_task->final_note : 'Задача решена' }}
                                                @elseif($project->last_task->status == 10)
                                                    {{ $project->last_task->final_note ? $project->last_task->final_note : 'Задача решена' }}
                                                @elseif($project->last_task->status == 11)
                                                    {{ $project->last_task->final_note ? $project->last_task->final_note : 'Задача решена' }}
                                                @elseif($project->last_task->status == 12)
                                                    {{ $project->last_task->descriptions[$project->last_task->status] }}
                                                @elseif($project->last_task->status == 14)
                                                    {{ $project->last_task->final_note ? $project->last_task->final_note : 'Задача решена' }}
                                                @elseif($project->last_task->status == 15)
                                                    {{ $project->last_task->final_note ? $project->last_task->final_note : 'Задача решена' }}
                                                @elseif($project->last_task->status == 16)
                                                    {{ $project->last_task->final_note ? $project->last_task->final_note : 'Задача решена' }}
                                                @elseif($project->last_task->status == 17)
                                                    {{ $project->last_task->final_note ? $project->last_task->final_note : 'Задача решена' }}
                                                @elseif($project->last_task->status == 18)
                                                    {{ $project->last_task->final_note ? $project->last_task->final_note : 'Задача решена' }}
                                                @endif
                                                {{-- Результат задачи в зависимости из исхода решения задачи. (Пример: Произведен расчет объемов по проекту )--}}
                                            </p>
                                        @endif
                                    </div>
                                </div>
                                @if($project->last_task->final_note and !in_array($project->last_task->status, [6, 8, 9, 10, 11, 14, 15, 16, 17, 18]))
                                    <div class="row">
                                        <div class="col-md-12">
                                            <div class="left-bar-main_unit">
                                                    <span class="task-info_label" style="margin-top:5px;">
                                                        Комментарий:
                                                    </span>
                                                <p>
                                                    {{ $project->last_task->final_note }}
                                                </p>
                                            </div>
                                        </div>
                                    </div>
                                @endif
                                @if(!$project->last_task->status == 2)
                                    <div class="row">
                                        <div class="col-md-12">
                                            <div class="left-bar-main_unit">
                                                    <span class="task-info_label" style="margin-top:5px;">
                                                        Приложенные файлы
                                                    </span>
                                                <a href="#" class="task-info_file" target="_blank">
                                                    Коммерческое предложение.pdf
                                                </a>
                                                @if($project->last_task->status > 6 and $project->com_offers->whereIn('status', [1,2,4])->where('file_name', '!=', null))
                                                    @foreach($project->com_offers->whereIn('status', [1,2,4])->where('file_name', '!=', null) as $offer)
                                                        <a class="task-info_file" target="_blank" href="{{ asset('storage/docs/commercial_offers/' . $offer->file_name) }}">
                                                            Подписанное коммерческое предложение (файл)
                                                        </a>
                                                    @endforeach
                                                @endif
                                            </div>
                                        </div>
                                    </div>
                                @endif
                            @endif
                            <div class="row" style="margin-top:40px">
                                <div class="col-md-12">
                                    <button type="button" class="btn btn-sm btn-secondary" data-dismiss="modal">Закрыть</button>
                                    <a href="{{ route('projects::tasks', $project->id) }}" class="btn btn-sm btn-secondary pull-right">События проекта</a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endif
@endforeach
