@if(/*$project->status === 4 and */(in_array(Auth::user()->id, $contract_resp_user_ids) or $contracts->count()))
    <div class="card">
        <div class="card-header">
            <h4 class="card-title">
                <a data-target="#collapseContract" href="#" data-toggle="collapse">
                    Договоры
                    <b class="caret"></b>
                </a>
            </h4>
        </div>
        <div id="collapseContract" class="card-collapse collapse">
            <div class="card-body card-body-table">
                <div class="card strpied-tabled-with-hover">
                    @if(in_array(Auth::user()->id, $contract_resp_user_ids))
                    <div class="fixed-table-toolbar toolbar-for-btn">
                        <div class="pull-right">
                            <button class="btn-success btn btn-round btn-outline btn-sm add-btn" data-toggle="modal" data-target="#create-contract">
                                <i class="fa fa-plus"></i>
                                Новый договор
                            </button>
                        </div>
                    </div>
                    @endif
                    @if($contracts->count())
                        <div class="table-responsive">
                            <table class="table table-hover mobile-table">
                                <thead>
                                <tr>
                                    <th>Внешний №</th>
                                    <th>Тип</th>
                                    <th>Контрагент</th>
                                    <th>Дата добавления</th>
                                    <th class="text-center">Версия</th>
                                    <th>Статус</th>
                                    <th>Дата КС
                                        <button type="button" name="button" class="btn btn-link btn-primary btn-xs mn-0 pd-0" data-container="body"
                                                data-toggle="popover" data-placement="top" data-content="Ближайшая дата сдачи КС. В скобках указана дата, когда система начнёт посылать уведомления об отсутствующих сертификатах. Даты, выделенные жирным шрифом, уже нельзя редактировать." style="position:absolute;">
                                            <i class="fa fa-info-circle"></i>
                                        </button>
                                    </th>
                                    <th class="text-center">
                                        Заявки</th>
                                    <th class="text-right">
                                        Действия</th>
                                </tr>
                                </thead>
                                <tbody>
                                @foreach($contracts->where('main_contract_id', '') as $parent)
                                    @include('projects.modules.contract_row', ['contract' => $parent])
                                    @foreach($contracts->where('main_contract_id', $parent->id) as $contract)
                                        @include('projects.modules.contract_row')
                                    @endforeach
                                @endforeach
                                </tbody>
                            </table>
                        </div>
                    @endif
                </div>
                <!-- Таблица заявок -->
                @if($contracts->count())
                    <div class="card strpied-tabled-with-hover" >
                        <div class="fixed-table-toolbar toolbar-for-btn">
                            @if($contract_requests->whereIn('contract_id', $contracts->whereIn('status', [1,2,4])->pluck('id'))->where('status', 1)->count() > 0)<h6 style="padding-top:15px;" class="pull-left">Текущие заявки</h6>@endif

                            @if(in_array(Auth::id(), array_diff($resp_users->pluck('id')->unique()->toArray(), $contract_resp_user_ids)))
                                <div class="pull-right">
                                    <button class="btn-success btn btn-round btn-outline btn-sm add-btn" data-toggle="modal" data-target="#edit-contract">
                                        <i class="fa fa-plus"></i>
                                        Заявка на редактирование
                                    </button>
                                </div>
                            @endif
                        </div>
                        @if($contracts->count() and $contract_requests->count())
                            @if($contract_requests->whereIn('contract_id', $contracts->whereIn('status', [1,2,4])->pluck('id'))->where('status', 1)->count() > 0)
                                <div class="table-responsive">
                                    <table class="table table-hover mobile-table">
                                        <thead>
                                        <tr>
                                            <th>Название</th>
                                            <th>Договор</th>
                                            <th>Автор</th>
                                            <th>Дата</th>
                                            <th class="text-right">
                                                Действия</th>
                                        </tr>
                                        </thead>
                                        <tbody>
                                        @foreach($contract_requests->whereIn('contract_id', $contracts->whereIn('status', [1,2,4])->pluck('id'))->where('status', 1) as $contract_request)
                                            <tr>
                                                <td data-label="Название">{{ $contract_request->name }}</td>
                                                <td data-label="Договор">{{ $contract_request->contract_name }}  № {{ $contract_request->contract_number ? $contract_request->contract_number : $contract_request->contract_id }}</td>
                                                <td data-label="Автор">
                                                    @if($contract_request->user_id)
                                                        {{ $contract_request->last_name }}
                                                        {{ $contract_request->first_name }}
                                                        {{ $contract_request->patronymic }}
                                                    @else
                                                        Система
                                                    @endif
                                                </td>
                                                <td data-label="Дата" class="prerendered-date-time">{{ $contract_request->updated_at }}</td>
                                                <td data-label="" class="text-right actions">
                                                    <button  rel="tooltip" type="button" class="btn btn-info btn-link btn-xs padding-actions mn-0" data-toggle="modal" data-target="#view-contract-request-{{ $contract_request->id }}" data-original-title="Просмотр">
                                                        <i class="fa fa-eye"></i>
                                                    </button>
                                                </td>
                                            </tr>
                                        @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            @endif
                        @endif
                    </div>
                </div>
            @endif
        </div>
    </div>

    <div class="card">
        <div class="card-header">
            <h4 class="card-title">
                <a data-target="#collapse4" href="#" data-toggle="collapse">
                    Ключевые даты договоров
                    <b class="caret"></b>
                </a>
            </h4>
        </div>
        <div id="collapse4" class="card-collapse collapse">
            <div class="card-body">
                <div id="editableTable" class="table-responsive">
                    <!-- <button class="btn btn-primary" id="submit_data">Submit</button> -->
                    <table class="table table-hover mobile-table">
                        <thead>
                        <tr>
                            <th>Внешний №</th>
                            <th>Тип</th>
                            <th>Дата добавления</th>
                            <th class="text-center">Версия</th>
                            <th>Статус</th>
                            <th class="text-right">Действия</th>
                        </tr>
                        </thead>
                        <tbody>
                        @foreach($contracts->groupBy('contract_id')->sortByDesc('version') as $contract_group)
                            @foreach($contract_group as $contract)
                                @if($contract->key_dates->count())
                                    <tr>
                                        <td data-label="Внешний №" data-target=".collapseKeyDates{{ $contract->contract_id}}" data-toggle="collapse" class="collapsed tr-pointer" aria-expanded="false">{{ ($contract->foreign_id)? $contract->foreign_id : 'Отсутствует' }}
                                            @if(isset($contract->main_contract_id))
                                                <br>
                                                <a href="{{ route('projects::contract::card', [$contract->project_id, $contract->main_contract_id]) }}" class="table-link" >
                                                    <u>Осн. договор: {{ $contract->main_contract->name_for_humans }} </u>
                                                </a>
                                            @endif
                                        </td>
                                        <td data-label="Тип" data-target=".collapseKeyDates{{ $contract->contract_id}}" data-toggle="collapse" class="collapsed tr-pointer" aria-expanded="false">
                                            {{ $contract->name }}
                                        </td>
                                        <td data-label="Дата добавления" data-target=".collapseKeyDates{{ $contract->contract_id}}" data-toggle="collapse" class="collapsed tr-pointer prerendered-date-time" aria-expanded="false">{{$contract->updated_at}}</td>
                                        <td data-label="Версия" class="text-center" data-target=".collapseKeyDates{{ $contract->contract_id}}" data-toggle="collapse" class="collapsed tr-pointer" aria-expanded="false">
                                            {{ $contract->version }}
                                        </td>
                                        <td data-label="Статус" data-target=".collapseKeyDates{{ $contract->contract_id}}" data-toggle="collapse" class="collapsed tr-pointer" aria-expanded="false">{{ $contract->contract_status[$contract->status] }}</td>
                                        <td data-label="" class="text-right actions">
                                            <a href="{{ route('projects::contract::card', [$contract->project_id, $contract->id]) }}" rel="tooltip" class="btn-link btn-xs btn btn-open padding-actions mn-0" data-original-title="Открыть">
                                                <i class="fa fa-share-square-o"></i>
                                            </a>
                                        </td>
                                    </tr>
                                    <tr class="collapseKeyDates{{ $contract->contract_id }} contact-note card-collapse collapse activity-detailed keyDatesIssue">
                                        <th>#</th>
                                        <th>Наименование</th>
                                        <th>Сумма </th>
                                        <th>Дата c</th>
                                        <th>Дата по</th>
                                        <th>Примечание </th>
                                    </tr>
                                    @foreach($contract->key_dates as $key => $key_date)
                                        <tr class="collapseKeyDates{{ $contract->contract_id }} contact-note card-collapse collapse activity-detailed">
                                            <td data-label="#">{{ $key + 1 }}</td>
                                            <td data-label="Наименование" >{{ $key_date->name }}</td>
                                            <td data-label="Сумма">{{ $key_date->sum ? round($key_date->sum, 2) : '' }}</td>
                                            <td data-label="Дата с" class="prerendered-date">{{ $key_date->date_from ? $key_date->date_from->format('d.m.Y') : '' }}</td>
                                            <td data-label="Дата по" class="prerendered-date">{{ $key_date->date_to ? $key_date->date_to->format('d.m.Y') : '' }}</td>
                                            <td data-label="Примечание">{{ $key_date->note }}</td>
                                        </tr>
                                        @if ($key_date->related_key_dates->count())
                                            @foreach($key_date->related_key_dates as $skey => $related_key_date)
                                                <tr class="collapseKeyDates{{ $contract->contract_id }} contact-note card-collapse collapse activity-detailed">
                                                    <td data-label="#">{{ ($key + 1) . '.' . ($skey + 1) }}</td>
                                                    <td data-label="Наименование">{{ $related_key_date->name }}</td>
                                                    <td data-label="Сумма">{{ $related_key_date->sum ? round($related_key_date->sum, 2) : '' }}</td>
                                                    <td data-label="Дата с" class="prerendered-date">{{ $related_key_date->date_from ? $related_key_date->date_from->format('d.m.Y') : '' }}</td>
                                                    <td data-label="Дата по" class="prerendered-date">{{ $related_key_date->date_to ? $related_key_date->date_to->format('d.m.Y') : '' }}</td>
                                                    <td data-label="Примечание">{{ $related_key_date->note }}</td>
                                                </tr>
                                            @endforeach
                                        @endif
                                    @endforeach
                                @endif
                                @break
                            @endforeach
                        @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
