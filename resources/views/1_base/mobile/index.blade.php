@extends('layouts.app')

@section('title', $sectionTitle)

@section('url', route($routeNameFixedPart.'getPageCore'))

@section('css_top')
    @includeIf('1_base.mobile.css')
    @includeIf(explode('/views/', $baseBladePath)[1].'/mobile/css')
@endsection

@section('content')

<div id="dataGridAncor"></div>
<div id="mainPopup"></div>
<div id="externalPopup"></div>

@endsection

@section('js_footer')
    @includeIf(explode('/views/', $baseBladePath)[1].'/desktop/components')
@endsection