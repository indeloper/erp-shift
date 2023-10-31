@extends('layouts.app')

@section('title', 'Объекты')

@section('url', route('objects::base-template'))

@section('css_top')
    @include('objects.desktop.css')
@endsection

@section('content')

<!-- <div id="container">
    <div id="headerRow">
        <div id="gridHeader">
            Объекты
        </div>
    </div>
    <div id="dataGridContainer"></div>
</div> -->
<div id="dataGridAnchor"></div>
<div id="mainPopup"></div>
<div id="bitrixProjectsPopup"></div>

@endsection

@section('js_footer')
    @include('objects.desktop.components')
@endsection
