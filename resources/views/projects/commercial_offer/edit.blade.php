@extends('layouts.app')

@section('title', 'Коммерческие предложения')

@section('url', route('projects::card', $commercial_offer->project_id))

@section('css_top')
    <link href="{{ mix('css/projects.css') }}" rel="stylesheet" />
    <style>
        li.selected {
            background-color: #efefef;
        }
        .dropdown-menu {
            width:100%;
        }
        #prior::-webkit-inner-spin-button,
        #prior::-webkit-outer-spin-button
        {
            -webkit-appearance: none;
        }

        [class*=" hide"] > * {
            background: #F5F5F5;
            color: #707070;
        }

        [class^="hide"] > * {
            background: #F5F5F5;
            color: #707070;
         }

        .select2-search__field {
            width: 100% !important;
        }

        .select2-hidden-accessible {
            margin: 2.38em 0 0 140px !important;
        }

        .select2-container--default .select2-selection--multiple .select2-selection__choice {
            background-color: #f7f7f7!important;
        }

    </style>
@endsection

@section('content')
    <div class="row">
        <div class="col-md-12">
            <div aria-label="breadcrumb" role="navigation">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item">
                        <a href="{{ route('projects::card', $commercial_offer->project_id) }}" class="table-link">{{ $work_volume->project_name }}</a>
                    </li>
                    <li class="breadcrumb-item">
                        <a href="{{ route('projects::commercial_offer' . (($commercial_offer->is_tongue) ? '::card_tongue' : '::card_pile'), [$commercial_offer->project_id, $commercial_offer->id]) }}" class="table-link">Коммерческое предложение</a>
                    </li>
                    <li class="breadcrumb-item active" aria-current="page">Редактирование КП</li>
                </ol>
            </div>
        </div>
    </div>
    <div class="card @if ($commercial_offer->isNeedToBeColored()) card-important @endif">
        <div class="card-header">
            <div class="row">
                <div class="col-md-12">
                    <h4 class="card-title" style="margin-top: 2px">
                        Коммерческое предложение
                    </h4>
                </div>
            </div>
            <hr style="margin-top:10px">
        </div>
        <div class="card-body">
            <div class="accordions" id="accordion">
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
                                </div>
                                <div class="table-responsive">
                                    <table class="table table-hover mobile-table">
                                        <thead>
                                        <tr>
                                            <th>Автор</th>
                                            <th>Дата</th>
                                            <th class="text-right">
                                                Действия</th>
                                        </tr>
                                        </thead>
                                        <tbody>
                                        @foreach ($commercial_offer_requests as $offer_request)
                                            @if ($offer_request->status === 1)
                                                <tr class="confirm">
                                            @elseif ($offer_request->status === 2)
                                                <tr class="reject">
                                            @else
                                                <tr>
                                                    @endif
                                                    <td data-label="Автор">
                                                        @if($offer_request->last_name)
                                                            {{ $offer_request->last_name }}
                                                            {{ $offer_request->first_name }}
                                                            {{ $offer_request->patronymic }}
                                                        @else
                                                            Система
                                                        @endif
                                                    </td>
                                                    <td data-label="Дата">{{ $offer_request->updated_at }}</td>
                                                    <td data-label="" class="text-right actions">
                                                        <button rel="tooltip" onclick="" class="btn-info btn-link btn-xs btn padding-actions mn-0" data-toggle="modal" data-target="#view-request-offer{{ $offer_request->id }}" data-original-title="Просмотр">
                                                            @if ($offer_request->status === 0)
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
                            </div>
                        </div>
                    </div>
                </div>
                <div class="card">
                    <div class="card-header">
                        <h4 class="card-title">
                            <a data-target="#collapseTwo" href="#" data-toggle="collapse">
                                Коммерческое предложение
                                <b class="caret"></b>
                            </a>
                        </h4>
                    </div>
                    <div id="collapseTwo" class="card-collapse collapse show">
                        <div id="app1" class="card-body">
                            <div class="strpied-tabled-with-hover">
                                <div class="fixed-table-toolbar toolbar-for-btn" style="margin-bottom:10px">
                                </div>
                                <div class="card" id="scrollAfterSubcontractorAdd">
                                    @if($subcontractors->count() > 0)
                                        <div class="card-title">
                                            <div class="row">
                                                <div class="col-md-12">
                                                    <h6 style="margin: 30px 15px 20px 5px">
                                                        Подрядчики
                                                    </h6>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="row">
                                            <div class="col-md-12">
                                                <div class="card strpied-tabled-with-hover">
                                                    <div class="card table-with-links">
                                                        <div class="table-responsive">
                                                            <table class="table mobile-table">
                                                                <thead>
                                                                <tr>
                                                                    <th>Название подрядчика</th>
                                                                    <th>Тип</th>
                                                                    <th>Приложенные файлы</th>
                                                                    <th class="text-right"> Действия </th>
                                                                </tr>
                                                                </thead>
                                                                <tbody>
                                                                @foreach($subcontractors as $subcontractor)
                                                                    @foreach($subcontractor->file as $file)
                                                                    <tr>
                                                                        <td data-label="Название подрядчика">{{ $subcontractor->short_name }}</td>
                                                                        <td data-label="Тип">Субподряд</td>
                                                                        <td data-label="Приложенные файлы"><a  target="_blank" href="{{ asset('storage/docs/commercial_offers_contractor_files/' . $file->file_name) }}">{{ $file->original_name }}</a></td>
                                                                        <td data-label="" class="text-right actions">
                                                                            <button rel="tooltip" onclick="detach_subcontractors({{ $file->id }}, 0)" class="btn-danger btn-link btn-xs btn pd-0" data-toggle="modal" data-target="#view-request" data-original-title="Удалить">
                                                                                <i class="fa fa-times"></i>
                                                                            </button>
                                                                        </td>
                                                                    </tr>
                                                                @endforeach
                                                                    @endforeach
                                                                </tbody>
                                                            </table>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    @endif

                                    <div class="row" id="add_subcontract" style="display:none">
                                        <div class="col-md-12">
                                            <div class="card" style="border:1px solid rgba(0,0,0,.125); padding-bottom:-20px">
                                                <form id="add_subcontractor_form" action="{{ route('projects::commercial_offer::attach_subcontractor', $commercial_offer->work_volume_id) }}" method="post" enctype="multipart/form-data">
                                                    @csrf
                                                    <input type="hidden" name="com_offer_id" value="{{ $commercial_offer->id }}">
                                                    <input type="hidden" name="type" value="0">
                                                    <div class="materials-container">
                                                        <button type="button" class="close" id="close_sub">
                                                            <span aria-hidden="true">×</span>
                                                        </button>
                                                        <div class="row">
                                                            <div class="col-md-5" style="padding-right:0">
                                                                <label for="">Подряд</label>
                                                                <select class="js-select-subcontractor" name="subcontractor_id" data-title="Выберите подряд" data-style="btn-default btn-outline" data-menu-style="dropdown-blue" required style="width:100%;">
                                                                    <option value=""></option>
                                                                </select>
                                                            </div>
                                                            <div class="col-sm-4"  style="padding:5px 0 0 15px">
                                                                <label for="" style="margin-bottom:0">Приложенные файлы</label>
                                                                <div class="file-container" style="margin-top:4px;">
                                                                    <div id="fileName4" class="file-name"></div>
                                                                    <div class="file-upload ">
                                                                        <label class="pull-right">
                                                                            <span><i class="nc-icon nc-attach-87" style="font-size:25px; line-height: 40px"></i></span>
                                                                            <input type="file" name="document" accept="*" form="add_subcontractor_form" id="uploadedFile4" class="form-control-file file ">
                                                                        </label>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                            <div class="col-md-3 text-right" style="padding:0;">
                                                                <button id="submit_contr" class="btn-info btn btn-wd btn-outline d-none" style="margin-top:38px">
                                                                    Сохранить
                                                                </button>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </form>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="row" id="btn_container">
                                        <div class="col-md-12 text-right" style="height:70px">
                                            <button rel="tooltip" title="" type="button" class="btn-info btn-wd btn btn-outline btn-round" id="show_subcontract">
                                                Назначить субподряд
                                            </button>
                                            <p class="text-warning" id="warning" style="display:none">Работы не выбраны</p>
                                        </div>
                                    </div>
                                    <div class="card strpied-tabled-with-hover">
                                        <div class="card table-with-links">
                                            <div class="table-responsive com-table-responsive">
                                                <table class="table mobile-table work-table">
                                                    <thead>
                                                        <tr>
                                                            <th style="width:80px"></th>
                                                            <th>Вид работы</th>
                                                            <th class="text-center">Ед. измер.</th>
                                                            <th class="text-center">Кол-во</th>
                                                            <th class="text-center">
                                                                Срок, дн.
                                                            </th>
                                                            <th class="text-center">Стоимость, ед/руб</th>
                                                            <th class="text-center" style="white-space:pre-wrap; min-width:120px">Общая стоимость, руб</th>
                                                            <th>Исполнитель</th>
                                                            <th></th>
                                                            <th class="td-0"></th>
                                                        </tr>
                                                    </thead>
                                                    <tbody>
                                                    @foreach($commercial_offer->works->sortBy('order')->groupBy('work_group_id')->sortKeys() as $id => $group)
                                                        <tr
                                                                @if($commercial_offer->reviews(2, $id)->get()->count() > 0) onclick="showReview({{ $commercial_offer->reviews(2, $id)->get() }}, this)" id="{{ 'WorkCategory' . $id }}" style="background-color:{{ $commercial_offer->reviews(2, $id)->get()[0]->result_status == 0 ? '#e4bfbf':'#C9FFD4' }};" @endif
                                                        class="tr-title">
                                                            <td></td>
                                                            <td class="th-text">
                                                                {{ $work_groups[$id] . ':' }}
                                                            </td>
                                                            <td></td>
                                                            <td></td>
                                                            <td></td>
                                                            <td></td>
                                                            <td></td>
                                                            <td></td>
                                                            <td class="text-right action-material actions">
                                                                <button rel="tooltip" onclick="avans_modal_show('{{ $work_groups[$id] . ':' }}', $('#result_work_{{ $id }}').val().replace(/ /g, '').replace(/,/g, '.'))" data-toggle="modal" data-target="#add_avans_to_cp" class="btn-info btn-link btn-xs btn pd-0 mn-0" data-original-title="Авансирование">
                                                                    <i class="avans"></i>
                                                                </button>
                                                            </td>
                                                            <td class="td-0"></td>
                                                        </tr>

                                                        @foreach($group as $work)
                                                            <tr
                                                                    @if($work->reviews->count() > 0) onclick="showReview({{ $work->reviews }}, this)" id="{{ array_reverse(explode('\\',get_class($commercial_offer->works->first())))[0] . $work->id }}" style="background-color: {{ $work->reviews[0]->result_status == 0 ? '#fdd8d8':'#C9FFD4'  }};" @endif
                                                            class="{{ $work->is_hidden ? 'hide' : '' }} work" wvw_id="{{ $work->id }}">
                                                                <td style="text-align:center">
                                                                    <button type="button" data-original-title="Вверх" class="btn btn-link btn-up up">
                                                                    <i class="fa fa-chevron-up"></i>
                                                                    </button>
                                                                    <button type="button" data-original-title="Вниз" class="btn btn-link btn-down down">
                                                                        <i class="fa fa-chevron-down"></i>
                                                                    </button>
                                                                </td>
                                                                <td data-label="Вид работы">
                                                                    {{ $work->manual->name }}
                                                                    @if($work->shown_materials->count() and $work->manual->show_materials)
                                                                        @if($id == 2)
                                                                            @php $combine_ids = []; @endphp
                                                                            (
                                                                            @foreach($work->shown_materials->where('combine_id', '!=', null) as $index => $material)
                                                                            @if(!in_array($material->combine_id, $combine_ids))
                                                                                {{ $material->combine_pile() }}
                                                                                    (
                                                                                    @foreach($work_volume_materials->where('combine_id' , $material->combine_id) as $key =>  $item)
                                                                                        @if(!in_array($item->combine_id, $combine_ids))
                                                                                            {{ $item->name . ' + ' }}
                                                                                        @else
                                                                                            {{ $item->name }}
                                                                                        @endif

                                                                                        @php $combine_ids[] = $material->combine_id; @endphp
                                                                                    @endforeach
                                                                                    @if ($index != $work->shown_materials->where('combine_id', '!=', null)->count() - 2)
                                                                                    ),
                                                                                    @endif
                                                                                @php
                                                                                    $combine_ids[] = $material->combine_id;
                                                                                @endphp
                                                                            @endif

                                                                            @endforeach
                                                                            @foreach($work->shown_materials->where('combine_id', '=', null)->values() as $key => $material)
                                                                                @if($key < $work->shown_materials->where('combine_id', '=', null)->count() - 1)
                                                                                {{ $material->name . ' ,' }}
                                                                                @else
                                                                                {{ $material->name }}
                                                                                @endif
                                                                            @endforeach
                                                                            )
                                                                        @else
                                                                            (
                                                                            @foreach($work->shown_materials->slice(0, -1) as $material)
                                                                                {{ $material->name . ','}}
                                                                            @endforeach
                                                                            {{ $work->shown_materials->last()->name }}
                                                                            )
                                                                        @endif
                                                                    @endif
                                                                </td>
                                                                <td data-label="Ед. измерения" class="text-center">{{ $work->unit }}</td>
                                                                <td data-label="Количество" class="text-center">{{ number_format($work->count, 3, '.', '') }}</td>
                                                                <td data-label="Срок, дн." class="text-center">
                                                                    <input type="text" onchange="change_work_term(this, {{ $work->id }}, {{ $work->work_group_id }})" class="form-control term price-input" value="{{ $work->term }}" maxlength="9">
                                                                </td>
                                                                <td data-label="Стоимость за ед.,руб" class="text-center">
                                                                    <input type="text" onchange="change_work_price(this, {{ $work->id }}, {{ $work->work_group_id }})" class="form-control price-input" value="{{ $work->price_per_one }}" maxlength="9">
                                                                </td>
                                                                <td data-label="Общая стоимость, руб" class="text-center">
                                                                    <input type="text" readonly="readonly" class="form-control price-input {{ $work->is_hidden ? "hide" : "" }}general_price {{ $work->is_hidden ? "hide" : "" }}common_work_price {{ $work->is_hidden ? "hide" : "" }}work_result_price_{{ $work->work_group_id }}" value="{{ $work->result_price }}" maxlength="9">
                                                                </td>
                                                                <td data-label="Исполнитель">
                                                                    @if ($work->contractor_name || $work->subcontractor())
                                                                        {{ $work->contractor_name ?? ($work->subcontractor()->short_name ?? '') }}
                                                                    @else
                                                                        <div class="form-check mn-top-7" style="display:none">
                                                                            <label class="form-check-label">
                                                                                <input class="form-check-input work_check" name="subcontractor_works[]" value="{{ $work->id }}" form="add_subcontractor_form" type="checkbox">
                                                                                <span class="form-check-sign"></span>
                                                                            </label>
                                                                        </div>
                                                                    @endif
                                                                </td>
                                                                <td class="text-right action-material actions">
                                                                    <button rel="tooltip" onclick="toggle_one({{ $work->id }}, 1)" class="btn-warning btn-link btn-xs btn" data-original-title="{{ $work->is_hidden ? 'Показать работу' : 'Скрыть работу' }}" style="padding: 0;">
                                                                        <i class="fa {{ $work->is_hidden ? "fa-eye" : "fa-eye-slash" }}"></i>
                                                                    </button>
                                                                    @if(!$work->is_hidden)
                                                                    <button rel="tooltip" onclick="avans_modal_show('{{ $work->manual->name }}', $(this).closest('.work').find('.general_price').first().val().replace(/ /g, '').replace(/,/g, '.'))" data-toggle="modal" data-target="#add_avans_to_cp" class="btn-info btn-link btn-xs btn mn-0" data-original-title="Авансирование" style="padding: 0;">
                                                                        <i class="avans"></i>
                                                                    </button>
                                                                    @endif
                                                                </td>
                                                                <td class="td-0"></td>
                                                            </tr>
                                                        @endforeach
                                                        <tr class="total">
                                                            <td></td>
                                                            <td class="result">{{ ['Итого по шпунтовым работам:',
                                                            'Итого по устройству свайного поля:',
                                                            'Итого по земельным работам:',
                                                            'Итого по монтажу систем крепления:',
                                                            'Итого по дополнительным работам:',
                                                            ][$id - 1] }}</td>
                                                            <td></td>
                                                            <td></td>
                                                            <td></td>
                                                            <td></td>
                                                            <td class="text-center sum">
                                                                <input style="margin-top:0" type="text" id="result_work_{{$id}}" class="form-control price-input" readonly="readonly" value="{{ $group->where('is_hidden', 0)->pluck('result_price')->sum() }}">
                                                            </td>
                                                            <td></td>
                                                            <td></td>
                                                            <td class="td-0"></td>
                                                        </tr>
                                                    @endforeach
                                                    @php $result_works_cost = $commercial_offer->works->where('is_hidden', 0)->pluck('result_price')->sum(); @endphp
                                                    <tr class="medium-total">
                                                        <td></td>
                                                        <td class="result">Итого по работам:</td>
                                                        <td></td>
                                                        <td></td>
                                                        <td></td>
                                                        <td></td>
                                                        <td class="text-center sum">
                                                            <input style="margin-top:0" type="text" id="result_work" class="form-control price-input" readonly="readonly" value="{{ $result_works_cost }}">
                                                        </td>
                                                        <td></td>
                                                        <td></td>
                                                        <td class="td-0"></td>
                                                    </tr>
                                                    </tbody>
                                                </table>
                                            </div>
                                        </div>
                                    </div>
                                    <!-- Таблица материалов -->
                                    @if($material_subcontractors->count() > 0)
                                        <div class="card-title" id="scrollAfterAddMatSubcontract">
                                            <div class="row">
                                                <div class="col-md-12">
                                                    <h6 style="margin: 30px 15px 20px 5px">
                                                        Поставщики
                                                    </h6>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="row">
                                            <div class="col-md-12">
                                                <div class="card strpied-tabled-with-hover">
                                                    <div class="card table-with-links">
                                                        <div class="table-responsive">
                                                            <table class="table mobile-table">
                                                                <thead>
                                                                <tr>
                                                                    <th>Название поставщика</th>
                                                                    <th>Тип</th>
                                                                    <th>Приложенные файлы</th>
                                                                    <th class="text-right"> Действия </th>
                                                                </tr>
                                                                </thead>
                                                                <tbody>
                                                                @foreach($material_subcontractors as $subcontractor)
                                                                    @foreach($subcontractor->file as $file)
                                                                        <tr>
                                                                            <td data-label="Название поставщика">{{ $subcontractor->short_name }}</td>
                                                                            <td data-label="Тип">Поставка</td>
                                                                            <td data-label="Приложенные файлы"><a  target="_blank" href="{{ asset('storage/docs/commercial_offers_contractor_files/' . $file->file_name) }}">{{ $file->original_name }}</a></td>
                                                                            <td data-label="" class="text-right actions">
                                                                                <button rel="tooltip" onclick="detach_subcontractors({{ $file->id }}, 1)" class="btn-danger btn-link btn-xs btn pd-0" data-toggle="modal" data-target="#view-request" data-original-title="Удалить">
                                                                                    <i class="fa fa-times"></i>
                                                                                </button>
                                                                            </td>
                                                                        </tr>
                                                                    @endforeach
                                                                @endforeach
                                                                </tbody>
                                                            </table>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    @endif
                                    @if($work_volume_materials->count())
                                        <div class="row">
                                            <div class="col-md-12">
                                                <h6 style="margin:20px 0 15px 5px">Материалы</h6>
                                            </div>
                                        </div>

                                        <div class="row" id="add_mat_subcontract" style="display:none">
                                            <div class="col-md-12">
                                                <div class="card" style="border:1px solid rgba(0,0,0,.125); padding-bottom:-20px">
                                                    <form id="add_mat_subcontractor_form" action="{{ route('projects::commercial_offer::attach_subcontractor', $commercial_offer->work_volume_id) }}" method="post" enctype="multipart/form-data">
                                                        @csrf
                                                        <input type="hidden" name="com_offer_id" value="{{ $commercial_offer->id }}">
                                                        <input type="hidden" name="type" value="1">
                                                        <div class="materials-container">
                                                            <div class="row">
                                                                <div class="col-md-5">
                                                                    <label for="">Поставка</label>
                                                                    <select class="js-select-subcontractor" name="subcontractor_id" data-title="Выберите поставщика" data-style="btn-default btn-outline" data-menu-style="dropdown-blue" required style="width:100%;">
                                                                        <option value=""></option>
                                                                    </select>
                                                                </div>
                                                                <div class="col-sm-4">
                                                                    <label for="" style="margin-bottom:0">Приложенные файлы</label>
                                                                    <div class="file-container" style="margin-top:4px;">
                                                                        <div id="fileName" class="file-name"></div>
                                                                        <div class="file-upload ">
                                                                            <label class="pull-right">
                                                                                <span><i class="nc-icon nc-attach-87" style="font-size:25px; line-height: 40px"></i></span>
                                                                                <input type="file" name="document" accept="*" form="add_mat_subcontractor_form" id="uploadedFile" class="form-control-file file ">
                                                                            </label>
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                                <div class="col-md-3 text-right" style="padding:0 10px 0 0;">
                                                                    <button id="submit_mat_contr" class="btn-info btn btn-wd btn-outline d-none" style="margin-top:38px">
                                                                        Сохранить
                                                                    </button>
                                                                    <button type="button" class="close" id="close_mat_sub">
                                                                        <span aria-hidden="true">×</span>
                                                                    </button>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </form>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="row" id="btn_mat_container">
                                            <div class="col-md-12 text-right" style="height:70px">
                                                <button rel="tooltip" title="" type="button" class="btn-info btn-wd btn btn-outline btn-round" id="show_mat_subcontract">
                                                    Назначить поставщика
                                                </button>
                                                <p class="text-warning" id="warning" style="display:none">Работы не выбраны</p>
                                            </div>
                                        </div>
                                        <div class="card strpied-tabled-with-hover" style="margin-bottom:0">
                                            <div class="card table-with-links">
                                                <div class="table-responsive com-table-responsive">
                                                    <table class="table mobile-table">
                                                        <thead>
                                                        <tr>
                                                            {{--<th style="width:80px"></th>--}}
                                                            <th>Наименование</th>
                                                            <th class="text-center">Ед. измер.</th>
                                                            <th class="text-center">КОЛ-ВО</th>
                                                            <th class="text-center">Модификация</th>
                                                            <th class="text-center">СТ-ТЬ, ЕД/РУБ</th>
                                                            <th class="text-center">ОБЩ. СТ-ТЬ, РУБ</th>
                                                            <th>Поставщик</th>
                                                            <th class="text-center">
                                                            @if($work_volume_materials->where('work_group_id', '<>', 2)->first())
                                                                Б/У
                                                            @endif
                                                            </th>
                                                            <th class="text-right"></th>
                                                            <th class="td-0"></th>
                                                        </tr>
                                                        </thead>
                                                        <tbody>
                                                        <!-- Материалы для Шпунтовые работы-->
                                                        @foreach($work_volume_materials->groupBy('work_group_id') as $id => $group)
                                                            <tr
                                                                @if($commercial_offer->reviews(1, $group->first()->manual->category->id)->get()->count() > 0) onclick="showReview({{ $commercial_offer->reviews(1, $group->first()->manual->category->id)->get() }}, this)" id="{{ 'MaterialWork' . $id }}" style="background-color: {{ $commercial_offer->reviews(1, $group->first()->manual->category->id)->get()[0]->result_status == 0 ? '#e4bfbf':'#C9FFD4' }};" @endif
                                                            class="tr-title">
                                                                {{--<td></td>--}}
                                                                <td colspan="6" class="th-text">
                                                                    {{ ['Материалы для устройства шпунтового ограждения:',
                                                                    'Материалы для устройства свайного поля:',
                                                                    'Материалы для земельных работ:',
                                                                    'Материалы для монтажа систем крепления:',
                                                                    'Материалы для дополнительных работ:',
                                                                    ][$id - 1] }}</td>
                                                                <td></td>
                                                                <td></td>
                                                                <td class="text-right actions">
                                                                    <button rel="tooltip" onclick="avans_modal_show('{{['материалы для устройства шпунтового ограждения',
                                                                            'материалы для устройства свайного поля',
                                                                            'материалы для земельных работ',
                                                                            'материалы для монтажа систем крепления',
                                                                            'материалы для дополнительных работ:',
                                                                            ][$id - 1]}}', $('#result_material_' + '{{ $id }}').val().replace(/ /g, '').replace(/,/g, '.'))" data-toggle="modal" data-target="#add_avans_to_cp" class="btn-info btn-link btn-xs btn pd-0" data-original-title="Авансирование">
                                                                        <i class="avans"></i>
                                                                    </button>
                                                                </td>
                                                                <td class="td-0"></td>
                                                            </tr>

                                                            @foreach($group as $material)
                                                                @foreach($splits->where('man_mat_id', $material->manual_material_id) as $split)
                                                                    @if ( in_array($split->type, [1,3,5]) )
                                                                        <tr
                                                                                @if($split->reviews->count() > 0) onclick="showReview({{ $split->reviews }}, this)" id="{{ 'Split' . $split->id }}" style="background-color: {{ $split->reviews[0]->result_status == 0 ? '#fdd8d8':'#C9FFD4'  }};" @endif
                                                                        class="material">
                                                                            <td data-label="Наименование" class="material_name_td">
                                                                                <select name="belongs" class="selectpicker mobile-select" onchange="rent_term(this, {{ $split->count }}, {{ $split->id }}, '{{ $split->type }}')" data-style="btn-default btn-outline" data-menu-style="dropdown-blue" required>
                                                                                    <option value="" disabled="disabled" selected="selected">{{ $split->name }}</option>
                                                                                    <optgroup label="Разделить материал">
                                                                                        <option value="new_type=1">Продажа</option>
                                                                                        <option value="new_type=3">Аренда</option>
                                                                                        <option value="new_type=5">Давальческий</option>
                                                                                    </optgroup>
                                                                                    <optgroup label="Объединить материал">
                                                                                        @foreach($splits->where('man_mat_id', $split->man_mat_id)->where('id', '!=', $split->id)->whereNotIn('type', [2, 4]) as $sibling)
                                                                                            <option value="target_split_id={{ $sibling->id }}">{{ $sibling->name }}</option>
                                                                                        @endforeach
                                                                                    </optgroup>
                                                                                </select>
                                                                            </td>
                                                                            <td data-label="Ед. измерения" class="text-center">{{ $material->unit }}</td>
                                                                            <td data-label="Количество" class="text-center material_count">{{ number_format($split->count, 3, '.', '') }}</td>
                                                                            <td data-label="Модификация" class="text-center material_term">
                                                                                <div class="row text-center">
                                                                                    <div class="col-12">
                                                                                  @if (in_array($split->type, [1, 5]) and $split->buyback()->doesntExist())
                                                                                            <button rel="tooltip" onclick="split_more(2, {{ $split->count }}, {{ $split->id }}, '{{ $split->type }}')" class="btn-success btn-link btn-xs btn mn-0" data-original-title="Обратный выкуп" style="padding: 0px">
                                                                                                <i class="fa fa-undo"></i>
                                                                                            </button>
                                                                                    @elseif ($split->type == 3 and $split->security()->doesntExist())
                                                                                            <button rel="tooltip" onclick="split_more(4, {{ $split->count }}, {{ $split->id }}, '{{ $split->type }}', '{{ $split->time }}')" class="btn-success btn-link btn-xs btn mn-0" data-original-title="Обеспечительный платеж" style="padding: 0px">
                                                                                                <i class="fa fa-ruble"></i>
                                                                                            </button>
                                                                                    @endif
                                                                                    </div>
                                                                                </div>
                                                                            </td>
                                                                            <td data-label="Стоимость за ед.,руб" class="text-center  each_material_price_td">
                                                                                @if($material->is_our === 0)
                                                                                    <input type="text" {{ ($split->type == '5') ? 'disabled' : '' }} value="{{ ($split->type == '5') ? '' : $split->price_per_one }}" class="form-control price-input" maxlength="9" readonly>
                                                                                @else
                                                                                    <input type="text" {{ ($split->type == '5') ? 'disabled' : '' }} onchange="change_material_price(this, {{ $material->manual_material_id }}, {{ $material->work_group_id }}, {{ $split->count }}, '{{ $split->id }}')" value="{{ (float)$split->price_per_one }}" class="form-control price-input  price-one" maxlength="9">
                                                                                @endif
                                                                            </td>
                                                                            <td  data-label="Общая стоимость, руб" class="text-center general_material_price_td">
                                                                                <input type="text" onchange="change_security_pay_all(this, {{ $split->id }}, {{ $material->work_group_id }})" readonly="readonly" class="form-control price-input @if($material->is_our === 1)  common_material_price  general_price @endif  material_result_price_{{ $material->work_group_id }}" value="{{ (float)$split->reslt_price }}" maxlength="9">
                                                                            </td>
                                                                            <td data-label="Поставщик">
                                                                                @if($split->contractor)
                                                                                    {{ $split->contractor->short_name }}
                                                                                @else
                                                                                    <div class="form-check mat-form-check" style="display:none">
                                                                                        <label class="form-check-label">
                                                                                            <input class="form-check-input material_check" name="subcontractor_works[]" value="{{ $split->id }}" form="add_mat_subcontractor_form" type="checkbox">
                                                                                            <span class="form-check-sign"></span>
                                                                                        </label>
                                                                                    </div>
                                                                                @endif
                                                                            </td>
                                                                            @if($id != 2)
                                                                            <td data-label="Б/у" class="text-left check_used_material">
                                                                                <div class="form-check">
                                                                                    <label class="form-check-label">
                                                                                        <input id="materials_is_used" onchange="change_material_used(this, {{ $split->id }})" class="form-check-input" name="is_used" type="checkbox" value="1" {{ $splits->where('id', ($split->id))->pluck('is_used')->first() ? 'checked' : '' }}>
                                                                                        <span class="form-check-sign"></span>
                                                                                    </label>
                                                                                </div>
                                                                            </td>
                                                                            @else
                                                                                <td></td>
                                                                            @endif

                                                                            <td class="text-right action_material actions">
                                                                                <button rel="tooltip" onclick="avans_modal_show('{{ $material->name }}', $(this).closest('tr').find('.general_price').first().val().replace(/ /g, '').replace(/,/g, '.'), '{{ $split->type }}')" data-toggle="modal" data-target="#add_avans_to_cp" class="btn-info btn-link btn-xs btn pd-0 mn-0" data-original-title="Авансирование">
                                                                                    <i class="avans"></i>
                                                                                </button>
                                                                            </td>

                                                                            <td class="td-0"></td>
                                                                        </tr>
                                                                    @endif

                                                                    @if($split->security()->exists())
                                                                        <tr
                                                                            @if($split->security->reviews->count() > 0)
                                                                            onclick="showReview({{ $split->security->reviews }}, this)" id="{{ 'Split' . $split->security->id }}" style="background-color: {{ $split->security->reviews[0]->result_status == 0 ? '#fdd8d8':'#C9FFD4'  }};" @endif
                                                                        class=" material">
                                                                            <td data-label="Наименование" class="material_name_td">Обеспечительный платеж за {{ $split->name }}</td>
                                                                            <td data-label="Ед. измерения" class="text-center">{{ $material->unit }}</td>
                                                                            <td data-label="Количество" class="text-center material_count">{{ number_format($split->security->count, 3, '.', '') }}</td>
                                                                            <td data-label="Модификация" class="text-center material_term"></td>
                                                                            <td data-label="Стоимость за ед.,руб" class="text-center each_material_price_td">
                                                                               <input type="text" onchange="change_security_pay(this, {{ $split->security->id }}, {{ $material->work_group_id }}, {{ $split->security->count }}, {{ 4 }}, {{ $split->time }})" value="{{ (float)$split->security->security_price_one }}" class="form-control price-input price-one" maxlength="9">
                                                                            </td>
                                                                            <td data-label="Общая стоимость, руб" class="text-center general_material_price_td">
                                                                                <input type="text" onchange="change_security_pay_all(this, {{ $split->security->id }}, {{ $material->work_group_id }}, {{ $split->security->count }}, {{ 4 }})" class="form-control price-input common_material_price general_price security_pay material_result_price_{{ $material->work_group_id }}" value="{{ (float)$split->security->security_price_result }}" maxlength="9">
                                                                            </td>
                                                                            <td></td>
                                                                            @if($id != 2)
                                                                            <td data-label="Б/у" class="check_used_material"></td>
                                                                                @else
                                                                                <td></td>
                                                                            @endif
                                                                            <td class="text-right action_material actions">
                                                                                <button rel="tooltip"  data-original-title="Удалить" class="btn-danger btn-link btn-xs btn pd-0" onclick="delete_securuty_payment(this, {{ $split->security->id }}, {{ $material->work_group_id }}, '{{ 4 . '|' . $split->time }}')">
                                                                                    <i class="fa fa-times"></i>
                                                                                </button>
                                                                                <button rel="tooltip" onclick="avans_modal_show('{{ $material->name }}', $(this).closest('tr').find('.general_price').first().val().replace(/ /g, '').replace(/,/g, '.'), '2')" data-toggle="modal" data-target="#add_avans_to_cp" class="btn-info btn-link btn-xs btn pd-0 mn-0" data-original-title="Авансирование">
                                                                                    <i class="avans"></i>
                                                                                </button>
                                                                            </td>
                                                                            <td class="td-0"></td>
                                                                        </tr>
                                                                    @endif
                                                                @endforeach
                                                            @endforeach

                                                            <tr class="total">
                                                                {{--<td></td>--}}
                                                                <td colspan="5" class="result">
                                                                    {{ ['Итого по материалам для устройства шпунтового ограждения',
                                                                    'Итого по материалам для устройства свайного поля',
                                                                    'Итого по материалам для земельных работ',
                                                                    'Итого по материалам для монтажа систем крепления',
                                                                    'Итого по материалам для дополнительных работ',
                                                                        ][$id - 1] }}</td>
                                                                <td class="text-center sum">
                                                                    <input style="margin-top:0" type="text" id="result_material_{{ $id }}" class="form-control price-input" readonly="readonly" value="{{ number_format((float)array_sum($splits->whereIn('man_mat_id', $group->pluck('manual_material_id')->unique())->pluck('result_price')->toArray()) + (float)array_sum($splits->where('type', 4)->whereIn('man_mat_id', $group->pluck('manual_material_id')->unique())->pluck('security_price_result')->toArray()), 2, ',', ' ') }}">
                                                                </td>
                                                                <td></td>
                                                                <td></td>
                                                                <td></td>
                                                                <td class="td-0"></td>
                                                            </tr>
                                                        @endforeach

                                                        @if ($splits->where('type', 4)->count() > 0)
                                                            <tr class="tr-title">
                                                                {{--<td></td>--}}
                                                                <td class="th-text">Возврат обеспечительного платежа:</td>
                                                                <td class="th-text"></td>
                                                                <td class="text-center"></td>
                                                                <td class="text-center"></td>
                                                                <td class="text-center"></td>
                                                                <td></td>
                                                                <td></td>
                                                                <td></td>
                                                                <td></td>
                                                                <td class="td-0"></td>
                                                            </tr>
                                                            @foreach ($splits->where('type', 4) as $secure)
                                                                <tr class="material">

                                                                    <td>Возврат обеспечительного платежа за {{ $secure->parent->name }}</td>
                                                                    <td data-label="Ед. измерения" class="text-center">{{ $secure->WV_material->unit ?? 'т' }}</td>
                                                                    <td data-label="Количество" class="text-center">{{ number_format($secure->count, 3, '.', '') }}</td>
                                                                    <td class="text-center"></td>
                                                                    <td data-label="Стоимость за ед.,руб" class="text-center each_material_price_td">
                                                                        <input id="info_{{ $secure->id }}" type="text" readonly value="{{ (float)$secure->security_price_one }}" class="form-control duplicate price-input price-one" maxlength="9">
                                                                    </td>
                                                                    <td data-label="Общая стоимость, руб" class="text-center general_material_price_td">
                                                                        <input id="general_info_{{ $secure->id }}" type="text" readonly class="form-control price-input duplicate common_material_price general_price security_pay material_result_price_{{ $secure->WV_material->work_group_id }}" value="{{ (float)$secure->security_price_result }}" maxlength="9">
                                                                    </td>
                                                                    <td></td>
                                                                    <td></td>
                                                                    <td></td>
                                                                    <td class="td-0"></td>
                                                                </tr>
                                                            @endforeach
                                                        @endif

                                                        @if ($splits->where('type', 2)->count() > 0)
                                                            <tr class="tr-title">
                                                                {{--<td></td>--}}
                                                                <td class="th-text">Обратный выкуп материалов:</td>
                                                                <td class="th-text"></td>
                                                                <td class="text-center"></td>
                                                                <td class="text-center"></td>
                                                                <td class="text-center"></td>
                                                                <td></td>
                                                                <td></td>
                                                                <td></td>
                                                                <td></td>
                                                                <td class="td-0"></td>
                                                            </tr>
                                                            @foreach ($splits->where('type', 2) as $buyback)
                                                                <tr
                                                                        @if($buyback->reviews->count() > 0)
                                                                        onclick="showReview({{ $buyback->reviews }}, this)" id="{{ 'Split' . $buyback->id }}" style="background-color: {{ $splits->where('type', 2)->where('man_mat_id', $buyback->man_mat_id)->first()->reviews[0]->result_status == 0 ? '#fdd8d8':'#C9FFD4'  }};" @endif
                                                                class="material">
                                                                    <td data-label="Наименование" class="material_name_td">Обратный выкуп ({{ $buyback->parent->name }})</td>
                                                                    <td data-label="Ед. измерения" class="text-center">{{ $buyback->WV_material->unit ?? 'т' }}</td>
                                                                    <td data-label="Количество" class="text-center material_count">{{ number_format($buyback->count, 3, '.', '') }}</td>
                                                                    <td data-label="Модификация" class="text-center material_term"></td>
                                                                    <td data-label="Стоимость за ед.,руб" class="text-center each_material_price_td">
                                                                        <input type="text" onchange="change_security_pay(this, {{ $buyback->id }}, {{ $buyback->WV_material->work_group_id }}, {{ $buyback->count }}, {{ 2 }})" value="{{ (float)$buyback->security_price_one }}" class="form-control price-input price-one" maxlength="9">
                                                                    </td>
                                                                    <td data-label="Общая стоимость, руб" class="text-center general_material_price_td">
                                                                        <input type="text" onchange="change_security_pay_all(this, {{ $buyback->id }}, {{ $buyback->WV_material->work_group_id }}, {{ $buyback->count }}, {{ 2 }})" class="form-control price-input buyback common_material_price general_price security_pay material_result_price_{{ $buyback->WV_material->work_group_id }}" value="{{ (float)$buyback->security_price_result }}" maxlength="9">
                                                                    </td>
                                                                    <td></td>
                                                                    <td></td>
                                                                    <td class="text-right action_material actions">
                                                                        <button rel="tooltip"  data-original-title="Удалить" class="btn-danger btn-link btn-xs btn pd-0" onclick="delete_securuty_payment(this, {{ $buyback->id }}, {{ $buyback->WV_material->work_group_id }}, '{{ 2 }}')">
                                                                            <i class="fa fa-times"></i>
                                                                        </button>
                                                                    </td>
                                                                    <td class="td-0"></td>
                                                                </tr>
                                                            @endforeach
                                                        @endif
                                                        @if ($splits->whereIn('type', [2,4])->count() > 0)
                                                            <tr class="total">
                                                                {{--<td></td>--}}
                                                                <td class="result">
                                                                    Итого по {{ [
                                                                2 => 'обратному выкупу',
                                                                4 => 'возврату обеспечительного платежа',
                                                                3 => 'обратному выкупу и возврату обеспечительного платежа'
                                                                ][$splits->whereIn('type', [2,4])->pluck('type')->unique()->count() == 2 ? 3 : $splits->whereIn('type', [2,4])->pluck('type')->unique()[0]] }}
                                                                </td>
                                                                <td></td>
                                                                <td></td>
                                                                <td></td>
                                                                <td></td>
                                                                <td class="text-center sum">
                                                                    <input style="margin-top:0" type="text" id="result_material_buyback" class="form-control price-input" readonly="readonly" value="{{ number_format((float)array_sum($splits->pluck('security_price_result')->toArray()), 2, ',', ' ') }}">
                                                                </td>
                                                                <td></td>
                                                                <td></td>
                                                                <td></td>
                                                                <td class="td-0"></td>
                                                            </tr>
                                                        @endif


                                                        @php $result_material_cost = array_sum($splits->pluck('result_price')->toArray()) + array_sum($splits->where('type', 4)->pluck('security_price_result')->toArray()); @endphp
                                                        @php $result_security_pay = array_sum($splits->pluck('security_price_result')->toArray()); @endphp
                                                        <tr class="medium-total">
                                                            {{--<td></td>--}}
                                                            <td colspan="5" class="text-left result">Итого по материалам:</td>
                                                            <td class="text-center sum">
                                                                <input style="margin-top:0" type="text" id="result_material" class="form-control price-input" readonly="readonly" value="{{ number_format($result_material_cost, 2, ',', ' ') }}">
                                                            </td>
                                                            <td></td>
                                                            <td></td>
                                                            <td></td>
                                                            <td class="td-0"></td>
                                                        </tr>
                                                        @php $result_cost = $result_works_cost + $result_material_cost; @endphp
                                                        <tr class="grand-total">
                                                            {{--<td></td>--}}
                                                            <td colspan="5" class="text-left result">Итого по проекту:</td>
                                                            <td class="text-center sum">
                                                                <input style="margin-top:0" type="text" id="result" class="form-control price-input" readonly="readonly" value="{{ number_format($result_cost, 2, ',', ' ') }}">
                                                            </td>
                                                            <td></td>
                                                            <td></td>
                                                            <td></td>
                                                            <td class="td-0"></td>
                                                        </tr>
                                                        @php $result_cost = $result_cost - $result_security_pay; @endphp
                                                            <tr class="grand-total" {!!  ($result_security_pay > 0) ? '' : 'style="display: none;"' !!}>
                                                                {{--<td></td>--}}
                                                                <td colspan="5" class="text-left result">Итого по проекту после обратного выкупа и возврата обеспечительного платежа:</td>
                                                                <td class="text-center sum">
                                                                    <input style="margin-top:0" type="text" id="result_without_security" class="form-control price-input" readonly="readonly" value="{{ number_format($result_cost, 2, ',', ' ') }}">
                                                                </td>
                                                                <td></td>
                                                                <td></td>
                                                                <td></td>
                                                                <td class="td-0"></td>
                                                            </tr>
                                                        <tr>
                                                            {{--<td></td>--}}
                                                            <td colspan="3"></td>
                                                            <td class="text-center">НДС(%):</td>
                                                            <td class="text-center">
                                                                <input style="margin-top:0" type="text" id="nds" class="form-control price-input" onchange="calc_nds(this)" value="{{ $commercial_offer->nds }}">
                                                            </td>
                                                            <td class="text-center">
                                                                <input style="margin-top:0" type="text" id="result_nds" class="form-control price-input" readonly="readonly" value="{{ number_format($result_cost - $result_cost / ($commercial_offer->nds / 100 + 1), 2, ',', ' ') }}">
                                                            </td>
                                                            <td colspan="3"></td>
                                                            <td class="td-0"></td>
                                                        </tr>
                                                        </tbody>
                                                    </table>
                                                </div>
                                            </div>
                                        </div>
                                    @endif

                                    <div class="row">
                                        <div class="col-md-12">
                                            <div class="card" >
                                                <div class="form-container">
                                                    <h6 class="thin">Авансирования</h6>
                                                    <div id="containerAdvancementCard">
                                                        <div class="row">
                                                            <div class="col-md-6">
                                                                <div class="text-left">
                                                                    <button type="button" class="btn btn-sm btn-round btn-outline btn-info" style="margin-top: 15px;" onclick="send_avans('empty')">Добавить авансирование</button>
                                                                </div>
                                                            </div>
                                                        </div>
                                                        @if($commercial_offer->advancements->count())
                                                            <div class="row">
                                                                @foreach ($commercial_offer->advancements as $advancement)
                                                                    <div class="copyAdvancementCard col-md-6" style="display: inline-block">
                                                                        <div class="row justify-content-center" style="margin-top:10px">
                                                                            <div class="col-md-12">
                                                                                <button type="button" class="close" onclick="remove_element_advancement(this, {{ $advancement->id }})">
                                                                                    <span aria-hidden="true">×</span>
                                                                                </button>
                                                                                <textarea
                                                                                        @if($advancement->reviews->count() > 0) onclick="showReview({{ $advancement->reviews }}, this)" id="{{ 'advancement' . $advancement->id }}" style="background-color: {{ $advancement->reviews[0]->result_status == 0 ? '#fdd8d8':'#C9FFD4'  }};" @endif
                                                                                        class="form-control textarea-rows" columns="50" rows="10" name="notes[]" id="adnvancement" onchange="update_adv({{ $advancement->id }}, this)" placeholder="Укажите примечание">{{ $advancement->description }}</textarea>
                                                                            </div>
                                                                        </div>
                                                                    </div>
                                                                @endforeach
                                                            </div>
                                                        @endif
                                                        <hr>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <!-- Примечания -->
                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="card" >
                                                <div class="form-container">
                                                    <h6 class="thin">Примечания к документу</h6>
                                                    <div id="containerCommentCard">
                                                        @foreach ($commercial_offer->notes as $note)
                                                            <div class="copyCommentCard">
                                                                <div class="row justify-content-center" style="margin-top:10px">
                                                                    <div class="col-md-12">
                                                                        <button type="button" class="close" onclick="remove_element_comment(this, {{ $note->id }})">
                                                                            <span aria-hidden="true">×</span>
                                                                        </button>
                                                                        <textarea
                                                                                @if($note->reviews->count() > 0) onclick="showReview({{ $note->reviews }}, this)" id="{{ 'note' . $note->id }}" style="background-color: {{ $note->reviews[0]->result_status == 0 ? '#fdd8d8':'#C9FFD4'  }};" @endif
                                                                        class="form-control textarea-rows" onchange="update_comment({{ $note->id }}, this)" name="note" required placeholder="Укажите примечание">{{ $note->note }}</textarea>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        @endforeach
                                                    </div>
                                                    <div class="row">
                                                        <div class="col-md-12 text-left pd-l-0">
                                                            <button type="button" class="btn btn-sm btn-round btn-outline btn-info" data-toggle="modal" data-target="#add_manual_notes" style="margin-top: 15px;">Добавить стандартное примечание</button>
                                                            <button type="button" class="btn btn-sm btn-round btn-outline btn-success" id="addCommentCard" style="margin-top: 15px;">
                                                                <i class="fa fa-plus"></i>
                                                                Добавить примечание
                                                            </button>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="card" >
                                                <div class="form-container">
                                                    <h6 class="thin">Требования к заказчику</h6>
                                                    <div id="containerRequireCard">
                                                        @foreach($commercial_offer->requirements as $requirement)
                                                            <div
                                                            class="copyRequireCard">
                                                                <div class="row justify-content-center" style="margin-top:10px">
                                                                    <div class="col-md-12">
                                                                        <button type="button" class="close" onclick="remove_element_require(this, {{ $requirement->id }})">
                                                                            <span aria-hidden="true">×</span>
                                                                        </button>
                                                                        <textarea
                                                                                @if($requirement->reviews->count() > 0) onclick="showReview({{ $requirement->reviews }}, this)" id="{{ 'requirement' . $requirement->id }}" style="background-color: {{ $requirement->reviews[0]->result_status == 0 ? '#fdd8d8':'#C9FFD4'  }};" @endif
                                                                        class="form-control textarea-rows" onchange="update_require({{ $requirement->id }}, this)" name="requirement" required placeholder="Укажите требование к заказчику">{{ $requirement->requirement }}</textarea>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        @endforeach
                                                    </div>
                                                    <div class="row">
                                                        <div class="col-md-12 text-left pd-l-0">
                                                            <button type="button" class="btn btn-sm btn-round btn-outline btn-info" data-toggle="modal" data-target="#add_manual_requirement" style="margin-top: 15px;">Добавить стандартное требование</button>
                                                            <button type="button" class="btn btn-sm btn-round btn-outline btn-success" id="addRequireCard" style="margin-top: 15px;">
                                                                <i class="fa fa-plus"></i>
                                                                Добавить требование
                                                            </button>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                        <hr>
                                        <div class="row justify-content-between">
                                            <div class="col-md-7 col-xl-6">
                                                @if( $signers->count() > 0)
                                                <div class="card" >
                                                    <div class="form-container mobile-select-container">
                                                        <h6 class="thin">Выбрать ответственного за подписание КП</h6>
                                                        <select id="select_signer" name="singer_user_id" class="selectpicker" onchange="set_signer(this.value)" data-style="btn-default btn-outline" data-menu-style="dropdown-blue" required>
                                                            @foreach($signers as $signer)
                                                                <option value="{{ $signer->id }}" @if($signer->id === $commercial_offer->signer_user_id) selected @endif>
                                                                    {{ $signer->group_name . ':'}} {{ !is_null($signer->first_name) ? mb_substr($signer->first_name, 0, 1) . '.' : ''}} {{ !is_null($signer->patronymic) ? mb_substr($signer->patronymic, 0, 1) . '.' : ''}} {{ !is_null($signer->last_name) ? $signer->last_name : ''}}
                                                                </option>
                                                            @endforeach
                                                        </select>
                                                    </div>
                                                </div>
                                                @endif

                                                <div class="card">
                                                    <div class="form-container mobile-select-container">
                                                        <h6 class="thin">Заголовок Коммерческого Предложения</h6>
                                                        <div class="row justify-content-center" style="margin-top:10px">
                                                            <div class="col-md-12">
                                                                <textarea class="form-control textarea-rows" onchange="update_title(this)" name="title" placeholder="Укажите необходимый заголовок">{{ $title }}</textarea>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="col-md-7 col-xl-6">
                                                <div class="card" >
                                                    <div class="form-container mobile-select-container">
                                                        <h6 class="thin">Адресат КП</h6>
                                                        <select name="contact_id" class="selectpicker" onchange="set_contact(this.value)" data-style="btn-default btn-outline" data-menu-style="dropdown-blue" required>
                                                            <option value="0" selected>Не выбран</option>
                                                            @foreach($contacts as $contact)
                                                                <option value="{{ $contact->id }}" @if($contact->id === $commercial_offer->contact_id) selected @endif>
                                                                    {{ $contact->position . ':'}} {{ !is_null($contact->first_name) ? mb_substr($contact->first_name, 0, 1) . '.' : ''}} {{ !is_null($contact->patronymic) ? mb_substr($contact->patronymic, 0, 1) . '.' : ''}} {{ !is_null($contact->last_name) ? $contact->last_name : ''}}
                                                                </option>
                                                            @endforeach
                                                        </select>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    <div class="row" style="margin-top:30px">
                                        <div class="col-md-8 col-xl-6">
                                            <div class="form-check">
                                                <label class="form-check-label">
                                                    <input class="form-check-input" type="checkbox" value="0" id="show_contract_number" onclick="" name="" {{ ($commercial_offer->contract_date or $commercial_offer->contract_number) ? 'checked' : '' }}>
                                                    <span class="form-check-sign"></span>
                                                    <span class="lable-check" style="text-transform:none;font-size:14px">
                                                        Присвоить номер договора графику производства работ
                                                    </span>
                                                </label>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="row" id="contract_number" style="padding-left:38px; {{ ($commercial_offer->contract_date or $commercial_offer->contract_number) ? '' : 'display:none' }}">
                                        <div class="col-md-2" style="margin-top:2px">
                                            <div class="form-group">
                                                <label for="">Номер договора</label>
                                                <input class="form-control" id="" type="text" onchange="set_contract_number(this)" value="{{ $commercial_offer->contract_number }}" name="contract_number" placeholder="Введите номер">
                                            </div>
                                        </div>
                                        <div class="col-md-2">
                                            <div class="form-group">
                                                <label class="col-form-label" style="padding-bottom:0">Дата договора</label>
                                                <input id="contract_date" name="contract_date" type="text" class="form-control datepicker" placeholder="Выберите дату" autocomplete="off">
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        @if($commercial_offer_requests->where('status', 0)->count() == 0 and $commercial_offer->status == 1)

                            <div class="card-footer">
                                <div class="row">
                                    <div class="col-md-12">
                                        <div class="text-center">
                                            <button type="button" class="btn btn-info btn-wd" data-toggle="modal" data-target="#save-offer">Сформировать КП</button>
                                        </div>
                                    </div>
                                </div>
                            </div>

                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Модалки -->

    @foreach ($commercial_offer_requests as $offer_request)
        <div class="modal fade bd-example-modal-lg show" id="view-request-offer{{ $offer_request->id }}" role="dialog" aria-labelledby="modal-search" style="display: none;">
            <div class="modal-dialog modal-lg" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Заявка на КП</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">×</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <hr style="margin-top:0">
                        <div class="card border-0" >
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-md-12">
                                        <label class="control-label">Описание</label>
                                        <p>
                                            {{ $offer_request->description }}
                                        </p>
                                    </div>
                                </div>
                                @if ($offer_request->files->where('is_result', 0)->count() > 0)
                                    <div class="row">
                                        <div class="col-md-12">
                                            <label class="control-label">Приложенные файлы</label>
                                            <br>
                                            @foreach($offer_request->files->where('is_result', 0)->where('is_proj_doc', 0) as $file)
                                                <a target="_blank" href="{{ asset('storage/docs/commercial_offer_request_files/' . $file->file_name) }}">
                                                    {{ $file->original_name }}
                                                </a>
                                                <br>
                                            @endforeach

                                            @foreach($offer_request->files->where('is_result', 0)->where('is_proj_doc', 1) as $file)
                                                <a target="_blank" href="{{ asset('storage/docs/project_documents/' . $file->file_name) }}">
                                                    {{ $file->original_name }}
                                                </a>
                                                <br>
                                            @endforeach
                                        </div>
                                    </div>
                                @endif
                                <br>
                                @if($offer_request->status != 0)
                                    <div class="row">
                                        <div class="col-md-12">
                                            <div class="form-group">
                                                <div class="row">
                                                    <div class="col-md-12">
                                                        <h6 style="margin:10px 0">
                                                            Решение
                                                        </h6>
                                                        <p class="form-control-static">{{ $offer_request->result_comment }}</p>
                                                    </div>
                                                </div>
                                                @if ($offer_request->files->where('is_result', 1)->count() > 0)
                                                    <div class="row">
                                                        <div class="col-md-12">
                                                            <label class="control-label">Приложенные файлы</label>
                                                            <br>
                                                            @foreach($offer_request->files->where('is_result', 1)->where('is_proj_doc', 0) as $file)
                                                                <a target="_blank" href="{{ asset('storage/docs/commercial_offer_request_files/' . $file->file_name) }}">
                                                                    {{ $file->original_name }}
                                                                </a>
                                                                <br>
                                                            @endforeach

                                                            @foreach($offer_request->files->where('is_result', 1)->where('is_proj_doc', 1) as $file)
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
                                @else
                                    <form id="update_request_form{{ $offer_request->id }}" action="{{ route('projects::commercial_offer::requests::update') }}" method="post" enctype="multipart/form-data">
                                        @csrf
                                        <hr>
                                        <input type="hidden" name="offer_request_id" value="{{ $offer_request->id }}">
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
                                                            <div class="form-check form-check-radio">
                                                                <label class="form-check-label"  style="text-transform:none;font-size:13px">
                                                                    <input class="form-check-input" type="radio" name="status" id="" value="confirm" required>
                                                                    <span class="form-check-sign"></span>
                                                                    Подтвердить
                                                                </label>
                                                            </div>
                                                        </div>
                                                        <div class="col-md-4" style="padding-left:0;">
                                                            <div class="form-check form-check-radio">
                                                                <label class="form-check-label" style="text-transform:none;font-size:13px">
                                                                    <input class="form-check-input" type="radio" name="status" id="" value="reject" required>
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
                                                                <div id="fileNameOffer" class="file-name"></div>
                                                                <div class="file-upload ">
                                                                    <label class="pull-right">
                                                                        <span><i class="nc-icon nc-attach-87" style="font-size:25px; line-height: 40px"></i></span>
                                                                        <input type="file" name="documents[]" accept="*" id="uploadedOfferFiles" class="form-control-file file " multiple>
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
                        @if ($offer_request->status === 0)
                            <button id="submit_wv" type="submit" form="update_request_form{{ $offer_request->id }}" class="btn btn-info">Сохранить</button>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    @endforeach

    <div class="modal fade bd-example-modal-lg show" id="add_manual_notes" role="dialog" aria-labelledby="modal-search" style="display: none;">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Добавление примечания</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">×</span>
                    </button>
                </div>
                <div class="modal-body">
                    <hr style="margin-top:0">
                    <div class="card border-0" >
                        <div class="card-body">
                            <form id="form_add_manual_notes" class="form-horizontal" action="{{ route('projects::commercial_offer::add_manual_note', $commercial_offer->id) }}" method="post" enctype="multipart/form-data">
                                @csrf
                                <button type="submit" disabled style="display: none" aria-hidden="true"></button>
                                <div class="row">
                                    <div class="col-md-12">
                                        <label>Примечания<star class="star">*</star></label>
                                        <div class="form-group">
                                            <select name="names[]" id="PS" style="width: 100%" data-style="btn-default btn-outline" data-menu-style="dropdown-blue" multiple required>
                                                @foreach($manual_notes as $manual_note)
                                                    <option is_need="{{ $manual_note->need_value }}">{{ $manual_note->name }}</option>
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
                    <button form="form_add_manual_notes" type="button" onclick="submit_half_ajax(this)" class="btn btn-info">Сохранить</button>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade bd-example-modal-lg show" id="add_manual_requirement" role="dialog" aria-labelledby="modal-search" style="display: none;">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Добавление требований</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">×</span>
                    </button>
                </div>
                <div class="modal-body">
                    <hr style="margin-top:0">
                    <div class="card border-0" >
                        <div class="card-body">
                            <form id="form_add_manual_requirements" class="form-horizontal" action="{{ route('projects::commercial_offer::add_manual_requirement', $commercial_offer->id) }}" method="post" enctype="multipart/form-data">
                                @csrf
                                <button type="submit" disabled style="display: none" aria-hidden="true"></button>
                                <div class="row">
                                    <div class="col-md-12">
                                        <label>Требования<star class="star">*</star></label>
                                        <div class="form-group">
                                            <select name="names[]" id="demand" style="width: 100%" data-style="btn-default btn-outline" data-menu-style="dropdown-blue" multiple required>
                                                @foreach($manual_requirements as $manual_requirement)
                                                    <option is_need="{{ $manual_requirement->need_value }}">{{ $manual_requirement->name }}</option>
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
                    <button form="form_add_manual_requirements" type="button" onclick="submit_half_ajax(this)" class="btn btn-info">Сохранить</button>
                </div>
            </div>
        </div>
    </div>

    @if($commercial_offer_requests->where('status', 0)->count() == 0 and $commercial_offer->status == 1)
        <div class="modal fade bd-example-modal-lg show" id="save-offer" role="dialog" aria-labelledby="modal-search" style="display: none;">
            <div class="modal-dialog modal-lg" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Коммерческое предложение</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">×</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <hr style="margin-top:0">
                        <div class="card border-0" >
                            <div class="row" style="margin-top:15px">
                                <div class="col-md-5">
                                    @if($commercial_offer->file_name)
                                        <a target="_blank" class="old_parts" target="_blank" href="{{ asset('storage/docs/commercial_offers/' . $commercial_offer->file_name) }}">Коммерческое предложение.pdf</a>
                                    @else
                                        <p class="old_parts">Документ отсутствует</p>
                                    @endif
                                    <a class="new_parts d-none" target="_blank">Коммерческое предложение.pdf</a>
                                </div>
                                <div class="col-md-7 text-right" style="margin-top:3px">
                                    <button type="button" class="btn btn-round btn-sm btn-outline" id="budget">Бюджет</button>
                                    <button type="button" class="btn btn-round btn-sm btn-outline" id="upload">Загрузить готовый файл КП</button>
                                    <a id="offer_create" target="_blank" href="{{ route('projects::commercial_offer::create_pdf', $commercial_offer->id) }}" class="btn btn-primary btn-outline btn-round btn-sm">Сформировать документ</a>
                                </div>
                            </div>
                            <form id="attach_document" method="post" action="{{ route('projects::commercial_offer::attach_document', $commercial_offer->id) }}" enctype="multipart/form-data">
                                @csrf
                                <div class="row" id="file" style="display:none; margin-top:40px">
                                    <label class="col-sm-5 col-form-label" for="" style="font-size:0.80">
                                        Коммерческое предложение
                                    </label>
                                    <div class="col-sm-7">
                                        <div class="file-container">
                                            <div id="fileName5" class="file-name"></div>
                                            <div class="file-upload ">
                                                <label class="pull-right">
                                                    <span><i class="nc-icon nc-attach-87" style="font-size:25px; line-height: 40px"></i></span>
                                                    <input type="file" name="commercial_offer" accept="*" id="uploadedFile5" class="form-control-file file">
                                                </label>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="row" id="file_budget" style="display:none; margin-top:40px">
                                    <label class="col-sm-5 col-form-label" for="" style="font-size:0.80">Бюджет</label>
                                    <div class="col-sm-7">
                                        <div class="file-container">
                                            <div id="fileName6" class="file-name"></div>
                                            <div class="file-upload ">
                                                <label class="pull-right">
                                                    <span><i class="nc-icon nc-attach-87" style="font-size:25px; line-height: 40px"></i></span>
                                                    <input type="file" name="budget" accept="*" id="uploadedFile6" class="form-control-file file">
                                                </label>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="row" id="file_budget" style="margin-top:40px">
                                    <label class="col-sm-2 col-form-label" style="font-size:0.80">Комментарий</label>
                                    <div class="col-sm-10">
                                        <textarea class="form-control textarea-rows" name="comment" maxlength="1000"></textarea>
                                    </div>
                                </div>
                            </form>
                        </div>
                        <div class="row" style="margin-top: 50px">
                            <div class="col-md-12 text-center">
                                <button type="submit" id="submit_form_attach_document" form="attach_document" class="btn btn-info btn-outline" style="white-space: pre-wrap" disabled>Отправить на внутреннее согласование</button> <!-- Кнопка появляется после загрузки или формирования документа -->
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    @endif

    <div class="modal fade bd-example-modal-lg show" id="add_avans_to_cp" role="dialog" aria-labelledby="modal-search" style="display: none;">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <input class="avans_service" type="hidden" name="avans_service[]">
                    <input class="avans_work" type="hidden" name="avans_work[]">
                    <h5 id="target_name_title" class="modal-title">Авансирование для </h5>
                </div>
                <div class="modal-body">
                    <hr style="margin-top:0">
                    <div class="card border-0" >
                        <div class="row" style="margin-top:15px">
                            <div class="col-md-12">
                                <div class="row">
                                    <div class="col-md-12">
                                        <form id="add_avans" class="" action="{{ route('projects::commercial_offer::add_advancement', [$commercial_offer->project_id, $commercial_offer->id]) }}" method="post">
                                            @csrf
                                            <button type="submit" disabled style="display: none" aria-hidden="true"></button>
                                            <div class="row">
                                                <div class="col-md-8 avans_size">
                                                    <div class="form-group">
                                                        <label>Размер</label>
                                                        <input type="hidden" id="max_avans" name="max_avans">
                                                        <input type="hidden" id="avans_title" name="avans_title">
                                                        <input id="target_name" name="target_name" type="hidden">
                                                        <input id="avans_value" class="form-control" type="number" name="avans_value" required pattern="^\d+(\.|\,)\d{2}$" min="0" placeholder="Укажите размер аванса" step="0.01">
                                                    </div>
                                                </div>
                                                <div class="col-md-4" style="padding-top:40px;">
                                                    <div class="form-check form-check-radio">
                                                        <label class="form-check-label">
                                                            <input class="form-check-input radio_avans" type="radio" required name="avans_unit" checked id="Radio1" onclick="$('#avans_value')[0].max = $('#max_avans').val()" value="руб" >
                                                            <span class="form-check-sign"></span>
                                                            руб.
                                                        </label>
                                                        <label class="form-check-label">
                                                            <input class="form-check-input radio_avans" type="radio" required name="avans_unit" id="Radio2" onclick="$('#avans_value')[0].max = 100" value="%">
                                                            <span class="form-check-sign"></span>
                                                            %
                                                        </label>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="row">
                                                <div class="col-md-12">
                                                    <label>Примечание</label>
                                                    <textarea class="form-control textarea-rows" name="avans_note" maxlength="250" placeholder="Укажите примечание">{{ isset($commercial_offer->advancement->description) ? $commercial_offer->advancement->description : '' }}</textarea>
                                                </div>
                                            </div>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="row" style="margin-top: 50px">
                        <div class="col-md-12 text-center">
                            <button type="button" onclick="send_avans(this)" form="add_avans" class="btn btn-info">Сохранить</button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>


    <!-- D-none -->
    <div class="d-none">
        <!-- ПРИМЕЧАНИЯ -->
        <!-- Поле примечания -->
        <div class="copyCommentCard" id="comment">
            <div class="row justify-content-center" style="margin-top:10px">
                <div class="col-md-12">
                    <button type="button" class="close" onclick="remove_element_comment(this, 0)">
                        <span aria-hidden="true">×</span>
                    </button>
                    <textarea class="form-control textarea-rows" onchange="update_comment(0, this)" name="note" placeholder="Укажите примечание"></textarea>
                </div>
            </div>
        </div>
        <!-- Поле требований -->
        <div class="copyRequireCard" id="require">
            <div class="row justify-content-center" style="margin-top:10px">
                <div class="col-md-12">
                    <button type="button" class="close" onclick="remove_element_require(this, 0)">
                        <span aria-hidden="true">×</span>
                    </button>
                    <textarea class="form-control textarea-rows" onchange="update_require(0, this)" name="requirement" placeholder="Укажите требование к заказчику"></textarea>
                </div>
            </div>
        </div>
    </div>

    <!-- Кнопка для базового URL -->
    <button class="d-none" id="url" url="{{ URL::to('/') }}"></button>

