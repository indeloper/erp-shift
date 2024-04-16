<b>Заявка на формирование приказов</b>
<i><a href='{{ $notificationData->getData()['orderRequestAuthor']->getExternalUserUrl() }}'>{{ $notificationData->getData()['orderRequestAuthor']->format('L f. p.', 'именительный') ?? null }}</a>{{ morphos\Russian\RussianLanguage::verb('создал', mb_strtolower($notificationData->getData()['orderRequestAuthor']->gender)) }} заявку <u>#{{ $notificationData->getData()['orderRequestId'] }}</u></i>
<b>Организация:</b> {{ $notificationData->getData()['company']->name }}
<b>Адрес объекта:</b> {{ $notificationData->getData()['projectObject']->short_name }}