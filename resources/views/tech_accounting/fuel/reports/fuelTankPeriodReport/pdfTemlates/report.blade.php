<b>{{$company}}</b>
<br><br><br>
<div style="width:100%; text-align: center;">
    <span style="font-weight:bold">ОТЧЕТ по дизельному топливу</span>
</div>
<br><br>

Адрес объекта: {{$objectAdress}}
<br>
№ Бака (емкости): {{$tank_number}}
<br><br>

<table style="border-collapse: collapse; font-size:80%; width: 100%">
    

    <tr>
        <th style="border:1px solid; padding: 10px;" rowspan="2">
            Наименование
        </th>
        <th colspan="2" style="border:1px solid; padding: 10px;">
            Документ
        </th>
        <th  rowspan="2" style="border:1px solid; padding: 10px;">
            Количество (л)
        </th>
        <th  rowspan="2" style="border:1px solid; padding: 10px;">
            Отметка бухгалтерии
        </th>
    </tr>
    <tr>
        
        <th style="border:1px solid; padding: 10px;">
            Дата
        </th>
        <th style="border:1px solid; padding: 10px;">
            Номер
        </th>
    </tr>



    <tr>
        <td style="border:1px solid; padding: 10px;">
            Остаток в баке на {{$dateFrom}}
        </td>
        <td style="border:1px solid; padding: 10px;"></td>
        <td style="border:1px solid; padding: 10px;"></td>
        <td style="border:1px solid; padding: 10px;">{{$fuelVolumeDateBegin}}</td>
        <td style="border:1px solid; padding: 10px;"></td>
    </tr>

    <tr>
        <td style="border:1px solid; padding: 10px; text-align: center;">
            Приход
        </td>
        <td style="border:1px solid; padding: 10px;"></td>
        <td style="border:1px solid; padding: 10px;"></td>
        <td style="border:1px solid; padding: 10px;"></td>
        <td style="border:1px solid; padding: 10px;"></td>
    </tr>

    


    <tr>
        <td style="border:1px solid; padding: 10px;">
            Накладная
        </td>
        <td style="border:1px solid; padding: 10px;">
            12.12.2023
        </td>
        <td style="border:1px solid; padding: 10px;">
            455464
        </td>
        <td style="border:1px solid; padding: 10px;">
        500
        </td>
        <td style="border:1px solid; padding: 10px;">
            Отметки бух
        </td>
    </tr>
</table>