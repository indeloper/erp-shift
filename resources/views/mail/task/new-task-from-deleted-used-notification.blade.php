@component('mail::message')

    {!! $name !!}

@isset($tasks_url)
@component('mail::button', ['url' => $tasks_url])
Посмотреть
@endcomponent
@endisset

    {!! $info !!}

@isset($url)
@component('mail::button', ['url' => $url])
Посмотреть
@endcomponent
@endisset

    Благодарим вас за использование нашего приложения!

    Спасибо, {{ config('app.name') }}
@endcomponent
