@extends('layouts.app')

@section('title', 'Документооборот: Площадка ⇆ Офис')

@section('url', route('project-object-documents'))

@section('css_top')
    @include('project_object_documents.mobile.css')
@endsection

@section('content')

    <div style="display: flex; justify-content:end">
        <div id="newDocumentButtonMobile"></div>
    </div>
    
    <div id="responsiblesFilterMobile" style="margin-bottom:5px"></div>
    <div id="documentsListMobile"></div>
    <div id="popupFormMobile"></div>
    <div id="popupLoadPanel"></div>
    
    <!-- <div id="statusOptionsFormMobile"></div> -->

@endsection

@section('js_footer')
    @include('project_object_documents.mobile.components')
@endsection