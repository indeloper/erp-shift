@extends('layouts.app')

@section('title', 'Типовые узлы')

@section('url', route('building::nodes::index'))

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
                        <form action="{{ route('building::nodes::index') }}">
                            <input class="form-control" type="text" value="{{ Request::get('search') }}" name="search" placeholder="Поиск">
                        </form>
                    </div>
                    @can('manual_nodes_edit')
                    <div class="pull-right">
                        <button class="btn btn-round btn-outline btn-sm add-btn" data-toggle="modal" data-target="#create-category">
                            <i class="glyphicon fa fa-plus"></i>
                            Добавить категорию узлов
                        </button>
                    </div>
                    @endcan
                </div>
                @if(!$node_categories->isEmpty())
                <div class="table-responsive">
                    <table class="table table-hover mobile-table">
                        <thead>
                        <tr>
                            <th class="text-left">Название категории</th>
                            <th class="text-right">Действия</th>
                        </tr>
                        </thead>
                        <tbody>
                        @foreach ($node_categories as $key => $category)
                            <tr style="cursor:default" class="href" data-href="{{ route('building::nodes::category::view', $category->id) }}">
                                <td data-label="Название категории" class="text-left">{{ $category->name }}</td>
                                <td class="text-right actions">
                                    <!-- <a href="{{ route('building::nodes::category::view', $category->id) }}" class="btn btn-link btn-xs btn-space" data-original-title="Просмотр узлов категории">
                                        <i class="fa fa-folder-open"></i>
                                    </a> -->
                                    @can('manual_nodes_edit')
                                    <button rel="tooltip" onclick="edit_category({{ $category }})" class="btn btn-link btn-xs btn-success btn-space" data-toggle="modal" data-target="#edit-category" data-original-title="Редактировать">
                                        <i class="fa fa-edit"></i>
                                    </button>
                                    @endcan
                                    <button rel="tooltip" onclick="view_category({{ $category }})" class="btn btn-link btn-xs btn-info btn-space" data-toggle="modal" data-target="#view-category" data-original-title="Просмотр">
                                        <i class="fa fa-eye"></i>
                                    </button>
                                    @can('manual_nodes_edit')
                                    @if($category->id > 8)
                                        <button rel="tooltip" onclick="delete_category(this, {{ $category->id }})" class="btn btn-danger btn-xs btn-link btn-space" data-original-title="Удалить">
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
                @elseif(Request::has('search'))
                    <p class="text-center">По вашему запросу ничего не найдено</p>
                @else
                    <p class="text-center">В этом разделе пока нет ни одной категории узлов</p>
                @endif
            </div>
        </div>
    </div>

    <!-- Модалки -->
    <!-- Создание категории-->
    @can('manual_nodes_edit')
    <div class="modal fade bd-example-modal-lg show" id="create-category" role="dialog" aria-labelledby="modal-search" style="display: none;">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Создание категории узлов</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">×</span>
                    </button>
                </div>
                <div class="modal-body">
                    <hr style="margin-top:0">
                    <div class="card border-0" >
                        <div class="card-body ">
                            <form id="create_node_category" class="form-horizontal" action="{{ route('building::nodes::category::store') }}" method="post">
                                @csrf
                                <div class="row">
                                    <div class="col-md-12">
                                        <div class="form-group">
                                            <label>Название<star class="star">*</star></label>
                                            <input name="category_name" type="text" placeholder="Укажите название" class="form-control" maxlength="100" required>
                                        </div>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-sm-12">
                                        <div class="form-group">
                                            <label>Описание</label>
                                            <textarea class="form-control textarea-rows" name="category_description" maxlength="200"></textarea>
                                        </div>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-sm-5">
                                        <div class="form-group">
                                            <label>Коэффициент запаса, %<star class="star">*</star></label>
                                            <input name="safety_factor" type="number" class="form-control" required min="0" max="100" step="0.01">
                                        </div>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Закрыть</button>
                    <button type="submit" form="create_node_category" class="btn btn-info">Сохранить</button>
                </div>
            </div>
        </div>
    </div>
    @endcan
    <!-- Просмотр категории-->
    <div class="modal fade bd-example-modal-lg show" id="view-category" role="dialog" aria-labelledby="modal-search" style="display: none;">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="view_category_name">Шпунт</h5>
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
                                        <p class="form-control-static" id="view_category_description">Описание категории узлов</p>
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-sm-12">
                                    <div class="form-group">
                                        <label>Коэффициент запаса, %</label>
                                        <p class="form-control-static" id="view_safety_factor">0,05</p>
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
    <div class="modal fade bd-example-modal-lg show" id="edit-category" role="dialog" aria-labelledby="modal-search" style="display: none;">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Редактирование категории узлов</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">×</span>
                    </button>
                </div>
                <div class="modal-body">
                    <hr style="margin-top:0">
                    <div class="card border-0" >
                        <div class="card-body ">
                            <form id="edit_node_category" class="form-horizontal" action="{{ route('building::nodes::category::update') }}" method="post">
                                @csrf
                                <input type="hidden" name="id" id="edit_category_id">
                                <div class="row">
                                    <div class="col-md-12">
                                        <div class="form-group">
                                            <label>Название<star class="star">*</star></label>
                                            <input name="category_name" id="edit_category_name" type="text" placeholder="Укажите название" class="form-control" maxlength="100" required>
                                        </div>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-sm-12">
                                        <div class="form-group">
                                            <label>Описание</label>
                                            <textarea id="edit_category_description" class="form-control textarea-rows" name="category_description" maxlength="200"></textarea>
                                        </div>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-sm-5">
                                        <div class="form-group">
                                            <label>Коэффициент запаса, %<star class="star">*</star></label>
                                            <input name="safety_factor" id="edit_safety_factor" type="number" class="form-control" required min="0" max="100" step="0.01">
                                        </div>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Закрыть</button>
                    <button type="submit" form="edit_node_category" class="btn btn-info">Сохранить</button>
                </div>
            </div>
        </div>
    </div>
    @endcan
@endsection

@section('js_footer')
    @can('manual_nodes_edit')
    <meta name="csrf-token" content="{{ csrf_token() }}" />

    <script>
        var CSRF_TOKEN = $('meta[name="csrf-token"]').attr('content');

        function delete_category(e, id) {
            swal({
                title: 'Вы уверены?',
                text: "Категория узлов будет удалена",
                type: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#3085d6',
                cancelButtonText: 'Назад',
                confirmButtonText: 'Удалить'
            }).then((result) => {
                if(result.value) {
                    $.ajax({
                        url:'{{ route("building::nodes::category::delete") }}',
                        type: 'POST',
                        data: {
                            _token: CSRF_TOKEN,
                            category_id: id,
                        },
                        dataType: 'JSON',
                        success: function (data) {
                            e.closest('tr').remove();
                        }
                    });
                }
            });
        }

        function view_category(e) {
            $('#view_category_name').text(e.name);
            $('#view_safety_factor').text(e.safety_factor);
            if (!e.description) {
                $('#view_category_description').text('Нет описания');
            } else {
                $('#view_category_description').text(e.description);
            }
        }

        function edit_category(e) {
            $('#edit_category_id').val(e.id);
            $('#edit_category_name').val(e.name);
            $('#edit_safety_factor').val(e.safety_factor);
            $('#edit_category_description').val(e.description);
        }
    </script>
    @endcan
@endsection
