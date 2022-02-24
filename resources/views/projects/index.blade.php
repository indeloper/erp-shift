@extends('layouts.app')

@section('title', 'Проекты')

@section('css_top')
    <link href="{{ mix('css/projects.css') }}" rel="stylesheet" />
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
    <div class="card strpied-tabled-with-hover" id="filter">
        <div class="card-body">
            <h6>Фильтрация по материалам</h6>
            <div class="row">
                <div class="col-md-4" style="margin:15px 5px 20px 0">
                    <template>
                        <el-input  placeholder="Введите наименование материала" @keyup.native.enter="filter" v-model="param_value" clearable
                                  class="d-inline-block"
                                  style="margin-bottom: 10px"
                        ></el-input>
                    </template>
                </div>
                <div class="col-md-2" style="margin:18px 5px 20px 0">
                    <div class="left-edge">
                        <div class="page-container">
                            <button type="button" v-on:click="filter" class="btn btn-wd btn-info">Добавить</button>
                        </div>
                    </div>
                </div>
            </div>
            <template>
                <div class="row" v-if="filter_items.length > 0">
                    <div class="col-md-12" style="margin: 10px 0 10px 0">
                        <h6>Выбранные фильтры</h6>
                    </div>
                </div>
                <div class="row" v-if="filter_items.length > 0">
                    <div class="col-md-9">
                        <div class="bootstrap-tagsinput">
                            <span class="badge badge-azure" v-on:click="delete_budge(index)" v-for="(item, index) in filter_items">@{{ item }}<span data-role="remove" class="badge-remove-link"></span></span>
                        </div>
                    </div>
                    <div class="col-md-3 text-right mnt-20--mobile text-center--mobile">
                        <button type="button" @click="delete_all_badges()" class="btn btn-sm show-all">
                            Удалить фильтры
                        </button>
                    </div>
                </div>
            </template>
        </div>
    </div>

    <div class="row">
    <div class="col-md-12">
        <div class="card strpied-tabled-with-hover">
            <div class="fixed-table-toolbar toolbar-for-btn">
                <div class="fixed-search">
                    <form action="{{ route('projects::index') }}">
                        <input class="form-control" type="text" value="{{ Request::get('search') }}" name="search" placeholder="Поиск">
                        <input type="hidden" name="material_names" value="{{ Request::get('material_names') }}">
                    </form>
                </div>
                @can('projects_create')
                    <div class="pull-right">
                        <a class="btn btn-round btn-outline btn-sm add-btn" href="{{ route('projects::create') }}">
                            <i class="glyphicon fa fa-plus"></i>
                            Добавить
                        </a>
                    </div>
                @endcan
            </div>
            <div class="table-responsive main-table-responsive">
                @if(!$projects->isEmpty())
                <table class="table table-hover mobile-table">
                    <thead>
                        <tr>
                            <th class="text-center">ID</th>
                            <th>Проект</th>
                            <th>Объект</th>
                            <th>Адрес</th>
                            <th>Контрагент</th>
                            <th>Шпунт
                                @php $key = 1; @endphp
                                <button type="button" name="button" class="btn btn-link btn-primary btn-xs pd-0" data-html="true" data-container="body" data-toggle="popover" data-placement="right"
                                        data-content="@foreach($projects->first()->getModel()->project_status as $id => $status)
                                                    {{$key++ . '. ' . $status . ' - ' . $projects->first()->getModel()->project_status_description[$id] }}<br/><br/>
                                                    @endforeach">
                                    <i class="fa fa-info-circle"></i>
                                </button>
                            </th>
                            <th>Сваи</th>
                            <!-- <th>Статус проекта</th> -->
                            <th>Юр. лицо</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($projects as $project)
                        <tr style="cursor:default" class="href @if ($project->is_important) row-important @endif" data-href="{{ route('projects::card', [$project->id, 'project_id' => $project->id, 'contractor_id' => $project->contractor_id]) }}">
                            <td  data-label="ID">{{ $project->id }}</td>
                            <td  data-label="Проект">{{ $project->name }}</td>
                            <td  data-label="Объект">{{ $project->project_name }}</td>
                            <td  data-label="Адрес">{{ $project->project_address }}</td>
                            <td  data-label="Контрагент">{{ $project->contractor_name }}</td>
                            <td  data-label="Шпунт">{!! $project->tongue_statuses !!}</td>
                            <td  data-label="Сваи">{!! $project->pile_statuses !!}</td>
                            <td  data-label="Юр. лицо">{{ $project::$entities[$project->entity] }}</td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
                @elseif(Request::has('search'))
                <p class="text-center">По вашему запросу ничего не найдено</p>
            @else
                <p class="text-center">В этом разделе пока нет ни одного проекта</p>
            @endif
            </div>
            <div class="col-md-12">
                <div class="right-edge fix-pagination">
                    <div class="page-container">
                        {{ $projects->appends(['search' => Request::get('search'), 'status' => Request::get('status'), 'material_names' => Request::get('material_names')])->links() }}
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@include('sections.modal_task')
@endsection

@section('js_footer')
    <script type="text/javascript">
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
        var requests = Array();

        var filter = new Vue({
            el: '#filter',
            data: {
                parameter: 1,
                param_value: '',
                filter_items: [],
            },
            mounted: function () {
                let that = this;
                let material_names = {!! json_encode(explode(',', (string)Request::get('material_names'))) !!};

                if (material_names[0]) {
                    this.filter_items = this.filter_items.concat(material_names);
                }
            },
            methods: {
                filter: function () {
                    if(this.param_value != '') {
                        this.filter_items.push(this.param_value);
                        this.send_filter(this.filter_items);
                    }
                },
                delete_budge: function (index) {
                    this.filter_items.splice(index, 1);

                    this.send_filter(this.filter_items);
                },
                delete_all_badges: function () {
                    this.filter_items.splice(0, this.filter_items.length);

                    this.send_filter(this.filter_items);
                },
                send_filter: function (filter) {
                    window.location.href = '{{ route('projects::index', ['search' => Request::get('search')]) }}&material_names=' + filter;
                },
            }
        });

    </script>
@endsection
