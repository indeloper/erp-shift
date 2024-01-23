
<tr>
    <td class="td-normal row-without-borders" colspan=4></td>
</tr>

<tr class="table-summary">
    <td class="td-center table-summary" colspan=4>
        Корректировки
    </td>
</tr>

@foreach($objectTransferGroups['adjustment'] as $fuelFlowOperation)
    <tr>
        <td class="td-normal" style="width:60%">
            Корректировка остатков топлива ({{ $userModelInstance::find($fuelFlowOperation['author_id'])->user_full_name }})
        </td>
        <td class="td-center">
            {{$fuelFlowOperation['event_date'] ? $carbonInstance::create($fuelFlowOperation['event_date'])->format('d.m.Y') : ''}}
        </td>
        <td class="td-normal">{{$fuelFlowOperation['document']}}</td>
        <td class="td-normal"
            style="text-align:right"
        >

            @if($fuelFlowOperation['volume'] > 0)
                + 
            @endif
            @if($fuelFlowOperation['volume'] < 0)
                - 
            @endif


            {{number_format($fuelFlowOperation['volume'], 0, ',', ' ')}}
        </td>
    </tr>

@endforeach