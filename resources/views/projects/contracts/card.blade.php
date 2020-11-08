@extends('layouts.app')

@section('title', 'Договор')

@section('css_top')
    <style>
        .select2-container .select2-selection.select2-selection--multiple .select2-search.select2-search--inline .select2-search__field:not([placeholder='']) {
            width: 100% !important;
        }

        .bootstrap-datetimepicker-widget.dropdown-menu.bottom.open {
            height: 325px;
        }

        @media (max-width: 1025px){
            td[type=input],
            td[type=text]{
                max-width: 100%;
            }

            td[name=buttons] {
                max-width: 100%;
                width: 100%;
                display: block!important;
            }

            .mobile-table input {
                margin: 0;
            }

            td[type=number],
            td[type=from],
            td[type=to] {
                width: 100%;
                max-width: 400px
            }

            .mobile-table input {
                max-width: 100%;
                height: 37px!important;
            }

            .mobile-table td:before {
                margin-bottom: 5px;
            }

            #but_add {
                margin-top:30px
            }

            .fat-number::before,
            .fat-number {
                font-size: 23px!important;
                margin-bottom: 10px!important;
                font-weight: 600!important;
            }
        }
    </style>

@endsection

@section('content')
<div class="row">
    <div class="col-md-12">
        <div aria-label="breadcrumb" role="navigation">
            <ol class="breadcrumb">
                <li class="breadcrumb-item">
                    <a href="{{ route('projects::card', $contract->project_id) }}" class="table-link">{{ $contract->project_name }}</a>
                </li>
                <li class="breadcrumb-item active" aria-current="page">{{ $contract->name . $contract->type == 7 ? (' к договору №' . $contract->internal_id) : ( ' №' . $contract->internal_id)}}</li>
            </ol>
        </div>
    </div>
