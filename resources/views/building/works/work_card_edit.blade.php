@extends('layouts.app')

@section('title', 'Изменение работы')

@section('url', route('building::works::edit', $work->id))

@section('content')
<div class="row">
    <div class="col-md-12">
        <div aria-label="breadcrumb" role="navigation">
            <ol class="breadcrumb">
                <li class="breadcrumb-item">
                    <a href="{{ route('building::works::index') }}" class="table-link">Работы</a>
                </li>
                <li class="breadcrumb-item">
                    <a href="{{ route('building::works::card', $work->id) }}" class="table-link">{{ $work->name }}</a>
                </li>
                <li class="breadcrumb-item active" aria-current="page">Редактирование</li>
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
                <form id="search_by_attrs" novalidate="novalidate">
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
            <div class="fixed-table-toolbar toolbar-for-btn">
                <div class="row">
                    <div class="col-md-6">
                        <div class="fixed-search">
                            <form>
                                <input class="form-control" type="text" value="{{ Request::get('search') }}" name="search" placeholder="Поиск">
                                <input type="hidden" value="{{ Request::get('show_all') }}" name="show_all">
                            </form>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="right-edge">
                            <div class="page-container">
                                @if(Request::get('show_all') == 1)
                                <a class="btn btn-sm show-all" href="{{ route('building::works::edit', [$work->id, 'show_all' => false]) }}" id="show_checked">
                                    Показать только выбранные
                                </a>
                                @else
                                <a class="btn btn-sm show-all" href="{{ route('building::works::edit', [$work->id, 'show_all' => true]) }}" id="show_all">
                                    Показать все материалы
                                </a>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="table-responsive">
                <table class="table table-hover mobile-table">
                    <thead>
                    <tr>
                        <th class="bs-checkbox " style="width: 36px; " data-field="state">
                            <div class="th-inner ">
                                <input id="check_all" type="checkbox">
                            </div>
                            <div class="fht-cell"></div>
                        </th>
                        <th class="text-left">Название материала</th>
                        <th class="text-left">Категория</th>
                        <th class="text-left">Цена купли/продажи</th>
                        <th class="text-left">Цена за месяц использования</th>
                        <th class="text-right">Действия</th>
                    </tr>
                    </thead>
                    <tbody id="put_here">
                    @foreach($materials as $material)
                        <tr>
                            <td data-label="Выбрать материал" class="bs-checkbox mobile-check">
                                <input data-index="1" name="material_checks[]" value="{{ $material->id }}" onchange="select_material({{ $material->id }}, {{ $work->id }}, this)" type="checkbox" class="select-item check_material" @if(isset($material->work_relations[0])) checked @endif>
                            </td>
                            <td data-label="Название материала" class="text-left">{{ $material->name }}</td>
                            <td data-label="Категория" class="text-left">{{ $material->category_name }}</td>
                            <td data-label="Цена купли/продажи" class="text-left">{{ $material->buy_cost }} ₽</td>
                            <td data-label="Цена за месяц использования" class="text-left">{{ $material->use_cost }} ₽</td>
                            <td class="text-right actions">
                                <button type="button" rel="tooltip" onclick="show_material_info({{ $material }})" class="btn btn-link btn-xs btn-info btn-space" data-toggle="modal" data-target="#view-material" data-original-title="Просмотр">
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
               <h5 class="modal-title" id="material_name"></h5>
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
                                                <tbody id="attributes_place_show">

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
                                   <p class="form-control-static" id="description_show"></p>
                               </div>
                           </div>
                       </div>
                       <div class="row" style="margin-top:20px">
                           <div class="col-sm-12">
                               <div class="form-group">
                                   <label>Паспорт материала</label><br>
                                   <a href="#">Отсутствует</a>
                               </div>
                           </div>
                       </div>
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
                                                <div class="table-full-width">
                                                    <table class="table">
                                                        <thead>
                                                            <tr>
                                                                <th>Название</th>
                                                                <th class="text-center">Ед. измерения</th>
                                                                <th class="text-center">ЦЕНА ЗА ЕДИНИЦУ, РУБ</th>
                                                                <th class="text-center">НДС, %</th>
                                                                <th class="text-center">СРОК ИСПОЛНЕНИЯ ЗА ЕД., ДНЕЙ</th>
                                                            </tr>
                                                        </thead>
                                                        <tbody id="works_place_show">

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
@endsection

