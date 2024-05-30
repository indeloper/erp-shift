@extends('layouts.app')

@section('title', 'Работы')

@section('url', route('building::works::index'))

@section('css_top')
    <style>
        @media (min-width: 4000px)  {
            .tooltip {
                left:65px!important;
            }
        }

        @media (min-width: 3600px) and (max-width: 4000px)  {
            .tooltip {
                left:45px!important;
            }
        }
    </style>
@endsection

@section('content')
<div class="row">
    <div class="col-md-12 mobile-card">
        <div class="nav-container" style="margin:0 0 10px 15px">
            <ul class="nav nav-icons" role="tablist">
                <li class="nav-item @if (Request::is('building/works')) show active @endif">
                    <a class="nav-link link-line @if (Request::is('building/works')) active-link-line @endif" href="{{ route('building::works::index', ['deleted' => (Request::get('deleted') ?? false)]) }}">
                        Все работы
                    </a>
                </li>
                @foreach($work_groups as $id => $name)
                    <li class="nav-item @if (Request::is('building/works/type/' . $id)) show active @endif">
                        <a class="nav-link link-line @if (Request::is('building/works/type/' . $id)) active-link-line @endif" href="{{ route('building::works::type', [$id, 'deleted' => (Request::get('deleted') ?? false)]) }}">
                            {{ $name }}
                        </a>
                    </li>
                @endforeach
            </ul>
        </div>
        <div class="card strpied-tabled-with-hover">
            <div class="fixed-table-toolbar toolbar-for-btn">
                <div class="fixed-search">
                    <form>
                        <input class="form-control" type="text" value="{{ Request::get('search') }}" name="search" placeholder="Поиск">
                    </form>
                </div>
                <form method="GET" style="display:inline-block; margin-left: 5px;">
                    <input type="hidden" name="deleted" value="{{ !(Request::get('deleted') ?? false) }}">
                    <button type="submit" class="btn">
                        Показать {{ (!Request::get('deleted') ? 'удаленное' : 'не удаленное') }}

                    </button>
                </form>

                @can('manual_works_edit')
                <div class="pull-right">
                    <button class="btn btn-round btn-outline btn-sm add-btn" data-toggle="modal" data-target="#create-work">
                        <i class="glyphicon fa fa-plus"></i>
                        Добавить работу
                    </button>
                </div>
                @endcan
            </div>
            @if($works->isNotEmpty())
                <div class="table-responsive">
                <table class="table table-hover mobile-table">
                    <thead>
                        <tr>
                            <th class="text-left">Название работы</th>
                            <th class="text-center">Ед. измерения</th>
                            <th class="text-center">Цена за ед., руб.</th>
                            <th class="text-right">Действия</th>
                        </tr>
                    </thead>
                    <tbody>
                    @foreach($works as $n => $work)
                        <tr style="cursor:default" class="href" data-href="{{ route('building::works::card', $work->id) }}">
                            <td data-label="Название работы">{{ $work->name }}</td>
                            <td data-label="Единица измерения" class="text-center">{{ $work->unit }}</td>
                            <td data-label="Цена за единицу, руб." class="text-center">{{ $work->price_per_unit }} ₽</td>
                            <td data-label="" class="text-right actions">
                                @can('manual_works_edit')
                                <div style="min-width:72px;display:inline-block">
                                    <button rel="tooltip" class="btn btn-link btn-xs btn-space copy" data-toggle="modal" data-target="#copy-work" data-original-title="Сделать копию"
                                            description="{{ $work->description }}" work_name="{{ $work->name }}" price_per_unit="{{ $work->price_per_unit }}"
                                            unit="{{ $work->unit }}" nds="{{ $work->nds }}" unit_per_days="{{ $work->unit_per_days }}" work_group="{{ $work->work_group_id }}"
                                            show_materials="{{ $work->show_materials ? true : false }}" work_id="{{ $work->id }}">
                                        <i class="fa fa-clone"></i>
                                    </button>
                                    <button rel="tooltip" class="btn btn-link btn-xs btn-success btn-space edit" data-toggle="modal" data-target="#edit-work" data-original-title="Редактировать"
                                            work_id="{{ $work->id }}" description="{{ $work->description }}" work_name="{{ $work->name }}" price_per_unit="{{ $work->price_per_unit }}"
                                            unit="{{ $work->unit }}" nds="{{ $work->nds }}" unit_per_days="{{ $work->unit_per_days }}" work_group="{{ $work->work_group_id }}"
                                            show_materials="{{ $work->show_materials ? true : false }}">
                                        <i class="fa fa-edit"></i>
                                    </button>
                                </div>
                                @endcan
                                <div style="min-width:72px;display:inline-block">
                                    <button rel="tooltip" class="btn btn-link btn-xs btn-info btn-space see" data-toggle="modal" data-target="#view-work" data-original-title="Просмотр"
                                            description="{{ $work->description }}" work_name="{{ $work->name }}" price_per_unit="{{ $work->price_per_unit }}" unit="{{ $work->unit }}"
                                            nds="{{ $work->nds }}" unit_per_days="{{ $work->unit_per_days }}" work_group="{{ $work->work_group[$work->work_group_id] }}"
                                            show_materials="{{ $work->show_materials ? true : false }}">
                                        <i class="fa fa-eye"></i>
                                    </button>
                                    @if(Auth::user()->can('works_remove'))
                                        @if($work->deleted_at)
                                            <button work_id="{{ $work->id }}" rel="tooltip" class="btn btn-success btn-xs btn-link btn-space restore" data-original-title="Восстановить">
                                                <i class="fa fa-repeat"></i>
                                            </button>
                                        @else
                                            <button work_id="{{ $work->id }}" rel="tooltip" class="btn btn-danger btn-xs btn-link btn-space remove" data-original-title="Удалить">
                                                <i class="fa fa-times"></i>
                                            </button>
                                        @endif
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @endforeach
                    </tbody>
                </table>
            </div>
            @elseif(Request::has('search'))
                <p class="text-center">По вашему запросу ничего не найдено</p>
            @else
                <p class="text-center">В этом разделе пока нет ни одной работы</p>
            @endif
            <div class="col-md-12" style="padding:0; margin-top:20px; margin-left:-2px">
                <div class="right-edge fix-pagination">
                    <div class="page-container">
                        {{ $works->appends(['search' => Request::get('search'), 'deleted' => Request::get('deleted') ?? false])->links() }}
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<!-- Модалки -->
<!-- Создание работы-->
@can('manual_works_edit')
<div class="modal fade bd-example-modal-lg show" id="create-work" role="dialog" aria-labelledby="modal-search" style="display: none;">
    <div class="modal-dialog modal-lg" role="document">
       <div class="modal-content">
           <div class="modal-header">
               <h5 class="modal-title">Создание работы</h5>
               <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                   <span aria-hidden="true">×</span>
               </button>
           </div>
           <div class="modal-body">
               <hr style="margin-top:0">
               <div class="card border-0" >
                   <div class="card-body ">
                       <form id="work_store" class="form-horizontal" action="{{ route('building::works::store') }}" method="post" enctype="multipart/form-data">
                           @csrf
                           <div class="row">
                               <div class="col-md-7">
                                   <div class="form-group">
                                       <label>Название<star class="star">*</star></label>
                                       <input type="text" name="name" placeholder="Укажите название" class="form-control" required maxlength="150">
                                   </div>
                               </div>
                               <div class="col-md-5" style="margin-top:35px">
                                   <div class="form-check">
                                       <label class="form-check-label" style="text-transform: initial">
                                           <input class="form-check-input" name="show_materials" type="checkbox" value="1">
                                           <span class="form-check-sign"></span>
                                           Прописывать задействованные материалы
                                       </label>
                                   </div>
                               </div>
                           </div>
                           <div class="row">
                               <div class="col-sm-12">
                                   <div class="form-group">
                                       <label>Описание</label>
                                       <textarea class="form-control textarea-rows" name="description" maxlength="200"></textarea>
                                   </div>
                               </div>
                           </div>
                           <div class="row">
                               <div class="col-sm-6">
                                   <div class="form-group">
                                       <label>Цена за ед. работы, руб<star class="star">*</star></label>
                                       <div class="form-group">
                                           <input name="price_per_unit" type="text" placeholder="Стоимость работы" class="form-control" required maxlength="20">
                                       </div>
                                   </div>
                               </div>
                               <div class="col-sm-6">
                                   <div class="form-group">
                                       <label>Дневная норма выполнения<star class="star">*</star></label>
                                       <div class="form-group">
                                           <input name="unit_per_days" type="text" placeholder="Срок исполнения работы" class="form-control" required maxlength="5">
                                       </div>
                                   </div>
                               </div>
                           </div>
                           <div class="row">
                               <div class="col-sm-3">
                                   <div class="form-group">
                                       <label>Единица измерения<star class="star">*</star></label>
                                       <div class="form-group">
                                           <select name="unit" class="selectpicker" data-title="Ед.измерения" data-style="btn-default btn-outline" data-menu-style="dropdown-blue" required>
                                               <option>шт</option>
                                               <option>м.п</option>
                                               <option>м<sup>2</sup></option>
                                               <option>м<sup>3</sup></option>
                                               <option>т</option>
                                               <option>ед</option>
                                               <option>смена</option>
                                           </select>
                                       </div>
                                   </div>
                               </div>
                               <div class="col-sm-3">
                                   <div class="form-group">
                                       <label>НДС<star class="star">*</star></label>
                                       <div class="form-group">
                                           <select name="nds" class="selectpicker" data-title="Укажите НДС" data-style="btn-default btn-outline" data-menu-style="dropdown-blue" required>
                                               <option value="0">0</option>
                                               <option value="10">10%</option>
                                               <option value="20">20%</option>
                                           </select>
                                       </div>
                                   </div>
                               </div>
                               <div class="col-sm-6">
                                   <div class="form-group">
                                       <label>Группа работ<star class="star">*</star></label>
                                       <div class="form-group">
                                           <select name="work_group" class="selectpicker" data-title="Выберите группу работ" data-style="btn-default btn-outline" data-menu-style="dropdown-blue" required>
                                               @foreach($work_groups as $id => $name)
                                                   <option value="{{$id}}" @if (Request::is('building/works/type/' . $id)) selected="selected" @endif>{{ $name }}</option>
                                               @endforeach
                                           </select>
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
               <button type="submit" form="work_store" class="btn btn-info">Сохранить</button>
          </div>
       </div>
    </div>
