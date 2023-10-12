@extends('layouts.app')

@section('title', $sectionTitle)

@section('url', route($routeNameFixedPart.'getPageCore'))

@section('css_top')
    @include(explode('/views/', $baseBladePath)[1].'/desktop/css')
@endsection

@section('content')

<div id="dataGridAncor"></div>
<div id="mainPopup"></div>

@endsection

@section('js_footer')
    @include(explode('/views/', $baseBladePath)[1].'/desktop/components')
@endsection