@component('mail::message')

    {!! $name !!}

    {!! $link !!}

    Благодарим вас за использование нашего приложения!

    Спасибо, {{ config('app.name') }}
@endcomponent
