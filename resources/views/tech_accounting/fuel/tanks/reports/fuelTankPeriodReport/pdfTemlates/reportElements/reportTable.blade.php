<table style="border-collapse: collapse; width: 100%; font-weight: normal">

    <tr>
        <th class="td-normal " rowspan="2" style="width:60%">
            Наименование
        </th>
        <th colspan="2" class="td-normal" style="width:25%">
            Документ
        </th>
        <th rowspan="2" class="td-normal" style="width:15%">
            Количество топлива (л)
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

    @if($fuelTankId != 'no_tank_direct_fuel_flow')
        <tr class="table-summary">
            <td class="td-normal table-summary" colspan=3>
                <b>Остаток в баке на {{$summaryData['dateFrom']}}</b>
            </td>
            <td class="td-normal table-summary" style="text-align:right">
                <b>{{number_format($summaryData['fuelLevelPeriodStart'], 0, ',', ' ')}}</b>
            </td>
        </tr>
    @endif
    
    @if($fuelTankId != 'no_tank_direct_fuel_flow')
        @foreach ($objectTransferGroups as $flowTypeSlug=>$objectTransferGroup)
            @if(!empty($objectTransferGroups['adjustment']) && empty($isAdjustmentsRendered))
                @php $isAdjustmentsRendered = true; @endphp
                @include('tech_accounting.fuel.tanks.reports.fuelTankPeriodReport.pdfTemlates.reportElements.reportTableAdjustmentOperations')
            @endif

            @if(empty($objectTransferGroups['income']) && empty($isEmptyIncomeRegionRendered) && $fuelTankId != 'no_tank_direct_fuel_flow')
            @php $isEmptyIncomeRegionRendered = true; @endphp
                @include('tech_accounting.fuel.tanks.reports.fuelTankPeriodReport.pdfTemlates.reportElements.reportTableEmptyIncomesOperations')
            @endif
            
            @if($fuelTankId != 'no_tank_direct_fuel_flow')
                @include('tech_accounting.fuel.tanks.reports.fuelTankPeriodReport.pdfTemlates.reportElements.reportTableOperations')
            @endif

            @if(empty($objectTransferGroups['outcome']) && empty($isEmptyOutcomeRegionRendered)  && $fuelTankId != 'no_tank_direct_fuel_flow')
            @php $isEmptyOutcomeRegionRendered = true; @endphp
                @include('tech_accounting.fuel.tanks.reports.fuelTankPeriodReport.pdfTemlates.reportElements.reportTableEmptyOutcomesOperations')
            @endif

        @endforeach

    @else
        @include('tech_accounting.fuel.tanks.reports.fuelTankPeriodReport.pdfTemlates.reportElements.reportTableDirectFuelFlowOperations')
    @endif

    @if($fuelTankId != 'no_tank_direct_fuel_flow')
        <tr>
            <td class="td-normal row-without-borders" colspan=4></td>
        </tr>
    
 
        <tr class="table-summary">
            <td class="td-normal table-summary" colspan=3>
                <b>Остаток в баке на {{$summaryData['dateTo']}}</b>
            </td>
            <td class="td-normal table-summary" style="font-weight:bolder; text-align:right">
                <b>{{number_format($summaryData['fuelLevelPeriodFinish'], 0, ',', ' ')}}</b>
            </td>
        </tr>
    @endif

</table>

