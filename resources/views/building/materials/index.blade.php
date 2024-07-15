@extends('layouts.app')

@section('title', 'Категории материалов')

@section('url', route('building::materials::index'))

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
    <div class="col-md-12">
        <div class="nav-container" style="margin:0 0 10px 15px">
            <ul class="nav nav-icons" role="tablist">
                @can('manual_materials')
                <li class="nav-item @if (Request::is('building/materials')) show active @endif">
                    <a class="nav-link link-line @if (Request::is('building/materials')) active-link-line @endif" href="{{ route('building::materials::index') }}">
                        Материалы
                    </a>
                </li>
                @endcan
                @can('manual_nodes')
                <li class="nav-item @if (Request::is('building/nodes')) show active @endif">
                    <a class="nav-link link-line @if (Request::is('building/nodes')) active-link-line @endif" href="{{ route('building::nodes::index') }}">
                        Типовые узлы
                    </a>
                </li>
                @endcan
            </ul>
        </div>
        <div class="card strpied-tabled-with-hover">
            <div class="fixed-table-toolbar toolbar-for-btn">
                <div class="fixed-search">
                    <form action="{{ route('building::materials::index') }}">
                        <input class="form-control" type="text" value="{{ Request::get('search') }}" name="search" placeholder="Поиск">
                    </form>
                </div>
                <div class="pull-right">
                    @can('manual_materials_edit')
                    <button class="btn btn-round btn-outline btn-sm add-btn" data-toggle="modal" data-target="#create-category">
                        <i class="glyphicon fa fa-plus"></i>
                        Добавить категорию
                    </button>
                    @endcan
                </div>
            </div>
            <div class="table-responsive">
                <table class="table table-hover mobile-table">
                    <thead>
                        <tr>
                            <th class="text-left">Название категории</th>
                            <th class="text-right">Действия</th>
                        </tr>
                    </thead>
                    <tbody>
                    @foreach ($categories as $category)
                        <tr style="cursor:default" class="href" data-href="{{ route('building::materials::card', $category->id) }}">
                            <td data-label="Название категории" class="text-left">{{ $category->name }}</td>
                            <td class="text-right actions">
                                @can('manual_materials_edit')
                                <button rel="tooltip" onclick="clone_category({{ $category }})" class="btn btn-link btn-xs btn-space" data-toggle="modal" data-target="#clone-category" data-original-title="Сделать копию">
                                    <i class="fa fa-clone"></i>
                                </button>
                                @endcan
                                @can('manual_materials_edit')
                                <button rel="tooltip" onclick="edit_category({{ $category }})" class="btn btn-link btn-xs btn-success btn-space" data-toggle="modal" data-target="#edit-category" data-original-title="Редактировать">
                                    <i class="fa fa-edit"></i>
                                </button>
                                @endcan
                                <button rel="tooltip" onclick="view_category({{ $category }})" class="btn btn-link btn-xs btn-info btn-space" data-toggle="modal" data-target="#view-category" data-original-title="Просмотр">
                                    <i class="fa fa-eye"></i>
                                </button>
                                @can('manual_materials_edit')
                                    @if($category->id > 14)
                                    <button rel="tooltip" onclick="delete_category(this, {{ $category->id }})" class="btn btn-danger btn-xs btn-link btn-space" data-toggle="modal" data-target="#" data-original-title="Удалить">
                                        <i class="fa fa-times"></i>
                                    </button>
                                    @endif
                                @endcan
                            </td>
                        </tr>
                    @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Модалки -->
