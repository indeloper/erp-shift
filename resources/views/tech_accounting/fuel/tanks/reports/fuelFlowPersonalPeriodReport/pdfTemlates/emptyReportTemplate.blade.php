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
    </style>
</head>

<body>
<div class="report-caption">
    <span>Отчет по дизельному топливу<br>{{$dateFrom}} - {{$dateTo}}</span>
</div>

<table style="width: 100%; margin-top: 35px;">
    <tr>
        <td style="width: 40%;">Материально ответственное лицо:</td>
        <td style="width: 22%; border-bottom: 1px solid; padding-right:5px; text-align: center;">

        </td>
        <td style="width:2%"></td>
        <td style="width: 12%; border-bottom: 1px solid"></td>
        <td style="width:2%"></td>
        <td style="width: 22%; border-bottom: 1px solid;  text-align: center;">
            {{$userModelInstance::find($responsibleId)->user_full_name}}
        </td>
    </tr>
    <tr>
        <td></td>
        <td style=" text-align: center; font-size: 10; color: #3f3f3f"><i>должность</i></td>
        <td></td>
        <td style=" text-align: center; font-size: 10; color: #3f3f3f"><i>подпись</i></td>
        <td></td>
        <td style="text-align: center; font-size: 10; color: #3f3f3f"><i>Ф.И.О.</i></td>
    </tr>
    <tr>
        <td style="width: 40%; padding-top: 25px;">Отчет с документами принял и проверил:</td>
        <td style="width: 22%; border-bottom: 1px solid; padding-right:5px; padding-top: 25px;"></td>
        <td style="width:2%; padding-top: 25px;"></td>
        <td style="width: 12%; border-bottom: 1px solid; padding-top: 25px;"></td>
        <td style="width:2%; padding-top: 25px;"></td>
        <td style="width: 22%; border-bottom: 1px solid;  text-align: center; padding-top: 25px;">
        </td>
    </tr>
    <tr>
        <td></td>
        <td style=" text-align: center; font-size: 10; color: #3f3f3f"><i>должность</i></td>
        <td></td>
        <td style=" text-align: center; font-size: 10; color: #3f3f3f"><i>подпись</i></td>
        <td></td>
        <td style="text-align: center; font-size: 10; color: #3f3f3f"><i>Ф.И.О.</i></td>
    </tr>
</table>
</body>
