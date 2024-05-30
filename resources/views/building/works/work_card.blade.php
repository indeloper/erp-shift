@extends('layouts.app')

@section('title', $work->name)

@section('url', route('building::works::card', $work->id))

@section('css_top')
    <style>
        @media (min-width: 1000px) {
            .main-table-responsive {
                overflow-x: auto;
                overflow-y: hidden;
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
                    <a href="{{ route('building::works::index') }}" class="table-link">Работы</a>
                </li>
                <li class="breadcrumb-item active" aria-current="page">
                    {{ $work->name }} @if($work->is_copied) (Копия) @endif
                </li>
            </ol>
        </div>
    </div>
</div>
<div class="row">
    <div class="col-md-12">
        <div class="card">
            <div class="card-header">
                <h6 style="font-weight:400;">
                    Поиск материалов по атрибутам
                </h6>
            </div>
            <div class="card-body">
                <form id="search_by_attrs">
                    @csrf
                    <input type="hidden" name="work_id" value="{{ $work->id }}">
                    <div class="row">
                        <div class="col-md-4">
                            <div class="form-group">
                                <select id="category" name="category_id" class="selectpicker" data-title="Выберите категорию" data-style="btn-default btn-outline" data-menu-style="dropdown-blue" onchange="get_category_attr()" required>
                                    @foreach($categories as $category)
                                        <option value="{{ $category->id }}">{{ $category->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <select id="attr" name="attribute_id" class="selectpicker" data-title="Выберите атрибут" data-style="btn-default btn-outline" data-menu-style="dropdown-blue" disabled onchange="get_attr_values()" required>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group">
                                <select id="values" class="selectpicker" name="values[]" data-title="Выберите значение" data-style="btn-default btn-outline" data-menu-style="dropdown-blue" multiple disabled required onchange="show_hide_submit_button()">
                                </select>
                            </div>
                        </div>
                        <div class="col-md-1" style="padding:0 0 0 20px;">
                            <div class="form-group">
                                <button id="search_button" type="submit" class="btn btn-link btn-check d-none" style="color: limegreen;">
                                    <i class="pe-7s-plus"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                </form>
                <div class="row">
                    <div class="col-md-12">
                        <div class="bootstrap-tagsinput">
                            <div id="parameters">
                                <!-- <span class="badge badge-azure">Атрибут: Значение<span data-role="remove" class="badge-remove-link" onclick=""></span></span> -->
                            </div>
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
            <div class="card-header">
                <h4 class="card-title" style="margin-bottom:15px">Список материалов @if($work->is_copied) оригинальной работы <a target="_blank" href="{{ route('building::works::card', $work->parent->parent_work->id) }}">{{ $work->parent->parent_work->name }}</a>@endif</h4>
            </div>
            <div class="fixed-table-toolbar toolbar-for-btn">
                <div class="row">
                    <div class="col-md-6">
                        <div class="fixed-search">
                            <form>
                                <input class="form-control" type="text" value="{{ Request::get('search') }}" name="search" placeholder="Поиск">
                            </form>
                        </div>
                    </div>
                    @can('manual_works_edit')
                    <div class="col-md-6">
                        <div class="pull-right">
                            @if(!$work->is_copied)
                                <a href="{{ route('building::works::edit', $work->id) }}" class="btn btn-outline btn-sm edit-btn" style="margin-top: 3px">
                                    <i class="glyphicon fa fa-pencil-square-o"></i>
                                    Редактировать
                                </a>
                            @endif
                        </div>
                    </div>
                    @endcan
                </div>
            </div>
            <div class="table-responsive main-table-responsive">
                <table class="table table-hover mobile-table">
                    <thead>
                        <tr>
                            <th class="text-left">Название материала</th>
                            <th class="text-left">Категория</th>
                            <th class="text-center">Цена купли/продажи</th>
                            <th class="text-center">Цена за месяц использования</th>
                            <th class="text-right">Действия</th>
                        </tr>
                    </thead>
                    <tbody id="put_here">
                        @foreach($materials as $material)
                        <tr style="cursor:default" >
                            <td  data-label="Название материала" class="text-left">{{ $material->name }}</td>
                            <td data-label="Категория" class="text-left">{{ $material->category_name }}</td>
                            <td data-label="Цена купли/продажи" class="text-center">{{ $material->buy_cost }} ₽</td>
                            <td data-label="Цена за месяц использования" class="text-center">{{ $material->use_cost }} ₽</td>
                            <td data-label="" class="text-right actions">
                                <button type="button" rel="tooltip" onclick="show_material_info({{ $material->manual_material_id }})" class="btn btn-link btn-xs btn-info btn-space" data-toggle="modal" data-target="#view-material" data-original-title="Просмотр">
                                    <i class="fa fa-eye"></i>
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
<!-- Модалки -->
<!-- Просмотр материала-->
<div class="modal fade bd-example-modal-lg show" id="view-material" role="dialog" aria-labelledby="modal-search" style="display: none;">
    <div class="modal-dialog modal-lg" role="document">
       <div class="modal-content">
           <div class="modal-header">
               <h5 class="modal-title" id="material_name_show">Шпунт Ларсена Л4</h5>
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
                                        <div class="table-responsive ">
                                            <table class="table mobile-table">
                                                <thead>
                                                    <tr>
                                                        <th>Параметр</th>
                                                        <th class="text-center">Значение</th>
                                                        <th class="text-center">Ед.измерения</th>
                                                    </tr>
                                                </thead>
                                                <tbody id="attributes_place_show">
                                                    <tr>
                                                        <td data-label="Параметр">Длина</td>
                                                        <td data-label="Значение" class="text-center">10</td>
                                                        <td data-label="Ед.измерения" class="text-center">м</td>
                                                    </tr>
                                                    <tr>
                                                        <td>Страна</td>
                                                        <td class="text-center">Россия</td>
                                                        <td class="text-center"></td>
                                                    </tr>
                                                    <tr>
                                                        <td>Материал</td>
                                                        <td class="text-center">Сталь</td>
                                                        <td></td>
                                                    </tr>
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
                                   <p class="form-control-static" id="description_show">Описание материала</p>
                               </div>
                           </div>
                       </div>
                       <div class="row" style="margin-top:20px">
                           <div class="col-sm-12">
                               <div class="form-group">
                                   <label>Паспорт материала</label><br>
                                   <a id="passport_show">Отсутствует</a>
                               </div>
                           </div>
                       </div>
                       <!-- <div class="row" style="margin-top:20px">
                           <div class="col-sm-12">
                               <div class="form-group">
                                   <label>Группа работ</label><br>
                                   <p id="work_group" class="form-control-static">Шпунтовые работы</p>
                               </div>
                           </div>
                       </div> -->
                       <div class="accordions" id="accordion" style="margin-top:20px">
                           <div class="card">
                               <div class="card-header">
                                   <h4 class="card-title">
                                       <a data-target="#collapseOne" href="#" data-toggle="collapse">
                                           Список работ
                                           <b class="caret"></b>
                                       </a>
                                   </h4>
                               </div>
                               <div id="collapseOne" class="card-collapse collapse">
                                   <div class="card-body">
                                       <div class="row">
                                            <div class="col-md-12">
                                                <div class="table-responsive">
                                                    <table class="table mobile-table">
                                                        <thead>
                                                            <tr>
                                                                <th>Название</th>
                                                                <th class="text-center">Ед. измерения</th>
                                                                <th class="text-center">ЦЕНА ЗА ЕД., РУБ</th>
                                                                <th class="text-center">НДС, %</th>
                                                                <th class="text-center">СРОК ИСПОЛНЕНИЯ ЗА ЕД., ДНЕЙ</th>
                                                            </tr>
                                                        </thead>
                                                        <tbody id="material_works_show">
                                                            <tr>
                                                                <td data-label="Название">Вибропогружение</td>
                                                                <td data-label="Ед. измерения" class="text-center">штука</td>
                                                                <td data-label="ЦЕНА ЗА ЕДИНИЦУ, РУБ" class="text-center">10 000</td>
                                                                <td data-label="НДС, %" class="text-center">20</td>
                                                                <td data-label="СРОК ИСПОЛНЕНИЯ ЗА ЕД., ДНЕЙ" class="text-center">0,3</td>
                                                            </tr>
                                                        </tbody>
                                                    </table>
                                                </div>
                                            </div>
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

<!-- Кнопка для параметров поиска -->
<div class="d-none">
    <span class="badge badge-azure">Атрибут: Значение<span data-role="remove" class="badge-remove-link"></span></span>
</div>

@endsection

@section('js_footer')
<meta name="csrf-token" content="{{ csrf_token() }}" />

<script>
var CSRF_TOKEN = $('meta[name="csrf-token"]').attr('content');

function show_material_info(mat_id) {
    $.ajax({
        url:'{{ route("building::works::get_materials") }}',
        type: 'POST',
        data: {
            _token: CSRF_TOKEN,
            mat_id: mat_id,
        },
        dataType: 'JSON',
        success: function (data) {
            $('#material_name_show').html(data.name);

            var tr_for_parameters = '';

            $.each(data.parameters, function (index, parameter) {
                var name = parameter.name;
                var value = parameter.value;
                var unit = parameter.unit;
                tr_for_parameters += "<tr>" + "<td>" + name + "</td><td class=\"text-center\">" + value + "</td><td class=\"text-center\">" + unit + "</td>" + "</tr>";
            });
            $("#attributes_place_show").html(tr_for_parameters);

            $('#description_show').html(data.description);
            if (data.passport_show != null) {
                $('#passport_show').html(data.passport_file);
                $('#passport_show').attr('href', '#');
            }

            var tr_for_works = '';
            $.each(data.work_relations, function (index, relation) {
                var name = relation.name;
                var unit = relation.unit;
                var price = relation.price_per_unit;
                var nds = relation.nds;
                var days = relation.unit_per_days;
                tr_for_works += "<tr>" + "<td>" + name + "</td><td class=\"text-center\">" + unit + "</td><td class=\"text-center\">" + price + "</td>" + "<td class=\"text-center\">" + nds + "</td>" + "<td class=\"text-center\">" + days + "</td>" + "</tr>";
            });
            $("#material_works_show").html(tr_for_works);
        }
    });
}

function get_category_attr() {
    var category_id = $('#category').val();
    $.ajax({
        url:'{{ route("building::works::get_attributes") }}',
        type: 'POST',
        data: {
            _token: CSRF_TOKEN,
            id: category_id,
        },
        dataType: 'JSON',
        success: function (data) {
            $("#attr").removeAttr('disabled');
            var option = '';
            $.each(data, function (index, value) {
                var name = value.name;
                var id = value.id;
                var unit = value.unit;
                option += "<option value=\"" + id + "\">" + name + ', ' + unit + "</option>";
            });
            $("#attr").html(option);
            $("#attr").selectpicker('refresh');
        }
    });
}

function get_attr_values() {
    var attr_id = $('#attr').val();
    var category_id = $('#category').val();

    $.ajax({
        url:'{{ route("building::works::get_values") }}',
        type: 'POST',
        data: {
            _token: CSRF_TOKEN,
            id: attr_id,
            category_id: category_id
        },
        dataType: 'JSON',
        success: function (data) {
            $("#values").removeAttr('disabled');
            var option = '';
            $.each(data, function (index, value) {
                var values = value;
                option += "<option value=\"" + values + "\">" + values + "</option>";
            });
            $("#values").html(option);
            $("#values").selectpicker('refresh');
        }
    });
}

function show_hide_submit_button() {
    var value = $('#values').val();

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

function print_results(data)
{
    var results_tr = '';

    $.each(data, function(key, value) {
        var mat_name = value.name;
        var mat_category = value.category_name;
        var mat_id = value.id;
        var mat_buy_cost = value.buy_cost;
        var mat_use_cost = value.use_cost;

        results_tr += "<tr>\n" +
            "                            <td class=\"text-left\">" + mat_name + "</td>\n" +
            "                            <td class=\"text-left\">" + mat_category + "</td>\n" +
            "                            <td class=\"text-center\">" + mat_buy_cost + "</td>\n" +
            "                            <td class=\"text-center\">" + mat_use_cost + "</td>\n" +
            "                            <td class=\"text-right \">\n" +
            "                                <button type=\"button\" rel=\"tooltip\" onclick=\"show_material_info(" + mat_id + ")\" class=\"btn btn-link btn-xs btn-info btn-space\" data-toggle=\"modal\" data-target=\"#view-material\" data-original-title=\"Просмотр\">\n" +
            "                                    <i class=\"fa fa-eye\"></i>\n" +
            "                                </button>\n" +
            "                            </td>\n" +
            "                        </tr>";
    });

    return results_tr;
}

$(document).ready(function () {
    var request = {};
    request.category_id = [];
    request.attr_id = [];
    request.values = [];

    $("#search_by_attrs").submit(function(e){
        e.preventDefault(e);

        var category_name = $('#category option:selected').text();
        var category_id = $('#category').val();
        var attr_name = $('#attr option:selected').text();
        var attr_id = $('#attr').val();
        var values = $('#values').val();
        var result = '';

        if ($('.badge').length >= 2) {
            var badge = $('#parameters').find($("[category_id=" + category_id + "][attr_id="+ attr_id + "]"));

            $.each(badge, function(key, item) {
                var old_values = $(item).attr('values').split(',');
                $.each(old_values, function (index, value) {
                    values.push(value);
                });

                $(item).closest('.badge').remove();
            });

            function unique(array) {
                return $.grep(array, function(el, index) {
                    return index === $.inArray(el, array);
                });
            }

            result = category_name + '. ' + attr_name + ': ' + unique(values);

            var button = "<span class=\"badge badge-azure\">" + result + "<span data-role=\"remove\" class=\"badge-remove-link\" category_id=\"" + category_id + "\" attr_id=\"" + attr_id + "\" values=\"" + unique(values) + "\"></span></span>"

            $('#parameters').append(button);
        } else {
            result = category_name + '. ' + attr_name + ': ' + values;

            var button = "<span class=\"badge badge-azure\">" + result + "<span data-role=\"remove\" class=\"badge-remove-link\" category_id=\"" + category_id + "\" attr_id=\"" + attr_id + "\" values=\"" + values + "\"></span></span>"

            $('#parameters').append(button);
        }

        //данные для пост в массиве
        request.category_id.push($('#category').val());
        request.attr_id.push($('#attr').val());
        $.each(values, function(key, value) {
            request.values.push(value);
        });

        $.ajax({
            url:'{{ route("building::works::search_by_attributes") }}',
            type: 'POST',
            data: {
                _token: CSRF_TOKEN,
                category_id: request.category_id,
                attr_id: request.attr_id,
                values: request.values
            },
            dataType: 'JSON',
            success: function (data) {
                results_tr = print_results(data);

                $('#put_here').html(results_tr);

                $('#search_button').attr('disabled', 'disabled');
            }
        });

        $('#attr').selectpicker('refresh');
        $('#values option').remove();
        $('#values').attr('disabled', 'disabled');
        $('#values').selectpicker('refresh');
    });

    jQuery(document).on('click', '.badge-remove-link', function() {
        function f1(item, level = 0){
            if (item instanceof Array){
                return (level > 0 ? 1 : 0) + item.map(function(value, index){
                    return f1(value, level + 1)
                }).reduce((a, b) => a + b , 0)
            }
            return 0;
        }

        Array.prototype.diff = function (arr) {
            var mergedArr = this.concat(arr);

            return mergedArr.filter(function (e) {
                return mergedArr.indexOf(e) === mergedArr.lastIndexOf(e);
            });
        };

        var attr_id = $(this).attr('attr_id').split('');
        var values = $(this).attr('values').split(',');

        var request_values = '';

        if (f1(request.values) === 0) {
            var new_values = values.diff(request.values);
        } else {
            $.each(request.values, function(key, value) {
                request_values += value.join(' ');
                request_values += ' ';
            });

            var new_values = values.diff($.trim(request_values).split(' '));
        }

        var new_attrs = attr_id.diff(request.attr_id);
        request.values = new_values;
        request.attr_id = new_attrs;
        request.from = 'refresh';

        jQuery(this).closest('.badge').remove();

        if ($('.badge').length >= 2) {
            $.ajax({
                url:'{{ route("building::works::search_by_attributes") }}',
                type: 'POST',
                data: {
                    _token: CSRF_TOKEN,
                    category_id: request.category_id,
                    attr_id: request.attr_id,
                    values: request.values,
                    from: request.from
                },
                dataType: 'JSON',
                success: function (data) {
                    results_tr = print_results(data);

                    $('#put_here').html(results_tr);

                    $('#search_button').attr('disabled', 'disabled');
                }
            });
        } else {
            $.ajax({
                url:'{{ route("building::works::get_all_materials") }}',
                type: 'POST',
                data: {
                    _token: CSRF_TOKEN,
                    id: {{ $work->id }}
                },
                dataType: 'JSON',
                success: function (data) {
                    results_tr = print_results(data);

                    $('#put_here').html(results_tr);

                    $('#search_button').attr('disabled', 'disabled');
                }
            });
        }
    });
});

</script>

@endsection