<!-- Создание категории-->
@can('manual_materials_edit')
<div class="modal fade bd-example-modal-lg show" id="create-category" role="dialog" aria-labelledby="modal-search" style="display: none;">
    <div class="modal-dialog modal-lg" role="document">
       <div class="modal-content">
           <div class="modal-header">
               <h5 class="modal-title">Создание категории</h5>
               <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                   <span aria-hidden="true">×</span>
               </button>
           </div>
           <div class="modal-body">
               <hr style="margin-top:0">
               <div class="card border-0" >
                   <div class="card-body ">
                       <form id="create_category_form" class="form-horizontal" action="{{ route('building::materials::category::store') }}" method="post" enctype="multipart/form-data">
                           @csrf
                           <div class="row">
                               <div class="col-md-12">
                                   <div class="form-group">
                                        <label>Название<span class="star">*</span></label>
                                        <input type="text" name="name" placeholder="Укажите название" class="form-control" maxlength="50" required>
                                    </div>
                               </div>
                           </div>
                           <div class="row">
                               <div class="col-md-12">
                                   <div class="form-group">
                                        <label>Название материалов внутри категории</label>
                                        <input type="text" name="formula" placeholder="Укажите название" class="form-control" maxlength="1000">
                                    </div>
                               </div>
                           </div>
                           <div class="row">
                               <div class="col-sm-12">
                                   <div class="form-group">
                                       <label>Описание</label>
                                       <textarea class="form-control textarea-rows " name="description" maxlength="200"></textarea>
                                   </div>
                               </div>
                           </div>
                           <input name="file_ids" type="hidden" :value="files.map(file => file.id)">

                           <div class="modal-section" id="files_create">
                               <label for="">Файлы
{{--                                   <br><span class="important-tip"></span>--}}
                               </label>
                               <el-upload
                                   action="{{ route('file_entry.store') }}"
                                   :headers="{ 'X-CSRF-TOKEN': '{{ csrf_token() }}' }"
                                   :limit="10"
                                   :before-upload="beforeUpload"
                                   :on-preview="handlePreview"
                                   :on-remove="handleRemove"
                                   :on-exceed="handleExceed"
                                   :on-success="handleBackSuccess"
                                   :on-error="handleError"
                                   multiple
                               >
                                   <el-button size="small" type="primary">Загрузить</el-button>
                                   <div class="el-upload__tip" slot="tip">Файлы формата pdf размером до 5Мб</div>
                               </el-upload>
                               <div class="error-message d-none" id="back-upload-section-send-error">Обязательное поле</div>
                           </div>
                           <div class="row">
                               <div class="col-sm-3">
                                   <div class="form-group">
                                       <label>Единица измерения<span class="star">*</span></label>
                                       <div class="form-group">
                                           <select id="create_category_unit" name="category_unit" class="selectpicker" data-title="Ед.измерения" data-style="btn-default btn-outline" data-menu-style="dropdown-blue" required>
                                               <option>шт</option>
                                               <option>м.п</option>
                                               <option>м&sup2</option>
                                               <option>м&sup3</option>
                                               <option>т</option>
                                           </select>
                                       </div>
                                   </div>
                               </div>
                           </div>
                           <hr>
                           <h6 style="margin:20px 0 10px">Атрибуты</h6>
                           <input name="attrs" type="hidden" v-model="JSON.stringify(attrs)">
                           <div class="row">
                               <div class="col-md-12">
                                   <div class="row">
                                       <div class="col-md-5">
                                           <div class="form-group">
                                               <label>Название<span class="star">*</span></label>
                                               <select id="create_category_attribute" onchange="attr_changed()" class="js-select-attr-first" style="width:100%;">
                                                   <option value="1">Удельная масса</option>
                                                   <option value="2">Удельный объем</option>
                                                   <option value="3">Удельный погонаж</option>
                                                   <option value="4">Удельная площадь</option>
                                               </select>
                                            </div>
                                       </div>
                                       <div class="col-md-4">
                                           <div class="form-group">
                                                <label>Ед. измерения</label>
                                                <input id="attr_unit_create" type="text" v-model="attr_unit" placeholder="Укажите ед.измерения" class="form-control">
                                            </div>
                                       </div>
                                       <div class="col-md-2 pull-right required-check">
                                           <div class="form-check ">
                                               <label class="form-check-label">
                                                   <input class="form-check-input" name="attr_required" v-model="is_required" type="checkbox" value="0">
                                                   <span class="form-check-sign"></span>
                                                   Обязательный
                                               </label>
                                           </div>
                                       </div>
                                   </div>
                                   <div class="row not_static_attrs">
                                      <div class="col-md-4">
                                          <div class="form-group">
                                              <label>От</label>
                                              <input id="attr_from_create" type="number" v-model="attr_from" placeholder="Укажите от" class="form-control attr_from_create" >
                                          </div>
                                      </div>
                                      <div class="col-md-4">
                                          <div class="form-group">
                                              <label>До</label>
                                              <input id="attr_to_create" type="number" v-model="attr_to" placeholder="Укажите до" class="form-control attr_to_create" >
                                          </div>
                                      </div>
                                      <div class="col-md-4">
                                          <div class="form-group">
                                              <label>Шаг</label>
                                              <input id="attr_step_create" type="number" v-model="attr_step" placeholder="Укажите шаг" class="form-control attr_step_create" >
                                          </div>
                                      </div>
                                  </div>
                                  <div class="row static_attrs" style="display:none;">
                                      <div class="col-md-4">
                                          <div class="form-group">
                                              <label>Значение для статических полей</label>
                                              <input id="attr_value_create" type="text" v-model="attr_value" placeholder="Укажите статическое значение" class="form-control attr_value_create" >
                                          </div>
                                      </div>
                                  </div>
                                   <div class="row">
                                       <div class="col-md-12 text-center">
                                           <button type="button" id="add_attr" class="btn btn-outline btn-round btn-sm btn-success" v-on:click="new_attr">
                                               <i class="fa fa-check"></i>
                                               Добавить
                                           </button>
                                       </div>
                                   </div>
                                   <hr>
                                   <div class="row">
                                       <div class="col-md-12">
                                           <div class="bootstrap-tagsinput">
                                               <span class="badge" v-bind:class="{ 'badge-orange': attr['is_required'], 'badge-azure': !attr['is_required']}"v-for="(attr, key) in attrs">@{{ attr['name'] }}, @{{ attr['unit'] }}<span data-role="remove" class="badge-remove-link" v-on:click="attrs.splice(key,1)"></span></span>
                                           </div>
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
                <button id="submit_cat" type="submit" form="create_category_form" class="btn btn-info">Сохранить</button>
           </div>
        </div>
    </div>
