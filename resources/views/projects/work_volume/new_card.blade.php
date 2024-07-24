@extends('layouts.app')

@section('title', 'Объёмы работ')

@section('url', route('projects::index'))

@section('css_top')
    <link href="https://cdn.jsdelivr.net/npm/jquery-steps@1.1.0/demo/css/jquery.steps.css" rel="stylesheet" />

    <style>

        .wizard > .steps a {
            background: #fff;
            font-size: 14px;
            color: #616161;
        }

        .wizard > .steps a:hover {
            color: #616161;
        }

        .wizard > .steps a .number {
            display: none;
        }

          .wizard > .steps a:hover, .wizard > .steps .done a:hover {
            background: #EDEDED;
        }

        .wizard > .steps .current a,
        .wizard > .steps .current a:hover {
            background-color:#34008E;
            color:#fff;
        }

        .wizard > .steps > ul > li {
            width: 33.3%;
        }

        .wizard > .steps .done a {
            background: #fff;
            color: #616161!important;
        }

        .wizard > .actions a, .wizard > .actions a:active{
            background: #CFCFCF;
        }

        .wizard > .actions a:hover {
            background: #CFCFCF;

        }

        .material_place {
            margin-bottom: 10px;
        }

        .wizard .content {
            min-height: 100px;
        }
        .wizard .content > .body {
            width: 100%;
            height: auto;
            padding: 15px;
            position: absolute;
        }
        .wizard .content .body.current {
            position: relative;
        }

        .wizard  > .content {
            background-color: #fff!important;
        }

        .wizard > .actions ul{
            width:55%!important;
            position: relative;
        }

        .wizard > .actions ul li:last-child {
            position: absolute;
            right:0;
        }

        a[href="#next"],a[href="#previous"]{
            background-color: transparent !important;
            margin-top: -10px;
            font-size: 40px;
            padding: 0px 5px 0px 15px !important;
        }

        a[href="#next"]:hover,a[href="#previous"]:hover{
            color:#D6D6D6!important;
        }

        a[href="#previous"]{
            padding: 0px 5px 0px 0px !important;
        }

        a[href="#finish"]{
            background-color: #0311b6!important;
            font-size: 16px;
            padding: 10px 18px!important;
        }

        a[href="#finish"]:hover{
            background-color: #010eab!important;
        }

        .wizard > a{
            cursor: pointer;
        }

        @media(max-width:991px){
            .wizard > .steps li {
                display:block!important;
                width:50%!important;
            }

            .wizard > .actions ul{
                width:60%!important;
            }

            a[href="#next"]:hover,
            a[href="#previous"]:hover{
                color:#fff!important;
            }
        }

        @media(max-width:421px){
            .wizard > .steps li {
                display:block!important;
                width:100%!important;
            }
            .wizard > .steps li a{
                padding: 9px 10px!important;
            }

            .wizard > .actions ul{
                width:95%!important;
            }

            a[href="#next"] {
                padding-left: 35px!important;
            }

            a[href="#next"],a[href="#previous"]{
                font-size:45px;
                margin-top: -15px;
            }

            a[href="#next"]:active,
            a[href="#next"]:hover,
            a[href="#previous"]:active,
            a[href="#previous"]:hover{
                color:#fff!important;
            }
        }

        #calc-mounts{
            padding-right: 0px;
        }

        .has-error .select2-selection {
            border-color: rgb(185, 74, 72) !important;
        }

        label.error {
            font-size: 0px;
        }


        #get_composite_pile {
            margin: 2.38em 0 0 -140px !important;
        }
    </style>
@endsection

