@extends('layouts.app')

@section('title', 'Документооборот: Площадка ⇆ Офис')

@section('url', route('project-object-documents'))

@section('css_top')
    @include('project_object_documents.desktop.css')
@endsection

@section('content')

    <div id="container">
        <div id="headerRow">
            <div id="gridHeader">
                Документы
            </div>
            <div id="headerToolbarWrapper">
                <div class="headerToolbarItem-wrapper">
                    <div class="main-filter-label">Ответственные: </div>
                    <div id="responsiblesFilterSelect" class="headerToolbarItem dxTagBoxItem"></div>
                </div>
                <div class="headerToolbarItem-wrapper">
                    <div class="main-filter-label">Объекты: </div>
                    <div id="objectsFilterSelect" class="headerToolbarItem dxTagBoxItem"></div>
                </div>
                <div class="headerToolbarItem-wrapper">
                    <div id="groupingAutoExpandAllTrue" class="headerToolbarItem"></div>
                </div>
                <div class="headerToolbarItem-wrapper">
                    <div id="groupingAutoExpandAllFalse" class="headerToolbarItem"></div>
                </div>
                <div class="headerToolbarItem-wrapper">
                    <div id="toolbarDropDownButton" class="headerToolbarItem"></div>
                </div>
                <!-- <div class="headerToolbarItem-wrapper">
                    <div id="downloadButton" class="headerToolbarItem"></div>
                </div>
                <div class="headerToolbarItem-wrapper">
                    <div id="addRowButton" class="headerToolbarItem"></div>
                </div> -->
            </div>
        </div>
        <div id="dataGridContainer"></div>
    </div>

    @php
        $actionUrl = route('projectObjectDocument.downloadXls');
        if(str_contains(URL::full(), 'showArchive=1'))
        $actionUrl = $actionUrl.'?customSearchParams=showArchive=1';
    @endphp

    <form id="downloadXlsForm" method="post" action="{{$actionUrl}}" hidden>
        @csrf
        <input id="filterOptions" name="filterOptions">
        <input id="projectObjectsFilter" name="projectObjectsFilter">
        <input id="projectResponsiblesFilter" name="projectResponsiblesFilter">
    </form>

    <div id="statusOptionsForm"></div>

@endsection

@section('js_footer')
    @include('project_object_documents.desktop.js')
@endsection