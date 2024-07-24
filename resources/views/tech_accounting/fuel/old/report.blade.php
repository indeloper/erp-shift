@php use Carbon\Carbon; @endphp
    <!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    @vite('resources/img/apple-icon.png')
    @vite('resources/img/favicon.ico')
    <meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1"/>
    <meta content='width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=0, shrink-to-fit=no'
          name='viewport'/>
    <!-- CSS Files -->
    @vite('resources/css/ttn.css')
    <link href="https://fonts.googleapis.com/css?family=Roboto:300,400,500,700,900&display=swap" rel="stylesheet">
</head>
{{--@if ($operations->count() > 49 && $operations->count() <= 55)
<style>
    .ttn-table th {
        font-size: 5pt;
    }
</style>
@elseif ($operations->count() > 55  && $operations->count() <= 67)
    <style>
        .ttn-table th {
            font-size: 4pt;
        }
    </style>
@endif--}}
<style>
    div.tablePage {
        page-break-inside: avoid;
        page-break-after: always;
    }
</style>
<style media="screen">
    .title {
        font-size: 5pt;
    }

    .test-c {
        background-color: red;
    }

    .logo-print {
        width: 180px;
        margin-top: -5px
    }

    body {
        margin: 0px;
    }

    .pbprint {
        padding-bottom: 15px;
    }

    @media print {
        body {
            width: 100% !important;
            height: 100% !important;
            padding: 0 !important;
            margin: 0 !important;
        }
    }

    .hidden-th {
        border-right: none !important;
        border-left: none !important;
    }

    .hidden-special-th {
        border-right: none !important;
        border-left: none !important;
        border-top: none !important;
    }

    #operation-separator.hidden-tr th {
        border-right: none !important;
        border-left: none !important;
        border-bottom: none !important;
    }

    .head-applic {
        font-size: 5pt;
    }

    @media (max-width: 450px) {
        .logo-print {
            width: 120px
        }

        .head-applic {
            font-size: 5pt;
        }

        .pbprint {
            padding-bottom: 5px;
        }
    }
</style>
<style type="text/css" media="print">

    .logo-print {
        width: 170px;
        margin-top: -5px
    }

    .hidden-th {
        border-right: none !important;
        border-left: none !important;
    }

    .hidden-special-th {
        border-right: none !important;
        border-left: none !important;
        border-top: none !important;
    }

    #operation-separator.hidden-tr th {
        border-right: none !important;
        border-left: none !important;
        border-bottom: none !important;
    }

    @page {
        size: auto;   /* auto is the initial value */
        margin: 20px;
    }

    body {
        margin: 0px;
    }

    .pbprint {
        padding-bottom: 14px;
    }

    @media print {
        body {
            width: 100% !important;
            height: 100% !important;
            padding: 0 !important;
            margin: 0 !important;
        }

        #debug-panel {
            display: none !important;
        }
    }

    .head-applic {
        font-size: 5pt;
    }

    @media (max-width: 450px) {
        .logo-print {
            width: 120px
        }

        .head-applic {
            font-size: 5pt;
        }

        .pbprint {
            padding-bottom: 5px;
        }
    }
</style>
<body>
<div style="position: absolute; left: 1000px; top: 200px; width: 300px; display: none;" id="debug-panel">
    pageHeight
    <el-input-number :min="0"
                     :max="2000"
                     v-model="pageHeight"
                     :precision="0"
                     :step="1"
                     style="display: block; width: 100%"
                     @change="rerender"
    ></el-input-number>
    <br>
    pageContentHeight
    <el-input-number :min="0"
                     :max="2000"
                     v-model="pageContentHeight"
                     :precision="0"
                     :step="1"
                     style="display: block; width: 100%"
                     @change="rerender"
    ></el-input-number>
    <br>
    pageSpecialContentHeight
    <el-input-number :min="0"
                     :max="2000"
                     v-model="pageSpecialContentHeight"
                     :precision="0"
                     :step="1"
                     style="display: block; width: 100%"
                     @change="rerender"
    ></el-input-number>
    <br>
    magicStandartPagePaddingCompensator
    <el-input-number :min="-2000"
                     :max="2000"
                     v-model="magicStandartPagePaddingCompensator"
                     :precision="0"
                     :step="1"
                     style="display: block; width: 100%"
                     @change="rerender"
    ></el-input-number>
    <br>
    <el-button style="display: block; width: 100%; margin: 0" @click.stop="print">Печать</el-button>
    <br>
    <el-button style="display: block; width: 100%; margin: 0" @click.stop="reset">Сброс</el-button>
