{!! str_replace('<br>', '', $notificationData->getName()) !!}
{!! str_replace('Ссылка на задачу:', '', $notificationData->getAdditionalInfo()) !!}
<i><a href='{{ str_replace('localhost', '192.11.22.33', $notificationData->getUrl()) }}'>
        Ссылка на задачу
</a></i>