</div>
<div class="row">
    <div class="col-xl-12 ml-auto mr-auto">
        <div class="card">
            <div class="card-header">
                <div class="row" style="padding:7px 2px 9px 2px">
                    <div class="col-md-7">
                        <h4 class="card-title">
                            {{ $contract->name_for_humans . ' ('. $contract->contract_status[$contract->status] .')'}}
                            <br>

                            @if($contract->garant_file_name)
                                <button rel="tooltip" href="" onclick="window.open('{{ asset('storage/docs/contracts/' . $contract->garant_file_name) }}');" class="btn-info btn-link btn-xs btn padding-actions" data-original-title="Просмотр гарантийного письма">
                                    <i class="fa fa-file-text-o"></i>
                                </button>
                            @endif
                            @if(($contract->status === 2) and in_array(Auth::user()->id, $responsible_user_ids))
                                <button  rel="tooltip"
                                        type="button"
                                        @if($contract_requests->where('status', 1)->count()) disabled title="Сначала нужно ответить на заявки" @endif
                                        class="btn btn-info btn-link btn-xs padding-actions mn-0"
                                        onclick="approve_contract({{ $contract->id }})"
                                        data-original-title="Подтвердить согласование">

                                    <i class="fa fa-check"></i>
                                </button>
                            @endif
                            @if($contract->status == 1 and in_array(Auth::user()->id, $responsible_user_ids))
                                <button rel="tooltip" data-toggle="modal" data-target="#update-contract" data-original-title="{{ ($contract->file_name ? 'Обновить договор' : 'Загрузить договор' ) }}" class="btn btn-link btn-lg padding-actions mn-0 {{ ($contract->file_name ? 'btn-outline btn-info' : 'btn-success' ) }}">
                                    <i class="fa fa-paperclip"></i>
                                </button>
                            @endif
                            @if(!is_null($contract->file_name) and $contract->status != 6)
                                <button rel="tooltip" onclick="window.open('{{ asset('storage/docs/contracts/' . $contract->file_name) }}');" class="btn btn-info btn-link btn-xs padding-actions" data-original-title="Просмотр договора" style="padding-top: 5px">
                                    <i class="fa fa-eye"></i>
                                </button>
                            @endif
                            @if(($contract->status === 4 or $contract->status === 5) and in_array(Auth::user()->id, $responsible_user_ids))
                                <button  rel="tooltip"
                                        type="button"
                                        @if($contract_requests->where('status', 1)->count()) disabled title="Сначала нужно ответить на заявки" @endif
                                        class="btn btn-success btn-link btn-xs padding-actions mn-0"
                                        data-toggle="modal"
                                        data-target="#signing-contract-{{ $contract->id }}"
                                        data-original-title="Подтвердить подписание">
                                        <i class="fa fa-check"></i>
                                    </button>
                                @endif
                                @if($contract->status === 6)
                                    <button rel="tooltip" onclick="window.open('{{ asset('storage/docs/contracts/' . $contract->final_file_name) }}');" class="btn btn-success btn-link btn-xs padding-actions" data-original-title="Просмотр подписанного договора">
                                        <i class="fa fa-eye"></i>
                                    </button>
                                @endif
                            @if(isset($contract->main_contract_id))
                                <a rel="tooltip" href="{{ route('projects::contract::card', [$contract->project_id, $contract->main_contract_id]) }}" class="btn btn-info btn-link btn-lg btn-space mn-0" data-original-title="Просмотр основного договора">
                                    <i class="fa fa-home"></i>
                                </a>
                            @endif</h4>
                        </div>
                        <div class="col-md-5 text-right">
                            <a href="{{ route('contractors::card', $contract->contractor_id) }}" class="table-link">
                                Контрагент: {{ $contract->contractor_name }}
                            </a>
                            @if($subcontract)
                                <br>
                                <a href="{{ route('contractors::card', $subcontract->id) }}" class="table-link">
                                    Исполнитель: {{ $subcontract->short_name }}
                                </a>
                            @endif
                            <br>
                            <button class="btn btn-outline btn-sm" data-toggle="modal" data-target="#attach_com_offers" style="margin-top: 10px">
                                Прикрепить КП
                            </button>
                            @can('contracts_delete_request')
                                @if(in_array($contract->status, [1,3]) and in_array(Auth::id(), $responsible_user_ids) and !$contract->hasRemoveRequest())
                                    <br><button class="btn btn-danger btn-outline btn-sm" data-toggle="modal" data-target="#delete_request" style="margin-top: 10px">
                                        Запросить удаление
                                    </button>
                                @endif
                            @endcan
                        </div>
                    </div>
                    <hr style="margin-top:10px">
                </div>
                <div class="card-body">
                    <div class="accordions" id="accordion">
                        @if($contract_requests->count() === 0 and (in_array($contract->status,[4,5,6]) or !in_array(Auth::id(), array_diff($project_resp_user_ids, $responsible_user_ids))))
                            @if($contract->theses->count() === 0)
                                <div class="col-md-12 text-center" style="margin-bottom: 15px">
                                    Не нашлось ни одной заявки.
                                </div>
                            @endif
                        @else
                            <div class="card">
                                <div class="card-header">
                                    <h4 class="card-title">
                                        <a data-target="#collapseOne" href="#" data-toggle="collapse">
                                            Текущие заявки
                                            <b class="caret"></b>
                                        </a>
                                    </h4>
                                </div>
                                <div id="collapseOne" class="card-collapse collapse show">
                                    <!-- Таблица заявок -->
                                    <div class="card-body">
                                        <div class="strpied-tabled-with-hover">
                                            <div class="fixed-table-toolbar toolbar-for-btn" style="margin-bottom:10px">
                                                @if(in_array($contract->status,[1,2]) and in_array(Auth::id(), array_diff($project_resp_user_ids, $responsible_user_ids)))
                                                    <div class="row">
                                                        <div class="col-md-12 text-right">
                                                            <button rel="tooltip" type="button" class="btn btn-outline btn-success btn-sm btn-round" data-toggle="modal" data-target="#edit-contract">
                                                                <i class="fa fa-plus"></i>
                                                                Заявка на редактирование
                                                            </button>
                                                        </div>
                                                    </div>
                                                @endif
                                            </div>
                                            @if ($contract_requests->count() > 0)
                                                <div class="table-responsive">
                                                    <table class="table table-hover mobile-table">
                                                        <thead>
                                                        <tr>
                                                            <th>Название</th>
                                                            <th>Автор</th>
                                                            <th>Дата</th>
                                                            <th class="text-right">
                                                                Действия</th>
                                                        </tr>
                                                        </thead>
                                                        <tbody>
                                                        @foreach($contract_requests as $contract_request)
                                                            @if ($contract_request->status === 2)
                                                                <tr class="confirm">
                                                            @elseif ($contract_request->status === 3)
                                                                <tr class="reject">
                                                            @else
                                                                <tr>
                                                                    @endif
                                                                    <td data-label="Название">{{ $contract_request->name }}</td>
                                                                    <td data-label="Автор">
                                                                        @if(!$contract_request->last_name)
                                                                            Система
                                                                        @else
                                                                            {{ $contract_request->last_name }}
                                                                            {{ $contract_request->first_name }}
                                                                            {{ $contract_request->patronymic }}
                                                                        @endif
                                                                    </td>
                                                                    <td data-label="Дата">{{ $contract_request->updated_at }}</td>
                                                                    <td class="text-right">
                                                                        <button rel="tooltip" type="button" class="btn btn-info btn-link btn-xs padding-actions" data-toggle="modal" data-target="#view-contract-request-{{ $contract_request->id }}" data-original-title="Просмотр">

                                                                    @if ($contract_request->status === 1 and in_array(Auth::user()->id, $responsible_user_ids))
                                                                        <i class="fa fa-edit"></i>
                                                                    @else
                                                                        <i class="fa fa-eye"></i>
                                                                    @endif
                                                                    </button>
                                                                </td>
                                                            </tr>
                                                            @endforeach
                                                    </tbody>
                                                </table>
                                            </div>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endif
                    @if(!$contract->files->count() and (in_array($contract->status,[2,4,3,5,6]) or !in_array(Auth::user()->id, $responsible_user_ids)))
                        @else
                        <div class="card">
                            <div class="card-header">
                                <h4 class="card-title">
                                    <a data-target="#collapseFiles" href="#" data-toggle="collapse">
                                        Приложенные файлы
                                    <b class="caret"></b>
                                    </a>
                                </h4>
                            </div>
                            <div id="collapseFiles" class="card-collapse collapse show">
                                <div class="card-body">
                                    @if($contract->status == 1 and in_array(Auth::user()->id, $responsible_user_ids))
                                    <div class="row">
                                        <div class="col-md-12 text-right">
                                            <button class="btn btn-outline btn-sm btn-round" style="margin:15px 0 25px 0" data-toggle="modal" data-target="#add-file">
                                                <i class="fa fa-plus"></i>
                                                Добавить файл
                                            </button>
                                        </div>
                                    </div>
                                    @endif
                                    @foreach($contract->files as $file)
                                    <div class="row">
                                        <div class="col-md-5 col-xl-6">
                                            <a href="" class="contract-file btn btn-social btn-link btn-facebook" rel="tooltip" data-original-title="Открыть файл" onclick="window.open('{{ asset('storage/docs/contracts/' . $file->file_name) }}');">
                                                <i class="fa fa-file" style="font-size:13px; top:-1"></i>
                                               {{ $file->name}}
                                            </a>
                                            @if($contract->status == 1 and in_array(Auth::user()->id, $responsible_user_ids))
                                            <button rel="tooltip" onclick="delete_file({{ $file->id }})" type="button" class="btn-danger btn-link btn-xs">
                                                <i class="fa fa-times"></i>
                                            </button>
                                            @endif
                                            <form type="hidden"></form>
                                        </div>
                                    </div>

                                        <form type="hidden" id="delete_contract_file_{{ $file->id }}" action="{{ route('projects::contract::delete_file') }}" method="post">
                                            @csrf
                                            <input type="hidden" name="file_id" value="{{ $file->id }}">
                                        </form>
                                    @endforeach
                                </div>
                            </div>
                        </div>
                        @endif
                        @if($contract->extra_contracts->count() != 0)
                        <div class="card">
                            <div class="card-header">
                                <h4 class="card-title">
                                    <a data-target="#collapseWne" href="#" data-toggle="collapse">
                                        Дополнительные соглашения
                                    <b class="caret"></b>
                                    </a>
                                </h4>
                            </div>
                            <div id="collapseWne" class="card-collapse collapse show">
                                <div class="card-body">
                                    <div class="row">
                                        <table class="table table-hover search-table mobile-table">
                                            <thead>
                                            <tr>
                                                <th class="text-center" > Внешний №  </th>
                                                <th style="min-width:90px"> Проект</th>
                                                <th style="min-width:140px"> Тип </th>
                                                <th style="min-width:160px">Дата добавления</th>
                                                <th class="text-center">Версия</th>
                                                <th style="min-width:90px">Статус</th>
                                                <th class="text-right">Действия</th>
                                            </tr>
                                            </thead>
                                            <tbody id="forResults">
                                            @foreach($contract->extra_contracts as $extra_contract)
                                                <tr style="cursor:default">
                                                    <td data-label="Внешний №" class="text-center">{{ $extra_contract->foreign_id ? $extra_contract->foreign_id : 'Отсутствует' }}</td>
                                                    <td data-label="Проект">{{ $extra_contract->project_name }}</td>
                                                    <td data-label="Тип">
                                                        {{ $extra_contract->name }}
                                                    </td>
                                                    <td data-label="Дата добавления">{{ $extra_contract->created_at }}</td>
                                                    <td data-label="Версия" class="text-center">{{ $extra_contract->version }}</td>
                                                    <td data-label="Статус">{{ $extra_contract->contract_status[$extra_contract->status] }}</td>
                                                    <td data-label="Действия" class="text-right actions">
                                                        @if($extra_contract->garant_file_name)
                                                            <a rel="tooltip" style="padding: 2px;" target="_blank" href="{{ asset('storage/docs/contracts/' . $extra_contract->garant_file_name) }}" class="btn-info btn-link btn-xs btn btn-space" data-original-title="Просмотр гарантийного письма">
                                                                <i class="fa fa-file-text-o"></i>
                                                            </a>
                                                        @endif
                                                        @if($extra_contract->status === 6)
                                                            <a rel="tooltip" style="padding: 2px !important;" target="_blank" href="{{ asset('storage/docs/contracts/' . $extra_contract->final_file_name) }}" class="btn btn-success btn-link btn-xs btn-space" data-original-title="Просмотр подписанного договора">
                                                                <i class="fa fa-eye"></i>
                                                            </a>
                                                        @endif
                                                        @if($extra_contract->status > 1 and $extra_contract->status != 6)
                                                            <a rel="tooltip" style="padding: 2px !important;" target="_blank" href="{{ asset('storage/docs/contracts/' . $extra_contract->file_name) }}" class="btn btn-info btn-link btn-xs btn-space" data-original-title="Просмотр договора" style="padding-top: 5px">
                                                                <i class="fa fa-eye"></i>
                                                            </a>
                                                        @endif
                                                        <a style="padding: 2px !important;" href="{{ route('projects::contract::card', [$extra_contract->project_id, $extra_contract->id]) }}" rel="tooltip" class="btn-link btn-xs btn btn-open btn-space" data-original-title="Открыть">
                                                            <i class="fa fa-share-square-o"></i>
                                                        </a>
                                                    </td>
                                                </tr>
                                            @endforeach
                                            </tbody>
                                        </table>
                                    </div>
                                </div>

                            </div>

                        </div>
                    @endif
                    @if($contract->theses_check->count() or in_array(Auth::user()->id, $responsible_user_ids))
                            @if($contract->theses->count() === 0 and in_array($contract->status, [2,3,4,5,6]))
                            @else
                                <div class="card">
                                    <div class="card-header">
                                        <h4 class="card-title">
                                            <a data-target="#collapseThree" href="#" data-toggle="collapse">
                                                Тезисы
                                                <b class="caret"></b>
                                            </a>
                                        </h4>
                                    </div>
                                    <div id="collapseThree" class="card-collapse collapse show">
                                        <div class="card-body">
                                            @if($contract->status == 1 and in_array(Auth::user()->id, $responsible_user_ids))
                                                <div class="row">
                                                    <div class="col-md-12 text-right">
                                                        <button class="btn btn-outline btn-sm btn-round" style="margin:15px 0 25px 0" data-toggle="modal" data-target="#add-thesis">
                                                            <i class="fa fa-plus"></i>
                                                            Добавить тезис
                                                        </button>
                                                    </div>
                                                </div>
                                            @endif
                                            @foreach($contract->theses_check as $thesis)
                                                <div class="thesis-container @if($thesis->verifier_status == 1) @elseif($thesis->verifier_status == 3) check-thesis @else reject-thesis @endif">
                                                    <div class="row">
                                                        <div class="col-md-10 col-xl-11">
                                                            <h6>{{ $thesis->name }}</h6>
                                                            <p>
                                                                {{ $thesis->description }}
                                                            </p>
                                                        </div>
                                                        @if($thesis->verifier_status == 1 and $contract->status == 1)
                                                            <div class="col-xl-1 col-md-2 text-right">
                                                                <button type="button" class="btn btn-thesis btn-reject-thesis" onclick="add_thesis_id_to_reject({{ $thesis->id }}, {{ '\'' . $thesis->name . '\'' }})" data-toggle="modal" data-target="#reject-thesis-request">
                                                                    <i class="fa fa-times"></i>
                                                                </button>
                                                                <form method="post" action="{{ route('projects::contract::agree_thesis', $thesis->id) }}">
                                                                    @csrf
                                                                    <button class="btn btn-thesis btn-check-thesis" data-original-title="Подтвердить">
                                                                        <i class="fa fa-check"></i>
                                                                    </button>
                                                                </form>
                                                            </div>
                                                        @endif
                                                    </div>
                                                </div>
                                            @endforeach

                                            @if(in_array(Auth::user()->id, $responsible_user_ids))
                                        @foreach($contract->theses as $thesis)
                                            <div class="thesis-container col-sm-12 @if($thesis->status == 1) @elseif($thesis->status == 3) check-thesis @else reject-thesis @endif">
                                                <div class="row">
                                                    <div class="col-md-10">
                                                        <a class="thesis-link" onclick="show_thesis(this)">{{ $thesis->name }}</a>
                                                    </div>
                                                    @if($thesis->status === 2)
                                                        <div class="col-md-2 text-right">
                                                            @if($contract->status == 1)
                                                                <button rel="tooltip" onclick="" class="btn btn-edit btn-link mn-0 padding-actions" onclick="set_select({{ $thesis }})" data-toggle="modal" data-target="#edit-thesis-{{ $thesis->id }}" data-original-title="Редактировать">
                                                                    <i class="fa fa-edit"></i>
                                                                </button>
                                                                <a class="btn btn-link mn-0 padding-actions" data-toggle="modal" onclick="delete_thesis({{ $thesis->id }})" data-original-title="Отклонён">
                                                                    <i class="fa fa-times i-reject-thesis"></i>
                                                                </a>
                                                            @endif
                                                        </div>
                                                    @endif
                                                </div>
                                                <div class="row thesis-detailed" style="display:none">
                                                    <div class="col-md-12">
                                                        <div class="row">
                                                            <div class="col-md-12">
                                                                <p>
                                                                    {{ $thesis->description }}
                                                                </p>
                                                            </div>
                                                        </div>
                                                        <div class="row">
                                                            <div class="col-sm-12">
                                                                <div class="card strpied-tabled-with-hover" style="margin-bottom:0">
                                                                    <div class="table-responsive">
                                                                        <table class="table">
                                                                            <thead>
                                                                            <tr>
                                                                                <th>Согласующий</th>
                                                                                <th>Дата ответа</th>
                                                                                <th class="text-right">Ответ</th>
                                                                            </tr>
                                                                            </thead>
                                                                            <tbody>
                                                                            @foreach($thesis->verifiers as $verifier)
                                                                                <tr>
                                                                                    <td>{{ $verifier->last_name . ' ' . $verifier->first_name . ' ' . $verifier->patronymic }}</td>
                                                                                    <td>{{ ($verifier->created_at == $verifier->updated_at) ? "ожидание ответа" : $verifier->updated_at}}</td>
                                                                                    @if($verifier->status == 2)
                                                                                        <td class="text-right">
                                                                                            <i class="fa fa-times i-reject-thesis"></i>
                                                                                        </td>
                                                                                    @elseif($verifier->status == 3)
                                                                                        <td class="text-right">
                                                                                            <i class="fa fa-check i-check-thesis"></i>
                                                                                        </td>
                                                                                    @else
                                                                                        <td class="text-right">
                                                                                        </td>
                                                                                    @endif
                                                                                </tr>
                                                                            @endforeach

                                                                            </tbody>
                                                                        </table>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        @endforeach
                                                @endif
                                    </div>

                                </div>
                            </div>
                        @endif
                    @endif

                    <div class="card">
                        <div class="card-header">
                            <h4 class="card-title">
                                <a data-target="#collapse4" href="#" data-toggle="collapse">
                                    Ключевые даты
                                    <b class="caret"></b>
                                </a>
                            </h4>
                        </div>
                        <div id="collapse4" class="card-collapse collapse show">
                            <div class="card-body">
                                <div id="editableTable" class="table-responsive">
                                    <!-- <button class="btn btn-primary" id="submit_data">Submit</button> -->
                                    <table class="table table-responsive-md table-sm editable mobile-table" id="makeEditable">
                                        <thead>
                                            <tr>
                                                <th>#</th>
                                                <th>Наименование</th>
                                                <th>Сумма </th>
                                                <th>Дата c</th>
                                                <th>Дата по</th>
                                                <th>Примечание </th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <tr class="example d-none">
                                                <td class="fat-number" data-label="#"></td>
                                                <td data-label="Наименование" type="input"></td>
                                                <td data-label="Сумма" type="number"></td>
                                                <td data-label="Дата с" type="from"></td>
                                                <td data-label="Дата по" type="to"></td>
                                                <td data-label="Примечание" type="text"></td>
                                            </tr>
                                            @foreach($contract->key_dates as $key => $key_date)
                                                <tr class="real key_date_{{ $key_date->id }}">
                                                    <td data-label="#">{{ $key + 1 }}</td>
                                                    <td data-label="Наименование" type="input">{{ $key_date->name }}</td>
                                                    <td data-label="Сумма" type="number">{{ $key_date->sum ? round($key_date->sum, 2) : '' }}</td>
                                                    <td data-label="Дата с" type="from">{{ $key_date->date_from ? $key_date->date_from->format('d.m.Y') : '' }}</td>
                                                    <td data-label="Дата по" type="to">{{ $key_date->date_to ? $key_date->date_to->format('d.m.Y') : '' }}</td>
                                                    <td data-label="Примечание" type="text">{{ $key_date->note }}</td>
                                                </tr>
                                                @if ($key_date->related_key_dates->count())
                                                    @foreach($key_date->related_key_dates as $skey => $related_key_date)
                                                        <tr class="sub_key_{{ $key_date->id }} key_date_{{ $related_key_date->id }}">
                                                            <td data-label="#">{{ ($key + 1) . '.' . ($skey + 1) }}</td>
                                                            <td data-label="Наименование" type="input">{{ $related_key_date->name }}</td>
                                                            <td data-label="Сумма" type="number">{{ $related_key_date->sum ? round($related_key_date->sum, 2) : '' }}</td>
                                                            <td data-label="Дата с" type="from">{{ $related_key_date->date_from ? $related_key_date->date_from->format('d.m.Y') : '' }}</td>
                                                            <td data-label="Дата по" type="to">{{ $related_key_date->date_to ? $related_key_date->date_to->format('d.m.Y') : '' }}</td>
                                                            <td data-label="Примечание" type="text">{{ $related_key_date->note }}</td>
                                                        </tr>
                                                    @endforeach
                                                @endif
                                            @endforeach
                                        </tbody>
                                    </table>
                                    @can('contracts_create')
                                        <span style="float:right">
                                            <button id="but_add" class="btn btn-info btn-sm btn-outline">
                                                <i class="fa fa-plus"></i>
                                                Добавить строку
                                            </button>
                                        </span>
                                    @endcan
                                </div>
                            </div>
                        </div>
                    </div>

                    @if($contract->operations->count())
                        <div class="card">
                            <div class="card-header">
                                <h4 class="card-title">
                                    <a data-target="#collapse5" href="#" data-toggle="collapse">
                                        Операции
                                        <b class="caret"></b>
                                    </a>
                                </h4>
                            </div>
                            <div id="collapse5" class="card-collapse collapse show">
                                <div class="card-body">
                                    <div class="table-responsive">
                                        <!-- <button class="btn btn-primary" id="submit_data">Submit</button> -->
                                        <table class="table table-responsive-md table-sm editable mobile-table" id="makeEditable">
                                            <thead>
                                                <tr>
                                                    <th>Тип</th>
                                                    <th>Объект</th>
                                                    <th>Адрес</th>
                                                    <th>Автор</th>
                                                    <th>Статус</th>
                                                    <th>Перейти</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @foreach($contract->operations as $operation)
                                                    <tr>
                                                        <td data-label="Тип">{{ $operation->type_name }}</td>
                                                        <td data-label="Объект">{{ $operation->object_text }}</td>
                                                        <td data-label="Адрес">{{ $operation->address_text }}</td>
                                                        <td data-label="Автор">{{ $operation->author->full_name }}</td>
                                                        <td data-label="Статус">
                                                            {{ $operation->status_name }}
                                                            @if($operation->materialsPartTo()->whereDoesntHave('certificates')->count())
                                                                <button type="button" name="button" class="btn btn-link btn-warning btn-xs mn-0 pd-0" data-container="body"
                                                                        data-toggle="popover" data-placement="top" data-content="По операции отсутствуют сертификаты" data-trigger="hover">
                                                                    <i class="fa fa-info-circle"></i>
                                                                </button>
                                                            @endif
                                                        </td>
                                                        <td data-label="Перейти"><a href="{{ $operation->url }}">Карта операции</a></td>
                                                    </tr>
                                                @endforeach
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endif

                    @if(($contract->status == 2 or $contract->status == 4) and in_array(Auth::user()->id, $responsible_user_ids))
                    <div class="card-footer" style="margin-top:40px">
                        <div class="row">
                            <div class="col-md-12 text-center">
                                <button onclick="decline_contract({{ $contract->id }})" class="btn btn-wd btn-danger btn-info">
                                    Требуются изменения
                                </button>
                            </div>
                        </div>
                    </div>
                    @endif

                    @if($contract->status == 1 and in_array(Auth::user()->id, $responsible_user_ids))
                        <div class="card-footer" style="margin-top:40px">
                            <div class="row">
                                <div class="col-md-12 text-center">
                                    <button onclick="send_conctract({{ $contract->id }})" @if($contract->file_name and !$contract_requests->where('status', 1)->count() and $contract->theses->where('status', 1)->count() === 0) class="btn btn-md btn-outline btn-warning" @else class="btn btn-md btn-outline" disabled title="Необходимо прикрепить файл договора, дождаться согласования тезисов и ответить на заявки" @endif>
                                        Отправить на согласование
                                    </button>
                                </div>
                            </div>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>


