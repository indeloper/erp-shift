@extends('layouts.app')

@section('title', 'Проекты')

@section('url', route('projects::index'))

@section('css_top')
<style>
    .rTable {
        display: table;
        width: 100%;
        margin-bottom: 1rem;
        background-color: transparent;
        border-collapse: collapse;
        border-spacing: 2px;
        border-color: grey;
    }
    .rTableRow {
        display: table-row;
        padding: .75rem;
        vertical-align: top;
        border-top: 1px solid #dee2e6;
        font-size: 14px;
    }
    .rTableHeading {
        display: table-header-group;
        background-color: #ddd;
    }
    .rTableCell, .rTableHead {
        display: table-cell;
    }
    .rTableHeading {
        display: table-header-group;
        background-color: #ddd;
        font-weight: bold;
    }
    .rTableFoot {
        display: table-footer-group;
        font-weight: bold;
        background-color: #ddd;
    }
    .rTableBody {
        display: table-row-group;
    }
    .rTableHead span {
        font-size: 16px;
        font-weight: 600;
    }
</style>

@endsection

@section('content')

<nav aria-label="breadcrumb" role="navigation">
    <ol class="breadcrumb">
        <li class="breadcrumb-item">
            <a href="{{ route('contractors::card', $contractor->id) }}" class="table-link">{{ $contractor->short_name }}</a>
        </li>
        <li class="breadcrumb-item">
            <a href="{{ route('projects::card', $project->id) }}" class="table-link">{{ $project->name }}</a>
        </li>
        <li class="breadcrumb-item active" aria-current="page">События</li>
    </ol>
</nav>
<div class="card">
    <div class="fixed-table-toolbar toolbar-for-btn">
        <div class="fixed-search">
            <form action="{{ route('projects::tasks', $project->id) }}">
                <input class="form-control" type="text" value="{{ Request::get('search') }}" name="search" placeholder="Поиск">
            </form>
        </div>
    </div>
    <div class="table-responsive">
        <table class="table  table-hover mobile-table">
            <thead>
                <tr>
                    <th>Дата создания</th>
                    <th>Дата исполнения</th>
                    <th>Наименование</th>
                    <th>Исполнитель</th>
                    <th>Автор</th>
                </tr>
            </thead>
            @include('sections.history_for_tasks')
        </table>
    </div>
</div>

@endsection
