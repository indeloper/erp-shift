<!DOCTYPE html>
<html>
    <head>
        <meta charset="utf-8">
        <link rel="apple-touch-icon" sizes="76x76" href="{{ mix('img/apple-icon.png') }}">
        <link rel="icon" type="image/ico" href="{{ mix('img/favicon.ico') }}">
        <meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1" />
        <meta content='width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=0, shrink-to-fit=no' name='viewport' />
        <!-- CSS Files -->
        <link href="{{ mix('css/mat_acc_report.css') }}" rel="stylesheet" />
        <link href="https://fonts.googleapis.com/css?family=Roboto:300,400,500,700,900&display=swap" rel="stylesheet">
      </head>
    <style type="text/css" media="print">

        @page {
            size: auto;   /* auto is the initial value */
            margin: 0;  /* this affects the margin in the printer settings */
        }
    </style>
    <body>
        <div class="offer-list clearfix">
            <div class="list-header">
                <div class="top-header clearfix">
                    <!-- Лого для первой страницы -->
                    <div class="logo">
                            <img src="{{ mix('img/logosvg.png') }}" width="130%">
                    </div>
                    <!-- Лого для последующих страниц -->
                    <!-- <div class="small-logo">
                        <img src="../../assets/img/logosvg.png" alt="" width="100%">
                    </div> -->
                    <div style="width:185px; float:right; text-align: right" >
                        <p class="text-right address">
                          196128, г. Санкт-Петербург,<br>
                          ул.Варшавская д. 9, к.1, литера А<br>
                          Тел.: +7 (812) 922-76-96<br>
                          +7 (812) 326-94-06<br>
                          www.sk-gorod.com<br>
                          info@skgorod.com
                        </p>
                    </div>
                </div>
            </div>
            <div class="list-body">
                <div style="margin:40px auto;">
                    <h1 style="font-size:20px; text-align:center">
                        Отчет по операциям<br/>
                        от «{{ \Carbon\Carbon::now()->format('d.m.Y') }}»
                    </h1>
                </div>
                @if(isset($filterList) && count($filterList) > 0)
                <div style="font-size:16px">
                    <h3 style="font-size:17px; text-decoration:underline"><b>Фильтры:</b></h3>
                    @foreach($filterList as $filterItem)
                        <div style="margin:3px 0">
                            <span>{{$filterItem->text}}</span>
                        </div>
                    @endforeach
                </div>
                @endif
                <table class="report-table">
                    <thead>
                        <tr>
                            <th style="padding: 20px;background-color: #ffffff;border-color: #ffffff;border-bottom: red;"></th>
                            <th style="padding: 20px;background-color: #ffffff;border-color: #ffffff;border-bottom: red;"></th>
                            <th style="padding: 20px;background-color: #ffffff;border-color: #ffffff;border-bottom: red;"></th>
                            <th style="padding: 20px;background-color: #ffffff;border-color: #ffffff;border-bottom: red;"></th>
                        </tr>
                        <tr>
                            <th style="width:6%;">№ п/п</th>
                            <th style="width:10%">Тип операции</th>
                            <th style="width:11%">Материалы</th>
                            <th style="width:11%">Дата операции</th>
                            <th style="width:11%">Статус</th>
                        </tr>
                    </thead>
                    <tbody>

                    @foreach($operations as $operation)
                        <tr>
                            <td>{{$loop->iteration}}</td>
                            <td style="text-align:left">{{ $operation['operation_route_name'] }}</td>
                            <td style="text-align:left">{!! $operation['material_types_info'] !!}</td>
                            <td style="text-align:left">{{ \Carbon\Carbon::parse($operation['operation_date'])->format('d.m.Y') }}</td>
                            <td style="text-align:left">{{ $operation['operation_route_stage_name'] }}</td>
                        </tr>
                    @endforeach
                    </tbody>
                </table>
            </div>
            <div class="footer-contacts clearfix">
                Сформировал отчёт: <br>
                {{ Auth::user()->group->name }}, {{ Auth::user()->full_name }}<br>
            </div>
        </div>
    </body>
</html>

<script>
    setTimeout(function() {
        window.print();
    }, 2000);
    // ;
</script>
