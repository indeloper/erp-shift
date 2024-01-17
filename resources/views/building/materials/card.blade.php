@extends('layouts.app')

@section('title', $category->name)

@section('url', route('building::materials::card', $category->id))

@section('content')
<div class="row">
    <div class="col-md-12">
        <div aria-label="breadcrumb" role="navigation">
            <ol class="breadcrumb">
                <li class="breadcrumb-item">
                    <a href="{{ route('building::materials::index') }}" class="table-link">Категории материалов</a>
                </li>
                <li class="breadcrumb-item active" aria-current="page">{{ $category->name }}</li>
            </ol>
        </div>
    </div>
</div>
<div class="row">
    <div class="col-md-12">
        <div class="card" id="attr_search">
            <div class="card-header">
                <h6 style="font-weight:400;">
                    Поиск по атрибутам
                </h6>
            </div>
            <div class="card-body">
                <form id="search_by_attrs">
                    <div class="row">
                        <div class="col-md-7">
                            <div class="form-group">
                                <select class="selectpicker" id="search_attr" name="select_attr" data-title="Выберите атрибут" data-style="btn-default btn-outline" data-menu-style="dropdown-blue">
                                    @foreach($category->attributes as $attribute)
                                    <option value="{{ $attribute->id }}">{{ $attribute->name }}{{ $attribute->unit ? ', ' . $attribute->unit : '' }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <select class="selectpicker" id="search_value" name="select_value[]" data-title="Выберите значение" data-style="btn-default btn-outline" data-menu-style="dropdown-blue" multiple onchange="show_hide_submit_button()" disabled>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-1 text-center" style="padding:0;">
                            <div class="form-group">
                                <button id="search_button" type="submit" class="btn btn-check btn-link d-none">
                                    <i class="pe-7s-plus"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                </form>
                <div class="row">
                    <div class="col-md-12">
                        <div class="bootstrap-tagsinput">
                            <div id="parameters"></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<div class="row">
    <div class="col-md-12">
        <div class="card strpied-tabled-with-hover">
            <div class="fixed-table-toolbar toolbar-for-btn">
                <div class="fixed-search">
                    <form action="{{ route('building::materials::card', $category->id) }}">
                        <input class="form-control" type="text" value="{{ Request::get('search') }}" name="search" placeholder="Поиск">
                        <input  type="hidden" value="{{ Request::get('materials') }}" name="materials">
                        <input  type="hidden" value="{{ Request::get('deleted') }}" name="deleted">

                    </form>
                </div>
                <a style="margin-left:5px;"class="btn" href="{{ route('building::materials::card', [$category->id, 'materials' => !(Request::get('materials') ?? false), 'deleted'=> (Request::get('deleted') ?? false)]) }}">
                    Показать {{ (!Request::get('materials') ? 'материалы' : 'эталоны') }}
                </a>

                <a style="margin-left:5px;"class="btn" href="{{ route('building::materials::card', [$category->id, 'materials' =>   (Request::get('materials') ?? false), 'deleted'=> !(Request::get('deleted') ?? false)]) }}">
                    Показать {{ (!Request::get('deleted') ? 'удаленное' : 'не удаленное') }}
                </a>
                @if(Request::get('materials') == 1)
                <a style="margin-left:5px;"class="btn" href="{{ route('building::materials::card', [$category->id, 'materials' => 1, 'without_ref' => !(Request::get('without_ref') ?? false), 'deleted'=> (Request::get('deleted') ?? false)]) }}">
                    {{ (Request::get('without_ref') ? 'Все' : 'Без эталонов') }}
                </a>
                @endif

                <div class="pull-right">
                    <button class="btn btn-round btn-outline btn-sm add-btn btn-success" id="create-material-reference-button" data-toggle="modal" data-target="#create-material">
                        <i class="glyphicon fa fa-plus"></i>
                        Создать эталон
                    </button>
                </div>
                @if($className != 'ManualReference')
                    <div class="pull-right">
                        <button class="btn btn-round btn-outline btn-sm add-btn btn-success" id="create-material-button" data-toggle="modal" data-target="#create-material">
                            <i class="glyphicon fa fa-plus"></i>
                            Создать экземпляр
                        </button>
                    </div>
                @endif
            </div>
            <div class="table-responsive">
                <table class="table table-hover mobile-table">
                    <thead>
                        <tr style="cursor:default">
                            <th class="text-left">Название</th>
                            @if($className == 'ManualMaterial')
                            <th class="text-center">Цена купли-продажи</th>
                            <th class="text-center">Цена за месяц использования</th>
                            @endif
                            <th class="text-right">Действия</th>
                        </tr>
                    </thead>
                    <tbody id="put_here">
                    @foreach ($materials as $material)
                        <tr style="cursor:default" @if($material->parameters->where('value', null)->first()) class="reject" @endif>
                            <td data-label="Название" class="text-left">{{ $material->name }}</td>
                            @if($className == 'ManualMaterial')
                            <td data-label="Цена купли-продажи" class="text-center">{{ $material->buy_cost }} ₽</td>
                            <td data-label="Цена за месяц использования" class="text-center">{{ $material->use_cost }} ₽</td>
                            @endif
                            <td class="text-right actions">
                                <div style="min-width:72px;display:inline-block">
                                    <button rel="tooltip" onclick="clone_material({{ $material }})" class="btn btn-link btn-xs btn-space" data-toggle="modal" data-target="#clone-material" data-original-title="Сделать копию ">
                                        <i class="fa fa-clone"></i>
                                    </button>
                                    @can('manual_materials_edit')
                                        <button rel="tooltip" onclick="edit_material({{ $material }})" class="btn btn-link btn-xs btn-success btn-space" data-toggle="modal" data-target="#edit-material" data-original-title="Редактировать">
                                            <i class="fa fa-edit"></i>
                                        </button>
                                    @endcan
                                </div>
                                <div style="min-width:72px;display:inline-block">
                                    <button rel="tooltip" onclick="view_material({{ $material }}, {{ json_encode(array_values($category->attributes->where('value', '!=', null)->toArray())) }})" class="btn btn-link btn-xs btn-info btn-space" data-toggle="modal" data-target="#view-material" data-original-title="Просмотр">
                                        <i class="fa fa-eye"></i>
                                    </button>
                                    @if(Auth::user()->can('materials_remove'))
                                        @if($material->deleted_at)
                                            <button rel="tooltip" onclick="restore_material(this, {{ $material->id }})" class="btn btn-link btn-xs btn-success btn-space" data-original-title="Восстановить">
                                                <i class="fa fa-repeat"></i>
                                            </button>
                                        @else
                                            <button rel="tooltip" onclick="delete_material(this, {{ $material->id }})" class="btn btn-danger btn-xs btn-link btn-space" data-toggle="modal" data-target="#" data-original-title="Удалить">
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
        </div>
    </div>
</div>
<div class="col-md-12" id="paganation">
    <div class="right-edge">
        <div class="page-container">
            {{ $materials->appends(['materials' => Request::get('materials') ?? false, 'deleted'=> (Request::get('deleted') ?? false), 'search'=> (Request::get('search')  ?? false)])->links() }}
        </div>
    </div>
</div>

<!-- Модалки -->

<!-- Создание материала-->
<div class="modal fade bd-example-modal-lg show" id="create-material" role="dialog" aria-labelledby="modal-search" style="display: none;">
    <div class="modal-dialog modal-lg" role="document">
       <div class="modal-content">
           <div class="modal-header">
               <h5 class="modal-title">Создание материала</h5>
               <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                   <span aria-hidden="true">×</span>
               </button>
           </div>
           <div class="modal-body">
               <hr style="margin-top:0">
               <div class="card border-0" >
                   <div class="card-body ">
                       <form id="add_material_form" class="form-horizontal" action="{{ route('building::materials::store', $category->id) }}" method="post" enctype="multipart/form-data">
                           @csrf
                           <input type="hidden" name="className" id="createMaterialClassName" value="ManualMaterial">
                           <div class="row">
                               <div class="col-md-12">
                                   <div class="form-group">
                                       <label>Название<star class="star">*</star></label>
                                       <input value="" name="name" placeholder="Укажите название" required class="form-control" maxlength="50">
{{--                                       <p>{{$category->name}}</p>--}}
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
                           @if($className != 'ManualReference')
                           <div class="row">
                               <div class="col-sm-6">
                                   <div class="form-group">
                                       <label>Цена за месяц использования<star class="star">*</star></label>
                                       <input class="form-control" type="number" step="0.01" name="use_cost" min="0" max="999999.99" required>
                                   </div>
                               </div>
                               <div class="col-sm-6">
                                   <div class="form-group">
                                       <label>Цена купли-продажи<star class="star">*</star></label>
                                       <input class="form-control" type="number" step="0.01" name="buy_cost" min="0" max="999999.99" required>
                                   </div>
                               </div>
                               <div class="col-md-6 col-xl-6">
                                   <label for="">"Эталон"</label>
                                   <div class="form-group select-accessible-140">
                                       <select id="reference_select_create" name="manual_reference_id" style="width:100%;">
                                       </select>
                                   </div>
                               </div>
                           </div>
                           @endif
                           <hr>
                           <h6 style="margin:20px 0 10px">Параметры</h6>
                           <div class="row">
                               <div class="col-md-12">
                                   <div class="row">
                                       @foreach ($category->attributes->where('value', null) as $attr)
                                       <div class="col-md-4" style="padding-left:6px">
                                           <div class="form-group">
                                                <label style="text-transform:none; font-size:14px;line-height:1.1; min-height:24px">{{ $attr->name }}, {{ $attr->unit }}
                                                    @if($attr->is_required)
                                                        <star class="star">*</star>
                                                    @endif
                                                </label>
                                                <input name="attrs[{{ $attr->id }}]" @if($attr->step) min="{{ $attr->from }}" max="{{ $attr->to }}" step="{{ $attr->step }}" type="number" @else type="text" @endif  class="form-control" maxlength="100" {{ $attr->is_required && Auth::user()->id != 1 ? 'required': ''}}>
                                            </div>
                                       </div>
                                       @endforeach
                                   </div>
                               </div>
                           </div>
                           <div class="row" >
                               <label class="col-sm-3 col-form-label" for="" style="font-size:0.80">
                                   Паспорт материала
                               </label>
                               <div class="col-sm-6" style="padding-top:0px;">
                                   <div class="file-container">
                                       <div id="fileName" class="file-name"></div>
                                       <div class="file-upload ">
                                           <label class="pull-right">
                                               <span><i class="nc-icon nc-attach-87" style="font-size:25px; line-height: 40px"></i></span>
                                               <input type="file"  name="document" onchange="getFileName(this);" id="uploadedFile">
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
                <button id="" type="submit" form="add_material_form" class="btn btn-info">Сохранить</button>
           </div>
        </div>
    </div>
</div>
@can('manual_materials_edit')
<!-- Редактирование материала-->
<div class="modal fade bd-example-modal-lg show" id="edit-material" role="dialog" aria-labelledby="modal-search" style="display: none;">
    <div class="modal-dialog modal-lg" role="document">
       <div class="modal-content">
           <div class="modal-header">
               <h5 class="modal-title">Редактирование материала</h5>
               <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                   <span aria-hidden="true">×</span>
               </button>
           </div>
           <div class="modal-body">
               <hr style="margin-top:0">
               <div class="card border-0" >
                   <div class="card-body ">
                       <form id="edit_material_form" class="form-horizontal" action="{{ route('building::materials::update', $category->id) }}" method="post" enctype="multipart/form-data">
                           @csrf
                           <input type="hidden" name="className" value="{{ $className }}">
                           <input type="hidden" name="id" id="edit_material_id">
                           <div class="row">
                               <div class="col-md-12">
                                   <div class="form-group">
                                       <label>Название<star class="star">*</star></label>
                                       <input id="edit_material_name" name="name" type="text" placeholder="Укажите название" class="form-control edit-field" maxlength="250" required>
                                    </div>
                               </div>
                           </div>
                           <div class="row">
                               <div class="col-sm-12">
                                   <div class="form-group">
                                       <label>Описание</label>
                                       <textarea id="edit_material_description" class="form-control textarea-rows" name="description" maxlength="200"></textarea>
                                   </div>
                               </div>
                           </div>
                           @if($className != 'ManualReference')
                           <div class="row">
                               <div class="col-sm-6">
                                   <div class="form-group">
                                       <label>Цена за месяц использования<star class="star">*</star></label>
                                       <input id="edit_material_use_cost" class="form-control" type="number" step="0.01" name="use_cost" min="0" max="999999.99" required>
                                   </div>
                               </div>
                               <div class="col-sm-6">
                                   <div class="form-group">
                                       <label>Цена купли-продажи<star class="star">*</star></label>
                                       <input id="edit_material_buy_cost" class="form-control" type="number" step="0.01" name="buy_cost" min="0" max="999999.99" required>
                                   </div>
                               </div>
                               <div class="col-md-6 col-xl-6">
                                   <label for="">"Эталон"</label>
                                   <div class="form-group select-accessible-140">
                                       <select id="reference_select" name="manual_reference_id" style="width:100%;">
                                       </select>
                                   </div>
                               </div>
                           </div>

                           @endif

                           <hr>
                           <h6 style="margin:20px 0 10px">Параметры</h6>
                           <div class="row">
                               <div class="col-md-12">
                                   <div class="row">
                                   @foreach ($category->attributes as $attr)
                                       <div class="col-md-4" style="padding-left:6px">
                                           <div class="form-group">
                                               <label style="text-transform:none; font-size:14px;line-height:1.1; min-height:24px">{{ $attr->name }}, {{ $attr->unit }}
                                                   @if($attr->is_required)
                                                       <star class="star">*</star>
                                                   @endif
                                               </label>
                                               <input id="edit_attr{{ $attr->id }}" name="attrs[{{ $attr->id }}]" type="text" class="form-control edit-field" maxlength="100" placeholder="Не заполнено" {{ $attr->is_required && Auth::user()->id != 1 ?'required': ''}}>
                                           </div>
                                       </div>
                                   @endforeach
                                   </div>
                               </div>
                           </div>
                           <hr>
                           <div class="row" >
                               <label class="col-sm-3 col-form-label" for="" style="font-size:0.80">
                                   Паспорт материала
                               </label>
                               <div class="col-sm-6">
                                   <div class="file-container">
                                       <div id="fileName" class="file-name"></div>
                                       <div class="file-upload">
                                           <label class="pull-right">
                                               <span><i class="nc-icon nc-attach-87" style="font-size:25px; line-height: 40px"></i></span>
                                               <input type="file" name="document" id="uploadedFile1" onchange="getFileName(this);">
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
                <button id="" type="submit" form="edit_material_form" class="btn btn-info">Сохранить</button>
           </div>
        </div>
    </div>
</div>
@endcan
<!-- Клонирование материала-->
<div class="modal fade bd-example-modal-lg show" id="clone-material" role="dialog" aria-labelledby="modal-search" style="display: none;">
    <div class="modal-dialog modal-lg" role="document">
       <div class="modal-content">
           <div class="modal-header">
               <h5 class="modal-title">Копирование материала</h5>
               <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                   <span aria-hidden="true">×</span>
               </button>
           </div>
           <div class="modal-body">
               <hr style="margin-top:0">
               <div class="card border-0" >
                   <div class="card-body ">
                       <form id="clone_material_form" class="form-horizontal" action="{{ route('building::materials::clone', $category->id) }}" method="post" enctype="multipart/form-data">
                           @csrf
                           <input type="hidden" name="className" value="{{ $className }}">
                           <div class="row">
                               <div class="col-md-12">
                                   <div class="form-group">
                                       <label>Название<star class="star">*</star></label>
                                       <input id="clone_material_name" name="name" type="text" placeholder="Укажите название" class="form-control clone-field" maxlength="50" required>
                                    </div>
                               </div>
                           </div>
                           <div class="row">
                               <div class="col-sm-12">
                                   <div class="form-group">
                                       <label>Описание</label>
                                       <textarea id="clone_material_description" class="form-control textarea-rows clone-field" name="description" maxlength="200"></textarea>
                                   </div>
                               </div>
                           </div>
                           @if($className != 'ManualReference')
                           <div class="row">
                               <div class="col-sm-6">
                                   <div class="form-group">
                                       <label>Цена за месяц использования<star class="star">*</star></label>
                                       <input id="clone_material_use_cost" class="form-control" type="number" step="0.01" name="use_cost" min="0" max="999999.99" required>
                                   </div>
                               </div>
                               <div class="col-sm-6">
                                   <div class="form-group">
                                       <label>Цена купли-продажи<star class="star">*</star></label>
                                       <input id="clone_material_buy_cost" class="form-control" type="number" step="0.01" name="buy_cost" min="0" max="999999.99" required>
                                   </div>
                               </div>
                               <div class="col-md-6 col-xl-6">
                                   <label for="">"Эталон"</label>
                                   <div class="form-group select-accessible-140">
                                       <select id="reference_select_copy" name="manual_reference_id" style="width:100%;">
                                       </select>
                                   </div>
                               </div>
                           </div>
                           @endif
                           <hr>
                           <h6 style="margin:20px 0 10px">Параметры</h6>
                           <div class="row">
                               <div class="col-md-12">
                                   <div class="row">
                                   @foreach ($category->attributes as $attr)
                                       <div class="col-md-4" style="padding-left:6px">
                                           <div class="form-group">
                                               <label style="text-transform:none; font-size:14px;line-height:1.1; min-height:24px">{{ $attr->name }}, {{ $attr->unit }}
                                                   @if($attr->is_required)
                                                       <star class="star">*</star>
                                                   @endif
                                               </label>
                                               <input id="clone_attr{{ $attr->id }}" name="attrs[{{ $attr->id }}]" type="text" class="form-control clone-field" maxlength="100" placeholder="Не заполнено" {{ $attr->is_required && Auth::user()->id != 1 ?'required': ''}}>
                                           </div>
                                       </div>
                                   @endforeach
                                   </div>
                               </div>
                           </div>
                           <hr>
                           <div class="row" >
                               <label class="col-sm-3 col-form-label" for="" style="font-size:0.80">
                                   Паспорт материала
                               </label>
                               <div class="col-sm-6">
                                   <div class="file-container">
                                       <div id="fileName" class="file-name"></div>
                                       <div class="file-upload">
                                           <label class="pull-right">
                                               <span><i class="nc-icon nc-attach-87" style="font-size:25px; line-height: 40px"></i></span>
                                               <input type="file" name="document" id="uploadedFile2" onchange="getFileName(this);">
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
                <button id="" type="submit" form="clone_material_form" class="btn btn-info">Сохранить</button>
           </div>
        </div>
    </div>
</div>
<!-- Просмотр материала-->
<div class="modal fade bd-example-modal-lg show" id="view-material" role="dialog" aria-labelledby="modal-search" style="display: none;">
    <div class="modal-dialog modal-lg" role="document">
       <div class="modal-content">
           <div class="modal-header">
               <h5 id="view_material_name" class="modal-title"></h5>
               <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                   <span aria-hidden="true">×</span>
               </button>
           </div>
           <div class="modal-body">
               <hr style="margin-top:0">
               <div class="card border-0" >
                   <div class="card-body ">
                       <div class="row">
                           <div class="col-md-12">
                               <div class="row">
                                    <div class="col-md-12">
                                        <h6 style="font-weight:400">Параметры</h6>
                                        <div class="table-full-width">
                                            <table class="table mobile-table">
                                                <thead>
                                                    <tr>
                                                        <th>Параметр</th>
                                                        <th class="text-center">Значение</th>
                                                        <th class="text-center">Ед.измерения</th>
                                                    </tr>
                                                </thead>
                                                <tbody id="view_material_attributes">
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>
                                </div>
                           </div>
                       </div>
                       <div class="row">
                           <div class="col-sm-12">
                               <div class="form-group">
                                   <label>Описание</label>
                                   <p id="view_material_description" class="form-control-static">Описание материала</p>
                               </div>
                           </div>
                       </div>
                       @if($className != 'ManualReference')
                       <div class="row">
                           <div class="col-sm-6">
                               <div class="form-group">
                                   <label>Цена купли-продажи</label>
                                   <p id="view_material_buy_cost" class="form-control-static">Цена купли-продажи</p>
                               </div>
                           </div>
                           <div class="col-sm-6">
                               <div class="form-group">
                                   <label>Цена за месяц использования</label>
                                   <p id="view_material_use_cost" class="form-control-static">Цена за месяц использования</p>
                               </div>
                           </div>
                           <div class="col-sm-6">
                               <div class="form-group">
                                   <label>"Эталон"</label>
                                   <p id="view_material_reference_name" class="form-control-static">Эталон не прикреплен</p>
                               </div>
                           </div>
                       </div>
                       @endif
                       <div class="row" style="margin-top:20px">
                           <div class="col-sm-12">
                               <div class="form-group">
                                   <label>Паспорт материала</label><br>
                                   <a id="view_material_passport" target="_blank" rel="tooltip" class="btn-default btn-link btn-xs" data-original-title="Просмотр">
                                   </a>
                               </div>
                           </div>
                       </div>
                   </div>
               </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Закрыть</button>
           </div>
        </div>
    </div>
</div>

<!-- Кнопка для параметров поиска -->
<div class="d-none">
    <span class="badge badge-azure">Атрибут: Значение<span data-role="remove" class="badge-remove-link"></span></span>
</div>

<!-- Кнопка для базового URL -->
<button class="d-none" id="url" url="{{ URL::to('/') }}"></button>
@endsection

@section('js_footer')
<meta name="csrf-token" content="{{ csrf_token() }}" />

<script>
    var CSRF_TOKEN = $('meta[name="csrf-token"]').attr('content');
    var fileExtension = ['jpeg', 'jpg', 'png', 'gif', 'bmp', 'doc', 'docx', 'pdf'];
    $('#reference_select').select2();
    $('#reference_select_create').select2();
    $('#reference_select_copy').select2();

    function view_material(e, cat) {
        $('#view_material_attributes').empty();
        $('#view_material_works').empty();

        $('#view_material_name').text(e.name);
        $('#view_material_use_cost').text(e.use_cost);
        $('#view_material_buy_cost').text(e.buy_cost);
        $('#view_material_reference_name').text(e.reference_name);

        if (!e.description) {
            $('#view_material_description').text('Нет описания');
        } else {
            $('#view_material_description').text(e.description);
        }
        attrs = e.parameters;
        attrs.forEach(function(attr, id, attrs) {
            if (attr.value === 'null') {
                attr.value = 'Не задано';
            }
            $('#view_material_attributes').append("<tr>\n" +
                "<td data-label='Параметр'>" + attr.name + "</td>\n" +
                "<td data-label='Значение' class=\"text-center\">"+ attr.value +"</td>\n" +
                "<td data-label='Ед. измерения' class=\"text-center\">"+ attr.unit+"</td>\n" +
                "</tr>");
        });
        cat.forEach(function(attr, id, cat) {
            if (attr.value === 'null') {
                attr.value = 'Не задано';
            }
            $('#view_material_attributes').append("<tr>\n" +
                "<td data-label='Параметр'>" + attr.name + "</td>\n" +
                "<td data-label='Значение' class=\"text-center\">"+ attr.value +"</td>\n" +
                "<td data-label='Ед. измерения' class=\"text-center\">"+ attr.unit+"</td>\n" +
                "</tr>");
        });


        works = e.work_relations;
        if(works.length > 0) {
            $('#accordion').removeClass('d-none');

            works.forEach(function(work, id, works) {
                $('#view_material_works').append("<tr>\n" +
                    "<td>" + work.name + "</td>\n" +
                    "<td class=\"text-center\">" + work.unit + "</td>\n" +
                    "<td class=\"text-center\">" + work.price_per_unit + "</td>\n" +
                    "<td class=\"text-center\">" + work.nds + "</td>\n" +
                    "<td class=\"text-center\">" + work.unit_per_days + "</td>\n" +
                    "</tr>");
            });
        } else {
            $('#accordion').addClass('d-none');
        }

        passport = e.passport;
        var base_url = $('#url').attr('url');
        if (passport) {
            $('#view_material_passport').text(passport.name);
            url = base_url + '/storage/docs/material_passport/' + passport.file_name;
            $('#view_material_passport').attr('href', url);
        } else {
            $('#view_material_passport').text('Отсутствует');
            $('#view_material_passport').removeAttr('data-original-title');
        }
    }

    @can('manual_materials_edit')
        function edit_material(e) {
            $('[class*="edit-field"]').val('');
            $('#edit_material_id').val(e.id);
            $('#edit_material_name').val(e.name);
            $('#edit_material_description').val(e.description);
            $('#edit_material_use_cost').val(e.use_cost);
            $('#edit_material_buy_cost').val(e.buy_cost);

        $('#reference_select').select2('destroy');

        $('#reference_select').select2({
            language: "ru",
            ajax: {
                url: '/building/materials/get_references' + '?category_id=' + {!! $category->id !!},
                dataType: 'json',
                delay: 250,
            }
        });
        if (e.manual_reference_id) {
            var new_option = $("<option selected='selected'></option>").val(e.manual_reference_id).text(e.reference_name);
            $('#reference_select').append(new_option).trigger('change');
        } else {
            $('#reference_select').val('').change();
        }

        attrs = e.parameters;
            attrs.forEach(function(attr, id, attrs) {
                if (attr.value === 'null') {
                    attr.value = '';
                }
                $('#edit_attr'+attr.attr_id).val(attr.value);
            });
            works = e.work_relations;

            passport = e.passport;
            if (passport) {
                document.getElementById('fileName1').innerHTML = 'Имя файла: ' + passport.name;
            }
        }
    @endcan

    function clone_material(e) {
        $('[class*="clone-field"]').val('');
        $('#clone_material_name').val(e.name);
        $('#clone_material_description').val(e.description);
        $('#clone_material_use_cost').val(e.use_cost);
        $('#clone_material_buy_cost').val(e.buy_cost);

        $('#reference_select_copy').select2('destroy');

        $('#reference_select_copy').select2({
            language: "ru",
            ajax: {
                url: '/building/materials/get_references' + '?category_id=' + {!! $category->id !!},
                dataType: 'json',
                delay: 250,
            }
        });
        if (e.manual_reference_id) {
            var new_option = $("<option selected='selected'></option>").val(e.manual_reference_id).text(e.reference_name);
            $('#reference_select_copy').append(new_option).trigger('change');
        } else {
            $('#reference_select_copy').select2('val', '');
        }
        attrs = e.parameters;
        attrs.forEach(function(attr, id, attrs) {
            if (attr.value === 'null') {
                attr.value = '';
            }
            $('#clone_attr'+attr.attr_id).val(attr.value);
        });

        passport = e.passport;
        if (passport) {
            document.getElementById('fileName2').innerHTML = 'Имя файла: ' + passport.name;
        }
    }

    $('#search_attr').on('change', function () {
        $.ajax({
            url:"{{ route('building::materials::select_attr_value') }}",
            type: 'POST',
            data: {
                _token: CSRF_TOKEN,
                attr_id: $('#search_attr').val(),
            },
            dataType: 'JSON',
            success: function (data) {
                $("#search_value").removeAttr('disabled');
                $('#search_value option').remove();
                $('#search_value').selectpicker('refresh');

                $.each(data, function(index, value) {
                    var new_value = $("<option></option>").val(value).text(value);
                    $(new_value).appendTo($('#search_value'));
                });
                $('#search_value').selectpicker('refresh');
            }
        });
    });

    @if(Auth::user()->can('materials_remove'))
        function delete_material(e, id) {
            swal({
                title: 'Вы уверены?',
                text: "Материал будет удален!",
                type: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#3085d6',
                cancelButtonColor: '#d33',
                cancelButtonText: 'Назад',
                confirmButtonText: 'Удалить'
            }).then((result) => {
                if(result.value) {
                    $.ajax({
                        url:'{{ route("building::materials::delete") }}',
                        type: 'POST',
                        data: {
                            _token: CSRF_TOKEN,
                            mat_id: id,
                            className: "{!! $className !!}"
                        },
                        dataType: 'JSON',
                        success: function () {
                            e.closest('tr').remove();
                        }
                    });
                }
            });
        }
        function restore_material(e, id) {
            swal({
                title: 'Вы уверены?',
                text: "Материал будет восстановлен!",
                type: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#3085d6',
                cancelButtonColor: '#d33',
                cancelButtonText: 'Назад',
                confirmButtonText: 'Восстановить'
            }).then((result) => {
                if(result.value) {
                    $.ajax({
                        url:'{{ route("building::materials::restore") }}',
                        type: 'POST',
                        data: {
                            _token: CSRF_TOKEN,
                            mat_id: id,
                            className: "{!! $className !!}"
                        },
                        dataType: 'JSON',
                        success: function () {
                            e.closest('tr').remove();
                        }
                    });
                }
            });
        }

    @endif

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

    $(document).ready(function () {
        var request = {};
        request.attr_id = [];
        request.values = [];

        $("#search_by_attrs").submit(function(e){
            e.preventDefault(e);

            $('#paganation').addClass('d-none');

            var attr_name = $('#search_attr option:selected').text();
            var attr_id = $('#search_attr option:selected').val();
            var values = $('#search_value').val();
            var result = '';

            if ($('.badge').length >= 2) {
                var badge = $('#parameters').find($("[attr_id="+ attr_id + "]"));

                if (badge.length !== 0) {
                    indexToRemove = request.attr_id.indexOf(attr_id);

                    request.values.splice(indexToRemove, 1);
                    request.values.splice(indexToRemove, 0, values);

                    $(badge).closest('.badge').remove();
                } else {
                    request.attr_id.push(attr_id);
                    request.values.push(values);
                }

                result = attr_name + ': ' + values;

                var button = "<span class=\"badge badge-azure\">" + result + "<span data-role=\"remove\" class=\"badge-remove-link\" attr_id=\"" + attr_id + "\" values=\"" + values + "\"></span></span>"

                $('#parameters').append(button);
            } else {
                result = attr_name + ': ' + values;

                var button = "<span class=\"badge badge-azure\">" + result + "<span data-role=\"remove\" class=\"badge-remove-link\" attr_id=\"" + attr_id + "\" values=\"" + values + "\"></span></span>";

                $('#parameters').append(button);

                request.attr_id.push(attr_id);
                request.values.push(values);
            }

            $.ajax({
                url:'{{ route("building::materials::search_by_attributes") }}',
                type: 'POST',
                data: {
                    _token: CSRF_TOKEN,
                    attr_id: request.attr_id,
                    values: request.values,
                    className: '{{ $className }}',
                },
                dataType: 'JSON',
                success: function (data) {
                    var results_tr = '';

                    if (data.length === 0) {
                        results_tr = "<tr class=\"href\" data-href=\"\">\n" +
                            "                            <td class=\"text-left\">По вашему запросу ничего не найдено</td>\n" +
                            "                            <td class=\"text-left\"></td>\n" +
                            "                            <td class=\"text-left\"></td>\n" +
                            "                            <td class=\"text-right\">\n" +
                            "                            </td>\n" +
                            "                        </tr>";
                    } else {
                        $.each(data, function(key, value) {
                            var mat_id = value.id;
                            var mat_name = value.name;
                            var mat_buy_cost = value.buy_cost;
                            var mat_use_cost = value.use_cost;

                            results_tr += "<tr class=\"href\" data-href=\"\">\n" +
                                "                            <td class=\"text-left\">" + mat_name + "</td>\n" +
                                "                            <td class=\"text-left\">" + mat_buy_cost + "</td>\n" +
                                "                            <td class=\"text-left\">" + mat_use_cost + "</td>\n" +
                                "                            <td class=\"text-right\">\n" +
                                "                                <button id=\"clone" + key + "\" rel=\"tooltip\" class=\"btn btn-link btn-xs btn-space\" data-toggle=\"modal\" data-target=\"#clone-material\" data-original-title=\"Сделать копию \">\n" +
                                "                                    <i class=\"fa fa-clone\"></i>\n" +
                                "                                </button>\n" +
                                "                                <button id=\"edit" + key + "\" rel=\"tooltip\" class=\"btn btn-link btn-xs btn-success btn-space\" data-toggle=\"modal\" data-target=\"#edit-material\" data-original-title=\"Редактировать\">\n" +
                                "                                    <i class=\"fa fa-edit\"></i>\n" +
                                "                                </button>\n" +
                                "                                <button id=\"see" + key + "\" rel=\"tooltip\" class=\"btn btn-link btn-xs btn-info btn-space\" data-toggle=\"modal\" data-target=\"#view-material\" data-original-title=\"Просмотр\">\n" +
                                "                                    <i class=\"fa fa-eye\"></i>\n" +
                                "                                </button>\n" +
                                "                                <button rel=\"tooltip\" onclick=\"delete_material(this, " + mat_id + ")\" class=\"btn btn-danger btn-xs btn-link btn-space\" data-toggle=\"modal\" data-target=\"#\" data-original-title=\"Удалить\">\n" +
                                "                                    <i class=\"fa fa-times\"></i>\n" +
                                "                                </button>\n" +
                                "                            </td>\n" +
                                "                        </tr>";
                        });
                    }

                    $('#put_here').html(results_tr);

                    $.each(data, function(key, value) {
                        var mat = JSON.stringify(value);

                        $("#clone" + key).attr("onclick", "clone_material(" + mat + ")");
                        $("#edit" + key).attr("onclick", "edit_material(" + mat + ")");
                        $("#see" + key).attr("onclick", "view_material(" + mat + ")");
                    });

                    $('#search_button').attr('disabled', 'disabled');
                }
            });

            $('#search_attr').selectpicker('refresh');
            $('#search_value').selectpicker('refresh');
        });

        jQuery(document).on('click', '.badge-remove-link', function() {
            var category_id = {{ $category->id }};

            var attr_id = $(this).attr('attr_id');

            indexToRemove = request.attr_id.indexOf(attr_id);

            request.values.splice(indexToRemove, 1);
            request.attr_id.splice(indexToRemove, 1);

            $(this).closest('.badge').remove();

            if ($('.badge').length >= 2) {
                $.ajax({
                    url:'{{ route("building::materials::search_by_attributes") }}',
                    type: 'POST',
                    data: {
                        _token: CSRF_TOKEN,
                        attr_id: request.attr_id,
                        values: request.values
                    },
                    dataType: 'JSON',
                    success: function (data) {
                        var results_tr = '';

                        if (data.length === 0) {
                            results_tr = "<tr class=\"href\" data-href=\"\">\n" +
                                "                            <td class=\"text-left\">По вашему запросу ничего не найдено</td>\n" +
                                "                            <td class=\"text-left\"></td>\n" +
                                "                            <td class=\"text-left\"></td>\n" +
                                "                            <td class=\"text-right\">\n" +
                                "                            </td>\n" +
                                "                        </tr>";
                        } else {
                            $.each(data, function(key, value) {
                                var mat_id = value.id;
                                var mat_name = value.name;
                                var mat_buy_cost = value.buy_cost;
                                var mat_use_cost = value.use_cost;

                                results_tr += "<tr class=\"href\" data-href=\"\">\n" +
                                    "                            <td class=\"text-left\">" + mat_name + "</td>\n" +
                                    "                            <td class=\"text-left\">" + mat_buy_cost + "</td>\n" +
                                    "                            <td class=\"text-left\">" + mat_use_cost + "</td>\n" +
                                    "                            <td class=\"text-right\">\n" +
                                    "                                <button id=\"clone" + key + "\" rel=\"tooltip\" class=\"btn btn-link btn-xs btn-space\" data-toggle=\"modal\" data-target=\"#clone-material\" data-original-title=\"Сделать копию \">\n" +
                                    "                                    <i class=\"fa fa-clone\"></i>\n" +
                                    "                                </button>\n" +
                                    "                                <button id=\"edit" + key + "\" rel=\"tooltip\" class=\"btn btn-link btn-xs btn-success btn-space\" data-toggle=\"modal\" data-target=\"#edit-material\" data-original-title=\"Редактировать\">\n" +
                                    "                                    <i class=\"fa fa-edit\"></i>\n" +
                                    "                                </button>\n" +
                                    "                                <button id=\"see" + key + "\" rel=\"tooltip\" class=\"btn btn-link btn-xs btn-info btn-space\" data-toggle=\"modal\" data-target=\"#view-material\" data-original-title=\"Просмотр\">\n" +
                                    "                                    <i class=\"fa fa-eye\"></i>\n" +
                                    "                                </button>\n" +
                                    "                                <button rel=\"tooltip\" onclick=\"delete_material(this, " + mat_id + ")\" class=\"btn btn-danger btn-xs btn-link btn-space\" data-toggle=\"modal\" data-target=\"#\" data-original-title=\"Удалить\">\n" +
                                    "                                    <i class=\"fa fa-times\"></i>\n" +
                                    "                                </button>\n" +
                                    "                            </td>\n" +
                                    "                        </tr>";
                            });
                        }

                        $('#put_here').html(results_tr);

                        $.each(data, function(key, value) {
                            var mat = JSON.stringify(value);

                            $("#clone" + key).attr("onclick", "clone_material(" + mat + ")");
                            $("#edit" + key).attr("onclick", "edit_material(" + mat + ")");
                            $("#see" + key).attr("onclick", "view_material(" + mat + ")");
                        });

                        $('#search_button').attr('disabled', 'disabled');
                    }
                });
            } else {
                $('#paganation').removeClass('d-none');

                $('#paganation').removeClass('d-none');

                $.ajax({
                    url:'{{ route("building::materials::search_by_attributes") }}',
                    type: 'POST',
                    data: {
                        _token: CSRF_TOKEN,
                        category_id: category_id
                    },
                    dataType: 'JSON',
                    success: function (data) {
                        var results_tr = '';

                        if (data.length === 0) {
                            results_tr = "<tr class=\"href\" data-href=\"\">\n" +
                                "                            <td class=\"text-left\">По вашему запросу ничего не найдено</td>\n" +
                                "                            <td class=\"text-left\"></td>\n" +
                                "                            <td class=\"text-left\"></td>\n" +
                                "                            <td class=\"text-right\">\n" +
                                "                            </td>\n" +
                                "                        </tr>";
                        } else {
                            $.each(data, function(key, value) {
                                var mat_id = value.id;
                                var mat_name = value.name;
                                var mat_buy_cost = value.buy_cost;
                                var mat_use_cost = value.use_cost;

                                results_tr += "<tr class=\"href\" data-href=\"\">\n" +
                                    "                            <td class=\"text-left\">" + mat_name + "</td>\n" +
                                    "                            <td class=\"text-left\">" + mat_buy_cost + "</td>\n" +
                                    "                            <td class=\"text-left\">" + mat_use_cost + "</td>\n" +
                                    "                            <td class=\"text-right\">\n" +
                                    "                                <button id=\"clone" + key + "\" rel=\"tooltip\" class=\"btn btn-link btn-xs btn-space\" data-toggle=\"modal\" data-target=\"#clone-material\" data-original-title=\"Сделать копию \">\n" +
                                    "                                    <i class=\"fa fa-clone\"></i>\n" +
                                    "                                </button>\n" +
                                    "                                <button id=\"edit" + key + "\" rel=\"tooltip\" class=\"btn btn-link btn-xs btn-success btn-space\" data-toggle=\"modal\" data-target=\"#edit-material\" data-original-title=\"Редактировать\">\n" +
                                    "                                    <i class=\"fa fa-edit\"></i>\n" +
                                    "                                </button>\n" +
                                    "                                <button id=\"see" + key + "\" rel=\"tooltip\" class=\"btn btn-link btn-xs btn-info btn-space\" data-toggle=\"modal\" data-target=\"#view-material\" data-original-title=\"Просмотр\">\n" +
                                    "                                    <i class=\"fa fa-eye\"></i>\n" +
                                    "                                </button>\n" +
                                    "                                <button rel=\"tooltip\" onclick=\"delete_material(this, " + mat_id + ")\" class=\"btn btn-danger btn-xs btn-link btn-space\" data-toggle=\"modal\" data-target=\"#\" data-original-title=\"Удалить\">\n" +
                                    "                                    <i class=\"fa fa-times\"></i>\n" +
                                    "                                </button>\n" +
                                    "                            </td>\n" +
                                    "                        </tr>";
                            });
                        }

                        $('#put_here').html(results_tr);

                        $.each(data, function(key, value) {
                            var mat = JSON.stringify(value);

                            $("#clone" + key).attr("onclick", "clone_material(" + mat + ")");
                            $("#edit" + key).attr("onclick", "edit_material(" + mat + ")");
                            $("#see" + key).attr("onclick", "view_material(" + mat + ")");
                        });
                    }
                });

                $('#search_button').attr('disabled', 'disabled');
            }
        });
    });


    $('#create-material-reference-button').on('click', function () {
        $('#createMaterialClassName').val('ManualReference');
    });

    $('#create-material-button').on('click', function () {
        $('#createMaterialClassName').val('ManualMaterial');
    });

    $('#reference_select_create').select2({
        language: "ru",
        ajax: {
            url: '/building/materials/get_references' + '?category_id=' + {!! $category->id !!},
            dataType: 'json',
            delay: 250,
        }
    });

</script>
@endsection
