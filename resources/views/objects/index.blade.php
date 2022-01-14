 @extends('layouts.app')

@section('title', 'Объекты')

@section('url', route('objects::index'))

 @section('css_top')
     <style>
         [data-balloon],
         [data-balloon]:before,
         [data-balloon]:after {
             z-index: 9999;
         }
     </style>
 @endsection

@section('content')
<div class="row">
    <div class="col-md-12">
        <div class="card strpied-tabled-with-hover">
            <div class="fixed-table-toolbar toolbar-for-btn">
                <div class="fixed-search">
                    <form action="{{ route('objects::index') }}">
                        <input class="form-control" type="text" value="{{ Request::get('search') }}" name="search" placeholder="Поиск">
                    </form>
                </div>
            </div>

        @if(!$objects->isEmpty())
            <div class="table-responsive">
                <table class="table table-hover mobile-table">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Название</th>
                            <th>Адрес</th>
                            <th>Сокращенное наименование</th>
                            <th>Кадастровый номер</th>
                            <th class="text-right"></th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($objects as $object)
                        <tr style="cursor:default">
                            <td  data-label="ID">{{ $object->id }}</td>
                            <td  data-label="Название">{{ $object->name }}</td>
                            <td data-label="Адрес">{{ $object->address }}</td>
                            <td data-label="Сокращенное наименование">
                                @if($object->short_name)
                                    {{ $object->short_name }}
                                @else
                                    <button type="button" name="button" class="btn btn-link btn-warning btn-xs mn-0 pd-0" data-container="body"
                                            data-toggle="popover" data-placement="top" data-content="Заполните сокращенное наименование" data-trigger="hover">
                                        <i class="fa fa-info-circle"></i>
                                    </button>
                                @endif
                            </td>
                            <td data-label="Кадастровый номер">{{ $object->cadastral_number }}</td>
                            <td data-label="" class="td-actions text-right actions text-nowrap">
                                @can('objects_edit')
                                <button class="btn-success btn-link btn-xs padding-actions mn-0 btn edit-button"
                                        onclick="edit_object({{ $object }})" data-toggle="modal"
                                        data-target="#edit-object" data-balloon-pos="up" aria-label="Редактировать"

                                ><i class="fa fa-edit"></i>
                                </button>
                                @endcan
                                @if(!Auth()->user()->hasLimitMode(0))
                                <button  class="btn-info btn-link btn-xs padding-actions mn-0 btn"
                                        data-toggle="modal" data-target="#view-object{{ $object->id }}"
                                        data-balloon-pos="up" aria-label="Просмотр"

                                ><i class="fa fa-eye"></i>
                                </button>
                                @endif
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            @elseif(Request::has('search'))
                <p class="text-center">По вашему запросу ничего не найдено</p>
            @else
                <p class="text-center">В этом разделе пока нет ни одного объекта</p>
            @endif
            <div class="col-md-12 fix-pagination">
                <div class="right-edge">
                    <div class="page-container">
                        {{ $objects->appends(['search' => Request::get('search')])->links() }}
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@foreach($objects as $object)
<div class="modal fade bd-example-modal-lg show" id="view-object{{ $object->id }}" tabindex="-1" role="dialog" aria-labelledby="modal-search" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
       <div class="modal-content">
           <div class="modal-header">
               <h5 class="modal-title">Дополнительная информация</h5>
               <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                   <span aria-hidden="true">×</span>
               </button>
           </div>
           <div class="modal-body">
               <div class="row">
                   <div class="col-md-12">
                       @if(!$projects->where('object_id', $object->id)->isEmpty())
                       <div class="card strpied-tabled-with-hover" style="border:none">
                           <div class="table-responsive">
                               <table class="table table-hover">
                                   <caption style="caption-side: top">Связанные проекты</caption>
                                   <thead>
                                       <tr>
                                           <th>Контрагент</th>
                                           <th>Проект</th>
                                           <th>Статус проекта</th>
                                       </tr>
                                   </thead>
                                   <tbody>
                                       @foreach($projects->where('object_id', $object->id) as $project)
                                        <tr>
                                            <td>
                                                <a href="{{ route('contractors::card', $project->contractor_id) }}" class="table-link">
                                                    {{ $project->contractor_name }}
                                                </a>
                                            </td>
                                            <td>
                                                <a href="{{ route('projects::card', $project->id) }}" class="table-link">
                                                    {{ $project->name }}
                                                </a>
                                            </td>
                                            <td>Активен</td>
                                        </tr>
                                        @endforeach
                                   </tbody>
                               </table>
                           </div>
                       </div>
                       @else
                           <p class="text-center">Связанные проекты не найдены</p>
                       @endif
                   </div>
               </div>
               <div class="row">
                   <div class="col-md-12">
                       @if($object->getLastTenOperations()->count())
                       <div class="card strpied-tabled-with-hover" style="border:none">
                           <div class="table-responsive">
                               <table class="table table-hover">
                                   <caption style="caption-side: top">Последние топливные операции (10)</caption>
                                   <thead>
                                       <tr>
                                           <th>Номер топливной ёмкости</th>
                                           <th>Тип операции</th>
                                           <th>Объем, л</th>
                                           <th>Дата операции</th>
                                       </tr>
                                   </thead>
                                   <tbody>
                                       @foreach($object->getLastTenOperations() as $operation)
                                           <tr>
                                               <td>{{ $operation->fuel_tank_number }}</td>
                                               <td>
                                                   @if ($operation->type == 1)
                                                       <i class="fas fa-plus" title="Заправка"></i>
                                                   @elseif ($operation->type == 2)
                                                       <i class="fas fa-minus" title="Расход"></i>
                                                   @else
                                                       <i class="fas fa-edit" title="Ручное изменени"></i>
                                                   @endif
                                               </td>
                                               <td>{{ $operation->value }}</td>
                                               <td>{{ $operation->formatted_operation_date }}</td>
                                           </tr>
                                       @endforeach
                                   </tbody>
                               </table>
                               <a class="nav-link" href="{{ route('building::tech_acc::fuel_tank_operations.index', ['tank_number' => $object->fuel_tanks->pluck('tank_number')->toArray()]) }}">
                                   Посмотреть все операции по объекту
                               </a>
                           </div>
                       </div>
                       @else
                           <p class="text-center">Топливные записи не найдены</p>
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
@endforeach