</div>
@endcan
@can('manual_works_edit')
<!-- Редактирование работы-->
<div class="modal fade bd-example-modal-lg show" id="edit-work" role="dialog" aria-labelledby="modal-search" style="display: none;">
    <div class="modal-dialog modal-lg" role="document">
       <div class="modal-content">
           <div class="modal-header">
               <h5 class="modal-title">Редактирование работы</h5>
               <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                   <span aria-hidden="true">×</span>
               </button>
           </div>
           <div class="modal-body">
               <hr style="margin-top:0">
               <div class="card border-0" >
                   <div class="card-body ">
                       <form id="work_edit" class="form-horizontal" action="{{ route('building::works::update') }}" method="post" enctype="multipart/form-data">
                           @csrf
                           <input type="hidden" id="work_id" name="work_id">
                           <div class="row">
                               <div class="col-md-7">
                                   <div class="form-group">
                                       <label>Название<star class="star">*</star></label>
                                       <input id="edit_name" type="text" name="name" placeholder="Укажите название" class="form-control" required maxlength="150">
                                   </div>
                               </div>
                               <div class="col-md-5" style="margin-top:35px">
                                   <div class="form-check">
                                       <label class="form-check-label" style="text-transform: initial">
                                           <input id="edit_show_materials" class="form-check-input" name="show_materials" type="checkbox" value="1">
                                           <span class="form-check-sign"></span>
                                           Прописывать задействованные материалы
                                       </label>
                                   </div>
                               </div>
                           </div>
                           <div class="row">
                               <div class="col-sm-12">
                                   <div class="form-group">
                                       <label>Описание</label>
                                       <textarea id="edit_description" class="form-control textarea-rows" name="description"  maxlength="200"></textarea>
                                   </div>
                               </div>
                           </div>
                           <div class="row">
                               <div class="col-sm-6">
                                   <div class="form-group">
                                       <label>Цена за единицу<star class="star">*</star></label>
                                       <div class="form-group">
                                           <input id="edit_price_per_unit" name="price_per_unit" type="text" placeholder="Стоимость работы" class="form-control" required maxlength="20">
                                       </div>
                                   </div>
                               </div>
                               <div class="col-sm-6">
                                   <div class="form-group">
                                       <label>Дневная норма выполнения<star class="star">*</star></label>
                                       <div class="form-group">
                                           <input id="edit_unit_per_days" name="unit_per_days" type="text" placeholder="Срок исполнения работы" class="form-control" required maxlength="5">
                                       </div>
                                   </div>
                               </div>
                           </div>
                           <div class="row">
                               <div class="col-sm-3">
                                   <div class="form-group">
                                       <label>Единица измерения<star class="star">*</star></label>
                                       <div class="form-group">
                                           <select id="edit_unit" name="unit" class="selectpicker" data-title="Ед.измерения" data-style="btn-default btn-outline" data-menu-style="dropdown-blue" required>
                                               <option>шт</option>
                                               <option>м.п</option>
                                               <option>м<sup>2</sup></option>
                                               <option>м<sup>3</sup></option>
                                               <option>т</option>
                                               <option>ед</option>
                                               <option>смена</option>
                                           </select>
                                       </div>
                                   </div>
                               </div>
                               <div class="col-sm-3">
                                   <div class="form-group">
                                       <label>НДС<star class="star">*</star></label>
                                       <div class="form-group">
                                           <select id="edit_nds" name="nds" class="selectpicker" data-title="Укажите НДС" data-style="btn-default btn-outline" data-menu-style="dropdown-blue" required>
                                               <option value="0">0</option>
                                               <option value="10">10%</option>
                                               <option value="20">20%</option>
                                           </select>
                                       </div>
                                   </div>
                               </div>
                               <div class="col-sm-6">
                                   <div class="form-group">
                                       <label>Группа работ<star class="star">*</star></label>
                                       <div class="form-group">
                                           <select id="edit_work_group" name="work_group" class="selectpicker" data-title="Выберите группу работ" data-style="btn-default btn-outline" data-menu-style="dropdown-blue" required>
                                               @foreach($work_groups as $id => $name)
                                                   <option value="{{$id}}">{{ $name }}</option>
                                               @endforeach
                                           </select>
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
               <button type="submit" form="work_edit" class="btn btn-info">Сохранить</button>
          </div>
       </div>
    </div>
