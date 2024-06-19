{!! str_replace('<br>', '', $notificationData->getName()) !!}
<i><a href='{{ str_replace('localhost', '192.11.22.33', $notificationData->getData()['tasks_url']) }}'>
        Новые задачи
   </a>
</i>
<i><a href='{{ str_replace('localhost', '192.11.22.33', $notificationData->getUrl()) }}'>
        Список проектов
   </a>
</i>