</div>
<!-- Просмотр категории-->
@endcan
<div class="modal fade bd-example-modal-lg show" id="view-category" role="dialog" aria-labelledby="modal-search" style="display: none;">
    <div class="modal-dialog modal-lg" role="document">
       <div class="modal-content">
           <div class="modal-header">
               <h5 id="view_category_name" class="modal-title"></h5>
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
                                   <p id="view_category_description" class="form-control-static">Описание категории материалов</p>
                               </div>
                           </div>
                       </div>
                       @if(Auth::user()->is_su)
                       <div class="row">
                           <div class="col-sm-12">
                               <div class="form-group">
                                   <label>Название материалов</label>
                                   <p id="view_category_formula" class="form-control-static">Название материалов внутри категории</p>
                               </div>
                           </div>
                       </div>
                       @endif
                       <div class="row">
                           <div class="col-sm-12">
                               <div class="form-group">
                                   <label>Единицы измерения</label>
                                   <p id="view_category_unit" class="form-control-static"></p>
                               </div>
                           </div>
                       </div>
                       <h6 style="margin:20px 0 10px">Атрибуты</h6>
                       <div class="row">
                           <div class="col-md-12">
                               <div class="row">
                                   <div class="col-md-12">
                                       <div class="bootstrap-tagsinput">
                                           <span class="badge" v-bind:class="{ 'badge-orange': attr['is_required'], 'badge-azure': !attr['is_required']}" v-for="(attr, key) in attrs">@{{ attr['name'] }}, @{{ attr['unit'] }}</span>
                                       </div>
                                   </div>
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
<!-- Редактирование категории-->
@can('manual_materials_edit')
<div class="modal fade bd-example-modal-lg show" id="edit-category" role="dialog" aria-labelledby="modal-search" style="display: none;">
    <div class="modal-dialog modal-lg" role="document">
       <div class="modal-content">
           <div class="modal-header">
               <h5 class="modal-title">Редактирование категории</h5>
               <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                   <span aria-hidden="true">×</span>
               </button>
           </div>
           <div class="modal-body">
               <hr style="margin-top:0">
               <div class="card border-0" >
                   <div class="card-body ">
                       <form id="edit_category_form" class="form-horizontal" action="{{ route('building::materials::category::update') }}" method="post" enctype="multipart/form-data">
                           @csrf
                           <input type="hidden" name="id" id="edit_category_id">
                           <div class="row">
                               <div class="col-md-12">
                                   <div class="form-group">
                                        <label>Название<span class="star">*</span></label>
                                        <input id="edit_category_name" type="text" name="name" placeholder="Укажите название" class="form-control" maxlength="50" required>
                                    </div>
                               </div>
                           </div>
                           <div class="row">
                               <div class="col-md-12">
                                   <div class="form-group">
                                        <label>Название материалов внутри категории</label>
                                        <input id="edit_category_formula" type="text" name="formula" placeholder="Укажите название" class="form-control" maxlength="1000">
                                    </div>
                               </div>
                           </div>
                           <div class="row">
                               <div class="col-sm-12">
                                   <div class="form-group">
                                       <label>Описание</label>
                                       <textarea id="edit_category_description" class="form-control textarea-rows" name="description" maxlength="200"></textarea>
                                   </div>
                               </div>
                           </div>
                            <input name="file_ids" type="hidden" :value="files.map(file => file.id)">
                           <div class="modal-section">
                               <label for="">Файлы
{{--                                   <br><span class="important-tip"></span>--}}
                               </label>
                               <el-upload
                                   action="{{ route('file_entry.store') }}"
                                   :headers="{ 'X-CSRF-TOKEN': '{{ csrf_token() }}' }"
                                   :limit="10"
                                   :file-list="files"
                                   :before-upload="beforeUpload"
                                   :on-success="add_files"
                                   :on-remove="remove_files"
                                   :on-exceed="handleExceed"
                                   multiple
                               >
                                   <el-button size="small" type="primary">Загрузить</el-button>
                                   <div class="el-upload__tip" slot="tip">Файлы формата pdf размером до 5Мб</div>
                               </el-upload>
                               <div class="error-message d-none" id="back-upload-section-send-error">Обязательное поле</div>
                           </div>
                           <div class="row">
                                <div class="col-sm-3">
                               <div class="form-group">
                                   <label>Единица измерения<span class="star">*</span></label>
                                   <div class="form-group">
                                       <select id="edit_category_unit" name="category_unit" class="selectpicker" data-title="Ед.измерения" data-style="btn-default btn-outline" data-menu-style="dropdown-blue" required>
                                           <option>шт</option>
                                           <option>м.п</option>
                                           <option>м&sup2</option>
                                           <option>м&sup3</option>
                                           <option>т</option>
                                       </select>
                                   </div>
                               </div>
                           </div>
                           </div>
                           <hr>
                           <h6 style="margin:20px 0 10px">Атрибуты</h6>
                           <input name="attrs" type="hidden" v-model="JSON.stringify(attrs)">
                           <div class="row">
                               <div class="col-md-12">
                                   <div class="row">
                                       <div class="col-md-5">
                                           <div class="form-group">
                                               <label>Название<span class="star">*</span></label>
                                               <select id="edit_category_attribute" onchange="edit_attr_changed()" class="js-select-attr-edit"  style="width:100%;" required>
                                                   <option value="1">Удельная масса</option>
                                                   <option value="2">Удельный объем</option>
                                                   <option value="3">Удельный погонаж</option>
                                                   <option value="4">Удельная площадь</option>
                                               </select>
                                            </div>
                                       </div>
                                       <div class="col-md-4">
                                           <div class="form-group">
                                                <label>Ед. измерения<span class="star">*</span></label>
                                                <input id="attr_unit_edit" type="text" v-model="attr_unit" placeholder="Укажите ед.измерения" class="form-control">
                                            </div>
                                       </div>
                                       <div class="col-md-2 text-right required-check">
                                           <div class="form-check">
                                               <label class="form-check-label">
                                                   <input class="form-check-input" v-model="is_required" type="checkbox" value="">
                                                   <span class="form-check-sign"></span>
                                                   Обязательный
                                               </label>
                                           </div>
                                       </div>
                                   </div>
                                   <div class="row not_static_attrs">
                                      <div class="col-md-4">
                                          <div class="form-group">
                                              <label>От</label>
                                              <input type="number" v-model="attr_from" placeholder="Укажите от" class="form-control attr_from_create" >
                                          </div>
                                      </div>
                                      <div class="col-md-4">
                                          <div class="form-group">
                                              <label>До</label>
                                              <input type="number" v-model="attr_to" placeholder="Укажите до" class="form-control attr_to_create" >
                                          </div>
                                      </div>
                                      <div class="col-md-4">
                                          <div class="form-group">
                                              <label>Шаг</label>
                                              <input type="number" v-model="attr_step" placeholder="Укажите шаг" class="form-control attr_step_create" >
                                          </div>
                                      </div>
                                  </div>
                                  <div class="row static_attrs" style="display:none;">
                                      <div class="col-md-4">
                                          <div class="form-group">
                                              <label>Значение для статических полей</label>
                                              <input type="text" v-model="attr_value" placeholder="Укажите статическое значение" class="form-control attr_value_create" >
                                          </div>
                                      </div>
                                  </div>
                                   <div class="row">
                                       <div class="col-md-12 text-center">
                                           <button type="button" class="btn btn-outline btn-round btn-success btn-sm" v-on:click="new_attr">
                                               <i class="fa fa-check"></i>
                                               Добавить
                                           </button>
                                       </div>
                                   </div>
                                   <hr>
                                   <div class="row">
                                       <div class="col-md-12">
                                           <div class="bootstrap-tagsinput">
                                               <span class="badge" v-bind:class="{ 'badge-orange': attr['is_required'], 'badge-azure': !attr['is_required']}"v-for="(attr, key) in attrs">@{{ attr['name'] }}, @{{ attr['unit'] }}<span data-role="remove" class="badge-remove-link" v-on:click="attrs.splice(key,1)"></span></span>
                                           </div>
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
                <button id="" type="submit" form="edit_category_form" class="btn btn-info">Сохранить</button>
           </div>
        </div>
    </div>
