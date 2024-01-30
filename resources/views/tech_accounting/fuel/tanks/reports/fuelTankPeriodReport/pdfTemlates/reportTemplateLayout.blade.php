@if(!($loop->first && $loop->parent->first && $loop->parent->parent->first && $loop->parent->parent->parent->first))
    <pagebreak/>
@endif

@include('tech_accounting.fuel.tanks.reports.fuelTankPeriodReport.pdfTemlates.reportElements.reportHeader')

@include('tech_accounting.fuel.tanks.reports.fuelTankPeriodReport.pdfTemlates.reportElements.reportTable')

@include('tech_accounting.fuel.tanks.reports.fuelTankPeriodReport.pdfTemlates.reportElements.reportFooter')
