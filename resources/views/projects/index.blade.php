@extends('layouts.app')

@section('title', 'Проекты')

@section('css_top')
@endsection

@section('content')
    <div id="app-test">
        <projects-index></projects-index>
    </div>
    <div id="dataGridAnchor"></div>

@endsection

@section('js_footer')
    <script src="/js/projects/init-datagrid.js"></script>
@endsection