</div>
@endcan
<!-- Клонирование категории-->
@can('manual_materials_edit')
<div class="modal fade bd-example-modal-lg show" id="clone-category" role="dialog" aria-labelledby="modal-search" style="display: none;">
    <div class="modal-dialog modal-lg" role="document">
       <div class="modal-content">
           <div class="modal-header">
               <h5 class="modal-title">Создание копии</h5>
               <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                   <span aria-hidden="true">×</span>
               </button>
           </div>
           <div class="modal-body">
               <hr style="margin-top:0">
               <div class="card border-0" >
                   <div class="card-body ">
                       <form id="clone_category_form" class="form-horizontal" action="{{ route('building::materials::category::clone') }}" method="post" enctype="multipart/form-data">
                           @csrf
                           <div class="row">
                               <div class="col-md-12">
                                   <div class="form-group">
                                        <label>Название<span class="star">*</span></label>
                                        <input id="clone_category_name" type="text" name="name" placeholder="Укажите название" class="form-control" maxlength="50" required>
                                    </div>
                               </div>
                           </div>
                           <div class="row">
                               <div class="col-md-12">
                                   <div class="form-group">
                                        <label>Название материалов внутри категории</label>
                                        <input id="clone_category_formula" type="text" name="formula" placeholder="Укажите название" class="form-control" maxlength="1000">
                                    </div>
                               </div>
                           </div>
                           <div class="row">
                               <div class="col-sm-12">
                                   <div class="form-group">
                                       <label>Описание</label>
                                       <textarea id="clone_category_description" class="form-control textarea-rows" name="description" maxlength="200"></textarea>
                                   </div>
                               </div>
                           </div>
                           <div class="row">
                               <div class="col-sm-3">
                                   <div class="form-group">
                                       <label>Единица измерения<span class="star">*</span></label>
                                       <div class="form-group">
                                           <select id="clone_category_unit" name="category_unit" class="selectpicker" data-title="Ед.измерения" data-style="btn-default btn-outline" data-menu-style="dropdown-blue" required>
                                               <option>шт</option>
                                               <option>м.п</option>
                                               <option>м&sup2</option>
                                               <option>м&sup3</option>
                                               <option>т</option>
                                           </select>
                                       </div>
                                   </div>
                               </div>
                           </div>
                           <hr>
                           <h6 style="margin:20px 0 10px">Атрибуты</h6>
                           <input name="attrs" type="hidden" v-model="JSON.stringify(attrs)">
                           <div class="row">
                               <div class="col-md-12">
                                   <div class="row">
                                       <div class="col-md-5">
                                           <div class="form-group">
                                               <label>Название<span class="star">*</span></label>
                                               <select id="clone_category_attribute" onchange="clone_attr_changed()" class="js-select-attr-clone"  style="width:100%;" required>
                                                   <option value="1">Удельная масса</option>
                                                   <option value="2">Удельный объем</option>
                                                   <option value="3">Удельный погонаж</option>
                                                   <option value="4">Удельная площадь</option>
                                               </select>
                                            </div>
                                       </div>
                                       <div class="col-md-4">
                                           <div class="form-group">
                                                <label>Ед. измерения<span class="star">*</span></label>
                                                <input id="attr_unit_clone" type="text" v-model="attr_unit" placeholder="Укажите ед.измерения" class="form-control" required>
                                            </div>
                                       </div>
                                       <div class="col-md-2 text-right required-check">
                                           <div class="form-check">
                                               <label class="form-check-label">
                                                   <input class="form-check-input" v-model="is_required" type="checkbox" value="">
                                                   <span class="form-check-sign"></span>
                                                   Обязательный
                                               </label>
                                           </div>
                                       </div>
                                   </div>
                                   <div class="row not_static_attrs">
                                      <div class="col-md-4">
                                          <div class="form-group">
                                              <label>От</label>
                                              <input type="number" v-model="attr_from" placeholder="Укажите от" class="form-control attr_from_create" >
                                          </div>
                                      </div>
                                      <div class="col-md-4">
                                          <div class="form-group">
                                              <label>До</label>
                                              <input type="number" v-model="attr_to" placeholder="Укажите до" class="form-control attr_to_create" >
                                          </div>
                                      </div>
                                      <div class="col-md-4">
                                          <div class="form-group">
                                              <label>Шаг</label>
                                              <input type="number" v-model="attr_step" placeholder="Укажите шаг" class="form-control attr_step_create" >
                                          </div>
                                      </div>
                                  </div>
                                  <div class="row static_attrs" style="display:none;">
                                      <div class="col-md-4">
                                          <div class="form-group">
                                              <label>Значение для статических полей</label>
                                              <input type="text" v-model="attr_value" placeholder="Укажите статическое значение" class="form-control attr_value_create" >
                                          </div>
                                      </div>
                                  </div>
                                   <div class="row">
                                       <div class="col-md-12 text-center">
                                           <button type="button" class="btn btn-outline btn-round btn-success btn-sm" v-on:click="new_attr">
                                               <i class="fa fa-check"></i>
                                               Добавить
                                           </button>
                                       </div>
                                   </div>
                                   <hr>
                                   <div class="row">
                                       <div class="col-md-12">
                                           <div class="bootstrap-tagsinput">
                                               <span class="badge" v-bind:class="{ 'badge-orange': attr['is_required'], 'badge-azure': !attr['is_required']}"v-for="(attr, key) in attrs">@{{ attr['name'] }}, @{{ attr['unit'] }}<span data-role="remove" class="badge-remove-link" v-on:click="attrs.splice(key,1)"></span></span>
                                           </div>
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
                <button id="" type="submit" form="clone_category_form" class="btn btn-info">Копировать</button>
           </div>
        </div>
    </div>