</div>
@endcan

<!-- Просмотр работы-->
<div class="modal fade bd-example-modal-lg show" id="view-work" role="dialog" aria-labelledby="modal-search" style="display: none;">
    <div class="modal-dialog modal-lg" role="document">
       <div class="modal-content">
           <div class="modal-header">
               <h5 class="modal-title" id="name"></h5>
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
                                   <p id="show_materials">Задействованные материалы прописываются</p>
                               </div>
                           </div>
                       </div>
                       <div class="row">
                           <div class="col-sm-12">
                               <div class="form-group">
                                   <label>Описание</label>
                                   <p id="description" class="form-control-static" style="font-size:14px">Отсутствует</p>
                               </div>
                           </div>
                       </div>
                       <div class="row" style="margin-top:30px">
                           <div class="col-md-12">
                               <div class="form-group mn-0">
                                    <div class="row">
                                        <label class="col-sm-5 mn-0">Цена за единицу, руб</label>
                                        <div class="col-sm-3">
                                            <p class="form-control-static pd-0 mn-0" id="price_per_unit"></p>
                                        </div>
                                    </div>
                                </div>
                           </div>
                       </div>
                       <hr>
                       <div class="row">
                           <div class="col-md-12">
                               <div class="form-group mn-0">
                                    <div class="row">
                                        <label class="col-sm-5 mn-0">Единица измерения</label>
                                        <div class="col-sm-3">
                                            <p class="form-control-static pd-0 mn-0" id="unit"></p>
                                        </div>
                                    </div>
                                </div>
                           </div>
                       </div>
                       <hr>
                       <div class="row">
                           <div class="col-md-12">
                               <div class="form-group mn-0">
                                    <div class="row">
                                        <label class="col-sm-5 mn-0">НДС, %</label>
                                        <div class="col-sm-3">
                                            <p class="form-control-static pd-0 mn-0" id="nds"></p>
                                        </div>
                                    </div>
                                </div>
                           </div>
                       </div>
                       <hr>
                       <div class="row">
                           <div class="col-md-12">
                               <div class="form-group mn-0">
                                    <div class="row">
                                        <label class="col-sm-5 mn-0">Срок исполнения за единицу, дней</label>
                                        <div class="col-sm-3">
                                            <p class="form-control-static pd-0 mn-0" id="unit_per_days"></p>
                                        </div>
                                    </div>
                                </div>
                           </div>
                       </div>
                       <hr>
                       <div class="row">
                           <div class="col-md-12">
                               <div class="form-group mn-0">
                                    <div class="row">
                                        <label class="col-sm-5 mn-0">Группа работ</label>
                                        <div class="col-sm-3">
                                            <p class="form-control-static pd-0 mn-0" id="work_group"></p>
                                        </div>
                                    </div>
                                </div>
                           </div>
                       </div>

                       <!-- <div class="row">
                            <div class="col-md-12">
                                <div class="table-full-width">
                                    <table class="table">
                                        <thead>
                                            <tr>
                                                <th class="text-left">Цена за единицу, руб</th>
                                                <th>Единица измерения</th>
                                                <th class="text-center">НДС, %</th>
                                                <th class="text-center">Дневная норма выполнения</th>
                                                <th >Группа работ</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <tr>
                                                <td class="text-left" id="price_per_unit"></td>
                                                <td id="unit"></td>
                                                <td class="text-center" id="nds"></td>
                                                <td class="text-center" id="unit_per_days"></td>
                                                <td id="work_group"></td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div> -->
                   </div>
               </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Закрыть</button>
           </div>
        </div>
    </div>