<form id="approve_contract" action="{{ route('projects::contract::approve', [$contract->project_id, $contract->id]) }}" method="post">@csrf
    <input name="contract_id" type="hidden" id="approved_contract_id">
</form>
<form id="decline_contract" action="{{ route("projects::contract::decline", [$contract->project_id, $contract->id]) }}" method="post">
    @csrf
    <input name="contract_id" type="hidden" id="declined_contract_id">
</form>
<!-- Модалки -->
<!-- Добавление гарантийного письма или договора-->
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
                                        <div class="file-upload ">
                                            <label class="pull-right">
                                                <span><i class="nc-icon nc-attach-87" style="font-size:25px; line-height: 40px"></i></span>
                                                <input type="file" name="document" accept="*" id="upload_document" class="form-control-file file" onchange="getFileName(this)">
                                            </label>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            @if ($contract->type == 1)
                            <div class="row" >
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
                            @endif
                        </form>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Закрыть</button>
                <button id="" form="add_files-{{ $contract->id }}" type="button" onclick="submit_file(this)" {{-- onclick="this.form.submit(); this.disabled=true;" --}} class="btn btn-info">Подтвердить</button>
            </div>
        </div>
    </div>
</div>
<!-- Добавление заявки на редактирование. Отклонение тезиса-->
<div class="modal fade bd-example-modal-lg show" id="reject-thesis-request" role="dialog" aria-labelledby="modal-search" style="display: none;">
    <div class="modal-dialog modal-lg" role="document">
       <div class="modal-content">
           <div class="modal-header">
               <h5 class="modal-title">Заявка на редактирование</h5>
               <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                   <span aria-hidden="true">×</span>
               </button>
           </div>
           <div class="modal-body">
               <hr style="margin-top:0">
               <div class="card border-0" >
                   <div class="card-body ">
                       <form id="reject_thesis_request" class="form-horizontal" action="{{ route('projects::contract::reject_thesis') }}" method="post" enctype="multipart/form-data">
                           @csrf
                           <input type="hidden" name="thesis_id" id="reject_thesis_id">
                           <div class="row">
                               <div class="col-md-12">
                                   <div class="form-group">
                                        <label>Название</label>
                                        <input name="name" id="form_reject_thesis_name" type="text" placeholder="Укажите название заявки" class="form-control" required>
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
                                   <div class="file-container">
                                       <div id="fileName" class="file-name"></div>
                                       <div class="file-upload ">
                                           <label class="pull-right">
                                               <span><i class="nc-icon nc-attach-87" style="font-size:25px; line-height: 40px"></i></span>
                                               <input type="file" name="documents[]" accept="*" id="uploadedReject" class="form-control-file file" onchange="getFileName(this)" multiple>
                                           </label>
                                       </div>
                                   </div>
                               </div>
                           </div>
                       </form>
                   </div>
               </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Закрыть</button>
                <button form="reject_thesis_request" type="submit" class="btn btn-info">Сохранить</button>
           </div>
        </div>
    </div>
