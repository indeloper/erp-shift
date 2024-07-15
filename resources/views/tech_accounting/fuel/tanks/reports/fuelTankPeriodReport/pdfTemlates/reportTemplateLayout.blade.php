
@include('tech_accounting.fuel.tanks.reports.fuelTankPeriodReport.pdfTemlates.reportElements.reportHeader')

@include('tech_accounting.fuel.tanks.reports.fuelTankPeriodReport.pdfTemlates.reportElements.reportTable')

@if($fuelTankId === 'no_tank_direct_fuel_flow')
    @include('tech_accounting.fuel.tanks.reports.fuelTankPeriodReport.pdfTemlates.reportElements.reportFuelDirectFlowsBottomModule')
@else
    @include('tech_accounting.fuel.tanks.reports.fuelTankPeriodReport.pdfTemlates.reportElements.reportFuelTankFlowsBottomModule')
@endif

@if(!($loop->last && $loop->parent->last && $loop->parent->parent->last && $loop->parent->parent->parent->last))
    <pagebreak/>
@endif

