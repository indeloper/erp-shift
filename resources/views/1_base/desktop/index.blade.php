@extends('layouts.app')

@section('title', $sectionTitle)

@section('url', route($routeNameFixedPart.'getPageCore'))

@section('css_top')
    @includeIf('1_base.desktop.css')
    @includeIf(explode('/views/', $baseBladePath)[1].'/desktop/css')
@endsection

@section('content')

<div id="dataGridAncor"></div>
<div id="mainPopup"></div>
<div id="externalPopup"></div>

@endsection

@section('js_footer')
    @foreach($components as $component)
        @includeif($component)
    @endforeach
@endsection