{!! str_replace('<br>', '', $notificationData->getName()) !!}
{!! str_replace(':', '', $notificationData->getAdditionalInfo()) !!}
<i><a href='{{ str_replace('localhost', '192.11.22.33', $notificationData->getUrl()) }}'>
        Ссылка на тех поддержку
</a></i>