</div>
<div class="ttn-list clearfix">
    <div class="list-header">
        <div class="top-header clearfix">
            <img src="{{ asset('img/logosvg.png') }}" alt="ск город" class="logo-print">
        </div>
    </div>
    <div class="list-body" style="margin-top:-20px">
        <div style="width: 60%">
                <span style="font-size: 4pt; width:100%; display:inline-block; text-align: center">
                    <b>ООО "СК ГОРОД"</b>
                </span>
        </div>
        <div style="width: 60%; margin-top: -12px;">
                <span
                    style="border-top:0.5px solid black; font-size: 4pt; width:100%; display:inline-block; text-align: center;">
                    организация
                </span>
        </div>
        <div style="width: 60%; margin-top: -12px;">
                <span style="font-size: 4pt; width:100%; display:inline-block; text-align: center">
                    @switch($mode)
                        @case(1)
                            <b>{{ $fuelTank->object->location . '; ' . $fuelTank->name }}</b>
                            @break
                        @case(2)
                            <b>{{ $object->location }}</b>
                            @break
                        @case(3)
                            <b>{{ $fuelTank->object->location . '; ' . $fuelTank->name }}</b>
                            @break
                    @endswitch
                </span>
        </div>
        <div style="width: 60%; margin-top: -12px;">
                <span
                    style="border-top:0.5px solid black; font-size: 4pt; width:100%; display:inline-block; text-align: center;">
                    адреса объектов
                </span>
        </div>
        <div style="width: 60%; float: right;">
            <table class="ttn-table" style="border-bottom:0">
                <tbody>
                <tr>
                    <th style="width:25%;" rowspan="2">Номер документа</th>
                    <th style="width:25%;" rowspan="2">Дата составления</th>
                    <th style="width:50%" colspan="2">Отчетный период</th>
                </tr>
                <tr>
                    <th style="width:25%">с</th>
                    <th style="width:25%">по</th>
                </tr>
                <tr style="border-top: 2px solid black;">
                    <th style="border-left: 2px solid black; border-bottom: 0; width:25%; opacity: 0;">.</th>
                    <th style="width:25%; border-bottom: 2px solid black;"
                        rowspan="2">{{ Carbon::now()->format('d.m.Y') }}</th>
                    <th style="width:25%; border-bottom: 2px solid black;"
                        rowspan="2">{{ now()->parse(request()->operation_date_from ?? (isset($operations) ? ($operations->first() ? $operations->first()->operation_date : false) : now()))->format('d.m.Y') }}</th>
                    <th style="width:25%; border-right: 2px solid black; border-bottom: 2px solid black;"
                        rowspan="2">{{ now()->parse(request()->operation_date_to ?? (isset($operations) ? ($operations->last() ? $operations->last()->operation_date : false) : now()))->format('d.m.Y') }}</th>
                </tr>
                <tr style="border-bottom: 2px solid black;">
                    <th style="border-left: 2px solid black; border-top: 0; opacity: 0">.</th>
                </tr>
                </tbody>
            </table>
        </div>
        <div style="margin:0 auto; margin-bottom:2px; margin-top: 70px">
            <h1 style="font-size:8pt; text-align:center; text-transform: uppercase">
                <b>ОТЧЕТ по дизельному топливу</b>
            </h1>
        </div>
        <div style="width: 40%; float: left;">
                <span style="font-size: 4pt; width:100%; display:inline-block;">
                    Материально ответственное лицо
                </span>
        </div>
        <div style="width: 50%; float: left">
                <span style="font-size: 4pt; width:100%; display:inline-block;">
                    {{ $responsible_user->group->name . ' ' . $responsible_user->long_full_name }}
                </span>
        </div>
        <div style="width: 40%; float: left; margin-top: -12px;">
                <span style="font-size: 1pt; opacity: 0">
                    .
                </span>
        </div>
        <div style="width: 50%; float: left; margin-top: -12px; margin-bottom: 7px;">
                <span
                    style="border-top:0.5px solid black; font-size: 4pt; width:100%; display:inline-block; text-align: center;">
                    должность, фамилия, имя, отчество
                </span>
        </div>
        <div>
            <table class="ttn-table splitForPrint" style="border: 0;">
                <tr>
                    <th style="width: 45%" rowspan="2">Наименование</th>
                    <th style="width: 25%" colspan="2">Документ</th>
                    <th style="width: 10%" rowspan="2">Количество дизельного литров</th>
                    <th style="width: 20%" colspan="2">Отметки бухгалтера</th>
                </tr>
                <tr>
                    <th style="width: 12.5%">дата</th>
                    <th style="width: 12.5%">номер</th>
                    <th style="width: 20%; opacity: 0" colspan="2">.</th>
                </tr>
                <tr>
                    <th style="width: 45%">1</th>
                    <th style="width: 12.5%; border-bottom: 2px solid black;">2</th>
                    <th style="width: 12.5%; border-bottom: 2px solid black;">3</th>
                    <th style="width: 10%; border-bottom: 2px solid black;">4</th>
                    <th style="width: 10%">5</th>
                    <th style="width: 10%">6</th>
                </tr>
                <tr>
                    <th style="width: 45%">Остаток на объекте на
                        <b>{{ Carbon::parse(request()->operation_date_from)->locale('ru')->isoFormat('D[-е] MMMM YYYY') }}</b>
                    </th>
                    <th style="width: 12.5%; border-left: 2px solid black;"></th>
                    <th style="width: 12.5%"></th>
                    <th style="width: 10%; border-right: 2px solid black;"><b>{{ $start_value }}</b></th>
                    <th style="width: 10%"></th>
                    <th style="width: 10%"></th>
                </tr>
                <tr>
                    <th style="width: 45%"><b>Приход</b></th>
                    <th style="width: 12.5%; border-left: 2px solid black;"></th>
                    <th style="width: 12.5%"></th>
                    <th style="width: 10%; border-right: 2px solid black;"></th>
                    <th style="width: 10%"></th>
                    <th style="width: 10%"></th>
                </tr>
                {{--ROW TO REPEAT--}}
                @foreach($operations->where('type', 1) as $operation)
                    <tr class="page-breakable-row">
                        <th style="width: 45%; text-align: left">{{ $operation->contractor->short_name }}</th>
                        <th style="width: 12.5%; border-left: 2px solid black;">{{ Carbon::parse($operation->operation_date)->format('d.m.Y') }}</th>
                        <th style="width: 12.5%"></th>
                        <th style="width: 10%; border-right: 2px solid black;">{{ number_format($operation->value, 3) }}</th>
                        <th style="width: 10%"></th>
                        <th style="width: 10%"></th>
                    </tr>
                @endforeach
                <tr>
                    <th style="width: 45%; text-align: right">Итого по приходу</th>
                    <th style="width: 12.5%; border-left: 2px solid black;">X</th>
                    <th style="width: 12.5%">X</th>
                    <th style="width: 10%; border-right: 2px solid black;">{{ number_format($operations->where('type', 1)->sum('value'), 3) }}</th>
                    <th style="width: 10%"></th>
                    <th style="width: 10%"></th>
                </tr>
                <tr>
                    <th style="width: 45%; text-align: right">Итого c остатком</th>
                    <th style="width: 12.5%; border-left: 2px solid black;">X</th>
                    <th style="width: 12.5%">X</th>
                    <th style="width: 10%; border-right: 2px solid black;">{{ number_format($start_value + $operations->where('type', 1)->sum('value'), 3) }}</th>
                    <th style="width: 10%"></th>
                    <th style="width: 10%"></th>
                </tr>
                <tr id="operation-separator">
                    <th style="width: 45%; border-left: 0; opacity: 0;">.</th>
                    <th style="width: 35%; border-left: 2px solid black; border-right: 2px solid black;"
                        colspan="3"></th>
                    <th style="width: 20%; border-right: 0; " colspan="2"></th>
                </tr>
                <tr class="special-page-breakable-row page-breakable-row">
                    <th style="width: 45%"><b>Расход</b></th>
                    <th style="width: 12.5%; border-left: 2px solid black;"></th>
                    <th style="width: 12.5%"></th>
                    <th style="width: 10%; border-right: 2px solid black;"></th>
                    <th style="width: 10%"></th>
                    <th style="width: 10%"></th>
                </tr>
                {{--ROW TO REPEAT--}}
                @foreach($operations->where('type', 2) as $operation)
                    <tr class="page-breakable-row">
                        <th style="width: 45%; text-align: left">{{ $operation->our_technic->name . ($operation->our_technic->trashed() ? ' (удалена)' : '') }}</th>
                        <th style="width: 12.5%; border-left: 2px solid black;">{{ Carbon::parse($operation->operation_date)->format('d.m.Y') }}</th>
                        <th style="width: 12.5%"></th>
                        <th style="width: 10%; border-right: 2px solid black;">{{ number_format($operation->value, 3) }}</th>
                        <th style="width: 10%"></th>
                        <th style="width: 10%"></th>
                    </tr>
                @endforeach

                <tr>
                    <th style="width: 45%; text-align: right">Итого по расходу</th>
                    <th style="width: 12.5%; border-left: 2px solid black;">X</th>
                    <th style="width: 12.5%">X</th>
                    <th style="width: 10%; border-right: 2px solid black;">{{ number_format($operations->where('type', 2)->sum('value'), 3) }}</th>
                    <th style="width: 10%"></th>
                    <th style="width: 10%"></th>
                </tr>
                <tr>
                    <th style="width: 45%">Остаток на объекте на
                        <b>{{ Carbon::parse(request()->operation_date_to)->locale('ru')->isoFormat('D[-е] MMMM YYYY') }}</b>
                    </th>
                    <th style="width: 12.5%; border-left: 2px solid black; border-bottom: 2px solid black;"></th>
                    <th style="width: 12.5%; border-bottom: 2px solid black;"></th>
                    <th style="width: 10%; border-right: 2px solid black; border-bottom: 2px solid black;">
                        <b>{{ number_format($operations->last()->result_value ?? 0, 3) }}</b></th>
                    <th style="width: 10%"></th>
                    <th style="width: 10%"></th>
                </tr>
            </table>
        </div>
        <div style="width: 30%; float: left;">
                <span style="font-size: 4pt; width:100%; display:inline-block;">
                    Приложение
                </span>
        </div>
        <div style="width: 60%; float: left">
                <span style="font-size: 4pt; width:100%; display:inline-block; opacity: 0;">
                    .
                </span>
        </div>
        <div style="width: 1%; float: left">
                <span style="font-size: 4pt; width:100%; display:inline-block; opacity: 0;">
                    .
                </span>
        </div>
        <div style="width: 9%; float: left">
                <span style="font-size: 4pt; width:100%; display:inline-block;">
                    документов
                </span>
        </div>
        <div style="width: 30%; float: left; margin-top: -12px;">
                <span style="font-size: 1pt; opacity: 0">
                    .
                </span>
        </div>
        <div style="width: 60%; float: left; margin-top: -12px; margin-bottom: 7px;">
                <span
                    style="border-top:0.5px solid black; font-size: 4pt; width:100%; display:inline-block; text-align: center;">
                    .
                </span>
        </div>
        <div style="width: 10%; float: left; margin-top: -12px;">
                <span style="font-size: 1pt; opacity: 0">
                    .
                </span>
        </div>
        <div style="width: 30%; float: left;">
                <span style="font-size: 4pt; width:100%; display:inline-block;">
                    Отчет с документами принял и проверил
                </span>
        </div>
        <div style="width: 20%; float: left">
                <span style="font-size: 4pt; width:100%; display:inline-block; opacity: 0;">
                    гл. бухгалтер
                </span>
        </div>
        <div style="width: 5%; float: left">
                <span style="font-size: 4pt; width:100%; display:inline-block; opacity: 0;">
                    .
                </span>
        </div>
        <div style="width: 20%; float: left">
                <span style="font-size: 4pt; width:100%; display:inline-block; opacity: 0;">
                    .
                </span>
        </div>
        <div style="width: 5%; float: left">
                <span style="font-size: 4pt; width:100%; display:inline-block; opacity: 0;">
                    .
                </span>
        </div>
        <div style="width: 20%; float: left">
                <span style="font-size: 4pt; width:100%; display:inline-block; opacity: 0;">
                    Рябинина А.Ю.
                </span>
        </div>
        <div style="width: 30%; float: left; margin-top: -12px;">
                <span style="font-size: 4pt; width:100%; display:inline-block; opacity: 0;">
                    .
                </span>
        </div>
        <div style="width: 20%; float: left; margin-top: -12px;">
                <span
                    style="border-top:0.5px solid black; font-size: 4pt; width:100%; display:inline-block; text-align: center;">
                    должность
                </span>
        </div>
        <div style="width: 5%; float: left; margin-top: -12px;">
                <span style="font-size: 4pt; width:100%; display:inline-block; opacity: 0;">
                    .
                </span>
        </div>
        <div style="width: 20%; float: left; margin-top: -12px;">
                <span
                    style="border-top:0.5px solid black; font-size: 4pt; width:100%; display:inline-block; text-align: center;">
                    подпись
                </span>
        </div>
        <div style="width: 5%; float: left; margin-top: -12px;">
                <span style="font-size: 4pt; width:100%; display:inline-block; opacity: 0;">
                    .
                </span>
        </div>
        <div style="width: 20%; float: left; margin-top: -12px;">
                <span
                    style="border-top:0.5px solid black; font-size: 4pt; width:100%; display:inline-block; text-align: center;">
                    расшифровка подписи
                </span>
        </div>
        <div style="width: 30%; float: left;">
                <span style="font-size: 4pt; width:100%; display:inline-block;">
                    Материальное ответственное лицо
                </span>
        </div>
        <div style="width: 20%; float: left">
                <span style="font-size: 4pt; width:100%; display:inline-block; opacity: 0;">
                    Рук. проектов
                </span>
        </div>
        <div style="width: 5%; float: left">
                <span style="font-size: 4pt; width:100%; display:inline-block; opacity: 0;">
                    .
                </span>
        </div>
        <div style="width: 20%; float: left">
                <span style="font-size: 4pt; width:100%; display:inline-block; opacity: 0;">
                    .
                </span>
        </div>
        <div style="width: 5%; float: left">
                <span style="font-size: 4pt; width:100%; display:inline-block; opacity: 0;">
                    .
                </span>
        </div>
        <div style="width: 20%; float: left">
                <span style="font-size: 4pt; width:100%; display:inline-block; opacity: 0;">
                    Самсонов К.Н.
                </span>
        </div>
        <div style="width: 30%; float: left; margin-top: -12px;">
                <span style="font-size: 4pt; width:100%; display:inline-block; opacity: 0;">
                    .
                </span>
        </div>
        <div style="width: 20%; float: left; margin-top: -12px;">
                <span
                    style="border-top:0.5px solid black; font-size: 4pt; width:100%; display:inline-block; text-align: center;">
                    должность
                </span>
        </div>
        <div style="width: 5%; float: left; margin-top: -12px;">
                <span style="font-size: 4pt; width:100%; display:inline-block; opacity: 0;">
                    .
                </span>
        </div>
        <div style="width: 20%; float: left; margin-top: -12px;">
                <span
                    style="border-top:0.5px solid black; font-size: 4pt; width:100%; display:inline-block; text-align: center;">
                    подпись
                </span>
        </div>
        <div style="width: 5%; float: left; margin-top: -12px;">
                <span style="font-size: 4pt; width:100%; display:inline-block; opacity: 0;">
                    .
                </span>
        </div>
        <div style="width: 20%; float: left; margin-top: -12px;">
                <span
                    style="border-top:0.5px solid black; font-size: 4pt; width:100%; display:inline-block; text-align: center;">
                    расшифровка подписи
                </span>
        </div>
    </div>
</div>
</body>
</html>
@vite('resources/js/core/jquery.3.2.1.min.js')
<script>
    setTimeout(breakPage, 1500);

    function breakPage() {
        let MaxHeight = 920;
        let RunningHeight = 0;
        let PageNo = 1;
        $('table.splitForPrint>tbody>tr').each(function () {
            if (RunningHeight + $(this).height() > MaxHeight) {
                RunningHeight = 0;
                PageNo += 1;
                MaxHeight = 1120;
            }
            RunningHeight += $(this).height();
            $(this).attr("data-page-no", PageNo);
        });
        for (let i = 1; i <= PageNo; i++) {
            if (i < PageNo) {
                $('table.splitForPrint').parent().append("<div class='tablePage'><table class='ttn-table' style='border: 0;' id='Table" + i + "'><tbody></tbody></table></div>");
            } else {
                $('table.splitForPrint').parent().append("<div><table class='ttn-table' id='Table" + i + "'><tbody></tbody></table></div>");
            }
            let rows = $('table tr[data-page-no="' + i + '"]');
            $('#Table' + i).find("tbody").append(rows);
        }
        $('table.splitForPrint').remove();
        setTimeout(function () {
            window.print();
        }, 500);
    }
</script>
