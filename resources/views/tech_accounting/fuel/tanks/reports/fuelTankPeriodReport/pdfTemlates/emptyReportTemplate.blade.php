<head>
    <title>
        Отчет по дизельному топливу {{$dateFrom}} - {{$dateTo}}
    </title>

    <style>
        body {
            font-size: 14;
            font-family: 'calibri', sans-serif;
        }

        .report-caption {
            font-size: 16;
            width: 100%;
            text-align: center;
            font-weight: bold;
        }

        .td-normal {
            border: 1px solid;
            padding: 5px 10px;
        }

        .td-center {
            border: 1px solid;
            padding: 5px 10px;
            text-align: center;
        }

        th {
            font-weight: normal;
        }

        .table-summary {
            color: #3f3f3f;
            background-color: #f5f5f5;
            font-weight: bold;
        }

        .row-without-borders {
            border-left: 0;
            border-right: 0;
        }

        .notification-normal {
            text-align: center; 
            font-size: 14;
            width: 100%;
        }
    </style>
</head>


<body>
                <div class="report-caption">
                    <span>Отчет по дизельному топливу<br>{{$dateFrom}} - {{$dateTo}}</span>
                </div>

                <div style="margin-top: 20px" class="notification-normal">
                    Для выбранных параметров поиска данные отсутствуют
                </div>

                <div style="margin-top: 20px" class="notification-normal">
                    <span class="report-caption">Параметры поиска:</span>
                    
                    @if($responsiblesFilter->count())
                        <br>
                        Ответственны@if($responsiblesFilter->count()>1)е@elseй@endif:
                        @foreach($responsiblesFilter as $user)
                            {{$user->user_full_name}}
                            @if($responsiblesFilter->count() > 1 && !$loop->last)
                                , 
                            @endif
                        @endforeach
                    @endif

                    @if($tanksFilter->count())
                        <br>
                        Топливн@if($tanksFilter->count()>1)ые@elseая@endif емкост@if($responsiblesFilter->count()>1)и@elseь@endif: 
                        @foreach($tanksFilter as $tank)
                            {{$tank->tank_number}}
                            @if($tanksFilter->count() > 1 && !$loop->last)
                                , 
                            @endif
                        @endforeach
                    @endif

                    @if($objectsFilter->count())
                        <br>
                        Объект@if($objectsFilter->count()>1)ы@endif: 
                        @foreach($objectsFilter as $object)
                            {{$object->short_name}}
                            @if($objectsFilter->count() > 1 && !$loop->last)
                                , 
                            @endif
                        @endforeach
                    @endif                    
                </div>                
</body>