</div>
<!-- Добавление заявки на редактирование-->
<div class="modal fade bd-example-modal-lg show" id="edit-contract" role="dialog" aria-labelledby="modal-search" style="display: none;">
    <div class="modal-dialog modal-lg" role="document">
       <div class="modal-content">
           <div class="modal-header">
               <h5 class="modal-title">Заявка на редактирование</h5>
               <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                   <span aria-hidden="true">×</span>
               </button>
           </div>
           <div class="modal-body">
               <hr style="margin-top:0">
               <div class="card border-0" >
                   <div class="card-body ">
                       <form id="store_request" class="form-horizontal" action="{{ route('projects::contracts::requests::store', $contract->project_id) }}" method="post" enctype="multipart/form-data">
                       @csrf
                           <input type="hidden" name="contract_id" value="{{ $contract->id }}">
                           <div class="row">
                               <div class="col-md-12">
                                   <div class="form-group">
                                       <label>Название<star class="star">*</star></label>
                                       <input class="form-control" placeholder="Укажите название заявки" name="name" required maxlength="500">
                                    </div>
                               </div>
                           </div>
                           <div class="row">
                               <div class="col-sm-12">
                                   <div class="form-group">
                                       <label>Описание<star class="star">*</star></label>
                                       <textarea class="form-control textarea-rows " name="description" required="" maxlength="500"></textarea>
                                   </div>
                               </div>
                           </div>
                           <div class="row">
                               <div class="col-sm-12">
                                   <label for="" style="font-size:0.80">
                                       Приложенные файлы
                                   </label>
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
                       </form>
                   </div>
               </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Закрыть</button>
                <button id="submit_req" form="store_request" type="submit" class="btn btn-info">Сохранить</button>
           </div>
        </div>
    </div>
