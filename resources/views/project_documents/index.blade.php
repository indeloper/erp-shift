@extends('layouts.app')

@section('title', 'Проектная документация')

@section('url', route('project_documents::index'))

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
        <div class="card strpied-tabled-with-hover">
            <div class="fixed-table-toolbar toolbar-for-btn text-center text-sm-left">
                <div class="fixed-search">
                    <form action="{{ route('project_documents::index') }}">
                        <input class="form-control" type="text" value="{{ Request::get('search') }}" name="search" placeholder="Поиск">
                    </form>
                </div>
                @if(strlen(Request::get('search')) > 0)
                    <a href="{{ route('project_documents::index') }}" role="button"
                    class="btn btn-secondary btn-sm btn-outline m-0 d-block d-sm-inline-block">Сброс</a>
                @endif
            </div>
            @if(!$projects->isEmpty())
            <div class="table-responsive main-table-responsive">
                <table class="table table-hover mobile-table">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Проект</th>
                            <th>Контрагент</th>
                            <th>Адрес</th>
                            <th>ИНН</th>
                            <th>Статус проекта</th>
                            <th>Юр. лицо</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($projects as $project)
                        <tr style="cursor:default" class="href" data-href="{{ route('project_documents::card', $project->id) }}">
                            <td data-label="ID">{{ $project->id }}</td>
                            <td data-label="Проект">{{ $project->name }}</td>
                            <td data-label="Контрагент"><a href="{{ route('contractors::card', $project->contractor_id) }}" class="table-link">{{ $project->contractor_name }}</a></td>
                            <td data-label="Адрес"><a href="{{ route('contractors::card', $project->contractor_id) }}" class="table-link">{{ $project->project_address }}</a></td>
                            <td data-label="ИНН">{{ $project->contractor_inn }}</td>
                            <td data-label="Статус проекта">{{ $project->project_status[$project->status] }}</td>
                            <td  data-label="Юр. лицо">{{ $project::$entities[$project->entity] }}</td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            @elseif(Request::has('search'))
                <p class="text-center">По вашему запросу ничего не найдено</p>
            @else
                <p class="text-center">В этом разделе пока нет ни одного проекта</p>
            @endif
            <div class="col-md-12" style="padding:0; margin-top:20px; margin-left:-2px">
                <div class="right-edge fix-pagination">
                    <div class="page-container">
                        {{ $projects->appends(['search' => Request::get('search')])->links() }}
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
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
    </script>
@endsection
