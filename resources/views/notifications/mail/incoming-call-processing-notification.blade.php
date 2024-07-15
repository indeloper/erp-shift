@component('mail::message')

    {!! $name !!}


    Благодарим вас за использование нашего приложения!

    Спасибо, {{ config('app.name') }}
@endcomponent
