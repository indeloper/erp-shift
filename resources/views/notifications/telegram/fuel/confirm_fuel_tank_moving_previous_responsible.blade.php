<b>Перемещение топливной емкости</b>

<i><a href='{{ $notificationData->getData()['newResponsible']->getExternalUserUrl() }}'>{{ $notificationData->getData()['newResponsible']->format('L f. p.', 'именительный') ?? null }}</a>{{ morphos\Russian\RussianLanguage::verb('подтвердил', mb_strtolower($notificationData->getData()['newResponsible']->gender)) }} перемещение {{ now()->format('d.m.Y в H:m') }}</i>
<b>Номер емкости:</b> {{ $notificationData->getData()['tank']->tank_number }}
<b>Остаток топлива:</b> {{ $notificationData->getData()['tank']->fuel_level }} л
<b>С объекта:</b> {{ App\Models\ProjectObject::find($notificationData->getData()['lastTankTransferHistory']->previous_object_id)->short_name ?? null }}
<b>На объект:</b> {{ (App\Models\ProjectObject::find($notificationData->getData()['tank']->object_id)->short_name ?? null ) }}