@section('content')
    <div class="row">
        <div class="col-md-12">
            <div aria-label="breadcrumb" role="navigation">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item">
                        <a href="{{ route('projects::card', $work_volume->project_id) }}" class="table-link">{{ $work_volume->project_name }}</a>
                    </li>
                    <li class="breadcrumb-item active" aria-current="page">Объем работ @if ($work_volume->type == 0) шпунтовое направление @elseif ($work_volume->type == 1) свайное направление @endif</li>
                </ol>
            </div>
        </div>
    </div>
    <div class="card">
        <div class="card-header">
            <div class="row">
                <div class="col-md-9">
                    <h4 class="card-title" style="margin-top: 2px">Объем работ @if ($work_volume->type == 0) шпунтовое направление @elseif ($work_volume->type == 1) свайное направление @endif</h4>
                </div>
            </div>
            <hr style="margin-top:10px">
        </div>
        <div class="card-body" style="min-height:450px">
            <div class="accordions" id="accordion">
                <div class="card">
                    <div class="card-header">
                        <h4 class="card-title">
                            <a data-target="#collapseOne" href="#" data-toggle="collapse" style="line-height:1.4">
                                <span class="collapse-span">Заявки на {{ $service_name }}</span>
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
                                @if($work_volume_requests->count())
                                    <div class="table-responsive">
                                        <table class="table table-hover mobile-table">
                                            <thead>
                                            <tr>
                                                <th>Автор</th>
                                                <th>Дата</th>
                                                <th class="td-actions text-right" data-field="actions">
                                                    Действия
                                                </th>
                                            </tr>
                                            </thead>
                                            <tbody>
                                            @php
                                                $unresolved = 0;
                                            @endphp
                                            @foreach ($work_volume_requests as $key => $wv_request)
                                                @if(!session()->has('edited_wv_id'))
                                                    @if ($wv_request->status === 1)
                                                        <tr class="confirm">
                                                    @elseif ($wv_request->status === 2)
                                                        <tr class="reject">
                                                    @else
                                                        <tr>
                                                    @endif
                                                @else
                                                    @if(session()->get('edited_wv_request_id', 'default') == $wv_request->id)
                                                        <tr class="info">
                                                    @else
                                                        <tr class="text-secondary">
                                                            @endif
                                                            @endif
                                                            @if($wv_request->status === 0)
                                                                @php
                                                                    $unresolved++;
                                                                @endphp
                                                            @endif
                                                            <td data-label="Автор">
                                                                {{ $wv_request->last_name }}
                                                                {{ $wv_request->first_name }}
                                                                {{ $wv_request->patronymic }}
                                                            </td>
                                                            <td data-label="Дата" class="prerendered-date-time">{{ $wv_request->updated_at }}</td>
                                                            <td data-label="" class="text-right actions ">
                                                                <button rel="tooltip" data-placement="top" class="btn-edit btn-link btn-xs btn padding-actions mn-0"
                                                                        @if(session()->get('edited_wv_request_id', 'default') == $wv_request->id) onclick="clearSession()" data-original-title="Отменить редактирование" @else data-toggle="modal" data-target="#view-request{{ $wv_request->id }}" data-original-title="Просмотр" @endif>
                                                                    @if ($wv_request->status === 0 && $unresolved == 1  && session()->get('edited_wv_id', 'default') != $work_volume->id && (isset($WV_resp) ? $WV_resp == Auth::id(): false))
                                                                        <i class="fa fa-edit"></i>
                                                                    @elseif(session()->get('edited_wv_request_id', 'default') == $wv_request->id)
                                                                        <i class="fa fa-times"></i>
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
                <div class="card">
                    <div class="card-header">
                        <h4 class="card-title">
                            <a data-target="#collapseTwo" href="#" data-toggle="collapse" style="line-height:1.4">
                                <span class="collapse-span">Объем работ @if ($work_volume->type == 0) шпунтовое направление @elseif ($work_volume->type == 1) свайное направление @endif</span>
                                <b class="caret"></b>
                            </a>
                        </h4>
                    </div>
                    <div id="collapseTwo" class="card-collapse collapse show">
                        <div class="card-body">
                            <div class="strpied-tabled-with-hover">
                                <!-- <div class="fixed-table-toolbar toolbar-for-btn" style="margin-bottom:10px">
                                    <div class="row">
                                        <div class="col-md-12">
                                            <div class="pull-right">
                                                <button class="btn btn-sm btn-outline ">
                                                    Калькулятор
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                </div> -->
                                <div class="card">
                                    <!--<div class="card-title">
                                        <div class="row">
                                            <div class="col-md-12">
                                                <h6 style="margin: 30px 15px 20px 0">
                                                    {{ $service_name }}
                                                </h6>
                                            </div>
                                        </div>
                                    </div> -->
                                    <div class="row" style="margin-bottom:25px">
                                        @if($is_tongue)
                                            @if(session()->get('edited_wv_id', 'default') == $work_volume->id)
                                                <div class="col-md-4 col-xl-3">
                                                    <label for="">Глубина откопки котлована, м.</label>
                                                    <input id="depth" name="depth" onchange="change_depth(this)" value="{{ $work_volume->depth }}" min="0.01" max="50" step="0.01" type="number" placeholder="Укажите глубину откопки" class="form-control">
                                                </div>
                                            @else
                                                <div class="col-md-12 text-right">
                                                    <label style="font-size:0.91rem">Глубина откопки котлована: </label> <span style="text-decoration:underline;font-size:1.03rem;white-space:nowrap">{{ $work_volume->depth }} метров</span>
                                                </div>
                                            @endif
                                        @endif
                                        @if(session()->get('edited_wv_id', 'default') == $work_volume->id)
                                            @if($is_tongue)
                                                <div class="col-xl-9 col-md-8 text-right" style="padding-bottom:10px; margin-top:20px;">
                                                    <button class="btn btn-sm btn-outline btn-round" type="button" data-toggle="modal" data-target="#calc-tongue">
                                                        Шпунтовое ограждение
                                                    </button>
                                                    <button class="btn btn-sm btn-outline btn-round" type="button" data-toggle="modal" data-target="#calc-mounts">
                                                        Система крепления
                                                    </button>
                                                </div>
                                            @else
                                                <div class="col-md-4">

                                                </div>
                                                <div class="col-md-8 text-right" style="padding-bottom:10px">
                                                    <button class="btn btn-sm btn-outline btn-round" type="button" data-toggle="modal" data-target="#calc_composite_pile">
                                                        Составные сваи
                                                    </button>
                                                </div>
                                            @endif
                                        @endif
                                    </div>
                                    @if(session()->get('edited_wv_id', 'default') == $work_volume->id)
                                        <div class="row" style="margin-bottom:30px">
                                            <div class="col-xl-8 col-md-12 pd-0-min">
                                                <div class="card" style="border:1px solid rgba(0,0,0,.125);">
                                                    @include('projects.work_volume.modules.add_material')
                                                </div>
                                            </div>
                                            <div class="col-xl-4 col-md-12 pd-0-min">
                                               <div class="card" style="border:1px solid rgba(0,0,0,.125);" id="attr_search">
                                                   <div id="filter_materials" class="materials-container">
                                                       <template>
                                                       <h6 style="font-weight:400; line-height:1.3; margin-bottom: 10px">
                                                           Фильтрация материалов по атрибутам
                                                       </h6>
                                                           <div class="row">
                                                               <div class="col-xl-12">
                                                                   <div class="form-group">
                                                                       <select class="selectpicker" id="search_category" name="search_category" data-title="Выберите категорию" data-style="btn-default btn-outline" data-menu-style="dropdown-blue" v-on:change="get_attrs">
                                                                           <option v-for="(category, key) in categories" v-bind:value="key">@{{ category.name }}</option>
                                                                       </select>
                                                                   </div>
                                                               </div>
                                                           </div>
                                                           <div class="row">
                                                               <div class="col-xl-12">
                                                                   <div class="form-group">
                                                                       <select class="selectpicker" id="search_attr" name="select_attr" data-title="Выберите атрибут" data-style="btn-default btn-outline" data-menu-style="dropdown-blue" v-on:change="get_params">
                                                                           <option v-for="attr in attrs" v-bind:value="attr.id">@{{ attr.name }}</option>
                                                                       </select>
                                                                   </div>
                                                               </div>
                                                           </div>
                                                           <div class="row">
                                                               <div class="col-xl-7 col-md-6">
                                                                   <div class="form-group">
                                                                       <select class="selectpicker" id="search_value" name="select_value[]" data-title="Выберите значение" data-style="btn-default btn-outline" data-menu-style="dropdown-blue" multiple >
                                                                           <option v-for="(param, key) in params" v-bind:value="key">@{{ param.value }}</option>
                                                                       </select>
                                                                   </div>
                                                               </div>
                                                               <div class="col-xl-5 col-md-6 text-right" style="margin-top:-5px">
                                                                   <div class="form-group" style="margin-bottom:0px">
                                                                       <button id="search_button" v-on:click="add_filters" class="btn btn-round btn-success btn-outline">
                                                                           Добавить
                                                                       </button>
                                                                   </div>
                                                               </div>
                                                           </div>
                                                       <div class="row">
                                                           <div class="col-md-12">
                                                               <div class="bootstrap-tagsinput">
                                                                   <div id="parameters">
                                                                       <span class="badge badge-azure" v-for="(filter, key) in filters">@{{ filter.value }}@{{ (filter.unit) ? ', ' + filter.unit : '' }}<span data-role="remove" class="badge-remove-link" values="0.733" v-on:click="delete_filter(key)"></span></span>
                                                                   </div>
                                                               </div>
                                                           </div>
                                                       </div>
                                                   </template>
                                                   </div>
                                               </div>
                                           </div>
                                        </div>
                                    @else
                                        @if($work_volume_materials->where('is_tongue', ($work_volume->type == 1 ? 0 : 1))->count())
                                            <div class="card strpied-tabled-with-hover" >
                                                <div class="fixed-table-toolbar toolbar-for-btn">
                                                </div>
                                                <div class="table-responsive">
                                                    <table class="table table-hover mobile-table">
                                                        <thead>
                                                        <tr>
                                                            <th>Наименование материала</th>
                                                            <th class="text-center">Единицы измерения</th>
                                                            <th class="text-center">Количество</th>
                                                        </tr>
                                                        </thead>
                                                        <tbody>
                                                        @foreach($work_volume_materials_card->where('is_tongue', ($work_volume->type == 1 ? 0 : 1))->where('combine_id', null)->groupBy('node_id') as $node_id => $materials)
                                                            @if($node_id == '')
                                                                @foreach($materials as $material)
                                                                    <tr>
                                                                        <td data-label="Наименование материала">{{ $material->name }}</td>
                                                                        <td data-label="Единицы измерения" class="text-center">{{ $material->unit }}</td>
                                                                        <td data-label="Количество" class="text-center">{{ number_format($material->count, 3, '.', '') }}</td>
                                                                    </tr>
                                                                @endforeach
                                                            @endif
                                                        @endforeach
                                                        @if($work_volume->type == 0)
                                                        @foreach($complects as $material)
                                                            <tr>
                                                                <td data-label="Наименование материала">{{ $material->name }}</td>
                                                                <td data-label="Единицы измерения" class="text-center">{{ $material->unit }}</td>
                                                                <td data-label="Количество" class="text-center">{{ number_format($material->count, 3, '.', '') }}</td>
                                                            </tr>
                                                        @endforeach
                                                        @endif
                                                        @php $combine_ids = []; @endphp

                                                        @foreach($work_volume_materials_card->where('is_tongue', ($work_volume->type == 1 ? 0 : 1))->where('combine_id', '!=', null) as $material)
                                                            @if(!in_array($material->combine_id, $combine_ids))
                                                                <tr>
                                                                    <td data-label="Наименование материала">
                                                                        {{ $material->combine_pile() }}
                                                                        (
                                                                        @foreach($work_volume_materials_card->where('combine_id' , $material->combine_id) as $key => $item)
                                                                            @if(!in_array($item->combine_id, $combine_ids))
                                                                                {{ $item->name . ' + ' }}
                                                                            @else
                                                                                {{ $item->name }}
                                                                            @endif

                                                                            @php $combine_ids[] = $material->combine_id; @endphp
                                                                        @endforeach
                                                                        )
                                                                    </td>
                                                                    <td data-label="Единицы измерения" class="text-center">{{ $material->unit }}</td>
                                                                    <td data-label="Количество" class="text-center">{{ number_format($material->count, 3, '.', '') }}</td>
                                                                </tr>
                                                            @endif
                                                        @endforeach
                                                        </tbody>
                                                    </table>
                                                </div>
                                            </div>
                                        @endif
                                    @endif

                                    <div class="table-responsive" id="work_table">
                                        <table class="table mobile-table">
                                            <thead>
                                            @if($works->count())
                                                <tr>
                                                    @if(session()->get('edited_wv_id', 'default') == $work_volume->id)
                                                    <th style="width:80px"></th>
                                                    @endif
                                                    <th>Вид работы</th>
                                                    <th class="text-center">Ед. измерения</th>
                                                    <th class="text-center">Количество</th>
                                                    <th class="text-center">Срок, дней</th>
                                                    @if(session()->get('edited_wv_id', 'default') == $work_volume->id)
                                                        <th class="text-right">Действия</th>
                                                    @endif
                                                </tr>
                                            @endif
                                            </thead>
                                            <tbody>
                                            <tr class="work" style="border-bottom:none"></tr>
                                            @foreach($works->groupBy('manual.work_group_id') as $id => $group)
                                                <tr class="tr-title">
                                                    @if(session()->get('edited_wv_id', 'default') == $work_volume->id)
                                                        <td></td>
                                                    @endif
                                                    <td class="th-text">{{$work_groups[$id]}}</td>
                                                    <td></td>
                                                    <td></td>
                                                    <td></td>
                                                    @if(session()->get('edited_wv_id', 'default') == $work_volume->id)
                                                        <td class="td-actions text-right">
                                                            <form action="{{ route('projects::work_volume::delete_works', [$work_volume->id, $id]) }}" method="post">
                                                                @csrf
                                                                <button type="submit" class="btn btn-link p-1">
                                                                    <i class="fa fa-times" style="color:red"></i>
                                                                </button>
                                                            </form>
                                                        </td>
                                                    @endif
                                                </tr>
                                                @foreach($group as $work)
                                                    <tr class="work" wvw_id="{{ $work->id }}">
                                                        @if(session()->get('edited_wv_id', 'default') == $work_volume->id)
                                                            <td class="text-left text-center--mobile">
                                                                <button type="button" data-original-title="Вверх" class="btn btn-link btn-up up mn-0">
                                                                    <i class="fa fa-chevron-up"></i>
                                                                </button>
                                                                <button type="button" data-original-title="Вниз" class="btn btn-link btn-down down mn-0">
                                                                    <i class="fa fa-chevron-down"></i>
                                                                </button>
                                                            </td>
                                                        @endif
                                                        <td data-label="Вид работы">
                                                            {{ $work->manual->name }}
                                                            @if($work->shown_materials->count() and $work->manual->show_materials)
                                                                @if($id == 2)
                                                                    (
                                                                    @php $combine_ids = []; @endphp
                                                                    @foreach($work->shown_materials->where('combine_id', '!=', null)->groupBy('manual_material_id')->values() as  $index => $material)
                                                                        @if(!in_array($material->first()->combine_id, $combine_ids))
                                                                            {{ $material->first()->combine_pile() }}
                                                                            (
                                                                            @foreach($work_volume_materials_card->where('combine_id' , $material->first()->combine_id) as $key =>  $item)
                                                                                @if(!in_array($item->combine_id, $combine_ids))
                                                                                    {{ $item->name . ' + ' }}
                                                                                @else
                                                                                    {{ $item->name }}
                                                                                @endif

                                                                                @php $combine_ids[] = $material->first()->combine_id; @endphp
                                                                            @endforeach
                                                                            @if ($index != $work->materials->where('combine_id', '!=', null)->count() - 2)
                                                                                ),
                                                                            @endif
                                                                            @php
                                                                                $combine_ids[] = $material->first()->combine_id;
                                                                            @endphp
                                                                        @endif

                                                                    @endforeach
                                                                    @foreach($work->shown_materials->where('combine_id', '=', null)->groupBy('manual_material_id')->values() as $key => $material)
                                                                        @if($key < $work->shown_materials->where('combine_id', '=', null)->groupBy('manual_material_id')->count() - 1)
                                                                        {{ $material->last()->name . ','}}
                                                                        @else
                                                                        {{ $work->shown_materials->where('combine_id', '=', null)->groupBy('manual_material_id')->last()->last()->name }}
                                                                        @endif
                                                                    @endforeach
                                                                    )
                                                                @else
                                                                    @if($work->shown_materials->isNotEmpty())
                                                                        (
                                                                        @foreach($work->shown_materials->slice(0, -1) as $material)
                                                                            @if(isset($material->manual)) {{ $material->name . ','}} @endif
                                                                        @endforeach
                                                                        {{ $work->shown_materials->last() ? (isset($work->shown_materials->last()->manual) ? $work->shown_materials->last()->name : '') : '' }}
                                                                        )
                                                                    @endif
                                                                @endif
                                                            @endif
                                                        </td>
                                                        <td data-label="Ед. измерения" class="text-center">{{ $work->unit }}</td>
                                                        <td data-label="Количество" class="text-center">{{ number_format($work->count, 3, '.', '') }}</td>
                                                        <td data-label="Срок, дней" class="text-center">{{ $work->term }}</td>
                                                        @if(session()->get('edited_wv_id', 'default') == $work_volume->id)
                                                            <td data-label="" class="td-actions text-right actions">
                                                                <button rel="tooltip" onclick="edit_work(this, {{ $work->id }})" class="btn-success btn-link btn-xs btn padding-actions mn-0" data-original-title="Редактировать">
                                                                    <i class="fa fa-edit"></i>
                                                                </button>
                                                                <button rel="tooltip" onclick="delete_work(this, {{ $work->id }})" class="btn-danger btn-link btn-xs btn padding-actions mn-0" data-original-title="Удалить">
                                                                    <i class="fa fa-times"></i>
                                                                </button>
                                                            </td>
                                                        @endif
                                                    </tr>
                                                @endforeach
                                            @endforeach
                                            </tbody>
                                        </table>
                                    </div>

                                    <!-- Добавление, редактирование работ -->

                                    <!-- end work -->
                                    @if(session()->get('edited_wv_id', 'default') == $work_volume->id)
                                            <div class="row">
                                                <div class="col-md-12 text-right" >
                                                    <div class="m-center">
                                                        <button id="addWork" type="button" class="btn btn-sm btn-success btn-round btn-outline" onclick="add_new_work(this)">
                                                            <i class="fa fa-plus"></i>
                                                            Добавить запись
                                                        </button>
                                                    </div>
                                                </div>
                                            </div>

                                    @endif
                                    @if(!$work_volume_requests->where('status', 0)->count() && $work_volume->status == 1 && ($is_tongue ? Auth::user()->isInGroup(53)/*16*/ : 1))
                                    <div class="text-center">
                                            <button type="button" class="btn btn-wd btn-primary" data-toggle="modal" data-target="#commentAccept">
                                                Сохранить
                                            </button>
                                        </div>
                                    @elseif(session()->get('edited_wv_id', 'default') == $work_volume->id)
                                        <div class="text-center" style="margin-top:35px">
                                            <button class="btn btn-wd btn-primary" @if($is_tongue) onclick="checkDepth(this)" @else data-toggle="modal" data-target="#solveRequest" @endif>
                                                Сохранить изменения
                                            </button>
                                        </div>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    @if(session()->get('edited_wv_id', 'default') == $work_volume->id)
        <div class="d-none">
            <table>
                <tr id="new_work">
                    <td colspan="6" style="padding:0!important">
                        <div class="work-container" style="padding-top:10px">
                            <div class="row">
                                <div class="col-md-12">
                                    <button class="btn btn-close-record btn-link pull-right btn-sm" onclick="delete_input_now(this)" style="padding: 0; margin-bottom:0; color: red; float:right;">
                                        <i class="pe-7s-close"></i>
                                    </button>
                                </div>
                            </div>
                            <div class="elements-work-container">
                                <form id="save_one" class="validate" action="{{ route('projects::work_volume::save_one', [$work_volume->id]) }}" method="post">
                                    @csrf
                                    <input type="hidden" name="is_tongue" value="{{ $is_tongue }}">
                                    <div class="add-work">
                                        <div class="row">
                                            <div class="col-md-6" style="padding:0!important; margin-bottom:15px">
                                                <h6 class="work-title" style="padding-top:0!important;">
                                                    Работы
                                                </h6>
                                            </div>
                                        </div>
                                        <div class="row work_input">
                                            <div class="col-md-12">
                                                <div class="row">
                                                    <div class="col-md-4" style="padding: 0">
                                                        <div class="form-group" style="margin-top:6px">
                                                            <select class="js-select-work-first" onchange="add_work_count(this)" name="work_id[]" style="width:100%;" required>
                                                                <option value="">Не выбрано</option>
                                                            </select>
                                                        </div>
                                                    </div>
                                                    <!-- Поле количество работ появляется только для работы каторая в базе не привязана к материалам  -->
                                                    <div class="col-md-4" style="padding: 0">
                                                        <div class="form-group">
                                                            <div class="row">
                                                                <div class="col-md-6 m-pr-pl">
                                                                    <label for="">Количество</label>
                                                                </div>
                                                                <div class="col-md-6 ">
                                                                    <input type="number" name="work_count[]" required onchange="add_term(this)" placeholder="Укажите количество" class="form-control" min="0.001" step="0.001"  style="margin-top:0">
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <div class="col-md-3" style="padding: 0">
                                                        <div class="form-group">
                                                            <div class="row">
                                                                <div class="col-md-4 m-pr-pl">
                                                                    <label for="">Срок, дней</label>
                                                                </div>
                                                                <div class="col-md-8">
                                                                    <input name="work_term[]" type="number" required onchange="edit_term(this)" placeholder="Срок работы" class="form-control" style="margin-top:0">
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                                <hr class="hr-work">
                                            </div>
                                        </div>

                                        <div class="row">
                                            <div class="col-md-12" style="padding:0; margin-top:10px">
                                                <button type="button" class="btn btn-round btn-success btn-outline btn-sm" style="font-size:14px; margin-left:0!important" onclick="add_work(this)">
                                                    <i class="fa fa-plus"></i>
                                                    Добавить работу
                                                </button>
                                            </div>
                                        </div>
                                        <hr>
                                    </div>
                                    <div class="add-material">
                                        <div class="row">
                                            <div class="col-md-6" style="padding:0!important">
                                                <h6 class="work-title" style="margin-bottom:20px; padding-left:0!important">
                                                    Материалы
                                                </h6>
                                            </div>
                                        </div>
                                        <div class="material_input"></div>
                                        <div class="row">
                                            <div class="col-md-12" style="padding:0">
                                                <button type="button" class="btn btn-round btn-success btn-outline btn-sm" onclick="add_material(this, {{ $work_volume_materials }})" style="margin-left:0px!important;font-size:14px">
                                                    <i class="fa fa-plus"></i>
                                                    Добавить материал
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                </form>
                            </div>
                            <div class="row">
                                <div class="col-md-12 text-center" style="padding-top:40px">
                                    <button type="submit" form="save_one" class="btn btn-info btn-sm" style="margin-left:0!important; font-size:14px">
                                        Сохранить
                                    </button>
                                </div>
                            </div>
                        </div>
                    </td>
                    <td class="td-0"></td>
                </tr>

                <tr id="edit_work">
                    <td colspan="6" style="padding:0!important">
                        <div class="work-container">
                            <div class="row">
                                <div class="col-md-12">
                                    <button class="btn btn-close-record btn-link pull-right btn-sm" onclick="delete_input_now(this)" style="padding: 0; margin-bottom:0">
                                        <i class="pe-7s-close"></i>
                                    </button>
                                </div>
                            </div>
                            <div class="elements-work-container">
                                <form id="edit_one" class="validate" action="{{ route('projects::work_volume::edit_one') }}" method="post">
                                    @csrf
                                    <input type="hidden" name="wv_work_id" class="wv_work_id">
                                    <input type="hidden" name="wv_id" value="{{ $work_volume->id }}">
                                    <input type="hidden" name="is_tongue" value="{{ $is_tongue }}">
                                    <div class="add-work">
                                        <div class="row">
                                            <div class="col-md-6" style="padding:0!important">
                                                <h6 class="work-title" style="padding-top:0!important">
                                                    Работы
                                                </h6>
                                            </div>
                                        </div>
                                        <div class="row work_input">
                                            <div class="col-md-12">
                                                <div class="row">
                                                    <div class="col-md-4" style="padding: 0">
                                                        <div class="form-group" style="margin-top:6px">
                                                            <select class="js-select-work-first" onchange="add_work_count(this)" name="work_id[]" style="width:100%;" required>
                                                                <option value="">Не выбран</option>
                                                            </select>
                                                        </div>
                                                    </div>
                                                    <!-- Поле количество работ появляется только для работы каторая в базе не привязана к материалам  -->
                                                    <div class="col-md-4" style="padding: 0">
                                                        <div class="form-group">
                                                            <div class="row">
                                                                <div class="col-md-6 m-pr-pl">
                                                                    <label for="">Количество</label>
                                                                </div>
                                                                <div class="col-md-6">
                                                                    <input type="number" name="work_count[]" required onchange="add_term(this)" id="edit_work_count" placeholder="Укажите количество" class="form-control" min="0.001" step="0.001">
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <div class="col-md-3" style="padding: 0">
                                                        <div class="form-group">
                                                            <div class="row">
                                                                <div class="col-md-4 m-pr-pl">
                                                                    <label for="">Срок, дней</label>
                                                                </div>
                                                                <div class="col-md-8">
                                                                    <input name="work_term[]" id="edit_work_term" required onchange="edit_term(this)" type="number" placeholder="Срок работы" class="form-control">
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>

                                            </div>

                                        </div>
                                        <div class="row">
                                            <div class="col-md-6"  style="padding:0; margin-top:10px">
                                                <button type="button" class="btn btn-round btn-success btn-outline btn-sm" style="margin-left:0!important; font-size:14px;" onclick="add_work(this)">
                                                    <i class="fa fa-plus"></i>
                                                    Добавить работу
                                                </button>
                                            </div>
                                        </div>
                                        <hr>
                                    </div>
                                    <div class="add-material">
                                        <div class="row">
                                            <div class="col-md-6" style="padding:0!important">
                                                <h6 class="work-title" style="padding-top:0!important; padding-left:0!important; margin-bottom:20px;">
                                                    Материалы
                                                </h6>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="material_input"></div>
                                    <div class="row">
                                        <div class="col-md-6"  style="padding:0">
                                            <button type="button" class="btn btn-round btn-success btn-outline btn-sm" onclick="add_material(this, {{ $work_volume_materials }})" style="margin-left:0!important; font-size:14px;">
                                                <i class="fa fa-plus"></i>
                                                Добавить материал
                                            </button>
                                        </div>
                                    </div>
                                </form>
                                <div class="row" style="margin-top:40px">
                                    <div class="col-md-12 text-center">
                                        <button type="submit" form="edit_one" class="btn btn-info btn-sm" style="margin-left:0!important; font-size:14px">
                                            Сохранить
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </td>
                    <td class="td-0"></td>
                </tr>
            </table>

            <div class="row" id="new_work_input">
                <div class="col-md-12">
                    <div class="row">
                        <div class="col-md-4" style="padding: 0">
                            <div class="form-group" style="margin-top:6px">
                                <select class="js-select-work" onchange="add_work_count(this)" name="work_id[]" style="width:100%;" required>
                                    <option value="">Не выбран</option>
                                </select>
                            </div>
                        </div>
                        <!-- Поле количество работ появляется только для работы каторая в базе не привязана к материалам  -->
                        <div class="col-md-4">
                            <div class="form-group">
                                <div class="row">
                                    <div class="col-md-6 m-pr-pl">
                                        <label for="">Количество</label>
                                    </div>
                                    <div class="col-md-6" style="padding:0">
                                        <input type="number" name="work_count[]" required onchange="add_term(this)" placeholder="Укажите количество" step="0.001" class="form-control" min="0.001" style="margin-top:0">
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="col-md-3">
                            <div class="form-group">
                                <div class="row">
                                    <div class="col-md-4 m-pr-pl">
                                        <label for="">Срок, дней</label>
                                    </div>
                                    <div class="col-md-8" style="padding: 0">
                                        <input name="work_term[]" type="number" required onchange="edit_term(this)" placeholder="Срок работы" class="form-control" style="margin-top:0">
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-xl-1 col-md-1 text-center m-close">
                            <div class="form-group" style="margin-bottom:0">
                                <button class="btn btn-close btn-link" onclick="delete_work_input(this)" style="margin-bottom:0">
                                    <i class="fa fa-times"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                    <hr class="hr-work">
                </div>
            </div>

            <div class="row" id="new_material">
                <div class="col-md-12">
                    <div class="material-item">
                        <div class="row">
                            <div class="col-md-6" style="padding:0; width:90%">
                                <div class="form-group">
                                    <select class="js-select-material" onchange="add_work_count(this)" name="material_id[]" style="width:100%;" required>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-1 text-center" style="padding:3px 0; width:10%">
                                <div class="form-group" style="margin-bottom:0">
                                    <button type="button" class="btn btn-close btn-link" onclick="delete_material_input(this)">
                                        <i class="fa fa-times"></i>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

        </div>

        <!-- Модалки -->
        <div class="modal fade bd-example-modal-lg show" id="solveRequest" role="dialog" aria-labelledby="modal-search" style="display: none;">
            <div class="modal-dialog modal-lg" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Сохранение изменений</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">×</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <hr style="margin-top:0">
                        <div class="card border-0" >
                            <div class="card-body ">
                                <form id="updateWVRequest" action="{{ route('projects::work_volume_request::wv_update', session()->get('edited_wv_request_id', 'none')) }}" method="post">
                                    @csrf
                                    <div class="row">
                                        <label class="col-sm-3 col-form-label">Комментарий<star class="star">*</star></label>
                                        <div class="col-sm-7">
                                            <div class="form-group">
                                                <textarea class="form-control textarea-rows" required maxlength="500" name="confirm_comment"></textarea>
                                            </div>
                                        </div>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Закрыть</button>
                        <button type="submit" form="updateWVRequest" class="btn btn-wd btn-primary">
                            Отправить
                        </button>
                    </div>
                </div>
            </div>
        </div>
    @endif
    <div class="modal fade bd-example-modal-lg show" id="commentAccept" role="dialog" aria-labelledby="modal-search" style="display: none;">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Согласование ОР</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">×</span>
                    </button>
                </div>
                <div class="modal-body">
                    <hr style="margin-top:0">
                    <div class="card border-0" >
                        <div class="card-body ">
                            <form id="confirmForm" action="{{ route('projects::work_volume::send', $work_volume->id) }}" method="post">
                                @csrf
                                <input type="hidden" name="is_tongue" value="{{ $is_tongue }}">
                                @if (!isset($sop))
                                    <input type="hidden" name="noSOP" value="noSOP">
                                @endif
                                <div class="row">
                                    <label class="col-sm-3 col-form-label">Комментарий<star class="star">*</star></label>
                                    <div class="col-sm-7">
                                        <div class="form-group">
                                            <textarea class="form-control textarea-rows" required maxlength="500" name="final_note"></textarea>
                                        </div>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Закрыть</button>
                    <button type="submit" form="confirmForm" class="btn btn-wd btn-primary">
                        Отправить
                    </button>
                </div>
            </div>
        </div>
    </div>

    @php
        $unsolved = 0;
    @endphp
    @foreach ($work_volume_requests as $key => $wv_request)
        <div class="modal fade bd-example-modal-lg show" id="view-request{{ $wv_request->id }}" role="dialog" aria-labelledby="modal-search" aria-hidden="true">
            <div class="modal-dialog modal-lg" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Заявка</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">×</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <hr style="margin-top:0">
                        <div class="card border-0" >
                            <div class="card-body">
                                <div class="accordions" id="accordion">
                                    <form id="update_request_form{{ $wv_request->id }}" class="form-horizontal" action="{{ route('projects::work_volume_request::update', [$wv_request->project_id, $wv_request->work_volume_id]) }}" method="post" enctype="multipart/form-data">
                                        @csrf
                                        <input type="hidden" value="{{ $wv_request->id }}" name="wv_request_id" />
                                        <div class="card">
                                            <h4 class="card-title">
                                                {{ $wv_request->tongue_pile ? 'Свайное направление' : 'Шпунтовое направление' }}
                                            </h4>
                                            <div class="card-body ">
                                                <div class="row">
                                                    <div class="col-md-12">
                                                        <div class="form-group">
                                                            <div class="row">
                                                                <div class="col-md-12">
                                                                    <h6 style="margin:10px 0">
                                                                        Описание
                                                                    </h6>
                                                                    <p class="form-control-static">{{ $wv_request->description }}</p>
                                                                </div>
                                                            </div>
                                                            @if ($wv_request->excavation_description !== null)
                                                                <div class="row">
                                                                    <div class="col-md-12">
                                                                        <label class="control-label">Земляные работы</label>
                                                                        <p>{{ $wv_request->excavation_description }}</p>
                                                                    </div>
                                                                </div>
                                                            @endif

                                                            @if ($wv_request->files->where('is_result', 0)->count() > 0)
                                                                <div class="row">
                                                                    <div class="col-md-12">
                                                                        <label class="control-label">Приложенные файлы</label>
                                                                        <br>
                                                                        @foreach($wv_request->files->where('is_result', 0)->where('is_proj_doc', 0) as $file)
                                                                            <a target="_blank" href="{{ asset('storage/docs/work_volume_request_files/' . $file->file_name) }}">
                                                                                {{ $file->original_name }}
                                                                            </a>
                                                                            <br>
                                                                        @endforeach

                                                                        @foreach($wv_request->files->where('is_result', 0)->where('is_proj_doc', 1) as $file)
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
                                                @if($wv_request->status === 0)
                                                    @php
                                                        $unsolved++;
                                                    @endphp
                                                @endif
                                                @if($unsolved == 0 || $unsolved == 1)
                                                    @if ($wv_request->status === 0 && (session()->get('edited_wv_request_id', 'default') != $wv_request->id))
                                                        @if(Auth::id() == $WV_resp)
                                                            <hr>
                                                            <div class="row solve{{ $wv_request->id }}">
                                                                <div class="col-md-12">
                                                                    <div class="row">
                                                                        <div class="col-md-12">
                                                                            <h6 style="margin:10px 0 20px">Результат</h6>
                                                                        </div>
                                                                    </div>
                                                                    <div class="row" style="margin-bottom:15px">
                                                                        <div class="col-md-4" style="padding-left:0">
                                                                            <div class="form-check form-check-radio">
                                                                                <label class="form-check-label"  style="text-transform:none;font-size:13px">
                                                                                    <input class="form-check-input" type="radio" name="status" id="" value="confirm" onclick="reject({{ $wv_request->id }}, 'close')" required>
                                                                                    <span class="form-check-sign"></span>
                                                                                    Подтвердить
                                                                                </label>
                                                                            </div>
                                                                        </div>
                                                                        <div class="col-md-4" style="padding-left:0">
                                                                            <div class="form-check form-check-radio">
                                                                                <label class="form-check-label" style="text-transform:none;font-size:13px">
                                                                                    <input class="form-check-input" type="radio" name="status" id="" value="reject" onclick="reject({{ $wv_request->id }}, 'open')" required>
                                                                                    <span class="form-check-sign"></span>
                                                                                    Отклонить
                                                                                </label>
                                                                            </div>
                                                                        </div>
                                                                    </div>
                                                                    <div class="{{ $wv_request->id }}" style="display: none">
                                                                        <div class="row">
                                                                            <div class="col-md-12">
                                                                                <div class="row">
                                                                                    <label class="col-sm-3 col-form-label">Комментарий<star class="star">*</star></label>
                                                                                    <div class="col-sm-9">
                                                                                        <div class="form-group">
                                                                                            <textarea class="form-control textarea-rows textarea{{ $wv_request->id }}" name="comment"></textarea>
                                                                                        </div>
                                                                                    </div>
                                                                                </div>
                                                                            </div>
                                                                        </div>
                                                                        <div class="row">
                                                                            <label class="col-sm-3 col-form-label" for="" style="font-size:13px">
                                                                                Приложенные файлы
                                                                            </label>
                                                                            <div class="col-sm-6">
                                                                                <div class="file-container">
                                                                                    <div id="fileName" class="file-name"></div>
                                                                                    <div class="file-upload ">
                                                                                        <label class="pull-right">
                                                                                            <span><i class="nc-icon nc-attach-87" style="font-size:25px; line-height: 40px"></i></span>
                                                                                            <input type="file" name="documents[]" accept="*" id="uploadedFiles" class="form-control-file file" onchange="getFileName(this)" multiple>
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
                                                                                <select class="js-select-proj-doc" name="project_documents[]" data-title="Выберите документ" data-style="btn-default btn-outline" multiple data-menu-style="dropdown-blue" style="width:100%;">
                                                                                </select>
                                                                            </div>
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        @endif
                                                    @else
                                                        <hr>
                                                        <div class="row">
                                                            <div class="col-md-12">
                                                                <div class="form-group">
                                                                    <div class="row">
                                                                        <div class="col-md-12">
                                                                            <h6 style="margin:10px 0">
                                                                                Решение
                                                                            </h6>
                                                                            <p class="form-control-static">@if(session()->get('edited_wv_request_id', 'default') == $wv_request->id) Вы приняли заявку @else {{ $wv_request->result_comment }} @endif</p>
                                                                        </div>
                                                                    </div>
                                                                    @if ($wv_request->files->where('is_result', 1)->count() > 0)
                                                                        <div class="row">
                                                                            <div class="col-md-12">
                                                                                <label class="control-label">Приложенные файлы</label>
                                                                                <br>
                                                                                @foreach($wv_request->files->where('is_result', 1)->where('is_proj_doc', 0) as $file)
                                                                                    <a target="_blank" href="{{ asset('storage/docs/work_volume_request_files/' . $file->file_name) }}">
                                                                                        {{ $file->original_name }}
                                                                                    </a>
                                                                                    <br>
                                                                                @endforeach

                                                                                @foreach($wv_request->files->where('is_result', 1)->where('is_proj_doc', 1) as $file)
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
                                                    @endif
                                                @endif
                                            </div>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Закрыть</button>
                        @if($unsolved == 0 || $unsolved == 1)
                            @if(Auth::id() == $WV_resp)
                                @if ($wv_request->status === 0 && (session()->get('edited_wv_request_id', 'default') != $wv_request->id))
                                    <button id="submit_wv" type="submit" form="update_request_form{{ $wv_request->id }}" class="btn btn-info">Сохранить</button>
                                @endif
                            @endif
                        @endif
                    </div>
                </div>
            </div>
        </div>
    @endforeach
    <!-- end modal -->

    @if(session()->get('edited_wv_id', 'default') == $work_volume->id)

    <div class="modal fade bd-example-modal-lg show" id="complect_materials_modal" role="dialog" aria-labelledby="modal-search" style="display: none;">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Объединение материалов</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">×</span>
                    </button>
                </div>
                <div class="modal-body">
                    <hr style="margin-top:0">
                    <div class="card border-0" >
                        <div class="card-body ">
                            <form id="make_complect" action="{{ route("projects::work_volume::complect_materials", $work_volume->id) }}">
                                <div class="materials-container">
                                    <div class="row">
                                        <div class="col-md-12">
                                            <label for="">Название объединенных материалов<star class="star">*</star></label>
                                            <input type="text" id="" name="name" class="form-control" maxlength="100" required placeholder="Укажите название для объединенного материала">
                                        </div>
                                    </div>
                                    <div class="row">
                                        <div class="col-md-12">
                                            <h6 class="mb-10 mt-30 text-center">Материалы</h6>
                                            @foreach($work_volume_materials->where('combine_id', null)->where('node_id', '') as $material)
                                                <div class="form-check">
                                                    <label class="form-check-label" style="font-size: 13px; padding-left: 24px;line-height: 20px;">
                                                        <input class="form-check-input combine" name="complect_ids[]" type="checkbox" value="{{ $material->id }}" onclick="checkCheckboxes()">
                                                        <span class="form-check-sign"></span>
                                                        {{ ($material->name) .'; '. number_format($material->count, 3, '.', '') .' '. $material->unit . ';'}}
                                                    </label>
                                                </div>
                                            @endforeach
                                        </div>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Закрыть</button>
                    <button id="combineSubmit" type="submit" form="make_complect" class="btn btn-wd btn-primary btn-outline" disabled>
                        Объединить
                    </button>
                </div>
            </div>
        </div>
    </div>


    <div class="modal fade" id="calc-mounts" data-backdrop="static" data-keyboard="false" tabindex="-1" aria-labelledby="staticBackdropLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <form id="form_mount_calc" action="{{ route('projects::work_volume::create_mount_calc', $work_volume->id) }}" method="post">
                    @csrf
                    <div>
                        <h3>Обвязочная балка</h3>
                        <section>
                            <div class="col-12">
                                <div class="row">
                                    <div class="checkbox-radios">
                                        <div class="form-check form-check-radio">
                                            <label class="form-check-label">
                                                <input class="form-check-input" type="radio" name="is_out" value="0" checked>
                                                <span class="form-check-sign"></span>
                                                Конструктивный
                                            </label>
                                        </div>
                                        <div class="form-check form-check-radio">
                                            <label class="form-check-label">
                                                <input class="form-check-input" type="radio" name="is_out" value="1">
                                                <span class="form-check-sign"></span>
                                                Извлекаемый
                                            </label>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <hr>
                            <div class="col-12">
                                <div class="material_place first_material">
                                    <div class="row">
                                        <div class="col-md-3">
                                            <label for="">Тип балки</label>
                                        </div>
                                        <div class="col-md-8" style="width:86%; padding-right:0px">
                                            <select name="strapping_beam[]" style="width:100%;" onchange="calc_change_mat(this)" class="selectpicker material-type select_beam" ajax_material_find="get_beam" data-title="Выберите тип балки" data-style="btn-default btn-outline" data-menu-style="dropdown-blue">
                                            </select>
                                        </div>
                                        <div class="col-1" style="width:10%;padding-top:6px">
                                            <button rel="tooltip" type="button" class="btn-danger btn-link btn padding-actions mn-0" onclick="remove_material_place(this)" data-original-title="Удалить" style="margin top:6px">
                                                <i class="fa fa-times"></i>
                                            </button>
                                        </div>
                                    </div>
                                    <br>
                                    <div class="row amount" style="display:none">
                                        <div class="col-md-3">
                                            <label for="">Кол-во, м.п.<star class="star">*</star></label>
                                        </div>
                                        <div class="col-md-3">
                                            <input type="number" name="strapping_beam_count[]" class="form-control mat_length" min="0.001" step="0.001" max="10000000" required>
                                        </div>
                                        <div class="col-md-2">
                                            <label for="">Кол-во, т<star class="star">*</star></label>
                                        </div>
                                        <div class="col-md-3">
                                            <input type="number" name="strapping_beam_count_weight[]" class="form-control mat_weight mat_beam" min="0.001" step="0.001" max="10000000" required>
                                        </div>
                                    </div>
                                </div>
                                <div class="row" style="margin-top:20px">
                                    <div class="col-md-12 text-center">
                                        <button type="button" class="btn btn-outline btn-sm btn-round" onclick="add_new_material(this)">
                                            <i class="fa fa-plus"></i>
                                            Добавить балку
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </section>
                        <h3>Угловые распоры</h3>
                        <section>
                            <div class="material_place first_material">
                                <div class="row">
                                    <div class="col-md-3">
                                        <label for="">Тип трубы</label>
                                    </div>
                                    <div class="col-md-8" style="width:86%; padding-right:0px">
                                        <select name="corner_strut[]" style="width:100%;" class="selectpicker material-type select_pipe" onchange="calc_change_mat(this)" ajax_material_find="get_pipe" data-title="Выберите тип трубы" data-style="btn-default btn-outline" data-menu-style="dropdown-blue">
                                        </select>
                                    </div>
                                    <div class="col-1"  style="width:10%;padding-top:6px">
                                        <button rel="tooltip" type="button" class="btn-danger btn-link btn padding-actions mn-0" onclick="remove_material_place(this)" data-original-title="Удалить">
                                            <i class="fa fa-times"></i>
                                        </button>
                                    </div>
                                </div>
                                <br>
                                <div class="amount" style="display:none">
                                    <div class="row">
                                        <div class="col-md-3">
                                            <label for="">Кол-во, шт<star class="star">*</star></label>
                                        </div>
                                        <div class="col-md-3">
                                            <input type="number" name="corner_strut_count[]" class="form-control common_mat_count" min="1" step="1" max="10000000" required>
                                        </div>
                                        <div class="col-md-2">
                                            <label for="">Длина, м.п.<star class="star">*</star></label>
                                        </div>
                                        <div class="col-md-3">
                                            <input type="number" name="corner_strut_length[]" class="form-control mat_length" min="0.001" step="0.001" max="10000000" required>
                                        </div>
                                    </div>
                                    <div class="row" style="margin-top: 8px;">
                                        <div class="col-md-6"></div>
                                        <div class="col-md-2">
                                            <label for="">Тоннаж, т<star class="star">*</star></label>
                                        </div>
                                        <div class="col-md-3">
                                            <input type="number" name="corner_strut_count_weight[]" class="form-control mat_weight" min="0.001" step="0.001" max="10000000" required>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="row" style="margin-top:20px">
                                <div class="col-md-12 text-center">
                                    <button type="button" class="btn btn-outline btn-sm btn-round" onclick="add_new_material(this)">
                                        <i class="fa fa-plus"></i>
                                        Добавить распору
                                    </button>
                                </div>
                            </div>
                        </section>
                        <h3>Поперечные распоры</h3>
                        <section>
                            <div class="material_place first_material">
                                <div class="row">
                                    <div class="col-md-3">
                                        <label for="">Тип трубы</label>
                                    </div>
                                    <div class="col-md-8" style="width:86%; padding-right:0px">
                                        <select name="cross_strut[]" style="width:100%;" class="selectpicker material-type select_pipe" onchange="calc_change_mat(this)" ajax_material_find="get_pipe" data-title="Выберите тип трубы" data-style="btn-default btn-outline" data-menu-style="dropdown-blue">
                                        </select>
                                    </div>
                                    <div class="col-1" style="width:10%;padding-top:6px">
                                        <button rel="tooltip" type="button" class="btn-danger btn-link btn padding-actions mn-0" onclick="remove_material_place(this)" data-original-title="Удалить">
                                            <i class="fa fa-times"></i>
                                        </button>
                                    </div>
                                </div>
                                <br>
                                <div class="amount" style="display:none">
                                    <div class="row">
                                        <div class="col-md-3">
                                            <label for="">Кол-во, шт<star class="star">*</star></label>
                                        </div>
                                        <div class="col-md-3">
                                            <input type="number" name="cross_strut_count[]" class="form-control common_mat_count" min="1" max="10000000" required>
                                        </div>
                                        <div class="col-md-2">
                                            <label for="">Длина, м.п.<star class="star">*</star></label>
                                        </div>
                                        <div class="col-md-3">
                                            <input type="number" name="cross_strut_length[]" class="form-control mat_length" min="0.001" step="0.001" max="10000000" required>
                                        </div>
                                    </div>
                                    <div class="row" style="margin-top: 8px;">
                                        <div class="col-md-6"></div>
                                        <div class="col-md-2">
                                            <label for="">Тоннаж, т<star class="star">*</star></label>
                                        </div>
                                        <div class="col-md-3">
                                            <input type="number" name="cross_strut_count_weight[]" class="form-control mat_weight" min="0.001" step="0.001" max="10000000" required>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="row" style="margin-top:20px">
                                <div class="col-md-12 text-center">
                                    <button type="button" class="btn btn-outline btn-sm btn-round" onclick="add_new_material(this)">
                                        <i class="fa fa-plus"></i>
                                        Добавить распору
                                    </button>
                                </div>
                            </div>
                        </section>
                        <h3>Подкосы</h3>
                        <section>
                            <div class="material_place first_material">
                                <div class="row">
                                    <div class="col-md-3">
                                        <label for="">Тип трубы</label>
                                    </div>
                                    <div class="col-md-8" style="width:86%; padding-right:0px">
                                        <select name="strut[]" style="width:100%;" class="selectpicker material-type select_pipe" onchange="calc_change_mat(this)" ajax_material_find="get_pipe" data-title="Выберите тип трубы" data-style="btn-default btn-outline" data-menu-style="dropdown-blue">
                                        </select>
                                    </div>
                                    <div class="col-1"  style="width:10%;padding-top:6px">
                                        <button rel="tooltip" type="button" class="btn-danger btn-link btn padding-actions mn-0" onclick="remove_material_place(this)" data-original-title="Удалить">
                                            <i class="fa fa-times"></i>
                                        </button>
                                    </div>
                                </div>
                                <br>
                                <div class="amount" style="display:none">
                                    <div class="row">
                                        <div class="col-md-3">
                                            <label for="">Кол-во, шт<star class="star">*</star></label>
                                        </div>
                                        <div class="col-md-3">
                                            <input type="number" name="strut_count[]" class="form-control common_mat_count" min="1" max="10000000" required>
                                        </div>
                                        <div class="col-md-2">
                                            <label for="">Длина, м.п.<star class="star">*</star></label>
                                        </div>
                                        <div class="col-md-3">
                                            <input type="number" name="strut_length[]" class="form-control mat_length" min="0.001" step="0.001" max="10000000" required>
                                        </div>
                                    </div>
                                    <div class="row" style="margin-top: 8px;">
                                        <div class="col-md-6"></div>
                                        <div class="col-md-2">
                                            <label for="">Тоннаж, т<star class="star">*</star></label>
                                        </div>
                                        <div class="col-md-3">
                                            <input type="number" name="strut_count_weight[]" class="form-control mat_weight" min="0.001" step="0.001" max="10000000" required>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="row" style="margin-top:20px">
                                <div class="col-md-12 text-center">
                                    <button type="button" class="btn btn-outline btn-sm btn-round" onclick="add_new_material(this)">
                                        <i class="fa fa-plus"></i>
                                        Добавить подкос
                                    </button>
                                </div>
                            </div>
                        </section>
                        <h3>Закладные детали</h3>
                        <section>
                            <div class="material_place first_material">
                                <div class="row">
                                    <div class="col-md-3">
                                        <label for="">Тип детали</label>
                                    </div>
                                    <div class="col-md-8" style="width:86%; padding-right:0px">
                                        <select name="embedded_parts[]" style="width:100%;" class="selectpicker material-type embedded_parts_select" ajax_material_find="get_detail" data-title="Выберите тип закладной детали" data-style="btn-default btn-outline" data-menu-style="dropdown-blue">
                                        </select>
                                    </div>
                                    <div class="col-1"  style="width:10%;padding-top:6px">
                                        <button rel="tooltip" type="button" class="btn-danger btn-link btn padding-actions mn-0" onclick="remove_material_place(this)" data-original-title="Удалить">
                                            <i class="fa fa-times"></i>
                                        </button>
                                    </div>
                                </div>
                                <br>
                                <div class="row amount" style="display:none">
                                    <div class="col-md-3">
                                        <label for="">Кол-во, шт.<star class="star">*</star></label>
                                    </div>
                                    <div class="col-md-3">
                                        <input type="number" name="embedded_parts_count[]" class="form-control common_nodes_count node_count" min="1" max="10000000" required>
                                    </div>
                                    <div class="col-md-2">
                                        <label for="">Кол-во, т.</label>
                                    </div>
                                    <div class="col-md-3">
                                        <input type="number" name="embedded_parts_weight[]" class="form-control node_weight" max="10000000" required>
                                    </div>
                                </div>
                            </div>
                            <div class="row" style="margin-top:20px">
                                <div class="col-md-12 text-center">
                                    <button type="button" class="btn btn-outline btn-sm btn-round" onclick="add_new_material(this)">
                                        <i class="fa fa-plus"></i>
                                        Добавить деталь
                                    </button>
                                </div>
                            </div>
                        </section>
                        <h3>Стойки</h3>
                        <section>
                            <div class="material_place first_material">
                                <div class="row">
                                    <div class="col-md-3">
                                        <label for="">Тип трубы</label>
                                    </div>
                                    <div class="col-md-8" style="width:86%; padding-right:0px">
                                        <select name="racks[]" style="width:100%;" class="selectpicker material-type select_pipe" onchange="calc_change_mat(this)" ajax_material_find="get_pipe" data-title="Выберите тип трубы" data-style="btn-default btn-outline" data-menu-style="dropdown-blue">
                                        </select>
                                    </div>
                                    <div class="col-1"  style="width:10%;padding-top:6px">
                                        <button rel="tooltip" type="button" class="btn-danger btn-link btn padding-actions mn-0" onclick="remove_material_place(this)" data-original-title="Удалить">
                                            <i class="fa fa-times"></i>
                                        </button>
                                    </div>
                                </div>
                                <br>
                                <div class="amount" style="display:none">
                                    <div class="row">
                                        <div class="col-md-3">
                                            <label for="">Кол-во, шт<star class="star">*</star></label>
                                        </div>
                                        <div class="col-md-3">
                                            <input type="number" name="racks_count[]" class="form-control common_mat_count" min="1" max="10000000" required>
                                        </div>
                                        <div class="col-md-2">
                                            <label for="">Длина, м.п.<star class="star">*</star></label>
                                        </div>
                                        <div class="col-md-3">
                                            <input type="number" name="racks_length[]" onchange="" class="form-control mat_length" min="0.001" step="0.001" max="10000000" required>
                                        </div>
                                    </div>
                                    <div class="row" style="margin-top: 8px;">
                                        <div class="col-md-6"></div>
                                        <div class="col-md-2">
                                            <label for="">Тоннаж, т<star class="star">*</star></label>
                                        </div>
                                        <div class="col-md-3">
                                            <input type="number" name="racks_count_weight[]" class="form-control mat_weight" min="0.001" step="0.001" max="10000000" required>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="row" style="margin-top:20px">
                                <div class="col-md-12 text-center">
                                    <button type="button" class="btn btn-outline btn-sm btn-round" onclick="add_new_material(this)">
                                        <i class="fa fa-plus"></i>
                                        Добавить стойку
                                    </button>
                                </div>
                            </div>
                        </section>
                        <h3>Узлы (оп. стол., лист г/к)</h3>
                        <section>
                            <div class="material_place first_material">
                                <div class="row unit_node">
                                    <div class="col-md-3">
                                        <label for="">Тип узла</label>
                                    </div>
                                    <div class="col-md-8" style="width:86%; padding-right:0px">
                                        <select name="nodes[]" style="width:100%;" class="selectpicker material-type nodes_select" ajax_material_find="get_nodes" data-title="Выберите тип узла" data-style="btn-default btn-outline" data-menu-style="dropdown-blue">
                                        </select>
                                    </div>
                                    <div class="col-1" style="width:10%;padding-top:6px">
                                        <button rel="tooltip" type="button" class="btn-danger btn-link btn padding-actions mn-0" onclick="remove_material_place(this)" data-original-title="Удалить">
                                            <i class="fa fa-times"></i>
                                        </button>
                                    </div>
                                </div>
                                <br>
                                <div class="row amount" style="display:none">
                                    <div class="col-md-3">
                                        <label for="">Кол-во, <span>шт</span>.<star class="star">*</star></label>
                                    </div>
                                    <div class="col-md-3">
                                        <input name="nodes_count[]" type="number" class="form-control common_nodes_count node_count" min="1" max="10000000" required>
                                    </div>
                                    <div class="col-md-2 node_weight_hide">
                                        <label for="">Кол-во, т.</label>
                                    </div>
                                    <div class="col-md-3 node_weight_hide">
                                        <input type="number" name="nodes_weight[]" class="form-control node_weight" max="10000000" required>
                                    </div>
                                </div>
                            </div>
                            <div class="row" style="margin-top:20px">
                                <div class="col-md-12 text-center">
                                    <button type="button" class="btn btn-outline btn-sm btn-round" onclick="add_new_material(this)">
                                        <i class="fa fa-plus"></i>
                                        Добавить узел
                                    </button>
                                </div>
                            </div>
                        </section>
                    </div>
                </form>

                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Закрыть</button>
                </div>

            </div>
        </div>
    </div>

        <!-- система крепления -->
        <div class="modal fade bd-example-modal-lg show" id="calc-mounts" area-hidden="true" role="dialog" aria-labelledby="modal-search" style="display: none;">
            <div class="modal-dialog modal-lg" role="document">
                <form id="form_mount_calc" action="{{ route('projects::work_volume::create_mount_calc', $work_volume->id) }}" method="post">
                    @csrf
                    <div>
                        <h3>Обвязочная балка</h3>
                        <section>
                            <div class="col-12">
                                <div class="row">
                                    <div class="checkbox-radios">
                                        <div class="form-check form-check-radio">
                                            <label class="form-check-label">
                                                <input class="form-check-input" type="radio" name="is_out" value="0" checked>
                                                <span class="form-check-sign"></span>
                                                Конструктивный
                                            </label>
                                        </div>
                                        <div class="form-check form-check-radio">
                                            <label class="form-check-label">
                                                <input class="form-check-input" type="radio" name="is_out" value="1">
                                                <span class="form-check-sign"></span>
                                                Извлекаемый
                                            </label>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <hr>
                            <div class="col-12">
                                <div class="material_place first_material">
                                    <div class="row">
                                        <div class="col-md-3">
                                            <label for="">Тип балки</label>
                                        </div>
                                        <div class="col-md-8" style="width:86%; padding-right:0px">
                                            <select name="strapping_beam[]" style="width:100%;" onchange="calc_change_mat(this)" class="selectpicker material-type select_beam" ajax_material_find="get_beam" data-title="Выберите тип балки" data-style="btn-default btn-outline" data-menu-style="dropdown-blue">
                                            </select>
                                        </div>
                                        <div class="col-1" style="width:10%;padding-top:6px">
                                            <button rel="tooltip" type="button" class="btn-danger btn-link btn padding-actions mn-0" onclick="remove_material_place(this)" data-original-title="Удалить" style="margin top:6px">
                                                <i class="fa fa-times"></i>
                                            </button>
                                        </div>
                                    </div>
                                    <br>
                                    <div class="row amount" style="display:none">
                                        <div class="col-md-3">
                                            <label for="">Кол-во, м.п.<star class="star">*</star></label>
                                        </div>
                                        <div class="col-md-3">
                                            <input type="number" name="strapping_beam_count[]" class="form-control mat_length" min="0.001" step="0.001" max="10000000" required>
                                        </div>
                                        <div class="col-md-2">
                                            <label for="">Кол-во, т<star class="star">*</star></label>
                                        </div>
                                        <div class="col-md-3">
                                            <input type="number" name="strapping_beam_count_weight[]" class="form-control mat_weight mat_beam" min="0.001" step="0.001" max="10000000" required>
                                        </div>
                                    </div>
                                </div>
                                <div class="row" style="margin-top:20px">
                                    <div class="col-md-12 text-center">
                                        <button type="button" class="btn btn-outline btn-sm btn-round" onclick="add_new_material(this)">
                                            <i class="fa fa-plus"></i>
                                            Добавить балку
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </section>
                        <h3>Угловые распоры</h3>
                        <section>
                            <div class="material_place first_material">
                                <div class="row">
                                    <div class="col-md-3">
                                        <label for="">Тип трубы</label>
                                    </div>
                                    <div class="col-md-8" style="width:86%; padding-right:0px">
                                        <select name="corner_strut[]" style="width:100%;" class="selectpicker material-type select_pipe" onchange="calc_change_mat(this)" ajax_material_find="get_pipe" data-title="Выберите тип трубы" data-style="btn-default btn-outline" data-menu-style="dropdown-blue">
                                        </select>
                                    </div>
                                    <div class="col-1"  style="width:10%;padding-top:6px">
                                        <button rel="tooltip" type="button" class="btn-danger btn-link btn padding-actions mn-0" onclick="remove_material_place(this)" data-original-title="Удалить">
                                            <i class="fa fa-times"></i>
                                        </button>
                                    </div>
                                </div>
                                <br>
                                <div class="amount" style="display:none">
                                    <div class="row">
                                        <div class="col-md-3">
                                            <label for="">Кол-во, шт<star class="star">*</star></label>
                                        </div>
                                        <div class="col-md-3">
                                            <input type="number" name="corner_strut_count[]" class="form-control common_mat_count" min="1" step="1" max="10000000" required>
                                        </div>
                                        <div class="col-md-2">
                                            <label for="">Длина, м.п.<star class="star">*</star></label>
                                        </div>
                                        <div class="col-md-3">
                                            <input type="number" name="corner_strut_length[]" class="form-control mat_length" min="0.001" step="0.001" max="10000000" required>
                                        </div>
                                    </div>
                                    <div class="row" style="margin-top: 8px;">
                                        <div class="col-md-6"></div>
                                        <div class="col-md-2">
                                            <label for="">Тоннаж, т<star class="star">*</star></label>
                                        </div>
                                        <div class="col-md-3">
                                            <input type="number" name="corner_strut_count_weight[]" class="form-control mat_weight" min="0.001" step="0.001" max="10000000" required>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="row" style="margin-top:20px">
                                <div class="col-md-12 text-center">
                                    <button type="button" class="btn btn-outline btn-sm btn-round" onclick="add_new_material(this)">
                                        <i class="fa fa-plus"></i>
                                        Добавить распору
                                    </button>
                                </div>
                            </div>
                        </section>
                        <h3>Поперечные распоры</h3>
                        <section>
                            <div class="material_place first_material">
                                <div class="row">
                                    <div class="col-md-3">
                                        <label for="">Тип трубы</label>
                                    </div>
                                    <div class="col-md-8" style="width:86%; padding-right:0px">
                                        <select name="cross_strut[]" style="width:100%;" class="selectpicker material-type select_pipe" onchange="calc_change_mat(this)" ajax_material_find="get_pipe" data-title="Выберите тип трубы" data-style="btn-default btn-outline" data-menu-style="dropdown-blue">
                                        </select>
                                    </div>
                                    <div class="col-1" style="width:10%;padding-top:6px">
                                        <button rel="tooltip" type="button" class="btn-danger btn-link btn padding-actions mn-0" onclick="remove_material_place(this)" data-original-title="Удалить">
                                            <i class="fa fa-times"></i>
                                        </button>
                                    </div>
                                </div>
                                <br>
                                <div class="amount" style="display:none">
                                    <div class="row">
                                        <div class="col-md-3">
                                            <label for="">Кол-во, шт<star class="star">*</star></label>
                                        </div>
                                        <div class="col-md-3">
                                            <input type="number" name="cross_strut_count[]" class="form-control common_mat_count" min="1" max="10000000" required>
                                        </div>
                                        <div class="col-md-2">
                                            <label for="">Длина, м.п.<star class="star">*</star></label>
                                        </div>
                                        <div class="col-md-3">
                                            <input type="number" name="cross_strut_length[]" class="form-control mat_length" min="0.001" step="0.001" max="10000000" required>
                                        </div>
                                    </div>
                                    <div class="row" style="margin-top: 8px;">
                                        <div class="col-md-6"></div>
                                        <div class="col-md-2">
                                            <label for="">Тоннаж, т<star class="star">*</star></label>
                                        </div>
                                        <div class="col-md-3">
                                            <input type="number" name="cross_strut_count_weight[]" class="form-control mat_weight" min="0.001" step="0.001" max="10000000" required>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="row" style="margin-top:20px">
                                <div class="col-md-12 text-center">
                                    <button type="button" class="btn btn-outline btn-sm btn-round" onclick="add_new_material(this)">
                                        <i class="fa fa-plus"></i>
                                        Добавить распору
                                    </button>
                                </div>
                            </div>
                        </section>
                        <h3>Подкосы</h3>
                        <section>
                            <div class="material_place first_material">
                                <div class="row">
                                    <div class="col-md-3">
                                        <label for="">Тип трубы</label>
                                    </div>
                                    <div class="col-md-8" style="width:86%; padding-right:0px">
                                        <select name="strut[]" style="width:100%;" class="selectpicker material-type select_pipe" onchange="calc_change_mat(this)" ajax_material_find="get_pipe" data-title="Выберите тип трубы" data-style="btn-default btn-outline" data-menu-style="dropdown-blue">
                                        </select>
                                    </div>
                                    <div class="col-1"  style="width:10%;padding-top:6px">
                                        <button rel="tooltip" type="button" class="btn-danger btn-link btn padding-actions mn-0" onclick="remove_material_place(this)" data-original-title="Удалить">
                                            <i class="fa fa-times"></i>
                                        </button>
                                    </div>
                                </div>
                                <br>
                                <div class="amount" style="display:none">
                                    <div class="row">
                                        <div class="col-md-3">
                                            <label for="">Кол-во, шт<star class="star">*</star></label>
                                        </div>
                                        <div class="col-md-3">
                                            <input type="number" name="strut_count[]" class="form-control common_mat_count" min="1" max="10000000" required>
                                        </div>
                                        <div class="col-md-2">
                                            <label for="">Длина, м.п.<star class="star">*</star></label>
                                        </div>
                                        <div class="col-md-3">
                                            <input type="number" name="strut_length[]" class="form-control mat_length" min="0.001" step="0.001" max="10000000" required>
                                        </div>
                                    </div>
                                    <div class="row" style="margin-top: 8px;">
                                        <div class="col-md-6"></div>
                                        <div class="col-md-2">
                                            <label for="">Тоннаж, т<star class="star">*</star></label>
                                        </div>
                                        <div class="col-md-3">
                                            <input type="number" name="strut_count_weight[]" class="form-control mat_weight" min="0.001" step="0.001" max="10000000" required>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="row" style="margin-top:20px">
                                <div class="col-md-12 text-center">
                                    <button type="button" class="btn btn-outline btn-sm btn-round" onclick="add_new_material(this)">
                                        <i class="fa fa-plus"></i>
                                        Добавить подкос
                                    </button>
                                </div>
                            </div>
                        </section>
                        <h3>Закладные детали</h3>
                        <section>
                            <div class="material_place first_material">
                                <div class="row">
                                    <div class="col-md-3">
                                        <label for="">Тип детали</label>
                                    </div>
                                    <div class="col-md-8" style="width:86%; padding-right:0px">
                                        <select name="embedded_parts[]" style="width:100%;" class="selectpicker material-type embedded_parts_select" ajax_material_find="get_detail" data-title="Выберите тип закладной детали" data-style="btn-default btn-outline" data-menu-style="dropdown-blue">
                                        </select>
                                    </div>
                                    <div class="col-1"  style="width:10%;padding-top:6px">
                                        <button rel="tooltip" type="button" class="btn-danger btn-link btn padding-actions mn-0" onclick="remove_material_place(this)" data-original-title="Удалить">
                                            <i class="fa fa-times"></i>
                                        </button>
                                    </div>
                                </div>
                                <br>
                                <div class="row amount" style="display:none">
                                    <div class="col-md-3">
                                        <label for="">Кол-во, шт.<star class="star">*</star></label>
                                    </div>
                                    <div class="col-md-3">
                                        <input type="number" name="embedded_parts_count[]" class="form-control common_nodes_count node_count" min="1" max="10000000" required>
                                    </div>
                                    <div class="col-md-2">
                                        <label for="">Кол-во, т.</label>
                                    </div>
                                    <div class="col-md-3">
                                        <input type="number" name="embedded_parts_weight[]" class="form-control node_weight" max="10000000" required>
                                    </div>
                                </div>
                            </div>
                            <div class="row" style="margin-top:20px">
                                <div class="col-md-12 text-center">
                                    <button type="button" class="btn btn-outline btn-sm btn-round" onclick="add_new_material(this)">
                                        <i class="fa fa-plus"></i>
                                        Добавить деталь
                                    </button>
                                </div>
                            </div>
                        </section>
                        <h3>Стойки</h3>
                        <section>
                            <div class="material_place first_material">
                                <div class="row">
                                    <div class="col-md-3">
                                        <label for="">Тип трубы</label>
                                    </div>
                                    <div class="col-md-8" style="width:86%; padding-right:0px">
                                        <select name="racks[]" style="width:100%;" class="selectpicker material-type select_pipe" onchange="calc_change_mat(this)" ajax_material_find="get_pipe" data-title="Выберите тип трубы" data-style="btn-default btn-outline" data-menu-style="dropdown-blue">
                                        </select>
                                    </div>
                                    <div class="col-1"  style="width:10%;padding-top:6px">
                                        <button rel="tooltip" type="button" class="btn-danger btn-link btn padding-actions mn-0" onclick="remove_material_place(this)" data-original-title="Удалить">
                                            <i class="fa fa-times"></i>
                                        </button>
                                    </div>
                                </div>
                                <br>
                                <div class="amount" style="display:none">
                                    <div class="row">
                                        <div class="col-md-3">
                                            <label for="">Кол-во, шт<star class="star">*</star></label>
                                        </div>
                                        <div class="col-md-3">
                                            <input type="number" name="racks_count[]" class="form-control common_mat_count" min="1" max="10000000" required>
                                        </div>
                                        <div class="col-md-2">
                                            <label for="">Длина, м.п.<star class="star">*</star></label>
                                        </div>
                                        <div class="col-md-3">
                                            <input type="number" name="racks_length[]" onchange="" class="form-control mat_length" min="0.001" step="0.001" max="10000000" required>
                                        </div>
                                    </div>
                                    <div class="row" style="margin-top: 8px;">
                                        <div class="col-md-6"></div>
                                        <div class="col-md-2">
                                            <label for="">Тоннаж, т<star class="star">*</star></label>
                                        </div>
                                        <div class="col-md-3">
                                            <input type="number" name="racks_count_weight[]" class="form-control mat_weight" min="0.001" step="0.001" max="10000000" required>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="row" style="margin-top:20px">
                                <div class="col-md-12 text-center">
                                    <button type="button" class="btn btn-outline btn-sm btn-round" onclick="add_new_material(this)">
                                        <i class="fa fa-plus"></i>
                                        Добавить стойку
                                    </button>
                                </div>
                            </div>
                        </section>
                        <h3>Узлы (оп. стол., лист г/к)</h3>
                        <section>
                            <div class="material_place first_material">
                                <div class="row unit_node">
                                    <div class="col-md-3">
                                        <label for="">Тип узла</label>
                                    </div>
                                    <div class="col-md-8" style="width:86%; padding-right:0px">
                                        <select name="nodes[]" style="width:100%;" class="selectpicker material-type nodes_select" ajax_material_find="get_nodes" data-title="Выберите тип узла" data-style="btn-default btn-outline" data-menu-style="dropdown-blue">
                                        </select>
                                    </div>
                                    <div class="col-1" style="width:10%;padding-top:6px">
                                        <button rel="tooltip" type="button" class="btn-danger btn-link btn padding-actions mn-0" onclick="remove_material_place(this)" data-original-title="Удалить">
                                            <i class="fa fa-times"></i>
                                        </button>
                                    </div>
                                </div>
                                <br>
                                <div class="row amount" style="display:none">
                                    <div class="col-md-3">
                                        <label for="">Кол-во, <span>шт</span>.<star class="star">*</star></label>
                                    </div>
                                    <div class="col-md-3">
                                        <input name="nodes_count[]" type="number" class="form-control common_nodes_count node_count" min="1" max="10000000" required>
                                    </div>
                                    <div class="col-md-2 node_weight_hide">
                                        <label for="">Кол-во, т.</label>
                                    </div>
                                    <div class="col-md-3 node_weight_hide">
                                        <input type="number" name="nodes_weight[]" class="form-control node_weight" max="10000000" required>
                                    </div>
                                </div>
                            </div>
                            <div class="row" style="margin-top:20px">
                                <div class="col-md-12 text-center">
                                    <button type="button" class="btn btn-outline btn-sm btn-round" onclick="add_new_material(this)">
                                        <i class="fa fa-plus"></i>
                                        Добавить узел
                                    </button>
                                </div>
                            </div>
                        </section>
                    </div>
                </form>
            </div>
        </div>

        <div class="modal fade bd-example-modal-lg show" id="calc_composite_pile" role="dialog" aria-labelledby="modal-search" style="display: none;">
            <div class="modal-dialog modal-lg" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Расчет составных свай</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">×</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <hr style="margin-top:0">
                        <div class="card border-0" >
                            <div class="card-body ">
                                <div class="row" style="margin-bottom:30px">
                                    <div class="col-md-12 mr-auto ml-auto">
                                        <form id="form_composite_pile" action="{{ route('projects::work_volume::create_composite_pile', $work_volume->id) }}" method="post">
                                            @csrf
                                            <div class="row">
                                                <div class="col-md-4">
                                                    <label for="">Выберите сваи<star class="star">*</star></label>
                                                </div>
                                                <div class="col-md-8">
                                                    <select name="piles[]" id="get_composite_pile"  style="width:100%;" class="selectpicker" data-title="Выберите сваи" data-style="btn-default btn-outline" data-menu-style="dropdown-blue" required multiple="multiple" onchange="checkSelectCount(this)">
                                                    </select>
                                                </div>
                                            </div>
                                            <div class="row">
                                                <div class="col-md-4">
                                                    <label for="">Выберите количество<star class="star">*</star></label>
                                                </div>
                                                <div class="col-md-8">
                                                    <input id="composite_pile_count" name="count" type="number" class="form-control" min="1" maxlength="10" readonly="readonly">
                                                </div>
                                            </div>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Закрыть</button>
                        <button id="composite_pile_submit" form="form_composite_pile" class="btn btn-primary btn-outline" disabled="disabled">Произвести расчет</button>
                    </div>
                </div>
            </div>
        </div>

    @endif

    @include('projects.work_volume.modules.tongue_calc')

