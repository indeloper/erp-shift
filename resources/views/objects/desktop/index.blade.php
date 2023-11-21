@extends('layouts.app')

@section('title', 'Объекты')

@section('url', route('objects::base-template'))

@section('css_top')
    @include('objects.desktop.css')
@endsection

@section('content')
    <div id="dataGridAnchor"></div>
    <div id="mainPopup"></div>
    <div id="bitrixProjectsPopup"></div>
    <div id="shortNameConfigurationPopup"></div>
@endsection

@section('js_footer')
    @include('objects.desktop.components')
@endsection
