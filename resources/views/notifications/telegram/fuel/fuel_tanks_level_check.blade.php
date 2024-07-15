<b>Ошибка в топливных остатках</b>
<b>Номер емкости:</b> {{$notificationData->getData()['tank']->tank_number}}
<b>Id емкости:</b> {{$notificationData->getData()['tank']->id}}
<b>Начальная дата:</b> {{$notificationData->getData()['dateFrom']}}
<b>Остатки:</b>
Таблица fuel_tanks: {{$notificationData->getData()['tank']->fuel_level}}
Последняя запись в TransferHistories: {{$notificationData->getData()['periodReportTankFuelLevel']}}
Расчет по сумме топливных операций: {{$notificationData->getData()['calculatedTankFuelLevel']}}