@section('js_footer')
<meta name="csrf-token" content="{{ csrf_token() }}" />

<script>
    var materials_id = {{ $materials->pluck('id') }};
    var edit = "{{ app('request')->input('show_all') ? app('request')->input('show_all') : '' }}";

$('.select-item').click(function(){
    if ($(this).is(':checked')){
        $(this).parents('tr').addClass('tr-selected');
    }
});

$('#check_all').click(function(){
    if ($(this).is(':checked')){
        $('td').addClass('tr-selected');
    }
});

var CSRF_TOKEN = $('meta[name="csrf-token"]').attr('content');

var work = {{ $work->id }};

$('#check_all').on('change', function () {
    if($('#check_all').is(':checked')) {
        $('.check_material').prop('checked', true).trigger('change');
    }
    else {
        $('.check_material').prop('checked', false).trigger('change');
    }

});

function select_material(manual_material_id, manual_work_id, e) {
    $.ajax({
        url:'{{ route("building::works::select_material") }}',
        type: 'POST',
        data: {
            _token: CSRF_TOKEN,
            manual_material_id: manual_material_id,
            manual_work_id: manual_work_id,
            is_checked: $(e)[0].checked
        },
        dataType: 'JSON'
    });
}

function show_material_info(material) {
    $('#material_name').html(material.name);
    var tr_for_parameters = '';
    var tr_for_works = '';

    $.each(material.parameters, function (index, parameter) {
        var name = parameter.name;
        var value = parameter.value;
        var unit = parameter.unit;
        tr_for_parameters += "<tr>" + "<td data-label='Параметр'>" + name + "</td><td data-label='Значение' class=\"text-center\">" + value + "</td><td data-label='Единица измерения' class=\"text-center\">" + unit + "</td>" + "</tr>";
    });
    $("#attributes_place_show").html(tr_for_parameters);

    $('#description_show').html(material.description);

    $.each(material.work_relations, function (index, work) {
        var name = work.name;
        var unit = work.unit;
        var price_per_unit = work.price_per_unit;
        var nds = work.nds;
        var unit_per_days = work.unit_per_days;

        tr_for_works +=
            "<tr>" +
            "<td>" + name + "</td>" +
            "<td class=\"text-center\">" + unit + "</td>" +
            "<td class=\"text-center\">" + price_per_unit + "</td>" +
            "<td class=\"text-center\">" + nds + "</td>" +
            "<td class=\"text-center\">" + unit_per_days + "</td>" +
            "</tr>";
    });

    $('#works_place_show').html(tr_for_works);
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

                if (unit.length !== 0) {
                    option += "<option value=\"" + id + "\">" + name + ', ' + unit + "</option>";
                } else {
                    option += "<option value=\"" + id + "\">" + name + "</option>";
                }
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
            category_id: category_id,
            materials_id: materials_id,
            edit: edit
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

$(document).ready(function () {
    var request = {};
    request.attr_id = [];
    request.values = [];

    $("#search_by_attrs").submit(function(e){
        e.preventDefault(e);

        $("#category").attr('disabled', 'disabled');
        $("#category").selectpicker('refresh');

        var category_name = $('#category option:selected').text();
        var attr_name = $('#attr option:selected').text();
        var attr_id = $('#attr').val();
        var values = $('#values').val();
        var result = '';

        if ($('.badge').length >= 1) {
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

            result = category_name + '. ' + attr_name + ': ' + values;

            var button = "<span class=\"badge badge-azure\">" + result + "<span data-role=\"remove\" class=\"badge-remove-link\" attr_id=\"" + attr_id + "\" values=\"" + values + "\"></span></span>"

            $('#parameters').append(button);
        } else {
            result = category_name + '. ' + attr_name + ': ' + values;

            var button = "<span class=\"badge badge-azure\">" + result + "<span data-role=\"remove\" class=\"badge-remove-link\" attr_id=\"" + attr_id + "\" values=\"" + values + "\"></span></span>"

            $('#parameters').append(button);

            request.attr_id.push(attr_id);
            request.values.push(values);
        }

        $.ajax({
            url:'{{ route("building::works::search_by_attributes") }}',
            type: 'POST',
            data: {
                _token: CSRF_TOKEN,
                attr_id: request.attr_id,
                values: request.values,
                edit: edit,
                materials_id: materials_id
            },
            dataType: 'JSON',
            success: function (data) {
                var results_tr = '';

                if (data.length === 0) {
                    results_tr = "<tr>\n" +
                        "                            <td>\n" +
                        "                            </td>\n" +
                        "                            <td class=\"text-left\">По вашему запросу ничего не найдено</td>\n" +
                        "                            <td class=\"text-left\"></td>\n" +
                        "                            <td class=\"text-left\"></td>\n" +
                        "                            <td class=\"text-left\"></td>\n" +
                        "                            <td class=\"text-right\">\n" +
                        "                            </td>\n" +
                        "                        </tr>";
                } else {
                    $.each(data, function(key, value) {
                        var mat_name = value.name;
                        var mat_category = value.category_name;
                        var mat_id = value.id;
                        var mat_buy_cost = value.buy_cost;
                        var mat_use_cost = value.use_cost;

                        results_tr += "<tr>\n" +
                            "                                <td class=\"bs-checkbox\">\n" +
                            "                                    <input data-index=\"1\" name=\"material_checks[]\" value=\"" + mat_id + "\" onchange=\"select_material(" + mat_id + ", {{ $work->id }}, this)\" type=\"checkbox\" class=\"select-item check_material\">\n" +
                            "                                </td>\n" +
                            "                                <td class=\"text-left\">" + mat_name + "</td>\n" +
                            "                                <td class=\"text-left\">" + mat_category + "</td>\n" +
                            "                                <td class=\"text-left\">" + mat_buy_cost + " ₽</td>\n" +
                            "                                <td class=\"text-left\">" + mat_use_cost + " ₽</td>\n" +
                            "                                <td class=\"text-right \">\n" +
                            "                                    <button id=\"show" + key + "\" type=\"button\" rel=\"tooltip\" class=\"btn btn-link btn-xs btn-info btn-space\" data-toggle=\"modal\" data-target=\"#view-material\" data-original-title=\"Просмотр\">\n" +
                            "                                        <i class=\"fa fa-eye\"></i>\n" +
                            "                                    </button>\n" +
                            "                                </td>\n" +
                            "                            </tr>";
                    });
                }

                $('#put_here').html(results_tr);

                $.each(data, function(key, value) {
                    var mat = JSON.stringify(value);

                    $("#show" + key).attr("onclick", "show_material_info(" + mat + ")");
                });

                $('#search_button').attr('disabled', 'disabled');
            }
        });

        $('#attr').selectpicker('refresh');
        $('#values').selectpicker('refresh');
    });

    jQuery(document).on('click', '.badge-remove-link', function() {
        var attr_id = $(this).attr('attr_id');

        indexToRemove = request.attr_id.indexOf(attr_id);

        request.values.splice(indexToRemove, 1);
        request.attr_id.splice(indexToRemove, 1);

        $(this).closest('.badge').remove();

        if ($('.badge').length >= 1) {
            $.ajax({
                url:'{{ route("building::works::search_by_attributes") }}',
                type: 'POST',
                data: {
                    _token: CSRF_TOKEN,
                    attr_id: request.attr_id,
                    values: request.values,
                    edit: edit,
                    materials_id: materials_id
                },
                dataType: 'JSON',
                success: function (data) {
                    var results_tr = '';

                    if (data.length === 0) {
                        results_tr = "<tr>\n" +
                            "                            <td>\n" +
                            "                            </td>\n" +
                            "                            <td class=\"text-left\">По вашему запросу ничего не найдено</td>\n" +
                            "                            <td class=\"text-left\"></td>\n" +
                            "                            <td class=\"text-left\"></td>\n" +
                            "                            <td class=\"text-left\"></td>\n" +
                            "                            <td class=\"text-right\">\n" +
                            "                            </td>\n" +
                            "                        </tr>";
                    } else {
                        $.each(data, function(key, value) {
                            var mat_name = value.name;
                            var mat_category = value.category_name;
                            var mat_id = value.id;
                            var mat_buy_cost = value.buy_cost;
                            var mat_use_cost = value.use_cost;

                            results_tr += "<tr>\n" +
                                "                                <td class=\"bs-checkbox\">\n" +
                                "                                    <input data-index=\"1\" name=\"material_checks[]\" value=\"" + mat_id + "\" onchange=\"select_material( \"" + mat_id + "\", {{ $work->id }}, this)\" type=\"checkbox\" class=\"select-item check_material\">\n" +
                                "                                </td>\n" +
                                "                                <td class=\"text-left\">" + mat_name + "</td>\n" +
                                "                                <td class=\"text-left\">" + mat_category + "</td>\n" +
                                "                                <td class=\"text-left\">" + mat_buy_cost + " ₽</td>\n" +
                                "                                <td class=\"text-left\">" + mat_use_cost + " ₽</td>\n" +
                                "                                <td class=\"text-right \">\n" +
                                "                                    <button id=\"show" + key + "\" type=\"button\" rel=\"tooltip\" class=\"btn btn-link btn-xs btn-info btn-space\" data-toggle=\"modal\" data-target=\"#view-material\" data-original-title=\"Просмотр\">\n" +
                                "                                        <i class=\"fa fa-eye\"></i>\n" +
                                "                                    </button>\n" +
                                "                                </td>\n" +
                                "                            </tr>";
                        });
                    }

                    $('#put_here').html(results_tr);

                    $.each(data, function(key, value) {
                        var mat = JSON.stringify(value);

                        $("#show" + key).attr("onclick", "show_material_info(" + mat + ")");
                    });

                    $('#search_button').attr('disabled', 'disabled');
                }
            });
        } else {
            $("#category").removeAttr('disabled', 'disabled');
            $("#category").selectpicker('refresh');

            var phpMaterials = {!! json_encode($materials) !!};
            var results_tr = '';

            $.each(phpMaterials, function(key, value) {
                var mat_name = value.name;
                var mat_category = value.category_name;
                var mat_id = value.id;
                var mat_buy_cost = value.buy_cost;
                var mat_use_cost = value.use_cost;

                results_tr += "<tr>\n" +
                    "                                <td class=\"bs-checkbox\">\n" +
                    "                                    <input data-index=\"1\" name=\"material_checks[]\" value=\"" + mat_id + "\" onchange=\"select_material( \"" + mat_id + "\", {{ $work->id }}, this)\" type=\"checkbox\" class=\"select-item check_material\">\n" +
                    "                                </td>\n" +
                    "                                <td class=\"text-left\">" + mat_name + "</td>\n" +
                    "                                <td class=\"text-left\">" + mat_category + "</td>\n" +
                    "                                <td class=\"text-left\">" + mat_buy_cost + " ₽</td>\n" +
                    "                                <td class=\"text-left\">" + mat_use_cost + " ₽</td>\n" +
                    "                                <td class=\"text-right \">\n" +
                    "                                    <button id=\"show" + key + "\" type=\"button\" rel=\"tooltip\" class=\"btn btn-link btn-xs btn-info btn-space\" data-toggle=\"modal\" data-target=\"#view-material\" data-original-title=\"Просмотр\">\n" +
                    "                                        <i class=\"fa fa-eye\"></i>\n" +
                    "                                    </button>\n" +
                    "                                </td>\n" +
                    "                            </tr>";
            });

            $('#put_here').html(results_tr);

            $.each(phpMaterials, function(key, value) {
                var mat = JSON.stringify(value);

                $("#show" + key).attr("onclick", "show_material_info(" + mat + ")");
            });

            $('#search_button').attr('disabled', 'disabled');
        }
    });
});

</script>

@endsection
