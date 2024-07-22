@extends('layouts.telegram')

@push('scripts_after')
    <script
            type="text/javascript"
            src="{{ asset('js/telegram/auth-web-apps.js')}}"
    ></script>
@endpush
