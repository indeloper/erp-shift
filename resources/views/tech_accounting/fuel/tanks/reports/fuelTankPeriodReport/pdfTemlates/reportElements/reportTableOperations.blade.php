@if($flowTypeSlug==='income' || $flowTypeSlug==='outcome')
    <tr>
        <td class="td-normal row-without-borders" colspan=4></td>
    </tr>

    <tr class="table-summary">
        <td class="td-center table-summary" colspan=4>
            @if(empty($isEmptyIncomeRegionRendered) && $flowTypeSlug==='income')
                Приход
            @endif
            @if(empty($isEmptyOutcomeRegionRendered) && $flowTypeSlug==='outcome')
                Расход
            @endif
        </td>
    </tr>
@endif

@foreach($objectTransferGroup as $fuelFlowOperation)
    @continue($flowTypeSlug=='adjustment')
    <tr>
        <td class="td-normal" style="width:60%">
            @if($flowTypeSlug==='income')
                {{$fuelFlowOperation['contractor']}}
            @endif
            @if($flowTypeSlug==='outcome')
                {{$fuelFlowOperation['fuel_consumer']}}
            @endif
            @if($flowTypeSlug==='adjustment')
                Корректировка остатков топлива ({{ $userModelInstance::find($fuelFlowOperation['author_id'])->user_full_name }})
            @endif
        </td>
        <td class="td-center">
            {{$fuelFlowOperation['event_date'] ? $carbonInstance::create($fuelFlowOperation['event_date'])->format('d.m.Y') : ''}}
        </td>
        <td class="td-normal">{{$fuelFlowOperation['document']}}</td>
        <td class="td-normal"
            style="text-align:right"
        >
            @if($flowTypeSlug==='adjustment')
                @if($fuelFlowOperation['volume'] > 0)
                    + 
                @endif
                @if($fuelFlowOperation['volume'] < 0)
                    - 
                @endif
            @endif

            {{number_format($fuelFlowOperation['volume'], 0, ',', ' ')}}
        </td>
    </tr>

    @php
        if($flowTypeSlug==='income') {
            $totlalOperationsValuesInstance->incomesTotalAmount += $fuelFlowOperation['volume'];
        }
        if($flowTypeSlug==='outcome') {
            $totlalOperationsValuesInstance->outcomesTotalAmount += $fuelFlowOperation['volume'];
        }
    @endphp

@endforeach

@if($totlalOperationsValuesInstance->incomesTotalAmount || $totlalOperationsValuesInstance->outcomesTotalAmount)
    @if($flowTypeSlug==='income' || $flowTypeSlug==='outcome')
        <tr class="table-summary">
            <td class="td-normal table-summary">
                Итого по @if($flowTypeSlug==='income')
                    приходу
                @elseif($flowTypeSlug==='outcome')
                    расходу
                @endif
            </td>
            <td class="td-center table-summary">
                ×
            </td>
            <td class="td-center table-summary">
                ×
            </td>
            <td class="td-normal table-summary" style="font-weight:bolder; text-align:right">
                <b>
                    @if($flowTypeSlug==='income')
                        {{number_format($totlalOperationsValuesInstance->incomesTotalAmount, 0, ',', ' ')}}
                    @else
                        {{number_format($totlalOperationsValuesInstance->outcomesTotalAmount , 0, ',', ' ')}}
                    @endif
                </b>
            </td>

        </tr>
    @endif
@endif

@if($flowTypeSlug==='income')
    <tr class="table-summary">
        <td class="td-normal table-summary">
            Итого по остаткам
        </td>

        <td class="td-center">
            ×
        </td>
        <td class="td-center">
            ×
        </td>
        <td class="td-normal table-summary" style=" text-align:right">
            {{number_format($totlalOperationsValuesInstance->incomesTotalAmount + $summaryData['fuelLevelPeriodStart'], 0, ',', ' ')}}
        </td>
    </tr>

@endif
