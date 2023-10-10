<style>
    .td-normal {
        border:1px solid; 
        padding: 5px 10px;
    }
    .td-center {
        border:1px solid; 
        padding: 5px 10px;
        text-align: center;
    }
</style>

<b>{{$company}}</b>
<br><br><br>
<div style="width:100%; text-align: center;">
    <span style="font-weight:bold">ОТЧЕТ по дизельному топливу<br>{{$dateFrom}} - {{$dateTo}}</span>
</div>
<br><br>

Адрес объекта: {{$objectAdress}}
<br>
№ Бака (ёмкости): {{$tank_number}}
<br><br>

<table style="border-collapse: collapse; font-size:80%; width: 100%">
    

    <tr>
        <th class="td-normal" rowspan="2">
            Наименование
        </th>
        <th colspan="2" class="td-normal">
            Документ
        </th>
        <th  rowspan="2" class="td-normal">
            Количество (л)
        </th>
        <th  rowspan="2" class="td-normal">
            Отметка бухгалтерии
        </th>
    </tr>
    <tr>
        
        <th class="td-normal">
            Дата
        </th>
        <th class="td-normal">
            Номер
        </th>
    </tr>



    <tr>
        <td class="td-normal" colspan="3">
            Остаток в баке на {{$dateFrom}}
        </td>
        <td class="td-normal">{{number_format($fuelVolumeDateBegin, 0, ',', ' ')}}</td>
        <td class="td-normal"></td>
    </tr>

    <tr>
        <td class="td-center" style="border-right: 0;">
            <b>Приход</b> 
        </td>
        <td class="td-normal" style="border-left: 0;" colspan=4></td>
    </tr>

    @foreach($fuelIncomes as $fuelIncome)
        <tr>
            <td class="td-center">
                {{$fuelIncome['contractor']->short_name}}
            </td>
            <td class="td-normal">
                {{$carbonInstance::create($fuelIncome->document_date)->format('d.m.Y')}}
            </td>
            <td class="td-normal">
                {{$fuelIncome->document}}
            </td>
            <td class="td-normal">
                {{number_format($fuelIncome->volume, 0, ',', ' ')}}
            </td>
            <td class="td-normal"></td>
        </tr>
    @endforeach

    <tr><td class="td-normal" colspan=5></tr>

    <tr>
        <td class="td-center">
           <b>Итого по приходу</b> 
        </td>
        <td class="td-center">
            х
        </td>
        <td class="td-center">
            х
        </td>
        <td class="td-normal">
            <b>{{number_format($fuelSumIncomes, 0, ',', ' ')}}</b> 
        </td>
        <td class="td-normal">
        </td>
    </tr>

    <tr><td class="td-normal" colspan=5></tr>
    <tr>
        <td class="td-center" style="border-right: 0;">
            <b>Расход</b> 
        </td>
        <td class="td-normal" style="border-left: 0;" colspan=4></td>
    </tr>

    @foreach($fuelOutcomes as $fuelOutcome)
        <tr>
            <td class="td-center">
                {{$fuelOutcome['ourTechnic']->name}}
            </td>
            <td class="td-normal">
                {{$carbonInstance::create($fuelIncome->document_date)->format('d.m.Y')}}
            </td>
            <td class="td-normal">
                {{$fuelOutcome->document}}
            </td>
            <td class="td-normal">
                {{number_format($fuelOutcome->volume, 0, ',', ' ')}}
            </td>
            <td class="td-normal"></td>
        </tr>
    @endforeach

    <tr><td class="td-normal" colspan=5></tr>

    <tr>
        <td class="td-center">
            <b>Итого по расходу</b>
        </td>
        <td class="td-center">
            х
        </td>
        <td class="td-center">
            х
        </td>
        <td class="td-normal">
            <b>{{number_format($fuelSumOutcomes, 0, ',', ' ')}}</b>
        </td>
        <td class="td-normal">
        </td>
    </tr>

    <tr><td class="td-normal" colspan=5></tr>

    <tr>
        <td class="td-normal" colspan=3>
            Остаток в баке на {{$dateTo}}
        </td>

        <td class="td-normal">{{number_format($fuelVolumeDateBegin + $fuelSumIncomes - $fuelSumOutcomes, 0, ',', ' ')}}</td>
        <td class="td-normal"></td>
    </tr>
    
    
    <tr>
        <td style="padding-top: 20px ;">
            Остаток топлива на начало периода (л)
        </td>
        <td colspan="2" style="border-bottom: 1px solid; padding-top: 20px; text-align:center">{{number_format($fuelVolumeDateBegin, 0, ',', ' ')}}</td>
        <td colspan="2"></td>
    </tr>
    <tr>
        <td style="padding-top: 10px ;">
            Итого поступило (л)
        </td>
        <td colspan="2" style="border-bottom: 1px solid; padding-top: 10px; text-align:center">{{number_format($fuelSumIncomes, 0, ',', ' ')}}</td>
        <td colspan="2"></td>
    </tr>
    <tr>
        <td style="padding-top: 10px;">
            Итого израсходовано (л)
        </td>
        <td colspan="2" style="border-bottom: 1px solid; padding-top: 10px; text-align:center">{{number_format($fuelSumOutcomes, 0, ',', ' ')}}</td>
        <td colspan="2"></td>
    </tr>
    <tr>
        <td style="padding-top: 10px ;">
            Остаток топлива на конец периода (л)
        </td>
        <td colspan="2" style="border-bottom: 1px solid; padding-top: 10px; text-align:center">{{number_format($fuelVolumeDateBegin + $fuelSumIncomes - $fuelSumOutcomes, 0, ',', ' ')}}</td>
        <td colspan="2"></td>
    </tr>

    <tr >
        <td style="padding-top: 40px; font-size: 80%">
            Материально ответственное лицо
        </td>
        <td colspan="2" style="border-bottom: 1px solid; padding-top: 40px; text-align: right;"></td>
        <td style="border-bottom: 1px solid; padding-top: 40px; text-align: center;">{{$fuelTankResponsible->user_full_name}}</td>
        <td></td>
    </tr>
    <tr>
        <td></td>
        <td style="text-align: center; font-size: 80%">Должность</td>
        <td style="text-align: center; font-size: 80%">Подпись</td>
        <td style="text-align: center; font-size: 80%">Расшифровка</td>
        <td></td>
    </tr>

    <tr >
        <td style="padding-top: 20px ; font-size: 80%">
            Отчет с документами принял и проверил
        </td>
        <td colspan="2" style="border-bottom: 1px solid; padding-top: 20px;"></td>
        <td style="border-bottom: 1px solid; padding-top: 20px;"></td>
        <td></td>
    </tr>
    <tr>
        <td></td>
        <td style="text-align: center; font-size: 80%">Должность</td>
        <td style="text-align: center; font-size: 80%">Подпись</td>
        <td style="text-align: center; font-size: 80%">Расшифровка</td>
        <td></td>
    </tr>

</table>
<br><br>


