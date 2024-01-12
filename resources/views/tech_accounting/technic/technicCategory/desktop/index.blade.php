@extends('layouts.app')

@section('title', $sectionTitle)

@section('url', route($routeNameFixedPart.'getPageCore'))

@section('css_top')
    @include(explode('/views/', $basePath)[1].'/desktop/css')
@endsection

@section('content')

<div id="dataGridAnchor"></div>
<div id="mainPopup"></div>

<form id="downloadXlsForm" target="_blank" method="post" action="{{route($routeNameFixedPart.'downloadXls')}}">
    @csrf
    <!-- 
    <input id="filterOptions" type="hidden" name="filterOptions">
    <input id="filterList" type="hidden" name="filterList"> 
    -->
</form>

@endsection

@section('js_footer')
    @include(explode('/views/', $basePath)[1].'/desktop/components')
@endsection