</div>

@can('manual_works_edit')
<!-- Копирование работы-->
<div class="modal fade bd-example-modal-lg show" id="copy-work" role="dialog" aria-labelledby="modal-search" style="display: none;">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Копирование работы</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">×</span>
                </button>
            </div>
            <div class="modal-body">
                <hr style="margin-top:0">
                <div class="card border-0" >
                    <div class="card-body ">
                        <form id="work_copy" class="form-horizontal" action="{{ route('building::works::store') }}" method="post" enctype="multipart/form-data">
                            @csrf
                            <input type="hidden" name="copy" value="true">
                            <input id="copy_id" type="hidden" name="copy_id">
                            <div class="row">
                                <div class="col-md-7">
                                    <div class="form-group">
                                        <label>Название<star class="star">*</star></label>
                                        <input id="copy_name" type="text" name="name" placeholder="Укажите название" class="form-control" required maxlength="150">
                                    </div>
                                </div>
                                <div class="col-md-5" style="margin-top:35px">
                                    <div class="form-check">
                                        <label class="form-check-label" style="text-transform: initial">
                                            <input id="copy_show_materials" class="form-check-input" name="show_materials" type="checkbox" value="1">
                                            <span class="form-check-sign"></span>
                                            Прописывать задействованные материалы
                                        </label>
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-sm-12">
                                    <div class="form-group">
                                        <label>Описание</label>
                                        <textarea id="copy_description" class="form-control textarea-rows" name="description"  maxlength="200"></textarea>
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-sm-6">
                                    <div class="form-group">
                                        <label>Цена за единицу<star class="star">*</star></label>
                                        <div class="form-group">
                                            <input id="copy_price_per_unit" name="price_per_unit" type="text" placeholder="Стоимость работы" class="form-control" required maxlength="20">
                                        </div>
                                    </div>
                                </div>
                                <div class="col-sm-6">
                                    <div class="form-group">
                                        <label>Дневная норма выполнения<star class="star">*</star></label>
                                        <div class="form-group">
                                            <input id="copy_unit_per_days" name="unit_per_days" type="text" placeholder="Срок исполнения работы" class="form-control" required maxlength="5">
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-sm-3">
                                    <div class="form-group">
                                        <label>Единица измерения<star class="star">*</star></label>
                                        <div class="form-group">
                                            <select id="copy_unit" name="unit" class="selectpicker" data-title="Ед.измерения" data-style="btn-default btn-outline" data-menu-style="dropdown-blue" required>
                                                <option>шт</option>
                                                <option>м.п</option>
                                                <option>м<sup>2</sup></option>
                                                <option>м<sup>3</sup></option>
                                                <option>т</option>
                                                <option>ед</option>
                                                <option>смена</option>
                                            </select>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-sm-3">
                                    <div class="form-group">
                                        <label>НДС<star class="star">*</star></label>
                                        <div class="form-group">
                                            <select id="copy_nds" name="nds" class="selectpicker" data-title="Укажите НДС" data-style="btn-default btn-outline" data-menu-style="dropdown-blue" required>
                                                <option value="0">0</option>
                                                <option value="10">10%</option>
                                                <option value="20">20%</option>
                                            </select>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-sm-6">
                                    <div class="form-group">
                                        <label>Группа работ<star class="star">*</star></label>
                                        <div class="form-group">
                                            <select id="copy_work_group" name="work_group" class="selectpicker" data-title="Выберите группу работ" data-style="btn-default btn-outline" data-menu-style="dropdown-blue" required>
                                                @foreach($work_groups as $id => $name)
                                                    <option value="{{$id}}">{{ $name }}</option>
                                                @endforeach
                                            </select>
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
                <button type="submit" form="work_copy" class="btn btn-info">Сохранить</button>
            </div>
        </div>
    </div>
