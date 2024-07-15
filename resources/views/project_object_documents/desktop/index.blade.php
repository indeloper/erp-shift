@extends('layouts.app')

@section('title', 'Документооборот: Площадка ⇆ Офис')

@section('url', route('project-object-documents'))

@section('css_top')
    @include('project_object_documents.desktop.css')
@endsection

@section('content')

    @php
        $actionUrl = route('projectObjectDocument.downloadXls');
        if(Str::contains(URL::full(), 'showArchive=1'))
        $actionUrl = $actionUrl.'?customSearchParams=showArchive=1';
    @endphp

    <form id="downloadXlsForm" method="post" action="{{$actionUrl}}" hidden>
        @csrf
        <input id="filterOptions" name="filterOptions">
        <input id="projectObjectsFilter" name="projectObjectsFilter">
        <input id="projectResponsiblesFilter" name="projectResponsiblesFilter">
    </form>

    <div id="dataGridAnchor"></div>
    <div id="statusOptionsForm"></div>

@endsection

@section('js_footer')
    @include('project_object_documents.desktop.js')
@endsection
