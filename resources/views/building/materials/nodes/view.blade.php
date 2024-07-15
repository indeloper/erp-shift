@extends('layouts.app')

@section('title', $node_category->name)

@section('url', route('building::nodes::category::view', $node_category->id))

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

        @media (min-width: 2500px) and (max-width: 3600px)  {
            .tooltip {
                left:35px!important;
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
                        <a href="{{ route('building::nodes::index') }}" class="table-link">Типовые узлы</a>
                    </li>
                    <li class="breadcrumb-item active" aria-current="page">{{ $node_category->name }}</li>
                </ol>
            </div>
            <div class="card strpied-tabled-with-hover">
                <div class="fixed-table-toolbar toolbar-for-btn">
                    <div class="fixed-search">
                        <form action="{{ route('building::nodes::category::view', $node_category->id) }}">
                            <input class="form-control" type="text" value="{{ Request::get('search') }}" name="search" placeholder="Поиск">
                        </form>
                    </div>
                    @can('manual_nodes_edit')
                    <div class="pull-right">
                        <button class="btn btn-round btn-outline btn-sm add-btn" data-toggle="modal" data-target="#create-node">
                            <i class="glyphicon fa fa-plus"></i>
                            Добавить узел
                        </button>
                    </div>
                    @endcan
                </div>
                @if(!$nodes->isEmpty())
                <div class="table-responsive">
                    <table class="table table-hover mobile-table">
                        <thead>
                        <tr>
                            <th class="text-left">Название узла</th>
                            <th class="text-left">Вес узла, т</th>
                            <th class="text-left">Купля/Продажа, руб</th>
                            <th class="text-left">Аренда, руб/мес</th>
                            <th class="text-right">Действия</th>
                        </tr>
                        </thead>
                        <tbody>
                        @foreach ($nodes as $key => $node)
                            <tr>
                                <td data-label="Название узла" class="text-left">{{ $node->name }}</td>
                                <td data-label="Вес узла, т" class="text-left">{{ $weight[$key] - floor($weight[$key]) ? number_format($weight[$key], 3, '.', '') : $weight[$key]  }}, т</td>
                                <td data-label="Купля/Продажа, руб" class="text-left">{{ $buy_cost[$key] - floor($buy_cost[$key]) ? number_format($buy_cost[$key], 3, '.', '') : $buy_cost[$key] }}, руб</td>
                                <td data-label="Аренда, руб/мес" class="text-left">{{ $use_cost[$key] - floor($use_cost[$key]) ? number_format($use_cost[$key], 3, '.', '') : $use_cost[$key] }}, руб/мес</td>
                                <td class="text-right actions">
                                    @can('manual_nodes_edit')
                                    <div style="min-width:72px;display:inline-block">
                                        <button rel="tooltip" onclick="copy_node({{ $node }})" class="btn btn-link btn-xs btn-space" data-toggle="modal" data-target="#copy-node" data-original-title="Сделать копию">
                                            <i class="fa fa-clone"></i>
                                        </button>
                                        <button rel="tooltip" onclick="edit_node({{ $node }})" class="btn btn-link btn-xs btn-success btn-space" data-toggle="modal" data-target="#edit-node" data-original-title="Редактировать">
                                            <i class="fa fa-edit"></i>
                                        </button>
                                    </div>
                                    @endcan
                                    <div style="min-width:72px;display:inline-block">
                                        <button rel="tooltip" onclick="view_node({{ $node }})" class="btn btn-link btn-xs btn-info btn-space" data-toggle="modal" data-target="#view-node" data-original-title="Просмотр">
                                            <i class="fa fa-eye"></i>
                                        </button>
                                        @can('manual_nodes_edit')
                                        <button rel="tooltip" onclick="delete_node(this, {{ $node->id }})" class="btn btn-danger btn-xs btn-link btn-space" data-original-title="Удалить">
                                            <i class="fa fa-times"></i>
                                        </button>
                                        @endcan
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
                    <p class="text-center">В этом разделе пока нет ни одного типового узла</p>
                @endif
            </div>
        </div>
    </div>

    <!-- Модалки -->
    <!-- Создание категории-->
    @can('manual_nodes_edit')
    <div class="modal fade bd-example-modal-lg show" id="create-node" role="dialog" aria-labelledby="modal-search" aria-hidden="true" style="display: none;">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Создание узла</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">×</span>
                    </button>
                </div>
                <div class="modal-body">
                    <hr style="margin-top:0">
                    <div class="card border-0" >
                        <div class="card-body ">
                            <form id="create_node" class="form-horizontal" action="{{ route('building::nodes::node::store') }}" method="post">
                                @csrf
                                <input name="materials" type="hidden" id="put_materials_id">
                                <input name="count" type="hidden" id="put_materials_count">
                                <input name="node_category_id" type="hidden" value="{{ $node_category->id }}">
                                <div class="row">
                                    <div class="col-md-12">
                                        <div class="form-group">
                                            <label>Название<star class="star">*</star></label>
                                            <input name="node_name" type="text" placeholder="Укажите название" class="form-control" maxlength="150" required>
                                        </div>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-sm-12">
                                        <div class="form-group">
                                            <label>Описание</label>
                                            <textarea class="form-control textarea-rows" name="node_description" maxlength="200"></textarea>
                                        </div>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="form-check col-sm-6">
                                        <label class="form-check-label">
                                            <input name="is_compact_wv" class="form-check-input" value="1" type="checkbox" checked>
                                            <span class="form-check-sign"></span>
                                            Раскладывать на материалы
                                        </label>
                                    </div>
{{--                                    <div class="form-check col-sm-6">--}}
{{--                                        <label class="form-check-label">--}}
{{--                                            <input name="is_compact_cp" class="form-check-input" value="1" type="checkbox">--}}
{{--                                            <span class="form-check-sign"></span>--}}
{{--                                            Раскладывать на материалы в КП--}}
{{--                                        </label>--}}
{{--                                    </div>--}}
                                </div>
                                <h6 style="margin:20px 0 10px">Материалы</h6>
                                <div class="row">
                                    <div class="col-md-12">
                                        <div class="row">
                                            <div class="col-md-5">
                                                <div class="form-group">
                                                    <label>Материал<span class="star">*</span></label>
                                                    <select id="materials_select" style="width:100%;">
                                                    </select>
                                                </div>
                                            </div>
                                            <div class="col-md-4">
                                                <div class="form-group">
                                                    <label class="change_count_unit">Количество</label><span class="star">*</span>
                                                    <input id="material_count" type="number" placeholder="Укажите количество материала в тоннах" min="0" class="form-control">
                                                </div>
                                            </div>
                                            <div class="col-md-1" style="margin-top:21px">
                                                <div class="form-group">
                                                    <button id="append" type="button" class="btn-check btn btn-link">
                                                        <i class="pe-7s-plus"></i>
                                                    </button>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="row">
                                            <div class="col-md-12">
                                                <div class="bootstrap-tagsinput">
                                                    <div id="place_here">
                                                    </div>
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
                    <button type="submit" form="create_node" class="btn btn-info">Сохранить</button>
                </div>
            </div>
        </div>
    </div>
    @endcan
    <!-- Просмотр категории-->
    <div class="modal fade bd-example-modal-lg show" id="view-node" role="dialog" aria-labelledby="modal-search" style="display: none;">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="view_node_title">Узел крепления трубы</h5>
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
                                        <p class="form-control-static" id="view_node_description">Описание узла</p>
                                    </div>
                                </div>
                            </div>
                            <h6 style="margin:20px 0 10px">Материалы</h6>
                            <div class="row">
                                <div class="col-md-12">
                                    <div class="row">
                                        <div class="col-md-12">
                                            <div class="bootstrap-tagsinput">
                                                <div id="view_put_here">

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
    <!-- Редактирование категории-->
    @can('manual_nodes_edit')
    <div class="modal fade bd-example-modal-lg show" id="edit-node" role="dialog" aria-labelledby="modal-search" aria-hidden="true" style="display: none;">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Редактирование узла</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">×</span>
                    </button>
                </div>
                <div class="modal-body">
                    <hr style="margin-top:0">
                    <div class="card border-0" >
                        <div class="card-body ">
                            <form id="edit_node" class="form-horizontal" action="{{ route('building::nodes::node::update') }}" method="post" novalidate="novalidate">
                                @csrf
                                <input name="materials" type="hidden" id="edit_put_materials_id">
                                <input name="count" type="hidden" id="edit_put_materials_count">
                                <input name="node_category_id" type="hidden" value="{{ $node_category->id }}">
                                <input id="edit_node_id" name="node_id" type="hidden">
                                <div class="row">
                                    <div class="col-md-12">
                                        <div class="form-group">
                                            <label>Название<star class="star">*</star></label>
                                            <input id="edit_node_name" name="node_name" type="text" placeholder="Укажите название" class="form-control" maxlength="150" required>
                                        </div>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-sm-12">
                                        <div class="form-group">
                                            <label>Описание</label>
                                            <textarea id="edit_node_description" class="form-control textarea-rows" name="node_description" maxlength="200"></textarea>
                                        </div>
                                    </div>
                                </div>
                                <div class="form-check col-sm-6">
                                    <label class="form-check-label">
                                        <input name="is_compact_wv" id="is_compact_wv" class="form-check-input" value="1" type="checkbox" checked>
                                        <span class="form-check-sign"></span>
                                        Раскладывать на материалы
                                    </label>
                                </div>
                                <h6 style="margin:20px 0 10px">Материалы</h6>
                                <div class="row">
                                    <div class="col-md-12">
                                        <div class="row">
                                            <div class="col-md-5">
                                                <div class="form-group">
                                                    <label>Материал<span class="star">*</span></label>
                                                    <select id="edit_materials_select" style="width:100%;">
                                                    </select>
                                                </div>
                                            </div>
                                            <div class="col-md-4">
                                                <div class="form-group">
                                                    <label class="change_count_unit">Количество</label><span class="star">*</span>
                                                    <input id="edit_material_count" type="number" placeholder="Укажите количество материала в тоннах" min="0" class="form-control">
                                                </div>
                                            </div>
                                            <div class="col-md-1" style="margin-top:21px">
                                                <div class="form-group">
                                                    <button id="edit_append" type="button" class="btn-check btn btn-link">
                                                        <i class="pe-7s-plus"></i>
                                                    </button>
                                                </div>
                                            </div>
                                        </div>
                                        <hr>
                                        <div class="row">
                                            <div class="col-md-12">
                                                <div class="bootstrap-tagsinput">
                                                    <div id="edit_place_here">

                                                    </div>
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
                    <button type="submit" form="edit_node" class="btn btn-info">Сохранить</button>
                </div>
            </div>
        </div>
    </div>
    @endcan
    <!-- Клонирование категории-->
    @can('manual_nodes_edit')
    <div class="modal fade bd-example-modal-lg show" id="copy-node" role="dialog" aria-labelledby="modal-search" aria-hidden="true" style="display: none;">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Копирование узла</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">×</span>
                    </button>
                </div>
                <div class="modal-body">
                    <hr style="margin-top:0">
                    <div class="card border-0" >
                        <div class="card-body ">
                            <form id="copy_node" class="form-horizontal" action="{{ route('building::nodes::node::clone') }}" method="post" novalidate="novalidate">
                                @csrf
                                <input name="materials" type="hidden" id="copy_put_materials_id">
                                <input name="count" type="hidden" id="copy_put_materials_count">
                                <input name="node_category_id" type="hidden" value="{{ $node_category->id }}">
                                <div class="row">
                                    <div class="col-md-12">
                                        <div class="form-group">
                                            <label>Название<star class="star">*</star></label>
                                            <input id="copy_node_name" name="node_name" type="text" placeholder="Укажите название" class="form-control" maxlength="150" required>
                                        </div>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-sm-12">
                                        <div class="form-group">
                                            <label>Описание</label>
                                            <textarea id="copy_node_description" class="form-control textarea-rows" name="node_description" maxlength="200"></textarea>
                                        </div>
                                    </div>
                                </div>
                                <h6 style="margin:20px 0 10px">Материалы</h6>
                                <div class="row">
                                    <div class="col-md-12">
                                        <div class="row">
                                            <div class="col-md-5">
                                                <div class="form-group">
                                                    <label>Материал<span class="star">*</span></label>
                                                    <select id="copy_materials_select" style="width:100%;">
                                                    </select>
                                                </div>
                                            </div>
                                            <div class="col-md-4">
                                                <div class="form-group">
                                                    <label class="change_count_unit">Количество</label><span class="star">*</span>
                                                    <input id="copy_material_count" type="number" placeholder="Укажите количество материала в тоннах" min="0" class="form-control">
                                                </div>
                                            </div>
                                            <div class="col-md-1" style="margin-top:21px">
                                                <div class="form-group">
                                                    <button id="copy_append" type="button" class="btn-check btn btn-link">
                                                        <i class="pe-7s-plus"></i>
                                                    </button>
                                                </div>
                                            </div>
                                        </div>
                                        <hr>
                                        <div class="row">
                                            <div class="col-md-12">
                                                <div class="bootstrap-tagsinput">
                                                    <div id="copy_place_here">

                                                    </div>
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
                    <button type="submit" form="copy_node" class="btn btn-info">Сохранить</button>
                </div>
            </div>
        </div>
    </div>
    @endcan
@endsection

@section('js_footer')
    <meta name="csrf-token" content="{{ csrf_token() }}" />

    <script>
        Array.prototype.remove = function(el) {
            return this.splice(this.indexOf(el), 1);
        };

        $('#materials_select').select2({
            language: 'ru',
            dropdownParent: $('#create-node'),
            ajax: {
                url: '/projects/ajax/get-material?is_tongue=1',
                dataType: 'json',
                delay: 250,
            }
        }).on('select2:select', function(e) {
            console.log(e.params.data);
            $('.change_count_unit')[0].innerHTML = 'Количество, ' + e.params.data.unit
        });

        $('#edit_materials_select').select2({
            language: 'ru',
            dropdownParent: $('#edit-node'),
            ajax: {
                url: '/projects/ajax/get-material?is_tongue=1',
                dataType: 'json',
                delay: 250,
            }
        }).on('select2:select', function(e) {
            console.log(e.params.data);
            $('.change_count_unit')[1].innerHTML = 'Количество, ' + e.params.data.unit
        });

        $('#copy_materials_select').select2({
            language: 'ru',
            dropdownParent: $('#copy-node'),
            ajax: {
                url: '/projects/ajax/get-material?is_tongue=1',
                dataType: 'json',
                delay: 250,
            }
        }).on('select2:select', function(e) {
            console.log(e.params.data);
            $('.change_count_unit')[2].innerHTML = 'Количество, ' + e.params.data.unit
        });



        var CSRF_TOKEN = $('meta[name="csrf-token"]').attr('content');

        function delete_node(e, id) {
            swal({
                title: 'Вы уверены?',
                text: "Узел будет удален!",
                type: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#3085d6',
                cancelButtonText: 'Назад',
                confirmButtonText: 'Удалить'
            }).then((result) => {
                if(result.value) {
                    $.ajax({
                        url:'{{ route("building::nodes::node::delete") }}',
                        type: 'POST',
                        data: {
                            _token: CSRF_TOKEN,
                            node_id: id,
                        },
                        dataType: 'JSON',
                        success: function (data) {
                            e.closest('tr').remove();
                        }
                    });
                }
            });
        }

        function view_node(e) {
            $('#view_node_title').text(e.name);
            if (!e.description) {
                $('#view_node_description').text('Нет описания');
            } else {
                $('#view_node_description').text(e.description);
            }

            $.each(e.node_materials, function(key, value) {
                var material_name = value.materials.name;
                var category_unit = value.unit;
                var count = value.count;

                var result = material_name + ', ' + category_unit + ': ' + count;
                var button = "<span class=\"badge badge-azure\">" + result + "</span>";

                $('#view_put_here').append(button);
            });
        }

        $('#view-node').on("hide.bs.modal", function () {
            $('#view_node_title').text('');
            $('#view_node_description').text('');

            $('#view_put_here').empty();
        });

        var edit_materials_id = [];
        var edit_materials_count = [];

        $("#edit_append").click(function(){
            var material_name = $('#edit_materials_select option:selected').text();
            var material_id = $('#edit_materials_select').val();
            var count = $('#edit_material_count').val();

            if(material_name !== 'Выберите материал' && count !== '' && count > 0) {
                $('#edit_material_count').removeClass('is-invalid');
                var result = material_name + ': ' + count;

                var button = "<span class=\"badge badge-azure\">" + result + "<span data-role=\"remove\" class=\"badge-remove-link\" mat_id=\"" + material_id + "\" count=\"" + count + "\"></span></span>";

                $('#edit_place_here').append(button);

                edit_materials_id.push(material_id);
                edit_materials_count.push(count);

                $('#edit_material_count').val('');
            } else {
                $('#edit_material_count').addClass('is-invalid');
            }
        });

        function edit_node(e) {
            $('#node_id').val(e.id);
            $('#edit_node_name').val(e.name);
            $('#edit_node_id').val(e.id);
            if (e.description) {
                $('#edit_node_description').text(e.description);
            }
            $('#is_compact_wv').prop("checked", e.is_compact_wv);

            $.each(e.node_materials, function(key, value) {
                var material_name = value.materials.name;
                var material_id = value.materials.id;
                var category_unit = value.unit;
                var count = value.count;

                var result = material_name + ', ' + category_unit + ': ' + count;
                var button = "<span class=\"badge badge-azure\">" + result + "<span id='edit_remove' data-role=\"remove\" class=\"badge-remove-link\" mat_id=\"" + material_id + "\" count=\"" + count + "\"></span></span>";

                $('#edit_place_here').append(button);

                edit_materials_id.push(material_id);
                edit_materials_count.push(count);
            });
        }

        $(document).on('click', '#edit_remove', function() {
            var count = $(this).attr('count');
            var mat_id = $(this).attr('mat_id');

            edit_materials_id.remove(mat_id);
            edit_materials_count.remove(count);

            $(this).closest('.badge').remove();
        });

        $("#edit_node").submit(function(e) {
            if (edit_materials_id.length > 0) {
                $('#edit_put_materials_count').val(edit_materials_count);
                $('#edit_put_materials_id').val(edit_materials_id);
                $(this).off('submit').submit();
            } else {
                e.preventDefault(e);
                $('#edit_material_count').addClass('is-invalid');
            }
        });

        $('#edit-node').on("hide.bs.modal", function () {
            $('#edit_node_name').val('');
            $('#edit_node_id').val('');
            $('#edit_node_description').text('');

            edit_materials_id = [];
            edit_materials_count = [];

            $('#edit_place_here').empty();
        });

        var materials_id = [];
        var materials_count = [];

        $("#append").click(function(){
            var material_name = $('#materials_select option:selected').text();
            var material_id = $('#materials_select').val();
            var count = $('#material_count').val();

            if(material_name !== 'Выберите материал' && count !== '' && count > 0) {
                $('#material_count').removeClass('is-invalid');
                var result = material_name + ': ' + count;

                var button = "<span class=\"badge badge-azure\">" + result + "<span data-role=\"remove\" class=\"badge-remove-link\" mat_id=\"" + material_id + "\" count=\"" + count + "\"></span></span>";

                $('#place_here').append(button);

                materials_id.push(material_id);
                materials_count.push(count);

                $('#material_count').val('');
            } else {
                $('#material_count').addClass('is-invalid');
            }
        });

        $(document).on('click', '.badge-remove-link', function() {
            var count = $(this).attr('count');
            var mat_id = $(this).attr('mat_id');

            materials_id.remove(mat_id);
            materials_count.remove(count);

            $(this).closest('.badge').remove();
        });

        $("#create-node").submit(function(e) {
            if (materials_id.length > 0) {
                $('#put_materials_count').val(materials_count);
                $('#put_materials_id').val(materials_id);
                $(this).off('submit').submit();
            } else {
                e.preventDefault(e);
                $('#material_count').addClass('is-invalid');
            }
        });

        var copy_materials_id = [];
        var copy_materials_count = [];

        function copy_node(e) {
            $('#copy_node_name').val(e.name);
            if (e.description) {
                $('#copy_node_description').text(e.description);
            }

            $.each(e.node_materials, function(key, value) {
                var material_name = value.materials.name;
                var material_id = value.materials.id;
                var category_unit = value.unit;
                var count = value.count;

                var result = material_name + ', ' + category_unit + ': ' + count;
                var button = "<span class=\"badge badge-azure\">" + result + "<span id='copy_remove' data-role=\"remove\" class=\"badge-remove-link\" mat_id=\"" + material_id + "\" count=\"" + count + "\"></span></span>";

                $('#copy_place_here').append(button);

                copy_materials_id.push(material_id);
                copy_materials_count.push(count);
            });
        }

        $("#copy_append").click(function(){
            var material_name = $('#copy_materials_select option:selected').text();
            var material_id = $('#copy_materials_select').val();
            var count = $('#copy_material_count').val();

            if(material_name !== 'Выберите материал' && count !== '' && count > 0) {
                $('#copy_material_count').removeClass('is-invalid');
                var result = material_name + ': ' + count;

                var button = "<span class=\"badge badge-azure\">" + result + "<span id='copy_remove' data-role=\"remove\" class=\"badge-remove-link\" mat_id=\"" + material_id + "\" count=\"" + count + "\"></span></span>";

                $('#copy_place_here').append(button);

                copy_materials_id.push(material_id);
                copy_materials_count.push(count);

                $('#copy_material_count').val('');
            } else {
                $('#copy_material_count').addClass('is-invalid');
            }
        });

        $(document).on('click', '#copy_remove', function() {
            var count = $(this).attr('count');
            var mat_id = $(this).attr('mat_id');

            copy_materials_id.remove(mat_id);
            copy_materials_count.remove(count);

            $(this).closest('.badge').remove();
        });

        $("#copy_node").submit(function(e) {
            if (copy_materials_id.length > 0) {
                $('#copy_put_materials_count').val(copy_materials_count);
                $('#copy_put_materials_id').val(copy_materials_id);
                $(this).off('submit').submit();
            } else {
                e.preventDefault(e);
                $('#copy_material_count').addClass('is-invalid');
            }
        });

        $('#copy-node').on("hide.bs.modal", function () {
            $('#copy_node_name').val('');
            $('#copy_node_description').text('');

            copy_materials_id = [];
            copy_materials_count = [];

            $('#copy_place_here').empty();
        });
    </script>
@endsection