</div>
@endcan
<meta name="csrf-token" content="{{ csrf_token() }}" />
@endsection

@section('js_footer')

<script>
        @if(Auth::user()->can('works_remove'))
        var CSRF_TOKEN = $('meta[name="csrf-token"]').attr('content');

        $('.remove').click(function () {
            var e = this;
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
                        url:"{{ route('building::works::delete') }}",
                        type: 'POST',
                        data: {
                            _token: CSRF_TOKEN,
                            id: $(this).attr("work_id")
                        },
                        dataType: 'JSON',
                        success: function () {
                            e.closest('tr').remove();
                        }
                    });
                }
            })
        });

        $('.restore').click(function () {
            var e = this;
            swal({
                title: 'Вы уверены?',
                text: "Работа будет восстановлена!",
                type: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#3085d6',
                cancelButtonColor: '#d33',
                cancelButtonText: 'Назад',
                confirmButtonText: 'Восстановить'
            }).then((result) => {
                if(result.value) {
                    $.ajax({
                        url:"{{ route('building::works::restore') }}",
                        type: 'POST',
                        data: {
                            _token: CSRF_TOKEN,
                            id: $(this).attr("work_id")
                        },
                        dataType: 'JSON',
                        success: function () {
                            e.closest('tr').remove();
                        }
                    });
                }
            })
        });
    @endif

    $('.see').click(function() {
        var name = $(this).attr('work_name');
        var description = $(this).attr('description');
        var price_per_unit = $(this).attr('price_per_unit');
        var unit = $(this).attr('unit');
        var nds = $(this).attr('nds');
        var unit_per_days = $(this).attr('unit_per_days');
        var work_group_id = $(this).attr('work_group');

        $("#name").html(name);
        if (description != '') {
            $("#description").html(description);
        }
        $("#show_materials")[0].innerHTML = $(this).attr('show_materials') ? 'Задействованные материалы прописываются' : 'Задействованные материалы не прописываются';
        $("#price_per_unit").html(price_per_unit);
        $("#unit").html(unit);
        $("#nds").html(nds);
        $("#unit_per_days").html(unit_per_days);
        $("#work_group").html(work_group_id);
    });

    @can('manual_works_edit')
        $('.edit').click(function() {
            var work_id = $(this).attr('work_id');
            var name = $(this).attr('work_name');
            var description = $(this).attr('description');
            var price_per_unit = $(this).attr('price_per_unit');
            var unit = $(this).attr('unit');
            var nds = $(this).attr('nds');
            var unit_per_days = $(this).attr('unit_per_days');
            var work_group = $(this).attr('work_group');

            $('#work_id').val(work_id);
            $("#edit_name").val(name);
            $("#edit_show_materials")[0].checked = $(this).attr('show_materials');
            $("#edit_description").html(description);
            $("#edit_price_per_unit").val(price_per_unit);
            $("#edit_unit_per_days").val(unit_per_days);

            $('#edit_work_group').val(work_group);
            $('#edit_work_group').selectpicker('render');

            $('#edit_nds').val(nds);
            $('#edit_nds').selectpicker('render');

            $('#edit_unit').val(unit);
            $('#edit_unit').selectpicker('render');
        });
    @endcan

    @can('manual_works_edit')
    $('.copy').click(function() {
        var id = $(this).attr('work_id');
        var name = $(this).attr('work_name');
        var description = $(this).attr('description');
        var price_per_unit = $(this).attr('price_per_unit');
        var unit = $(this).attr('unit');
        var nds = $(this).attr('nds');
        var unit_per_days = $(this).attr('unit_per_days');
        var work_group = $(this).attr('work_group');

        $("#copy_id").val(id);
        $("#copy_name").val(name);
        $("#copy_show_materials")[0].checked = $(this).attr('show_materials');
        $("#copy_description").html(description);
        $("#copy_price_per_unit").val(price_per_unit);
        $("#copy_unit_per_days").val(unit_per_days);

        $('#copy_work_group').val(work_group);
        $('#copy_work_group').selectpicker('render');

        $('#copy_nds').val(nds);
        $('#copy_nds').selectpicker('render');

        $('#copy_unit').val(unit);
        $('#copy_unit').selectpicker('render');
    });
    @endcan

    function pagination (){
        if(screen.width<=769){
            if($('.pagination .page-item').length > 7){
                $('.pagination .dot').remove();
                first = $('.pagination .page-item:first-child');
                last = $('.pagination .page-item:last-child');
                active = $('.pagination .page-item.active');

                $('.pagination .page-item').addClass('d-none');
                $(first).removeClass('d-none');
                $(last).removeClass('d-none');
                $(active).removeClass('d-none');
                $(first).next().removeClass('d-none');
                $(last).prev().removeClass('d-none');

                $(active).next().removeClass('d-none');
                $(active).prev().removeClass('d-none');

                if($(first).nextAll(':lt(2)').hasClass('d-none')){
                    $('<span class="dot" style="padding-top:5px">...</span>').insertBefore($(active).prev());
                }

                if($(last).prevAll(':lt(2)').hasClass('d-none')){
                    $('<span class="dot" style="padding-top:5px">...</span>').insertAfter($(active).next());
                }
            }
            return true;
        } else {
            return false;
        }
    };

    $(document).ready(function(){
        if(screen.width<=769){
            pagination ();
        }
    });

    $(window).resize(function(){
        if(screen.width<=769){
            if($('.pagination .page-item').length > 7){
                pagination ();
            }
        } else {
            $('.pagination .page-item').removeClass('d-none');
            $('.pagination .dot').remove();
        }
    });
</script>
@endsection
