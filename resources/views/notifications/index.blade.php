@extends('layouts.app')

@section('title', 'Уведомления')

@section('url', route('notifications::index'))

@section('css_top')
    <style media="screen">
        .form-check .form-check-sign::after {
            margin-left: -20px;
        }

        .th-check:after,
        .th-check:before {
            margin-top:-17px!important;
        }
    </style>
@endsection

@section('js_footer')
    <script type="text/javascript" src="{{ asset('js/notifications.js')}}"></script>
@endsection

@section('content')
    <div class="row">
        <div class="col-md-12 mobile-card">
            <div class="card strpied-tabled-with-hover">
                @include('notifications.shared.list')
            </div>
        </div>
    </div>

@endsection
