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
                          Тел.: +7(812) 922-76-96<br>
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
                        Отчет по состоянию материалов <br/>
                        от «{{ \Carbon\Carbon::now()->format('d.m.Y') }}»
                    </h1>
                </div>
                <div style="font-size:16px">
                    <h3 style="font-size:17px; text-decoration:underline"><b>Фильтры:</b></h3>
                    <div style="margin:3px 0">
                        <span><b>По состоянию на: </b></span><span>{{ \Carbon\Carbon::parse($filters['date'])->format('d.m.Y') }}</span>
                    </div>
                    <br>
                    @if(isset($filters[1]))
                        @foreach($filters[1] as $materials)
                            <div style="margin:3px 0">
                                <span><b>Материал: </b></span><span>{{ $materials }}</span>
                            </div>
                        @endforeach
                    @endif
                    @if(isset($filters[0]))
                        @foreach($filters[0] as $object)
                            <div style="margin:3px 0">
                                <span><b>Объект: </b></span><span>{{ $object }}</span>
                            </div>
                        @endforeach
                    @endif
                    @if(isset($filters[3]))
                        @foreach($filters[3] as $reference)
                            <div style="margin:3px 0">
                                <span><b>Эталон: </b></span><span>{{ $reference }}</span>
                            </div>
                        @endforeach
                    @endif
                </div>
                <table class="report-table">
                    <thead>
                        <tr>
                            <th style="padding: 20px;background-color: #ffffff;border-color: #ffffff;border-bottom: red;"></th>
                            <th style="padding: 20px;background-color: #ffffff;border-color: #ffffff;border-bottom: red;"></th>
                            <th style="padding: 20px;background-color: #ffffff;border-color: #ffffff;border-bottom: red;"></th>
                            <th style="padding: 20px;background-color: #ffffff;border-color: #ffffff;border-bottom: red;"></th>
                        </tr>
                        <tr>
                            <th style="width:8%;">№</th>
                            <th style="width:54%">Материал</th>
                            <th style="width:19%">Ед. измерения</th>
                            <th style="width:19%">Количество</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($prepared_results->values() as $id => $result)
                            <tr>
                                <td colspan="4" class="td-head">{{ $id + 1 }}. @if($result[0]['object_short_name']) <u>{{ $result[0]['object_short_name'] }}</u> @else <u>{{ $result[0]['object_name'] }}</u> по адресу <u>{{ $result[0]['object_address'] }}</u> @endif </td>
                            </tr>
                            @foreach($result as $mat_num => $material)
                                <tr>
                                    <td>{{ $id + 1 }}.{{ $mat_num + 1 }}</td>
                                    <td style="text-align:left">{{ $material['mat_name'] }}</td>
                                    <td>{{ $material['mat_unit'] }}</td>
                                    <td>{{ round($material['mat_count'] , 2) }}</td>
                                </tr>
                            @endforeach
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