</div>
<!-- Добавить файл -->
<div class="modal fade bd-example-modal-lg show" id="add-file" role="dialog" aria-labelledby="modal-search" style="display: none;">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Добавление файла</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">×</span>
                </button>
            </div>
            <div class="modal-body">
                <hr style="margin-top:0">
                <div class="card border-0" >
                    <div class="card-body">
                        <form id="add_file" class="form-horizontal" action="{{ route('projects::contract::add_files', $contract->id) }}" method="post" enctype="multipart/form-data">
                            @csrf
                            <input type="hidden" name="type" value="3">
                            <div class="row">
                                <div class="col-md-12">
                                    <div class="form-group">
                                        <label>Название<star class="star">*</star></label>
                                        <input name="name" type="text" placeholder="Укажите название" required maxlength="250" class="form-control">
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-7">
                                    <label for="" style="font-size:0.80">
                                        Приложенные файлы<star class="star">*</star>
                                    </label>
                                    <div class="file-container">
                                        <div id="fileName" class="file-name"></div>
                                        <div class="file-upload ">
                                            <label class="pull-right">
                                                <span><i class="nc-icon nc-attach-87" style="font-size:25px; line-height: 40px"></i></span>
                                                <input type="file" name="document" accept="*" id="upload_document" class="form-control-file file" onchange="getFileName(this)">
                                            </label>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Закрыть</button>
                <button form="add_file" onclick="submit_file(this)" type="button" class="btn btn-info">Сохранить</button>
            </div>
        </div>
    </div>
</div>
<!-- Добавить тезис -->
<div class="modal fade bd-example-modal-lg show" id="add-thesis" role="dialog" aria-labelledby="modal-search" style="display: none;">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Создание тезиса</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">×</span>
                </button>
            </div>
            <div class="modal-body">
                <hr style="margin-top:0">
                <div class="card border-0" >
                    <div class="card-body">
                        <form id="add_thesis" class="form-horizontal" action="{{ route('projects::contract::add_thesis', $contract->id) }}" method="post" enctype="multipart/form-data">
                            @csrf
                            <div class="row">
                                <div class="col-md-12">
                                    <div class="form-group">
                                        <label>Название<star class="star">*</star></label>
                                        <input name="name" type="text" placeholder="Укажите название" required maxlength="250" class="form-control">
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-sm-12">
                                    <div class="form-group">
                                        <label>Описание<star class="star">*</star></label>
                                        <textarea name="description" class="form-control textarea-rows " name="description" required="" maxlength="1000"></textarea>
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-12">
                                    <label>Согласующие<star class="star">*</star></label>
                                    <div class="form-group">
                                        <select id="findResp" name="user_ids[]" required multiple style="width: 100%">
                                            @foreach($responsible_users as $user)
                                                @if($user->id != Auth::id())
                                                    <option value="{{ $user->id }}">{{ trim($user->last_name . ' ' . $user->first_name . ' ' . $user->patronymic) . ', ' .  $user->group_name }}</option>
                                                @endif
                                            @endforeach
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
                <button form="add_thesis" type="submit_wv" class="btn btn-info">Сохранить</button>
            </div>
        </div>
    </div>
