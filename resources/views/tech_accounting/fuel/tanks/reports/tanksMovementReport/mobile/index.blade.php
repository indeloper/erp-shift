@extends('layouts.app')

@section('title', '...')

@section('url', route('...'))

@section('css_top')
    @include('... .mobile.css')
@endsection

@section('content')

    <div  class="new-entity-button-mobile">
        <div id="newEntityButtonMobile"></div>
    </div>

    <div id="filterTagBox" class="customFilterMobile"></div>

    <div id="entitiesListMobile"></div>
    <div id="popupMobile"></div>
    <div id="popupLoadPanel"></div>

@endsection

@section('js_footer')
    @include('... .mobile.components')
@endsection
