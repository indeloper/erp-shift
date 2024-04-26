@component('mail::message')

    {!! $name !!}

    {!! $info !!}

@isset($url)
@component('mail::button', ['url' => $url])
Посмотреть
@endcomponent
@endisset

    Благодарим вас за использование нашего приложения!

    Спасибо, {{ config('app.name') }}
@endcomponent