</div>
<!--Редактировать тезис -->
@foreach($contract->theses as $thesis)
<div class="modal fade bd-example-modal-lg show" id="edit-thesis-{{ $thesis->id }}" role="dialog" aria-labelledby="modal-search" style="display: none;">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Редактирование тезиса</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">×</span>
                </button>
            </div>
            <div class="modal-body">
                <hr style="margin-top:0">
                <div class="card border-0" >
                    <div class="card-body">
                        <form id="edit_thesis-{{ $thesis->id }}" class="form-horizontal" action="{{ route('projects::contract::update_thesis', $contract->id) }}" method="post" enctype="multipart/form-data">
                            @csrf
                            <input type="hidden" value="{{ $thesis->id }}" name="thesis_id">
                            <div class="row">
                                <div class="col-md-12">
                                    <div class="form-group">
                                        <label>Название<star class="star">*</star></label>
                                        <input name="name" type="text" placeholder="Укажите название" required maxlength="250" class="form-control" value="{{ $thesis->name }}">
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-sm-12">
                                    <div class="form-group">
                                        <label>Описание<star class="star">*</star></label>
                                        <textarea name="description" class="form-control textarea-rows " name="description" required maxlength="1000">{{ $thesis->description }}</textarea>
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-7">
                                    <label>Согласующие<star class="star">*</star></label>
                                    <div class="form-group">
                                        <select id="findResp_edit" name="user_ids[]" required multiple style="width: 100%">
                                            @foreach($responsible_users->merge($verifier_users) as $user)
                                                <option @if(in_array($user->id, $thesis->get_verifiers->pluck('user_id')->toArray())) selected @endif value="{{ $user->id }}">{{ $user->last_name . ' ' . $user->first_name . ' ' . $user->patronymic . ', ' .  $user->group_name }}</option>
                                            @endforeach
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
                <button form="edit_thesis-{{ $thesis->id }}" type="submit_wv" class="btn btn-info">Сохранить</button>
            </div>
        </div>
    </div>
</div>
@endforeach
<!--Редактировать договор -->
<div class="modal fade bd-example-modal-lg show" id="update-contract" role="dialog" aria-labelledby="modal-search" style="display: none;">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Редактирование договора</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">×</span>
                </button>
            </div>
            <div class="modal-body">
                <hr style="margin-top:0">
                <div class="card border-0" >
                    <div class="card-body">
                        <form id="form_update_contract" class="form-horizontal" action="{{ route('projects::contract::update', $contract->id) }}" method="post" enctype="multipart/form-data">
                            @csrf
                            <div class="row">
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label>Внешний номер договора</label>
                                        <input name="foreign_id" placeholder="" class="form-control" value="{{ (!is_null($contract->foreign_id) ? $contract->foreign_id : '') }}">
                                    </div>
                                </div>

                                @if ($contract->type == 1)
                                <div class="col-md-3">
                                    <div class="form-group">
                                        <label>Число сдачи КС
                                            <button type="button" name="button" class="btn btn-link btn-primary btn-xs mn-0 pd-0" data-container="body"
                                                    data-toggle="popover" data-placement="top" data-content="Каждый месяц до указанного числа необходимо прикрепить сертификаты на поставленный материал" style="position:absolute;">
                                                <i class="fa fa-info-circle"></i>
                                            </button>
                                        </label>
                                        <input name="ks_date" min="1" max="31" type="number" value="{{ $contract->ks_date }}" class="form-control" placeholder="Укажите число">
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
                                @endif
                            </div>
                            <div class="row">
                                <div class="col-md-8">
                                    <label for="" style="font-size:0.80">
                                        Обновить файл договора
                                    </label>
                                    <div class="file-container">
                                        <div id="fileName" class="file-name"></div>
                                        <div class="file-upload ">
                                            <label class="pull-right">
                                                <span><i class="nc-icon nc-attach-87" style="font-size:25px; line-height: 40px"></i></span>
                                                <input type="file" name="contract_file" accept="*" id="upload_document" class="form-control-file file" onchange="getFileName(this)">
                                            </label>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Закрыть</button>
                <button form="form_update_contract" class="btn btn-primary">Отправить</button>
            </div>
        </div>
    </div>
</div>
<!-- Просмотр заявки-->
@foreach($contract_requests as $contract_request)
    <div class="modal fade bd-example-modal-lg show" id="view-contract-request-{{ $contract_request->id }}" role="dialog" aria-labelledby="modal-search" style="display: none;">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">
                        {{ $contract_request->name }}
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
                                            <a rel="tooltip" data-original-title="Просмотреть файл" onclick="window.open('{{ asset('storage/docs/contract_request_files/' . $file->file_name) }}');">
                                                {{ $file->original_name }}
                                            </a>
                                            <br>
                                        @endforeach

                                        @foreach($contract_request->files->where('is_result', 0)->where('is_proj_doc', 1) as $file)
                                            <a rel="tooltip" data-original-title="Просмотреть файл" onclick="window.open('{{ asset('storage/docs/contract_request_files/' . $file->file_name) }}');">
                                                {{ $file->original_name }}
                                            </a>
                                            <br>
                                        @endforeach
                                    </div>
                                </div>
                            @endif
                            <br>
                            @if($contract_request->status != 1)
                                <div class="row">
                                    <div class="col-md-12">
                                        <div class="form-group">
                                            <div class="row">
                                                <div class="col-md-12">
                                                    <h6 style="margin:10px 0">
                                                        Решение
                                                    </h6>
                                                    <p class="form-control-static">{{ $contract_request->result_comment }}</p>
                                                </div>
                                            </div>
                                            @if ($contract_request->files->where('is_result', 1)->count() > 0)
                                                <div class="row">
                                                    <div class="col-md-12">
                                                        <label class="control-label">Приложенные файлы</label>
                                                        <br>
                                                        @foreach($contract_request->files->where('is_result', 1)->where('is_proj_doc', 0) as $file)
                                                            <a rel="tooltip" data-original-title="Просмотреть файл" onclick="window.open('{{ asset('storage/docs/contract_request_files/' . $file->file_name) }}');">
                                                                {{ $file->original_name }}
                                                            </a>
                                                            <br>
                                                        @endforeach

                                                        @foreach($contract_request->files->where('is_result', 1)->where('is_proj_doc', 1) as $file)
                                                            <a rel="tooltip" data-original-title="Просмотреть файл" onclick="window.open('{{ asset('storage/docs/project_documents/' . $file->file_name) }}');">
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
                            @elseif(in_array(Auth::user()->id, $responsible_user_ids))
                                <form id="update_request_form_{{ $contract_request->id }}" action="{{ route('projects::contracts::requests::update', [$contract_request->project_id, $contract_request->id]) }}" method="post" enctype="multipart/form-data">
                                    @csrf
                                    <hr>
                                    <input type="hidden" name="contract_request_id" value="{{ $contract_request->id }}">
                                    <div class="row" style="margin-bottom:20px">
                                        <div class="col-md-12">
                                            <form class="" action="" method="post">
                                                <div class="row">
                                                    <div class="col-md-12">
                                                        <h6 style="margin:10px 0 20px">Результат</h6>
                                                    </div>
                                                </div>
                                                <div class="row" style="margin-bottom:15px">
                                                    <div class="col-md-4" style="padding-left:0">
                                                        <div class="form-check form-check-radio" rel="tooltip" @if($contract->status != 1) data-original-title="Чтобы подтвердить, сначала нажмите кнопку 'Требуются изменения'" @endif >
                                                            <label class="form-check-label"  style="text-transform:none;font-size:13px">
                                                                <input class="form-check-input" type="radio" name="status" required id="" value="confirm" rel="tooltip" @if($contract->status != 1) disabled @endif>
                                                                <span class="form-check-sign"></span>
                                                                Подтвердить
                                                            </label>
                                                        </div>
                                                    </div>
                                                    <div class="col-md-4">
                                                        <div class="form-check form-check-radio">
                                                            <label class="form-check-label" style="text-transform:none;font-size:13px">
                                                                <input class="form-check-input" type="radio" name="status" required id="" value="reject">
                                                                <span class="form-check-sign"></span>
                                                                Отклонить
                                                            </label>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="row">
                                                    <div class="col-md-12">
                                                        <div class="row">
                                                            <label class="col-sm-3 col-form-label">Комментарий<star class="star">*</star></label>
                                                            <div class="col-sm-9">
                                                                <div class="form-group">
                                                                    <textarea class="form-control textarea-rows" name="result_comment" required maxlength="250"></textarea>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="row">
                                                    <label class="col-sm-3 col-form-label" for="" style="font-size:0.80">
                                                        Приложенные файлы
                                                    </label>
                                                    <div class="col-sm-6">
                                                        <div class="file-container">
                                                            <div id="fileName" class="file-name"></div>
                                                            <div class="file-upload ">
                                                                <label class="pull-right">
                                                                    <span><i class="nc-icon nc-attach-87" style="font-size:25px; line-height: 40px"></i></span>
                                                                    <input type="file" name="documents[]" accept="*" id="upload_document" class="form-control-file file" onchange="getFileName(this)" multiple>
                                                                </label>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="row">
                                                    <label class="col-sm-3 col-form-label">
                                                        Проектная документация
                                                    </label>
                                                    <div class="col-sm-6">
                                                        <select class="js-select-proj-doc" name="project_documents[]" data-title="Выберите документ" data-style="btn-default btn-outline" data-menu-style="dropdown-blue" multiple style="width:100%;">
                                                        </select>
                                                    </div>
                                                </div>
                                            </form>
                                        </div>
                                    </div>
                                </form>
                            @endif
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Закрыть</button>
                    @if ($contract_request->status === 1 and in_array(Auth::user()->id, $responsible_user_ids))
                        <button id="submit_wv" type="submit" form="update_request_form_{{ $contract_request->id }}" class="btn btn-info">Сохранить</button>
                    @endif
                </div>
            </div>
        </div>
    </div>
