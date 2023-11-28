@extends('layouts.app')

@section('title', $sectionTitle)

@section('url', route($routeNameFixedPart.'getPageCore'))

@section('css_top')
    @includeIf('1_base.css')
    @includeIf('1_base.mobile.css')
    @includeIf(explode('/views/', $baseBladePath)[1].'/mobile/css')
@endsection

@section('content')

    <div class="new-entity-button-wrapper">
        <div id="newEntityButtonMobile"></div>
    </div>

    <div id="filterElementMobile1" class="filter-element-mobile"></div>
    <div id="filterElementMobile2" class="filter-element-mobile"></div>
    <div id="filterElementMobile3" class="filter-element-mobile"></div>

    <div id="entitiesListMobile"></div>

    <div id="popupMobile"></div>
    <div id="popupLoadPanelMobile"></div>

@endsection

@section('js_footer')
    @includeIf(explode('/views/', $baseBladePath)[1].'/desktop/components')
@endsection