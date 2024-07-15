@component('mail::message')
    {{ $name }}

@isset($url)
@component('mail::button', ['url' => $url])
Посмотреть
@endcomponent
@endisset

    Благодарим вас за использование нашего приложения!

    Спасибо, {{ config('app.name') }}
@endcomponent