<!--Модалка редактировать-->
@can('objects_edit')
<div class="modal fade bd-example-modal-lg show" id="edit-object" role="dialog" aria-labelledby="modal-search" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
       <div class="modal-content">
           <div class="modal-header">
               <h5 class="modal-title">Редактировать объект</h5>
               <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                   <span aria-hidden="true">×</span>
               </button>
           </div>
           <div class="modal-body">
               <div class="card border-0">
                   <div class="card-body">
                       <form id="form_edit_object" class="form-horizontal" action="{{ route('objects::update') }}" method="post">
                           @csrf
                           <input id="update_object_id" name="object_id" type="hidden">

                           <div class="form-group">
                               <div class="row">
                                   <label class="col-sm-3 col-form-label">Название<star class="star">*</star></label>
                                   <div class="col-sm-9">
                                       <input class="form-control" id="update_name" type="text" name="name" required maxlength="150">
                                   </div>
                               </div>
                           </div>
                           <div class="form-group">
                               <div class="row">
                                   <label class="col-sm-3 col-form-label">
                                       Сокращенное наименование
                                       <button type="button" name="button" class="btn btn-link btn-primary btn-xs mn-0 pd-0" data-container="body"
                                               data-toggle="popover" data-placement="top" data-content="Поле необходимо для возможности поиска по сокращенному наименованию">
                                           <i class="fa fa-info-circle"></i>
                                       </button>
                                   </label>
                                   <div class="col-sm-9">
                                       <input class="form-control" id="update_short_name" type="text" name="short_name" maxlength="500">
                                   </div>
                               </div>
                           </div>
                           <div class="form-group">
                               <div class="row">
                                   <label class="col-sm-3 col-form-label">Адрес объекта<star class="star">*</star></label>
                                   <div class="col-sm-9">
                                       <input class="form-control" id="update_address" type="text" name="address" required maxlength="250">
                                   </div>
                               </div>
                           </div>
                           <div class="form-group">
                               <div class="row">
                                   <label class="col-sm-3 col-form-label">Кадастровый номер</label>
                                   <div class="col-sm-9">
                                       <input class="form-control cadastral_number" pattern="[0-9]{2}:[0-9]{2}:[0-9]{6,7}:[0-9]{1,5}" id="update_cadastral_number" type="text" name="cadastral_number" minlength="14" maxlength="19">
                                   </div>
                               </div>
                           </div>
                           @if(Auth::user()->isProjectManager() or Auth::user()->isInGroup(43)/*8*/)
                               <div class="form-group" id="">
                                   <div class="row">
                                       <label class="col-sm-3 col-form-label">Отв. за мат. учет<star class="star">*</star></label>
                                       <div class="col-sm-9">
                                           <select id="resp_users_role_one" name="resp_user_role_one[]" multiple style="width:100%;">
                                           </select>
                                       </div>
                                   </div>
                               </div>
                           @endif
                           <div class="form-group" id="">
                               <div class="row">
                                   <label class="col-sm-3 col-form-label">Тип учета<star class="star">*</star></label>
                                   <div class="col-sm-9">
                                       <select id="material_accounting_type" name="material_accounting_type" style="width:100%;">
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
                <button type="submit" form="form_edit_object" class="btn btn-info">Сохранить</button>
           </div>
        </div>
    </div>