</div>
@endcan
@endsection

@section('js_footer')

<script>
@can('manual_materials_edit')
    var unitOptions = {
        'шт': {
            1: 'Масса 1 шт',
            2: 'Объем 1 шт',
            3: 'Длина 1 шт',
            4: 'Площадь 1 шт',
        },
        'м.п': {
            1: 'Масса 1 м.п',
            2: 'Объем 1 м.п',
            4: 'Площадь 1 м.п',
        },
        'м²': {
            1: 'Масса 1 м²',
            2: 'Объем 1 м²',
            3: 'Погонаж 1 м²',
        },
        'м³': {
            1: 'Масса 1 м³',
            3: 'Погонаж 1 м³',
            4: 'Площадь 1 м³',
        },
        'т': {
            2: 'Объем 1 т',
            3: 'Погонаж 1 т',
            4: 'Площадь 1 т',
        }
    };
    var legacyOptions = {
        0: 'Удельная масса',
        1: 'Удельный объем',
        2: 'Удельный погонаж',
        3: 'Удельная площадь',
    };

    var attr_new = new Vue({
        el: '#create-category',
        data: {
            cat_unit: '',
            attrs: [],
            attr_id: '',
            attr_name: '',
            attr_unit: '',
            is_required: false,
            attr_to: '',
            attr_from: '',
            attr_step: '',
            attr_value: '',
            files: [],
        },
        mounted: function() {
            var that = this;
            $('#attr_unit_create').val('т');
            this.attr_unit = 'т';
            $('#attr_unit_create').prop('disabled', true);
            this.attr_id = $('.js-select-attr-first').find(':selected').val() - 1;
            this.attr_name = $('.js-select-attr-first').find(':selected')[0].text;
            $("#create_category_unit").change(function(){
                that.cat_unit = $(this).children("option:selected").val();
            });
        },
        watch: {
            cat_unit(val) {
                this.rerenderOptions(val);
            },
        },
        methods: {
            new_attr: function () {

                if (this.attr_name.trim() === "") {

                } else {
                    this.attrs.push({
                        'id': this.attr_id,
                        'name': unitOptions[this.cat_unit][this.attr_id + 1] ? unitOptions[this.cat_unit][this.attr_id + 1] : this.attr_name.trim(),
                        'unit': this.attr_unit.trim(),
                        'is_required': this.is_required,
                        'to': this.attr_to.trim(),
                        'from':this.attr_from.trim(),
                        'step':this.attr_step.trim(),
                        'value':this.attr_value.trim(),
                    });
                    this.attr_id = "";
                    this.attr_name = "";
                    this.attr_unit = "";
                    this.is_required = false;
                    this.attr_to = "";
                    this.attr_from = "";
                    this.attr_step = "";
                    this.attr_value = "";
                }

            },
            rerenderOptions(val) {
                const selectElem = $("#create_category_attribute");
                selectElem.empty();
                const selectOptions = Object.entries(unitOptions[val]);
                for (let i in selectOptions) {
                    if (!isNaN(+i)) {
                        const optionValue = selectOptions[i][0];
                        const optionLabel = selectOptions[i][1];
                        selectElem.append($("<option></option>")
                            .attr("value", optionValue).text(optionLabel));
                    }
                }
                attr_changed();
            },
            handleRemove(file, fileList) {
                if (file.hasOwnProperty('response')) {
                    let index = -1;
                    if ((index = this.files.findIndex(el => el.id === file.response.data[0].id)) !== -1) {
                        this.files.splice(index, 1);
                    }
                }
            },

            handleFrontSuccess(response, file, fileList) {
                file.url = file.response.data[0] ? '{{ asset('storage/docs/tech_accounting/') }}' + '/' + file.response.data[0].filename : '#';
                this.files.push(...file.response.data);
            },
            handleBackSuccess(response, file, fileList) {
                file.url = file.response.data[0] ? '{{ asset('storage/docs/tech_accounting/') }}' + '/' + file.response.data[0].filename : '#';
                this.files.push(...file.response.data);
            },
            handleDocSuccess(response, file, fileList) {
                file.url = file.response.data[0] ? '{{ asset('storage/docs/tech_accounting/') }}' + '/' + file.response.data[0].filename : '#';
                this.files.push(...file.response.data);
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
            },
            handleExceed(files, fileList) {
                this.$message.warning(`Невозможно загрузить ${files.length} файлов. Вы можете загрузить еще ${10 - fileList.length} файлов`);
            },
            beforeUpload(file) {
                const ALLOWED_EXTENSIONS = ['pdf'];
                const FILE_MAX_LENGTH = 5_000_000;
                const nameParts = file.name.split('.');
                if (ALLOWED_EXTENSIONS.indexOf(nameParts[nameParts.length - 1]) === -1) {
                    this.$message.warning(`Ошибка загрузки файла. Разрешенные форматы: PDF.`);
                    return false;
                }
                if (file.size > FILE_MAX_LENGTH) {
                    this.$message.warning(`Ошибка загрузки файла. Размер файла не должен превышать 5Мб`);
                    return false;
                }
                return true;
            },
        }
    });
    var edit = new Vue({
        el: '#edit-category',
        data: {
            cat_unit: '',
            attrs: [],
            attr_id: '',
            attr_name: '',
            attr_unit: '',
            is_required: false,
            attr_to: '',
            attr_from: '',
            attr_step: '',
            attr_value: '',
            files: [],

        },
        mounted: function() {
            var that = this;
            $('#attr_unit_edit').val('т');
            this.attr_unit = 'т';
            $('#attr_unit_edit').prop('disabled', true);
            this.attr_id = $('.js-select-attr-edit').find(':selected').val() - 1;
            this.attr_name = $('.js-select-attr-edit').find(':selected')[0].text;
            $("#edit_category_unit").change(function(){
                that.cat_unit = $(this).children("option:selected").val();
            });
        },
        watch: {
            cat_unit(val) {
                this.rerenderOptions(val);
            },
        },

        methods: {
            new_attr: function () {
                if (this.attr_name.trim() === "") {

                } else {
                    this.attrs.push({
                        'id': this.attr_id,
                        'name': unitOptions[this.cat_unit][this.attr_id + 1] ? unitOptions[this.cat_unit][this.attr_id + 1] : this.attr_name.trim(),
                        'unit': this.attr_unit.trim(),
                        'is_required': this.is_required,
                        'to': this.attr_to.trim(),
                        'from':this.attr_from.trim(),
                        'step':this.attr_step.trim(),
                        'value':this.attr_value.trim(),
                    });

                    this.attr_id = "";
                    this.attr_name = "";
                    this.attr_unit = "";
                    this.is_required = false;
                    this.attr_to = "";
                    this.attr_from = "";
                    this.attr_step = "";
                    this.attr_value = "";
                }

            },
            rerenderOptions(val) {
                const selectElem = $("#edit_category_attribute");
                selectElem.empty();
                const selectOptions = Object.entries(unitOptions[val]);
                for (let i in selectOptions) {
                    if (!isNaN(+i)) {
                        const optionValue = selectOptions[i][0];
                        const optionLabel = selectOptions[i][1];
                        selectElem.append($("<option></option>")
                            .attr("value", optionValue).text(optionLabel));
                    }
                }
                edit_attr_changed();
            },
            add_files(response, file, fileList) {
                this.files.push(response.data[0]);
            },
            remove_files(file, fileList) {
                var that = this;

                that.files = _.remove(that.files, function(n) {
                    return n.id != file.id;
                });
                that.alsoFiles = _.remove(that.alsoFiles, function(n) {
                    return n.id != file.id;
                });
            },
            handleExceed(files, fileList) {
                this.$message.warning(`Невозможно загрузить ${files.length} файлов. Вы можете загрузить еще ${10 - fileList.length} файлов`);
            },
            beforeUpload(file) {
                const ALLOWED_EXTENSIONS = ['pdf'];
                const FILE_MAX_LENGTH = 5_000_000;
                const nameParts = file.name.split('.');
                if (ALLOWED_EXTENSIONS.indexOf(nameParts[nameParts.length - 1]) === -1) {
                    this.$message.warning(`Ошибка загрузки файла. Разрешенные форматы: PDF.`);
                    return false;
                }
                if (file.size > FILE_MAX_LENGTH) {
                    this.$message.warning(`Ошибка загрузки файла. Размер файла не должен превышать 5Мб`);
                    return false;
                }
                return true;
            },
        }
    });
    var clone = new Vue({
        el: '#clone-category',
        data: {
            cat_unit: '',
            attrs: [],
            attr_id: '',
            attr_name: '',
            attr_unit: '',
            is_required: false,
            attr_to: '',
            attr_from: '',
            attr_step: '',
            attr_value: '',
        },
        mounted: function() {
            var that = this;
            $('#attr_unit_clone').val('т');
            this.attr_unit = 'т';
            $('#attr_unit_clone').prop('disabled', true);
            this.attr_id = $('.js-select-attr-clone').find(':selected').val() - 1;
            this.attr_name = $('.js-select-attr-clone').find(':selected')[0].text;
            $("#clone_category_unit").change(function(){
                that.cat_unit = $(this).children("option:selected").val();
            });
        },
        watch: {
            cat_unit(val) {
                this.rerenderOptions(val);
            },
        },
        methods: {
            new_attr: function () {
                if (this.attr_name.trim() === "") {

                } else {
                    this.attrs.push({
                        'id': this.attr_id,
                        'name': unitOptions[this.cat_unit][this.attr_id + 1] ? unitOptions[this.cat_unit][this.attr_id + 1] : this.attr_name.trim(),
                        'unit': this.attr_unit.trim(),
                        'is_required': this.is_required,
                        'to': this.attr_to.trim(),
                        'from':this.attr_from.trim(),
                        'step':this.attr_step.trim(),
                        'value':this.attr_value.trim(),
                    });

                    this.attr_id = "";
                    this.attr_name = "";
                    this.attr_unit = "";
                    this.is_required = false;
                    this.attr_to = "";
                    this.attr_from = "";
                    this.attr_step = "";
                    this.attr_value = "";
                }
            },
            rerenderOptions(val) {
                const selectElem = $("#clone_category_attribute");
                selectElem.empty();
                const selectOptions = Object.entries(unitOptions[val]);
                for (let i in selectOptions) {
                    if (!isNaN(+i)) {
                        const optionValue = selectOptions[i][0];
                        const optionLabel = selectOptions[i][1];
                        selectElem.append($("<option></option>")
                            .attr("value", optionValue).text(optionLabel));
                    }
                }
                clone_attr_changed();
            },

        }
    });
    @endcan
    var view = new Vue({
        el: '#view-category',
        data: {
            attrs: [],
        },
    });
