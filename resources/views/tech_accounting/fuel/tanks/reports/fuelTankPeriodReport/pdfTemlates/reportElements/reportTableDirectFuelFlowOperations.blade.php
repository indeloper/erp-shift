<tr>
    <td class="td-normal row-without-borders" colspan=4></td>
</tr>
@if(isset($objectTransferGroups["groupedIncomes"]))
    <tr class="table-summary">
        <td class="td-center table-summary" colspan=4>
            Приходы
        </td>
    </tr>

    @foreach($objectTransferGroups['groupedIncomes'] as $fuelFlowOperation)

        @php 
            $totlalOperationsValuesInstance->outcomesTotalAmount += $fuelFlowOperation['volume'];
        @endphp
        <tr>
            <td class="td-normal" style="width:60%">{{$fuelFlowOperation['contractor']}}</td>
            <td class="td-center">
                {{$fuelFlowOperation['event_date'] ? $carbonInstance::create($fuelFlowOperation['event_date'])->format('d.m.Y') : ''}}
            </td>
            <td class="td-normal">{{$fuelFlowOperation['document']}}</td>
            <td class="td-normal" style="text-align:right">
                {{number_format($fuelFlowOperation['volume'], 0, ',', ' ')}}
            </td>
        </tr>
    @endforeach

    <tr class="table-summary">
        <td class="td-normal table-summary">
            Итого по приходу
        </td>
        <td class="td-center table-summary">
            ×
        </td>
        <td class="td-center table-summary">
            ×
        </td>
        <td class="td-normal table-summary" style="font-weight:bolder; text-align:right">
            <b>{{number_format($totlalOperationsValuesInstance->outcomesTotalAmount , 0, ',', ' ')}}</b>
        </td>
    </tr>
@endif

@if(isset($objectTransferGroups["outcomes"]))
    <tr>
        <td class="td-normal row-without-borders" colspan=4></td>
    </tr>
    <tr class="table-summary">
        <td class="td-center table-summary" colspan=4>
            Расходы
        </td>
    </tr>
    @foreach($objectTransferGroups['outcomes'] as $fuelFlowOperation)
        <tr>
            <td class="td-normal" style="width:60%">{{$fuelFlowOperation['fuel_consumer']}}</td>
            <td class="td-center"></td>
            <td class="td-normal"></td>
            <td class="td-normal" style="text-align:right">
                {{number_format($fuelFlowOperation['volume'], 0, ',', ' ')}}
            </td>
        </tr>
    @endforeach

    <tr class="table-summary">
        <td class="td-normal table-summary">
            Итого по расходу
        </td>
        <td class="td-center table-summary">
            ×
        </td>
        <td class="td-center table-summary">
            ×
        </td>
        <td class="td-normal table-summary" style="font-weight:bolder; text-align:right">
            <b>{{number_format($totlalOperationsValuesInstance->outcomesTotalAmount , 0, ',', ' ')}}</b>
        </td>
    </tr>
@endif