</div>
@endcan
@endsection

@section('js_footer')
<script src="//api-maps.yandex.ru/2.1/?lang=ru_RU&load=SuggestView&onload=onLoad"></script>
{{--<script type='text/javascript' src="https://rawgit.com/RobinHerbots/jquery.inputmask/3.x/dist/jquery.inputmask.bundle.js"></script>--}}
<script>
    // $('.cadastral_number').inputmask('Regex', {
    //     regex: "[0-9]{2}:[0-9]{2}:[0-9]{6,7}:[0-9]{1,5}"
    // });

    tooltip = $('<link>').attr('rel','stylesheet').attr('type','text/css').attr('href','css/object_tooltips.css');
    $(tooltip).appendTo('head');

    function objectsTooltip (){
        if ($(document).height() > $(window).height()) {
            $(tooltip).remove();
        }  else {
            $(tooltip).appendTo('head')
        }
    }

    objectsTooltip ();

    $(window).resize(function(){
        objectsTooltip ();
    });

    $('.cadastral_number').mask('00:00:000000Z:0ZZZZ', { translation: { 'Z': { pattern: /[0-9]/, optional: true } }, placeholder: "__:__:______:____" });


    function onLoad(ymaps) {
        var suggestView = new ymaps.SuggestView('update_address', {results: 5, offset: [0, 0]});
    }

    $('#resp_users_role_one').select2();
    $('#material_accounting_type').select2();

    function edit_object(data) {
        $('#resp_users_role_one').select2('destroy');
        $('#resp_users_role_one').find('option').remove();

        $('#material_accounting_type').select2('destroy');
        $('#material_accounting_type').find('option').remove();

        $('#update_object_id').val(data.id);
        $('#update_name').val(data.name);
        $('#update_address').val(data.address);
        $('#update_short_name').val(data.short_name);
        $('#update_cadastral_number').val(data.cadastral_number);

        $('#resp_users_role_one').select2({
            language: "ru",
            ajax: {
                url: '{{ route('tasks::get_users') }}',
                dataType: 'json',
                delay: 250,
            }
        });

        $.each(data.resp_users, function(index, resp_user) {
            var user = $("<option selected='selected'></option>").val(resp_user.user.id).text(resp_user.user.full_name);
            $('#resp_users_role_one').append(user).trigger('change');
        });

        $('#material_accounting_type').select2({
            language: "ru",
            ajax: {
                url: '{{ route('material.material-accounting-types.lookup-list') }}',
                dataType: 'json',
                delay: 250,
            },
            minimumResultsForSearch: Infinity
        });

        var material_accounting_type = $("<option selected='selected'></option>").val(data.material_accounting_type.id).text(data.material_accounting_type.name);
        $('#material_accounting_type').append(material_accounting_type).trigger('change');
    }

    if (window.location.href.indexOf('set_short_name') !== -1) {
        $('.edit-button').click();
        setTimeout(() => {$('#update_short_name').focus()}, 1000);
    }

</script>

@endsection
