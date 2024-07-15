@extends('layouts.app')

@section('title', 'Сотрудники')

@section('url', route('users::index'))

@section('content')
<div class="row">
    <div class="col-md-12">
        <div class="nav-container" style="margin:0 0 10px 15px">
            <ul class="nav nav-icons" role="tablist">
                <li class="nav-item @if (Request::is('users')) show active @endif">
                    <a class="nav-link link-line @if (Request::is('users')) active-link-line @endif" href="">
                        Список сотрудников
                    </a>
                </li>
                @can('users_permissions')
                <li class="nav-item show">
                    <a class="nav-link link-line" href="{{ route('users::department_permissions') }}">
                        Группы прав
                    </a>
                </li>
                @endcan
            </ul>
        </div>
        <div class="card strpied-tabled-with-hover">
            <div class="fixed-table-toolbar toolbar-for-btn text-center text-sm-left">
                <div class="fixed-search">
                    <form action="{{ route('users::index') }}">
                        <input class="form-control" type="text" value="{{ Request::get('search') }}" name="search" placeholder="Поиск">
                    </form>
                </div>
                @if(strlen(Request::get('search')) > 0)
                    <a href="{{ route('users::index') }}" role="button"
                    class="btn btn-secondary btn-sm btn-outline m-0 d-block d-sm-inline-block">Сброс</a>
                @endif
                @can('users_create')
                <div class="pull-right float-none float-sm-right">
                    <a class="btn btn-round btn-outline btn-sm add-btn d-block d-sm-inline-block" href="{{ route('users::create') }}">
                        <i class="glyphicon fa fa-plus"></i>
                        Добавить
                    </a>
                </div>
                @endif
            </div>
            @if($users->isNotEmpty())
                <div class="table-responsive">
                    <table class="table table-hover mobile-table">
                        <thead>
                            <tr>
{{--                                <th class="text-center">ID</th>--}}
                                <th>ФИО</th>
                                <th>Должность</th>
                                <th>Подразделение</th>
                                <th>Компания</th>
                                <th>Должностная категория</th>
                                <th class="text-center">Статус</th>
                            </tr>
                        </thead>
                        <tbody>
                        @foreach($users->where('id', '!=', 1) as $user)
                            <tr style="cursor:default" class="href" data-href="{{ route('users::card', $user->id) }}">
{{--                                <td data-label="id" class="text-center">--}}
{{--                                    {{ $user->id }}--}}
{{--                                </td>--}}
                                <td data-label="ФИО">{{ $user->last_name }} {{ $user->first_name }} {{ $user->patronymic }}</td>
                                <td data-label="Должность">{{ $user->group_name }}</td>
                                <td data-label="Подразделение">{{ $user->dep_name }}</td>
                                <td data-label="Компания">{{ $companies[$user->company] }}</td>
                                <td data-label="Должностная категория">{{ $user->job_category_name }}</td>
                                <td data-label="Статус" class="text-center">@if($user->status)Активен@elseНе активен@endif</td>
                            </tr>
                        @endforeach
                        </tbody>
                    </table>
                </div>
            @elseif(Request::has('search'))
                <p class="text-center">По вашему запросу ничего не найдено</p>
            @endif
            <div class="col-md-12 fix-pagination">
                <div class="right-edge">
                    <div class="page-container">
                        {{ $users->appends(['search' => Request::get('search'), 'job_category_id' => Request::get('job_category_id')])->links() }}
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