@endforeach

<div class="modal fade bd-example-modal-lg show" id="attach_com_offers" tabindex="-1" role="dialog" aria-labelledby="modal-search" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
       <div class="modal-content">
           <div class="modal-header">
               <h5 class="modal-title">Прикрепить КП</h5>
               <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                   <span aria-hidden="true">×</span>
               </button>
           </div>
           <div class="modal-body">
               <div class="card border-0">
                   <div class="card-body">
                       <form id="form_attach_com_offers" class="form-horizontal" action="{{ route('projects::contracts::attach_com_offers', $contract->id) }}" method="post">
                           @csrf
                             <div class="form-group">
                                 <div class="row">
                                     <label class="col-sm-3 col-form-label">Коммерческие предложения<star class="star">*</star></label>
                                     <div class="col-sm-9">
                                         <select name="offer_ids[]" multiple class="selectpicker" style="width:100%;">
                                             @foreach($com_offers_options as $offer)
                                             <option value="{{ $offer->id }}" @if($contract->commercial_offers->where('option', $offer->option)->where('is_tongue', $offer->is_tongue)->count()) selected @endif>{{ $offer->option ? $offer->option: (($offer->is_tongue ? 'Шпунт' : 'Сваи') . ': Без наименования') }}</option>
                                             @endforeach
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
                <button type="submit" form="form_attach_com_offers" class="btn btn-info">Сохранить</button>
           </div>
        </div>
    </div>
</div>

<!-- Modal for contract delete -->
<div class="modal fade bd-example-modal-lg show" id="delete_request" role="dialog" aria-labelledby="modal-search" style="display: none;">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Запрос на удаление контракта</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">×</span>
                </button>
            </div>
            <div class="modal-body">
                <hr style="margin-top:0">
                <div class="card border-0" >
                    <div class="card-body">
                        <form id="contract_delete_request" class="form-horizontal" action="{{ route('projects::contracts::contract_delete_request', $contract->project_id) }}" method="post">
                            @csrf
                            <input type="hidden" name="contract_id" value="{{ $contract->id }}">
                            <input type="hidden" name="contract_name" value="{{ $contract->name_for_humans . ' ('. $contract->contract_status[$contract->status] .')'}}">
                            <div class="row">
                                <div class="col-md-12">
                                    <div class="form-group">
                                        <label>Основание<span class="star">*</span></label>
                                        <input type="text" name="reason" placeholder="Укажите основание" class="form-control" maxlength="250" required>
                                    </div>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Закрыть</button>
                <button type="submit" form="contract_delete_request" class="btn btn-info">Подтвердить</button>
            </div>
        </div>
    </div>
</div>

@endsection

@section('js_footer')
@can('contracts_create')
<script>
    $(function() {
        $('#makeEditable').SetEditable({
            $addButton: $('#but_add'),
            onEdit: function(params = []) {
                updateNumbers();
                var tr = params[0];
                var name = $(tr).find("[type=input]").first().text();

                if (! name) return;

                var sum = $(tr).find("[type=number]").first().text();
                var date_from = $(tr).find("[type=from]").first().text();
                var date_to = $(tr).find("[type=to]").first().text();
                var note = $(tr).find("[type=text]").first().text();
                var key_id = $(tr).is('[class*=key_date_]') ? Number($(tr).attr('class').split(" ").pop().split("_").pop()) : '';
                var parent_key_id = $(tr).is('[class^=sub_key_]') ? Number($(tr).attr('class').split(" ").shift().split("_").pop()) : '';
                parent_key_id ? updateSubNumbers(parent_key_id) : '';

                var formData = new FormData();
                formData.append('_token', CSRF_TOKEN);
                formData.append('name', name);
                formData.append('sum', sum);
                formData.append('date_from', date_from);
                formData.append('date_to', date_to);
                formData.append('note', note);
                formData.append('key_id', key_id);
                formData.append('parent_key_id', parent_key_id);

                $.ajax({
                    method: 'POST',
                    data: formData,
                    url: '{{ route('projects::contract::key_date_fork', $contract->id) }}',
                    processData: false,
                    contentType: false,
                    success: function (response) {
                        $(tr).attr('id', '').removeClass('key_date_' + key_id).addClass('key_date_' + response.id);

                        if (response.key_date_id) { updateSubNumbers(response.key_date_id); }
                    }
                });
            }, //Called after edition
            onAdd: function() {
                updateNumbers();
            }, //Called after new tr adding
            onBeforeDelete: function(params = []) {
                var tr = params[0];
                var id = $(tr).attr('class').split("_").pop();
                if (! Number(id)) return;
                var formData = new FormData();
                formData.append('_token', CSRF_TOKEN);
                formData.append('id', id);

                $.ajax({
                    method: 'POST',
                    data: formData,
                    url: '{{ route('projects::contract::remove_key_date') }}',
                    processData: false,
                    contentType: false
                });

                removeSubKeys(id);
            },
            onDelete: function () {
                updateNumbers();
            },
            onBeforeSubDelete: function(params = []) {
                var tr = params[0];

                if ($(tr).is('#editing')) return;

                var id = $(tr).attr('class').split("_").pop();
                var parent_key_id = $(tr).attr('class').split(" ").shift().split("_").pop();

                var formData = new FormData();
                formData.append('_token', CSRF_TOKEN);
                formData.append('id', id);

                $.ajax({
                    method: 'POST',
                    data: formData,
                    url: '{{ route('projects::contract::remove_key_date') }}',
                    processData: false,
                    contentType: false
                });
            },
            onSubDelete: function (params = []) {
                var tr = params[0];
                var parent_key_id = $(tr).attr('class').split(" ").shift().split("_").pop();
                updateSubNumbers(parent_key_id, 'delete');
            }
        });
    });
