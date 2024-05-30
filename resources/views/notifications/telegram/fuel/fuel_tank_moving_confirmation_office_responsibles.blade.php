<b>Перемещение топливной емкости</b>

<i>{{ now()->format('d.m.Y в H:m') }} завершено перемещение топливной емкости</i>
<b>Номер емкости:</b> {{ $notificationData->getData()['tank']->tank_number }}
<b>Остаток топлива:</b> {{ $notificationData->getData()['tank']->fuel_level }} л
<b>С объекта:</b> {{ App\Models\ProjectObject::find($lastTankTransferHistory->previous_object_id)->short_name ?? null }} (<a href='{{ $notificationData->getData()['previousResponsible'] ? $notificationData->getData()['previousResponsible']->getExternalUserUrl() : null }}'>{{ $notificationData->getData()['previousResponsible'] ? $notificationData->getData()['previousResponsible']->format('L f. p.', 'именительный') : null }})</a>"
<b>На объект:</b> {{ App\Models\ProjectObject::find($notificationData->getData()['tank']->object_id)->short_name ?? null }} (<a href='{{ $notificationData->getData()['newResponsible']->getExternalUserUrl() }}'>{{ $notificationData->getData()['newResponsible']->format('L f. p.', 'именительный') ?? null }}</a>)"