@endif

<div class="modal fade bd-example-modal-lg show" id="create-contract" role="dialog" aria-labelledby="modal-search" style="display: none;">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    Создание нового договора
                </h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">×</span>
                </button>
            </div>
            <div class="modal-body">
                <hr style="margin-top:0">
                <div class="card border-0" >
                    <div class="card-body ">
                        <form id="create_contract_form" class="form-horizontal" action="{{ route('projects::contract::store', $project->id) }}" method="post" enctype="multipart/form-data">
                            @csrf
                            <div class="row">
                                <div class="col-sm-12">
                                    <div class="form-group select-accessible-75">
                                        <label>Тип<star class="star">*</star></label>
                                        <select name="name" id="create_contract_choose_name" class="selectpicker js-select-type select-accessible-4" data-title="Выберите тип договора" data-style="btn-default btn-outline" data-menu-style="dropdown-blue" style="width: 100%" required>
                                            <option value="Договор с заказчиком">Договор с заказчиком</option>
                                            <option value="Договор поставки">Договор поставки</option>
                                            <option value="Договор субподряда">Договор субподряда</option>
                                            <option value="Договор услуг">Договор услуг</option>
                                            <option value="Договор на оформление проекта">Договор на оформление проекта</option>
                                            <option value="Договор на аренду техники">Договор на аренду техники</option>
                                            <option value="Иное">Иное</option>
                                            @if ($contracts->where('main_contract_id', '!=', 'null')->whereIn('status', [5, 6])->count() > 0) <option value="Доп. соглашение">Доп. соглашение</option> @endif
                                        </select>
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-sm-12">
                                    <div class="form-group">
                                        <label>Коммерческие предложения<star id="create_contract_attach_co_star" class="star">*</star></label>
                                        <select name="offer_ids[]" id="create_contract_attach_co" multiple class="selectpicker" data-title="Выберите КП" data-style="btn-default btn-outline" data-menu-style="dropdown-blue" required style="width: 100%">
                                            @foreach($com_offers_options as $offer)
                                            <option value="{{ $offer->id }}">{{ $offer->option ? $offer->option: (($offer->is_tongue ? 'Шпунт' : 'Сваи') . ': Без наименования') }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                            </div>
                            <div id="main_contract_select_row" class="row d-none">
                                <div class="col-sm-12">
                                    <div class="form-group">
                                        <label>Основной договор<star class="star">*</star></label>
                                        <select name="main_contract_id" onchange="setInternalId(this)" class="selectpicker js-select-main-contract select-accessible-4" data-style="btn-default btn-outline" data-menu-style="dropdown-blue" style="width: 100%">
                                            <option value=""></option>
                                            @foreach($contracts->where('main_contract_id', null)->whereIn('status', [5, 6]) as $contract)
                                                <option value="[{{ $contract->id }},{{ $contract->contract_id }}]">{{ $contract->name_for_humans  }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                            </div>
                            <div id="contract_type_name" class="row d-none">
                                <div class="col-sm-8">
                                    <div class="form-group">
                                        <label>Тип договора<star class="star">*</star></label>
                                        <input name="contract_type_name" placeholder="Введите название договора" type="text" class="form-control">
                                    </div>
                                </div>
                                <div class="col-sm-4">
                                    <div class="row">
                                        <div class="col-sm-2">
                                            <div class="form-check">
                                                <label class="form-check-label">
                                                    <input id="material_needed" class="form-check-input" type="checkbox">
                                                    <span class="form-check-sign"></span>
                                                </label>
                                            </div>
                                        </div>
                                        <div class="col-sm-10">
                                            <div class="form-check">
                                                <label>Договор включает исполнителя<star class="star">*</star></label>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div id="contractor_select_row" class="row d-none">
                                <div class="col-sm-12">
                                    <div class="form-group">
                                        <label id="subcontractor_role">Выберите исполнителя<star class="star">*</star></label>
                                        <select id="js-select-subcontractor" name="subcontractor_id" style="width:100%;" data-style="btn-default btn-outline" data-menu-style="dropdown-blue">
                                            <option value=""></option>
                                        </select>
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-sm-4">
                                    <label>
                                        Внутренний номер
                                    </label>
                                    <input id="internalId" placeholder="" disabled class="form-control" value="{{ $next_contract_id }}">
                                </div>
                                <div class="col-sm-4">
                                    <div class="form-group">
                                        <label>Внешний номер</label>
                                        <input name="foreign_id" placeholder="" class="form-control">
                                    </div>
                                </div>
                            </div>
                            <div class="row" id="ks_date_field">
                                <div class="col-sm-4">
                                    <div class="form-group">
                                        <label>Число сдачи КС
                                            <button type="button" name="button" class="btn btn-link btn-primary btn-xs mn-0 pd-0" data-container="body"
                                                    data-toggle="popover" data-placement="top" data-content="Каждый месяц до указанного числа необходимо прикрепить сертификаты на поставленный материал" style="position:absolute;">
                                                <i class="fa fa-info-circle"></i>
                                            </button>
                                        </label>
                                        <input name="ks_date" min="1" max="31" type="number" class="form-control" placeholder="Укажите число">
                                    </div>
                                </div>
                                <div class="col-sm-5">
                                    <div class="form-group">
                                        <label>За сколько дней  начинать уведомлять
                                            <button type="button" name="button" class="btn btn-link btn-primary btn-xs mn-0 pd-0" data-container="body"
                                                    data-toggle="popover" data-placement="top" data-content="Число показывает, за сколько дней до дня сдачи КС система начнёт присылать уведомления об отсутствующих сертификатах (каждый день)" style="position:absolute;">
                                                <i class="fa fa-info-circle"></i>
                                            </button>
                                        </label>
                                        <input name="start_notifying_before" min="1" max="31" type="number" class="form-control" placeholder="Укажите число" value="{{ $contract->start_notifying_before ?? 10 }}">
                                    </div>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Закрыть</button>
                <button id="submit_contract_request" type="submit" {{-- onclick="this.form.submit(); this.disabled=true;" --}} form="create_contract_form" class="btn btn-info">Сохранить</button>
            </div>
        </div>
    </div>
</div>
@if($contracts->count())

    @foreach($contract_requests as $contract_request)
        @if(isset($contract_request->contract_id) and isset($contracts->find($contract_request->contract_id)->name))
            <div class="modal fade bd-example-modal-lg show" id="view-contract-request-{{ $contract_request->id }}" role="dialog" aria-labelledby="modal-search" style="display: none;">
                <div class="modal-dialog modal-lg" role="document">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title">
                                Заявка на редактирование договора
                            </h5>
                            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                <span aria-hidden="true">×</span>
                            </button>
                        </div>
                        <div class="modal-body">
                            <hr style="margin-top:0">
                            <div class="card border-0" >
                                <div class="card-body ">
                                    <div class="row">
                                        <div class="col-sm-12">
                                            <div class="form-group">
                                                <label>Название</label>
                                                <p>{{ $contract_request->name }}</p>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="row">
                                        <div class="col-sm-12">
                                            <div class="form-group">
                                                <label>Договор для редактирования</label>
                                                <p>{{ $contracts->find($contract_request->contract_id)->name_for_humans }}</p>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="row">
                                        <div class="col-sm-12">
                                            <div class="form-group">
                                                <label>Описание</label>
                                                <p>{{ $contract_request->description }}</p>
                                            </div>
                                        </div>
                                    </div>
                                    @if ($contract_request->files->where('is_result', 0)->count() > 0)
                                        <div class="row">
                                            <div class="col-md-12">
                                                <label class="control-label">Приложенные файлы</label>
                                                <br>
                                                @foreach($contract_request->files->where('is_result', 0)->where('is_proj_doc', 0) as $file)
                                                    <a target="_blank" href="{{ asset('storage/docs/contract_request_files/' . $file->file_name) }}">
                                                        {{ $file->original_name }}
                                                    </a>
                                                    <br>
                                                @endforeach

                                                @foreach($contract_request->files->where('is_result', 0)->where('is_proj_doc', 1) as $file)
                                                    <a target="_blank" href="{{ asset('storage/docs/project_documents/' . $file->file_name) }}">
                                                        {{ $file->original_name }}
                                                    </a>
                                                    <br>
                                                @endforeach
                                            </div>
                                        </div>
                                    @endif
                                </div>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-dismiss="modal">Закрыть</button>
                        </div>
                    </div>
                </div>
            </div>
        @endif
    @endforeach

    @foreach($contracts as $contract)
        <form id="approve_contract" action="{{ route('projects::contract::approve', [$contract->project_id, $contract->id]) }}" method="post">@csrf
            <input name="contract_id" type="hidden" id="approved_contract_id">
        </form>

        <div class="modal fade bd-example-modal-lg show" id="signing-contract-{{ $contract->id }}" role="dialog" aria-labelledby="modal-search" style="display: none;">
            <div class="modal-dialog modal-lg" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Подтверждение подписания</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">×</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <hr style="margin-top:0">
                        <div class="card border-0" >
                            <div class="card-body ">
                                <form id="add_files-{{ $contract->id }}" class="form-horizontal" action="{{ route('projects::contract::add_files', $contract->id) }}" method="post" enctype="multipart/form-data">
                                    @csrf
                                    <input type="hidden" name="contract_id" value="{{ $contract->id }}">
                                    <div class="row">
                                        <div class="col-md-3">
                                            <label>Вид документа<star class="star">*</star></label>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <select name="type" class="selectpicker" data-title="Выберите тип документа" data-style="btn-default btn-outline" data-menu-style="dropdown-blue" required>
                                                    <option value="1">Договор</option>
                                                    @if($contract->status !== 5) <option value="2">Гарантийное письмо</option> @endif
                                                </select>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="row" >
                                        <label class="col-sm-3 col-form-label" for="" style="font-size:0.80">
                                            Приложенный документ<star class="star">*</star>
                                        </label>
                                        <div class="col-sm-6">
                                            <div class="file-container">
                                                <div id="fileName" class="file-name"></div>
                                                <div class="file-upload">
                                                    <label class="pull-right">
                                                        <span><i class="nc-icon nc-attach-87" style="font-size:25px; line-height: 40px"></i></span>
                                                        <input type="file" name="document" accept="*" id="upload_document_garant" class="form-control-file file" onchange="getFileName(this)">
                                                    </label>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="row">
                                        <label class="col-sm-3 col-form-label" for="" style="font-size:0.80">
                                            <label>Число сдачи КС <star class="star">*</star>
                                                <button type="button" name="button" class="btn btn-link btn-primary btn-xs mn-0 pd-0" data-container="body"
                                                        data-toggle="popover" data-placement="top" data-content="Каждый месяц до указанного числа необходимо прикрепить сертификаты на поставленный материал" style="position:absolute;">
                                                    <i class="fa fa-info-circle"></i>
                                                </button>
                                            </label>
                                        </label>
                                        <div class="col-sm-6">
                                            <div class="form-group">
                                                <input name="ks_date" min="1" max="31" type="number" value="{{ $contract->ks_date }}" class="form-control" placeholder="Укажите число" required>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="row" >
                                        <label class="col-sm-3 col-form-label" for="" style="font-size:0.80">
                                            <label>За сколько дней  начинать уведомлять
                                                <button type="button" name="button" class="btn btn-link btn-primary btn-xs mn-0 pd-0" data-container="body"
                                                        data-toggle="popover" data-placement="top" data-content="Число показывает, за сколько дней до дня сдачи КС система начнёт присылать уведомления об отсутствующих сертификатах (каждый день)" style="position:absolute;">
                                                    <i class="fa fa-info-circle"></i>
                                                </button>
                                            </label>
                                        </label>
                                        <div class="col-sm-6">
                                            <div class="form-group">
                                                <input name="start_notifying_before" min="1" max="31" type="number" class="form-control" placeholder="Укажите число" value="{{ $contract->start_notifying_before ?? 10 }}">
                                            </div>
                                        </div>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Закрыть</button>
                        <button id="" form="add_files-{{ $contract->id }}" type="button" onclick="submit_file(this)" class="btn btn-info">Подтвердить</button>
                    </div>
                </div>
            </div>
        </div>

    @endforeach

    <div class="modal fade bd-example-modal-lg show" id="edit-contract" role="dialog" aria-labelledby="modal-search" style="display: none;">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">
                        Заявка на редактирование договора
                    </h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">×</span>
                    </button>
                </div>
                <div class="modal-body">
                    <hr style="margin-top:0">
                    <div class="card border-0" >
                        <div class="card-body ">
                            <form id="create_contract_request_form" class="form-horizontal" action="{{ route('projects::contracts::requests::store', $project->id) }}" method="post" enctype="multipart/form-data">
                                @csrf
                                <div class="row">
                                    <div class="col-sm-12">
                                        <div class="form-group">
                                            <label>Название<star class="star">*</star></label>
                                            <input class="form-control" name="name" required maxlength="500">
                                        </div>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-sm-12">
                                        <div class="form-group">
                                            <label>Договор для редактирования<star class="star">*</star></label>
                                            <select name="contract_id" id="select-contract" style="width:100%" required>
                                                <option value=""></option>
                                                @foreach($contracts as $contract)
                                                    @if(in_array($contract->status, [1,2,4]))
                                                        <option value="{{ $contract->id }}"> {{ $contract->name_for_humans  }} </option>
                                                    @endif
                                                @endforeach
                                            </select>
                                        </div>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-sm-12">
                                        <div class="form-group">
                                            <label>Описание<star class="star">*</star></label>
                                            <textarea class="form-control textarea-rows" name="description" required maxlength="500"></textarea>
                                        </div>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-sm-12">
                                        <label for="" style="font-size:0.80">
                                            Приложенные файлы
                                        </label>
                                        <div class="col-sm-6">
                                            <div class="file-container">
                                                <div id="fileName" class="file-name"></div>
                                                <div class="file-upload ">
                                                    <label class="pull-right">
                                                        <span><i class="nc-icon nc-attach-87" style="font-size:25px; line-height: 40px"></i></span>
                                                        <input type="file" name="documents[]" accept="*" id="uploadedContractRequestFiles" class="form-control-file file" onchange="getFileName(this)" multiple>
                                                    </label>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-sm-12">
                                        <label class="col-sm-6 p-0">
                                            Проектная документация
                                        </label>
                                        <div class="col-sm-6">
                                            <select class="js-select-proj-doc" name="project_documents[]" data-title="Выберите документ" data-style="btn-default btn-outline" multiple data-menu-style="dropdown-blue" style="width:100%;">
                                            </select>
                                        </div>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Закрыть</button>
                    <button id="submit_contract_request" type="submit" {{-- onclick="this.form.submit(); this.disabled=true;" --}} form="create_contract_request_form" class="btn btn-info">Сохранить</button>
                </div>
            </div>
        </div>
    </div>

@endif

@push('js_footer')
    <script>
        function submit_file(e) {
            var form  = $('#' + $(e).attr('form'));
            if (form[0].reportValidity()) {
                if (form.find("[name='document']")[0].files.length > 0) {
                    form.submit();
                }
                else {
                    swal({
                        title: 'Внимание',
                        text: "Необходимо прикрепить файл",
                        type: 'warning',
                        confirmButtonText: 'Ок',
                        timer: '3000'
                    });
                }
            }
        }

        $('#create_contract_choose_name').on('change', function(e) {
            if ($(this).val() != 'Договор с заказчиком') {
                $('#create_contract_attach_co_star').hide();
                $('#create_contract_attach_co').removeAttr('required');
                $('#ks_date_field').addClass('d-none');
            } else {
                $('#create_contract_attach_co_star').show();
                $('#create_contract_attach_co').attr('required', 'required');
                $('#ks_date_field').removeClass('d-none');
            }
        });

        $('#material_needed').on('change', function(e) {
            let $contractorSelectRow = $('#contractor_select_row');
            let $subcontractorSelect = $("#js-select-subcontractor");
            if ($(this)[0].checked) {
                $contractorSelectRow.removeClass('d-none');
                $subcontractorSelect.prop("disabled", false);
                $subcontractorSelect.attr("required", 'required');
            } else {
                $contractorSelectRow.addClass('d-none');
                $subcontractorSelect.removeAttr('required');
                $subcontractorSelect.val('').trigger('change');
            }
        });
        function setInternalId(e) {
            if ($(e).val()) {
                $('#internalId').val(JSON.parse($(e).val())[1]);
            } else {
                $('#internalId').val({{ $next_contract_id }});
            }
        }
    </script>
@endpush