</script>
@endcan
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

    function show_thesis(element){
        var details = $(element).parent().parent().siblings('.thesis-detailed');
        if($(details).css('display')=="none"){
            $(details).show(100);
        } else {
            $(details).hide(100);
        }
    };

    function check_thesis(element) {
        var thesis = $(element).closest('.thesis-container');
            $(thesis).toggleClass('check-thesis');
    };


</script>
<script> //script for contracts module
    function approve_contract(contract_id) {
        swal({
            title: 'Вы уверены?',
            text: "Перевести договор в статус 'согласовано'?",
            type: 'question',
            showCancelButton: true,
            cancelButtonText: 'Назад',
            confirmButtonText: 'Перевести'
        }).then((result) => {
            if(result.value) {
                $('#approved_contract_id').val(contract_id);
                $('#approve_contract').submit();
            }
        })
    }

</script>

<meta name="csrf-token" content="{{ csrf_token() }}" />

<script>
var CSRF_TOKEN = $('meta[name="csrf-token"]').attr('content');

function add_thesis_id_to_reject(thesis_id, name) {
    $('#reject_thesis_id').val(thesis_id);
    $('#form_reject_thesis_name').val(name);
}

function show_thesis(element){
    var details = $(element).parent().parent().siblings('.thesis-detailed');
    if($(details).css('display')=="none"){
        $(details).show(100);
    } else {
        $(details).hide(100);
    }
}

function set_select(thesis){
    $('#select_'+thesis.id).val(5);
    $('.selectpicker').refresh();
}

function check_thesis(element) {
    var thesis = $(element).closest('.thesis-container');
    if($(thesis).hasClass('.check-thesis')){
        $(thesis).removeClass('.check-thesis')
    } else {
        $(thesis).addClass('.check-thesis')
    }
}

function delete_thesis(id) {
    swal({
        title: 'Вы уверены?',
        text: "Тезис будет удален!",
        type: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#d33',
        cancelButtonColor: '#3085d6',
        cancelButtonText: 'Назад',
        confirmButtonText: 'Удалить'
    }).then((result) => {
        if(result.value) {
            $.ajax({
                url:'{{ route("projects::contract::delete_thesis") }}',
                type: 'POST',
                data: {
                    _token: CSRF_TOKEN,
                    thesis_id:  id,
                },
                dataType: 'JSON',
                success: function () {
                    location.reload();
                }
            });
        }
    });
}

function decline_contract(contract_id) {
    swal({
        title: 'Вы уверены?',
        text: "Договор будет отклонён! \n Будет создана новая версия",
        type: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#d33',
        cancelButtonColor: '#3085d6',
        cancelButtonText: 'Назад',
        confirmButtonText: 'Отклонить'
    }).then((result) => {
        if(result.value) {
            $('#declined_contract_id').val(contract_id);
            $('#decline_contract').submit();
        }
    });
}

function delete_file(id) {
    swal({
        title: 'Вы уверены?',
        text: "Файл будет удален",
        type: 'question',
        showCancelButton: true,
        confirmButtonColor: '#d33',
        cancelButtonColor: '#3085d6',
        cancelButtonText: 'Назад',
        confirmButtonText: 'Удалить'
    }).then((result) => {
        if(result.value) {
            $('#delete_contract_file_'+id).submit();
        }
    });
}

function send_conctract(contract_id) {
    swal({
        title: 'Внимание!',
        text: "Возможность редактирования договора будет закрыта.",
        type: 'warning',
        showCancelButton: true,
        confirmButtonColor: 'success',
        cancelButtonColor: '#aaa',
        cancelButtonText: 'Назад',
        confirmButtonText: 'Отправить'
    }).then((result) => {
        if(result.value) {
            $.ajax({
                url:'{{ route("projects::contract::send_contract", [$contract->project_id, $contract->id]) }}',
                type: 'POST',
                data: {
                    _token: CSRF_TOKEN,
                    contract_id:  contract_id,
                },
                dataType: 'JSON',
                success: function () {
                    location.reload();
                }
            });
        }
    });
}

function reject_info(thesis_id, user_id) {
    $.ajax({
        url:'{{ route("projects::contract::get_reject_info") }}',
        type: 'POST',
        data: {
            _token: CSRF_TOKEN,
            user_id: user_id,
            thesis_id: thesis_id,
        },
        dataType: 'JSON',
        success: function (data) {
            $('#reject_name').html(data.name);
            $('#reject_description').html(data.description);
        }
    });
}

$('.js-select-proj-doc').select2({
    language: "ru",
    closeOnSelect: false,
    ajax: {
        url: '/projects/ajax/get_project_documents/' + {{ $contract->project_id }},
        dataType: 'json',
        delay: 250,
    }
}).on("select2:unselecting", function(e) {
    $(this).data('state', 'unselected');
}).on("select2:open", function(e) {
    if ($(this).data('state') === 'unselected') {
        $(this).removeData('state');
        var self = $(this);
        setTimeout(function() {
            self.select2('close');
        }, 1);
    }
});

$('#findResp').select2({
    language: "ru",
    closeOnSelect: false,
    placeholder: "Выберите согласующих",
    ajax: {
        url: '/tasks/get-users',
        dataType: 'json',
        delay: 250
    }
}).on("select2:unselecting", function(e) {
    $(this).data('state', 'unselected');
}).on("select2:open", function(e) {
    if ($(this).data('state') === 'unselected') {
        $(this).removeData('state');
        var self = $(this);
        setTimeout(function() {
            self.select2('close');
        }, 1);
    }
});
$('#findResp_edit').select2({
    language: "ru",
    closeOnSelect: false,
    placeholder: "Выберите согласующих",
    ajax: {
        url: '/tasks/get-users',
        dataType: 'json',
        delay: 250
    }
}).on("select2:unselecting", function(e) {
    $(this).data('state', 'unselected');
}).on("select2:open", function(e) {
    if ($(this).data('state') === 'unselected') {
        $(this).removeData('state');
        var self = $(this);
        setTimeout(function() {
            self.select2('close');
        }, 1);
    }
});

</script>

@endsection