@endsection

@section('js_footer')
    <meta name="csrf-token" content="{{ csrf_token() }}" />

    <script type="text/javascript">

        @if(session()->has('attach_subcontractor'))
            $(document).ready(function () {
                $('html, body').animate({
                    scrollTop: $('#scrollAfterSubcontractorAdd').offset().top
                }, 'fast');
            });
        @elseif(session()->has('attach_mat_subcontractor'))
            $(document).ready(function () {
                $('html, body').animate({
                    scrollTop: $('#scrollAfterAddMatSubcontract').offset().top
                }, 'fast');
            });
        @endif

        var lastNotify;
        var currentNotify;
        var main = new Vue({
            el: '#app1',
            data: {
                notifications: []
            },
            methods: {
                accept(review, notify) {
                    $.ajax({
                        url:"{{ route('projects::store_review', $commercial_offer->id) }}", //SET URL
                        type: 'GET', //CHECK ACTION
                        data: {
                            _token: CSRF_TOKEN,
                            review: review.review, //SET ATTRS
                            form_reviewable_type: review.reviewable_type, //SET ATTRS
                            form_reviewable_id: review.reviewable_id, //SET ATTRS
                            commercial_offer_id: review.commercial_offer_id, //SET ATTRS
                            result_status: 1, //SET ATTRS
                        },
                        dataType: 'JSON',
                        success: function() {
                            $('#'+notify.el_id).css('background-color', '#C9FFD4');
                        },
                    });
                },
                lock(notify) {
                    if(notify.locked) {
                        notify.message = '<div id="app1">' +
                            '<label>' + lastNotify.reviewText.review + ' </label>' +
                            '<br>' +
                            '<button onclick="main.accept(lastNotify.reviewText, lastNotify)" class="btn btn-outline btn-success btn-sm float-left">Подтвердить<i class="fas fa-check"></i> </button>' +
                            '<button onclick="main.lock(lastNotify)" class="btn btn-outline btn-sm float-right">Закрепить<i class="fa fa-lock-open"></i> </button>' +
                            '</div>';
                        notify.duration = 500;
                        notify.locked = false;
                    } else {
                        notify.message = '<div id="app1">' +
                            '<label>' + lastNotify.reviewText.review + ' </label>' +
                            '<br>' +
                            '<button onclick="main.accept(lastNotify.reviewText, lastNotify)" class="btn btn-outline btn-success btn-sm float-left">Подтвердить<i class="fas fa-check"></i> </button>' +
                            '<button onclick="main.lock(lastNotify)" class="btn btn-outline btn-sm float-right">Открепить<i class="fa fa-lock"></i> </button>' +
                            '</div>';
                        notify.duration = 0;
                        notify.locked = true;
                    }
                },

                open(review, e) {
                    lastNotify  ? lastNotify.close() : '' ;
                    lastNotify = main.$notify.info({
                        title: 'Комментарий согласующего',
                        dangerouslyUseHTMLString: true,
                        reviewText: review[0],
                        locked: false,
                        onClose: function() {
                            main.notifications.pop(this);
                        },
                        message: '',
                        el_id: $(e).attr('id'),
                        offset: 200,
                        duration: 3000,
                    });
                    this.notifications.push(lastNotify);
                    lastNotify.message = '<div id="app1">' +
                        '<label>' + lastNotify.reviewText.review + ' </label>' +
                        '<br>' +
                        '<button onclick="main.accept(lastNotify.reviewText, lastNotify)" class="btn btn-outline btn-success btn-sm float-left">Подтвердить<i class="fas fa-check"></i> </button>' +
                        '<button onclick="main.lock(lastNotify)" class="btn btn-outline btn-sm float-right">Закрепить<i class="fa fa-lock-open"></i> </button>' +
                        '</div>';
                    lastNotify.locked = false;
                },
                close() {
                    lastNotify ? lastNotify.close() : '' ;
                }
            }
        });
        $(document).click(function() {
            // main.close();
        });

        function showReview(review, e) {
            main.open(review, e);
        }

        $("#contract_date").datetimepicker({
            format: 'DD.MM.YYYY',
            locale: 'ru',
            icons: {
                time: "fa fa-clock-o",
                date: "fa fa-calendar",
                up: "fa fa-chevron-up",
                down: "fa fa-chevron-down",
                previous: 'fa fa-chevron-left',
                next: 'fa fa-chevron-right',
                today: 'fa fa-screenshot',
                clear: 'fa fa-trash',
                close: 'fa fa-remove'
            },
            maxDate: moment(),
            {!! $commercial_offer->contract_date ? " date: ('" . $commercial_offer->contract_date . "').replace( /(\d{2}).(\d{2}).(\d{4})/, '$2/$1/$3')," : 'date: null' !!}
        }).on('dp.change', function() {
            set_contract_number(this);
        });

        $(document).ready(function(){
            $(".up,.down").click(function(){
                var row = $(this).parents(".work:first");
                if(row.prev().hasClass('work')) {
                    if ($(this).is(".up")) {
                        $(this).prop("disabled",true);
                        $.ajax({
                            url:"{{ route('projects::work_volume::replace_material') }}",
                            type: 'GET',
                            data: {
                                _token: CSRF_TOKEN,
                                first_work_id: $(row).attr('wvw_id'),
                                second_work_id: $(row.prev()).attr('wvw_id'),
                                commercial_offer_id: '{{ $commercial_offer->id }}',
                            },
                            dataType: 'JSON',
                            success: function (data) {
                                row.insertBefore(row.prev());
                                $(".up").removeAttr('disabled');
                            }
                        });
                    }
                }

                if(row.next().hasClass('work')){
                    if ($(this).is(".down")) {
                        $(this).prop("disabled",true);
                        $.ajax({
                            url:"{{ route('projects::work_volume::replace_material') }}",
                            type: 'GET',
                            data: {
                                _token: CSRF_TOKEN,
                                first_work_id: $(row).attr('wvw_id'),
                                second_work_id: $(row.next()).attr('wvw_id'),
                                commercial_offer_id: '{{ $commercial_offer->id }}',

                            },
                            dataType: 'JSON',
                            success: function (data) {
                                row.insertAfter(row.next());
                                $(".down").removeAttr('disabled');
                            }
                        });

                    }
                }
            });
        });

        $(document).ready(function(){
            $(".material-up,.material-down").click(function(){
                var row = $(this).parents(".material:first");
                if(row.prev().hasClass('material')){
                    if ($(this).is(".material-up")) {
                        row.insertBefore(row.prev());
                    }
                }

                if(row.next().hasClass('material')){
                    if ($(this).is(".material-down")) {
                        row.insertAfter(row.next());
                    }
                }
            });
        });

        var CSRF_TOKEN = $('meta[name="csrf-token"]').attr('content');

        $(document).ready(function() {

            new AutoNumeric.multiple('[class*="price-input"]:not(.term):not(.general_price):not(#result_work_1):not(#result_work_2):not(#result_work_3):not(#result_work_4):not(#result_work):not(#result_material_1):not(#result_material_2):not(#result_material_3):not(#result_material_4):not(#result_material):not(#result):not(#result_without_security):not(#result_nds):not(#result_material_buyback)', { digitGroupSeparator: ' ', decimalCharacter: ',' , minimumValue: '0', maximumValue: '999999999'});

            new AutoNumeric.multiple('.general_price', { digitGroupSeparator: ' ', decimalCharacter: ',' , minimumValue: '0', maximumValue: '9999999999999'});
            new AutoNumeric.multiple('#result_work_1, #result_work_2, #result_work_3, #result_work_4, #result_work, #result_material_1, #result_material_2, #result_material_3, #result_material_4, #result_material, #result, #result_without_security, #result_nds, #result_material_buyback', { digitGroupSeparator: ' ', decimalCharacter: ',' , minimumValue: '0', maximumValue: '9999999999999'});


            $.each($("[class^='hide'],[class*=' hide']").find('input'), function() {
                $(this).attr('disabled','disabled');
            });
            $.each($("[class^='hide'],[class*=' hide']").find('select'), function() {
                $(this).attr('disabled','disabled');
            });

            $.each($('.material').find('.price-one'),function() {
                $(this).trigger('change');
            });
        });

        $('input.work_check').change(function() {
            if($('input.work_check:checked').length > 0 && $('input#uploadedFile4')[0].files.length > 0) {
                $('#submit_contr').removeClass('d-none');
            } else {
                $('#submit_contr').addClass('d-none');
            }
        });

        $('input#uploadedFile4').change(function(){
            var files = $(this)[0].files;
            var fileExtension = ['jpeg', 'jpg', 'png', 'gif', 'bmp', 'txt', 'doc', 'docx', 'xls', 'xlsx', 'pdf', 'rtf', 'dwg', 'dwl', 'dwl2', 'dxf', 'mpp', 'pptx'];

            if ($.inArray($(this).val().split('.').pop().toLowerCase(), fileExtension) == -1) {
                swal({
                    title: "Внимание",
                    text: "Поддерживаемые форматы: "+fileExtension.join(', '),
                    type: 'warning',
                });
                $(this).val('');
                $(this).parent().parent().siblings('#fileName5')[0].innerHTML = '';

                return false;
            } else {
                document.getElementById('fileName4').innerHTML = 'Количество файлов: ' + files.length;
                if (files.length === 1) {
                    document.getElementById('fileName4').innerHTML = 'Имя файла: ' + $('#uploadedFile4').val().split('\\').pop();
                    if ($('input.work_check:checked').length > 0 && $('input#uploadedFile4')[0].files.length > 0) {
                        $('#submit_contr').removeClass('d-none');
                    } else {
                        $('#submit_contr').addClass('d-none');
                    }
                } else {
                    $('#submit_contr').addClass('d-none');
                }
            }
        });

        $('input.material_check').change(function() {
            if($('input.material_check:checked').length > 0 && $('input#uploadedFile')[0].files.length > 0) {
                $('#submit_mat_contr').removeClass('d-none');
            } else {
                $('#submit_mat_contr').addClass('d-none');
            }
        });

        $('input#uploadedFile').change(function(){
            var files = $(this)[0].files;
            var fileExtension = ['jpeg', 'jpg', 'png', 'gif', 'bmp', 'txt', 'doc', 'docx', 'xls', 'xlsx', 'pdf', 'rtf', 'dwg', 'dwl', 'dwl2', 'dxf', 'mpp', 'pptx'];

            if ($.inArray($(this).val().split('.').pop().toLowerCase(), fileExtension) == -1) {
                swal({
                    title: "Внимание",
                    text: "Поддерживаемые форматы: "+fileExtension.join(', '),
                    type: 'warning',
                });
                $(this).val('');
                $(this).parent().parent().siblings('#fileName5')[0].innerHTML = '';

                return false;
            } else {
                document.getElementById('fileName').innerHTML = 'Количество файлов: ' + files.length;
                if (files.length === 1) {
                    document.getElementById('fileName').innerHTML = 'Имя файла: ' + $('#uploadedFile').val().split('\\').pop();
                    if ($('input.material_check:checked').length > 0) {
                        $('#submit_mat_contr').removeClass('d-none');
                    } else {
                        $('#submit_mat_contr').addClass('d-none');
                    }
                } else {
                    $('#submit_mat_contr').addClass('d-none');
                }
            }
        });

        $('input#uploadedFile5').change(function(){
            var files = $(this)[0].files;
            var fileExtension = ['jpeg', 'jpg', 'png', 'gif', 'bmp', 'txt', 'doc', 'docx', 'xls', 'xlsx', 'pdf', 'rtf', 'dwg', 'dwl', 'dwl2', 'dxf', 'mpp', 'pptx'];

            if ($.inArray($(this).val().split('.').pop().toLowerCase(), fileExtension) == -1) {
                swal({
                    title: "Внимание",
                    text: "Поддерживаемые форматы: "+fileExtension.join(', '),
                    type: 'warning',
                });
                $(this).val('');
                $(this).parent().parent().siblings('#fileName5')[0].innerHTML = '';

                return false;
            } else {
                document.getElementById('fileName5').innerHTML = 'Количество файлов: ' + files.length;
                if (files.length === 1) {
                    document.getElementById('fileName5').innerHTML = 'Имя файла: ' + $('#uploadedFile5').val().split('\\').pop();
                    $('#submit_form_attach_document').removeAttr('disabled');
                }
            }
        });

        $('input#uploadedFile6').change(function(){
            var files = $(this)[0].files;
            var fileExtension = ['jpeg', 'jpg', 'png', 'gif', 'bmp', 'txt', 'doc', 'docx', 'xls', 'xlsx', 'pdf', 'rtf', 'dwg', 'dwl', 'dwl2', 'dxf', 'mpp', 'pptx'];

            if ($.inArray($(this).val().split('.').pop().toLowerCase(), fileExtension) == -1) {
                swal({
                    title: "Внимание",
                    text: "Поддерживаемые форматы: "+fileExtension.join(', '),
                    type: 'warning',
                });
                $(this).val('');
                $(this).parent().parent().siblings('#fileName5')[0].innerHTML = '';

                return false;
            } else {
                document.getElementById('fileName6').innerHTML = 'Количество файлов: ' + files.length;
                if (files.length === 1) {
                    document.getElementById('fileName6').innerHTML = 'Имя файла: ' + $('#uploadedFile6').val().split('\\').pop();
                }
            }
        });

        $('#PS').select2({
            language: 'ru',
            closeOnSelect: false,
            placeholder: 'Выберите примечания'
        }).on('select2:selecting', function(e) {
          var cur = e.params.args.data.id;
          var old = (e.target.value == '') ? [cur] : $(e.target).val().concat([cur]);
          $(e.target).val(old).trigger('change');
          $(e.params.args.originalEvent.currentTarget).attr('aria-selected', 'true');
          $('[data-select2-id = 1]').find('.select2-search.select2-search--inline').insertBefore('.select2-selection__choice:first-child' );
          $('[data-select2-id = 1]').find('.select2-selection.select2-selection--multiple').height('33');
            var full_height=0;
            $('[data-select2-id = 1]').find(".select2-selection__rendered .select2-selection__choice").each(function(){
                full_height+=$(this).outerHeight();
                new_modal_height = 200 + full_height;
                $('#add_manual_notes').find('.modal-body').height(new_modal_height);
            });
            console.log(full_height);
          return false;
        }).on('select2:opening', function(e) {
            if (window['isClearClicked']) {
                e.preventDefault();
                $('.select2-selection.select2-selection--multiple').height('33');
                $('[data-select2-id = 1]').find('[data-select2-id = 1]').find('.select2-search.select2-search--inline').insertBefore('.select2-selection__choice:first-child' );

                 var full_height=0;
                 $('[data-select2-id = 1]').find(".select2-selection__rendered .select2-selection__choice").each(function(){
                     full_height+=$(this).outerHeight();
                     new_modal_height = 200 + full_height;
                     $('#add_manual_notes').find('.modal-body').height(new_modal_height);
                 });
                 console.log(full_height);

                window['isClearClicked'] = false;
            }
        }).on('select2:unselect', function(e) {
             $('[data-select2-id = 1]').find('.select2-selection.select2-selection--multiple').height('33');
             $('[data-select2-id = 1]').find('.select2-search.select2-search--inline').insertBefore('.select2-selection__choice:first-child' );

             var full_height=0;
             $('[data-select2-id = 1]').find(".select2-selection__rendered .select2-selection__choice").each(function(){
                 full_height+=$(this).outerHeight();
                 new_modal_height = 200 + full_height;
                 $('#add_manual_notes').find('.modal-body').height(new_modal_height);
             });
             console.log(full_height);

             window['isClearClicked'] = true;
        });

        $('#demand').select2({
            language: 'ru',
            closeOnSelect: false,
            placeholder: 'Выберите требования'
        }).on('select2:selecting', function(e) {
          var cur = e.params.args.data.id;
          var old = (e.target.value == '') ? [cur] : $(e.target).val().concat([cur]);
          $(e.target).val(old).trigger('change');
          $(e.params.args.originalEvent.currentTarget).attr('aria-selected', 'true');

          $('[data-select2-id = 2]').find('.select2-selection.select2-selection--multiple').height('33');
          $('[data-select2-id = 2]').find('.select2-search.select2-search--inline').insertBefore('.select2-selection__choice:first-child' );
          var full_height=0;
          $('[data-select2-id = 2]').find(".select2-selection__rendered .select2-selection__choice").each(function(){
              full_height+=$(this).outerHeight();
              new_modal_height = 300 + full_height;
              $('#add_manual_requirement').find('.modal-body').height(new_modal_height);
          });
          console.log(full_height);

          return false;
        }).on('select2:opening', function(e) {
            if (window['isClearClicked']) {
                e.preventDefault();

                $('[data-select2-id = 2]').find('.select2-selection.select2-selection--multiple').height('33');
                $('[data-select2-id = 2]').find('.select2-search.select2-search--inline').insertBefore('.select2-selection__choice:first-child' );
                var full_height=0;
                $('[data-select2-id = 2]').find(".select2-selection__rendered .select2-selection__choice").each(function(){
                    full_height+=$(this).outerHeight();
                    new_modal_height = 200 + full_height;
                    $('#add_manual_requirement').find('.modal-body').height(new_modal_height);
                });
                console.log(full_height);

                window['isClearClicked'] = false;
            }
        }).on('select2:unselect', function(e) {

            $('[data-select2-id = 2]').find('.select2-selection.select2-selection--multiple').height('33');
            $('[data-select2-id = 2]').find('.select2-search.select2-search--inline').insertBefore('.select2-selection__choice:first-child' );
            var full_height=0;
            $('[data-select2-id = 2]').find(".select2-selection__rendered .select2-selection__choice").each(function(){
                full_height+=$(this).outerHeight();
                new_modal_height = 200 + full_height;
                $('#add_manual_requirement').find('.modal-body').height(new_modal_height);
            });
            console.log(full_height);

             window['isClearClicked'] = true;
        });

        // Вложить КП
        $('#upload').click(function(){
            $('#file').fadeIn(300);
            $('#upload').hide();
        });

        $('#budget').click(function(){
            $('#file_budget').fadeIn(300);
            $('#budget').hide();
        });



        $('#show_subcontract').click(function(){
            $('.work .form-check').fadeIn(400);
            $('#add_subcontract').fadeIn(600);
            $('#btn_container').hide();
        });

        $('#show_mat_subcontract').click(function(){
            $('.material .form-check').fadeIn(400);
            $('#add_mat_subcontract').fadeIn(600);
            $('#btn_mat_container').hide();
        });


        $('#close_sub').click(function(){
            $('#add_subcontract').hide();
            $('#btn_container').fadeIn(400);
            $('#warning').hide();
            $('.work .form-check').hide(400);
        });


        $('#close_mat_sub').click(function(){
            $('#add_mat_subcontract').hide();
            $('#btn_mat_container').fadeIn(400);
            $('#warning').hide();
            $('.material .mat-form-check').hide(400);
        });

        // показать чекбокс rent
        $('#my_material').click(function(){
            if ($(this).is(':checked')){
                $('#show_rent').show(100);
            } else {
                $('#show_rent').hide(100);
            }
        });

        // показать чекбокс rent_time
        $('#rent').click(function(){
            if ($(this).is(':checked')){
                $('#rent_time').show(100);
            } else {
                $('#rent_time').hide(100);
            }
        });

        // показать шпунт
        $('#addSp').click(function(){
            if ($(this).is(':checked')){
                $('#spInfo').show(100);
            } else {
                $('#spInfo').hide(100);
            }
        });
        // Показать свайи
        $('#addSv').click(function(){
            if ($(this).is(':checked')){
                $('#svInfo').show(100);
            } else {
                $('#svInfo').hide(100);
            }
        });
        // Показать земляные
        $('#addZm').click(function(){
            if ($(this).is(':checked')){
                $('#zmInfo').show(100);
            } else {
                $('#zmInfo').hide(100);
            }
        });

        $('#show_contract_number').click(function(){
        if ($(this).is(':checked')){
            $('#contract_number').show(100);
        } else {
            $('#contract_number').hide(100);
            $("[name='contract_number']").val('').trigger('change');
            $("[name='contract_date']").val('').trigger('change');
            }
        });

        function set_contract_number(e) {
            console.log($(e).val());
            console.log($(e)[0].getAttribute('name'));
            $.ajax({
                url: '{{ route('projects::commercial_offer::set_contract_number', [$commercial_offer->project_id, $commercial_offer->id]) }}',
                type: 'POST',
                data: {
                    _token: CSRF_TOKEN,
                    field: $(e)[0].getAttribute('name'),
                    value: $(e).val(),
                },
                dataType: 'JSON',
            });
        }

        $('.js-select-subcontractor').select2({
            language: 'ru',
            ajax: {
                url: '/projects/ajax/get-subcontractors',
                dataType: 'json',
                delay: 250,
            },
            placeholder: 'Поиск по контрагентам',
        });

        $(".js-select-subcontractor").on("select2:open", function() {
            $(".select2-search__field").attr("placeholder", "Начните вводить название");
        });
        $(".js-select-subcontractor").on("select2:close", function() {
            $(".select2-search__field").attr("placeholder", null);
        });
        //////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
        var count_comment = 1;
        var count_require = 1;
        // Примечание добавить
        $("#addCommentCard").click(function() {
            if (count_comment < 10) {
                var cardComment = $("[class='copyCommentCard']").length;
                $("#comment").clone().attr('id', 'comment' + cardComment).appendTo("#containerCommentCard");
                $('#max_tongue').addClass('d-none');
                count_comment++;
            }
            if (count_comment == 10) {
                $('#max_tongue').removeClass('d-none');
            }
        });

        // Требование добавить
        $("#addRequireCard").click(function() {
            if (count_require < 10) {
                var cardRequire = $("[class='copyRequireCard']").length;
                $("#require").clone().attr('id', 'require' + cardRequire).appendTo("#containerRequireCard");
                $('#max_tongue').addClass('d-none');
                count_require++;
            }
            if (count_require == 10) {
                $('#max_tongue').removeClass('d-none');
            }
        });

        function send_avans(e) {
            if (e == 'empty') {
                $.ajax({
                    url: '{{ route('projects::commercial_offer::add_advancement', [$commercial_offer->project_id, $commercial_offer->id]) }}',
                    type: 'POST',
                    data: {
                        _token: CSRF_TOKEN,
                        avans_unit: '',
                        avans_value: '',
                        avans_title: '',
                        avans_note: '',
                        max_avans: '',
                    },
                    dataType: 'JSON',
                    success: function() {
                        location.reload();
                    }
                });
            } else if ($(e).parents('.modal-content').find(`form`)[0].reportValidity()) {
                var path = $(e).parents('.modal-content').find(`form`).attr('action');

                $.ajax({
                    url: path,
                    type: 'POST',
                    data: {
                        _token: CSRF_TOKEN,
                        avans_unit: $(e).parents('.modal-content').find(`.radio_avans:checked`).val(),
                        avans_value: $(e).parents('.modal-content').find(`[name='avans_value']`).val(),
                        avans_title: $(e).parents('.modal-content').find(`[name='avans_title']`).val(),
                        avans_note: $(e).parents('.modal-content').find(`[name='avans_note']`).val(),
                        max_avans: $(e).parents('.modal-content').find(`[name='max_avans']`).val(),
                    },
                    dataType: 'JSON',
                    success: function() {
                        location.reload();
                    }
                });
            }
        }


        function submit_half_ajax(e) {
            if ($(e).parents('.modal-content').find(`form`)[0].reportValidity()) {
                var path = $(e).parents('.modal-content').find(`form`).attr('action');
                var names = $(e).parents('.modal-content').find(`[name='names[]']`).val();

                $.ajax({
                    url: path,
                    type: 'POST',
                    data: {
                        _token: CSRF_TOKEN,
                        names: names,
                    },
                    dataType: 'JSON',
                    success: function() {
                        location.reload();
                    }
                });
            }
        }



        function rent_term(e, max_count, split_id, split_type) {
            var new_type = $(e).val().split('=');

            $(e).selectpicker('refresh');

            swal({
                title: 'Введите количество материала',
                input: 'number',
                inputValue: max_count,
                inputAttributes: {
                    min: 0,
                    max: max_count,
                    step: 0.01,
                },
                showCancelButton: true,
                inputValidator: (count) => {
                    if (!count) {
                        return 'Нужно число!'
                    } else if (count <= 0 || count > max_count) {
                        return 'Число должно быть от 0 до ' + max_count;
                    }
                }
            }).then((res) => {
                var time = 0;
                var count = res.value;
                if(res.dismiss) {
                    $(e).selectpicker('val', '')
                }
                else {
                    if ('3' == new_type[1] && new_type[0] == 'new_type') {
                        swal({
                            title: 'Введите срок аренды (мес)',
                            input: 'number',
                            showCancelButton: true,
                            inputValidator: (value) => {
                                if (!value) {
                                    return 'Нужно ввести срок!'
                                } else if (value <= 0) {
                                    return 'Число должно быть больше 0'
                                } else {
                                    $(e).find('option:selected').text('Аренда (' + value + ' м.)');
                                    $(e).selectpicker('refresh');
                                }
                            }
                        }).then((res) => {
                            time = res.value;
                            if (res.dismiss) {
                                $(e).selectpicker('val', '')
                            } else {
                                swal({
                                    title: 'Введите краткое описание материала (не обязательно)',
                                    input: 'text',
                                    showCancelButton: true,
                                }).then((res) => {
                                    if (res.dismiss) {
                                        $(e).selectpicker('val', '')
                                    } else {
                                        send_split_ajax(count, time, res.value, new_type, split_id, split_type);
                                    }
                                });

                            }
                        });
                    } else {
                        if (new_type[0] == 'new_type') {
                            swal({
                                title: 'Введите краткое описание материала (не обязательно)',
                                input: 'text',
                                showCancelButton: true,
                            }).then((res) => {
                                if (res.dismiss) {
                                    $(e).selectpicker('val', '')
                                } else {
                                    send_split_ajax(count, time, res.value, new_type, split_id, split_type);
                                }
                            });
                        } else {
                            send_split_ajax(count, time, '', new_type, split_id, split_type);
                        }
                    }
                }
            });
        }

        function send_split_ajax(count, time, comment, new_type, split_id, split_type) {
            $.ajax({
                url: '{{ route("projects::commercial_offer::split_material", $commercial_offer->id) }}',
                type: 'POST',
                data: {
                    _token: CSRF_TOKEN,
                    old_type: split_type,
                    [new_type[0]]: new_type[1],
                    count: count,
                    time: time,
                    comment: comment,
                    split_id: split_id,
                },
                dataType: 'JSON',
                success: function() {
                    location.reload();
                }
            });
        }

        function split_more(new_type, max_count, split_id, split_type, time = 0) {
            swal({
                title: 'Введите количество материала',
                input: 'number',
                inputValue: max_count,
                inputAttributes: {
                    min: 0,
                    max: max_count,
                    step: 0.01,
                },
                showCancelButton: true,
                inputValidator: (count) => {
                    if (!count) {
                        return 'Нужно число!'
                    } else if (count <= 0 || count > max_count) {
                        return 'Число должно быть от 0 до ' + max_count;
                    }
                }
            }).then((res) => {
                if (res.dismiss) {

                } else {
                    var count = res.value;
                    if ('4' == new_type) {
                        $.ajax({
                            url: '{{ route("projects::commercial_offer::split_material", $commercial_offer->id) }}',
                            type: 'POST',
                            data: {
                                _token: CSRF_TOKEN,
                                old_type: split_type,
                                new_type: new_type,
                                count: count,
                                time: time,
                                split_id: split_id,
                            },
                            dataType: 'JSON',
                            success: function () {
                                location.reload();
                            }
                        });
                    } else {
                        $.ajax({
                            url: '{{ route("projects::commercial_offer::split_material", $commercial_offer->id) }}',
                            type: 'POST',
                            data: {
                                _token: CSRF_TOKEN,
                                old_type: split_type,
                                new_type: new_type,
                                count: count,
                                time: time,
                                split_id: split_id,
                            },
                            dataType: 'JSON',
                            success: function () {
                                location.reload();
                            }
                        });
                    }
                }
            });

        }


        function remove_element_comment(element, id)
        {
            if(id > 0) {
                $.ajax({
                    url: '{{ route("projects::commercial_offer::delete_comment", $commercial_offer->id) }}',
                    type: 'POST',
                    data: {
                        _token: CSRF_TOKEN,
                        id: id,
                    },
                    dataType: 'JSON',
                });
            }

            $(element).closest('.copyCommentCard').remove();
            count_comment--;
        };
        function remove_element_require(element, id)
        {
            if(id > 0) {
                $.ajax({
                    url: '{{ route("projects::commercial_offer::delete_require", $commercial_offer->id) }}',
                    type: 'POST',
                    data: {
                        _token: CSRF_TOKEN,
                        id: id,
                    },
                    dataType: 'JSON',
                });
            }

            $(element).closest('.copyRequireCard').remove();
            count_comment--;
        };
        function remove_element_advancement(element, id)
        {
            if(id > 0) {
                $.ajax({
                    url: '{{ route("projects::commercial_offer::delete_advancement", $commercial_offer->id) }}',
                    type: 'POST',
                    data: {
                        _token: CSRF_TOKEN,
                        id: id,
                    },
                    dataType: 'JSON',
                });
            }

            $(element).closest('.copyAdvancementCard').remove();
        };

        $('input#uploadedOfferFiles').change(function(){
            var TongueFiles = $(this)[0].files;
            var fileExtension = ['jpeg', 'jpg', 'png', 'gif', 'bmp', 'txt', 'doc', 'docx', 'xls', 'xlsx', 'pdf', 'rtf', 'dwg', 'mpp', 'dwl', 'dwl2', 'dxf', 'pptx'];

            if ($.inArray($(this).val().split('.').pop().toLowerCase(), fileExtension) == -1) {
                swal({
                    title: "Внимание",
                    text: "Поддерживаемые форматы: "+fileExtension.join(', '),
                    type: 'warning',
                });
                $(this).val('');
                $(this).parent().parent().siblings('#fileName5')[0].innerHTML = '';

                return false;
            } else {
                document.getElementById('fileNameOffer').innerHTML = 'Количество файлов: ' + TongueFiles.length;
                if (TongueFiles.length === 1) {
                    document.getElementById('fileNameOffer').innerHTML = 'Имя файла: ' + $('#uploadedOfferFiles').val().split('\\').pop();
                    $('#submit_offer_request').removeClass('d-none');
                } else if (TongueFiles.length > 10) {
                    swal({
                        title: "Внимание",
                        text: "К заявке можно прикрепить не более десяти файлов!",
                        type: 'warning',
                    });
                    $('#submit_offer_request').addClass('d-none');
                } else {
                    $('#submit_offer_request').removeClass('d-none');
                }
            }
        });


        function change_work_price(e, work_id, work_group_id)
        {
                $(e).removeClass('is-invalid');
                $.ajax({
                    url:'{{ route("projects::commercial_offer::set_work_price") }}',
                    type: 'POST',
                    data: {
                        _token: CSRF_TOKEN,
                        work_id: work_id,
                        value: parseFloat($(e).val().replace(/ /g, '').replace(/,/g, '.')).toFixed(2)
                    },
                    dataType: 'JSON',
                    success: function (result_price) {

                        AutoNumeric.set($(e).closest('.work').find('.general_price').first()[0], result_price.toFixed(2));

                        var security_pay = 0;
                        $.each($('.security_pay'),function() {
                            security_pay += parseFloat(parseFloat($(this).val().replace(/ /g, '').replace(/,/g, '.')).toFixed(2));
                        });

                        var work_group_price = 0;
                        $.each($('.work_result_price_' + work_group_id),function() {
                            work_group_price += parseFloat(parseFloat($(this).val().replace(/ /g, '').replace(/,/g, '.')).toFixed(2));
                        });

                        var work_all_price = 0;
                        $.each($('.common_work_price'),function() {
                            work_all_price += parseFloat(parseFloat($(this).val().replace(/ /g, '').replace(/,/g, '.')).toFixed(2));
                        });

                        var general_price = 0;
                        $.each($('.general_price'),function() {
                            if(!$(this).hasClass('buyback') || $(this).hasClass('duplicate')) {
                                general_price += parseFloat(parseFloat($(this).val().replace(/ /g, '').replace(/,/g, '.')).toFixed(2));
                            }
                        });


                        security_pay = general_price - security_pay;
                        AutoNumeric.set($('#result_without_security')[0], security_pay.toFixed(2));
                        AutoNumeric.set($('#result')[0], general_price.toFixed(2));
                        AutoNumeric.set($('#result_work_' + work_group_id)[0], work_group_price.toFixed(2));
                        AutoNumeric.set($('#result_work')[0], work_all_price.toFixed(2));

                        if(security_pay == general_price) {
                            $('#result_without_security').closest('tr').hide();
                        } else {
                            $('#result_without_security').closest('tr').show();
                        }
                        $('#nds').trigger('change');
                    }
                });
        }


        function change_work_term(e, work_id, work_group_id)
        {
            if ($(e).val().match(/^\d+$/)) {
                $(e).removeClass('is-invalid');
                $.ajax({
                    url:'{{ route("projects::commercial_offer::set_work_term") }}',
                    type: 'POST',
                    data: {
                        _token: CSRF_TOKEN,
                        work_id: work_id,
                        value: parseFloat($(e).val()).toFixed(2)
                    },
                    dataType: 'JSON',
                });
            } else {
                $(e).addClass('is-invalid');
            }
        }


        function change_material_price(e, material_id, work_group_id, count, split_id)
        {
            var value = $(e).val().replace(/ /g, '').replace(/,/g, '.');
            if (value == '') {
                AutoNumeric.set($(e)[0], 0);
            }
            $.ajax({
                url:'{{ route("projects::commercial_offer::set_material_price") }}',
                type: 'POST',
                data: {
                    _token: CSRF_TOKEN,
                    material_id: material_id,
                    value: parseFloat($(e).val().replace(/ /g, '').replace(/,/g, '.')).toFixed(2),
                    offer_id: {{ $commercial_offer->id }},
                    count: count,
                    split_id: split_id,
                },
                dataType: 'JSON',
                success: function (result_price) {
                    AutoNumeric.set($(e).closest('tr').find('.general_price').first()[0], parseFloat(result_price.toFixed(2)));
                    var security_pay = 0;
                    $.each($('#collapseTwo').find('.security_pay'),function() {
                        if($(this).hasClass('duplicate')) {

                        } else {
                            security_pay += parseFloat(parseFloat($(this).val().replace(/ /g, '').replace(/,/g, '.')).toFixed(2));
                        }
                    });

                    var material_result_price = 0;
                    $.each($('#collapseTwo').find('.material_result_price_' + work_group_id),function() {
                        if($(this).hasClass('buyback') || $(this).hasClass('duplicate')) {

                        } else {
                            material_result_price += parseFloat(parseFloat($(this).val().replace(/ /g, '').replace(/,/g, '.')).toFixed(2));
                        }
                    });

                    var common_material_price = 0;
                    $.each($('#collapseTwo').find('.common_material_price'),function() {
                        if($(this).hasClass('buyback') || $(this).hasClass('duplicate')) {

                        } else {
                            common_material_price += parseFloat(parseFloat($(this).val().replace(/ /g, '').replace(/,/g, '.')).toFixed(2));
                        }
                    });

                    var general_price = 0;
                    $.each($('#collapseTwo').find('.general_price'),function() {
                        if($(this).hasClass('buyback') || $(this).hasClass('duplicate')) {

                        } else {
                            general_price += parseFloat(parseFloat($(this).val().replace(/ /g, '').replace(/,/g, '.')).toFixed(2));
                        }
                    });

                    security_pay = general_price - security_pay;
                    AutoNumeric.set($('#result_without_security')[0], security_pay.toFixed(2));
                    AutoNumeric.set($('#result')[0], general_price.toFixed(2));
                    AutoNumeric.set($('#result_material_' + work_group_id)[0], material_result_price.toFixed(2));

                    AutoNumeric.set($('#result_material')[0], parseFloat(common_material_price).toFixed(2));

                    if(security_pay == general_price) {
                        $('#result_without_security').closest('tr').hide();
                    } else {
                        $('#result_without_security').closest('tr').show();
                    }

                    $('#nds').trigger('change');
                }
            });
        }


        function change_material_used(e, split_id)
        {
            $.ajax({
                url:'{{ route("projects::commercial_offer::set_material_used") }}',
                type: 'POST',
                data: {
                    _token: CSRF_TOKEN,
                    split_id: split_id,
                    value: $(e)[0].checked
                },
                dataType: 'JSON',
            });
        }


        function detach_subcontractors(subcontractor_id, type = 0)
        {
            swal({
                title: 'Вы уверены?',
                text: "Подрядчик будет удален из исполнителей!",
                type: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#3085d6',
                cancelButtonText: 'Назад',
                confirmButtonText: 'Удалить'
            }).then((result) => {
                if(result.value) {
                    $.ajax({
                        url:'{{ route("projects::commercial_offer::detach_subcontractors") }}',
                        type: 'POST',
                        data: {
                            _token: CSRF_TOKEN,
                            subcontractor_id:  subcontractor_id,
                            type: type,
                            com_offer_id: {{ $commercial_offer->id }},
                        },
                        dataType: 'JSON',
                        success: function () {
                            location.reload();
                        }
                    });
                }
            });
        }

        function change_security_pay(e, split_id, work_group_id, count, type, time = 0)
        {
            var value = $(e).val().replace(/ /g, '').replace(/,/g, '.');
            if (value == '') {
                AutoNumeric.set($(e)[0], 0);
            }

            $.ajax({
                url:'{{ route("projects::commercial_offer::change_security_pay") }}',
                type: 'POST',
                data: {
                    _token: CSRF_TOKEN,
                    split_id: split_id,
                    offer_id: {{ $commercial_offer->id }},
                    value: parseFloat(value).toFixed(2),
                    count: $(e).closest('tr').find('.material_count').first().text(),
                    mat_count: count,
                    time: time,
                    type: type,
                },
                dataType: 'JSON',
                success: function (result_price) {
                    if (type == 4) {
                        AutoNumeric.set($('#info_' + split_id)[0], parseFloat(value).toFixed(2));
                        AutoNumeric.set($('#general_info_' + split_id)[0], parseFloat(result_price).toFixed(2));
                    }
                    AutoNumeric.set($(e).closest('tr').find('.general_price').first()[0], parseFloat(result_price).toFixed(2));

                    var security_pay = 0;
                    $.each($('#collapseTwo').find('.security_pay'),function() {
                        if($(this).hasClass('duplicate')) {

                        } else {
                            security_pay += parseFloat(parseFloat($(this).val().replace(/ /g, '').replace(/,/g, '.')).toFixed(2));
                        }
                    });

                    var common_material_price = 0;
                    $.each($('#collapseTwo').find('.common_material_price'),function() {
                        if($(this).hasClass('buyback') || $(this).hasClass('duplicate')) {

                        } else {
                            common_material_price += parseFloat(parseFloat($(this).val().replace(/ /g, '').replace(/,/g, '.')).toFixed(2));
                        }
                    });

                    var general_price = 0;
                    $.each($('#collapseTwo').find('.general_price'),function() {
                        if($(this).hasClass('buyback') || $(this).hasClass('duplicate')) {

                        } else {
                            general_price += parseFloat(parseFloat($(this).val().replace(/ /g, '').replace(/,/g, '.')).toFixed(2));
                        }
                    });

                    AutoNumeric.set($('#result_material_buyback')[0], security_pay.toFixed(2));

                    security_pay = general_price - security_pay;
                    AutoNumeric.set($('#result_without_security')[0], security_pay.toFixed(2));
                    AutoNumeric.set($('#result_material')[0], common_material_price.toFixed(2));
                    AutoNumeric.set($('#result')[0], parseFloat(general_price).toFixed(2));

                    if(security_pay == general_price) {
                        $('#result_without_security').closest('tr').hide();
                    } else {
                        $('#result_without_security').closest('tr').show();
                    }
                    $('#nds').trigger('change');
                }
            });
        }


        function change_security_pay_all(e, split_id, work_group_id)
        {
            AutoNumeric.set($(e).closest('tr').find('.price-input')[0], (parseFloat($(e).val().replace(/ /g, '').replace(/,/g, '.')) / parseFloat($(e).closest('tr').find('.material_count').first().text())).toFixed(2));

            $(e).closest('tr').find('.price-input').first().trigger('change');
        }


        function delete_securuty_payment(e, split_id, work_group_id, type)
        {
            swal({
                title: 'Вы уверены?',
                text: "Обеспечительный платеж будет удален!",
                type: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#3085d6',
                cancelButtonText: 'Назад',
                confirmButtonText: 'Удалить'
            }).then((result) => {
                if(result.value) {
                    $.ajax({
                        url:'{{ route("projects::commercial_offer::delete_securuty_payment") }}',
                        type: 'post',
                        data: {
                            _token: CSRF_TOKEN,
                            split_id:  split_id,
                            offer_id: {{ $commercial_offer->id }},
                            type: type,
                        },
                        dataType: 'JSON',
                        success: function () {
                            location.reload()
                        }
                    });
                }
            });
        }


        function calc_nds(e) {
            var result = $('#result').val().replace(/ /g, '').replace(/,/g, '.');
            if ($('#result_without_security').val() != $('#result').val()) {
                result = $('#result_without_security').val().replace(/ /g, '').replace(/,/g, '.');
            }

            AutoNumeric.set($('#result_nds')[0], parseFloat(parseFloat(result) - parseFloat(result) / (parseFloat($('#nds').val().replace(/ /g, '').replace(/,/g, '.')) / 100 + 1)).toFixed(2));

            $.ajax({
                url:'{{ route("projects::commercial_offer::set_nds") }}',
                type: 'POST',
                data: {
                    _token: CSRF_TOKEN,
                    nds: $(e).val().replace(/ /g, '').replace(/,/g, '.'),
                    com_offer: {{ $commercial_offer->id }},
                },
                dataType: 'JSON',
            });
        }


        function set_signer(signer_user_id) {
            $.ajax({
                url:'{{ route("projects::commercial_offer::set_signer", $commercial_offer->id) }}',
                type: 'POST',
                data: {
                    _token: CSRF_TOKEN,
                    signer_user_id:  signer_user_id,
                },
                dataType: 'JSON',
            });
        }

        function set_contact(contact_id) {
            $.ajax({
                url:'{{ route("projects::commercial_offer::set_contact", $commercial_offer->id) }}',
                type: 'POST',
                data: {
                    _token: CSRF_TOKEN,
                    contact_id: contact_id,
                },
                dataType: 'JSON',
            });
        }

        function update_adv(adv_id, e) {
            $.ajax({
                url:'{{ route("projects::commercial_offer::change_advancement", $commercial_offer->id) }}',
                type: 'POST',
                data: {
                    _token: CSRF_TOKEN,
                    adv_id:  adv_id,
                    adv_desc:  $(e).val(),
                },
                dataType: 'JSON',
            });
        }
        function update_comment(com_id, e) {
            $.ajax({
                url:'{{ route("projects::commercial_offer::change_comment", $commercial_offer->id) }}',
                type: 'POST',
                data: {
                    _token: CSRF_TOKEN,
                    note_id:  com_id,
                    note:  $(e).val(),
                },
                dataType: 'JSON',
                success: function(data) {
                    if(data > 0) {
                        $(e).attr('onchange', 'update_comment(' + data + ', this)');
                        $(e).siblings().first().attr('onclick', 'remove_element_comment(this, ' + data + ')');
                    }
                }
            });
        }
        function update_require(req_id, e) {
            $.ajax({
                url: '{{ route("projects::commercial_offer::change_require", $commercial_offer->id) }}',
                type: 'POST',
                data: {
                    _token: CSRF_TOKEN,
                    req_id: req_id,
                    req: $(e).val(),
                },
                dataType: 'JSON',
                success: function (data) {
                    if(data > 0) {
                        $(e).attr('onchange', 'update_require(' + data + ', this)');
                        $(e).siblings().first().attr('onclick', 'remove_element_require(this, ' + data + ')');
                    }
                }
            });

        }


        function toggle_one(one_id, type) {
            $.ajax({
                url: '{{ route("projects::commercial_offer::toggle_work_mat") }}',
                type: 'POST',
                data: {
                    _token: CSRF_TOKEN,
                    id: one_id,
                    type: type,
                    com_offer: '{{ $commercial_offer->id }}',
                },
                dataType: 'JSON',
                success: function () {
                    location.reload();
                }
            });
        }


        function avans_modal_show(target_name, max_value, type = '4') {
            $('#max_avans').val(max_value);
            $('#avans_value')[0].max = $('#max_avans').val();
            $('#target_name').val(target_name);
            $('#target_name_title').text('Авансирование на ' + ['стоимость материала ', ' обеспечительный платеж ', 'стоимость использования (' + (type.split('|')[1] ? type.split('|')[1] : '') + ' мес.) ', '', ''][type.split('|')[0] - 1] + target_name);
            $('#avans_title').val($('#target_name_title').text());
        }


        $('#offer_create').click(function () {
            setTimeout(function() {
                var commercial_offer_id = {{ $commercial_offer->id }};
                var base_url = $('#url').attr('url');

                $.ajax({
                    url:'{{ route("projects::get_com_offer") }}',
                    type: 'post',
                    data: {
                        _token: CSRF_TOKEN,
                        id:  commercial_offer_id,
                    },
                    dataType: 'JSON',
                    success: function (data) {
                        $('.old_parts').remove();
                        url = base_url + '/storage/docs/commercial_offers/' + data.file_name;
                        $('.new_parts').attr('href', url).removeClass('d-none');
                        $('#submit_form_attach_document').removeAttr('disabled');
                    }
                });
            }, 3000);
        });



        $('.js-select-proj-doc').select2({
            language: "ru",
            closeOnSelect: false,
            ajax: {
                url: '/projects/ajax/get_project_documents/' + {{ $commercial_offer->project_id }},
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


        function update_title(field)
        {
            $.ajax({
                url: '{{ route('projects::update_title') }}',
                type: 'POST',
                data: {
                    _token: CSRF_TOKEN,
                    title: $(field).val(),
                    commercial_offer: {{ $commercial_offer->id }},
                },
                dataType: 'JSON'
            });
        }
    </script>

@endsection
