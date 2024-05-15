{!! str_replace('<br>', '', $notificationData->getName()) !!}
<i><a href='{{ str_replace('localhost', '192.11.22.33', $notificationData->getUrl()) }}'>
        {!! str_replace(':', '', $notificationData->getAdditionalInfo()) !!}
</a></i>