@endsection

@section('js_footer')
    <meta name="csrf-token" content="{{ csrf_token() }}" />

    @vite('resources/js/plugins/jquery.steps.min.js')

    <script type="text/javascript">
        function checkCheckboxes() {
            checkedLength = $('.combine:checked').length;
            if (checkedLength) {
                $('#combineSubmit').removeAttr('disabled');
            } else {
                $('#combineSubmit').attr('disabled', 'disabled');
            }
        }

        $(document).ready(function(){
            $(".up,.down").click(function(){
                var row = $(this).parents(".work:first");
                if(row.prev().hasClass('work')) {
                    if ($(this).is(".up")) {
                        $.ajax({
                            url:"{{ route('projects::work_volume::replace_material') }}",
                            type: 'GET',
                            data: {
                                _token: CSRF_TOKEN,
                                first_work_id: $(row).attr('wvw_id'),
                                second_work_id: $(row.prev()).attr('wvw_id')
                            },
                            dataType: 'JSON',
                            success: function (data) {
                                row.insertBefore(row.prev());
                            }
                        });
                    }
                }

                if(row.next().hasClass('work')){
                    if ($(this).is(".down")) {
                        $.ajax({
                            url:"{{ route('projects::work_volume::replace_material') }}",
                            type: 'GET',
                            data: {
                                _token: CSRF_TOKEN,
                                first_work_id: $(row).attr('wvw_id'),
                                second_work_id: $(row.next()).attr('wvw_id')
                            },
                            dataType: 'JSON',
                            success: function (data) {
                                row.insertAfter(row.next());
                            }
                        });
                    }
                }
            });
        });
    </script>

    @if(Request::has('req'))
        <script>
            $('#view-request' + {{ Request::get('req') }}).modal('show');
        </script>
    @endif

    <script>

        // убрать стиль таблицы hover

        var CSRF_TOKEN = $('meta[name="csrf-token"]').attr('content');
        // показать чекбокс rent
        $('#my_material').click(function(){
            if ($(this).is(':checked')){
                $('#show_rent').show(100);
            } else {
                $('#rent')[0].checked = false;
                $('#show_rent').hide(100);
                $('#rent_time').hide(100);
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

        $(document).ready(function() {
            $('.material-type').change(function() {
                $(this).closest('.material_place').find('.amount').first().show();
            });
        });

        function checkDepth(e)
        {
            if ($('#depth').length != 0) {
                var depth = $('#depth').val();
                if (depth == 0 || !$('#depth')[0].checkValidity()) {
                    swal({
                        title: "Внимание",
                        text: "Укажите верную глубину откопки котлована",
                        type: 'warning',
                        timer: 1500,
                    }).then(function () {
                        $('#depth')[0].reportValidity();
                    });
                } else {
                    $('#solveRequest').modal('show');
                }
            }
        }

        function checkSelectCount(elem)
        {
            var count = $(elem).val().length;
            if (count != 2) {
                $('#get_composite_pile').closest('.col-md-8').addClass('has-error');
                $('#composite_pile_count').attr('readonly', 'readonly');
                $('#composite_pile_submit').attr('disabled', 'disabled');
            } else if (count == 2) {
                $('#get_composite_pile').closest('.col-md-8').removeClass('has-error');
                $('#composite_pile_count').removeAttr('readonly', 'readonly');
                $('#composite_pile_count').attr('required', 'required');
                $('#composite_pile_submit').removeAttr('disabled', 'disabled');
            }
        }

        $(document).ready(function() {
            session = '{{ session()->get('wv') }}';

            if (session != '') {
                swal(
                    'Внимание!',
                    'Заполните объём работ',
                    'warning'
                );
            }
        });

        //Селект принадлежность
        $(document).ready(function() {
            $('form select[name="is_our"]').on("change",function() {
                var val = $(this).val();
                if (val == '1' ) {
                    $('#rent_sale').show();
                } else {
                    $('#rent_sale').hide();
                }
            });
        });

        $(document).ready(function() {
            $('form select[name="is_rent"]').on("change",function() {
                var val = $(this).val();
                if (val == '1' ) {
                    $('#rent_date').show();
                } else {
                    $('#rent_date').hide();
                }
            });
        });


        //Селект вид погружения
        $(document).ready(function() {
            $('#get_angle').on("change",function() {
                if ($(this).val() == 0) {
                    $('#number_angle').hide();
                } else {
                    $('#number_angle').show();
                }
            });
        });



        // добавить узел
        var count_unit = 1;
        $('#add_nodes').click(function(){
            var unitBlock = $('.unit_node').length;
            if(count_unit<10){
                var new_node = $('#create_unit_node').clone().attr('id', '').addClass('unit_node').insertAfter($('.unit_node').last());

                $(new_node).find('select[name="nodes[]"]').first().select2({
                    language: "ru",
                    ajax: {
                        url: '/projects/ajax/get_nodes',
                        dataType: 'json',
                        delay: 250,
                    }
                });
                $(new_node).find('button').first().remove();
                $(new_node).find('.dropdown-menu').first().remove();

                count_unit++;
            }
        });

        // удалить узел
        function remove_unit(element) {
            $(element).closest('.unit').remove();
            count_unit--;
        };
        $('#own').click(function(){
            if ($(this).is(':checked')){
                $('#show_rent_calc').show(100);
            } else {
                $('#show_rent_calc').hide(100);
            }
        });

        // показать чекбокс rent_time
        $('#rent_calc').click(function(){
            if ($(this).is(':checked')){
                $('#rent_time_calc').show(100);
            } else {
                $('#rent_time_calc').hide(100);
            }
        });


    </script>

    @if(session()->get('edited_wv_id', 'default') == $work_volume->id)
        <script>
            var is_tongue = '{{ $work_volume->type ? 0 : 1 }}';
            var wv_id = {{ $work_volume->id }};
            var complect_ids = [];
            function detach_material(e) {
                swal({
                    title: 'Вы уверены?',
                    text: "Этот материал будет удалён из всех связанных с ним работ. Проверьте их количество и сроки",
                    type: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#d33',
                    cancelButtonColor: '#3085d6',
                    cancelButtonText: 'Назад',
                    confirmButtonText: 'Удалить!'
                }).then((result) => {
                    if(result.value) {
                        $.ajax({
                            url:"{{ route('projects::work_volume::detach_material') }}",
                            type: 'POST',
                            data: {
                                _token: CSRF_TOKEN,
                                mat_id: $(e).attr("mat_id"),
                                is_tongue: is_tongue,
                                wv_id: wv_id,
                            },
                            dataType: 'JSON',
                            success: function (data) {
                                $(e).closest('.badge').remove();

                                location.reload()
                            }
                        });
                    }

                })
            }

            function detach_compile(e) {
                swal({
                    title: 'Вы уверены?',
                    text: "Материалы будут разъединены!",
                    type: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#d33',
                    cancelButtonColor: '#3085d6',
                    cancelButtonText: 'Назад',
                    confirmButtonText: 'Разделить'
                }).then((result) => {
                    if(result.value) {
                        $.ajax({
                            url:"{{ route('projects::work_volume::detach_compile', $work_volume->id) }}",
                            type: 'POST',
                            data: {
                                _token: CSRF_TOKEN,
                                complect_id: $(e).attr("complect_id")
                            },
                            dataType: 'JSON',
                            success: function (data) {
                                $(e).closest('.badge').remove();

                                location.reload()
                            }
                        });
                    }
                })

            }


            var attr_new = new Vue({
                el: '#filter_materials',
                data: {
                    attrs: [],
                    categories: {!! $categories !!},
                    curr_unit: '',
                    params: [],
                    filters: [],
                },
                mounted: function() {
                    $('#search_category').selectpicker('refresh');
                },
                methods: {
                    get_attrs: function() {
                        this.attrs = this.categories[$('#search_category').val()].attributes;
                        this.params = [];
                        this.filters = [];
                        this.$nextTick(function () {
                            $('#search_attr').val('');
                            $('#search_attr').selectpicker('refresh');
                            $('#search_value').selectpicker('refresh');
                        })
                    },

                    get_params: function() {
                        $.ajax({
                            url:"{{ route('building::materials::select_attr_value') }}", //SET URL
                            type: 'POST', //CHECK ACTION
                            data: {
                                _token: CSRF_TOKEN,
                                attr_id: $('#search_attr').val(), //SET ATTRS
                                extended: true,
                            },
                            dataType: 'JSON',
                            success: function (data) {
                                attr_new.curr_unit = attr_new.attrs.find(x => x.id == $('#search_attr').val()).unit;
                                attr_new.params = Object.keys(data).map(function(i) {
                                    data[i]['unit'] = attr_new.curr_unit;
                                    return data[i];
                                });
                                attr_new.$nextTick(function () {
                                    $('#search_value').val('');
                                    $('#search_value').selectpicker('refresh');
                                })
                            }
                        });
                    },

                    delete_filter: function(filter_id) {
                        this.filters.splice(filter_id, 1);
                        this.$nextTick(function () {
                            $('#new_material_select').select2('open');
                            $('#new_material_select').select2('close');

                        })
                    },

                    add_filters: function() {
                        var filters_to_add = this.params.filter((param, key) => $('#search_value').val().includes(key.toString()));

                        this.filters = this.filters.concat(filters_to_add);
                        $('#new_material_select').select2('open');
                        $('#new_material_select').select2('close');
                    },

                    filter: function(params) {
                        $('#new_material_select').trigger('change');
                        return {
                            filters: {
                                'values': attr_new.filters,
                                'category': $('#search_category').val() == "" ? 0 : attr_new.categories[$('#search_category').val()].id,
                            },
                            q: params.term
                        };
                    },

                    process_results: function (data) {
                        return data;
                    },

                }
            });



            $('#new_material_select').select2({
                language: "ru",
                ajax: {
                    url: '/projects/ajax/get-material' + '?is_tongue=' + {!! $is_tongue !!},
                    dataType: 'json',
                    data: function(params) {
                        return attr_new.filter(params);
                    },
                    delay: 250,
                    processResults: function(data) {
                        return attr_new.process_results(data);
                    }
                }
            });


            $('#new_material_select').on('select2:select', function (e) {
                if (e.params.data.unit) {
                    $('#new_material_unit_block').show();
                    $('#new_material_unit').val(e.params.data.unit).trigger('change');
                    $('#material_count_input').attr('step', 0.001);
                    $('#material_count_input').attr('min', 0.001);
                    $('#is_node').val(0);
                } else {
                    $('#new_material_unit_block').hide();
                    $('#material_count')[0].innerHTML = 'Количество узлов';
                    $('#material_count_input').attr('step', 1);
                    $('#material_count_input').attr('min', 1);
                    $('#is_node').val(1);
                }
            });

            $('#add_material').select2({
                language: "ru",
                ajax: {
                    url: '/projects/ajax/get-material-work' + '?is_tongue=' + {!! $is_tongue !!} + '?work_volume_id=' + {!! $work_volume->id !!},
                    dataType: 'json',
                    delay: 250,
                    data: function (params) {
                        var material_ids = $('#input_now').find('select[name="material_id[]"]').map(function(idx, elem) {
                            return $(elem).val();
                        }).get();

                        return {
                            material_ids: material_ids,
                            q: params.term
                        };
                    }
                }
            });


            function add_new_work(e) {
                if($('#input_now').length == 0) {
                    var new_e = $('#new_work').clone().attr('id', 'input_now');
                    $(new_e).insertAfter($('.work').last());

                    $('#input_now').find('.js-select-work-first').first().select2({
                        language: "ru",
                        ajax: {
                            url: '/projects/ajax/get-work?is_tongue=' + {!! $is_tongue !!},
                            dataType: 'json',
                            delay: 250,
                            data: function (params) {
                                var material_ids = $('#input_now').find('select[name="material_id[]"]').map(function(idx, elem) {
                                    return $(elem).val();
                                }).get();

                                return {
                                    material_ids: material_ids,
                                    q: params.term,
                                    work_volume_id: {{ $work_volume->id }}
                                };
                            }
                        }
                    });
                    $(e).addClass('d-none');
                    select_all();
                }
            }


            function add_work(e) {
                // var material_ids = $('#input_now').find('select[name="material_id[]"]').map(function(idx, elem) {
                //     return '?material_id=' + $(elem).val();
                // }).get();

                var new_e = $('#new_work_input').clone().attr('id', '').addClass('work_input');

                $(new_e).insertAfter($(e).closest('.row').siblings('.work_input').last());

                    $(new_e).find('.js-select-work').first().select2({
                    language: "ru",
                    ajax: {
                        url: '/projects/ajax/get-work?is_tongue=' + {!! $is_tongue !!},
                        dataType: 'json',
                        delay: 250,
                        data: function (params) {
                            var material_ids = $('#input_now').find('select[name="material_id[]"]').map(function(idx, elem) {
                                return $(elem).val();
                            }).get();

                            var work_ids = $('#input_now').find('select[name="work_id[]"]').map(function(idx, elem) {
                                return $(elem).val();
                            }).get();

                            var work_ids = work_ids.filter(function (el) {
                                return el != '';
                            });

                            return {
                                work_volume_id: {!! $work_volume->id !!},
                                material_ids: material_ids,
                                work_ids: work_ids,
                                q: params.term
                            };
                        }
                    }
                });

                select_all();
            }

            function add_material(e, materials) {
                var titles = $('#input_now').find('select[name="material_id[]"]').map(function(idx, elem) {
                    return $(elem).val();
                }).get();

                var new_e = $('#new_material').clone().attr('id', '').addClass('material_input');
                $(new_e).insertAfter($(e).closest('.row').siblings('.material_input').last());
                $(new_e).find('.js-select-material').first().select2({
                    language: "ru",
                    ajax: {
                        url: '/projects/ajax/get-material-work?work_volume_id=' + {!! $work_volume->id !!},
                        dataType: 'json',
                        delay: 250,
                        data: function (params) {
                            var work_ids = $('#input_now').find('select[name="work_id[]"]').map(function(idx, elem) {
                                return $(elem).val();
                            }).get();

                            var material_ids = $('#input_now').find('select[name="material_id[]"]').map(function(idx, elem) {
                                return $(elem).val();
                            }).get();

                            return {
                                work_ids: work_ids,
                                material_ids: material_ids,
                                is_tongue: {!! $is_tongue !!},
                                q: params.term
                            };
                        }
                    }
                });

                $.each(titles, function(index, item) {
                    $(new_e).find('.js-select-material').first().find("option[value='" + item + "']").attr('disabled', 'disabled')
                });

                select_all()
            }

            function delete_work_input(e)
            {
                $(e).closest('.work_input').remove();
            }

            function delete_material_input(e)
            {
                $(e).closest('.material_input').remove();

                var material_ids = $('#input_now').find('select[name="material_id[]"]').map(function(idx, elem) {
                    return $(elem).val();
                }).get();

                if(material_ids.length == 0) {
                    $elements_count = $('#input_now').find('input[name="work_count[]"]');
                    $elements_term = $('#input_now').find('input[name="work_term[]"]');

                    $.each($elements_count, function(index, item) {
                        //$(item).removeAttr('readonly');
                        $(item).val('');
                    });

                    $.each($elements_term, function(index, item) {
                        $(item).val('');
                    });
                }

                var work_ids = $('#input_now').find('select[name="work_id[]"]').map(function(idx, elem) {
                    return $(elem).val();
                }).get();
            }

            function delete_input_now() {
                $('#input_now').remove();
                $('#addWork').removeClass('d-none');
            }


            function edit_work(e, id) {

                if($('#input_now').length == 0) {
                    var new_e = $('#edit_work').clone().attr('id', 'input_now');
                    $(new_e).insertAfter($(e).closest('.work'));

                    $('#input_now').find('.js-select-work-first').first().select2({
                        language: "ru",
                        ajax: {
                            url: '/projects/ajax/get-work?is_tongue=' + {!! $is_tongue !!},
                            dataType: 'json',
                            delay: 250,
                            data: function (params) {
                                var material_ids = $('#input_now').find('select[name="material_id[]"]').map(function(idx, elem) {
                                    return $(elem).val();
                                }).get();

                                var work_ids = $('#input_now').find('select[name="work_id[]"]').map(function(idx, elem) {
                                    return $(elem).val();
                                }).get();

                                var work_ids = work_ids.filter(function (el) {
                                    return el != '';
                                });

                                return {
                                    work_volume_id: {!! $work_volume->id !!},
                                    material_ids: material_ids,
                                    work_ids: work_ids,
                                    q: params.term
                                };
                            }
                        }
                    });

                    $.ajax({
                        url:'{{ route("projects::work_volume::get_one_work") }}',
                        type: 'POST',
                        data: {
                            _token: CSRF_TOKEN,
                            wv_work_id:  id,
                        },
                        dataType: 'JSON',
                        success: function (data) {
                            // alert(JSON.stringify(data.id));
                            $('#input_now').find('.wv_work_id').first().val(data.id);
                            var new_work = $("<option selected='selected'></option>").val(data.manual.id).text(data.manual.name);
                            $('#input_now').find('.js-select-work-first').first().append(new_work).trigger('change');
                            $('#edit_work_count').val(data.count);
                            $('#edit_work_term').val(data.term);

                            $.each(data.shown_materials, function(index, material) {
                                if(material.combine_id == null) {
                                    var new_e = $('#new_material').clone().attr('id', '').addClass('material_input');
                                    $(new_e).insertAfter($('#input_now .material_input').last());
                                    $(new_e).find('.js-select-material').first().select2({
                                        language: "ru",
                                        ajax: {
                                            url: '/projects/ajax/get-material-work?work_volume_id=' + {!! $work_volume->id !!},
                                            dataType: 'json',
                                            delay: 250,
                                            data: function (params) {
                                                var work_ids = $('#input_now').find('select[name="work_id[]"]').map(function(idx, elem) {
                                                    return $(elem).val();
                                                }).get();

                                                var material_ids = $('#input_now').find('select[name="material_id[]"]').map(function(idx, elem) {
                                                    return $(elem).val();
                                                }).get();

                                                return {
                                                    work_ids: work_ids,
                                                    material_ids: material_ids,
                                                    is_tongue: {!! $is_tongue !!},
                                                    q: params.term
                                                };
                                            }
                                        }
                                    });

                                    var new_option = $("<option selected='selected'></option>").val(material.id)
                                        .text(material.name + '; ' + material.count + ' ' + (material.unit ? material.unit + ';' : ''));

                                    $(new_e).find('.js-select-material').first().attr('need_change', 0).append(new_option).trigger('change')
                                }
                            });
                            var combine_ids = [];

                            $.each(data.shown_materials, function(index, material) {
                                if(material.combine_id != null) {
                                    $.ajax({
                                        url:'{{ route("projects::work_volume::get_pile_name") }}',
                                        type: 'POST',
                                        data: {
                                            _token: CSRF_TOKEN,
                                            combine_id:  material.combine_id,
                                        },
                                        dataType: 'JSON',
                                        success: function (name) {
                                            if(combine_ids.indexOf(material.combine_id) == -1) {
                                                var new_e = $('#new_material').clone().attr('id', '').addClass('material_input');
                                                $(new_e).insertAfter($('#input_now .material_input').last());
                                                $(new_e).find('.js-select-material').first().select2({
                                                    language: "ru",
                                                    ajax: {
                                                        url: '/projects/ajax/get-material-work?work_volume_id=' + {!! $work_volume->id !!},
                                                        dataType: 'json',
                                                        delay: 250,
                                                        data: function (params) {
                                                            var work_ids = $('#input_now').find('select[name="work_id[]"]').map(function(idx, elem) {
                                                                return $(elem).val();
                                                            }).get();

                                                            var material_ids = $('#input_now').find('select[name="material_id[]"]').map(function(idx, elem) {
                                                                return $(elem).val();
                                                            }).get();

                                                            return {
                                                                work_ids: work_ids,
                                                                material_ids: material_ids,
                                                                is_tongue: {!! $is_tongue !!},
                                                                q: params.term
                                                            };
                                                        }
                                                    }
                                                });

                                                var new_option = $("<option selected='selected'></option>").val(material.combine_id)
                                                    .text(name + '; ' + material.count + ' ' + (material.unit ? material.unit + ';' : ''));

                                                $(new_e).find('.js-select-material').first().attr('need_change', 0).append(new_option).trigger('change');

                                                combine_ids.push(material.combine_id);
                                            }
                                        }
                                    });

                                }
                            });

                        }
                    });
                }
                else {
                    // swal
                }

                select_all();
            }

            function delete_work(e, id) {
                swal({
                    title: 'Вы уверены?',
                    text: "Работа будет удалена!",
                    type: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#d33',
                    cancelButtonColor: '#3085d6',
                    cancelButtonText: 'Назад',
                    confirmButtonText: 'Удалить'
                }).then((result) => {
                    if(result.value) {
                        $.ajax({
                            url:'{{ route("projects::work_volume::delete_work") }}',
                            type: 'POST',
                            data: {
                                _token: CSRF_TOKEN,
                                wv_work_id:  id,
                            },
                            dataType: 'JSON',
                            success: function () {
                                $('#input_now').remove();
                                e.closest('.work').remove();

                                location.reload();
                            }
                        });
                    }
                });
            }

            function add_term(e) {
                if ($(e).val().match(/^\d+(\.\d{0,3})*$/)) {
                    $(e).removeClass('is-invalid');
                    var container = $(e).closest('.work_input');

                    /*$.ajax({
                    url:'{{ route("projects::work_volume::get_one_work_manual") }}',
                    type: 'POST',
                    data: {
                        _token: CSRF_TOKEN,
                        wv_work_id:  $(container).find("select[name='work_id[]']").first().val(),
                    },
                    dataType: 'JSON',
                    success: function (data) {
                        $(container).find("input[name='work_term[]']").first().val(Math.ceil(parseInt($(e).val()) / data.unit_per_days));
                    }
                });*/
                } else {
                    $(e).addClass('is-invalid');
                }
            }

            function edit_term(e) {
                if ($(e).val().match(/^\d+$/)) {
                    $(e).removeClass('is-invalid');
                } else {
                    $(e).addClass('is-invalid');
                }
            }

            function add_work_count(e) {
                if ($(e).attr('need_change') == 0) {
                    $(e).removeAttr('need_change');
                }
                else {
                    var material_ids = $('#input_now').find('select[name="material_id[]"]').map(function(idx, elem) {
                        return $(elem).val();
                    }).get();

                    var work_ids = $('#input_now').find('select[name="work_id[]"]').map(function(idx, elem) {
                        return $(elem).val();
                    }).get();

                    $.ajax({
                        url:'{{ route("projects::work_volume::get_work_count") }}',
                        type: 'GET',
                        data: {
                            wv_work_id: work_ids,
                            material_ids: material_ids,
                            work_volume_id: {{ $work_volume->id }},
                        },
                        dataType: 'JSON',
                        success: function (data) {
                            if(data) {
                                $elements = $('#input_now').find('input[name="work_count[]"]');
                                $.each($elements, function(index, item) {
                                    if(!$(item).val()) {
                                        $(item).val(data[index])/*.attr('readonly', 'readonly')*/;
                                    }

                                    var container = $(item).closest('.work_input');
                                    /*$.ajax({
                                        url:'{{ route("projects::work_volume::get_one_work_manual") }}',
                                        type: 'POST',
                                        data: {
                                            _token: CSRF_TOKEN,
                                            wv_work_id:  $(container).find("select[name='work_id[]']").first().val(),
                                        },
                                        dataType: 'JSON',
                                        success: function (data) {
                                            if (data !== null) {
                                                $(container).find("input[name='work_term[]']").first().val(Math.ceil(parseInt($(container).find("input[name='work_count[]']").first().val()) / data.unit_per_days));
                                            }
                                        }
                                    });*/
                                });
                            }
                        }
                    });
                }
            }

        </script>
    @endif


    <script type="text/javascript">
        function show_hide_submit_button() {
            var value = $('#search_value').val();

            if (value.length !== 0) {
                $('#search_button').removeClass('d-none');

                var attr = $('#search_button').attr('disabled');
                if (typeof attr !== typeof undefined && attr !== false) {
                    $('#search_button').removeAttr('disabled');
                }
            } else {
                $('#search_button').addClass('d-none');
            }
        }
    </script>

    <script>
        var form = $("#form_mount_calc");

        form.children("div").steps({
            headerTag: "h3",
            bodyTag: "section",
            transitionEffect: "slideLeft",
            enableAllSteps: true,
            showFinishButtonAlways: true,
            onStepChanging: function (event, currentIndex, newIndex)
            {
                var count_errors = 0;
                $('#calc-mounts').find('.body.current').first().find('.form-control').each(function(index, elem) {
                    if ($(elem).val() == 0 && !$(elem).is(":hidden")) {
                        if ($(elem).hasClass('error')) {
                            count_errors++;
                        }
                        else {
                            count_errors++;
                            $(elem).addClass('error');
                        }
                    }
                });

                return count_errors == 0;
            },
            onFinishing: function (event, currentIndex)
            {
                var count_errors = 0;

                $("#form_mount_calc").find('.form-control').each(function(index, elem) {
                    if ($(elem).val() == 0 && !$(elem).is(":hidden")) {
                        if ($(elem).hasClass('error')) {
                            count_errors++;
                        }
                        else {
                            count_errors++;
                            $(elem).addClass('error');
                        }
                    }
                });

                return count_errors == 0;
            },
            onFinished: function (event, currentIndex)
            {
                $(form).submit();
                $(form).find("input, textarea, select").val("");
                $(form).attr('method', 'get');
                $(form).attr('action', '#');
            },

            labels: {
                cancel: "Отменить",
                current: "Текущий:",
                pagination: "Pagination",
                finish: "Отправить",
                next: "›",
                previous: "‹",
                loading: "Загрузка ..."
            }
        });


        $('.select_beam').select2({
            language: "ru",
            dropdownParent: $("#calc-mounts"),
            ajax: {
                url: '/projects/ajax/get_beam',
                dataType: 'json',
                delay: 250,
            }
        });

        $('.select_pipe').select2({
            language: "ru",
            ajax: {
                url: '/projects/ajax/get_pipe',
                dataType: 'json',
                delay: 250,
            }
        });

        $('.embedded_parts_select').select2({
            language: "ru",
            ajax: {
                url: '/projects/ajax/get_detail',
                dataType: 'json',
                delay: 250,
            }
        });

        $('.nodes_select').select2({
            language: "ru",
            ajax: {
                url: '/projects/ajax/get_nodes',
                dataType: 'json',
                delay: 250,
            }
        });

        $('.nodes_select').on('select2:select', function (e) {
            var data = e.params.data;

            $(this).closest('.material_place').find('.amount').first().find('label').first().find('span').first().html(data.unit);

            if (data.unit == 'шт') {
                $(this).closest('.material_place').find('.node_weight_hide').show();
            }
            else {
                $(this).closest('.material_place').find('.node_weight_hide').hide();
            }

            $(this).attr('name', data.select);
            $(this).closest('.material_place').find('.amount').first().find('input').first().attr('name', data.input);
        });


        @if($is_tongue)
        $input = $('#depth');

        var timeout = null;
        $input.bind('keydown mousewheel', (function (event) {
            // increment/decrement
            if (event.keyCode == 38 || event.originalEvent.wheelDelta / 120 > 0) {
                event.preventDefault();
                if (parseInt($input.val())) {
                    $input.val((parseInt($input.val()) + 1));
                } else {
                    $input.val(0 + 1);
                }
            } else if ((event.keyCode == 40 || event.originalEvent.wheelDelta / 120 < 0) && (parseInt($input.val()) > 1)) {
                event.preventDefault();
                $input.val((parseInt($input.val()) - 1));
            }

            //check values
            clearTimeout(timeout);

            timeout = setTimeout(function () {
                change_depth($input);
            }, 1000);
        }));

        function change_depth(e)
        {
            if ($(e).val().match(/^\d+(\.\d{0,2})*$/) && $(e).val() <= 50) {
                $(e).removeClass('is-invalid');
                $.ajax({
                    url:'{{ route("projects::work_volume::change_depth", $work_volume->id) }}',
                    type: 'POST',
                    data: {
                        _token: CSRF_TOKEN,
                        depth:  $(e).val(),
                    },
                    dataType: 'JSON',
                    success: function (data) {

                    }
                });
            } else {
                $(e).addClass('is-invalid');
                $(e)[0].reportValidity();
            }
        }
        @endif

        function add_new_material(e)
        {
            var new_material = $(e).closest('.row').siblings('.material_place').first();

            var insert_material = $(new_material).clone().addClass('now_material').removeClass('first_material').insertAfter($(e).closest('.row').siblings('.material_place').last());

            var select = $('.now_material').children('.row').first().children('.col-md-8').find('select').first();

            $('.now_material').children('.row').first().children('.col-md-8').empty();

            $(select).removeAttr('data-select2-id').removeAttr('tabindex').removeAttr('aria-hidden');

            var url = $(select).attr('ajax_material_find');

            $(select).appendTo($('.now_material').children('.row').first().children('.col-md-8')).select2({
                language: "ru",
                ajax: {
                    url: '/projects/ajax/' + url,
                    dataType: 'json',
                    delay: 250,
                }
            });

            $('.now_material').find('.amount').first().hide();

            $('.now_material').prepend("<hr>");

            $('.now_material').find('input').each(function(index, elem) {
                $(elem).val(0);
                $(elem).removeClass('error');
            });

            $('.now_material').removeClass('now_material');

            $('.nodes_select').on('select2:select', function (e) {
                var data = e.params.data;

                $(this).closest('.material_place').find('.amount').first().find('label').first().find('span').first().html(data.unit);
                $(this).attr('name', data.select);
                $(this).closest('.material_place').find('.amount').first().find('input').first().attr('name', data.input);
            });

            $(document).ready(function() {
                $('.material-type').change(function() {
                    $(this).closest('.material_place').find('.amount').first().show();
                });
            });

            $('.mat_length').on('change', function () {
                var e = this;

                if ($(this).val() != 0) {
                    $(this).removeClass('error');
                }
                $.ajax({
                    url:'{{ route("projects::work_volume::count_weight") }}',
                    type: 'POST',
                    data: {
                        _token: CSRF_TOKEN,
                        material_length: $(e).val(),
                        mat_id: $(e).closest('.material_place').find('select').first().val(),
                    },
                    dataType: 'JSON',
                    success: function (data) {
                        $(e).closest('.amount').find('.mat_weight').first().val(parseFloat(data.material_value * $(e).val()).toFixed(3)).removeClass('error');
                    }
                });
            });

            $('.mat_weight').on('change', function () {
                var e = this;

                if ($(this).val() != 0) {
                    $(this).removeClass('error');
                }
                $.ajax({
                    url:'{{ route("projects::work_volume::count_weight") }}',
                    type: 'POST',
                    data: {
                        _token: CSRF_TOKEN,
                        material_weigth: $(e).val(),
                        mat_id: $(e).closest('.material_place').find('select').first().val(),
                    },
                    dataType: 'JSON',
                    success: function (data) {
                        $(e).closest('.amount').find('.mat_length').first().val(parseFloat($(e).val() / data.material_value).toFixed(3)).removeClass('error');
                    }
                });
            });

            $('.common_nodes_count').on('change', function () {
                if ($(this).val() != 0) {
                    $(this).removeClass('error');
                }
            });

            $('.common_mat_count').on('change', function () {
                if ($(this).val() != 0) {
                    $(this).removeClass('error');
                }
            });
            $('.node_count, .node_weight').on('change', function () {
                var e = $(this);

                $.ajax({
                    url:'{{ route("projects::work_volume::count_nodes") }}',
                    type: 'POST',
                    data: {
                        _token: CSRF_TOKEN,
                        material_value: $(e).val(),
                        mat_id: $(this).closest('.material_place').find('select').first().val(),
                        is_weight : $(e).hasClass('node_count')
                    },
                    dataType: 'JSON',
                    success: function (data) {
                        if ($(e).hasClass('node_count')) {
                            $(e).closest('.amount').find('.node_weight').first().val(data);
                        }
                        else if ($(e).hasClass('node_weight')) {
                            $(e).closest('.amount').find('.node_count').first().val(data);
                        }
                        // $(e).closest('.amount').find('.mat_weight').first().val(($(e).val() / data.material_value).toFixed(3)).removeClass('error');
                    }
                });
            });

            $('.nodes_select').on('select2:select', function (e) {
                var data = e.params.data;

                $(this).closest('.material_place').find('.amount').first().find('label').first().find('span').first().html(data.unit);

                if (data.unit == 'шт') {
                    $(this).closest('.material_place').find('.node_weight_hide').show();
                }
                else {
                    $(this).closest('.material_place').find('.node_weight_hide').hide();
                }

                $(this).attr('name', data.select);
                $(this).closest('.material_place').find('.amount').first().find('input').first().attr('name', data.input);
            });

            select_all();
        }

        function remove_material_place(e)
        {
            if(!$(e).closest('.material_place').hasClass('first_material')) {
                $(e).closest('.material_place').remove();
            }
            else {
                $(e).closest('.material_place').find('select').first().val('').change();
                $(e).closest('.material_place').find('input').val('');
                $(e).closest('.first_material').find('.amount').first().hide();
            }
        }

        function reject(id, act)
        {
            if (act === 'open') {
                $('.' + id).show();
                $('.but' + id).removeClass('d-none');
                $('.textarea' + id).attr('required', 'required').attr('maxlength', 500);
            } else if (act === 'close') {
                $('.' + id).hide();
                $('.but' + id).removeClass('d-none');
                $('.textarea' + id).removeAttr('required').attr('maxlength');
            }
        }

        $('#get_composite_pile').select2({
            language: "ru",
            maximumSelectionLength: 2,
            ajax: {
                url: '/projects/ajax/get_composite_pile',
                dataType: 'json',
                delay: 250,
                data: function (params) {
                    var material_ids = $('#get_composite_pile').map(function(idx, elem) {
                        return $(elem).val();
                    }).get();

                    return {
                        material_ids: material_ids,
                        q: params.term
                    };
                }
            }
        });

        function clearSession() {
            swal({
                title: 'Отменить редактирование?',
                text: "Все ваши изменения будут удалены!",
                type: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#3085d6',
                cancelButtonText: 'Назад',
                confirmButtonText: 'Отменить'
            }).then((result) => {
                if(result.value) {
                    $.ajax({
                        url:'{{ route("projects::work_volume::stop_edit") }}',
                        type: 'GET',
                        dataType: 'JSON',
                        success: function (data) {
                            location.reload();
                        }
                    });
                }
            });


        }

    </script>

    <script>
    $('.mat_weight').on('change', function () {
        var e = this;

        if ($(this).val() != 0) {
            $(this).removeClass('error');
        }
        $.ajax({
            url:'{{ route("projects::work_volume::count_weight") }}',
            type: 'POST',
            data: {
                _token: CSRF_TOKEN,
                material_weigth: $(e).val(),
                mat_id: $(e).closest('.material_place').find('select').first().val(),
            },
            dataType: 'JSON',
            success: function (data) {
                $(e).closest('.amount').find('.mat_length').first().val(($(e).val() / parseFloat(data.material_value)).toFixed(3)).removeClass('error');
            }
        });
    });

    $('.mat_length').on('change', function () {
        var e = this;

        if ($(this).val() != 0) {
            $(this).removeClass('error');

        }
        $.ajax({
            url:'{{ route("projects::work_volume::count_weight") }}',
            type: 'POST',
            data: {
                _token: CSRF_TOKEN,
                material_length: $(e).val(),
                mat_id: $(e).closest('.material_place').find('select').first().val(),
            },
            dataType: 'JSON',
            success: function (data) {
                $(e).closest('.amount').find('.mat_weight').first().val((parseFloat(data.material_value) * $(e).val()).toFixed(3)).removeClass('error');
            }
        });
    });

    $('.common_nodes_count').on('change', function () {
        if ($(this).val() != 0) {
            $(this).removeClass('error');
        }
    });

    $('.common_mat_count').on('change', function () {
        if ($(this).val() != 0) {
            $(this).removeClass('error');
        }
    });

    function calc_change_mat(e) {
        $(e).closest('.material_place').find('.amount').first().find('.mat_weight').first().val(0);
        $(e).closest('.material_place').find('.amount').first().find('.mat_weight').first().trigger('change');
    }

    $('.js-select-proj-doc').select2({
        language: "ru",
        closeOnSelect: false,
        ajax: {
            url: '/projects/ajax/get_project_documents/' + {{ $work_volume->project_id }},
            dataType: 'json',
            delay: 250,
        }
    });

    $('.node_count, .node_weight').on('change', function () {
        var e = $(this);

        $.ajax({
            url:'{{ route("projects::work_volume::count_nodes") }}',
            type: 'POST',
            data: {
                _token: CSRF_TOKEN,
                material_value: $(e).val(),
                mat_id: $(this).closest('.material_place').find('select').first().val(),
                is_weight : $(e).hasClass('node_count')
            },
            dataType: 'JSON',
            success: function (data) {
                if ($(e).hasClass('node_count')) {
                    $(e).closest('.amount').find('.node_weight').first().val(data);
                }
                else if ($(e).hasClass('node_weight')) {
                    $(e).closest('.amount').find('.node_count').first().val(data);
                }
                // $(e).closest('.amount').find('.mat_weight').first().val(($(e).val() / data.material_value).toFixed(3)).removeClass('error');
            }
        });
    });

    $(document).ready(function(){
        $('input[type="number"]').on('keyup',function(){
            v = parseInt($(this).val());
            min = parseInt($(this).attr('min'));
            max = parseInt($(this).attr('max'));

            /*if (v < min){
                $(this).val(min);
            } else */if (v > max){
                $(this).val(max);
            }
        })
    })

    </script>
<script type="text/javascript">
    var vm = new Vue ({
        el: '#base',
        mounted() {
            const that = this;
            $('.prerendered-date-time').each(function() {
                const date = $(this).text();
                const content = that.isValidDate(date, 'DD.MM.YYYY HH:mm:ss') ? that.weekdayDate(date, 'DD.MM.YYYY HH:mm:ss', 'DD.MM.YYYY dd HH:mm:ss') : '-';
                const innerSpan = $('<span/>', {
                    'class': that.isWeekendDay(date, 'DD.MM.YYYY HH:mm:ss') ? 'weekend-day' : ''
                });
                innerSpan.text(content);
                $(this).html(innerSpan);
            });
            $('.prerendered-date').each(function() {
                const date = $(this).text();
                const content = that.isValidDate(date, 'DD.MM.YYYY') ? that.weekdayDate(date, 'DD.MM.YYYY') : '-';
                const innerSpan = $('<span/>', {
                    'class': that.isWeekendDay(date, 'DD.MM.YYYY') ? 'weekend-day' : ''
                });
                innerSpan.text(content);
                $(this).html(innerSpan);
            });
        },
        methods: {
            isWeekendDay(date, format) {
                return [5, 6].indexOf(moment(date, format).weekday()) !== -1;
            },
            isValidDate(date, format) {
                return moment(date, format).isValid();
            },
            weekdayDate(date, inputFormat, outputFormat) {
                return moment(date, inputFormat).format(outputFormat ? outputFormat : 'DD.MM.YYYY dd');
            },
        }
    })
</script>

@endsection
