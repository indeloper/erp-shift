@extends('layouts.app')

@section('title', 'Подрядчики')

@section('url', route('subcontractors::index'))

@section('content')
<div class="row">
    <div class="col-md-12">
        <div class="card strpied-tabled-with-hover">
            <div class="fixed-table-toolbar toolbar-for-btn">
                <div class="fixed-search">
                    <form action="{{ route('subcontractors::index') }}">
                        <input class="form-control" type="text" value="{{ Request::get('search') }}" name="search" placeholder="Поиск">
                    </form>
                </div>
                @if(Auth::user()->department_id == 14/*6*/)
                    <div class="pull-right">
                        <a href="#">
                            <a class="btn btn-round btn-outline btn-sm add-btn" href="{{ route('subcontractors::create') }}">
                                <i class="glyphicon fa fa-plus"></i>
                                Добавить
                            </a>
                        </a>
                    </div>
                @endif
            </div>
            @if(!$contractors->isEmpty())
            <div class="table-responsive">
                <table class="table table-hover mobile-table">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Наименование</th>
                            <th>ИНН</th>
                            <th>ОГРН</th>
                            <th>Юридический адрес</th>
                            <th style="width:0; padding:0; margin:0;"></th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($contractors as $contractor)
                            <tr style="cursor:default" class="href" data-href="{{ route('subcontractors::card', [$contractor->id, 'contractor_id' => $contractor->id]) }}">
                                <td data-label="ID">{{ $contractor->id }}</td>
                                <td data-label="Наименование">{{ $contractor->short_name }}</td>
                                <td data-label="ИНН">{{ $contractor->inn }}</td>
                                <td data-label="ОГРН">{{ $contractor->ogrn }}</td>
                                <td data-label="Юридический адрес">{{ $contractor->legal_address }}</td>
                                <td style="width:0; padding:0; margin:0;"></td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            @elseif(Request::has('search'))
                <p class="text-center">По вашему запросу ничего не найдено</p>
            @else
                <p class="text-center">В этом разделе пока нет ни одного подрядчика</p>
            @endif
            <div class="col-md-12 fix-pagination">
                <div class="right-edge">
                    <div class="page-container">
                        {{ $contractors->appends(['search' => Request::get('search')])->links() }}
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