</script>


<meta name="csrf-token" content="{{ csrf_token() }}" />
<script>
    var CSRF_TOKEN = $('meta[name="csrf-token"]').attr('content');

    @can('manual_materials_edit')
    $('.js-select-attr-first').select2({
        language: 'ru',
        tags: true
    });
    $('.js-select-attr-edit').select2({
        language: 'ru',
        tags: true
    });
    $('.js-select-attr-clone').select2({
        language: 'ru',
        tags: true
    });

    function show_attrs_inputs(is_static)
    {
        if (is_static) {
            $('.not_static_attrs').hide();
            $('.static_attrs').show();
        } else {
            $('.not_static_attrs').show();
            $('.static_attrs').hide();
        }
    }

    function attr_changed() {
        var selectedId = $('.js-select-attr-first').find(':selected').val() - 1;

        if (selectedId === 0) {
            $('#attr_unit_create').val('т');
            attr_new.attr_unit = 'т';
            $('#attr_unit_create').prop('disabled', true);

            show_attrs_inputs(false);
        } else if (selectedId === 1) {
            $('#attr_unit_create').val('м3');
            attr_new.attr_unit = 'м3';
            $('#attr_unit_create').prop('disabled', true);

            show_attrs_inputs(false);

        } else if (selectedId === 2) {
            $('#attr_unit_create').val('м.п');
            attr_new.attr_unit = 'м.п';
            $('#attr_unit_create').prop('disabled', true);

            show_attrs_inputs(false);

        } else if (selectedId === 3) {
            $('#attr_unit_create').val('м2');
            attr_new.attr_unit = 'м2';
            $('#attr_unit_create').prop('disabled', true);

            show_attrs_inputs(false);
        } else {
            $('#attr_unit_create').val('');
            attr_new.attr_unit = '';
            $('#attr_unit_create').prop('disabled', false);
            show_attrs_inputs(true);
        }
        attr_new.attr_id = selectedId;
        attr_new.attr_name = $('.js-select-attr-first').find(':selected')[0].text;
    }

    function edit_attr_changed() {
        var selectedId = $('.js-select-attr-edit').find(':selected').val() - 1;
        if (selectedId === 0) {
            $('#attr_unit_edit').val('т');
            edit.attr_unit = 'т';
            $('#attr_unit_edit').prop('disabled', true);

        } else if (selectedId === 1) {
            $('#attr_unit_edit').val('м3');
            edit.attr_unit = 'м3';
            $('#attr_unit_edit').prop('disabled', true);

        } else if (selectedId === 2) {
            $('#attr_unit_edit').val('м.п');
            edit.attr_unit = 'м.п';
            $('#attr_unit_edit').prop('disabled', true);

        } else if (selectedId === 3) {
            $('#attr_unit_edit').val('м2');
            edit.attr_unit = 'м2';
            $('#attr_unit_edit').prop('disabled', true);

        } else {
            $('#attr_unit_edit').val('');
            edit.attr_unit = '';
            $('#attr_unit_edit').prop('disabled', false);
        }
        edit.attr_id = $('.js-select-attr-edit').find(':selected')[0].index;
        edit.attr_name = $('.js-select-attr-edit').find(':selected')[0].text;
    }

    function clone_attr_changed() {
        var selectedId = $('.js-select-attr-clone').find(':selected').val() - 1;
        if (selectedId === 0) {
            $('#attr_unit_clone').val('т');
            clone.attr_unit = 'т';
            $('#attr_unit_clone').prop('disabled', true);

        } else if (selectedId === 1) {
            $('#attr_unit_clone').val('м3');
            clone.attr_unit = 'м3';
            $('#attr_unit_clone').prop('disabled', true);

        } else if (selectedId === 2) {
            $('#attr_unit_clone').val('м.п');
            clone.attr_unit = 'м.п';
            $('#attr_unit_clone').prop('disabled', true);

        } else if (selectedId === 3) {
            $('#attr_unit_clone').val('м2');
            clone.attr_unit = 'м2';
            $('#attr_unit_clone').prop('disabled', true);

        } else {
            $('#attr_unit_clone').val('');
            clone.attr_unit = '';
            $('#attr_unit_clone').prop('disabled', false);
        }
        clone.attr_id = $('.js-select-attr-clone').find(':selected')[0].index;
        clone.attr_name = $('.js-select-attr-clone').find(':selected')[0].text;
    }
    @endcan
    function view_category(e) {
        $('#view_category_name').text(e.name);
        $('#view_category_unit').text(e.category_unit);
        @if(Auth::user()->is_su)
        $('#view_category_formula').text(e.formula);
        @endif

        if (!e.description) {
            $('#view_category_description').text('Нет описания');
        } else {
            $('#view_category_description').text(e.description);
        }

        @if(Auth::user()->is_su)
        if (!e.description) {
            $('#view_category_formula').text('Формула отсутствует');
        } else {
            $('#view_category_formula').text(e.formula);
        }
        @endif
        view.attrs = e.attributes;
    }
    @can('manual_materials_edit')

    function edit_category(e) {
        $('#edit_category_id').val(e.id);
        $('#edit_category_name').val(e.name);
        $('#edit_category_formula').val(e.formula);
        $('#edit_category_unit').selectpicker('val', e.category_unit);
        $('#edit_category_description').val(e.description);
        edit.files = e.documents;
        edit.attrs = e.attributes;
        edit.cat_unit = e.category_unit;
        edit.rerenderOptions(e.category_unit);
    }

    function clone_category(e) {
        $('#clone_category_name').val(e.name);
        $('#clone_category_formula').val(e.formula);
        $('#clone_category_unit').val(e.category_unit);
        $('#clone_category_description').val(e.description);
        clone.attrs = e.attributes;
        edit.cat_unit = e.category_unit;
        clone.rerenderOptions(e.category_unit);
    }

    function delete_category(e, id) {
        swal({
            title: 'Вы уверены?',
            text: "Категория будет удалена!",
            type: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#3085d6',
            cancelButtonColor: '#d33',
            cancelButtonText: 'Назад',
            confirmButtonText: 'Удалить'
        }).then((result) => {
            if(result.value) {
                $.ajax({
                    url:'{{ route("building::materials::category::delete") }}',
                    type: 'POST',
                    data: {
                        _token: CSRF_TOKEN,
                        category_id: id,
                    },
                    dataType: 'JSON',
                    success: function (data) {
                        console.log(data)
                        e.closest('tr').remove();
                    }
                });
            }
        });
    }

    @endcan
</script>

@endsection
